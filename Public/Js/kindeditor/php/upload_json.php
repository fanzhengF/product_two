<?php
/**
 * KindEditor PHP
 *
 * 本PHP程序是演示程序，建议不要直接在实际项目中使用。
 * 如果您确定直接使用本程序，使用之前请仔细确认相关安全设置。
 *
 */

require_once 'JSON.php';

$file = $_FILES['imgFile'];
$model = new PhotoLogicModel();
$result = $model->upload($file);

if ($result){ 
    $url = $model->url($result['src'], $result['ext']);
    header('Content-type: text/html; charset=UTF-8');
    $json = new Services_JSON();
    echo $json->encode(array('error' => 0, 'url' => $url));
}else{
    header('Content-type: text/html; charset=UTF-8');
    $json = new Services_JSON();
    echo $json->encode(array('error' => 1, 'message' => '上传失败！'));
}

/*
 * 图库LogicModel
 * */
class PhotoLogicModel 
{
    private $_pkey = 'd9c6dbd0d8db685e42b7a8720c23f3d4';
    private $_mkey = 'cd33159678ea24381c7f230412b306a5';
    private $_helper;

    public function __construct(){
        require '../../../../ThinkPHP/Extend/Library/ORG/Net/ResourceApi.class.php';
        //import('ORG.Net.ResourceApi');
        $this->_helper = new ResourceApiClient($this->_pkey, $this->_mkey);
    }

    /*
     * 图片上传
     * */
    public function upload($file){
        $pathinfo = pathinfo($file['name']);
        $_origin_temp_file = str_replace('tmp', $pathinfo['extension'] ,tempnam($_SERVER['SINASRV_CACHE_DIR'], uniqid()));
        //if(strpos($_origin_temp_file, '.') === false)
            $_origin_temp_file .= '.'.$pathinfo['extension'];
        $result = false;
        if(false !== move_uploaded_file($file['tmp_name'], $_origin_temp_file)) {
            $res = $this->_helper->upload($_origin_temp_file);
            if($res['success'] == true){
                $msg = $res['msg'];
                $result = array(
                    'src' => $msg['fpath'].'_p'.$msg['pid'].'_mk'.$msg['mid'],
                    'ext' => $msg['fext'],
                    'width' => $msg['image_width'],
                    'height' => $msg['image_height'],
                    'size' => $msg['fsize'],
                );
            }
        }
        return $result;
    }

    /*
     * 拼接访问特定格式图片的url
     *
     * @param  size 格式100X80
     * @param  clip 格式
     * @param scale 格式
     * @param watermark  水印ID
     * */
    public function url($fpath, $ext, $size = '', $clip = '',
        $border = '', $scale = '', $watermark  = '',
        $backgroud_color = '', $quality = ''){
        if(!$fpath)
            return false;
        $tmp = explode('_', $fpath);
        $file_id = $tmp[0];
        $pid = substr($tmp[1], 1);
        $mkid = substr($tmp[2], 2);
        return
        $this->_helper->get_resource_url($pid, $mkid, $file_id, $ext, $size, $clip,
            $border, $scale, $watermark, $backgroud_color, $quality);
    }

}

exit;




require_once 'JSON.php';

$php_path = dirname(__FILE__) . '/';
$php_url = dirname($_SERVER['PHP_SELF']) . '/';

//import('ORG.SINA.LejuVfs');
require_once $php_path.'../../../../ThinkPHP/Extend/Library/ORG/SINA/LejuVfs.class.php';
$vfs = LejuVfs::getInstance();
$save_path = '';
$save_url = $_SERVER['SINASRV_NDATA_CACHE_URL'];
//$save_url = 'http://st.jiaju.com/malljiaju/';




//文件保存目录路径
//$save_path = $php_path . '../attached/';
//文件保存目录URL
//$save_url = $php_url . '../attached/';
//定义允许上传的文件扩展名
$ext_arr = array(
	'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
	'flash' => array('swf', 'flv'),
	'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
	'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2','pdf'),
);
//最大文件大小
$max_size = 1000000;

//$save_path = realpath($save_path) . '/';

//PHP上传失败
if (!empty($_FILES['imgFile']['error'])) {
	switch($_FILES['imgFile']['error']){
		case '1':
			$error = '超过php.ini允许的大小。';
			break;
		case '2':
			$error = '超过表单允许的大小。';
			break;
		case '3':
			$error = '图片只有部分被上传。';
			break;
		case '4':
			$error = '请选择图片。';
			break;
		case '6':
			$error = '找不到临时目录。';
			break;
		case '7':
			$error = '写文件到硬盘出错。';
			break;
		case '8':
			$error = 'File upload stopped by extension。';
			break;
		case '999':
		default:
			$error = '未知错误。';
	}
	alert($error);
}
//有上传文件时
if (empty($_FILES) === false) {
	//原文件名
	$file_name = $_FILES['imgFile']['name'];
	//服务器上临时文件名
	$tmp_name = $_FILES['imgFile']['tmp_name'];
	//文件大小
	$file_size = $_FILES['imgFile']['size'];
	//检查文件名
	if (!$file_name) {
		alert("请选择文件。");
	}
	//检查目录
//	if (@is_dir($save_path) === false) {
//		alert("上传目录不存在。");
//	}
	//检查目录写权限
	
//	if (@is_writable($save_path) === false) {
//		alert("上传目录没有写权限。");
//	}
	//检查是否已上传
	if (@is_uploaded_file($tmp_name) === false) {
		alert("上传失败。");
	}
	//检查文件大小
	if ($file_size > $max_size) {
		alert("上传文件大小超过限制。");
	}
	//检查目录名
	$dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
	if (empty($ext_arr[$dir_name])) {
		alert("目录名不正确。");
	}
	//获得文件扩展名
	$temp_arr = explode(".", $file_name);
	$file_ext = array_pop($temp_arr);
	$file_ext = trim($file_ext);
	$file_ext = strtolower($file_ext);
	//检查扩展名
	if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
		alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
	}
	//创建文件夹
	if ($dir_name !== '') {
		$save_path .= $dir_name . "/";
		$save_url .= $dir_name . "/";
		if (!file_exists($save_path)) {
			//mkdir($save_path);
		}
	}
	$ymd = date("Ymd");
	$save_path .= $ymd . "/";
	$save_url .= $ymd . "/";
//	if (!file_exists($save_path)) {
//		mkdir($save_path);
//	}
	//新文件名
	$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
	//移动文件
	$file_path = $save_path . $new_file_name;
//	 $ret = $vfs->save($tmp_name, $file_path);
// var_dump($_FILES,$tmp_name, $file_path);exit;
//	if (move_uploaded_file($tmp_name, $file_path) === false) {
//		alert("上传文件失败。");
//	}
	//@chmod($file_path, 0644);
	$vfs = new VFS_dpool_storage();
	$res =  $vfs->rsync_write($save_path,$new_file_name, $tmp_name, true);
	//var_dump($res,$save_path,$new_file_name, $tmp_name);
	if (!$res)
	{
		//alert("上传文件失败。");
	}
	$file_url = $save_url . $new_file_name;

	header('Content-type: text/html; charset=UTF-8');
	$json = new Services_JSON();
	echo $json->encode(array('error' => 0, 'url' => $file_url));
	exit;
}

function alert($msg) {
	header('Content-type: text/html; charset=UTF-8');
	$json = new Services_JSON();
	echo $json->encode(array('error' => 1, 'message' => $msg));
	exit;
}
