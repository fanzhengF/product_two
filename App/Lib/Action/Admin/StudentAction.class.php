<?php
/**
 * 学生管理
 */

class StudentAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 学生列表
     */
    public function index() {
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
                    $data['s_no'] = $v[0];
                    $data['s_name'] = $v[1];
                    $data['s_sex'] = $v[2];
                    $data['s_birth'] = $v[3];
                    $data['s_card'] = $v[4];
                    $data['s_grade'] = $v[5];
                    $data['s_department'] = $v[6];
                    $data['s_domain'] = $v[7];
                    $data['s_nation'] = $v[8];
                    $data['s_mode'] = $v[9];
                    $data['s_mobile'] = $v[10];
                    $data['s_teacher'] = $v[11];
                    $data['s_time'] = time();
                    $result = M('MStudent' )->add($data);
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
                    $data['s_no'] = $v[0];
                    $data['s_name'] = $v[1];
                    $data['s_sex'] = $v[2];
                    $data['s_admin'] = $v[3];
                    $data['s_pass'] = $v[4];
                    $data['s_department'] = $v[5];
                    $data['s_major'] = $v[6];
                    $data['s_time'] = time();
                    $result = M('MStudentInfo' )->add($data);
                    if (!$result) {
                        $this->error('导入数据库失败' );
                    }
                }
            }
            $this->success('导入成功！');
            exit;
        }






        $D = D("MStudent");
        $me = M('Meeting');
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
        $D = D("MStudent");
        if(IS_POST){



            if (empty($_POST['s_no'])) {
                $this->error('学号不能为空！');
            }

            if (empty($_POST['s_name'])) {
                $this->error('学生姓名不能为空！');
            }

            if (empty($_POST['s_department'])) {
                $this->error('院系名称不能为空！');
            }

            if (empty($_POST['s_mobile'])) {
                $this->error('联系电话不能为空！');
            }

            if (empty($_POST['s_birth'])) {
                $this->error('出生日期不能为空！');
            }

            if (empty($_POST['s_card'])) {
                $this->error('证件号码不能为空！');
            }


            if (empty($_POST['s_nation'])) {
                $this->error('民族不能为空！');
            }


            if (empty($_POST['s_grade'])) {
                $this->error('年级不能为空！');
            }

            if (empty($_POST['s_domain'])) {
                $this->error('领域名称不能为空！');
            }




            if (empty($_POST['s_mode'])) {
                $this->error('培养方式不能为空！');
            }

            if (empty($_POST['s_teacher'])) {
                $this->error('导师不能为空！');
            }


           /* if (empty($_POST['s_admin'])) {
                $this->error('登录名不能为空！');
            }*/


            if (empty($_POST['s_pass'])) {
                $this->error('登录密码不能为空！');
            }

            if (empty($_POST['s_major'])) {
                $this->error('专业不能为空！');
            }





            $err_1 = $D->where(array('s_no'=>I("post.s_no")))->find();
            $err_2 = $D->where(array('s_card'=>I("post.s_card")))->find();
            if (!empty($err_1)) {
                $this->error("学号不能重复",U("/Admin/Student/add"));
            }
            if (!empty($err_2)) {
                $this->error("证件号码不能重复",U("/Admin/Student/add"));
            }
            try {
                $_POST['s_time'] = time();
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                     // echo $D->getLastSql();exit;
                    if($re){
                        $D1 = D("MStudentInfo");
                        $D1->add($D1->create(I('post.')));
                        //echo $D1->getLastSql();exit;
                        $this->success("添加成功",U("/Admin/Student/add"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Student/index"));
            }

        }
        /*院系*/
        $MD = M("MDepartmentInfo");
        $MDaLL = $MD->select();
        //专业
        $Msp = M("MStudentProfe");
        $MspaLL = $Msp->select();
        //  print_r($MDaLL);exit;
        $this->assign('MDaLL', $MDaLL);
        $this->assign('MspaLL', $MspaLL);
        $this->display();
    }









}