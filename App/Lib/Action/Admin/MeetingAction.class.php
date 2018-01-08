<?php
/**
 * 会议内容管理
 */

class MeetingAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 会议内容列表
     */
    public function index() {
        $D = D("Meeting");
        $count = $D->getList(I('get.'), 0, 0,1,0,'','',1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,0,'','',1);
        $M = M('MMt');
        $Mg = M('MGrade');
        $Mu = M('MUser');
        $Ms = M('MSignup');

        /*所属机构*/
        $MM = D("MMechanism");
        foreach ($list as $k=>$v){
            $list[$k]['m_type'] = $M->where(array('mt_id'=>$v['m_type']))->find()['mt_name'];
            $list[$k]['m_grade'] = $Mg->where(array('g_id'=>$v['m_grade']))->find()['g_name'];
            $list[$k]['m_xuefen'] = $Mg->where(array('g_id'=>$v['m_grade']))->find()['g_xuefen'];
            $MMaLL = $MM->where(array('m_id'=>$v['m_jigou']))->find()['m_name'];
            $list[$k]['m_jigou'] = $MMaLL;

            $u_name = $Mu->where(array('u_id'=>$v['m_uid']))->find()['u_name'];
            $list[$k]['m_uid'] = $this->getUser($u_name);

            $siim =  $Ms->field('s_uid')->where(array('s_mid'=>$v['m_id']))->select();
            foreach ($siim as $k=>$v) {
                $new2[$k] = $v['s_uid'];
            }
            $imsi =  implode(',', $new2);
            $imsi = $Mu->field('u_name')->where(array('u_id'=>array('in',$imsi)))->select();
            //$list[$k]['user'] = $imsi;
        }
//exit;
        //print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }


    private function getUser ($u_name){
        $ms =  M('MStudent');
        $mt =  M('MTeacher');

        $msName =  $ms->where(array('s_no'=>$u_name))->find()['s_name'];
        $mt_Name =  $mt->where(array('t_no'=>$u_name))->find()['t_name'];
        if (!empty($msName)) {
            $_name = $msName;
        }
        if (!empty($mt_Name)) {
            $_name = $mt_Name;
        }
        return $_name;
    }

    //查看参会人员
    public function user (){
      $id = I('get.id');
      if ($id >0) {
          $Ms = M('MSignup');
          $Mu = M('MUser');
          $count = $Ms->where(array('s_mid'=>$id))->count();
          import("ORG.Util.Page"); //载入分页类
          $page = new Page($count,self::PAGE_PER_NUM);
          $showPage = $page->show();
          $MtAll = $Ms->where(array('s_mid'=>$id))->order('s_time DESC')->limit($page->firstRow , $page->listRows)->select();
          foreach ($MtAll as $k=>$v){
              $imsi = $Mu->where(array('u_id'=>$v['s_uid']))->find();
              $MtAll[$k]['user'] = $imsi['u_name'];
              $MtAll[$k]['u_zhiye'] = $imsi['u_zhiye'];
              $MtAll[$k]['u_phone'] = $imsi['u_phone'];
              $MtAll[$k]['u_sex'] = $imsi['u_sex'];
              $MtAll[$k]['u_time'] = $imsi['u_time'];
              $MtAll[$k]['u_type'] = $imsi['u_type'];

          }
          $this->assign('list',$MtAll);
          $this->assign("page", $showPage);
          $this->assign("id", $id);
          $this->display();

      }
    }


    public function add(){
        $D = D("Meeting");
        if(IS_POST){

                if (empty($_POST['m_number']) ) {
                    $this->error('请您填写会议人数！');exit;
                }


            try {
                $bianma =  $D->where(array('m_bianma'=>$_POST['m_bianma']))->find();
                if (!empty($bianma)) $this->error('编码重复！');
                $file_name = $this->upload($_FILES['m_images'],'images');
                $file_name_zilaio = $this->upload($_FILES['m_ziliao'],'ziliao','file');
                $_POST['m_images'] = $file_name;
                $_POST['m_ziliao'] = $file_name_zilaio;
               // $_POST['m_bianma'] = 'hy00'.$getId;
                $_POST['time'] = time();
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                    if($re){
                        $D->where(array('m_id'=>$re))->save(array('m_bianma'=>'hy00'.$re));
                        $this->success("添加成功",U("/Admin/Meeting/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Meeting/index"));
            }

        }

        /*所属机构*/
        $MM = D("MMechanism");
        $MMaLL = $MM->where(array('m_parent'=>0))->select();
        $Mr = D("MRoom"); //所属会议室
        $arr = array();
        foreach ($MMaLL as $k=>$v){
            $MMaLLSub = $MM->where(array('m_parent'=>$v['m_id']))->select();
            $MMaLL[$k]['m_sub'] = $MMaLLSub;

        }
        $this->assign('MMall', $MMaLL);

        $M = M('MMt');
        $mtAll = $M->select();
        $this->assign('mtAll', $mtAll);
        $Mg = M('MGrade');
        $gradeAll = $Mg->select();
        $this->assign('gradeAll', $gradeAll);
        $this->assign('MrAll', $Mr->select());
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
                $m_ziliao_name =  substr($_FILES['m_ziliao']['name'],0,strpos($_FILES['m_ziliao']['name'],'.'));
                $_POST['m_ziliao_name'] = $m_ziliao_name;
                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    //echo $D->getLastSql();exit;
                    if($re){
                        $this->success("修改成功",U("/Admin/Meeting/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Meeting/index"));
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


    public function savefen (){
        $this->display();
    }

    public function delete(){
        $D = D("Meeting");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Meeting/index"));
        }else{
            $this->error("删除失败",U("/Admin/Meeting/index"));
        }
    }

    public function delsign(){
        $D = D("MSignup");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Meeting/user",array('id'=>I('get.m_id'))));
        }else{
            $this->error("删除失败",U("/Admin/Meeting/user",array('id'=>I('get.m_id'))));
        }
    }







}