<?php
/**
 * 品牌
 */

class BrandAction extends AdminAction
{
    const  PAGE_PER_NUM = 20; //每页记录数

    public function index() {
        $p = I('get.p',1);
        $D = D("Brand");
        $count = $D->getList(I('get.'), 0, 0,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows);

        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }

    public function add(){
        $BM = D("Brand");
        if(IS_POST){
            if($data = $BM->create(I("post."))){
                $data['create_time'] = time();
                $re = $BM->add($data);
                $this->success("添加成功",U("/Admin/Brand/index"));
                return ;
            }else{
                $this->error($BM->getError());
            }
        }

        $this->display();
    }

    public function edit()
    {
        $BM = D("Brand");
        if (!I('get.id'))
        {
            $this->error("非法参数");
            return ;
        }
        if(IS_POST)
        {
            if ($data = $BM->create(I("post.")))
            {
                $BM->save($data);
                //清除缓存
                D('DelCache','LogicModel')->DelShopCommandCache();
                D('DelCache','LogicModel')->DelRedisShopCache();
                $this->success("修改成功",U("/Admin/Brand/index"));
                return ;
            }else{
                $this->error($BM->getError());
            }
        }

        $data = $BM->find(I("get.id"));
        $this->assign('data',$data);
        $this->display();
    }

    //删除
    public function delete(){
        $BM = D("Brand");
        $re =$BM->toDelete(I("get.id"));

        if($re){
            //清除缓存
            D('DelCache','LogicModel')->DelShopCommandCache();
            D('DelCache','LogicModel')->DelRedisShopCache();
            $this->success("删除成功",U("/Admin/Brand/index"));
        }else{
            $this->error("删除失败",U("/Admin/Brand/index"));
        }
    }




}