<?php
/**
 * 机构管理
 */

class MechanismAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 机构列表
     */
    public function index() {
        $D = D("MMechanism");
        $count = $D->getList(I('get.'), 0, 0,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0);


        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }


    //子类列表
    public function SubclassIndex (){
        $D = D("MMechanism");
        $count = $D->getList(I('get.'), 0, 0,1,I('get.id'));
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,I('get.id'));


        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->assign('parentName', I('get.name'));
        $this->assign('parentId', I('get.id'));
        $this->display();
    }

    public function add(){
        $D = D("MMechanism");
        if(IS_POST){
			  if (empty($_POST['m_name']) ) {
                $this->error('请您填写机构名称！');exit;
            }
            try {
                $_POST['m_time'] = time();
                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                    if($re){
                        $this->success("添加成功",U("/Admin/Mechanism/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Mechanism/index"));
            }

        }
        $this->assign('parentId',I('get.parentId'));
        $this->assign('parentName',I('get.parentName'));
        $this->display();
    }






    public function edit(){
        $D = D("MMechanism");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {

                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    if($re){
                        $this->success("修改成功",U("/Admin/Mechanism/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Mechanism/index"));
            }
        }
        $data = $D->find(I("get.id"));
        $this->assign('parentName',I('get.parentName'));
        $this->assign('parentId',I('get.parentId'));
        $this->assign('data',$data);
        $this->display();
    }


    public function delete(){
        $D = D("MMechanism");
        $D->where(array('m_parent'=>I("get.id")))->delete();
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Mechanism/index"));
        }else{
            $this->error("删除失败",U("/Admin/Mechanism/index"));
        }
    }




}