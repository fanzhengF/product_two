<?php
/**
 * squid 操作类，暂时只提供清squid缓存接口
 *
 * @category ORG
 * @package SINA
 * @author tangyi@jiaju.com
 * @version $Id: Squid.class.php 445277 2013-03-13 13:06:34Z tangyi $
 */
require_once "SquidPurge.php";
class Squid
{
    /**
     * @access private
     */
    private $_path;

    /**
     * 构造函数，做一些初始化工作
     * @access public
     */
    public function __construct() {
        if(C('SINA_SQUID_PATH')) {
            $this->_path = C('SINA_SQUID_PATH');
        }
    }

    /**
     * 清文件squid缓存
     * @param string file
     * @access public
     * @return bool
     */
    public function purge($file) {
        if($this->_path) {
		    $url = parse_url($this->_path);
		    return SquidPurge($url['path'] . $file, array(
		    	'storage' => true,
		    	'domain' => $url['host']
		    ));
        } else {
            return false;
        }
    }
}
