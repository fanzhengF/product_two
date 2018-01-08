<?php
/**
 * 校外管理
 */

class XiaowaiAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 人员列表
     */
    public function index() {
        $Mu = D("MUser");
        $count = $Mu->getList(I('get.'), 0, 0,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $Mu->getList(I('get.'),$page->firstRow, $page->listRows,0);
        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->display();
    }



    public function delete(){
        $Mu = D("MUser");
        $re =$Mu->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Xiaowai/index"));
        }else{
            $this->error("删除失败",U("/Admin/Xiaowai/index"));
        }
    }





}