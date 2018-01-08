<?php
/**
 * @file LejuRedis.class.php
 * @synopsis
 * <pre>
 *      $redis = LejuRedis::getInstance();
 *      var_dump($redis->set('my_key', array("1", "2", "3")));
 *      var_dump($redis->get('my_key'));
 *      $redis->setCmdScopeMaster(array('zAdd'));
 *   	var_dump($redis->zAdd('my_zset', 500, 'user3'));
 *   	var_dump($redis->zAdd('my_zset', 200, 'user2'));
 *   	var_dump($redis->zAdd('my_zset', 300, 'user1'));
 *   	$redis->setCmdScopeSlave(array('zRange', 'zRangeByScore'));
 *   	var_dump($redis->zRange('my_zset', 0, -1, true));
 *   	var_dump($redis->zRangeByScore(
 *						'my_zset',
 *						'-inf',
 *						'+inf',
 *						array('withscores' => true, 'limit' => array(1, 1))
 *					));
 * </pre>
 * @author tangyi@jiaju.com
 * @version $Id: LejuRedis.class.php 484945 2013-06-05 02:39:14Z tangyi $
 * @date 2012-05-24
 */
class LejuRedis 
{
	/**
	 * @var $_instance
	 */
	private static $_instance = null;

	/**
	 * @var $_configMaster master config,default to empty array
	 */
	private $_configMaster = array();

	/**
	 * @var $_configSlave slave config,default to empty array
	 */
	private $_configSlave = array();

	/**
	 * @var $_redisKeyPrefix the redis key prefix
	 */
	private $_redisKeyPrefix = '_prefix';

	/**
	 * @ignore
	 */
	protected function __construct()
    {
        $master = $slave = array();
        if(C('REDIS.HOST')) {
            list($master['host'], $master['port']) = explode(":", C('REDIS.HOST'));
        }
        if(C('REDIS.HOST_R')) {
            list($slave['host'], $slave['port']) = explode(":", C('REDIS.HOST_R'));
        }
        if(empty($master)) {
            throw new ErrorException('redis host null.');
        }
        if(empty($slave)) {
            $slave = $master;
        }
		$this->_configMaster 	= $master;
        $this->_configSlave 	= $slave;
        if(C('REDIS.PREFIX')) {
            $this->_redisKeyPrefix 	= C('REDIS.PREFIX');
        }
		unset($config);
	}
    
    /**
     * 单例模式函数
     * @access public
     * @return vfs
     */
    public static function getInstance($options = array()) {
        if(!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }


	/**
	 * Get redis key prefix
	 * @return string
	 */
	public function getKeyPrefix()
	{
		return $this->_redisKeyPrefix;
	}

	/**
	 * Set redis key prefix
	 * @return
	 */
	public function setKeyPrefix($prefix)
	{
		$this->_redisKeyPrefix = $prefix;
	}

	/**
	 * @var $_redisMaster redis master,default to null
	 */
	private $_redisMaster = null;

	/**
	 * Get redis master server.If fail,throw ErrorException.
	 * @return Redis
	 */
	public function getRedisMaster()
	{
		if($this->_redisMaster instanceof Redis)
		{
			return $this->_redisMaster;
		}
		else
		{
			$this->_redisMaster = new Redis;
			try
			{
				$this->_redisMaster->connect($this->_configMaster['host'], $this->_configMaster['port']);
				$this->_redisMaster->setOption(Redis::OPT_PREFIX, $this->_redisKeyPrefix);
			}
			catch(Exception $e)
			{
				$this->errorShow();
				throw new ErrorException("Connect redis server " . implode(",", $this->_configMaster) . " fail !");
			}
			return $this->_redisMaster;
		}
	}
	/**
	 * @var $_redisSlave redis slave,default to null
	 */
	private $_redisSlave = null;

	/**
	 * Get redis salve server.If fail,throw a ErrorException.
	 * @return Redis
	 */
	public function getRedisSlave()
	{
		if($this->_redisSlave instanceof Redis)
		{
			return $this->_redisSlave;
		}
		else
		{
			$this->_redisSlave = new Redis;
			try
			{
				$this->_redisSlave->connect($this->_configSlave['host'], $this->_configSlave['port']);
				$this->_redisSlave->setOption(Redis::OPT_PREFIX, $this->_redisKeyPrefix);
			}
			catch(Exception $e)
			{
				$this->errorShow();
				throw new ErrorException("Connect redis server " . implode(",", $this->_configSlave) . " fail !");
			}
			return $this->_redisSlave;
		}
	}

	/**
	 * @var $_cmdScopeMaster master sever command scope
	 */
	private static $_cmdScopeMaster = array(
		'multi', 'exec', 'discard', 'watch', 'unwatch',
		//key - value structure
		'setex', 'psetex', 'setnx', 'del', 'delete', 'incr', 'incrBy',
		'incrByFloat', 'decr', 'decrBy',
		//list structrue
	    'lPop', 'rPop', 'blPop', 'brPop', 'lPush', 'rPush', 'lPushx', 'rPushx', 'lSet', 'lRem', 'lRemove', 'lInsert', 'lTrim', 'listTrim',
		//set structrue
		'sAdd', 'sRem', 'sRemove', 'sMove', 'sPop',
		//hash structrue
		'hSet', 'hSetNx', 'hDel', 'hIncrBy', 'hIncrByFloat', 'hMset',
		//transaction
		'multi', 'exec',
		//sorted set structrue
		'zAdd','zDelete','zDeleteRangeByRank', 'zCount','zRange','zRangeByScore',
		'expire',
		//server
		'info',

	);

	/**
	 * set master server commadn scope
	 * @param array $cmds
	 * @return void
	 */
	public function setCmdScopeMaster(array $cmds)
	{
		self::$_cmdScopeMaster = array_unique(array_merge(self::$_cmdScopeMaster, $cmds));
	}

	/**
	 * @var $_cmdScopeSlave slave sever command scope
	 */
	private static $_cmdScopeSlave = array(
		//key - value structure
		'exists', 'mGet', 'getMultiple',
		//list structure
		'lSize', 'lIndex', 'lGet', 'lRange',
		'lGetRange', 
		//set structrue
		'sIsMember', 'sContains', 'sCard', 'sSize', 'sRandMember', 'sMembers',
		//hash structrue
		'hGetAll', 'hGet', 'hLen', 'hKeys', 'hVals', 'hExists', 'hMGet',
		//sorted set structrue
		'zRevRange', 'zRevRangeByScore',
	);

	/**
	 * set slave server commadn scope
	 * @param array $cmds
	 * @return void
	 */
	public function setCmdScopeSlave(array $cmds)
	{
		self::$_cmdScopeSlave = array_unique(array_merge(self::$_cmdScopeSlave, $cmds));
	}

	/**
	 * set a key value
	 * @param string $key
	 * @param mixed $value
	 * @param int $expire
	 * @return bool
	 */
	public function set($key, $value, $expire = 0)
	{
		$value = serialize($value);
		$this->getRedisMaster();
		if($expire)
		{
			return $this->_redisMaster->setex($key, $expire, $value);
		}
		else
		{
			return $this->_redisMaster->set($key, $value);
		}
	}

	/**
	 * Get the value of a key
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		$this->getRedisSlave();
		return unserialize($this->_redisSlave->get($key));
	}

	/**
	 * Call Redis method use master or slave instance.If fail,throw a ErrorException.
	 * @return
	 */
	public function __call($name, $args)
	{
		if(in_array($name, self::$_cmdScopeMaster))
		{
			$this->getRedisMaster();
			return call_user_func_array(array($this->_redisMaster, $name), $args);
		}
		elseif(in_array($name, self::$_cmdScopeSlave))
		{
			$this->getRedisSlave();
			return call_user_func_array(array($this->_redisSlave, $name), $args);
		}
		else
		{
			throw new ErrorException("It is an invalidate method : {$name}!");
		}
	}

	/**
	 * Set redis resource to null when serializing
	 */
	public function __sleep()
	{
		$this->_redisMaster = $this->_redisSlave = null;
	}

	/**
	 * Set redis resource to null when destruct
	 */
	public function __destruct()
	{
		$this->_redisMaster = $this->_redisSlave = null;
	}
}
