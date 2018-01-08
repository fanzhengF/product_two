<?php
/*
 * 工具model
 */
class ToolLogicModel 
{
    const API_AMAP_KEY='949c951a447bb6c8bf6125a6217e67ac';
    const COOKIE_PREFIX='cookie_';
    //请求高德地图得到经度和纬度所在的城市
    function GetLngLatLocation($lng,$lat)
    {
        if($lng && $lat)
        {
            /*location经纬度坐标规则： 
            最多支持20个坐标点。
            多个点之间用"|"分割。
            经度在前，纬度在后，
            经纬度间以“，”分割，
            经纬度小数点后不得超过6位
            */
            //get($url, $params, $timeout = 60, $json = false)
             $ReqUrl="http://restapi.amap.com/v3/geocode/regeo";
            	$param =array();
            	$param['output']='json';
            	$param['location'] = $lng.','.$lat;
            	$param['key'] = self::API_AMAP_KEY;
             $req=Request::get($ReqUrl,$param,10,true);
             if($req['status']==1)//请求成功
             {
                $city_info = $req['regeocode']['addressComponent']['city'];
            	   if(is_string($city_info))
            	   {
            	    		$city = $city_info;
            	   }
            	   else 
            	   {
            	    		$city = $city_info[0];
            	   }
            	   if(!$city)
            	   {
            	    		$city = trim($req['regeocode']['addressComponent']['province']);
            	   }
	    	          $city = str_replace('市','', $city);
	    	          $city=trim($city);
             }
             return $city;
   
        }    
    }
    
    
    /*通过城市名得到对应的经度和纬度*/
    function GetLngLatFromCityName($cityName)
    {
        //高德地图说明接口地址:http://lbs.amap.com/api/webservice/guide/api/district/#district
        $param=array();
        $param['key']           =self::API_AMAP_KEY;
        $param['keywords']      =$cityName;
        $param['subdistrict']   ='0';
        $param['showbiz']       ='false';
        $param['extensions']    ='base';
        $param['output']        ='JSON';
        $ReqUrl='http://restapi.amap.com/v3/config/district';
        $req=Request::get($ReqUrl,$param,20,true);
        if($req['status']==1)
        {
            $districts=$req['districts'];
            if($districts)
            {
                foreach($districts as $cityArr)
                {
                    $retName   =$cityArr['name'];//城市名称
                    $retCenter =$cityArr['center'];//经度和纬度
                    list($lng,$lat)=explode(',',$retCenter);
                    $retArr=array();
                    $retArr['cityName'] =$retName;//城市名
                    $retArr['lng']      =$lng;//经度
                    $retArr['lat']      =$lat;//纬度
                    return $retArr;
                }
            }
        }
        
    }
    
    /*判断是不是微信登录
    * @return boolean 
    */
    function CheckWeiXinAgent()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];//获取浏览器信息
        return preg_match('/MicroMessenger/',$user_agent);  
    }
    
    
    
    
    /*设置Cookie*/
    function SetCityCookie($array)
    {
        if($array)
        {
            foreach($array as $cookieName => $cookieVal)
            {
                $cookieVal=ApiShopCityModel::ReplaceFilterName($cookieVal);//过滤省市县区等
                cookie(self::COOKIE_PREFIX.$cookieName,$cookieVal);     
            }
       }   
    } 
    
    /*获得Cookie*/
    function GetCookieValue($cookname)
    {
        if(preg_match('/'.self::COOKIE_PREFIX.'/',$cookname))
        {
            $value = cookie($cookname);   
        } 
        else 
        {
             $value = cookie(self::COOKIE_PREFIX.$cookname); 
        }
        return $value; 
    }
    
    
    /*删除所有Cookie*/
    function DeleteCookie()
    {
        $allCookie = $_COOKIE;
        foreach($allCookie as $cKname =>$ckVal)
        {
            cookie($cKname,null);     
        }
           
    }
    
    
    /*得到图片的真实路径*/
    function GetImgUrl($imgUrl,$size='')
    {
        if(!empty($imgUrl))
        {
            $pic = explode('.',$imgUrl);
            $imgSrc=D('Photo', 'LogicModel')->url($pic[0],$pic[1], $size);
            if($imgSrc)
            {
                return $imgSrc;
            }
        } 
        return 'http://static.jiaju.com/jiaju/com/m7gz/assets/images/sg-order/sg_sj.jpg';  
    }
    
    
    
    
    
    
    
     
     
    
    
}
