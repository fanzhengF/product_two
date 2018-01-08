<?php
/*
实体店城市列表切换
数据表：shg_user,shg_city
返回:
*/
class ChangeCityAction extends BaseAction
{
    
    /**初始化*/
    function _initialize()
    {
        $this->assign('WebTitle','切换城市-xxx'); //网页标题 
        //热门城市
        $HotCity=array( 
                        array('cityName' => '北京','cityId'=>36),
                        array('cityName' => '天津','cityId'=>37),
                        array('cityName' => '武汉','cityId'=>217),
                        array('cityName' => '上海','cityId'=>38)
                    );
       $this->assign('HotCity',$HotCity);
    }
    /*城市切换显示所有的城市
    */
    function index()
    {
        
        $ShopCityModel=D("ApiShopCity");
        $ShopCityData=$ShopCityModel->SearchShopCity(I("get."));
        if(IS_AJAX)
        { 
            $OutPut['data']=array();
            $ShopCityModel->setErrCode(1003);
            if($ShopCityData)
            {
                $OutPut['data']=$ShopCityData;
                $ShopCityModel->setErrCode(1000); 
            }
            $OutPut['errCode']=$ShopCityModel->getErrCode(); 
            $OutPut['errMsg']=$ShopCityModel->getErrMsg();
            $this->echoJson($OutPut);  
        }
        //检查Cookie是否存在
        $ToolModel=D('Tool','LogicModel');
        $cityName=$ToolModel->GetCookieValue('cityName');
        $cityId=$ToolModel->GetCookieValue('cityId');
        if(!empty($cityName) || !empty($cityId))
        {
            if($cityName)
            {
                $param['name']=trim($cityName);    
            }
            if($cityId)
            {
                $param['id']=$cityId;
                
            }
            $cityRow=D('ApiShopCity')->GetCityIdOrCode($param);
            $this->assign('cityRow',$cityRow);  
        }

        $this->assign('result',$ShopCityData);
        $this->display('changecity');
    }
    
    
    /**通过code切换城市
    *@通过城市Code得到城市名称,通过城市名称得到经度和纬度
    *@把经度和纬度，城市ID写到cookie里面
    *@link:/ChangeCity/change/code/bj.html
    *@param string $code 城市的缩写如北京 bj
    *@return /Index/shop返回到首页
    */
    function change()
    {
        $code=I('code');
        if(!empty($code))
        {
            $param['code']=$code;
            if(is_numeric($code))
            {
                $param['id']=$code;   
            }
            $cityRow=D('ApiShopCity')->GetCityIdOrCode($param);
            $toolModel=D('Tool','LogicModel');
            if($cityRow)
            {
                $retArr=$toolModel->GetLngLatFromCityName($cityRow['name']);
                
                //获取城市经度纬度成功
                if($retArr)
                {
                    //设置Cookie
                   $retArr['cityId']=$cityRow['id'];
                   $toolModel->SetCityCookie($retArr);
                   redirect('/Index/shop');
                }
                else 
                {
                    //失败删除cookie提示定位失败
                    $toolModel->DeleteCookie();
                    redirect('/ChangeCity/index');  
                }    
            } 
    
        }
        
        
    }
    
    
    
      
}