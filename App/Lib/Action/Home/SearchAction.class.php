<?php
/**
*搜索实体店商家
*数据表：shg_user
*/
class SearchAction extends BaseAction
{
    
    /**初始化*/
    function _initialize()
    {
        $this->assign('WebTitle','搜索商家-抢工长闪购'); //网页标题 
    }
    /*
    *把A-Z实体店列出来
    */
    function index()
    { 
        $ShopSearchModel=D("ApiShop");
        $result=$ShopSearchModel->ShopSearch(); 
        $this->assign("result",$result);
        $chars=range(65,90);
        $chars=array_map('self::IntToChar',$chars);
        $this->assign('Chars',$chars);
        $this->display();  
    }
    
    /*搜索结果页面
    *Param:shopName搜索的关键词
    */
    function search()
    {
        $ShopSearchModel=D("ApiShop");
        $shopName=I('shopName');
        $result=$ShopSearchModel->ShopSearch(I('get.'));
        //过滤空值和排序
        $reSortResult=$this->CheckResultIsEmpty($result); 
        //如果是AJAx请求则
        if(IS_AJAX)
        {
            $OutPut['data']=array();
            $ShopSearchModel->setErrCode(1003);
            if($reSortResult)
            {
                $OutPut['data']=$reSortResult; 
                $ShopSearchModel->setErrCode(1000); 
            }
            $OutPut['errCode']=$ShopSearchModel->getErrCode(); 
            $OutPut['errMsg']=$ShopSearchModel->getErrMsg();
            $this->echoJson($OutPut);  
        }
        
       
        $this->assign('shopName',$shopName);
        $this->assign("result",$reSortResult);
        $this->display();         
    }
    
    
    /*判断数组是否为空按相似度和序号排序
    * @param array $result 从数据库里面读取的数据
    * @return array 排序好只显示名称和url等需要的数据
    */
    function CheckResultIsEmpty(&$result)
    {
        $SortArr=array();
        $PercentArr=array();
        //重新排序的数据
        $ReSortResult=array();
        $index=0;
        foreach($result as $key => $item)
        {
            if(!$item)
            {
                 unset($result[$key]); 
                 continue;
            }
            foreach($item as $rkey => $row)
            {
                $row['url']='/Pay/?id='.$row['id'];//给前端需要的地址
                $this->FilterNothingField($row);
                $ReSortResult[$index]=$row;
                if(isset($row['percent']))
                {
                    $PercentArr[$index]=$row['percent'];   
                }
                $SortArr[$index]=$row['shop_sort'];  
                $index++;   
            }
            
        }
        //如果搜索的文字相似度大
        if($PercentArr)
        {
            array_multisort($PercentArr,SORT_DESC,$SortArr,SORT_ASC,$ReSortResult);  
        }
        else 
        {
            array_multisort($SortArr,SORT_ASC,$ReSortResult);  
        }
        return $ReSortResult;
    }
    
    
    /**搜索首页展示A-Z页面需要过滤是不是当前城市的数据
    *@param array $result 查询出来的数据
    */
    function GetCurrentCityData(&$result)
    {
        //取当前城市的Id
        $curCityId=$_COOKIE['cookie_cityId'];
        if(empty($curCityId)) $curCityId=self::DEFAULT_CITYID;
        foreach($result as $retkey => $itemRow)
        {
            foreach($itemRow as $itKey => $row)
            {
                if($row['city_id']!=$curCityId)
                { 
                    unset($result[$retkey][$itKey]); 
                    continue;//如果不是当前城市的数据则跳过
                }    
            }
        }   
    }

    
    //把数字转换为字母
    static function IntToChar($char)
    {
        return chr($char);   
    }
    
    /**
    *@把没有用的字段给过滤掉
    *@array $item 
    */
    function FilterNothingField(&$item)
    {
        $allowArray=array('id' => 1,'business_name' =>1,'percent' =>1,'shop_sort' =>1,'url' =>1);
        foreach($item as $itkey => $itval)
        {
           if(!isset($allowArray[$itkey])) unset($item[$itkey]);   
        }   
    }
    
    
    
      
}