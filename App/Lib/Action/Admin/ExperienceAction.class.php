<?php
/**
 * 会议心得管理
 */

class ExperienceAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 会议心得列表
     */
    public function index() {
        $D = D("MExperience");
        $user =  M('MUser');
        $meeting =  M('Meeting');


        $count = $D->getList(I('get.'), 0, 0,1,0);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,0);
        foreach ($list as $k=>$v){
            $list[$k]['u_name'] = $user->where(array('u_id'=>$v['e_uid']))->find()['u_name'];
            $list[$k]['m_name'] = $meeting->where(array('m_id'=>$v['e_mid']))->find()['m_name'];

        }

        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }


    public function add(){
        $D = D("MExperience");
        if(IS_POST){
            try {
                $_POST['time'] = time();

                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                 //   echo $D->getLastSql();exit;
                    if($re){
                        $this->success("添加成功",U("/Admin/Experience/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Experience/index"));
            }

        }

        $this->display();
    }






    public function edit(){
        $D = D("MExperience");
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
                        $this->success("修改成功",U("/Admin/Experience/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Experience/index"));
            }
        }
        $data = $D->find(I("get.id"));
        $this->assign('data',$data);
        $this->display();
    }


    public function delete(){
        $D = D("MExperience");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Experience/index"));
        }else{
            $this->error("删除失败",U("/Admin/Experience/index"));
        }
    }







}