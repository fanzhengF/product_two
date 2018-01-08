<?php
/**
 * 缩略图处理与存储
 *
 * @author mole <mole1230@gmail.com>
 * @version $Id: JPhoto.php 4741 2011-06-16 07:47:15Z hongfu $
 */
import('ORG.Util.Image.JImagick');
import('ORG.SINA.LejuVfs');
class JPhoto
{
	/**
	 * 创建临时文件目录
	 *
	 * @var string
	 */
	public $tempDir;

	/**
	 * @var JVfs
	 */
	private $_vfs;

	/**
	 * @var string
	 */
	private $_error;

	/**
	 * VFS 文件分发PEAR错误
	 *
	 * @var PEAR_Error
	 */
	private $_pearError;

	/**
	 * @return JVfs
	 */
	public function getVfs()
	{

		if ($this->_vfs == null) {
			$this->_vfs = LejuVfs::getInstance();
		}

		return $this->_vfs;
	}

	/**
	 * Get temp directory
	 *
	 * @return string
	 */
	public function getTempDir()
	{
		if ($this->tempDir === null) {
			$this->tempDir =$_SERVER["SINASRV_CACHE_DIR"] . '/' ;
			if (!is_dir($this->tempDir)) {
				if (!@mkdir($this->tempDir, 0777, true)) {
					throw new CException("Can't create temp dir '{$this->tempDir}'");
				}
			}
		}

		return $this->tempDir;
	}

	/**
	 * @return string
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * @return PEAR_Error
	 */
	public function getPearError()
	{
		return $this->_pearError;
	}

	/**
	 * 用于thumb默认配置
	 *
	 * @return array
	 */
	public function getDefaultOptions()
	{
		return array(
			// 水印图片文件
			'watermark' =>  'images/watermark.png',
			// 是否通过 VFS分发
			'vfs' => true,
			// 不包含hash目录，hash目录由文件basename生成
			'basedir' => '',
			// 用于 JImagick 类的属性设置，JImagick所有公有属性均可在些设置
			'imagick' => array(
				'format' => '',	// 图片保存格式，一般用原始图片格式保存
				'quality' => 100 // 图片保存时压缩质量
			),
			// 文件上传验证设置，除前三项外，其余将传给 JFileValidator 类
			'upload' => array(
				'isCheck' => true,			// 是否进行文件上传检查
				'fieldName' => 'filedata',	// 上传文件在 $_FILES 中的键值名
				'maxSize' => '1M',			// 文件最大尺寸
				'types' => 'jpg,jpeg,gif,png',	//可以上传文件后缀
                'maxFiles' => 1,            	//上传文件数
			),
			'thumbs' => array(
				// size：缩略图大小
				// mode：缩略模式
				// pos：水印位置
				// prefix: 文件名前缀
				// suffix: 文件名后缀
				//array('size' => '800x800', 'mode' => JImagick::MODE_RATIO, 'pos' => JImagick::BOTTOM_RIGHT, 'suffix' => ''),
				//array('size' => '160x120', 'mode' => JImagick::MODE_RATIO, 'suffix' => '_s')
			)
		);
	}

	/**
	 * 单文件缩略图
	 *
	 * @param array $setting	{@link getDefaultOptions()}
	 * @param string $basename	如果没有指定，则系统自动产生一个20位唯一ID
	 * @param bool $cleanSquid	是否清除Squid前端缓存
	 * @return array|false		出错将返回 false
	 */
	public function thumb($setting, $basename = null, $cleanSquid = false)
	{
		$setting = array_merge($this->getDefaultOptions(), $setting);
		$upload = $setting['upload'];
		$isCheck = $upload['isCheck'];
		unset($setting['upload']);
		unset($upload['isCheck']);
        if (empty($_FILES) || !($src = $_FILES[$upload['fieldName']]['tmp_name'])) {
            // echo "{'err':'上传数据为空','msg':''}";
            $this->_error = '上传数据为空';
            return false;
        } elseif($_FILES[$upload['fieldName']]['size'] > 4000000) {
            // echo "{'err':'上传图片大于2M','msg':''}";
            $this->_error = '上传图片大于4M';
            return false;
        }
		$file = $_FILES[$upload['fieldName']];
        $file['temp'] = $file['tmp_name'];
        //var_dump($file, $setting, $basename, $cleanSquid);
		$infos = $this->handleFile($file, $setting, $basename, $cleanSquid);
		return $infos;
	}

	/**
	 * 缩略图处理
	 *
	 * @param array $file array('temp' => , 'name' =>, 'size' =>, 'type' =>))
	 * @param array $setting {@link getDefaultOptions()}
	 * @param string $basename
	 * @param bool $cleanSquid
	 */
	public function handleFile($file, $setting, $basename = null, $cleanSquid = false, $id = null)
	{
		$watermark = $basedir = $imagick = $thumbs = $vfs = null;
		extract($setting, EXTR_IF_EXISTS);
		$infos = array();
		$imk = new JImagick($file['temp']);
		$imk->setOptions($imagick);

		empty($basename) && ($basename = uniqid() . sprintf('%07x', mt_rand(1000000, 9999999)));
		$basedir = !empty($basedir) ? trim($basedir, ' /') : '';
		$hashdir = $this->mkHashDir($basename);

		foreach ($thumbs as $thumb) {
			$realname = $basename . (($id !== null) ? '_' . $id : '');
			!empty($thumb['prefix']) && ($realname = $thumb['prefix'] . $basename);
			!empty($thumb['suffix']) && ($realname = $realname . $thumb['suffix']);

			$dst = $basedir . $hashdir . '/' . $realname . '.' . $imk->getFormat();
			$tmpf = tempnam($this->getTempDir(), "TMP_IMG");

			$imk->thumbnail($thumb['size'], $thumb['mode']);
			$watermark = isset($thumb['watermark']) ? $thumb['watermark'] : $watermark;
			if (!empty($thumb['pos']) && $watermark && $imk->getWidth() > 200 && $imk->getHeight() > 200) {
				if($imk->getAnimation()){//GIF动画加水印
					$imk->waterMarkGIF($watermark, $thumb['pos']);
				}else{
					$imk->waterMark($watermark, $thumb['pos']);
				}
			}

			$imk->save($tmpf);
			$size = @filesize($tmpf);
			$info = @getimagesize($tmpf);
			$width = $info[0];
			$height = $info[1];

			if (!$imk->isValid()) {
                var_dump('getError',$imk->getError());
				$this->_error = $imk->getError();
				@unlink($tmpf);
				return false;
			}

			if (!$this->save($tmpf, $dst, true, $cleanSquid)) {
				return false;
			}

			$infos[$thumb['size']] = array(
				'path' => $dst,
				'size' => $size,
				'type' => $file['type'],
				'originName' => $file['name'],
				'width' => intval($width),
				'height' => intval($height)
			);
		}

		return $infos;
	}

	/**
	 * 生成HASH散列目录
	 *
	 * @param string $filename
	 * @param int $deep
	 * @return string
	 */
	public function mkHashDir($filename, $deep = 2)
	{
		$m = strtolower(md5($filename));
		switch ($deep) {
		case 1:
			$d = $m[0] . $m[3];
			break;
		case 2:
			$d = $m[0] . $m[3] . '/' . $m[1] . $m[2];
			break;
		default:
			break;
		}

		return '/' . $d;
	}

	/**
	 * 保存文件
	 *
	 * @param string $src 原始文件
	 * @param string $dst 目标文件
	 * @param bool $vfs 是否进行VFS分发
	 * @param bo0l $cleanSquid 是否清除前端squid缓存
	 * @return bool
	 */
	public function save($src, $dst, $vfs = true, $cleanSquid = false)
	{
		if ($vfs) {
			// vfs distribute file

			$ret = $this->getVfs()->save( $src,$dst, true);
			if ($ret instanceof PEAR_Error) {
				$this->_error = 'VFS write failure';
				$this->_pearError = $ret;
				@unlink($src);
				return false;
			}

			// clean squid cache
			if ($cleanSquid) {
				$this->getVfs()->clearCache($dst);
			}
		} else {
			if (!is_dir(dirname($dst)))	{
				$this->_error =  "{$dst} dir not exists";
				@unlink($src);
				return false;
			}
			rename($src, $dst);
		}

		@unlink($src);
		return true;
	}
}
