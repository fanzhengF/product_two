<?php

/**
 * @author 栾涛 <286976625@qq.com>
 */
class NewRedis extends Redis {

	private static $_instance_master;
	private static $_instance_slave;

	public static function master() {
		if (!(self::$_instance_master instanceof self)) {
			self::$_instance_master = new self;
			try {
				list($redis_addr, $redis_port) = explode(':', $_SERVER['SINASRV_REDIS_HOST']);
				self::$_instance_master->connect($redis_addr, $redis_port);
			} catch (Exception $e) {
				//$this->errorShow();
				//throw new ErrorException("Connect redis server " . implode(",", $this->_configMaster) . " fail !");
				throw new ErrorException("Connect redis server master fail !");
			}
			return self::$_instance_master;
		}
		return self::$_instance_master;
	}

	public static function slave() {
		if (!(self::$_instance_slave instanceof self)) {
			self::$_instance_slave = new self;
			try {
				list($redis_addr, $redis_port) = explode(':', $_SERVER['SINASRV_REDIS_HOST_R']);
				self::$_instance_slave->connect($redis_addr, $redis_port);
			} catch (Exception $e) {
				//$this->errorShow();
				//throw new ErrorException("Connect redis server " . implode(",", $this->_configMaster) . " fail !");
				throw new ErrorException("Connect redis server slave fail !");
			}
			return self::$_instance_slave;
		}
		return self::$_instance_slave;
	}

}
