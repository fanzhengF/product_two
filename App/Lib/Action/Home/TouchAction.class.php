<?php
/**
 * 前端获取经度和纬度
 */
class TouchAction extends BaseAction
{
    const NEW_CITY = 'new_city';
    const OLD_CITY = 'old_city';
    const MOBILE_EXPIRE = 15552000;

    public $cookie_domain='';
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 前端掉此接口，传入经度和纬度
     * @param double $x 纬度
     * @param double $y 经度
     * @return json
     */
    public function getLoction()
    {
        	$expire = time()+self::MOBILE_EXPIRE;
        	$lat = I('param.x');//纬度
        	$lng = I('param.y');//经度
        	if(!$_GET['x'] && !$_GET['y'])
        	{
    		        $this->jsonReturn(0,"获取位置失败");;
    	    }
    	    //北京的城市的code
    	   $location_city = ApiShopModel::DEFAULT_CITYCOD;
    	   $location_city_name = '北京';
    	   //实例工具类
    	   $ToolModel=D('Tool','LogicModel');
    	   //获取经度纬度所在的城市
    	   $CityName=$ToolModel->GetLngLatLocation($lng,$lat);
    	   $retArr=array();
        $retArr['lng']      =$lng;//经度
        $retArr['lat']      =$lat;//纬度     
    	   if(empty($CityName))
        {
            
            $CityName=$location_city_name;//如果为空则默认北京      
        }
        //取得经度和纬度
        $cityRow=D('ApiShopCity')->GetCityIdOrCode(array('name' => $CityName));
        if($cityRow)
        {
            $location_city=$cityRow['code'];
            $retArr['cityId']=$cityRow['id'];
            $retArr['cityName'] =$CityName;//城市名
            $location_city_name=$CityName;
        }
        else 
        {
            $retArr['cityId']=ApiShopModel::DEFAULT_CITYID;
            $retArr['cityName'] =$CityName;//城市名     
        }
        $ToolModel->SetCityCookie($retArr);
        	setcookie('new_cityname', $location_city_name, $expire, '/', $this->cookie_domain);
        	setcookie(self::NEW_CITY, $location_city, $expire, '/', $this->cookie_domain);
        	setcookie(self::OLD_CITY, $location_city, $expire, '/', $this->cookie_domain);
        	$this->jsonReturn(1,array("city_code"=>$location_city,"city_name"=>$location_city_name,"link"=>U('ChangeCity/change?code='.$location_city)));
    }
    
     /**
     * 返回json结果
     */
    protected function jsonReturn($errorNum = 0, $data = array(), $msg = '') {
        $message['result'] = $errorNum;
        $message['data'] = $data;
        $message['message'] = $msg;
        die($this->ajaxReturn($message, 'json'));
    }

  

}