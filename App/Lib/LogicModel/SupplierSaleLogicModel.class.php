<?php

/**
 * 供应商优惠
 */

class SupplierSaleLogicModel extends Model{

    /**
     * @var 根据承诺优惠和优惠细则获取相应的中文解释
     */
    static public function getTypeZhByTypeAndRuleJson($type,$role_json){
        $function = SupplierSaleModel::getSaleType('en-us')[$type].'Zh';
        return self::$function($role_json);
    }

    /**
     * @var 根据优惠细则获取每满减中文解释
     */
    static private function everyFullCutZh($rule_json){
        $result = '';
        $rule   = json_decode($rule_json,true);
        if(is_array($rule)){
            $rule_one_key = current(array_keys($rule));
            $rule_one_value = current($rule);
            $result = '每满' . $rule_one_key . '减' . $rule_one_value;
        }

        return $result;

    }

    /**
     * @var 根据优惠细则获取折扣中文解释
     */
    static private function discountZh($rule_json){
        $result = '';
        $rule   = json_decode($rule_json,true);
        if(is_array($rule)){
            $discount = sprintf("%.1f",$rule['discount']*10);
            $result   = $discount. '折';
        }
        return $result;
    }

    /**
     * @var 获取处理过的新增信息
     */
    static public function getInsertData($operator,$data){
        $result = array();

        $sale_type = SupplierSaleModel::getSaleType('en-us');
        $sale      = array_keys($sale_type);

        //供应商优惠设置单一检测
        if($operator['role_id'] == AccessModel::SUPPLIER){
            $data['supplier_id'] = $operator['id'];
        }elseif($operator['role_id'] != AccessModel::SUPPLER && $operator['role_id'] != AccessModel::PLATFORM){
            throw new Exception("没有供应商优惠设置权限", 1);
        }
        $DSS = D('SupplierSale');
        $result_ss = $DSS->getOne('id',array('supplier_id'=>$data['supplier_id']));
        if(!empty($result_ss)){
            throw new Exception("只能设置一个承诺优惠", 1);
        }
        $result['supplier_id'] = $data['supplier_id'];

        //承诺优惠类型检测
        if(!in_array($data['sale_type'], $sale)){
            throw new Exception("承诺优惠选择错误", 1);
        }
        $result['type'] = $data['sale_type'];

        $rule = self::{'_'.$sale_type[$data['sale_type']]}($data);

        $result['rule'] = $rule;

        return $result;
    }


    /**
     * @var 获取处理过的修改信息
     */
    static public function getUpdateData($data){
        $result = array();
        $result['id'] = $data['id'];

        $sale_type = SupplierSaleModel::getSaleType('en-us');
        $sale      = array_keys($sale_type);
        if(!in_array($data['sale_type'], $sale)){
            throw new Exception("承诺优惠选择错误", 1);
        }
        $result['type'] = $data['sale_type'];

        $rule = self::{'_'.$sale_type[$data['sale_type']]}($data);

        $result['rule'] = $rule;

        return $result;
    }

    /**
     * @var 获取每满减格式规则
     */
    static private function _everyFullCut($data){
        $result = array();
        if(!is_numeric($data['everyfull']) || $data['everyfull'] < 0){
            throw new Exception("请输入正确的每满金额", 1);
        }
        if(!is_numeric($data['cut']) || $data['cut'] < 0){
            throw new Exception("请输入正确的减金额", 1);
        }
        if($data['cut'] >= $data['everyfull']){
            throw new Exception("减少的金额必须小于每满的金额", 1);
        }
        $result[$data['everyfull']] = $data['cut'];

        return jsonEncode($result);
    }

    /**
     * @var 获取折扣格式规则
     */
    static private function _discount($data){
        $result = array();
        if(!is_numeric($data['discount']) || $data['discount'] < 0){
            throw new Exception("请输入正确的折扣", 1);
        }
        $result['discount'] = round($data['discount'] / 10,2);

        return jsonEncode($result);
    }

    /**
     * @var 根据id获取供应商优惠设置编辑信息
     */
    static public function getEditInfoById($id){
        $field = 'id,supplier_id,type,rule';
        $where['id'] = $id;

        $DSS = D('SupplierSale');
        $result_ss = $DSS->getOne($field,$where);

        if(empty($result_ss)){
            throw new Exception("不存在该供应商优惠设置", 1);
        }

        $where['id'] = $result_ss['supplier_id'];
        $field       = 'business_name';
        $D = D("Access");
        $user_info = $D->getOne($field,$where);
        if(empty($user_info)){
            throw new Exception("不存在该供应商", 1);
        }
        $result_ss['supplier_name'] = $user_info['business_name'];
        $result_ss['rule_array']    = json_decode($result_ss['rule'],true);

        return $result_ss;
    }

    /**
     * 根据操作者获取是否可以编辑
     */
    static public function getIsEdit($operator,$data){
        //操作者是供应商必须和操作数据中的supplier_id一致
        if($operator['role_id'] == AccessModel::SUPPLIER){
            if($operator['id'] != $data['supplier_id']){
                throw new Exception("没有此供应商优惠设置权限", 1);
            }
        }elseif($operator['role_id'] != AccessModel::SUPPLER && $operator['role_id'] != AccessModel::PLATFORM){
            throw new Exception("没有供应商优惠设置权限", 1);
        }
    }

}