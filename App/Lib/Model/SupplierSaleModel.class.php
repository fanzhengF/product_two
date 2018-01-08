<?php

/**
 * 供应商优惠模型
 */

class SupplierSaleModel extends Model{

    //每满减
    const EVERYFULLCUT = 1;

    //折扣
    const DISCOUNT     = 2;

    /**
     * @var 自动验证字段
     */
    protected $_validate=array(
            array("supplier_id","require","供应商不能为空"),
            array("type","number","承诺优惠不能为空",self::MUST_VALIDATE,'',self::MODEL_BOTH),
            array("rule","require","优惠细则不能为空",self::MUST_VALIDATE,'',self::MODEL_BOTH),
    );

    /**
     * @var 获取优惠类型,暂时不用语言包方式
     */
    static public function getSaleType($language = 'zh-cn'){
        return  array(
            self::EVERYFULLCUT => $language == 'zh-cn' ? '每满减' : 'everyFullCut',
            self::DISCOUNT     => $language == 'zh-cn' ? '折扣'   : 'discount'
            );
    }

    /**
     * @var 查找
     */
    public function getList($where, $firstRow = 0, $listRows = 20,$onlyCount=0,$id, $role_id){
        //获取用户权限的内置条件
        $access_search = $this->_accessSearch($id, $role_id);
        //获取用户搜索条件
        $search = $this->_search($where);
        $where  = array_merge($search,$access_search);
        $pre = C("DB_PREFIX");
        $new_where = array();
        if(!empty($where) && key($where)){
            foreach ($where as $key => $value) {
                $new_where[$pre.'supplier_sale.'.$key] = $value;
            }
        }

        //默认显示status>0的.-1为删除
        $new_where[$pre.'user.status'] = array('gt',0);
        $this->where($new_where)->join($pre . 'user ON ' . $pre . 'user.id = ' . $pre . 'supplier_sale.supplier_id ');
        if($onlyCount){
            return $this->count();
        }

        $fields = 'id,supplier_id,type,rule';
        $fields_array = explode(',', $fields);
        foreach ($fields_array as $key => $value) {
            $fields_array[$key] = $pre . 'supplier_sale.'.$value;
        }
        $fields = implode(',', $fields_array);
        $list   = $this->field($fields)
                       ->order($pre . 'supplier_sale.id desc')
                       ->limit($firstRow , $listRows)
                       ->select();
        return $list;
    }

    /**
     * @var 搜索条件
     * @todo 搜索
     */
    private function _search($where){
        $map = array();

        //supplier_id in

        return $map;
    }

    /**
     * @var 一个默认的权限条件
     */
    private function _accessSearch($id, $role_id){
        $where = array();
        $access_search = C('ACCESS_SEARCH');

        if(isset($access_search['supplier_sale']['select'][$role_id])){

            //暂时只支持一维数组规则
            $key   = key($access_search['access']['select'][$role_id]);
            $value = current($access_search['access']['select'][$role_id]);

            //%s这种需要替换的
            if('%s' === $value){
                $where[$key] = $id;
            }else{
                $where[$key] = $value;
            }
        }

        return $where;
    }

    /**
     * @var 查找一条
     */
    public function getOne($field,$where){
        return $this->field($field)->where($where)->find();
    }


}