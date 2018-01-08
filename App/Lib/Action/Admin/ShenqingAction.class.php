<?php
/**
 * 会议申请审批
 */

class ShenqingAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数



    //申请会议列表
    public function index() {
        $Mt = M("Meeting");
        if (I('get.id')) {
            $map['m_u_name'] = I('get.id');
            $count = $Mt->where($map)->count();
            import("ORG.Util.Page"); //载入分页类
            $page = new Page($count, self::PAGE_PER_NUM);
            $showPage = $page->show();
            $list = $Mt->where($map)->order('time DESC')->limit($page->firstRow, $page->listRows)->select();
            $M = M('MMt');
            $Mg = M('MGrade');
            $Mu = M('MUser');
            $Ms = M('MSignup');
            $Mr = M('MRoom');
            $MM = M("MMechanism");
            foreach ($list as $k => $v) {
                /*
                $list[$k]['m_type'] = $M->where(array('mt_id' => $v['m_type']))->find()['mt_name'];
                $list[$k]['m_grade'] = $Mg->where(array('g_id' => $v['m_grade']))->find()['g_name'];
                $list[$k]['m_xuefen'] = $Mg->where(array('g_id' => $v['m_grade']))->find()['g_xuefen'];
                $list[$k]['m_address'] = $Mr->where(array('room_name' => $v['m_rname']))->find()['room_address'];
                $MMaLL = $MM->where(array('m_id' => $v['m_jigou']))->find()['m_name'];
                $list[$k]['m_jigou'] = $MMaLL;
                */
            }
            //print_r($list);exit;
            $this->assign("page", $showPage);
            $this->assign("list", $list);
            $this->display();
        }
    }








    /**
 * @var 未审批
 */
    public function wei() {
        $D = D("Meeting");
        $count = $D->getList(I('get.'), 0, 0,1,0,'','',1,2);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,0,'','',1,2);
        $M = M('MMt');
        $Mg = M('MGrade');
        $Mu = M('MUser');
        $Ms = M('MSignup');
        $Mr = M('MRoom');
        $MM = M("MMechanism");
        foreach ($list as $k=>$v){
            $list[$k]['m_type'] = $M->where(array('mt_id'=>$v['m_type']))->find()['mt_name'];
            $list[$k]['m_grade'] = $Mg->where(array('g_id'=>$v['m_grade']))->find()['g_name'];
            $list[$k]['m_xuefen'] = $Mg->where(array('g_id'=>$v['m_grade']))->find()['g_xuefen'];
            $list[$k]['m_address'] = $Mr->where(array('room_name'=>$v['m_rname']))->find()['room_address'];
            $MMaLL = $MM->where(array('m_id'=>$v['m_jigou']))->find()['m_name'];
            $list[$k]['m_jigou'] = $MMaLL;
        }
        //print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->display();
    }


    /**
     * @var 已审批
     */
    public function yi() {
        $D = D("Meeting");
        $count = $D->getList(I('get.'), 0, 0,1,0,'','',1,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,0,'','',1,1);
        $M = M('MMt');
        $Mg = M('MGrade');
        $Mu = M('MUser');
        $Ms = M('MSignup');
        $Mr = M('MRoom');
        $MM = M("MMechanism");
        foreach ($list as $k=>$v){
            $list[$k]['m_type'] = $M->where(array('mt_id'=>$v['m_type']))->find()['mt_name'];
            $list[$k]['m_grade'] = $Mg->where(array('g_id'=>$v['m_grade']))->find()['g_name'];
            $list[$k]['m_xuefen'] = $Mg->where(array('g_id'=>$v['m_grade']))->find()['g_xuefen'];
            $list[$k]['m_address'] = $Mr->where(array('room_name'=>$v['m_rname']))->find()['room_address'];
            $MMaLL = $MM->where(array('m_id'=>$v['m_jigou']))->find()['m_name'];
            $list[$k]['m_jigou'] = $MMaLL;
        }
        //print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->display();
    }


    public function edit(){
        $D = D("Meeting");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            if (empty($_POST['m_number']) ) {
                $this->error('请您填写会议人数！');exit;
            }
            try {
                if (!empty($_FILES['m_images']['name'])) {
                    $file_name = $this->upload($_FILES['m_images'],'images');
                    $_POST['m_images'] = $file_name;
                }
                if (!empty($_FILES['m_ziliao']['name'])) {
                    $file_name_zilaio = $this->upload($_FILES['m_ziliao'],'ziliao','file');
                    $_POST['m_ziliao'] = $file_name_zilaio;

                }

                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    //echo $D->getLastSql();exit;
                    if($re){
                        $this->success("修改成功",U("/Admin/Shenqing/yi"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Shenqing/yi"));
            }
        }
        /*所属机构*/
        $MM = D("MMechanism");
        $MMaLL = $MM->where(array('m_parent'=>0))->select();
        $arr = array();
        foreach ($MMaLL as $k=>$v){
            $MMaLLSub = $MM->where(array('m_parent'=>$v['m_id']))->select();
            $MMaLL[$k]['m_sub'] = $MMaLLSub;

        }
        $Mr = D("MRoom"); //所属会议室
        $this->assign('MMall', $MMaLL);
        $data = $D->find(I("get.id"));
        $M = M('MMt');
        $mtAll = $M->select();
        $this->assign('mtAll', $mtAll);
        $Mg = M('MGrade');
        $gradeAll = $Mg->select();
        $this->assign('gradeAll', $gradeAll);
        $this->assign('MrAll', $Mr->select());
        $this->assign('data',$data);
        $this->display();
    }






}