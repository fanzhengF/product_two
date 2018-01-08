<?php
/**
 * 会议海报管理
 */

class PosterAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 会议海报列表
     */
    public function index() {
        $D = D("MPoster");
        $Meeting = D("Meeting");
        $count = $D->getList(I('get.'), 0, 0,1,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,1);
        foreach ($list as $k=>$v) {
            $list[$k]['m_name'] = $Meeting->where(array('m_id'=>$v['p_mid']))->find()['m_name'];

        }
       // print_r($list);exit;
        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }


    public function add(){
        $D = D("MPoster");
        $Meeting = D("Meeting");

        if(IS_POST){
            try {
                $file_name = $this->upload($_FILES['p_images'],'images');
                $_POST['p_time'] = time();
                $_POST['p_images'] = $file_name;

                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                 //   echo $D->getLastSql();exit;
                    if($re){
                        $this->success("添加成功",U("/Admin/Poster/index"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Poster/index"));
            }

        }
        /*关联会议*/
        $meeting =  $Meeting->select();
        $this->assign('meeting', $meeting);
        $this->display();
    }






    public function edit(){
        $D = D("MPoster");
        $Meeting = D("Meeting");

        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {
                if (!empty($_FILES['p_images']['name'])) {
                    $file_name = $this->upload($_FILES['p_images'],'images');
                    $_POST['p_images'] = $file_name;
                }
                if($data = $D->create(I('post.'))){
                    $re = $D->save($data);
                    //echo $D->getLastSql();exit;
                    if($re){
                        $this->success("修改成功",U("/Admin/Poster/index"));
                        return ;
                    }else{
                        throw new Exception('修改失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Poster/index"));
            }
        }
        /*关联会议*/
        $meeting =  $Meeting->select();
        $this->assign('meeting', $meeting);
        $data = $D->find(I("get.id"));
        $this->assign('data',$data);
        $this->display();
    }


    public function delete(){
        $D = D("MPoster");
        $re =$D->delete(I("get.id"));
        if($re){
            $this->success("删除成功",U("/Admin/Poster/index"));
        }else{
            $this->error("删除失败",U("/Admin/Poster/index"));
        }
    }







}