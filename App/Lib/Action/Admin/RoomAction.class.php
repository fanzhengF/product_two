<?php
/**
 * 会议室管理
 */

class RoomAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 会议室列表
     */
    public function index() {
        $D = D("MRoom");
        $Msh = M("MShape");
        $Me = M("Meeting");
        $MM = M("MMechanism"); //所属机构
        $MD = M("MDepartmentInfo"); //院系
        $Ms = M('MSignup');
        $Mu = M('MUser');
        $count = $D->getList(I('get.'), 0, 0,1,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,1);

       // print_r($list);exit;
        foreach ($list as $k=>$v) {
          $MMaLL = $MM->where(array('m_id'=>$v['room_jigou']))->find()['m_name'];
          $MDaLL = $MD->where(array('d_id'=>$v['room_yx']))->find()['d_name'];
          $Mshall = $Msh->where(array('s_id'=>$v['room_shape']))->find()['s_name'];
          $list[$k]['room_jigou'] = $MMaLL;
          $list[$k]['room_yx'] = $MDaLL;
          $list[$k]['room_shape'] = $Mshall;
        }

        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->display();
    }


    public function add(){
        $D = D("MRoom");
        $Msh = M("MShape");
        $this->assign('msh',$Msh->select());
        if(IS_POST){
            if (empty($_POST['room_no'])) {
                $this->error('会议室编号不能为空！');
            }

            if (empty($_POST['room_name'])) {
                $this->error('会议室名称不能为空！');
            }

            if (empty($_POST['room_quyu'])) {
                $this->error('会议室区域不能为空！');
            }

            if (empty($_POST['room_address'])) {
                $this->error('会议室地点不能为空！');
            }

            if (empty($_POST['room_number'])) {
                $this->error('会议室人数不能为空！');
            }



            try {
                $_POST['room_time'] = time();
                if (is_array($_POST['room_facilities'])) {
                    $_POST['room_facilities'] = implode(',', $_POST['room_facilities']);
                }
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                   // echo $D->getLastSql();exit;
                    if($re){
                        $this->success("添加成功",U("/Admin/Room/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Room/index"));
            }

        }

        $D1 = D("MFacilities");
        $f_all =  $D1->order('f_time DESC')->select();
        /*所属机构*/
        $MM = D("MMechanism");
        $MMaLL = $MM->where(array('m_parent'=>0))->select();
        $arr = array();
        foreach ($MMaLL as $k=>$v){
            $MMaLLSub = $MM->where(array('m_parent'=>$v['m_id']))->select();
            $MMaLL[$k]['m_sub'] = $MMaLLSub;

        }
        /*院系*/
        $MD = D("MDepartmentInfo");
        $MDaLL = $MD->select();

      //  print_r($MDaLL);exit;
        $this->assign('MDaLL', $MDaLL);
        $this->assign('MMall', $MMaLL);
        $this->assign('f_all',$f_all);
        $this->display();
    }






    public function edit(){
        $D = D("MRoom");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {
				if (is_array($_POST['room_facilities'])) {
                    $_POST['room_facilities'] = implode(',', $_POST['room_facilities']);
                }
                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    if($re){
                        $this->success("修改成功",U("/Admin/Room/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Room/index"));
            }
        }
        $D1 = D("MFacilities");
        $f_all =  $D1->order('f_time DESC')->select();
        $data = $D->find(I("get.id"));
        $room_fa = explode(',',$data['room_facilities']);
			
		foreach ($f_all as $k =>$v) {
            if (in_array($v['f_name'],$room_fa)) { $checked = 'checked'; }else { $checked = ''; }
			$_she .= '<label ><input type="checkbox" '.$checked.' name="room_facilities[]" value="'.$v['f_name'].'" />&nbsp;'.$v['f_name'].'</label>&nbsp;&nbsp;';
		}
        /*所属机构*/
        $MM = D("MMechanism");
        $MMaLL = $MM->where(array('m_parent'=>0))->select();
        $arr = array();
        foreach ($MMaLL as $k=>$v){
            $MMaLLSub = $MM->where(array('m_parent'=>$v['m_id']))->select();
            $MMaLL[$k]['m_sub'] = $MMaLLSub;

        }
        /*院系*/
        $MD = D("MDepartmentInfo");
        $MDaLL = $MD->select();
        $Msh = M("MShape");
        $this->assign('msh',$Msh->select());
        //  print_r($MDaLL);exit;
        $this->assign('MDaLL', $MDaLL);
        $this->assign('MMall', $MMaLL);
		$this->assign('sheshi',$_she);
        $this->assign('data',$data);
        $this->display();
    }


    public function delete(){
        $D = D("MRoom");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Room/index"));
        }else{
            $this->error("删除失败",U("/Admin/Room/index"));
        }
    }




    /**
     * @var 自筹会议室列表
     */
    public function raised_index() {
        $D = D("MRoom");
        $Msh = M("MShape");
        $count = $D->getList(I('get.'), 0, 0,1,2);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,2);
        foreach ($list as $k=>$v) {
            $Mshall = $Msh->where(array('s_id'=>$v['room_shape']))->find()['s_name'];
            $list[$k]['room_shape'] = $Mshall;
        }

        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }


    public function raised_add(){
        $D = D("MRoom");
        $Msh = M("MShape");
        $this->assign('msh',$Msh->select());
        if(IS_POST){
            if (empty($_POST['room_name'])) {
                $this->error('会议室名称不能为空！');
            }

            if (empty($_POST['room_address'])) {
                $this->error('会议室地点不能为空！');
            }

            if (empty($_POST['room_number'])) {
                $this->error('会议室人数不能为空！');
            }
            try {
                $_POST['room_time'] = time();
                if (is_array($_POST['room_facilities'])) {
                    $_POST['room_facilities'] = implode(',', $_POST['room_facilities']);
                }
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                    //  echo $D->getLastSql();exit;
                    if($re){
                        $this->success("添加成功",U("/Admin/Room/raised_index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Room/raised_index"));
            }

        }
        $D1 = D("MFacilities");
        $f_all =  $D1->order('f_time DESC')->select();
        $this->assign('f_all',$f_all);
        $this->display();
    }






    public function raised_edit(){
        $D = D("MRoom");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {
                if (is_array($_POST['room_facilities'])) {
                    $_POST['room_facilities'] = implode(',', $_POST['room_facilities']);
                }
                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    if($re){
                        $this->success("修改成功",U("/Admin/Room/raised_index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Room/raised_index"));
            }
        }
        $data = $D->find(I("get.id"));
        $D1 = D("MFacilities");
        $f_all =  $D1->order('f_time DESC')->select();
        $room_fa = explode(',',$data['room_facilities']);
        foreach ($f_all as $k =>$v) {
            if (in_array($v['f_name'],$room_fa)) { $checked = 'checked'; }else { $checked = ''; }
            $_she .= '<label><input type="checkbox" '.$checked.' name="room_facilities[]" value="'.$v['f_name'].'" />&nbsp;'.$v['f_name'].'</label>&nbsp;&nbsp;';
        }
        $Msh = M("MShape");
        $this->assign('msh',$Msh->select());
        $this->assign('sheshi',$_she);
        $this->assign('data',$data);
        $this->display();
    }


    public function raised_delete(){
        $D = D("MRoom");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Room/raised_index"));
        }else{
            $this->error("删除失败",U("/Admin/Room/raised_index"));
        }
    }



}