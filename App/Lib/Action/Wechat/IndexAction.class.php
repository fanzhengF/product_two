<?php
/**
 *  获取微信平台信息页面
 *  guo.hao 2015-11-17
 */
class IndexAction extends WeChatAction {

    public function __construct()
    {
        parent::__construct();
        $this->_setMPLejuID(144769);    //抢工长服务平台
        $this->callBackUrl=I('returl'); 
        if(empty($this->callBackUrl))  $this->callBackUrl=C('MOBILE_SITE_DOMAIN');
    }
    
    public function getUserInfo()
    {
        //die;
        echo '<p>======= 访问之前 =========</p>';
        dump($_GET);
        dump($_COOKIE);
        
        
       
        $this->_doWeChatLogin();

        echo '<p>======= 访问之前 =========</p>';
        dump($_GET);
        dump($_COOKIE);

       
       die();
        $this->_weixin_js_sdk(C('WECHAT_APP_ID1'),C('WECHAT_APP_SECRET1'));


        die;
        $target = 'http://m.leju.com/api/weixin/account/userinfo.json';
        $data['timestamp'] = time();
        $data['appkey'] = $this->_getMpLejuAppKey();
        $data['openid'] = $_SESSION['wechat_open_id'];
        $token = '9280a12splsdne730743';    //我方提供的token串
        ksort($data);
        $tmpstr = http_build_query($data);
        $data['sign'] = md5($tmpstr . $token);
        $target .= http_build_query($data);
        $res = file_get_contents($target);
        var_dump($res);
    }


    public function get_wx_open_id() 
    {

        $this->_setMPLejuID(139488);
        if($this->callBackUrl)
        {
            $this->_setRetURL($this->callBackUrl);
        }
        //echo '<p>======= 访问之前 =========</p>';
        //dump($_GET);
        //dump($_COOKIE);
        //print_r($this);
        //die();
        $this->_doWeChatLogin();

        //echo '<p>======= 访问之前 =========</p>';
        //dump($_GET);
        //dump($_COOKIE);
    }

}