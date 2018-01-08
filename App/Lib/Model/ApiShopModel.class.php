<?php
/**
 *ApiShop实体店Model
 *TableName:user
 *类型:1平台管理员;2供应商;3分公司;4实体店
 */
class ApiShopModel extends BaseModel 
{
    
    protected $tableName='user';
    
    const DEFAULT_CITYCOD='bj';//默认北京的cityId
    const DEFAULT_CITYID=36;//默认北京的cityId
    const DEFAULT_CITY_LAT=39.904989;//默认北京的纬度
    const DEFAULT_CITY_LNG=116.405285;//默认北京的经度
    
    const PAGE_LIMIT_NUMBER=10;
    
    const SHOP_ROLE_ID=5;//需要查询的实体店类型
    
    const SUPPLIER_ROLE_ID=3;//供应商类型
    
    const SHOP_ROLE_STATUS=1;//实体店的状态,可用
    
    const EARTH_RADIUS = 6378.137;//地球半径
        
    const SHOP_SEARCHDATA_REDISKEYS='SHG:JIAJU:SHOPDATA';
    
    const SHOP_REDISKEYS_EXPIRETIME=3600;//redis过期时间秒
    
    const CACHE_EXPIRETIME=3600;//缓存时间
    
    
    static $ShopBrandData=NULL;//品牌数据列表
    
    static $CurrentCityId=NULL;
    /*构造函数*/
    public function __construct() 
    {
        parent::__construct();
        //调整超时时间，和使用内存
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ini_set('memory_limit','1024M');
        //加载品牌列表
        $this->ShopBrandList(); 
        //读取Cookie
        self::$CurrentCityId=self::DEFAULT_CITYID;
        if($_COOKIE['cookie_cityId'])
        {
            self::$CurrentCityId=$_COOKIE['cookie_cityId'];   
        }
    }
    
    /*获取推荐商家列表
    /*查询的类型为实体店，状态为可用，需要的参数有所在地CityCode
    *CityCode:用户所在的城市
    *经度：lng
    *纬度：lat
    *当前第几页：page=1
    */
    function GetRecommendShop($param=array())
    {
        
        $CacheData=$this->GetCommandShopCache($param);//首先判断是否有缓存
        if($CacheData) return $CacheData;
        
        $WhereOption=array();
        //判断条件如果有参数ShopId则说明来源是从Pay页面过来调实体店数据的，城市Id可以不用查
        //如果从首页过来则必须需要城市ID
        if(isset($param['ShopId']) && is_numeric($param['ShopId']))
        {
            $WhereOption['id']=intval($param['ShopId']);    
        }
        else 
        {
            //判断城市ID是否为空如果为空则默认北京
            $WhereOption['city_id']=self::DEFAULT_CITYID; 
        }
        //如果是从Pay页面或者其他页面过来需要查询实体
        if(isset($param['role_id']) && !empty($param['role_id']))
        {
            $WhereOption['role_id']=$param['role_id'];   
        }
        else 
        {
            $WhereOption['role_id']=self::SHOP_ROLE_ID;   
        }
        $WhereOption['status']=self::SHOP_ROLE_STATUS;
        //排序方法,城市为空时为这个方法排序+添加时间（添加时间越晚约靠前）
        
        $orderBy=array('shop_sort'=>'asc','create_time'=>'desc');
        if(isset($param['city_id']) && !empty($param['city_id']))
        {
            //定位成功
            $WhereOption['city_id']=$param['city_id']; 
            //定位成功时：排序+定位
             //$orderBy=array('shop_sort'=>'asc');
        }
        
        /*获得经度和纬度是数字类型*/
        $lng=is_numeric($param['lng']) ? $param['lng'] : 0;//经度longitude
        $lat=is_numeric($param['lat']) ? $param['lat'] : 0;//纬度latitude
        $PointA=array();
        $PointA['lng']=$lng;
        $PointA['lat']=$lat;

            //分页
            import('ORG.Util.Page');// 导入分页类
            $Page=intval($param['page']);//获取当前页
            $offset=0;
            if($Page >1)
            {
                $offset=($Page-1)*self::PAGE_LIMIT_NUMBER;  //如果当前页不是第一页，则计算出偏移值   
            }
            
            $UseFields=$this->GetUsedFields(); //获取需要的字段
            $result=$this->field($UseFields)->where($WhereOption)->order($orderBy)->limit($offset.','.self::PAGE_LIMIT_NUMBER)->select();
            //经度和续度不为空则开始许算
            //echo $this->getLastSql();
            
            $ShopSortArray=array();//实体体排序
            $ShopLngLatArray=array();//纬度和经度排序
            if($result)
            { 
               foreach($result as $key => $row)
                {
                    $result[$key]['advertising_map']=D('Tool','LogicModel')->GetImgUrl($row['advertising_map'],'127X97');//实体店推广图
                    $result[$key]['brandMap']=$this->GetShopBrand($row['id']);//实体店品牌列表
                    $result[$key]['supplierSale']=$this->GetSupplierSale($row['supplier_id']);//供应商优惠策略
                    
                    //计算实体的距离
                    $PointB=array();
                    $PointB['lng']=$row['longitude'];//实体店的经度
                    $PointB['lat']=$row['latitude'];//实体店的纬度
                    $distance=$this->GetLngLatDistance($PointA,$PointB);//得到两点之间的距离
                    $result[$key]['distance']=round($distance,2);
                    $result[$key]['mapurl']='/Index/map?id='.$row['id'].'';
                    $ShopSortArray[$key]=$row['shop_sort'];//实体店排序
                    $ShopLngLatArray[$key]=$distance;      //距离排序
                }
                //按ShopSort 纬度和经度排序
                array_multisort($ShopSortArray,SORT_ASC,$ShopLngLatArray,SORT_ASC,$result);
                
                $this->GetCommandShopCache($param,$result);//把缓存数据
            }
            return $result;
    }
    
    
    /**把查询结果放到缓存里面按参数缓存
    *@param array $param查询推荐商家的参数,参数md5
    *@return arrray 返回的结果数据
    */
    private function GetCommandShopCache($param,$data='')
    {
        //如果数据为空则取缓存数据
        //参数http_query
        $CacheKey=http_build_query($param);
        $CacheKey=md5($CacheKey);
        $CacheKey='SHG:JIAJU:CacheCommandShop'.$CacheKey;   //缓存Id
        if(empty($data))
        {
          $ShopData = self::CacheAndData($CacheKey,0);//取缓存数据 
          if($ShopData)
          {
            return $ShopData;  
          }    
        }
        else 
        {
         //把数据缓存起来
          $ret=self::CacheAndData($CacheKey,$data);   //设置Cache
        }
    }
    
    /*
    *搜索实体店如查参数为空则返回所有的A-Z实体
    *按关键词或者品牌搜索
    *Params:shopName
    */
    function ShopSearch($param=array())
    {
        //print_r($param);
        $WhereOption=array();
        $WhereOption['role_id']=self::SHOP_ROLE_ID;
        $WhereOption['status']=self::SHOP_ROLE_STATUS;
        //如果搜索关键词不为空
        $searchKey=0;
        if($param['shopName'] && !empty($param['shopName']))
        {
            $searchKey=trim($param['shopName']);
        }
        
        $ReturnShopData=array();//按首字母返回的数组
        
        //检查Redis数据中是否有数据,如果有数据则用Redis中的数据
        $GetRedisCacheData=$this->GetShopDataFromRedis();
        if($GetRedisCacheData)
        {
            foreach($GetRedisCacheData as $ckey => $cRow)
            {
                
                $cRest=json_decode($cRow,true);
                $ReturnShopData[$ckey]=$cRest;    
            }      
        }
        //如果缓存为空则读数据库里面的数据
        if(empty($ReturnShopData))
        {
            //排序方法按后台设置的顺序
            $orderBy=array('shop_sort'=>'asc');
            
            $UseFields=$this->GetUsedFields(); //获取需要的字段  
            
            //把A-Z所有首字母实体店搜出来
            for($char=65;$char<=90;$char++)
            {
                $FirstLetter=chr($char);
                $WhereOption['first_name_letter']=$FirstLetter;
                //按当前用户所在的城市搜索数据
                $WhereOption['city_id']=self::$CurrentCityId;
                $result=array();
                $result=$this->field($UseFields)->where($WhereOption)->order($orderBy)->select();
                if($result)
                {
                    foreach($result as $key => $row)
                    {
                        $result[$key]['brandMap']=$this->GetShopBrand($row['id']);//实体店品牌列表
                        $result[$key]['supplierSale']=$this->GetSupplierSale($row['supplier_id']);//供应商优惠策略  
                    }
                    //把查出来的数据放到数组中
                    $ReturnShopData[$FirstLetter]=$result;
                    //把数据放到Redis中,以首字母为Key,集合为数据
                    $this->WriteShopDataToRedis($FirstLetter,$result);   
                }
 
            } 
        }
        //关键词不为空则进行关键词查找
        if($searchKey)
        {
           $ReturnShopData=$this->SearchShopKeyWord($ReturnShopData,$searchKey); 
        }
        return $ReturnShopData;
         
    }
    /*实体店按关键词搜索
    *搜索方法：
    *第一步：先搜索实体店名称
    *第二步：搜索品牌
    *关键词和名称分割为数组,搜索关键词在字符串中出面的次数
    *出现的次数和搜索长度取百分比，大于80说明有数据
    */
    function SearchShopKeyWord($dataSource,$searchKey)
    {
        
        foreach($dataSource as $skey => $srow)
        {
            foreach($srow as $rkey => $row)
            {
                $business_name=$row['business_name'];//在商家名称中搜索
                $brandMap=$row['brandMap'];
                //以下是求百分比的具体方法
                $namePercent=$this->GetStringSimilar($searchKey,$business_name);
                
                $dataSource[$skey][$rkey]['percent']=$namePercent;//算出来的百分比
                
                
                //定义吕牌百分比
                $brandPercent=0;
                //如果品牌不为空同时搜一下品牌
                if($brandMap)
                {
                    foreach($brandMap as $brandRow)
                    {
                        $brandPercent=$this->GetStringSimilar($searchKey,$brandRow['brand_name']);
                        if($brandPercent >= 90) break;  //百分比大于80说明搜到了 
                    }
                }
                if($namePercent < 90) //如果百分比大于80说明有搜到的数据
                {
                    if($brandPercent < 90)
                    {
                        unset($dataSource[$skey][$rkey]);   
                    }
                }
                
            } 
        }
        return $dataSource;   
    }
    
    
    /*计算文本的相似度得到百度比*/
    function GetStringSimilar($searchKey,$destString)
    {
        $perCent=0;
        if(!empty($searchKey) && !empty($destString))
        {
            $needle=$this->SplitString($searchKey);//要搜索的字符串
            $maxLen=count($needle);//字符串数组长度
            $haystack=$this->SplitString($destString);
            $DestLen=count($haystack);
            //得到字符串数组长度，按最小的遍历查找
            //如果查找的字符串大于源字符串，则用源字符串查找
            if($maxLen > $DestLen)
            {
                $maxLen=$DestLen;
                $tempArr=$haystack;
                $haystack=$needle;
                $needle=$tempArr;   
            }
            $HashArr=array_flip($haystack);//把数组key和Value交换
            $similar=0;
            foreach($needle as $keyword)
            {
                if(isset($HashArr[$keyword]))
                {
                    $similar++;   
                }        
            }
            $perCent=round(($similar/$maxLen)*100);//文本相似度
            return $perCent; 
        }   
    }
    /*切分字符串得到这符串数组
    /*返回单元个数
    */
    function SplitString($string)
    {
        if($string)
        {
            preg_match_all("/./us",$string,$match); 
            return $match[0]; 
        }
        return array();   
    }
    
    
    /*供应商实体店分公司数据搜索
    *这个接口是给7gz.com用的
    */
    function GetSupplierCompanyEntity($param)
    {
            //获得当前的页数
            $PageNum=1;
            $PageSize=self::PAGE_LIMIT_NUMBER;
            if(isset($param['page']) && !empty($param['page']))
            {
                $PageNum=intval($param['page']);   
            }
            
            if(isset($param['page_size']) && !empty($param['page_size']))
            {
                $PageSize=intval($param['page_size']);   
            }
            $WhereOption=array();
            //$WhereOption['role_id']=self::SUPPLIER_ROLE_ID; //供应商的类型
            //按供应商Id搜索
            $SearchAll=true;//是否查询所有的数据
            if(isset($param['id']) && !empty($param['id']))
            {
                $WhereOption['id']=$param['id'];
                $SearchAll=false;   
            }
            if(isset($param['role_id']) && !empty($param['role_id']))
            {
                $WhereOption['role_id']=$param['role_id'];   
            }
            //按状态搜索供应商
            if(isset($param['status']) && is_numeric($param['status']))
            {
                $WhereOption['status']=$param['status'];
                $SearchAll=false;    
            }
            //按供应商名称搜索
            if(isset($param['business_name']) && !empty($param['business_name']))
            {
                $WhereOption['business_name']=array('like','%'.$param['business_name'].'%'); 
                $SearchAll=false;         
            }
            
            $result=array();
            $fields='';
            
            if($param['role_id']==self::SUPPLIER_ROLE_ID)//供应商的类型
            {
                $fields='id,status,platform_rate,business_name,bank_deposit,bank_province,bank_city,bank_branch,bank_name,bank_num,tel,bank_type,local_station_code';   
            }
            if($param['role_id']==self::SHOP_ROLE_ID)//实体店
            {
                $fields='id,status,name,email,tel,business_name,supplier_id,company_id,city_id,office_address,longitude,latitude,advertising_map,local_station_code';   
            }
            $limitNum='';
            if($SearchAll) //如果不传参数查询，限制条数
            {
                $limitNum='1000';   
            }
            $Offset=0;//数据偏移量
            if($PageNum >1)
            {
                $Offset=($PageNum-1)*$PageSize;
            }
            //得到总数
            $total=$this->where($WhereOption)->count();//得到查询的总数
            $result=$this->field($fields)->where($WhereOption)->order(array('id' =>'desc'))->limit($Offset.','.$PageSize)->select();
            //echo $this->getLastSql();
            $retData=array();
            $retData['total_count']  =$total;//总条数
            $retData['page_size']    =$PageSize;//每页多少个
            $retData['page']         =$PageNum;//第几页
            $retData['list']         =array();//数据集
        $this->setErrCode(1003);
        if($result)
        {
           $retData['list']          =$result;//数据集
           $this->setErrCode(1000);    
        }
        $OutPut['data']=$retData;
        $OutPut['errCode']=$this->getErrCode();
        $OutPut['errMsg']=$this->getErrMsg();
        return $OutPut;
                      
    }
    
    
    /**通过ID得到得到用户信息
    *得到商家分公司实体店的基本信息
    *return array $result 商家的信息
    */
    function GetShopUserInfo($param)
    {
            if(isset($param['id']) && !empty($param['id']))
            {
                $WhereOption['id']=intval($param['id']);  
            }
            if(isset($param['role_id']) && !empty($param['role_id']))
            {
                $WhereOption['role_id']=intval($param['role_id']);   
            }
            //按状态搜索供应商
            if(isset($param['status']) && is_numeric($param['status']))
            {
                $WhereOption['status']=intval($param['status']);  
            } 
            $result=$this->where($WhereOption)->select();
            if($result)
            {
               return $result[0];  
            }    
    }
    
    
    
    
    
    /*把数据写到Redis中
    *Param:
    *key是实体店名称的首字母
    *RowData:是所有实体名称首字母的集合
    */
    function WriteShopDataToRedis($RedisKey,$RowData)
    {
        //按当前所在城市为key保存到redis中
        $hsetRedisKey=self::SHOP_SEARCHDATA_REDISKEYS.'_'.self::$CurrentCityId;
        if($RedisKey && $RowData)
        {
            static $_index=1;
            try
            {  
                $redis = NewRedis::master();
                if($redis->ping())
                {
                    if(!$redis->hExists($hsetRedisKey, $RedisKey))
                    {
                        $ret=$redis->hSet($hsetRedisKey,$RedisKey,json_encode($RowData));
                        //设置keys的过期时间为1小时
                        if($_index==1)
                        {
                            $redis->expire($hsetRedisKey,self::SHOP_REDISKEYS_EXPIRETIME);      
                        }
                        $_index++;
                    }
                }
            }
            catch(Exception $e)
            {
                
            }  
        }
    }
    /*从Redis中取出数据*/
    function GetShopDataFromRedis()
    { 
        $hsetRedisKey=self::SHOP_SEARCHDATA_REDISKEYS.'_'.self::$CurrentCityId;
        try{    
            $redis = NewRedis::master();
            return $redis->hGetAll($hsetRedisKey);
        }
        catch(Exception $e)
        { 
        }       
    }
    
    
    /*通过供应商ID得到对应的优惠方案
    *Param:供应商ID(supplierid)
    */
    function GetSupplierSale($SupplierId)
    {
        $retDat=array();
        if(!empty($SupplierId))
        {
            $result=M("supplierSale")->field('supplier_id,type,rule')->where(array('supplier_id' =>$SupplierId))->find();
            if($result)
            {
                $retDat['supplier_id']=$SupplierId;//供应商Id
                $type=$result['type'];
                $retDat['type']=$type;//优惠类型
                $rule=$result['rule'];//取出优惠规则
                $ruleObj=array();
                if($rule)
                {
                    $ruleObj=json_decode($rule,true);//解析数据
                }
                if($type==1) //满减
                {   
                   //key值是满的值,value是减的值
                   //key值是倒叙
                    /*
                    array(
                        300 => 40   //满300减少40
                        200 => 20   //满200减少20
                        100 => 10,  //满100减少10
                    )*/
                    if($ruleObj)
                    {
                        $retDat['discount']=$ruleObj;  
                    }                   
                }
                elseif($type==2) //折扣
                {
                    $discount=0;
                    if($ruleObj) /*折扣规则array('discount'=>0.80)*/
                    { 
                        $discount=$ruleObj['discount'];
                    }
                    $retDat['discount']=$discount;//优惠折扣     
                }  
            }
            
            return $retDat;
        }   
    }
    
    
    /*通过实体ID得到对应的品牌列表
    *Table:shg_shop_brand
    *Param:int shopId
    *Return:Array()
    */
    function GetShopBrand($shopId)
    {
        $Result=array();
        if(!empty($shopId))
        {
           //获取该实体店的品牌Id
           $Result=M("shopBrand")->field('shop_id,brand_id')->where(array('shop_id' =>$shopId))->select();
           if($Result)
           {
                foreach($Result as $key => $row) 
                {
                    $Result[$key]['brand_name']=$this->GetBrandNameFromBrandId($row['brand_id']);
                }
           }
           return $Result;
        }   
    }
    
    /*得到品牌名称
    *Param:brand_id
    */
    function GetBrandNameFromBrandId($brandId)
    {
        if(!empty($brandId))
        {
            //遍历品牌列表数据得到品牌名称
            foreach(self::$ShopBrandData as $row)
            {
                if($row['id']==$brandId) return $row['name'];   
            }   
        } 
    }
    
    /*得到品牌数据列表
    *Table:shg_brand
    */
    function ShopBrandList()
    {
        $result=M("brand")->field('id,name')->select(); 
        if(self::$ShopBrandData==NULL && $result)  
        {
            self::$ShopBrandData=$result;  
        }
        else 
        {
            $result=self::$ShopBrandData; 
        }
        return $result;
    }
    
    //通过城市Code和城市名称得到对应CityId
    function GetCityId($param)
    {
        $CityData=D('ApiShopCity')->GetCityIdOrCode($param); 
        if($CityData)
        {
            return $CityData['id']; 
        }
        return 0;  
    }
    
    
    
    //得到数据表需要的字段
    private function GetUsedFields()
    {
        $FieldsString="id,supplier_id,company_id,business_name,office_address,advertising_map,longitude,latitude,first_name_letter,shop_sort,city_id";   
        return $FieldsString;
    }
    //将角度换算为弧度
    private function rad($degrees)
    {
        return $degrees * M_PI / 180.0;
    } 
    
    //计算两个经纬度的距度
    //参数：A点的纬度和经度，B点的纬度和经度
    function GetLngLatDistance($PointA,$PointB)
    {
           $lat1=$PointA['lat'];//经度1
           $lng1=$PointA['lng'];//纬度1
           $lat2=$PointB['lat'];//纬度2
           $lng2=$PointB['lng'];//经度2
           //将角度换成弧度
           $radLat1 = $this->rad($lat1);
           $radLat2 = $this->rad($lat2);
           $a       =$radLat1-$radLat2;
           $b       = $this->rad($lng1) - $this->rad($lng2);
           //计算距度
           $s = 2 * asin(sqrt(pow(sin ($a/2),2) +cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
           $s = $s * self::EARTH_RADIUS;
           $s = round ($s * 10000) / 10000;
           return $s;         
    }
    
    /**把数据缓存起来
    *@param string $CacheKey 保存的key
    *@param array $data 要保存的数据(有数据就保存,没有数据就取)
    *@return array
    */
    public static function CacheAndData($CacheKey,$data=0)
    {

            $retData=NULL;
            /*实例化Cache保存和取缓存*/
            try
            {  
                $redis=NewRedis::master();//实例
                $redis->ping();    
            }
            catch(Exception $e)
            {
                $redis=NULL;   
            }
            //判断
            if($redis)
            {
                $retData = $redis->get($CacheKey);//是否有这个key
                if($retData)
                {
                    return json_decode($retData,true);   
                } 
                if(!empty($data))//如果参数有数据则把数据保存起来
                {   
                    $redis->set($CacheKey,json_encode($data),self::CACHE_EXPIRETIME);   //设置Cache     
                }   
            }
    }

}