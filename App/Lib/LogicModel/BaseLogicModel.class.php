<?php
class BaseLogicModel {

    /**
     * 测试数据的城市编码
     */
    const TEST_DATA_CITY_CODE = 'dyd';

    /**
     * 每页查询的记录数
     */
    const PAGE_PER_NUM = 20;

    /**
     * 导出记录上限
     */
    const EXPORT_NUM_LIMIT = 500;

    protected $error = array();

    public function __construct(){
		$this->__init();
    }
	public function __init(){
        import('RequestLogModel');
	}

    public function setError($msg,$error_code=1){
        //if(APP_DEBUG) file_put_contents("LogicModelError.log",file_get_contents("LogicModelError.log")."\r\n".$msg);
        $this->error[]=array('code'=>$error_code,'msg'=>$msg);
        $RequestLogModel = D('RequestLog');
        $RequestLogModel->writeLog("LogicApiError",
                                GROUP_NAME."_".MODULE_NAME."_".ACTION_NAME."--->".get_class($this),
                                "",
                                0,
                                microtime(true),
                                $msg);


    }
    public function hasError(){
        return !empty($this->error);
    }
    public function getError(){
        $count = count($this->error);
        $error = array();
        if($count){
            $error = $this->error[$count-1];
        }
        return $error;
    }
    public function getAllError(){
        return $this->error;
    }
    public function clearError(){
        $this->error = array();
    }
    public static function postUrl($url, $post_fields, $timeout = 3, $host_ip = null) {
        $ch = curl_init();
        if (!is_null($host_ip))
        {
            $urldata = parse_url($url);
            if (!empty($urldata['query']))
            {
                $urldata['path'] .= "?" . $urldata['query'];
            }
            $headers = array("Host: " . $urldata['host']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $url = $urldata['scheme'] . "://" . $host_ip . $urldata['path'];
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $post_data = http_build_query($post_fields);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $ret = curl_exec($ch);
        if ($_GET['debugtest'])
        {
            echo "=====post fields======\r\n";
            var_dump($post_fields);

            echo "=====post data======\r\n";
            var_dump($post_data);

            echo '=====info====='."\r\n";
            print_r( curl_getinfo($ch) );

            echo '=====$response====='."\r\n";
            print_r( $ret );
        }
        curl_close($ch);
        return $ret;
    }

    /**
     *
     * @param int $code
     *
     */
    protected function setModelErrCode($code) {
        $model = D('Base');
        $model->setErrCode($code);
        $this->setError($model->getErrMsg(), $model->getErrCode());
    }
    /*public function __destruct(){
        if($this->hasError()){
            $RequestLogModel = D('RequestLog');
            $RequestLogModel->writeLog("LogicApiError", __CLASS__, "", 0, microtime(true), var_export($this->getAllError(),true));
        }
    }*/

    /**
     * 根据合同号获取合同信息
     * @param array $contractSn
     *
     * @return array 格式
     *            [
     *
     *            ]
     */
    public function getContractInfoByConstractSn($contractSn) {
        $contractSn = array_unique($contractSn);
        import("RequestLogModel");
        $curlInfo = $this->postUrl(C('GZ_DOMAIN') . 'Api/GetContractInfo/contractInfoList',
                array('data' => json_encode($contractSn)));
        $contractInfo = json_decode($curlInfo, true);
        if (!$contractInfo || 1 != $contractInfo['status']) {
            RequestLogModel::commonLog(
                array('data' => $curlInfo, 'function' => 'getContractInfoByConstractSn',
                    'msg' => 'curl 获取合同信息失败'));
            return array();
        }
        return $contractInfo['data'];
    }

    /**
     * 组建where条件
     * @param array $whereInfo
     * @param string $delimiter 键的分隔符，默认为 ‘-’
     *
     * @return array
     */
    protected function condition($whereInfo, $delimiter = '-') {
        $where = array();
        foreach ($whereInfo as $key => $val) {
            if (empty($val)) {
                continue;
            }
            $temp = explode($delimiter, $key);
            if (1 == count($temp)) {
                $where[$key] = array('eq', $val);
                continue;
            }
            switch ($temp[1]) {
                case 'eq':
                case 'gt':
                case 'egt':
                case 'lt':
                case 'elt':
                    $where[$temp[0]] = array($temp[1], $val);
                    break;
                case 'like':
                    $where[$temp[0]] = array($temp[1], "$val%");
                    break;
            }
        }
        return $where;
    }

}