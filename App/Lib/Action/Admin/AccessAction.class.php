<?php
import('AccessLogic');

class AccessAction extends AdminAction {
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 管理员列表
     */
    public function index() {
        $p = I('get.p',1);
        $D = D("Access");
        $rs = $D->adminList(I('get.'), $p, self::PAGE_PER_NUM,$this->my_info['id'],$this->my_info['role_id']);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($rs['num'], self::PAGE_PER_NUM);

        $this->assign('page', $page->show());
        $this->assign("list", $rs['list']);
        $statuses = $D->getStatus();
        $this->assign("statuses", $statuses);

        $roles = $D->getAccessView($this->my_info['role_id']);
        //print_r($roles);exit;
        $this->assign("roles", $roles);
        $this->assign("is_admin", $this->is_admin);
        if($D->checkIsShgOperator($this->my_info['role_id'])){
            $this->assign("is_shg_operator", 1);
            $order_by = $D->getOrderBy();
            $this->assign("order_by", $order_by);
        }

        $this->display();
    }

    /**
     * @var 获取二维码
     */
    public function getQrcode(){
        //如果url里再有&,html那里就需要urlencode()了
        $url  = I('get.url');
        $size = 20;
        $name = I('get.name','');

        Vendor('qrcode.qrcode');
        $qrcode = new \Plugin_Qrcode();
        //生成二维码
        ob_start();
        $qrcode->make_qrcode($url, FALSE, 'L', $size, 0);
        //不直接输出,用于给二维码加logo
        $image_string = ob_get_contents();

        ob_end_clean();

        //给二维码加入logo
        $code = imagecreatefromstring($image_string);
        $logo = imagecreatefromstring( file_get_contents ( WEB_ROOT . 'Public' . DS . 'Images' . DS . 'Admin' . DS . 'logo.jpg') );
        imagecopyresampled ( $code,$logo, 191, 191, 0, 0, 120, 120, 120, 120 );
        ob_clean();

        ob_start();
        imagepng ($code);
        //不直接输出,用于给二维码+logo加入大背景图
        $code_image_string = ob_get_contents();
        ob_end_clean();

        $new_code  = imagecreatefromstring($code_image_string);
        $code_base = imagecreatefromstring(file_get_contents ( WEB_ROOT . 'Public' . DS . 'Images' . DS . 'Admin' . DS . 'codebase.jpg') );
        $new_code_width  = imagesx ( $new_code );
        $new_code_height = imagesy ( $new_code );
        //最后合成背景输出
        imagecopyresampled ( $code_base, $new_code, 565, 1624, 0, 0, 502, 502, $new_code_width, $new_code_height );
        ob_clean();
        header("Content-Disposition: inline; filename=".$name);
        header("Content-type: image/png");
        imagepng ( $code_base);
    }

    /**
     * @var 修改用户状态
     */
    public function changeStatus(){
        $id = I('get.id');
        $status = I('get.status');

        //检查status状态值是否正常
        $D = D("Access");
        $statuses = $D->getStatus('en-us');
        if(!isset($statuses[$status])){
            $this->error("非法操作，请重试");
        }

        //检查状态是否有更改
        $list = $D->getOne('status,role_id',array('id'=>$id));
        if(empty($list)){
            $this->error("信息不存在，请重试");
        }

        if($status == $list['status']){
            $this->error("您无需更改状态，请重试");
        }

        //检查用户能否操作此id
        $check = $D->checkUpdateAccessByRoleIdAndId($id,$this->my_info['role_id'],$this->my_info['id']);
        if(false == $check){
            $this->error("您没有操作权限，请重试");
        }

        $function = '_change' . $statuses[$status];

        try {
            $result = $this->$function($id,$list['role_id']);
            if($result){
                //清除缓存

                /*
                 * D('DelCache','LogicModel')->DelShopCommandCache();
                D('DelCache','LogicModel')->DelRedisShopCache();
                D('DelCache','LogicModel')->DelRedisCityShopCache();
                D('DelCache','LogicModel')->DeleteShopMap();*/
                $this->success("更改状态成功",U('Access/index'));
            }else{
                throw new Exception("更改状态失败，请重试", 1);
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

    }

    /**
     * @var 系统管理员删除用户
     */
    public function delAdmin(){
        //应产品要求,此处不做删除处理.
        $this->error("请采用禁用方式");
        exit;
        $id = I('get.id');
        $role_id = I('get.role_id');

        $model = D('Access');
        $model->startTrans();
        try {
            //删除和禁用的ids获取是一致的
            $ids = AccessLogicModel::getStatusForbidden($id,$role_id);
            $result = $model->changeStatus($ids,AccessModel::STATUS_DELETE);
            if($result <= 0){
                throw new Exception("更新失败", 1);
            }

            $model->commit();
            $this->success("删除成功",U('Access/index'));
        } catch (Exception $e) {
            $model->rollback();
            $this->error("删除失败，请重试");
        }
    }

    /**
     * @var 状态迭代禁用
     */
    private function _changeStatusForbidden($id,$role_id){
        $model = D('Access');
        $model->startTrans();

        try {
            $ids = AccessLogicModel::getStatusForbidden($id,$role_id);
            $result = $model->changeStatus($ids,AccessModel::STATUS_FORBIDDEN);
            if($result <= 0){
                throw new Exception("更新失败", 1);
            }

            $model->commit();
            return true;
        } catch (Exception $e) {
            $model->rollback();
            return false;
        }

    }

    /**
     * @var 状态单一启用
     */
    private function _changeStatusNormal($id,$role_id){
        $model = D('Access');
        $ids   = array($id);

        //用户上级判断,如果上级是禁用的,也禁止启用
        AccessLogicModel::checkIsAllowStatus($id,$role_id);

        $result = $model->changeStatus($ids,AccessModel::STATUS_NORMAL);
        if($result <= 0){
            throw new Exception("更新失败", 1);
        }

        return true;
    }

    public function nodeList() {
        $this->assign("list", D("Access")->nodeList());
        $this->display();
    }

    public function roleList() {
        $this->assign("list", D("Access")->roleList());
        $this->display();
    }

    public function addRole() {
        if (IS_POST) {
            $this->checkToken();
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode(D("Access")->addRole());
        } else {
            $this->assign("info", $this->getRole());
            $this->display("editRole");
        }
    }

    public function editRole() {
        if (IS_POST) {
            $this->checkToken();
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode(D("Access")->editRole());
        } else {
            $M = M("Role");
            $info = $M->where("id=" . (int) $_GET['id'])->find();
            if (empty($info['id'])) {
                $this->error("不存在该角色", U('Access/roleList'));
            }
            $this->assign("info", $this->getRole($info));
            $this->display();
        }
    }

    public function opNodeStatus() {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode(D("Access")->opStatus("Node"));
    }

    public function opRoleStatus() {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode(D("Access")->opStatus("Role"));
    }

    public function opSort() {
        $M = M("Node");
        $datas['id'] = (int) $this->_post("id");
        $datas['sort'] = (int) $this->_post("sort");
        header('Content-Type:application/json; charset=utf-8');
        if ($M->save($datas)) {
            echo json_encode(array('status' => 1, 'info' => "处理成功"));
        } else {
            echo json_encode(array('status' => 0, 'info' => "处理失败"));
        }
    }

    public function delRole() {
        $M = M("Role");
        $id = (int) $this->_get("id");
        //判断该分组下有无用户
        $res = M('role_user')->where('role_id='.$id)->select();
        if($res){
            $this->error("该角色下有用户不能删除");
        }else{
            $result = $M->where("id=" . $id)->limit('1')->delete();
            if($result !== false) {
                $this->success("删除成功",U('Access/roleList'));
            } else {
                $this->error("删除失败，请重试");
            }
        }
    }

    public function editNode() {
        if (IS_POST) {
            $this->checkToken();
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode(D("Access")->editNode());
        } else {
            $M = M("Node");
            $info = $M->where("id=" . (int) $_GET['id'])->find();
            if (empty($info['id'])) {
                $this->error("不存在该节点", U('Access/nodeList'));
            }
            $this->assign("info", $this->getPid($info));
            $this->display();
        }
    }

    public function addNode() {
        if (IS_POST) {
            $this->checkToken();
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode(D("Access")->addNode());
        } else {
            $this->assign("info", $this->getPid(array('level' => 1)));
            $this->display("editNode");
        }
    }

    public function getCate(){
        $M = D("Cate");
        $catesMulList = $M->fetchRawArray();
        self::echoJson($catesMulList);
    }

    /**
     * @var 上传图片
     * @return json {"status":1,"data":{"msg":"上传成功!","info":{"src":"39\/ae\/c\/bf8a13120673d3c785ddc4e6975_p1_mk1.jpg","ext":"jpg","width":1024,"height":768,"size":777835,"src_all_path":"http:\/\/src.leju.com\/imp\/imp\/deal\/39\/ae\/c\/bf8a13120673d3c785ddc4e6975_p1_mk1_osd8be35.jpg","src_all_small_path":"http:\/\/src.leju.com\/imp\/imp\/deal\/39\/ae\/c\/bf8a13120673d3c785ddc4e6975_p1_mk1_s100X100_osd8be35.jpg"}}}
     */
    public function doUpload(){
        import('ORG.Net.ResourceApi');
        $file = $_FILES['Filedata'];
        $model = D('Photo', 'LogicModel');
        $result = $model->upload($file);
      //  print_r($result);exit;
        if($result){
            $result['src_all_path'] = $model->url($result['src'], $result['ext']);
            $result['src_all_small_path'] = $model->url($result['src'], $result['ext'],'100X100');
            $result['src'] = $result['src'].'.'.$result['ext'];
            $data = array('msg' => '上传成功!','info'=>$result );
            $this->returnJson(1, $data);
        }else{
            $data = array('msg' => '上传失败！');
            $this->returnJson(0, $data);
        }
    }

    /**
     * @var 获取供应商所属地方站城市列表
     */
    public function getLocalStation(){
        $local_station = '';
        $local_station = RedisCacheModel::getKey(RedisCacheModel::LOCAL_STATION_LIST);

        if(!empty($local_station)){
            echo $local_station;
            exit;
        }else{
            $url = C('SHG_LOCAL_STATION_URL');
            $result = Request::get($url,'',60,true);
            if(isset($result['errCode']) && $result['errCode'] == 1000){
                $local_station = arrayColumn($result['data'],'name','code');
                if(count($local_station) != count($result['data'])){
                    //@todo,数量不一致就是有重复的code
                }
                RedisCacheModel::setKey(RedisCacheModel::LOCAL_STATION_LIST,$local_station);
            }

            $this->echoJson($local_station);
        }


    }


    /**
     * @var 添加用户
     */
    public function addAdmin() {
        $D = D("Access");

        if (IS_POST) {
            $post_data = I('post.');
            $post_data['enterprise_qualification'] = I('post.filedata','');
            $post_data['shop_cate'] = I('post.cate','');
            $post_data['shop_brand'] = I('post.brand','');
            //print_r($post_data);exit;
           // print_r($this->my_info);exit;
            $this->checkToken();
            //获取相应的插入数据
            $data = AccessLogicModel::getInsertData($this->my_info,$post_data);
            if($data['error_msg']){
                $json_array = array('status' => 0, 'info' => $data['error_msg']);
                $this->echoJson($json_array);
            }

            //添加
            $D->startTrans();
            try {
                $result = AccessLogicModel::insertData($data);
                if($result > 0){
                    $D->commit();
                    $this->echoJson(array('status' => 1, 'info' => '账号已开通，请通知相关人员','url' => U("Access/index")));
                }else{
                    throw new Exception("新增失败", 1);
                }
            } catch (Exception $e) {
                    $D->rollback();
                    $this->echoJson(array('status' => 0, 'info' => $e->getMessage()));
            }

        } else {

            $info = array();
           // $info['roleOption'] = $D->getAccessView($this->my_info['role_id']);
            $info['roleOption'] = $D->getAccessView(6);
           // print_r($info['roleOption']);exit;
            $info['role_id'] = $this->my_info['role_id'];
            $info['supplier_id'] = $this->my_info['supplier_id'];
            $DC = D('City');
            $info['province'] = $DC->showProvince();
            $info['bank_type'] = AccessLogicModel::getBankType();
            $BD = D('Brand');
            $info['brand_list'] = $BD->getAllList();
            if($D->checkIsShgOperator($this->my_info['role_id'])){
                $this->assign("is_shg_operator", 1);
            }else{
                $this->assign("is_shg_operator", 0);
            }
            //print_r($info);exit;
            $this->assign("info", $info);
            $this->display();
        }
    }

    /**
     * @var  获取所属供应商
     * @todo 等待分相应文件优化
     */
    public function getOwnerSupplier(){
        $role_id      = I('get.role_id');
        $status_array = array(1=>AccessModel::STATUS_NORMAL,2=>array('gt',0));
        $status_id    = I('get.status_id',1);
        $status       = $status_array[$status_id];

        if($this->my_info['role_id'] >= $role_id){
            $this->returnJson(0, '1没有所属供应商');
        }
        $data = array();
        if($this->my_info['role_id'] == AccessModel::SUPPLER || $this->my_info['role_id'] == AccessModel::PLATFORM){
            $data['is_disabled'] = 0;
            //获取所有正常的供应商
            $D = D("Access");
            $field = 'id,business_name';
            $where = array(
                'status'  => $status,
                'role_id' => AccessModel::SUPPLIER,
                );
            $data['list'] = $D->getList($field,$where);

            $this->returnJson(1, $data);
        }else if($this->my_info['role_id'] == AccessModel::SUPPLIER){
            $data['is_disabled'] = 1;
            $data['list'] = array(
                    'id'   => $this->my_info['id'],
                    'business_name' => $this->my_info['business_name']
                );
            $this->returnJson(1, $data);
        }else if($this->my_info['role_id'] == AccessModel::COMPANY){
            $data['is_disabled'] = 1;
            $D = D("Access");
            $field = 'id,business_name';
            $where = array(
                'status'  => $status,
                'id'      => $this->my_info['supplier_id']
                );
            $supplier = $D->getOne($field,$where);
            $data['list'] = array(
                    'id'   => $supplier['id'],
                    'business_name' => $supplier['business_name']
                );
            $this->returnJson(1, $data);
        }else{
            $this->returnJson(0, '没有所属供应商');
        }

    }

    /**
     * @var  获取所属分公司
     * @todo 等待分相应文件优化
     */
    public function getOwnerCompany(){
        $role_id      = I('get.role_id');
        $supplier_id  = I('get.supplier_id');
        $status_array = array(1=>AccessModel::STATUS_NORMAL,2=>array('gt',0));
        $status_id    = I('get.status_id',1);
        $status       = $status_array[$status_id];

        if($this->my_info['role_id'] >= $role_id){
            $this->returnJson(0, '没有所属分公司');
        }
        $data = array();
        if($this->my_info['role_id'] == AccessModel::SUPPLER || $this->my_info['role_id'] == AccessModel::PLATFORM){
            $data['is_disabled'] = 0;
            if($supplier_id){
                //获取所有正常的所属分公司
                $D = D("Access");
                $field = 'id,business_name';
                $where = array(
                    'status'      => $status,
                    'supplier_id' => $supplier_id,
                    'role_id'     => AccessModel::COMPANY,
                    );
                $data['list'] = $D->getList($field,$where);
                $this->returnJson(1, $data);
            }else{
                $data['list'] = array();
                $this->returnJson(1, $data);
            }

        }else if($this->my_info['role_id'] == AccessModel::SUPPLIER){
            $data['is_disabled'] = 0;
            //获取供应商下面的所有分公司
            $D = D("Access");
            $field = 'id,business_name';
            $where = array(
                'status'      => $status,
                'supplier_id' => $this->my_info['id'],
                'role_id'     => AccessModel::COMPANY,
                );
            $data['list'] = $D->getList($field,$where);
            $this->returnJson(1, $data);

        }else if($this->my_info['role_id'] == AccessModel::COMPANY){
            $data['is_disabled'] = 1;
            $data['list'] = array(
                    'id'   => $this->my_info['id'],
                    'business_name' => $this->my_info['business_name']
                );
            $this->returnJson(1, $data);

        }else{
            $this->returnJson(0, '没有所属分公司');
        }
    }

    /**
     * @var  获取所属实体店
     * @todo 等待分相应文件优化
     */
    public function getOwnerShop(){
        $role_id = I('get.role_id');
        $company_id = I('get.company_id');
        if($this->my_info['role_id'] >= $role_id){
            $this->returnJson(0, '没有所属分公司');
        }
        $data = array();
        if($this->my_info['role_id'] == AccessModel::SUPPLER || $this->my_info['role_id'] == AccessModel::PLATFORM || $this->my_info['role_id'] == AccessModel::SUPPLIER){
            $data['is_disabled'] = 0;
            if($company_id){
                $D = D("Access");
                $field = 'id,business_name';
                $where = array(
                    'company_id' => $company_id,
                    'role_id'    => AccessModel::SHOP,
                    'status'     => array('gt',0)
                    );
                $data['list'] = $D->getList($field,$where);
                $this->returnJson(1, $data);
            }else{
                $data['list'] = array();
                $this->returnJson(1, $data);
            }

        }else if($this->my_info['role_id'] == AccessModel::COMPANY){
            $data['is_disabled'] = 1;
            $D = D("Access");
            $field = 'id,business_name';
            $where = array(
                'company_id' => $this->my_info['id'],
                'role_id'    => AccessModel::SHOP,
                'status'     => array('gt',0)
                );
            $data['list'] = $D->getList($field,$where);
            $this->returnJson(1, $data);

        }else{
            $this->returnJson(0, '没有所属实体店');
        }
    }

    public function getCityByProvinceId(){
        try {
            $province_id = I('get.province_id');
            if($province_id <= 0){
                throw new Exception("所选省份错误", 1);
            }
            $DC = D('City');
            $citys = $DC->showCityByProvince($province_id);
            if(empty($citys)){
                throw new Exception("所选省份没有城市", 1);
            }
            $this->returnJson(1, $citys);

        } catch (Exception $e) {
            $this->returnJson(1, array());
        }

    }

    public function changeRole() {
        header('Content-Type:application/json; charset=utf-8');
        if (IS_POST) {
            $this->checkToken();
            echo json_encode(D("Access")->changeRole());
        } else {
            $M = M("Node");
            $info = M("Role")->where("id=" . (int) $_GET['id'])->find();
            if (empty($info['id'])) {
                $this->error("不存在该用户组", U('Access/roleList'));
            }
            $access = M("Access")->field("CONCAT(`node_id`,':',`level`,':',`pid`) as val")->where("`role_id`=" . $info['id'])->select();
            $info['access'] = count($access) > 0 ? json_encode($access) : json_encode(array());
            $this->assign("info", $info);
            $datas = $M->where("level=1")->select();
            foreach ($datas as $k => $v) {
                $map['level'] = 2;
                $map['pid'] = $v['id'];
                $map['status'] = 1;
                $datas[$k]['data'] = $M->where($map)->select();
                foreach ($datas[$k]['data'] as $k1 => $v1) {
                    $map['level'] = 3;
                    $map['pid'] = $v1['id'];
                    $datas[$k]['data'][$k1]['data'] = $M->where($map)->select();
                }
            }
            $this->assign("nodeList", $datas);
            $this->display();
        }
    }

    /**
     * @var 封装编辑用户返回
     */
    private function editAdminReturn($type,$result,$msg,$url){
        if('post' == $type){
            if('error'==$result){
                $status = 0;
            }else{
                $status = 1;
            }
            $array = array();
            $array['status'] = $status;
            $array['info']   = $msg;
            if(!empty($url)){
                $array['url']   = $url;
            }
            $this->echoJson($array);
            exit;
        }else{
            $this->$result($msg, $url);
        }
    }


    /**
     * @var 编辑用户
     */
    public function editAdmin() {
        $D = D("Access");
        if(IS_POST){
            $id      = I('post.id');
            $role_id = I('post.role_id');
            $type    = 'post';
        }else{
            $id      = I('get.id');
            $role_id = I('get.role_id');
            $type    = 'get';
        }

        $role_name = $D->getRoleName('en-us');
        if(isset($role_name[$role_id])){
            $user_info = AccessLogicModel::getEditInfoByIdAndRoleName($id,$role_name[$role_id]);

            if(!empty($user_info)){
                //判定操作者是否允许编辑此id
                $is_edit = AccessLogicModel::getIsEdit($this->my_info,$user_info);
                if(false == $is_edit){
                    // $this->error("不允许操作该用户ID", U('Access/index'));
                    $this->editAdminReturn($type,'error','不允许操作该用户ID',U('Access/index'));
                }

                $user_info['role_id_zh'] = $D->getRoleName()[$user_info['role_id']];
            }else{
                $this->editAdminReturn($type,'error','不存在该用户ID',U('Access/index'));
            }
        }else{
            $this->editAdminReturn($type,'error','不存在该用户RoleID',U('Access/index'));
        }

        if(IS_POST){
            //获取相应的更新数据
            $post_data = I('post.');
            $post_data['enterprise_qualification'] = I('post.filedata');
            $post_data['shop_cate'] = I('post.cate','');
            $post_data['shop_brand'] = I('post.brand','');
            $data = AccessLogicModel::getUpdateData($this->my_info,$post_data);

            if($data['error_msg']){
                $this->editAdminReturn($type,'error',$data['error_msg']);
            }

            $D->startTrans();
            try {
                $result = AccessLogicModel::updateData($id,$data);
                if(false === $result){
                    throw new Exception("更新失败", 1);
                }else if ($result > 0){
                    $D->commit();
                    //清除缓存
                    D('DelCache','LogicModel')->DelShopCommandCache();
                    D('DelCache','LogicModel')->DelRedisShopCache();
                    D('DelCache','LogicModel')->DelRedisCityShopCache();
                    D('DelCache','LogicModel')->DeleteShopMap();
                    $this->editAdminReturn($type,'success','更新成功',U('Access/index'));
                }else{
                    throw new Exception("没有更新", 1);
                }

            } catch (Exception $e) {
                $D->rollback();
                $this->editAdminReturn($type,'error',$e->getMessage(),U('Access/index'));
            }

        }else{
            $user_info['platform_rate']  = sprintf("%.2f",$user_info['platform_rate']*100);
            $user_info['bank_type_list'] = AccessLogicModel::getBankType();
            $DC = D('City');
            $city = array();
            $city['provinces'] = $DC->showProvince();
            $province_id       = $DC->showProvinceByCity($user_info['city_id']);
            $city['citys']     = $DC->showCityByProvince($province_id);

            if(isset($user_info['ext']['enterprise_qualification']) && !empty($user_info['ext']['enterprise_qualification'])){
                $enterprise_qualification = $user_info['ext']['enterprise_qualification'];
                $this->assign("enterprise_qualification",$enterprise_qualification);
            }
            if(isset($user_info['ext']['advertising_map_url']) && !empty($user_info['ext']['advertising_map_url'])){
                $advertising_map_url = $user_info['ext']['advertising_map_url'];
                $this->assign("advertising_map_url",$advertising_map_url);
            }

            $this->assign("province_id", $province_id);
            $this->assign("local_station_id", $user_info['local_station_code']);
            $this->assign("city_id", $user_info['city_id']);
            $this->assign("bank_type", $user_info['bank_type']);
            $this->assign("brand_list", D('Brand')->getAllList());
            $this->assign("city", $city);

            $this->assign("info", $user_info);
            $shop_cate = array();
            if(is_array($user_info['ext']['shop_cate'])){
                $shop_cate = jsonEncode($user_info['ext']['shop_cate']);
            }
            if($D->checkIsShgOperator($this->my_info['role_id'])){
                $this->assign("is_shg_operator", 1);
            }else{
                $this->assign("is_shg_operator", 0);
            }
            $this->assign("shop_cate_html", $shop_cate);
            $this->display();
        }

    }

    private function getRole($info = array()) {
        import("Category");
        $cat = new Category('Role', array('id', 'pid', 'name', 'fullname'));
        $list = $cat->getList();               //获取分类结构
        foreach ($list as $k => $v) {
            $disabled = $v['id'] == $info['id'] ? ' disabled="disabled"' : "";
            $selected = $v['id'] == $info['pid'] ? ' selected="selected"' : "";
            $info['pidOption'].='<option value="' . $v['id'] . '"' . $selected . $disabled . '>' . $v['fullname'] . '</option>';
        }
        return $info;
    }

    // private function getRoleListOption($info = array()) {
    //     import("Category");
    //     $cat = new Category('Role', array('id', 'pid', 'name', 'fullname'));
    //     $list = $cat->getList();               //获取分类结构
    //     $info['roleOption'] = "";
    //     foreach ($list as $v) {
    //         $disabled = $v['id'] == 1 ? ' disabled="disabled"' : "";
    //         $selected = $v['id'] == $info['role_id'] ? ' selected="selected"' : "";
    //         $info['roleOption'].='<option value="' . $v['id'] . '"' . $selected . $disabled . '>' . $v['fullname'] . '</option>';
    //     }
    //     return $info;
    // }

    private function getPid($info) {
        $arr = array("请选择", "项目", "模块", "操作");
        for ($i = 1; $i < 4; $i++) {
            $selected = $info['level'] == $i ? " selected='selected'" : "";
            $info['levelOption'].='<option value="' . $i . '" ' . $selected . '>' . $arr[$i] . '</option>';
        }
        $level = $info['level'] - 1;
        import("Category");
        $cat = new Category('Node', array('id', 'pid', 'title', 'fullname'));
        $list = $cat->getList();               //获取分类结构
        $option = $level == 0 ? '<option value="0" level="-1">根节点</option>' : '<option value="0" disabled="disabled">根节点</option>';
        foreach ($list as $k => $v) {
            $disabled = $v['level'] == $level ? "" : ' disabled="disabled"';
            $selected = $v['id'] != $info['pid'] ? "" : ' selected="selected"';
            $option.='<option value="' . $v['id'] . '"' . $disabled . $selected . '  level="' . $v['level'] . '">' . $v['fullname'] . '</option>';
        }
        $info['pidOption'] = $option;
        return $info;
    }

}