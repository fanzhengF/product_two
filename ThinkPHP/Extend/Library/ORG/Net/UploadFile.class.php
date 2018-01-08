<?php
/* 语言定义 */
C('ORG_UPLOAD_TEMP', '创建临时文件错误');
C('ORG_UPLOAD_FILE_EXIST', '文件已经存在');
C('ORG_UPLOAD_IVALID_IMAGE', '非法图像文件');
C('ORG_UPLOAD_NO_FILE', '没有选择上传文件');
C('ORG_UPLOAD_MAX_SIZE', '上传文件大小超过了系统限制');
C('ORG_UPLOAD_ALLOW_EXT', '上传文件类型不允许');
C('ORG_UPLOAD_INVALID', '非法上传文件');
C('ORG_UPLOAD_ERROR', '文件上传失败');
C('ORG_UPLOAD_ERROR_DIR', '文件目录设置错误');
C('ORG_UPLOAD_FILENAME_REPEAT', '上传文件名有重复');

C('ORG_UPLOAD_ERR_INI_SIZE', '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值');
C('ORG_UPLOAD_ERR_FORM_SIZE', '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值');
C('ORG_UPLOAD_ERR_PARTIAL', '文件只有部分被上传');
C('ORG_UPLOAD_ERR_NO_FILE', '没有文件被上传');
C('ORG_UPLOAD_ERR_NO_TMP_DIR', '找不到临时文件');
C('ORG_UPLOAD_ERR_CANT_WRITE', '文件写入失败');
C('ORG_UPLOAD_ERR_UNKNOWN', '出现未知错误');

C('ORG_BINARY_EXT_ERROR', '二进制上传只适用于图片');

define('DRIVER_GD',         1); //常量，标识GD库类型
define('DRIVER_IMAGICK',    2); //常量，标识imagick库类型
define('DRIVER_MAGICKWAND', 3); //常量，标识imagick库类型

/* 缩略图相关常量定义 */
define('THUMB_SCALING',   1); //常量，标识缩略图等比例缩放类型
define('THUMB_FILLED',    2); //常量，标识缩略图缩放后填充类型
define('THUMB_CENTER',    3); //常量，标识缩略图居中裁剪类型
define('THUMB_NORTHWEST', 4); //常量，标识缩略图左上角裁剪类型
define('THUMB_SOUTHEAST', 5); //常量，标识缩略图右下角裁剪类型
define('THUMB_FIXED',     6); //常量，标识缩略图固定尺寸缩放类型
define('THUMB_FULL_1',    7); //常量，标识缩略图按短边缩小(保证完整原图)
define('THUMB_FULL_2',    8); //常量，标识缩略图按长边缩小(保证完整原图)

/* 水印相关常量定义 */
define('WATER_NORTHWEST', 1); //常量，标识左上角水印
define('WATER_NORTH',     2); //常量，标识上居中水印
define('WATER_NORTHEAST', 3); //常量，标识右上角水印
define('WATER_WEST',      4); //常量，标识左居中水印
define('WATER_CENTER',    5); //常量，标识居中水印
define('WATER_EAST',      6); //常量，标识右居中水印
define('WATER_SOUTHWEST', 7); //常量，标识左下角水印
define('WATER_SOUTH',     8); //常量，标识下居中水印
define('WATER_SOUTHEAST', 9); //常量，标识右下角水印

/**
 * 文件上传类
 * @category   ORG
 * @package  ORG
 * @subpackage  Net
 * @author   tangyi@jiaju.com
 * @version $Id: UploadFile.class.php 498412 2013-07-16 07:31:22Z liyue1 $
 */
class UploadFile {

    /**
     * getimagesize函数中的数字和扩展映射
     * @access private
     */
    private $image_ext_map = array(
        1 => 'gif',
        2 => 'jpg',
        3 => 'png',
        4 => 'swf',
        5 => 'psd',
        6 => 'bmp',
        7 => 'tiff',
        8 => 'tiff',
        9 => 'jpc',
        10 => 'jp2',
        11 => 'jpx',
        12 => 'jb2',
        13 => 'swf',
        14 => 'iff',
        15 => 'wbmp',
        16 => 'xbm',
    );

    /**
     * 系统默认配置文件，可以被覆盖
     * @access private
     */
    private $config =   array(
        'upload_max_size'           => -1,    // 上传文件的最大值
        'upload_allow_exts'         => array(),    // 允许上传的文件后缀 留空不作后缀检查
        'upload_replace'            => true,  // 同名文件是否覆盖
        'upload_sync'               => true,  // 是否同步上传
		'upload_dir'				=> 'uploads/',
        'upload_hash_method'        => 'md5_file',   // hash规则函数，根据这个生成文件名
        'upload_hash_level'         => 2,  // hash目录层次
        'upload_hash_width'         => 2,  // hash文件名每个目录占用的字符数
        'upload_suffix'             => '',  //后缀
        'upload_image_origin'       => true,  // 是否保存原图
        'upload_image_driver'       => DRIVER_GD,
        'upload_image_water_mark'       => false,
        'upload_image_water_mark_origin'=> true,  // 是否保存带水印的原图
        'upload_image_water_mark_sync'  => false, // 同步上传
        'upload_image_water_mark_pos'   => WATER_SOUTHEAST,  // 图片水印位置
		'upload_image_water_mark_pad'	=> 0,
        'upload_image_water_mark_suffix'=> '_watermark',  // 水印图片后缀
        'upload_image_water_mark_file'  => 'image/watermark.png',
        'upload_image_thumb'					=> false,  // 缩略图开关
		'upload_image_thumb_type'				=> array(THUMB_SCALING),
        'upload_image_thumb_sync'				=> false, // 同步上传
		'upload_image_thumb_water_mark'			=> true,
        'upload_image_thumb_suffix'				=> '_thumb',
        'upload_image_thumb_data'				=> array(
//			array(
//				'width'				=> 500,
//				'height'			=> 500,
//				'suffix'			=> '', // 缩略图后缀
//				'mode'				=> THUMB_SCALING,  // 缩略图类型
//				'sync'				=> true, // 同步
//				'water_mark'		=> true,
//			)
        ),
    );

    // 错误信息
    private $error = '';

    // 上传成功的文件信息
    private $upload_fileinfo = array();

    /**
     * @access public
     */
    public function __get($name){
        if(isset($this->config[$name])) {
            return $this->config[$name];
        }
        return null;
    }

    /**
     * @access public
     */
    public function __set($name,$value){
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     * @access public
     */
    public function __isset($name){
        return isset($this->config[$name]);
    }

    /**
     * 架构函数
     * @access public
     * @param array $config  上传参数
     */
    public function __construct($config=array()) {

        if(is_array($config)) {
            $this->config = array_merge($this->config,$config);
        }

    }


    /**
     * 上传一个文件
     * @access private
     * @param string src
     * @param string dst
     * @param boolean sync
     * @return string
     */

    protected function save($src, $dst, $sync = true) {


        import('ORG.SINA.LejuVfs');
        $vfs = LejuVfs::getInstance();
        // TODO : 可能影响了之后文件写入的句柄导致写入慢暂时去掉待查
        //if($vfs->fetch($dst.'&'.mt_rand()) && $this->upload_replace === false) {
        //    $this->error = C('ORG_UPLOAD_FILENAME_REPEAT');
        //    return false;
        //}
        $ret = $vfs->save($src, $dst, $sync);
        // 处理返回结果
        if(false === $ret) {
            $this->error = C('ORG_UPLOAD_ERROR');
            return false;
        } else {
            return true;
        }
    }

    /**
     * 上传单个上传字段中的文件 支持多附件
     * @access public
     * @param array $data  上传文件信息
     * @param array $is_file  是否通过file表单上传,否则看作是二进制上传
     * @return array()
     */
    public function upload($data, $is_file = true,$default_ext='') {
		$temp_dir = C('UPLOAD_TEMP_DIR');
		if(false === is_dir($temp_dir)) {
			if(false === mkdir($temp_dir, 0777, true)) {
				$this->error = C('ORG_UPLOAD_TEMP');
				return false;
			}
		}
		if(true === $is_file) {
			// 过滤无效上传
			if(empty($data['name'])) {
				$this->error = C('ORG_UPLOAD_NO_FILE');
				return false;
			}
			// TODO:简单获取文件后缀，如是图片则会再严格获取一次，缺少非图片文件后缀的严格判定
			$data['extension'] = $this->getExt($data['name']);
			if(false === $this->check($data)) {
				return false;
			}
			$_origin_temp_file = tempnam($temp_dir, uniqid());
			if(false === move_uploaded_file($data['tmp_name'], $_origin_temp_file)) {
				$this->error = C('ORG_UPLOAD_ERROR');
				return false;
			}
		} else {
			$_origin_temp_file = tempnam($temp_dir, uniqid());
			@file_put_contents($_origin_temp_file, $data);
        }

		// 根据规则生成文件名(不包括扩展名)
		if($this->upload_hash_method && function_exists($this->upload_hash_method)) {
			$filename = call_user_func($this->upload_hash_method, $_origin_temp_file);
			if($this->upload_hash_level && $this->upload_hash_width) {
				$_pos   = $this->upload_hash_level * $this->upload_hash_width;
				if($_pos >= strlen($filename)) {
					$this->error = C('ORG_UPLOAD_ERROR_DIR');
					return false;
				}
				$_left  = substr($filename, 0, $_pos);
				$_right = substr($filename, $_pos);
				$filename = $_right;
				for($i = $this->upload_hash_level; $i > 0; $i--) {
					$filename = substr($_left, ($i - 1) * $this->upload_hash_width, $this->upload_hash_width) . '/' . $filename;
				}
				$filename = '/' . $filename;
				unset($_pos, $_left, $_right);
			}
		} else {
			// 随机文件名
			$filename = uniqid();
		}
		$filename = $this->upload_dir.$filename.$this->upload_suffix;
		$_sync = $this->upload_sync;
        // TODO:If accessing the filename image is impossible getimagesize() will generate an error of level E_WARNING. On read error, getimagesize() will generate an error of level E_NOTICE. 但是在php版本5.2.6上没有抛出任何级别错误，仅仅返回false
        $image_size = @getimagesize($_origin_temp_file);
		if(false === $image_size) {
			if(false === $is_file) {
				$this->error = C('ORG_BINARY_EXT_ERROR');
			}
			 // 如果不是图片
            $file['extension'] = $default_ext;
            $filename .= '.' . $file['extension'];
			$ret = $this->save($_origin_temp_file, $filename, $_sync);
			if(false === $ret) return false;
			return array(
				'origin' => array('src' => $filename, 'size' => $file['size'], 'ext' => $file['extension'])
			);
		}
		// 如果上传文件是图片，则根据配置对图片进行处理
		$file['width']      = $image_size[0];
		$file['height']     = $image_size[1];
		$file['extension']  = $this->image_ext_map[$image_size[2]];
		
		if(!$this->checkExt($file['extension'])) {
			// 取得安全的后缀后再检查一次
			$this->error = C('ORG_UPLOAD_ALLOW_EXT');
			return false;
		}
		if($this->upload_image_origin) {
			// 保存原图
            $origin_filename = $filename . '.' . $file['extension'];
			$ret = $this->save($_origin_temp_file, $origin_filename, $_sync);
			if(false === $ret) return false;
			$info['origin'] = array(
				'src'   => $origin_filename,
				'width' => $file['width'],
				'height'=> $file['height'],
				'size'  => filesize($_origin_temp_file),
				'ext'   => $file['extension'],
			);
		}
		// 是否生成水印图，保存水印图
		if($this->upload_image_water_mark) {
			// 图片水印处理
			import('ORG.Util.Image.ThinkImage');
			$thinkimage = new ThinkImage($this->upload_image_driver);
			$_water_mark_temp_file = tempnam($temp_dir, uniqid());
			$thinkimage->open($_origin_temp_file)
					   ->water($this->upload_image_water_mark_file, $this->upload_image_water_mark_pos, $this->upload_image_water_mark_pad)
					   ->save($_water_mark_temp_file);
			if($this->upload_image_water_mark_origin) {
				$water_mark_filename = $filename . $this->upload_image_water_mark_suffix . '.' . $file['extension'];
				$_sync  = $this->upload_image_water_mark_sync;
				$ret    = $this->save($_water_mark_temp_file, $water_mark_filename, $_sync);
				if(false === $ret) return false;
				$info['water_mark'] = array(
					'src'   => $water_mark_filename,
					'width' => $file['width'],
					'height'=> $file['height'],
					'size'  => filesize($_water_mark_temp_file),
					'ext'   => $file['extension'],
				);
			}
        }
		// 是否生成缩略图
		if($this->upload_image_thumb && $this->upload_image_thumb_data) {
			import('ORG.Util.Image.ThinkImage');
            require_once(WEB_ROOT."ThinkPHP/Extend/Library/ORG/Util/Image/ThinkImage.class.php");

			$thinkimage = new ThinkImage($this->upload_image_driver);
			foreach($this->upload_image_thumb_data as $key=>$data) {
                //缩略图添加水印问题，水印的尺寸是否进行等比缩放   add by guo.hao 2015-1-16
                //$data['water_mark_scale'] 缩放的图片，水印图进行等比缩放否    默认是false
                $_water_mark_scale = isset($data['water_mark_scale']) ? $data['water_mark_scale'] : false ;
                if($_water_mark_scale){ //原逻辑，未改动
                    $_thumb_temp_file		= tempnam($temp_dir, uniqid());
                    $_thumb_water_mark		= isset($data['water_mark']) ? $data['water_mark'] : $this->upload_image_thumb_water_mark;
                    $_src_thumb_temp_file	= (false === $_thumb_water_mark) ? $_origin_temp_file: $_water_mark_temp_file;
                    $image_resource = $thinkimage->open($_src_thumb_temp_file);
                    if(isset($data['mode']) && is_array($data['mode']))
                    {
                        foreach($data['mode'] as $thumb_type) {
                            $image_resource->thumb($data['width'], $data['height'], $thumb_type);
                        }
                    } else {
                        foreach($this->upload_image_thumb_type as $thumb_type) {
                            $image_resource->thumb($data['width'], $data['height'], $thumb_type);
                        }
                    }
                    $image_resource->save($_thumb_temp_file);
                    $suffix				= isset($data['suffix']) ? $data['suffix'] : "_{$data['width']}x{$data['height']}";
                    $_sync_thumb_file	= isset($data['sync']) ? $data['sync'] : (isset($this->upload_image_thumb_sync) ? $this->upload_image_thumb_sync : $_sync);
                    $thumb_filename		= $filename . $this->upload_image_thumb_suffix . $suffix . '.' . $file['extension'];
                    $ret = $this->save($_thumb_temp_file, $thumb_filename, $_sync_thumb_file);
                    if(false === $ret) return false;
                    $info['thumb']['w'.$data['width'].'h'.$data['height']] = array(
                        'src'   => $thumb_filename,
                        'width' => intval($thinkimage->width()),
                        'height'=> intval($thinkimage->height()),
                        'size'	=> filesize($_thumb_temp_file),
                        'ext'   => $thinkimage->type(),
                    );
                    @unlink($_thumb_temp_file);
                }else{  //保留水印图大小  默认执行
                    $_thumb_temp_file		= tempnam($temp_dir, uniqid());
                    $_src_thumb_temp_file = $_origin_temp_file;
                    $image_resource = $thinkimage->open($_src_thumb_temp_file);
                    if(isset($data['mode']) && is_array($data['mode'])) {   //缩略的形式
                        foreach($data['mode'] as $thumb_type) {
                            $image_resource->thumb($data['width'], $data['height'], $thumb_type);
                        }
                    } else {
                        foreach($this->upload_image_thumb_type as $thumb_type) {
                            $image_resource->thumb($data['width'], $data['height'], $thumb_type);
                        }
                    }
                    //缩略之后的图像
                    $image_resource->save($_thumb_temp_file);

                    if($data['water_mark']){    //添加水印
                        $image_resource2 = $thinkimage->open($_thumb_temp_file);
                        $_thumb_temp_file2 = tempnam($temp_dir, uniqid());
                        $image_resource2
                            ->water($this->upload_image_water_mark_file, $this->upload_image_water_mark_pos, $this->upload_image_water_mark_pad)
                            ->save($_thumb_temp_file2);

                        $suffix				= isset($data['suffix']) ? $data['suffix'] : "_{$data['width']}x{$data['height']}";
                        $_sync_thumb_file	= isset($data['sync']) ? $data['sync'] : (isset($this->upload_image_thumb_sync) ? $this->upload_image_thumb_sync : $_sync);
                        $thumb_filename		= $filename . $this->upload_image_thumb_suffix . $suffix . '.' . $file['extension'];
                        $ret = $this->save($_thumb_temp_file2, $thumb_filename, $_sync_thumb_file);
                        if(false === $ret) return false;
                        $info['thumb']['w'.$data['width'].'h'.$data['height']] = array(
                            'src'   => $thumb_filename,
                            'width' => intval($thinkimage->width()),
                            'height'=> intval($thinkimage->height()),
                            'size'	=> filesize($_thumb_temp_file2),
                            'ext'   => $thinkimage->type(),
                        );
                        @unlink($_thumb_temp_file);
                        @unlink($_thumb_temp_file2);
                    }else{  //不添加水印
                        $suffix				= isset($data['suffix']) ? $data['suffix'] : "_{$data['width']}x{$data['height']}";
                        $_sync_thumb_file	= isset($data['sync']) ? $data['sync'] : (isset($this->upload_image_thumb_sync) ? $this->upload_image_thumb_sync : $_sync);
                        $thumb_filename		= $filename . $this->upload_image_thumb_suffix . $suffix . '.' . $file['extension'];
                        $ret = $this->save($_thumb_temp_file, $thumb_filename, $_sync_thumb_file);
                        if(false === $ret) return false;
                        $info['thumb']['w'.$data['width'].'h'.$data['height']] = array(
                            'src'   => $thumb_filename,
                            'width' => intval($thinkimage->width()),
                            'height'=> intval($thinkimage->height()),
                            'size'	=> filesize($_thumb_temp_file),
                            'ext'   => $thinkimage->type(),
                        );
                        @unlink($_thumb_temp_file);
                    }
                }
			}
		}
        @unlink($_origin_temp_file);
		isset($_water_mark_temp_file) && @unlink($_water_mark_temp_file);
		unset($_origin_temp_file, $_water_mark_temp_file, $_thumb_temp_file);
		return $info;
    }

    /**
     * 抓取运程文件
     * @param string $url 文件url
     */
    public function getFile($url){
        //开启缓冲区
        ob_start();
        //stream_context_create
        //创建并返回一个文本数据流并应用各种选项，可用于fopen(),file_get_contents()
        //等过程的超时设置、代理服务器、请求方式、头信息设置的特殊过程。
        $context = stream_context_create(
            array (
                'http' => array (
                    'follow_location' => false // don't follow redirects
                )
            )
        );
        //确保php.ini的Fopen wrappers已经开启
        readfile($url,true,$context);
        //获取缓冲区的内容
        $source = ob_get_contents();
        //关闭缓冲区
        ob_end_clean();
        $info = pathinfo($url);
        return array(
            'source'=>$source,
            'ext'=>$info['extension']
        );
    }
     
     
    
    /**
     * 检查上传的文件
     * @access private
     * @param array $file 文件信息
     * @return boolean
     */
    private function check($file) {
        // 文件上传失败
        if($file['error']!== 0) {
            $this->error($file['error']);
            return false;
        }
        // 检查文件大小
        if(!$this->checkSize($file['size'])) {
            $this->error = C('ORG_UPLOAD_MAX_SIZE');
            return false;
        }
        // 检查文件类型
        if(!$this->checkExt($file['extension'])) {
            $this->error = C('ORG_UPLOAD_ALLOW_EXT');
            return false;
        }
        // 检查是否合法上传
        if(!$this->checkUpload($file['tmp_name'])) {
            $this->error = C('ORG_UPLOAD_INVALID');
            return false;
        }
        return true;
    }

    /**
     * 检查文件大小是否合法
     * @access private
     * @param integer $size 数据
     * @return boolean
     */
    private function checkSize($size) {
        return !($size > $this->upload_max_size) || (-1 == $this->upload_max_size);
    }

    /**
     * 获取错误代码信息
     * @access public
     * @param string $errorNo  错误号码
     * @return void
     */
    protected function error($errorNo) {
         switch($errorNo) {
            case UPLOAD_ERR_INI_SIZE:
                $this->error = C('ORG_UPLOAD_ERR_INI_SIZE');
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $this->error = C('ORG_UPLOAD_ERR_FORM_SIZE');
                break;
            case UPLOAD_ERR_PARTIAL:
                $this->error = C('ORG_UPLOAD_ERR_PARTIAL');
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->error = C('ORG_UPLOAD_ERR_NO_FILE');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->error = C('ORG_UPLOAD_ERR_NO_TMP_DIR');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $this->error = C('ORG_UPLOAD_ERR_CANT_WRITE');
                break;
            default:
                $this->error = C('ORG_UPLOAD_ERR_UNKNOWN');
        }
        return ;
    }

    /**
     * 检查上传的文件后缀是否合法
     * @access private
     * @param string $ext 后缀名
     * @return boolean
     */
    private function checkExt($ext) {
        if(!empty($this->upload_allow_exts))
            return in_array(strtolower($ext),$this->upload_allow_exts,true);
        return true;
    }

    /**
     * 检查文件是否非法提交
     * @access private
     * @param string $filename 文件名
     * @return boolean
     */
    private function checkUpload($filename) {
        return is_uploaded_file($filename);
    }

    /**
     * 取得上传文件的后缀
     * @access private
     * @param string $filename 文件名
     * @return boolean
     */
    protected  function getExt($filename) {
        $pathinfo = pathinfo($filename);
        return $pathinfo['extension'];
    }

    /**
     * 取得最后一次错误信息
     * @access public
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * 通过文件流，获取文件类型
     * @param  string $ext 文件后缀
     * @return string      mime类型
     * add by guo.hao 2014-12-29
     */
    public function checkFileType($filename){
        $file = @fopen($filename, 'rb');
        if ($file){
            $str = @fread($file, 0x400); // 读取前 1024 个字节
            @fclose($file);
        }
        $strInfo  = @unpack("C2chars", $str);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);

        switch( $typeCode ) {
            case '255216':
                return 'jpg';
                break;
            case '7173':
                return 'gif';
                break;
            case '13780':
                return 'png';
                break;
            case '6677':
                return 'bmp';
                break;
            case '7790':
                return 'exe';
                break;
            case '7784':
                return 'midi';
                break;
            case '8297':
                return 'rar';
                break;
            case '6787':
                return 'swf';
                break;
            default:
                return false;
                break;
        }
    }



}
