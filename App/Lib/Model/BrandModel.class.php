<?php

/**
 * 品牌模型
 */

class BrandModel extends Model
{
    /**
     * @var $_table_name 定义默认表名
     */
    protected $tableName = "brand";

    /**
     * @var 自动验证字段
     */
    protected $_validate=array(
            array("name","require","品牌名字不能为空"),
            array('name','','品牌添加重复',0,'unique',self::MODEL_BOTH),
    );

    public function getAllList(){
        $fields = 'id,name,create_time';
        $list   = $this->field($fields)
                       ->order("id desc")
                       ->select();

        return $list;
    }


    /**
     * @var 查找品牌
     */
    public function getList($where, $firstRow = 0, $listRows = 20,$onlyCount=0){
        //获取用户搜索条件
        $where = $this->_search($where);
        $this->where($where);
        if($onlyCount){
            return $this->count();
        }

        $fields = 'id,name,create_time';
        $list   = $this->field($fields)
                       ->order("id desc")
                       ->limit($firstRow , $listRows)
                       ->select();

        return $list;
    }

    private function _search($where){
        $map = array();
        //用户id
        if($where['id']){
            $map['id'] = $where['id'];
        }
        //负责人姓名
        if($where['name']){
            $map['name'] = array('like', '%'.trim($where['name']).'%');
        }

        return $map;
    }

    /**
     * @var 删除
     */
    public function toDelete($id = NULL){
        $this->startTrans();
        try {
            if($id <= 0 ){
                throw new Exception("id不存在", 1);
            }

            // //删除品牌
            $result_delete = $this->where(array('id'=>$id))->delete();
            if(false === $result_delete){
                throw new Exception("删除失败", 1);
            }

            //删除实体店的关联品牌
            $SBM = M('ShopBrand');
            $result = $SBM->where(array('brand_id'=>$id))->delete();
            if(false === $result_delete){
                throw new Exception("删除失败", 1);
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;//@todo 暂时先不返回原因
        }

    }

}