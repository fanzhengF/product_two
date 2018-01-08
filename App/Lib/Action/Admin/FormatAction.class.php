<?php
/**
 * 海报版式管理
 */

class FormatAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 版式列表
     */
    public function index() {
        $D = D("MFormat");
        $count = $D->getList(I('get.'), 0, 0,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0);


        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->display();
    }










    public function edit(){
        $D = D("MFormat");
        if(IS_POST){
            try {
                if($data = $D->create(I('post.'))){
                    $re = $D->where(array('f_id'=>array('in','1,2')))->save(array('f_confirm'=>0));
                    //echo $D->getLastSql();exit;
                    if($re){
                        D("MFormat")->where(array('f_id'=>I('post.f_id')))->save(array('f_confirm'=>1));
                        die('-1');
                        return ;
                    }else{
                        die('-2');
                    }
                }else{
                    die('-2');
                }

            } catch (Exception $e) {
                die('-2');
            }
        }


    }





}