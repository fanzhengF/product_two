<?php
/**
 * 会议等级管理
 */

class GradeAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 会议等级列表
     */
    public function index() {
        $D = D("MGrade");
        $Mt = D("MMt");

        $count = $D->getList(I('get.'), 0, 0,1,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,1);
        foreach ($list as $k=>$v){
            $list[$k]['mt_name'] = $Mt->where(array('mt_id'=>$v['mt_id']))->find()['mt_name'];

        }
       // print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }


    public function add(){
        $D = D("MGrade");
        if(IS_POST){

            if (empty($_POST['g_name'])) {
                $this->error('等级名称不能为空！');
            }

            if (empty($_POST['g_xuefen'])) {
                $this->error('学分不能为空！');
            }

            try {
                $_POST['g_time'] = time();

                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                 //   echo $D->getLastSql();exit;
                    if($re){
                        $this->success("添加成功",U("/Admin/Grade/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Grade/index"));
            }

        }

        //类型
        $Mt = D("MMt");
        $MtaLL = $Mt->select();
        $this->assign('MtaLL', $MtaLL);

        $this->display();
    }






    public function edit(){
        $D = D("MGrade");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {

                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    //echo $D->getLastSql();exit;
                    if($re){
                        $this->success("修改成功",U("/Admin/Grade/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Grade/index"));
            }
        }
        $data = $D->find(I("get.id"));
        $this->assign('data',$data);
        //类型
        $Mt = D("MMt");
        $MtaLL = $Mt->select();
        $this->assign('MtaLL', $MtaLL);
        $this->display();
    }


    public function delete(){
        $D = D("MGrade");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Grade/index"));
        }else{
            $this->error("删除失败",U("/Admin/Grade/index"));
        }
    }







}