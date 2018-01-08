<?php
/**
 * 学分列表
 */

class XfAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 学分列表
     */
    public function wei() {
        $D = D("MCredit");
        $user =  M('MUser');
        $Mt =  M('Meeting');
        $ms =  M('MStudent');
        $mt =  M('MTeacher');
        $msi =  M('MStudentInfo');
        $msu =  M('MSignup');





        if($_GET['start_time']){
            $map['s_time'][] = array('egt', strtotime(trim($_GET['start_time'])));
        }

        if($_GET['end_time']){
            $_end_time = trim($_GET['end_time']);
            $map['s_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }


        if($_GET['s_name']){
            $map['s_name'] = array('like', '%'.trim($_GET['s_name']).'%');
        }


        if($_GET['s_no']){
            $map['s_no'] = array('like', '%'.trim($_GET['s_no']).'%');
        }
        if($_GET['s_mtype']){
            $map['s_mtype'] = array('like', '%'.trim($_GET['s_mtype']).'%');
        }

        $count = $msu->where($map)->count();
       // echo $msu->getLastSql();
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $msu->where($map)->limit($page->firstRow , $page->listRows)->order('s_time DESC')->select();
        foreach ($list as $k=>$v){
            $u_name = $user->where(array('u_id'=>$v['s_uid']))->find()['u_name'];
            $msName =  $ms->where(array('s_no'=>$u_name))->find()['s_name'];
            $mt_Name =  $mt->where(array('t_no'=>$u_name))->find()['t_name'];
            if (!empty($msName)) {
                $_name = $msName;
            }
            if (!empty($mt_Name)) {
                $_name = $mt_Name;
            }

            $list[$k]['s_major'] = $msi->where(array('s_no'=>$u_name))->find()['s_major'];
            $list[$k]['s_department'] = $ms->where(array('s_no'=>$u_name))->find()['s_department'];
            $list[$k]['no'] = $u_name;
            $list[$k]['s_name'] = $_name;
            $list[$k]['m_bianma'] = $Mt->where(array('m_id'=>$v['s_mid']))->find()['m_bianma'];
            $list[$k]['m_hyxf'] = $Mt->where(array('m_id'=>$v['s_mid']))->find()['m_hyxf'];
            $type = $Mt->where(array('m_id'=>$v['s_mid']))->find()['m_mtype'];
            $list[$k]['m_mtype'] = $type == 1 ? '是' : '否' ;
        }




        //print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->display();
    }












    public function edit(){
        $D = D("Meeting");
        $user =  M('MUser');
        $ms =  M('MStudent');
        $mt =  M('MTeacher');
        $msi =  M('MStudentInfo');
        $msu =  M('MSignup');
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {

                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    if($re){
                        $this->success("修改成功",U("/Admin/Xf/yi"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Xf/yi"));
            }
        }
        $data = $D->find(I("get.id"));
        $data['u_id'] =   $msu->where(array('s_mid'=>$data['m_id']))->find()['s_uid'];
        $data['no'] = $user->where(array('u_id'=>$data['u_id']))->find()['u_name'];
        $data['s_name'] = $ms->where(array('s_no'=>$data['no']))->find()['s_name'];
        $data['s_department'] = $ms->where(array('s_no'=>$data['no']))->find()['s_department'];
        $data['s_major'] = $msi->where(array('s_no'=>$data['no']))->find()['s_major'];


        $M = M('MMt');
        $mtAll = $M->select();
        $this->assign('mtAll', $mtAll);
        $Mg = M('MGrade');
        $gradeAll = $Mg->select();
        $Me = M('MEvaluate');
        $eCon = $Me->where(array('e_mid'=>I("get.id")))->order('e_time DESC')->field('e_content')->limit('0,1')->find()['e_content'];
        $this->assign('eCon', $eCon);
        $this->assign('gradeAll', $gradeAll);
        $this->assign('data',$data);
        $this->display();
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
        $this->assign("page", $showPage);
        $this->assign('list',$Mt_All);
        $this->display();

    }



}