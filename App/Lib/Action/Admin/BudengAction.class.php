<?php
/**
 * 会议补登管理
 */

class BudengAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 会议补登列表
     */
    public function index() {
        $D = D("Meeting");
        $count = $D->getList(I('get.'), 0, 0,1,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,1);
        $M = M('MMt');
        $Mg = M('MGrade');
        $Mu = M('MUser');

        /*所属机构*/
        $MM = D("MMechanism");
        foreach ($list as $k=>$v){
            $list[$k]['m_type'] = $M->where(array('mt_id'=>$v['m_type']))->find()['mt_name'];
            $list[$k]['m_grade'] = $Mg->where(array('g_id'=>$v['m_grade']))->find()['g_name'];
            $list[$k]['m_xuefen'] = $Mg->where(array('g_id'=>$v['m_grade']))->find()['g_xuefen'];
            $MMaLL = $MM->where(array('m_id'=>$v['m_jigou']))->find()['m_name'];
            $list[$k]['m_jigou'] = $MMaLL;
            $list[$k]['m_uid'] = $Mu->where(array('u_id'=>$v['m_uid']))->find()['u_name'];
        }

        //print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }


    public function add(){
        $D = D("Meeting");
        if(IS_POST){
            try {
                $bianma =  $D->where(array('m_bianma'=>$_POST['m_bianma']))->find();
                if (!empty($bianma)) $this->error('编码重复！');
                $file_name = $this->upload($_FILES['m_images'],'images');
                $file_name_zilaio = $this->upload($_FILES['m_ziliao'],'ziliao','file');
                $_POST['m_images'] = $file_name;
                $_POST['m_ziliao'] = $file_name_zilaio;
                $_POST['time'] = time();
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                    if($re){
                        $this->success("添加成功",U("/Admin/Budeng/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Budeng/index"));
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
        $this->assign('MMall', $MMaLL);

        $M = M('MMt');
        $mtAll = $M->select();
        $this->assign('mtAll', $mtAll);
        $Mg = M('MGrade');
        $gradeAll = $Mg->select();
        $this->assign('gradeAll', $gradeAll);
        $this->display();
    }






    public function edit(){
        $D = D("Meeting");
        $Mc = M("MCredit");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
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
                    //补登学分记录到学生学分
                    $no =  $D->field('m_u_name')->where(array('m_id'=>$_POST['m_id']))->find()['m_u_name'];
                    $number =  $Mc->where(array('c_no'=>$no))->find()['c_number'];
                    $data['c_number'] = $number + $_POST['m_hyxf'];
                    $Mc->where(array('c_no'=>$no))->save($data);
                    //echo $D->getLastSql();exit;
                    if($re){
                        $this->success("修改成功",U("/Admin/Budeng/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Budeng/index"));
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
        $this->assign('MMall', $MMaLL);
        $data = $D->find(I("get.id"));
        $M = M('MMt');
        $mtAll = $M->select();
        $this->assign('mtAll', $mtAll);
        $Mg = M('MGrade');
        $gradeAll = $Mg->select();
        $this->assign('gradeAll', $gradeAll);
        $this->assign('data',$data);
        $this->display();
    }


    public function delete(){
        $D = D("Meeting");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Budeng/index"));
        }else{
            $this->error("删除失败",U("/Admin/Budeng/index"));
        }
    }







}