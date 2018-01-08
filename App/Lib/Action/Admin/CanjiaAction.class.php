<?php
/**
 * 参加管理
 */

class CanjiaAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 参加列表
     */
    public function index() {
        $D = D("MSignup");
        $user =  M('MUser');
        $meeting =  M('Meeting');

        $count = $D->getList(I('get.'), 0, 0,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0);
        foreach ($list as $k=>$v){
            $list[$k]['u_type'] = $user->where(array('u_id'=>$v['s_uid']))->find()['u_type'];
            $list[$k]['u_name'] = $user->where(array('u_id'=>$v['s_uid']))->find()['u_name'];
            $list[$k]['u_zhiye'] = $user->where(array('u_id'=>$v['s_uid']))->find()['u_zhiye'];
            $list[$k]['u_phone'] = $user->where(array('u_id'=>$v['s_uid']))->find()['u_phone'];
            $list[$k]['m_name'] = $meeting->where(array('m_id'=>$v['s_mid']))->find()['m_name'];
        }


        $this->assign('meetAll',$meeting->select());
       // print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }






    public function delete(){
        $D = D("MSignup");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Canjia/index"));
        }else{
            $this->error("删除失败",U("/Admin/Canjia/index"));
        }
    }




}