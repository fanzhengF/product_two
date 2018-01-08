<?php

/**
 * 基础model
 */
class BaseModel extends Model {

    /**
     * @var int 错误状态码 1000代表正常状态
     */
    private $errCode = 1000;

    /**
     *
     * @var string 错误描述, 允许自定义错误消息提示
     */
    protected $errMsg = '';

    /**
     *
     * @var string 主数据库连接dns
     */
    protected static $masterDns;

    /**
     * 使用redis来保存上次时间
     */
    const REDIS_KEY_DATE = 'bss_order_logicid_last_date';

    /**
     * 上次使用的Num key的前缀
     */
    const REDIS_KEY_ORDER_PREFIX = 'bss_order_logicid_last_order_num';

    /**
     * 产生顺序值
     *
     * @param string $orderType 订单标记
     *                  RO 代表退单
     *
     * @return string 生成规则
     *           时间：Ymd  +  $orderType + 6位数  +   His
     *
     * 注意：当redis失败时， 最后的6位为随机数
     */
    public static function genSequenceStr($orderType) {
        $redis = NewRedis::master();
        $dateTime = date('YmdHis');
        $date = substr($dateTime, 0, 8);
        $time = substr($dateTime, 8);
        $rs = $redis->get(self::REDIS_KEY_DATE);
        if (!$rs) {
            $redis->set(self::REDIS_KEY_DATE, $date);
            $rs = $date;
        }
        $key = self::getRedisSequenKey($orderType);
        if ($date != $rs) {
            $redis->set(self::REDIS_KEY_DATE, $date);
            $redis->set($key, 0);
        }
        $num = $redis->INCR($key);
        if (!$num) {
            $num = mt_rand(0, 999998);
        }
        if (999999 <= $num) {
            $redis->set($key, 0);
            $num = $redis->INCR($key);
        }
        return $date . $orderType . str_pad($num, 6, '0', STR_PAD_LEFT) . $time;
    }

    protected static function getRedisSequenKey($orderType) {
        $key = self::REDIS_KEY_ORDER_PREFIX;
        switch (strtoupper($orderType)) {
            case 'RO':
                $key .= 'RO';
                break;
            case 'CO':
                $key .= 'CO';
                break;
            case 'BO':
                $key .= 'BO';
                break;
            default :
                $key .= 'COMMON';
        }
        return $key;
    }

    /**
     * 获取自定义错误状态码
     * 1000代表无错误
     *
     * @return int
     */
    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($code) {
        $this->errCode = $code;
        $this->errMsg = $this->errorInfo($code);
    }

    /**
     * 切换到主库
     */
    protected function masterDB(){
        if(!self::$masterDns){
            $db_type = C('DB_TYPE');
            $db_host = trim(current(explode(',',C('DB_HOST'))));
            $db_name = trim(current(explode(',',C('DB_NAME'))));
            $db_user = trim(current(explode(',',C('DB_USER'))));
            $db_pwd = trim(current(explode(',',C('DB_PWD'))));
            $db_port = trim(current(explode(',',C('DB_PORT'))));
            self::$masterDns = "{$db_type}://{$db_user}:{$db_pwd}@{$db_host}:{$db_port}/{$db_name}";
        }
        return $this->db(10086, self::$masterDns);
    }

    /**
     * 获取自定义错误消息
     *
     * @return string
     */
    public function getErrMsg() {
        return $this->errMsg;
    }

    /**
     * 错误消息对照表
     * @param int $code
     *
     * @return string
     */
    public function errorInfo($code) {
        $rs = array(
            1000 => '操作正常',
            1001 => '添加失败',
            1002 => '更新失败',
            1003 => '数据查询失败或记录不存在',

            2001 => '必填项为空',
            2002 => '订单中存在不满足下限的数据',
            2003 => '订单中不存在相应的商品记录或出库数量与订单中的商品数量不一致',
            2004 => '所传数据与已有数据不一致',
            2005 => '数据状态错误',
            2006 => '仓库预占失败',
            2007 => '合同中存在未完成的订单',
            2008 => '释放仓库予占失败',
            2009 => '下单数据中含有不同仓库的信息',

            3001 => '签名验证失败',
            3002 => '查询条件为空',
            3003 => '非合法的订单类型',
            3004 => '非法操作',

            4000 => '系统维护中。。。',

            5005 => '订单已全退',

            6000 => '发券成功',
            6001 => '无待发券合同',
            6002 => '发券失败',
            6003 => '该合同已发放代金券'
        );
        return isset($rs[$code]) ? $rs[$code] : '';
    }
}
