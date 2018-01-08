<?php
/**
 * 地址库 数据表
 */
class CityModel extends Model{
    /**
     * @var $_table_name 设置默认数据表
     */
    protected $tableName = "city";

    /**
     * @var status对应表字段
     */
    const STATUS_UESING  = 1;   //显示
    const STATUS_DISCARD = 0;   //不显示

    CONST ROOT_PID       = 0;//根

    /**
     * @var 获取开通的所有省
     */
    public function showProvince(){
        $where = array(
            'status' => self::STATUS_UESING,
            'pid'    => self::ROOT_PID
            );
        $this->where($where);
        $fields = 'id,name';
        $list   = $this->field($fields)
                       ->select();

        return $list;
    }

    /**
     * @var 根据省id查询所有属于市id
     */
    public function showCityByProvince($province_id){
        $where = array(
            'status' => self::STATUS_UESING,
            'pid'     => $province_id
            );
        $this->where($where);
        $fields = 'id,name';
        $list   = $this->field($fields)
                       ->select();

        return $list;
    }

    /**
     * @var 根据市id查询上级id
     */
    public function showProvinceByCity($city_id){
        $where = array(
            'status' => self::STATUS_UESING,
            'id'     => $city_id
            );
        $this->where($where);
        $fields = 'pid';
        $pid   = $this->field($fields)
                       ->find();

        return $pid['pid'] ? $pid['pid']: 0;
    }


}