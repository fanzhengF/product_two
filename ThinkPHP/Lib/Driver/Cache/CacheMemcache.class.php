<?php
defined('THINK_PATH') or exit();
/**
 * Memcache缓存驱动
 * @category   Extend
 * @package  Extend
 * @subpackage  Driver.Cache
 * @author tangyi@jiaju.com
 */
class CacheMemcache extends Cache {

	/**
	* 缓存服务器
	* @var servers
	* @access private 
	*/
	private $servers = array();

	/**
	* memcache资源
	* @var connections
	* @access private
	*/
	private $connections = array();

	/**
	* 架构函数
	* @access public
	*/
	function __construct() {
		if ( !extension_loaded('memcache') ) {
			throw_exception(L('_NOT_SUPPERT_').':memcache');
		}
		$groups = explode(",", C('MEMCACHE_HOST'));
		if($groups) {
			foreach($groups as $key=>$group) {
				$this->servers[$key] = explode(" ", $group);
			}
		}
	}

	/**
	* 获取memcache资源
	* @access protected
	* @return array
	*/
	protected function getConnections() {
		if(empty($this->connections)) {
			if(!empty($this->servers)) {
				foreach($this->servers as $server) {
					$c = new Memcache();
					if(is_array($server) && $server) {
						foreach($server as $v) {
							$m = explode(":",$v);
							$c->addServer($m[0],$m[1]);
						}
					}
					$this->connections[] = $c;
					unset($c);
				}
			}
		}
	}

	/**
	* 读取缓存
	* @access public
	* @param string $name 缓存变量名
	* @return mixed
	*/
	public function get($name) {
        $name = $this->generateKey($name);
		$this->getConnections();
		if($this->connections) {
			return $this->connections[0]->get($name);
		} else {
			return false;
		}
	}

	/**
	* 写入缓存
	* @access public
	* @param string $name 缓存变量名
	* @param mixed $value  存储数据
	* @param integer $expire  有效时间（秒）
	* @return boolen
	*/
    public function set($name, $value, $expire = null) {
		if(is_null($expire)) {
            $expire = C('DATA_CACHE_TIME');
		}
		$name = $this->generateKey($name);
		$this->getConnections();
		if($this->connections) {
			foreach($this->connections as $con) {
				C('DATA_CACHE_COMPRESS') == true ? $con->set($name, $value, MEMCACHE_COMPRESSED, $expire) : $con->set($name, $value, 0, $expire);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	* 删除缓存
	* @access public
	* @param string $name 缓存变量名
	* @return boolen
	*/
	public function rm($name, $ttl = false) {
        $name = $this->generateKey($name);
		$this->getConnections();
		if($this->connections) {
			foreach($this->connections as $con) {
				$ttl === false ? $con->delete($name): $con->delete($name, intval($ttl));
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * increment item's value
	 * @access public
	 * @param string $name
	 * @param integer $value
	 * @return boolean
	 */
	public function increment($name, $value=1) {
		 $name = $this->generateKey($name);
		$this->getConnections();
		if($this->connections) {
			foreach($this->connections as $con) {
				$con->increment($name, $value);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Decrement item's value
	 * @access public
	 * @param string $name
	 * @param integer $value
	 * @return boolean
	 */
	public function decrement($name, $value=1) {
		 $name = $this->generateKey($name);
		$this->getConnections();
		if($this->connections) {
			foreach($this->connections as $con) {
				$con->decrement($name, $value);
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	* generate a key with key prefix
	* @access protected
	* @param string $key
	* @return string
	*/
    protected function generateKey($key) {
		return C('DATA_CACHE_PREFIX') ? C('DATA_CACHE_PREFIX') . $key : $key;
	}
}
