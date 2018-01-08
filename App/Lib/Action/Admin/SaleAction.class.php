<?php
/**
 * 供应商优惠设置
 */
import('SupplierSaleLogic');
import('AccessLogic');

class SaleAction extends AdminAction{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 供应商优惠设置列表
     */
    public function index() {
        $p = I('get.p',1);
        $D = D("SupplierSale");
        $count = $D->getList(I('get.'), 0, 0,1,$this->my_info['id'],$this->my_info['role_id']);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,$this->my_info['id'],$this->my_info['role_id']);

        if(!empty($list)){
            $DA = D('Access');
            foreach ($list as $key => $value) {
                $list[$key]['type_zh'] = SupplierSaleModel::getSaleType()[$value['type']];
                $list[$key]['rule_zh'] = SupplierSaleLogicModel::getTypeZhByTypeAndRuleJson($value['type'],$value['rule']);
                $_result_access = $DA->getOne('business_name',array('id'=>$value['supplier_id']));
                $list[$key]['supplier_name'] = $_result_access['business_name'];
            }
        }
        if(!empty($list) && $this->my_info['role_id'] == AccessModel::SUPPLIER){
            $this->assign("no_add", 1);
        }
        $this->assign("page", $showPage);
        $this->assign("list", $list);

        $this->display();
    }


    /**
     * @var 添加供应商优惠设置
     */
    public function add(){
        //一个供应商只能拥有一个优惠
        if(IS_POST){
            $post_data = I('post.');

            $this->checkToken();
            try {
                //获取相应的插入数据
                $data = SupplierSaleLogicModel::getInsertData($this->my_info,$post_data);

                $DSS = D("SupplierSale");
                if($data = $DSS->create($data)){
                    $re = $DSS->add($data);
                    if($re > 0){
                        $this->success("添加成功",U("/Admin/Sale/index"));
                    }else{
                        throw new Exception('添加失败', 1);
                    }

                }else{
                    throw new Exception($DSS->getError(), 1);
                }
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

        }else{

            //获取供应商名称select
            $supplier_list = AccessLogicModel::getSaleSupplierListByUserInfo($this->my_info);
            $sale_type     = SupplierSaleModel::getSaleType();

            $this->assign("supplier_list", $supplier_list);
            $this->assign("sale_type", $sale_type);
            $this->display();
        }


    }


    /**
     * @var 修改供应商优惠设置
     */
    public function edit(){
        $id  = IS_POST ? I('post.id') : I('get.id');

        if (!$id){
            $this->error("非法参数");
            return ;
        }

        try {
            $data = SupplierSaleLogicModel::getEditInfoById($id);
            //判定操作者是否允许编辑此id
            SupplierSaleLogicModel::getIsEdit($this->my_info,$data);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }


        if(IS_POST){
            try {
                //获取相应的更新数据
                $post_data = I('post.');
                $update_data = SupplierSaleLogicModel::getUpdateData($post_data);

                $DSS = D("SupplierSale");
                if ($data = $DSS->create($update_data)){
                    $result = $DSS->save($data);
                    if(false === $result){
                        throw new Exception("更新失败", 1);
                    }else if ($result > 0){
                        //清除缓存
                        D('DelCache','LogicModel')->DelShopCommandCache();
                        D('DelCache','LogicModel')->DelRedisShopCache();
                        $this->success("更新成功",U("/Admin/Sale/index"));
                    }else{
                        throw new Exception("没有更新", 1);
                    }
                }else{
                    throw new Exception($DSS->getError(), 1);
                }
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

        }else{
            $sale_type     = SupplierSaleModel::getSaleType();

            $this->assign("sale_type", $sale_type);
            $this->assign('data',$data);
            $this->assign('type',$data['type']);

            //两种方式,先简单的通过if判断
            if($data['type'] == SupplierSaleModel::EVERYFULLCUT){
                $this->assign('rule_key',key($data['rule_array']));
                $this->assign('rule_value',current($data['rule_array']));
            }else{
                $discount = current($data['rule_array']);
                $this->assign('discount',sprintf("%.1f",$discount*10) );

            }

            $this->display();
        }
    }



}