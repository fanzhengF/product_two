<?php
/**
 * 学分管理
 */

class CreditAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 学分列表
     */
    public function index() {
        $D = D("MCredit");
        $user =  M('MUser');
        $meeting =  M('Meeting');
        $ms =  M('MStudent');
        $mt =  M('MTeacher');
        $msi =  M('MStudentInfo');
        $count = $D->getList(I('get.'), 0, 0,1,0,0);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,0,0);
        foreach ($list as $k=>$v){
            $_user = $user->where(array('u_id'=>$v['c_uid']))->find()['u_name'];
            $list[$k]['u_name'] = $user->where(array('u_id'=>$v['c_uid']))->find()['u_name'];

            $msName =  $ms->where(array('s_no'=>$_user))->find()['s_name'];
            $mt_Name =  $mt->where(array('t_no'=>$_user))->find()['t_name'];
            if (!empty($msName)) {
                $_name = $msName;
            }
            if (!empty($mt_Name)) {
                $_name = $mt_Name;
            }
            $list[$k]['m_name'] = $_name;

            $list[$k]['s_department'] = $ms->where(array('s_no'=>$v['c_no']))->find()['s_department'];
            $list[$k]['s_major'] = $msi->where(array('s_no'=>$v['c_no']))->find()['s_major'];
        }
       // print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->display();
    }
    public function index2() {
        $D = D("MCredit");
        $user =  M('MUser');
        $meeting =  M('Meeting');
        $ms =  M('MStudent');
        $mt =  M('MTeacher');
        $msi =  M('MStudentInfo');
        $count = $D->getList(I('get.'), 0, 0,1,0,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,0,1);
        foreach ($list as $k=>$v){
            $_user = $user->where(array('u_id'=>$v['c_uid']))->find()['u_name'];
            $list[$k]['u_name'] = $user->where(array('u_id'=>$v['c_uid']))->find()['u_name'];

            $msName =  $ms->where(array('s_no'=>$_user))->find()['s_name'];
            $mt_Name =  $mt->where(array('t_no'=>$_user))->find()['t_name'];
            if (!empty($msName)) {
                $_name = $msName;
            }
            if (!empty($mt_Name)) {
                $_name = $mt_Name;
            }
            $list[$k]['m_name'] = $_name;

            $list[$k]['s_department'] = $ms->where(array('s_no'=>$v['c_no']))->find()['s_department'];
            $list[$k]['s_major'] = $msi->where(array('s_no'=>$v['c_no']))->find()['s_major'];
        }
        // print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->display();
    }



    public function add(){
        $D = D("MCredit");
        if(IS_POST){
            try {
                $_POST['m_time'] = time();
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                    if($re){
                        $this->success("添加成功",U("/Admin/Credit/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Credit/index"));
            }

        }

        $this->display();
    }






    public function edit(){
        $D = D("MCredit");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {

                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    if($re){
                        $this->success("修改成功",U("/Admin/Credit/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Credit/index"));
            }
        }
        $data = $D->find(I("get.id"));
        $this->assign('data',$data);
        $this->display();
    }


    public function delete(){
        $D = D("MCredit");
        $re =$D->delete(I("get.id"));

        if($re){
            $this->success("删除成功",U("/Admin/Credit/index"));
        }else{
            $this->error("删除失败",U("/Admin/Credit/index"));
        }
    }




    //补登会议和参加的会议
    public function canjia(){
        $ms = D("MSignup");
        $Mt =  M('Meeting');
        $M = M('MMt');
        $Mg = M('MGrade');
        $Mr = M('MRoom');
        $id = I('get.id');
        $array = array();
        $mid =  $ms->field('s_mid')->where(array('s_uid'=>$id))->select();
        foreach ($mid as $k=>$v){
            $array[$k] = $v['s_mid'];
        }
        $im = implode(',',$array);
        $count = $Mt->where(array('m_id'=>array('in',''.$im.'')))->count();
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $Mt_All = $Mt->where(array('m_id'=>array('in',''.$im.'')))->limit($page->firstRow , $page->listRows)->order('time DESC')->select();
        foreach ($Mt_All as $k=>$v) {
            $Mt_All[$k]['m_type'] = $M->where(array('mt_id' => $v['m_type']))->find()['mt_name'];
            $Mt_All[$k]['m_grade'] = $Mg->where(array('g_id'=>$v['m_grade']))->find()['g_name'];
            $Mt_All[$k]['m_address'] = $Mr->where(array('room_name'=>$v['m_rname']))->find()['room_address'];

        }
        //print_r($Mt_All);exit;
        $this->assign("page", $showPage);
        $this->assign('list',$Mt_All);
        $this->display();

    }



}