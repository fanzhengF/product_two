<?php
/**
 * 焦点图
 */

class FocusmapAction extends AdminAction{

    public function index() {
        $D = D("Focusmap");
        $data = $D->getOne();
        $pic = explode(".",$data['src']);
        $data['src'] = D('Photo', 'LogicModel')->url($pic[0],$pic[1], '100X100');
        $data['src_big'] = D('Photo', 'LogicModel')->url($pic[0],$pic[1], '1000X1000');

        $this->assign("data", $data);
        $this->display();
    }

    public function add(){
        $FD = D("Focusmap");
        $data = $FD->getOne();
        if(!empty($data)){
            $this->error('只能添加一张焦点图',U("/Admin/Focusmap/index"));
        }

        if(IS_POST){
            try {
                $file = $_FILES['src'];
                if(empty($file) || $file['error'] != 0){
                    throw new Exception("请选择焦点图", 1);
                }
                $logic = D('Photo', 'LogicModel');
                $result = $logic->upload($file);

                if($result){
                    $_POST['src'] = $result['src'].'.'.$result['ext'];
                    if($data = $FD->create(I('post.'))){
                        $re = $FD->add($data);
                        if($re){
                            $this->success("添加成功",U("/Admin/Focusmap/index"));
                            return ;
                        }else{
                            throw new Exception('添加失败', 1);
                        }
                    }else{
                        throw new Exception($FD->getError(), 1);
                    }
                }else{
                    throw new Exception("上传焦点图失败", 1);
                }
            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Focusmap/index"));
            }

        }

        $this->display();
    }

    public function edit(){
        $FD = D("Focusmap");
        if (!I('get.id')){
            $this->error("非法参数");
            return ;
        }
        if(IS_POST){
            try {
                $file = $_FILES['src'];
                //有图则上传
                if(!empty($file) && $file['error'] == 0){
                    $logic = D('Photo', 'LogicModel');
                    $result = $logic->upload($file);
                    if(!$result){
                        throw new Exception("上传焦点图失败", 1);
                    }
                    $_POST['src'] = $result['src'].'.'.$result['ext'];
                }

                if($data = $FD->create(I('post.'))){
                    $re = $FD->save($data);
                    if($re){
                        //清除焦点图缓存
                        D('DelCache','LogicModel')->DelFocusMap();
                        $this->success("编辑成功",U("/Admin/Focusmap/index"));
                        return ;
                    }else{
                        throw new Exception('编辑失败', 1);
                    }
                }else{
                    throw new Exception($FD->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Admin/Focusmap/index"));
            }
        }
        $data = $FD->find(I("get.id"));
        $model = D('Photo', 'LogicModel');
        $pic = explode(".",$data['src']);
        $data['src_url'] = $model->url($pic[0], $pic[1],'100X100');

        $this->assign('data',$data);
        $this->display();
    }

}