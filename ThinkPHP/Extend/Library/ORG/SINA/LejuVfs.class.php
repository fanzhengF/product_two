<?php
/**
 * vfs 上传类，调用系统接口，可实现实时上传和异步上传
 *
 * @category ORG
 * @package SINA
 * @author tangyi@jiaju.com
 * @version $Id: LejuVfs.class.php 480456 2013-05-25 06:11:50Z tangyi $
 */
require_once("VFS/VFS/dpool_storage.php");
class LejuVfs
{
    /**
     * vfs类实例
     * @access private
     */
    private static $_instance;

    /**
     * @access private
     */
    private $_vfs;

    /**
     * @access private
     */
    private function __construct() {
        $this->_vfs = new VFS_dpool_storage();
    }

    /**
     * @access private
     */
    private function __clone() {}

    /**
     * 单例模式函数
     * @access public
     * @return vfs
     */
    public static function getInstance() {
        if(!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

	/**
	 * @synopsis copy dest file to VFS
	 *
	 * @param string $dest_file
	 * @param string $src_file
	 * @param bool $is_mkdir
	 *
	 * @returns
	 */
	public function copy($dest_file, $src_file, $is_mkdir = true)
    {
        $file = pathinfo($dest_file);
        return $this->_vfs->write($file['dirname'] . '/', $file['basename'], $src_file, $is_mkdir);
	}

	/**
	 * @synopsis rsync dest file to VFS
	 *
	 * @param string $dest_file
	 * @param string $src_file
	 * @param bool $is_mkdir
	 *
	 * @returns
	 */
    public function rsync($dest_file, $src_file, $is_mkdir = true)
    {
        $info = pathinfo($dest_file);
        //edit bug by jiazhu （多一个参数）
        return $this->_vfs->rsync_write($info['dirname'], $info['basename'], $src_file);
	}

	/**
	 * @synopsis fetch file from VFS
	 *
	 * @param string $file
	 *
	 * @returns
	 */
	public function fetch($file)
	{
		$info = pathinfo($file);
        $dir = rtrim($info['dirname'],'/\\')."/";
		return $this->_vfs->read($dir, $info['basename']);
	}

	/**
	 * 保存文件
	 *
	 * @param string src 原始文件
     * @param string dst 目标文件
     * @param boolean sync  是否同步上传
     * @param boolean unlink 是否删除源文件
     * @param boolean clear  是否清楚squid缓存
	 * @return boolean
	 */
	public function save($src, $dst, $sync = false, $unlink = false, $clear = false)
	{
        if ($clear) {
            import('ORG.SINA.Squid');
            $squid = new Squid();
            $squid->purge($dst);
        }
        if($sync) {
            //同步使用rsync
            $ret = $this->rsync($dst, $src, true);
        } else {
            //异步使用write
            $ret = $this->copy($dst, $src, true);
        }
        //	var_dump($ret);exit;
        if($unlink) {
            @unlink($src);
        }
        if ($ret instanceof PEAR_Error) {
         //   throw new ThinkException($ret->message);
        }
        return true;
	}
}
