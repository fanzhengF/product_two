<?php
/**
 * 教师管理
 */
//import('AccessLogic');

class TeacherAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 教师列表
     */
    public function index() {
       // print_r($_SERVER);exit;
        //导入excel
        if (!empty($_FILES['file_stu']['name'])) {
            $tmp_file = $_FILES ['file_stu'] ['tmp_name'];
            $file_types = explode ( ".", $_FILES ['file_stu'] ['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];
            if (strtolower ( $file_type ) != "xlsx"  && strtolower ( $file_type ) != "xls") {
                $this->error ( '不是Excel文件，重新上传' );
            }
            $savePath = SITE_PATH . '/public/upfile/Excel/';
            $str = date ( 'Ymdhis' );
            $file_name = $str . "." . $file_type;
            if (! copy ( $tmp_file, $savePath . $file_name )) {
                $this->error ( '上传失败' );
            }
            include_once(SITE_PATH.'/ThinkPHP/Extend/Library/ORG/PHPExcel.php');
            $res = $this->read($savePath.$file_name);
            foreach ($res as $k=>$v) {

                if ($k != 0 && $k != 1 && $k != 2 && $k != 3) {
                    $data['t_no'] = $v[0];
                    $data['t_name'] = $v[1];
                    $data['t_department'] = $v[2];
                    $data['t_sex'] = $v[3];
                    $data['t_birth'] = $v[4];
                    $data['t_title'] = $v[5];
                    $data['t_post'] = $v[6];
                    $data['t_email'] = $v[7];
                    $data['t_time'] = time();
                    $result = M('MTeacher' )->add($data);
                    if (!$result) {
                        $this->error('导入数据库失败' );
                    }
                }
            }
            $this->success('导入成功！');
            exit;
        }




        if (!empty($_FILES['file_stu_info']['name'])) {
            $tmp_file = $_FILES ['file_stu_info'] ['tmp_name'];
            $file_types = explode ( ".", $_FILES ['file_stu_info'] ['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];
            if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls") {
                $this->error ( '不是Excel文件，重新上传' );
            }
            $savePath = SITE_PATH . '/public/upfile/Excel/';
            $str = date ( 'Ymdhis' );
            $file_name = $str . "." . $file_type;
            if (! copy ( $tmp_file, $savePath . $file_name )) {
                $this->error ( '上传失败' );
            }
            include_once(SITE_PATH.'/ThinkPHP/Extend/Library/ORG/PHPExcel.php');
            $res = $this->read($savePath.$file_name);
            foreach ($res as $k=>$v) {

                if ($k != 0 && $k != 1 && $k != 2 && $k != 3) {
                    $data['t_no'] = $v[0];
                    $data['t_name'] = $v[1];
                    $data['t_admin'] = $v[2];
                    $data['t_pass'] = $v[3];
                    $data['t_department'] = $v[4];
                    $data['t_time'] = time();
                    $result = M('MTeacherInfo' )->add($data);
                    if (!$result) {
                        $this->error('导入数据库失败' );
                    }
                }
            }
            $this->success('导入成功！');
            exit;
        }






        $D = D("MTeacher");
        $count = $D->getList(I('get.'), 0, 0,1);

        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0);


        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }



    public function add(){
        $D = D("MTeacher");
        if(IS_POST){
            if (empty($_POST['t_no'])) {
                $this->error('教师编号不能为空！');
            }

            if (empty($_POST['t_name'])) {
                $this->error('教师姓名不能为空！');
            }

            if (empty($_POST['t_department'])) {
                $this->error('院系名称不能为空！');
            }

            if (empty($_POST['t_birth'])) {
                $this->error('出生日期不能为空！');
            }

            if (empty($_POST['t_title'])) {
                $this->error('职称不能为空！');
            }

            if (empty($_POST['t_post'])) {
                $this->error('职务不能为空！');
            }

/*
            if (empty($_POST['t_email'])) {
                $this->error('email不能为空！');
            }
*/

            if (empty($_POST['t_admin'])) {
                $this->error('登录名不能为空！');
            }

            if (empty($_POST['t_pass'])) {
                $this->error('密码不能为空！');
            }

            $err_1 = $D->where(array('t_no'=>I("post.t_no")))->find();
            if (!empty($err_1)) {
                $this->error("教师编号不能重复",U("/Admin/Teacher/add"));
            }

            try {
                $_POST['t_time'] = time();
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                    // echo $D->getLastSql();exit;
                    if($re){
                        $D1 = D("MTeacherInfo");
                        $D1->add($D1->create(I('post.')));
                        //echo $D1->getLastSql();exit;
                        $this->success("添加成功",U("/Admin/Teacher/add"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Teacher/index"));
            }

        }

        /*院系*/
        $MD = D("MDepartmentInfo");
        $MDaLL = $MD->select();

        //  print_r($MDaLL);exit;
        $this->assign('MDaLL', $MDaLL);
        $this->display();
    }






}