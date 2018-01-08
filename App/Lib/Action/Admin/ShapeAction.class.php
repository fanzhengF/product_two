<?php
/**
 * 会议室形状管理
 */

class ShapeAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 形状列表
     */
    public function index() {
        $D = D("MShape");
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
        $D = D("MShape");
        if(IS_POST){
            if (empty($_POST['s_name'])) {
                $this->error('形状名称不能为空！');
            }


            try {
                $_POST['s_time'] = time();
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                    if($re){
                        $this->success("添加成功",U("/Admin/Shape/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Shape/index"));
            }

        }

        $this->display();
    }






    public function edit(){
        $D = D("MShape");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {

                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    if($re){
                        $this->success("修改成功",U("/Admin/Shape/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Shape/index"));
            }
        }
        $data = $D->find(I("get.id"));
        $this->assign('data',$data);
        $this->display();
    }


    public function delete(){
        $D = D("MShape");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Shape/index"));
        }else{
            $this->error("删除失败",U("/Admin/Shape/index"));
        }
    }




}