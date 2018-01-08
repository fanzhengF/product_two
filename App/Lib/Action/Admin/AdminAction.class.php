<?php
/**
 * Admin 基类
 * @author jiazhu@jiaju.com
 * @version 2013-10-24
 */
import('ORG.Util.RBAC');
import('UserLogic');

class AdminAction extends BaseAction
{
	public $loginMarked;
	protected $sub_menu_parent = '';		//定义子菜单归属，父类菜单
	protected $access_list = '';			//用户权限数组

    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
      +----------------------------------------------------------
     */
    public function _initialize() {
        header("Content-Type:text/html; charset=utf-8");

        $systemConfig['TOKEN'] = C('TOKEN');
        $systemConfig['SITE_INFO'] = C('SITE_INFO');
        $this->loginMarked = md5($systemConfig['TOKEN']['admin_marked']);
        $this->checkLogin();
        //var_dump($_COOKIE,$_SESSION);exit();
        // 用户权限检查
        if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {

            if (!RBAC::AccessDecision('Admin') ) {
                //检查认证识别号
                if (!$_SESSION [C('USER_AUTH_KEY')]) {
                    //跳转到认证网关
                    redirect(C('USER_AUTH_GATEWAY'));
//                    redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
                }
                // 没有权限 抛出错误
                if (C('RBAC_ERROR_PAGE')) {
                    // 定义权限错误页面
                    redirect(C('RBAC_ERROR_PAGE'));
                } else {
                    if (C('GUEST_AUTH_ON')) {
                        $this->assign('jumpUrl', C('USER_AUTH_GATEWAY'));
                    }
                    $this->error(L('_VALID_ACCESS_'));
                }
            }
        }


        import("ORG.SensitiveFilter"); //载入敏感词过滤
        if ($_POST) {
            foreach ($_POST as $k=>$v){
                foreach ($v as $k1=>$v1){
                    $_POST[$k][$k1] = SensitiveFilter::filter($v1) === false ? 含有非法词汇 : $v1 ;
                }
            }
        }
        if ($_GET) {
            foreach ($_GET as $k=>$v){
                foreach ($v as $k1=>$v1){
                    $_GET[$k][$k1] = SensitiveFilter::filter($v1) === false ? 含有非法词汇 : $v1 ;
                }
            }
        }


        $this->my_info = $_SESSION['my_info'];
        $this->assign("menu", $this->show_menu());
        $this->assign("sub_menu", $this->show_sub_menu());
        $this->assign("my_info", $this->my_info);
        $this->assign("is_admin", $_SESSION[C('ADMIN_AUTH_KEY')]);
        $this->assign("site", $systemConfig);


    }


    //上传
    protected function upload ($file='',$path = 'images',$type = 'img'){
        $tmp_file = $file ['tmp_name'];
        $file_types = explode ( ".", $file ['name'] );
        $file_type = $file_types [count ( $file_types ) - 1];
/*
        if ($type =='img') {
            if ($file_type != 'png' && $file_type != 'jpg' && $file_type != 'gif' && $file_type != 'jpeg') {
                $this->error ( '图片格式错误！' );
            }
        }else if ($type =='file') {
            if ($file_type != 'doc' && $file_type != 'docx' ) {
                $this->error ( '文件格式错误！' );
            }
        }*/
        $savePath = SITE_PATH . '/public/upfile/'.$path.'/';
        $str = date ( 'Ymdhis' );
        $file_name = $str . "." . $file_type;

        if (! copy ( $tmp_file, $savePath . $file_name )) {
            //$this->error ( '上传失败' );
        }
        return $file_name;
    }


    public function checkLogin() {
        if (isset($_COOKIE[$this->loginMarked])) {
            $cookie = explode("_", $_COOKIE[$this->loginMarked]);
            $timeout = C("TOKEN");
            if (time() > (end($cookie) + $timeout['admin_timeout'])) {
                setcookie("$this->loginMarked", NULL, -3600, "/");
                unset($_SESSION[$this->loginMarked], $_COOKIE[$this->loginMarked]);
                $this->error("登录超时，请重新登录", U("Public/index"));
            } else {
                if ($cookie[0] == $_SESSION[$this->loginMarked]) {
                    setcookie("$this->loginMarked", $cookie[0] . "_" . time(), 0, "/");
                } else {
                    setcookie("$this->loginMarked", NULL, -3600, "/");
                    unset($_SESSION[$this->loginMarked], $_COOKIE[$this->loginMarked]);
                    $this->error("帐号异常，请重新登录", U("Public/index"));
                }
            }
        } else {
            $this->redirect("Public/index");
        }
        return TRUE;
    }

    /**
      +----------------------------------------------------------
     * 验证token信息
      +----------------------------------------------------------
     */
    protected function checkToken() {
       
        if (IS_POST) {
           
            if (!M("AdminPublic")->autoCheckToken($_POST)) {
                die(json_encode(array('status' => 0, 'info' => '令牌验证失败')));
            }
            unset($_POST[C("TOKEN_NAME")]);
        }
    }



    /**
      +----------------------------------------------------------
     * 显示一级菜单
      +----------------------------------------------------------
     */
    private function show_menu() {
        $cache = C('admin_big_menu');
        $count = count($cache);
        $this->sub_menu_parent = $this->get_menu_module();
        $module_name = $this->sub_menu_parent?$this->sub_menu_parent:MODULE_NAME;
        $i = 1;
        $menu = "";

         import('ORG.Util.RBAC');
         $this->access_list = RBAC::getAccessList($_SESSION["authId"]);

        foreach ($cache as $url => $name) {
            $check = "Admin.".str_replace("/",".",$url);
            // if(!UserLogicModel::getAuth($check)){
            //     continue;
            // }
            if ($i == 1) {
                $css = $url == $module_name || !$cache[$module_name] ? "fisrt_current" : "fisrt";
                $menu.='<li class="' . $css . '"><span><a href="' . U($url ) . '">' . $name . '</a></span></li>';
            } else if ($i == $count) {
                $css = $url == $module_name ? "end_current" : "end";
                $menu.='<li class="' . $css . '"><span><a href="' . U($url ) . '">' . $name . '</a></span></li>';
            } else {
                $css = $url == $module_name ? "current" : "";
                $menu.='<li class="' . $css . '"><span><a href="' . U($url ) . '">' . $name . '</a></span></li>';
            }
            $i++;

        }

        return $menu;
    }

    /*
     * 获得所属菜单模块
     */
    private function get_menu_module()
    {
    	$sub_menu = C('admin_sub_menu');
    	$tmp_key = '';
    	foreach ($sub_menu as $key => $val)
    	{
    		foreach ($val as $url => $title)
    		{
                // if($_GET['debug'] and !is_array($title)){var_dump($title, $title['name'], empty($title['name']));}
	    		if (!is_array($title))
	    		{
	    			if (!stripos($url, '/'))
	    			{
	    				$str = $key.'/'.$url;
	    			}else
	    			{
	    				if (stripos($url, '?'))
	    				{
	    					$str = substr($url, 0,stripos($url, '?'));
	    				}else
	    				{
	    					$str = $url;
	    				}
	    			}
                    // if($_GET['debug']){var_dump($str, 1, strtolower(MODULE_NAME.'/'.ACTION_NAME));}
	    			if (strtolower($str) == strtolower(MODULE_NAME.'/'.ACTION_NAME))
	    			{
	    				return $key;
	    			}

	    			//
	    			if (strtolower(MODULE_NAME) == strtolower(substr($str, 0,stripos($str, '/'))))
	    			{
	    				$tmp_key = $key;
	    			}
	    		}else
	    		{
	    			foreach ($title as $row => $col)
	    			{
	    				if (stripos($row, '?'))
	    				{
	    					$row = substr($row, 0,stripos($row, '?'));
	    					//var_dump($str);exit;
	    				}
	    				if (strtolower($row) == strtolower(MODULE_NAME.'/'.ACTION_NAME))
	    				{
	    					return $key;
	    				}
                        //这里多加个$row != 'Index/myInfo',保证tab切换正常
                        //因为'Index/myInfo' => '修改密码'Index是IndexAction
		    			if ($row != 'Index/myInfo' && strtolower(MODULE_NAME) == strtolower(substr($row, 0,stripos($row, '/'))))
		    			{
		    				$tmp_key = $key;
		    			}
	    			}
	    		}

    		}
    	}

    	return $tmp_key;
    }

    /**
      +----------------------------------------------------------
     * 显示二级菜单
      +----------------------------------------------------------
     */
    private function show_sub_menu() {
    	if ($this->sub_menu_parent != '')
    	{
    		$big = $this->sub_menu_parent;
    	}else
    	{
    		$big = MODULE_NAME == "Index" ? "Common" : MODULE_NAME;
    	}

        $cache = C('admin_sub_menu');
        $sub_menu = array();
        if ($cache[$big]) {
            $cache = $cache[$big];
            foreach ($cache as $url => $title) {

            	if (is_array($title))
            	{
            		foreach ($title as $row => $col)
            		{
                        if ($row != 'name') {
                            if(!UserLogicModel::getAuth("Admin/".$row)){
                                continue;
                            }
            				$sub_menu[$url][$row] = array('url' => U("$row"), 'title' => $col);
                        }
            		}
                    if(!empty($sub_menu[$url])){
                        $sub_menu[$url]['name'] = $title['name'];
                    }

            	}else{
                    if(!UserLogicModel::getAuth("Admin/".$url)){
                        continue;
                    }
               	    $sub_menu[$url] = array('url' => U("$url"), 'title' => $title);
            	}
            }
            return $sub_menu;
        } else {
           // return $sub_menu[] = array('url' => '#', 'title' => "该菜单组不存在");
        }
    }

    /**
     * @param string $data
     * @return string 过滤后信息
     * 过滤输入内容，防止xss和缓冲区溢出
     */
    protected function safeFilter($data) {
    	$data = strip_tags($data);
    	if (strlen($data) > 255) {
    		//去除16进制
    		$data = preg_replace('/\\[xX]([A-Fa-f0-9]{1,3})/', '',$data);
    	}
    	return $data;
    }

    public function export_csv($filename, $data, $isExcel = false){
        $contentType = 'application/octet-stream';
        if ($isExcel) {
            $contentType = 'application/vnd.ms-excel';
        }
        Header("Content-type: $contentType");
        Header("Accept-Ranges: bytes");
        Header("Content-Disposition: attachment; filename=".$filename); // 输出文件内容
        echo mb_convert_encoding($data, 'GBK', 'utf-8');
        exit;
    }

    /**
     * 通用导出 cvs
     * @param array $exportData
     * @param array $exportConfig
     * @param array $extraData
     */
    protected function commonExportCvs($exportData, $exportConfig, $extraData) {
        $data = implode(',', arrayColumn($exportConfig, 'title')) . "\n";
        foreach ($exportData as $val) {
            $temp = '';
            foreach ($exportConfig as $key => $field) {
                //echo $key . '<br />';
                if (isset($field['list'])) {
                    $temp .= $extraData[$field['list']][$val[$key]] . ",";
                } else if (isset($field['date'])) {
                    $temp .= date($field['date'], $val[$key]) . ",";
                } else if (isset ($field['callback'])) {
                    $extraDataDetail = isset($extraData[$key]) ? $extraData[$key] : $extraData['callback'];
                    $temp .= $field['callback'][0]->$field['callback'][1]($val, $key, $extraDataDetail) . ",";
                } else {
                    $temp .= "{$val[$key]},";
                }

            }
            $data .= substr($temp, 0, -1) . "\n";
        }
        //echo $data;exit;
        //exit;
        $this->export_csv(time() . '.csv', $data);
    }

    /*
     * 读取excel
     * */
    protected function read($filename,$encode='utf-8'){
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;

    }




}