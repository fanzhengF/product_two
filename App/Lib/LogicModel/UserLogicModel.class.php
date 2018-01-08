<?php

/*
 * 用户LogicModel
 * */

class UserLogicModel {

	static public function getInfo() {
		return $_SESSION['my_info'];
	}

	/**
	 * 获取当前用户Uid
	 * @return int
	 */
	static public function getUid() {
		return intval($_SESSION['my_info']['aid']);
	}

	/**
	 *
	 * @param type $GCA //分组.控制器.操作 例如:Home.Index.index
	 * @param type $uid //用户Id
	 * @return boolean
	 */
	static public function getAuth($GCA, $uid = FALSE) {
        $GCA = strtoupper($GCA);
        $GCA = str_replace("/",".",$GCA);
		list($Group, $Model, $Action) = explode('.', $GCA);
        //var_dump(RBAC::AccessDecision($Group,$Model,$Action),"---",$GCA,"<br />");
		if(RBAC::AccessDecision($Group,$Model,$Action)){
            return true;
        }else{
            return false;
        }
	}

}

?>