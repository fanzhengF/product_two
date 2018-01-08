<?php
/**
 *城市搜索页面Model
 *搜索所有实体店所在城市
 *Table:user,city
 */
class ApiShopCityModel extends BaseModel
{
    
    const ALL_CITY_DATA_REDISKEY='SHG:JIAJU:ALLCITYDATA:KEY';
    
    const SEARCH_CITY_DATA_REDISKEY='SHG:JIAJU:SEARCHCITYDATA:KEY';
    
    const SHOP_SEARCH_STEP_NUMBER=500;
    
    const CACHE_EXPIRETIME=3600;//缓存时间
    
    static $ShopCityData=NULL;
    
    protected $tableName='user';
    
    
    /*构造函数*/
    public function __construct() 
    {
        parent::__construct();
        //调整超时时间，和使用内存
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ini_set('memory_limit','1024M');
    }
    
    //搜索实体店所在的城市列表
    //第一步加载所有城市数据
    //第二步加载所有店铺
    //把实体店对应的城市列出来
    function SearchShopCity($param=array())
    {
            if(isset($param['cityName']) && !empty($param['cityName']))
            {
                $searchKey=trim($param['cityName']);   
            }
            $PinYinData=array();
            $GetRedisData=$this->SearchCityDataToRedis();//读取缓存中的数据
            if($GetRedisData)
            { 
                $PinYinData=$GetRedisData;      
            }
            else 
            {
              //缓存为空则读取数据库的数据
                $WhereOption=array();
                $WhereOption['role_id']=ApiShopModel::SHOP_ROLE_ID;//实体店类型
                $WhereOption['status']=ApiShopModel::SHOP_ROLE_STATUS;//启用状态
                //排序方法按后台设置的顺序
                $orderBy=array('shop_sort'=>'asc');
                $GetCountNumber= $this->where($WhereOption)->count();// 查询满足要求的总记录数
                $UserCityFields=$this->GetCityUsedFields();
                $totalNumber=0;//初始化总数
                //顺序读取一定数量的数据
                while($totalNumber <= $GetCountNumber)
                {    
                    $result=array();
                    $result=$this->field($UserCityFields)->where($WhereOption)->order($orderBy)->limit($totalNumber.','.self::SHOP_SEARCH_STEP_NUMBER)->select();
                    if($result)
                    {  
                        foreach($result as $skey => $srow)
                        {
                             $cityid=$srow['city_id']; 
                             $param=array();
                             $param['id']=$cityid;
                             $cityInfo=$this->GetCityIdOrCode($param);
                             $cityCode=$cityInfo['code'];
                             $cityName=$cityInfo['name'];
                             $fistUc=strtoupper($cityCode[0]);
                             if(!empty($fistUc))
                             {
                                 if(!isset($PinYinData[$fistUc][$cityid]))
                                 {
                                     $PinYinData[$fistUc][$cityid]=array(); 
                                 }
                                  $PinYinData[$fistUc][$cityid]=$cityInfo; 
                             }      
                        }   
                    }
                    //步长增加
                    $totalNumber+=self::SHOP_SEARCH_STEP_NUMBER;  
                }
                $this->SearchCityDataToRedis($PinYinData); 
            }
            //如果搜索城市关键词不为空
            if($searchKey)
            {
                //返回搜索结果
                return $this->SearchCityFromKeyWord($PinYinData,$searchKey);   
            }
            //返回首字A-Z所有的数据
            ksort($PinYinData);
            $this->SortCityDataById($PinYinData);
            return   $PinYinData;     
            
    }
    
    
    /*搜索城市关键词数据*/
    function SearchCityFromKeyWord(&$cityData,$searchKey)
    {
        $retList=array();
        if($cityData && $searchKey)
        {
            $keyLen=strlen($searchKey);//得到搜索字符串长度。
            if($keyLen==1)
            {
                //如果为一个字符则判断是否存在这样的数据
                $searchKey=strtoupper($searchKey);
                if(isset($cityData[$searchKey]))
                {
                   return $cityData[$searchKey]; 
                }   
            }
            //遍历数据搜索关键词
            foreach($cityData as $itemRow)
            {
                foreach($itemRow as $sRow)
                {
                    if(preg_match("/[a-zA-Z]+/",$searchKey))//如果全部都是字母
                    {
                        $hackString=$sRow['code'];   
                    }
                    else 
                    {
                        $hackString=$sRow['name'];
                    }
                    $similar=D("ApiShop")->GetStringSimilar($searchKey,$hackString);
                    if($similar > 90)
                    {
                        $retList[]= $sRow;  
                    }
                     
                } 
            }
            return  $retList; 
        }   
    }
    
    /*读取数据表得到所有的城市列表
    /*Table:shg_city
    */
    function GetAllCityData()
    {
            //连接Redis
            try 
            {
                $redis = NewRedis::master();
                $redis->ping();  
            } 
           catch (Exception $e) 
           { 
                $redis=NULL;       
           }
           $GetRedisCityData=NULL;
           if($redis)
           {
                $GetRedisCityData=$redis->get(self::ALL_CITY_DATA_REDISKEY);
           }
            //如是reids中的数据不为空则用redis中的数据
            if($GetRedisCityData)
            {
                $CityData=json_decode($GetRedisCityData,true);            
            }
            else 
            {
                $whereOptions['status']=1;//城市在线状态
                $whereOptions['pid']=array('gt','0');//城市在线状态
                $CityData=M("city")->field('id,code,name')->where($whereOptions)->order('id asc')->select();
                if($CityData)
                {
                    foreach($CityData as $cKey => $cRow)
                    {
                        $CityData[$cKey]['name']=self::ReplaceFilterName($cRow['name']);   
                    }
                    
                    if($redis)
                    {
                        $redis->setex(self::ALL_CITY_DATA_REDISKEY, self::CACHE_EXPIRETIME, json_encode($CityData)); // sets key → value, with 1h TTL.
                    }
                }
            }
            return $CityData;
    }
    
    /*通过CityID或CityCode得到对应的城市名称
    *array('id' =>1)通过Id获得 array('code' => 'bj')通过code获得
    *返回array('id' =>,'code'=>,'name'=>)
    */
    function GetCityIdOrCode($param)
    {
        if(self::$ShopCityData==NULL)
        {
            self::$ShopCityData=$this->GetAllCityData();
        }
        if(self::$ShopCityData)
        {
            foreach(self::$ShopCityData as $Row)
            {
                if($Row['id'] && $Row['id']==$param['id']) return $Row;
                if($Row['code'] && $Row['code']==$param['code']) return $Row; 
                if($Row['name'])
                {
                    $similar=D("ApiShop")->GetStringSimilar($param['name'],$Row['name']);
                    if($similar >= 90)
                    {
                        return $Row;    
                    }
                }    
            }   
        }   
    }
    
    /*把已经排序好的统计数据放到Redis中*/
    function SearchCityDataToRedis($data=array())
    {
            $cityData=array();
            try 
            {
                $redis = NewRedis::master();  
                $redis->ping(); 
            } 
            catch (Exception $e) 
            { 
                $redis=NULL;       
            }
            if($redis)
            {
                //先判断这个值是否存在，如果不存在则添加数据
                $GetRedisCityData=$redis->get(self::SEARCH_CITY_DATA_REDISKEY);
               if($redis && empty($GetRedisCityData) && $data)
               {
                    $ret=$redis->setex(self::SEARCH_CITY_DATA_REDISKEY, self::CACHE_EXPIRETIME, json_encode($data));
               }
               else 
               {
                   $cityData=json_decode($GetRedisCityData,true);   
               } 
           }
           return $cityData; 
             
           
    }
    
    
    
    /*按盟主键排一下序*/
    private function SortCityDataById(&$cityArr)
    {
        foreach($cityArr as $skey => $sRow)   
        {
            ksort($cityArr[$skey]);   
        }
    }
   //得到数据表需要的字段
    private function GetCityUsedFields()
    {
        $FieldsString="id,supplier_id,company_id,shop_sort,city_id";   
        return $FieldsString;
    }
    
    /***把城市中的省市县区去掉
    *param string $str 要过滤的名称
    *return string $str 过滤后的名称
    */
    public static function ReplaceFilterName($str)
    {
        $str=str_replace(array('省','市','县','区'),'',$str); 
        return $str;       
    }
    
    
    
    
    
    
    
    
    
    
    
    
}