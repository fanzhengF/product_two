<?php

class PublicAction extends AdminAction {


    /**
      +----------------------------------------------------------
     * 初始化
      +----------------------------------------------------------
     */
    public function _initialize() {
        header("Content-Type:text/html; charset=utf-8");
        $loginMarked = C("TOKEN");
        $this->loginMarked = md5($loginMarked['admin_marked']);
        $systemConfig['SITE_INFO'] = C('SITE_INFO');
        $this->assign("site", $systemConfig);
    }

    public function index() {
        
        //phpinfo();exit;
        if (IS_POST) {
           
            $this->checkToken();
          
            $returnLoginInfo = D("AdminPublic")->auth();
            //生成认证条件
            if ($returnLoginInfo['status'] == 1) {
                $map = array();
                // 支持使用绑定帐号登录
                $map['name'] = $this->_post('tel');
                //status > 0 为了增加删除功能
                $map['status'] = array('gt',0);

                import('ORG.Util.RBAC');
                $authInfo = RBAC::authenticate($map);
               // print_r($authInfo);exit;
                $_SESSION[C('USER_AUTH_KEY')] = $authInfo['id'];
                $_SESSION['name'] = $authInfo['name'];

                if ('admin'.$authInfo['name'] == C('ADMIN_AUTH_KEY')) {
                   
                    $_SESSION[C('ADMIN_AUTH_KEY')] = true;
                }

                // 缓存访问权限
                RBAC::saveAccessList();
            }
            $this->ajaxReturn($returnLoginInfo);
        } else {

            if (isset($_COOKIE[$this->loginMarked])) {
                $this->redirect("Index/index");
            }

            $this->display("login");
        }
    }

    public function loginOut() {
        setcookie("$this->loginMarked", NULL, -3600, "/");
        unset($_SESSION["$this->loginMarked"], $_COOKIE["$this->loginMarked"]);
        if (isset($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION);

        }
        session_destroy();
        $this->redirect("Index/index");
    }

    public function verify_code() {
        $w = isset($_GET['w']) ? (int) $_GET['w'] : 50;
        $h = isset($_GET['h']) ? (int) $_GET['h'] : 30;
        ob_end_clean();
        import("ORG.Util.Image");
        Image::buildImageVerify(4, 1, 'png', $w, $h);
    }
}