<?php
/**
 * 专业管理
 */

class StudentProfeAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 专业列表
     */
    public function index() {
        $D = D("MStudentProfe");

        $count = $D->getList(I('get.'), 0, 0,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0);
       // print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }




    public function add(){
        $D = D("MStudentProfe");
        if(IS_POST){
			if (empty($_POST['p_no']) ) {
                $this->error('请您填写专业号！');exit;
            }
			if (empty($_POST['p_name']) ) {
                $this->error('请您填写专业名称！');exit;
            }
            try {
                $_POST['p_time'] = time();
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                    if($re){
                        $this->success("添加成功",U("/Admin/StudentProfe/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/StudentProfe/index"));
            }

        }

        $this->display();
    }






    public function edit(){
        $D = D("MStudentProfe");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {

                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    if($re){
                        $this->success("修改成功",U("/Admin/StudentProfe/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/StudentProfe/index"));
            }
        }
        $data = $D->find(I("get.id"));
        $this->assign('data',$data);
        $this->display();
    }


    public function delete(){
        $D = D("MStudentProfe");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/StudentProfe/index"));
        }else{
            $this->error("删除失败",U("/Admin/StudentProfe/index"));
        }
    }




}