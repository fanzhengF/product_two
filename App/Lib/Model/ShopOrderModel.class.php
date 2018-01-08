<?php

/**
 * 订单模型
 */

class ShopOrderModel extends Model{

    //银联手机支付
    const UNIONPAY_MOBILE = 3;

    //微信支付
    const WEIXIN_PAY      = 4;

    //支付宝支付
    const ALIPAY          = 6;

    static public function getPayTypes(){
        return array(
            self::UNIONPAY_MOBILE => '银联手机',
            self::WEIXIN_PAY      => '微信',
            self::ALIPAY          => '支付宝',
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
        if(!empty($where)){
            foreach ($where as $key => $value) {
                $new_where[$pre.'shop_order.'.$key] = $value;
            }
        }

        //默认显示status>0的.-1为删除
        $new_where[$pre.'user.status'] = array('gt',0);
        $this->where($new_where)->join($pre . 'user ON ' . $pre . 'user.id = ' . $pre . 'shop_order.shop_id ');
        if($onlyCount){
            return $this->count();
        }

        $fields = 'trade_id,supplier_id,company_id,shop_id,pay_id,user_name,user_tel,total_price,really_price,platform_rate,platform_price,supplier_price,pay_type,pay_time';
        $fields_array = explode(',', $fields);
        foreach ($fields_array as $key => $value) {
            $fields_array[$key] = $pre . 'shop_order.'.$value;
        }
        $fields = implode(',', $fields_array);
        $list   = $this->field($fields)
                       ->order($pre.'shop_order.id desc')
                       ->limit($firstRow , $listRows)
                       ->select();

        return $list;
    }

    /**
     * @var 订单统计
     */
    public function getStatistics($where,$id, $role_id){
        //获取用户权限的内置条件
        $access_search = $this->_statisticsAccessSearch($id, $role_id);
        //获取用户搜索条件
        $search = $this->_statistics_search($where);
        $where  = array_merge($search,$access_search);

        $pre = C("DB_PREFIX");
        $new_where = array();
        if(!empty($where)){
            foreach ($where as $key => $value) {
                $new_where[$pre.'shop_order.'.$key] = $value;
            }
        }
        //默认显示status>0的.-1为删除
        $new_where[$pre.'user.status'] = array('gt',0);

        $array = array();
        $array['count']        = $this->where($new_where)->join($pre . 'user ON ' . $pre . 'user.id = ' . $pre . 'shop_order.shop_id')->count();
        $array['really_price'] = $this->where($new_where)->join($pre . 'user ON ' . $pre . 'user.id = ' . $pre . 'shop_order.shop_id')->sum($pre .'shop_order.really_price');

        return $array;
    }

    private function _statistics_search($post){
        $map = array();

        //实体店/分公司/供应商只能出现一种
        if(is_numeric($post['shop_id'])){
            $map['shop_id'] = $post['shop_id'];
        }elseif(is_numeric($post['company_id'])){
            $map['company_id'] = $post['company_id'];
        }elseif(is_numeric($post['supplier_id'])){
            $map['supplier_id'] = $post['supplier_id'];
        }

        //交易时间开始
        if($post['start_time']){
            $map['pay_time'][] = array('egt', strtotime(trim($post['start_time'])));
        }

        //交易时间结束
        if($post['end_time']){
            $_end_time = trim($post['end_time']);
            $map['pay_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }

        //默认7天之内
        if(empty($post['end_time']) && empty($post['start_time'])){
            $map['pay_time'][] = array('egt', strtotime('-7 day'));
            $map['pay_time'][] = array('elt', time());
        }
        return $map;
    }

    /**
     * @var 一个统计默认的权限条件
     */
    private function _statisticsAccessSearch($id, $role_id){
        $where = array();
        $access_search = C('ACCESS_SEARCH.order_statistics');

        if(isset($access_search['select'][$role_id])){

            $key   = key($access_search['select'][$role_id]);
            $value = current($access_search['select'][$role_id]);

            //%s这种需要替换的
            if('%s' === $value){
                $where[$key] = $id;
            }
        }

        return $where;
    }

    /**
     * @var 搜索条件
     */
    private function _search($post){
        $map = array();

        //供应商
        if(is_numeric($post['supplier_id'])){
            $map['supplier_id'] = $post['supplier_id'];
        }

        //分公司
        if(is_numeric($post['company_id'])){
            $map['company_id'] = $post['company_id'];
        }

        //实体店
        if(is_numeric($post['shop_id'])){
            $map['shop_id'] = $post['shop_id'];
        }

        //订单号
        if($post['trade_id']){
            $map['trade_id'] = array('like', '%'.trim($post['trade_id']).'%');
        }

        //支付单号
        if($post['pay_id']){
            $map['pay_id'] = array('like', '%'.trim($post['pay_id']).'%');
        }

        //客户姓名
        if($post['user_name']){
            $map['user_name'] = array('like', '%'.trim($post['user_name']).'%');
        }

        //客户手机号
        if($post['user_tel']){
            $map['user_tel'] = array('like', '%'.trim($post['user_tel']).'%');
        }

        //支付渠道
        $pay_types = array_keys(self::getPayTypes());
        if(in_array($post['pay_type_id'], $pay_types)){
            $map['pay_type'] = $post['pay_type_id'];
        }

        //交易时间开始
        if($post['start_time']){
            $map['pay_time'][] = array('egt', strtotime(trim($post['start_time'])));
        }

        //交易时间结束
        if($post['end_time']){
            $_end_time = trim($post['end_time']);
            $map['pay_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }

        return $map;
    }

    /**
     * @var 一个默认的权限条件
     */
    private function _accessSearch($id, $role_id){
        $where = array();
        $access_search = C('ACCESS_SEARCH');

        if(isset($access_search['order_index']['select'][$role_id])){

            $key   = key($access_search['order_index']['select'][$role_id]);
            $value = current($access_search['order_index']['select'][$role_id]);

            //%s这种需要替换的
            if('%s' === $value){
                $where[$key] = $id;
            }
        }

        return $where;
    }


    /**
     * @var 获取订单统计tab显示
     */
    static public function getTabView($business_name,$role_id){
        $tab_view = array(
            'business_name' => '',
            'supplier'      => 0,
            'company'       => 0,
            'shop'          => 0
            );

        $access_search = C('ACCESS_SEARCH.order_statistics');
        $tab_view_default = $access_search['tab_view_default'][$role_id];

        if(I('get.search_type')){
            //因为js锁住搜索项有延迟bug,所以采用x_id方式
            $DA = D('Access');
            $field = 'business_name';
            if(I('get.shop_id') > 0){
                $where = array('id' => I('get.shop_id'));
                $tab_view['shop'] = 1;
            }elseif(I('get.company_id') > 0){
                $where = array('id' => I('get.company_id'));
                $tab_view['company'] = 1;
            }elseif(I('get.supplier_id') > 0){
                $where = array('id' => I('get.supplier_id'));
                $tab_view['supplier'] = 1;
            }
            $info = $DA->getOne($field,$where);
            $tab_view['business_name'] = $info['business_name'];
        }else if(isset($access_search['tab_view_default'][$role_id]) && !empty($tab_view_default)){
            $tab_view[$tab_view_default] = 1;
            $tab_view['business_name']   = $business_name;
        }

        return $tab_view;
    }
}