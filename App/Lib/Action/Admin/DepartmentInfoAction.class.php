<?php
/**
 * 院系信息
 */

class DepartmentInfoAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 院系列表
     */
    public function index() {
        $D = D("MDepartmentInfo");
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
        $D = D("MDepartmentInfo");
        if(IS_POST){
            if (empty($_POST['d_name'])) {
                $this->error('院系名称不能为空！');
            }

            if (empty($_POST['d_abbreviation'])) {
                $this->error('院系简介不能为空！');
            }

            if (empty($_POST['d_superior'])) {
                $this->error('上级院校不能为空！');
            }
            try {
                $_POST['d_time'] = time();
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                    if($re){
                        $this->success("添加成功",U("/Admin/DepartmentInfo/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/DepartmentInfo/index"));
            }

        }

        $this->display();
    }






    public function edit(){
        $D = D("MDepartmentInfo");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {

                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    if($re){
                        $this->success("修改成功",U("/Admin/DepartmentInfo/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/DepartmentInfo/index"));
            }
        }
        $data = $D->find(I("get.id"));
        $this->assign('data',$data);
        $this->display();
    }


    public function delete(){
        $D = D("MDepartmentInfo");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/DepartmentInfo/index"));
        }else{
            $this->error("删除失败",U("/Admin/DepartmentInfo/index"));
        }
    }




}