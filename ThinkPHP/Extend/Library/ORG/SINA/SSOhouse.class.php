<?php
/**
 * Leju house sso client
 * @author   zhiliang <zhiliang@jiaju.com>
 * @date 	 2012-10-30
 */


class SSOhouse {
    const COOKIE_LUE = 'LUE';   //leju house user encrypt info
    const COOKIE_LUP = 'LUP';   //leju house user plain info
    const COOKIE_PATH = '/';
	const IHOUSE_KEY = 'nn33DSQgqMd32CZo';//
	
	private $_error;
	private $_errno = 0;
	private $_arrKeyMap = 
		array(
			"bt"=>"begintime", // 登录时间
			"email"=>"email",//
			"f"=>"f",
			"loginname"=>"loginname",
			"mobile"=>"mobile",
			"nickname"=>"displayname",
			"user"=>"userid",
			"uid"=>"uniqueid",
			"ut"=>"ut",
		);

	public function __construct() {
	/*	if(!$this->_parseConfigFile($config)){
			throw new Exception($this->getError());
		}
     */
    }
    public function getCookie(&$arrUserInfo) {
		$sup = isset($_COOKIE[self::COOKIE_LUP]) ? $_COOKIE[self::COOKIE_LUP] : '';
		if(!$sup) return false;
		return $this->_auth($arrUserInfo);
	}

    /**
     * delete cookie
     */
	public function delCookie() {
		// 产品可以在这里删除自己的cookie
		return true;
	}

	private function _auth(&$arrUserInfo) {
        // 不存在密文cookie或明文cookie视为无效
        if( !isset($_COOKIE[self::COOKIE_LUE]) ||
            !isset($_COOKIE[self::COOKIE_LUP])) {
                $this->_setError('not all cookie are exists ');
                return false;
			}
		parse_str($_COOKIE[self::COOKIE_LUE],$arrLUE);
		parse_str($_COOKIE[self::COOKIE_LUP],$arrLUP);

		foreach( $arrLUP as $key=>$val) {
			if(!array_key_exists($key,$this->_arrKeyMap)) continue;
			$arrUserInfo[$this->_arrKeyMap[$key]] = $val;
		}

		// 检查加密cookie
		if($_COOKIE[self::COOKIE_LUE]!= md5($_COOKIE[self::COOKIE_LUP].self::IHOUSE_KEY)) {
			$this->_setError("encrypt string error");
			return false;
        }

		return true;
	}



	public function _setError($error,$errno=0) {
		$this->_error = $error;
		$this->_errno = $errno;
	}

	public function getError() {
		return $this->_error;
	}

	public function getErrno() {
		return $this->_errno;
	}
}

?>
