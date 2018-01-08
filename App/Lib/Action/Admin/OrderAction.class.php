<?php
/**
 * 订单管理
 */
import('AccessLogic');

class OrderAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 订单明细列表
     */
    public function index() {
        $p = I('get.p',1);
        $D = D("ShopOrder");
        $count = $D->getList(I('get.'), 0, 0,1,$this->my_info['id'],$this->my_info['role_id']);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,$this->my_info['id'],$this->my_info['role_id']);

        $pay_types = ShopOrderModel::getPayTypes();
        if(!empty($list)){
            $DA = D('Access');
            $field = 'business_name';
            foreach ($list as $key => $value) {
                //供应商名称
                $_supplier_where = array('id' => $value['supplier_id']);
                $_supplier = $DA->getOne($field,$_supplier_where);
                $list[$key]['supplier_name'] = $_supplier['business_name'];

                //分公司名称
                $_company_where = array('id' => $value['company_id']);
                $_company = $DA->getOne($field,$_company_where);
                $list[$key]['company_name'] = $_company['business_name'];

                //实体店名称
                $_shop_where = array('id' => $value['shop_id']);
                $_shop = $DA->getOne($field,$_shop_where);
                $list[$key]['shop_name'] = $_shop['business_name'];

                //支付渠道
                $list[$key]['pay_type_name'] = $pay_types[$value['pay_type']];

                //平台扣点比率
                $list[$key]['platform_rate'] = sprintf("%.2f",$value['platform_rate']*100);
            }
        }

        //控制相应搜索显示
        $access_search = C('ACCESS_SEARCH.order_index');
        $access = $access_search['search'][$this->my_info['role_id']];

        //分配相应的初始条件检索数据
        $data = AccessLogicModel::getInitUserListForOrderAction($access,$this->my_info['id']);

        //分公司/实体店明细看不到平台扣点比率、平台结算金额，供应商结算金额
        if($this->my_info['role_id'] == AccessModel::COMPANY|| $this->my_info['role_id'] ==AccessModel::SHOP){
            $this->assign("unview", 1);
        }
        $this->assign("page", $showPage);
        $this->assign("pay_types", $pay_types);
        $this->assign("access", $access);
        $this->assign("data", $data);
        $this->assign("list", $list);

        $this->display();
    }


    /**
     * @var 订单统计
     */
    public function statistics(){
        //这样便于不切换用户调试
        $use_user_info = array();
        $use_user_info['id']            = $this->my_info['id'];
        $use_user_info['role_id']       = $this->my_info['role_id'];
        $use_user_info['business_name'] = $this->my_info['business_name'];
        $D = D("ShopOrder");
        $statistics = $D->getStatistics(I('get.'),$use_user_info['id'],$use_user_info['role_id']);
        $statistics['count']        = $statistics['count'] ? $statistics['count'] :0 ;
        $statistics['really_price'] = $statistics['really_price'] ? $statistics['really_price'] :0 ;
        //控制相应搜索显示
        $access_search = C('ACCESS_SEARCH.order_index');
        $access = $access_search['search'][$use_user_info['role_id']];
        //分配相应的初始条件检索数据
        $data = AccessLogicModel::getInitUserListForOrderAction($access,$use_user_info['id']);
        //tab里相应显示的数据
        $tab_view = ShopOrderModel::getTabView($use_user_info['business_name'],$use_user_info['role_id']);
        $this->assign("access", $access);
        $this->assign("tab_view", $tab_view);
        $this->assign("data", $data);
        $this->assign("statistics", $statistics);
        $this->display();
    }


}