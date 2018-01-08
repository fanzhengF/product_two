<?php
/**
 * 供应商结算
 */


class SettlementAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 供应商结算列表
     */
    public function index() {
        $p = I('get.p',1);
        $D = D("SupplierSettlement");
        $count = $D->getList(I('get.'), 0, 0,1,$this->my_info['id'],$this->my_info['role_id']);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,$this->my_info['id'],$this->my_info['role_id']);

        if($this->my_info['role_id'] == AccessModel::SUPPLIER){
            $this->assign("is_supplier", 1);
        }

        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }


}