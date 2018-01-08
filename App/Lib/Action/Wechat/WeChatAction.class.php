<?php
/**
 *  微信 基类
 *  guo.hao 2015-11-17
 */
class WeChatAction extends BaseAction{
    const CURRENT_CITY_COOKIE_KEY = 'current_city'; //当前城市cookie key
    const COOKIE_EXPIRE = 15552000;

    protected $_current_city;
    private $_mp_leju_id;
    private $_wechat_open_id;
    private $_wechat_returl;

    public function __construct(){
        parent::__construct();
        // 定位城市
        $this->_current_city = $this->getCity();
        $this->_wechat_returl = '';
    }

    //设置营销平台IP
    protected function _setMPLejuID($id){
        $this->_mp_leju_id = $id;
    }

    protected function _setRetURL($url){
        $this->_wechat_returl = $url;
    }

    //获取mp leju平台app key
    protected function _getMpLejuAppKey(){
        $map = array('0'=>'G','1'=>'N','2'=>'A','3'=> 'Q', '4'=> 'B', '5'=> '2', '6'=> '9', '7'=>'a', '8'=> 'f', '9'=> 'F');    //对应表
        $app_key = '';
        foreach(explode('',$this->_mp_leju_id) as $k=>$v)
            $app_key .= $map[$v];
        return $app_key;
    }

    //微信登录，获取openid
    protected function _doWeChatLogin(){
        if(array_key_exists('openid',$_GET) && !empty($_GET['openid'])){
            $this->_setWeChatOpenID($_GET['openid']);
        }else{
            $target = "http://m.leju.com/?site=api&ctl=weixin_oauth&act=redirect2&";
            $base_route = $_GET['_URL_'][0].'/'.$_GET['_URL_'][1].'/'.$_GET['_URL_'][2];
            $url_param = $_GET;
            unset($url_param['_URL_']);
            $data['wx_id'] = $this->_mp_leju_id;	//微信账号ID
            if ($this->_wechat_returl != '') {
                $data['returl'] = U($this->_wechat_returl,'',false,false,true);   //授权跳转地址 需urlencode 域名需提供我方待配置后方能生效
            } else {
                $data['returl'] = U($base_route,$url_param,false,false,true);   //授权跳转地址 需urlencode 域名需提供我方待配置后方能生效
            }
            
            $target .= http_build_query($data);
            //echo '<p>======= url参数 =========</p>';
            //dump($data);
            //echo '<p>======= 跳转url =========</p>';
            //dump($target);
            header('Location: '.$target);
            //$result = file_get_contents($target);
            //echo '<p>======= 跳转url =========</p>';
            //dump($result);
            //die;
        }
    }

    //设置open id
    protected function _setWeChatOpenID($open_id,$key = 'wei_chat_open_id'){
        //$res =  myCrypt($open_id,$key);
        setcookie($key, $open_id, time() + self::COOKIE_EXPIRE, '/', C('COOKIE_DOMAIN'));
        $this->_wechat_open_id = $open_id;
    }

    //获取open id
    protected function _getWeChatOpenID($key = 'wei_chat_open_id'){
        //$res =  myCrypt($this->_wechat_open_id,$key,'decode');
        return $this->_wechat_open_id = $_COOKIE[$key];
    }

    //渲染js sdk配置
    protected function _weixin_js_sdk($app_id,$app_secret){
        $jssdk = new WeixinJSSDKService($app_id, $app_secret);
        $jsapiParams = $jssdk->GetSignPackage();
        $this->assign ('jsapiParams', $jsapiParams);
        //echo '<p>======= js sdk =========</p>';
        //dump($jsapiParams);
        $public_path =  __ROOT__.'/Public';// 站点公共目录
        $this->assign('public_path', $public_path);
    }

    //404
    public function  page404(){
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
        $this->display("Common/404");
        exit;
    }

    // 返回json结果
    protected function jsonReturn($errorNum = 0, $data = array(), $msg = '') {
        $message['result'] = $errorNum;
        $message['data'] = $data;
        $message['message'] = $msg;
        $this->ajaxReturn($message, 'json');
    }

    //城市判断
    public function getCity(){
        $now_city = $_GET['current_city'];
        $expire = time() + self::COOKIE_EXPIRE;
        if(!$now_city){
            $now_city = cookie(self::CURRENT_CITY_COOKIE_KEY);
            if (!$now_city)
                return 'bj';
        }else{
            setcookie(self::CURRENT_CITY_COOKIE_KEY, $now_city, $expire, '/', C('COOKIE_DOMAIN'));
        }
        $cityinfo = M("City")->where(array('code'=>$now_city,'status'=>1))->find();
        if(!$cityinfo){
            $now_city = 'bj';
            setcookie(self::CURRENT_CITY_COOKIE_KEY, $now_city, $expire, '/', C('COOKIE_DOMAIN'));
        }
        return $now_city;
    }
}