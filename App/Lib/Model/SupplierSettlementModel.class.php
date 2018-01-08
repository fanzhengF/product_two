<?php

/**
 * 供应商结算模型
 */

class SupplierSettlementModel extends Model{

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
        if(!empty($where)){
            foreach ($where as $key => $value) {
                $new_where[$pre.'supplier_settlement.'.$key] = $value;
            }
        }
        //默认显示status>0的.-1为删除
        $new_where[$pre.'user.status'] = array('gt',0);
        $this->where($new_where)->join($pre . 'user ON ' . $pre . 'user.id = ' . $pre . 'supplier_settlement.supplier_id ');
        if($onlyCount){
            return $this->count();
        }

        $fields = 'id,supplier_id,supplier_name,serial_number,collect_money,collect_money_time';
        $fields_array = explode(',', $fields);
        foreach ($fields_array as $key => $value) {
            $fields_array[$key] = $pre . 'supplier_settlement.'.$value;
        }
        $fields = implode(',', $fields_array);
        $list   = $this->field($fields)
                       ->order($pre . 'supplier_settlement.id desc')
                       ->limit($firstRow , $listRows)
                       ->select();

        return $list;
    }

    /**
     * @var 搜索条件
     * @todo 搜索
     */
    private function _search($post){
        $map = array();

        //供应商ID
        if($post['supplier_id']){
            $map['supplier_id'] = $post['supplier_id'];
        }

        //供应商名称
        if($post['supplier_name']){
            $map['supplier_name'] = array('like', '%'.trim($post['supplier_name']).'%');
        }

        //收款流水号
        if($post['serial_number']){
            $map['serial_number'] = array('like', '%'.trim($post['serial_number']).'%');
        }

        //收款时间开始
        if($post['start_time']){
            $map['collect_money_time'][] = array('egt', strtotime(trim($post['start_time'])));
        }
        //收款时间结束
        if($post['end_time']){
            $_end_time = trim($post['end_time']);
            $map['collect_money_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }

        return $map;
    }

    /**
     * @var 一个默认的权限条件
     */
    private function _accessSearch($id, $role_id){
        $where = array();
        $access_search = C('ACCESS_SEARCH');

        if(isset($access_search['supplier_settlement']['select'][$role_id])){

            $key   = key($access_search['supplier_settlement']['select'][$role_id]);
            $value = current($access_search['supplier_settlement']['select'][$role_id]);

            //%s这种需要替换的
            if('%s' === $value){
                $where[$key] = $id;
            }
        }

        return $where;
    }


}