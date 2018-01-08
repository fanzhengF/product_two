<?php

/*
 * 权限LogicModel
 * @todo 分成6个文件
 */

class AccessLogicModel {

    /**
     * @var 获取所属部门数组
     */
    static public function getDepartment(){
        return array(
            1 => '到家服务部',
            2 => '产品部',
            3 => '技术部'
            );
    }

    /**
     * @var 获取银行类型数组
     */
    static public function getBankType(){
        return array(
            1 => '对公',
            2 => '对私',
            );
    }

    /**
     * @var 判断操作者时候可以编辑$data数据
     */
    static public function getIsEdit($operator,$data){
        $D = D("Access");
        $role_name    = $D->getRoleName('en-us');
        $role_name[1] = 'supper';
        if(isset($role_name[$operator['role_id']])){
            $function = '_isEdit' . ucfirst($role_name[$operator['role_id']]);
            $result   = self::$function($operator,$data);
            return $result;
        }else{
            return false;
        }

    }

    /**
     * @var 操作着是超级管理员时是否可编辑判断(可以编辑所有)
     */
    static private function _isEditSupper($operator,$data){
        return true;
    }

    /**
     * @var 操作着是平台管理员时是否可编辑判断(可以编辑所有供应商,分公司,实体店)
     */
    static private function _isEditPlatform($operator,$data){
        if($operator['role_id'] < $data['role_id']){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @var 操作着是供应商时是否可编辑判断(只可以编辑$data里所属供应商是操作者的)
     */
    static private function _isEditSupplier($operator,$data){
        if($operator['id'] == $data['supplier_id']){
            return true;
        }else{
            return false;
        }

    }

    /**
     * @var 操作着是分公司时是否可编辑判断(只可以编辑$data里所属分公司是操作者的)
     */
    static private function _isEditCompany($operator,$data){
        if($operator['id'] == $data['company_id']){
            return true;
        }else{
            return false;
        }
    }


    /**
     * @var 操作着是实体店时是否可编辑判断(拒绝编辑所有)
     */
    static private function _isEditShop($operator,$data){
        return false;
    }

    /**
     * @var 获取相应的插入数据
     */
    static public function getInsertData($operator,$data){
       // print_r($data);exit;
        //print_r($operator);exit;
        //检测共用字段
        $common_data = self::_checkInsertCommon($operator['role_id'],$data);
        //print_r($common_data);exit;
        if(isset($common_data['error_msg'])){
            return $common_data;
        }

        //检测相应字段
        $D = D("Access");
        $role_name = $D->getRoleName('en-us');
       // print_r($role_name);exit;
        if(isset($role_name[$data['role_id']])){
            $individuation_data = self::{'_'.$role_name[$data['role_id']]}($data,$operator);
            if(isset($individuation_data['error_msg'])){
                return $individuation_data;
            }

            return array_merge($common_data,$individuation_data);
        }else{
            return array('error_msg'=>'用户角色异常');
        }

    }

    /**
     * @var 获取相应的更新数据
     */
    static public function getUpdateData($operator,$data){
        //检测共用字段
        $common_data = self::_checkUpdateCommon($operator['role_id'],$data);
        if(isset($common_data['error_msg'])){
            return $common_data;
        }

        //检测相应字段
        $D = D("Access");
        $role_name = $D->getRoleName('en-us');

        if(isset($role_name[$data['role_id']])){
            $update_data = self::{'_'.$role_name[$data['role_id']]}($data,$operator,'update');
            if(isset($update_data['error_msg'])){
                return $update_data;
            }

            return array_merge($common_data,$update_data);
        }else{
            return array('error_msg'=>'用户角色异常');
        }
    }

    /**
     * @var 更新数据
     */
    static public function updateData($id,$data){
        $D = D("Access");
        $role_name = $D->getRoleName('en-us');
        $role_id = $data['role_id'];
        unset($data['role_id']);
        $result = self::{'_'.$role_name[$role_id].'Update'}($id,$data);
        return $result;
    }

    /**
     * @var 插入数据
     */
    static public function insertData($data){
        $D = D("Access");
        $role_name = $D->getRoleName('en-us');
        $result = self::{'_'.$role_name[$data['role_id']].'Insert'}($data);
        return $result;
    }



    /*
        *@var 机构处理
        * */
    static public function _jigou($data, $operator) {
        return $data;
    }


    /**
     * @var 新增机构管理
     */
    static private function _jigouInsert($data){
        $data['pwd'] = encrypt($data['pwd']);
        $data['create_time'] = time();
        $id = D("Access")->addAdmin($data);
        return $id;
    }




    /*
       *@var 学生处理
       * */
    static public function _student($data, $operator) {
        return $data;
    }


    /**
     * @var 新增学生管理
     */
    static private function _studentInsert($data){
        $data['pwd'] = encrypt($data['pwd']);
        $data['create_time'] = time();
        $id = D("Access")->addAdmin($data);
        return $id;
    }




    /*
     *@var 教师处理
     * */
    static public function _teacher($data, $operator) {
        return $data;
    }


    /**
     * @var 新增教师管理
     */
    static private function _teacherInsert($data){
        $data['pwd'] = encrypt($data['pwd']);
        $data['create_time'] = time();
        $id = D("Access")->addAdmin($data);
        return $id;
    }



    /*
    *@var 校外处理
    * */
    static public function _xiaowai($data, $operator) {
        return $data;
    }


    /**
     * @var 新增校外管理
     */
    static private function _xiaowaiInsert($data){
        $data['pwd'] = encrypt($data['pwd']);
        $data['create_time'] = time();
        $id = D("Access")->addAdmin($data);
        return $id;
    }




    /*
    *@var 会议室处理
    * */
    static public function _huiyishi($data, $operator) {
        return $data;
    }


    /**
     * @var 新增会议室管理
     */
    static private function _huiyishiInsert($data){
        $data['pwd'] = encrypt($data['pwd']);
        $data['create_time'] = time();
        $id = D("Access")->addAdmin($data);
        return $id;
    }




    /*
  *@var 会议处理
  * */
    static public function _huiyi($data, $operator) {
        return $data;
    }


    /**
     * @var 新增会议管理
     */
    static private function _huiyiInsert($data){
        $data['pwd'] = encrypt($data['pwd']);
        $data['create_time'] = time();
        $id = D("Access")->addAdmin($data);
        return $id;
    }



    /*
*@var 其他处理
* */
    static public function _qita($data, $operator) {
        return $data;
    }


    /**
     * @var 新增其他管理
     */
    static private function _qitaInsert($data){
        $data['pwd'] = encrypt($data['pwd']);
        $data['create_time'] = time();
        $id = D("Access")->addAdmin($data);
        return $id;
    }

    /**
     * @var 创建平台管理员字段处理
     */
    static private function _platform($data,$operator){
        $result = array();
        $department = self::getDepartment();
        if(isset($department[$data['department']])){
            $result['department'] = $data['department'];
        }else{
            $result['error_msg'] = '所属部门错误';
        }

        return $result;
    }

    /**
     * @var 创建供应商字段处理
     */
    static private function _supplier($data,$operator){
        $result = array();

        //供应商名称
        if(empty($data['supplier_name'])){
            $result['error_msg'] = '供应商名称不能为空';
            return $result;
        }
        $result['business_name'] = $data['supplier_name'];

        //所在城市@todo暂时没做范围检查
        if(!is_numeric($data['city_id'])){
            $result['error_msg'] = '所在城市不能为空';
            return $result;
        }
        $result['city_id'] = $data['city_id'];

        if(empty($data['local_station_id'])){
            $result['error_msg'] = '所属地方站不能为空';
            return $result;
        }
        $result['local_station_code'] = $data['local_station_id'];

        //办公地址
        if(empty($data['office_address'])){
            $result['error_msg'] = '办公地址不能为空';
            return $result;
        }
        $result['office_address'] = $data['office_address'];

        //企业资质
        if(!empty($data['enterprise_qualification']) && is_array($data['enterprise_qualification'])){
            $result['enterprise_qualification'] = $data['enterprise_qualification'];
        }

        //平台扣点
        if(empty($data['platform_rate'])){
            $result['error_msg'] = '平台扣点不能为空';
            return $result;
        }
        if(!is_numeric($data['platform_rate'])){
            $result['error_msg'] = '平台扣点必须是数字';
            return $result;
        }
        $result['platform_rate'] = round($data['platform_rate'] / 100,4);

        //银行帐号类型
        if(!isset(self::getBankType()[$data['bank_type']])){
            $result['error_msg'] = '请选择正确银行帐号类型';
            return $result;
        }
        $result['bank_type'] = $data['bank_type'];

        //开户行
        if(empty($data['bank_deposit'])){
            $result['error_msg'] = '开户行不能为空';
            return $result;
        }
        $result['bank_deposit'] = $data['bank_deposit'];

        //开户行省
        if(empty($data['bank_province'])){
            $result['error_msg'] = '开户行省不能为空';
            return $result;
        }
        $result['bank_province'] = $data['bank_province'];

        //开户行市
        if(empty($data['bank_city'])){
            $result['error_msg'] = '开户行市不能为空';
            return $result;
        }
        $result['bank_city'] = $data['bank_city'];

        //支行名称
        if(empty($data['bank_branch'])){
            $result['error_msg'] = '支行名称不能为空';
            return $result;
        }
        $result['bank_branch'] = $data['bank_branch'];

        //银行账户名称
        if(empty($data['bank_name'])){
            $result['error_msg'] = '银行账户名称不能为空';
            return $result;
        }
        $result['bank_name'] = $data['bank_name'];

        //账号
        if(empty($data['bank_num'])){
            $result['error_msg'] = '账号不能为空';
            return $result;
        }
        $result['bank_num'] = $data['bank_num'];

        return $result;
    }

    /**
     * @var 创建分公司字段处理
     */
    static private function _company($data,$operator){
        $result = array();

        //创建分公司时,如果是供应商创建,那么操作者的id必须和所属供应商id一致(即供应商只能创建自己底下的分公司)
        if($operator['role_id'] > AccessModel::PLATFORM  && $data['supplier_id'] != $operator['id']){
            $result['error_msg'] = '没有操作此所属供应商权限';
            return $result;
        }
        //所属供应商
        $result['supplier_id'] = $data['supplier_id'];

        //分公司名称
        if(empty($data['company_name'])){
            $result['error_msg'] = '分公司名称不能为空';
            return $result;
        }
        $result['business_name'] = $data['company_name'];

        //所在城市@todo暂时没做范围检查
        if(!is_numeric($data['city_id'])){
            $result['error_msg'] = '所在城市不能为空';
            return $result;
        }
        $result['city_id'] = $data['city_id'];

        //办公地址
        if(empty($data['office_address'])){
            $result['error_msg'] = '办公地址不能为空';
            return $result;
        }
        $result['office_address'] = $data['office_address'];

        //企业资质
        if(!empty($data['enterprise_qualification']) && is_array($data['enterprise_qualification'])){
            $result['enterprise_qualification'] = $data['enterprise_qualification'];
        }

        return $result;
    }

    /**
     * @var 创建实体店字段处理
     */
    static private function _shop($data,$operator,$type='insert'){
        $result = array();

        if($operator['role_id'] > AccessModel::COMPANY ){
            $result['error_msg'] = '没有操作此所属供应商权限';
            return $result;
        }

        //供应商的所属供应商只能是自己
        if(AccessModel::SUPPLIER == $operator['role_id'] && $data['supplier_id'] != $operator['id']){
            $result['error_msg'] = '没有操作此所属供应商权限';
            return $result;
        }
        //所属供应商
        $result['supplier_id'] = $data['supplier_id'];

        //所属分公司
        if(empty($data['company_id'])){
            $result['error_msg'] = '请选择所属分公司';
            return $result;
        }
        //分公司的所属分公司只能是自己
        if(AccessModel::COMPANY == $operator['role_id'] && $data['company_id'] != $operator['id']){
            $result['error_msg'] = '没有操作此所属供应商权限';
            return $result;
        }
        //所属分公司的所属供应商必须是正确的
        $D = D('Access');
        $field = 'supplier_id';
        $where = array(
            'status'  => AccessModel::STATUS_NORMAL,
            'id'      => $data['company_id'],
            );
        $company_data = $D->getOne($field,$where);
        if(empty($company_data) || $company_data['supplier_id'] != $data['supplier_id']){
            $result['error_msg'] = '分公司的所属供应商不正确';
            return $result;
        }
        $result['company_id'] = $data['company_id'];

        //实体店名称
        if(empty($data['shop_name'])){
            $result['error_msg'] = '实体店名称不能为空';
            return $result;
        }
        $result['business_name'] = $data['shop_name'];

        //实体店名称首字母
        $result['first_name_letter'] = ucfirst(getfirstchar($result['business_name']));

        //所在城市@todo暂时没做范围检查
        if(!is_numeric($data['city_id'])){
            $result['error_msg'] = '所在城市不能为空';
            return $result;
        }
        $result['city_id'] = $data['city_id'];

        //办公地址
        if(empty($data['office_address'])){
            $result['error_msg'] = '办公地址不能为空';
            return $result;
        }
        $result['office_address'] = $data['office_address'];

        //经纬度
        if(empty($data['longitude_latitude'])){
            $result['error_msg'] = '经纬度不能为空';
            return $result;
        }
        if(false === strpos($data['longitude_latitude'],',')){
            $result['error_msg'] = '经纬度格式不正确,必须含有英文的,';
            return $result;
        }else{
            $longitude_latitude  = explode(',', $data['longitude_latitude']);
            $result['longitude'] = $longitude_latitude[0];
            $result['latitude']  = $longitude_latitude[1];
        }

        //经营类别
        if(empty($data['shop_cate']) || !is_array($data['shop_cate'])){
            $result['error_msg'] = '请选择至少一个经营类别';
            return $result;
        }
        $result['shop_cate'] = $data['shop_cate'];

        //经营品牌
        if(empty($data['shop_brand']) || !is_array($data['shop_brand'])){
            $result['error_msg'] = '请选择至少一个经营品牌';
            return $result;
        }
        $result['shop_brand'] = $data['shop_brand'];

        //企业资质
        if(!empty($data['enterprise_qualification']) && is_array($data['enterprise_qualification'])){
            $result['enterprise_qualification'] = $data['enterprise_qualification'];
        }

        //实体店宣传图
        if(empty($data['advertising_map']) ){
            $result['error_msg'] = '请选择上传一个实体店宣传图';
            return $result;
        }
        $result['advertising_map'] = $data['advertising_map'];

        //实体店序号
        $is_shg_operator = $D->checkIsShgOperator($operator['role_id']);
        //平台管理员以上必须填写实体店序号;其他人给予默认值
        if($is_shg_operator){
            if(!is_numeric($data['shop_sort'])){
                $result['error_msg'] = '实体店序号不能为空';
                return $result;
            }
            $result['shop_sort'] = $data['shop_sort'];
        }elseif($type == 'insert'){
            $result['shop_sort'] = 65535;
        }

        return $result;
    }

    /**
     * @var 新增实体店
     */
    static private function _shopInsert($data){
        //经营类别
        $shop_cate = $data['shop_cate'];
        unset($data['shop_cate']);

        //经营品牌
        $shop_brand = $data['shop_brand'];
        unset($data['shop_brand']);

        //企业资质
        $enterprise_qualification = $data['enterprise_qualification'];
        unset($data['enterprise_qualification']);

        $data['create_time'] = time();
        $id = D("Access")->addAdmin($data);
        if($id > 1 ){
            //绑定经营类别
            $MSC = M("ShopCate");
            $shop_cate_data = array();
            $shop_cate_data['shop_id'] = $id;
            foreach ($shop_cate as $key => $value) {
                $shop_cate_data['cate_id'] = $value;
                $result_sc = $MSC->add($shop_cate_data);
                if(!$result_sc){
                    throw new Exception("绑定经营类别失败", 1);
                }
            }

            //绑定经营品牌
            $MSB = M("ShopBrand");
            $shop_brand_data = array();
            $shop_brand_data['shop_id'] = $id;
            foreach ($shop_brand as $key => $value) {
                $shop_brand_data['brand_id'] = $value;
                $result_sb = $MSB->add($shop_brand_data);
                if(!$result_sb){
                    throw new Exception("绑定经营品牌失败", 1);
                }
            }

            //绑定企业资质
            if(!empty($enterprise_qualification) && is_array($enterprise_qualification)){
                self::_enterprise_qualification($id,$enterprise_qualification);
            }
        }else{
            throw new Exception("新增实体店失败", 1);
        }

        return $id;
    }

    /**
     * @var 修改实体店
     */
    static private function _shopUpdate($id,$data){

        //经营类别
        $shop_cate = $data['shop_cate'];
        unset($data['shop_cate']);

        //经营品牌
        $shop_brand = $data['shop_brand'];
        unset($data['shop_brand']);

        //企业资质
        $enterprise_qualification = $data['enterprise_qualification'];
        unset($data['enterprise_qualification']);

        unset($data['supplier_id']);
        unset($data['company_id']);

        //判断是否更新企业资质
        $MEQ = M("EnterpriseQualification");
        $result_eq = $MEQ->field('src')->where('shop_id='.$id)->select();

        //判断是否更新经营类别
        $MSC = M('ShopCate');
        $result_sc = $MSC->field('cate_id')->where('shop_id='.$id)->select();

        //判断是否更新经营品牌
        $MSB = M('ShopBrand');
        $result_sb = $MSB->field('brand_id')->where('shop_id='.$id)->select();

        $result = D("Access")->editAdmin($id,$data);

        $cate_id_array = arrayColumn($result_sc,'cate_id');
        sort($shop_cate);
        sort($cate_id_array);
        if($shop_cate != $cate_id_array){
            //先删除再绑定
            $MSC->where('shop_id='.$id)->delete();
            $shop_cate_data = array();
            $shop_cate_data['shop_id'] = $id;
            foreach ($shop_cate as $key => $value) {
                $shop_cate_data['cate_id'] = $value;
                $result_sc = $MSC->add($shop_cate_data);
                if(!$result_sc){
                    throw new Exception("绑定经营类别失败", 1);
                }
            }
            $result = true;
        }


        $brand_id_array = arrayColumn($result_sb,'brand_id');
        sort($shop_brand);
        sort($brand_id_array);
        if($shop_brand != $brand_id_array){
            //先删除再绑定
            $MSB->where('shop_id='.$id)->delete();
            $shop_brand_data = array();
            $shop_brand_data['shop_id'] = $id;
            foreach ($shop_brand as $key => $value) {
                $shop_brand_data['brand_id'] = $value;
                $result_sb = $MSB->add($shop_brand_data);
                if(!$result_sb){
                    throw new Exception("绑定经营品牌失败", 1);
                }
            }
            $result = true;
        }

        $src_array = arrayColumn($result_eq,'src');
        sort($src_array);
        sort($enterprise_qualification);
        if($src_array != $enterprise_qualification){
            //先删除再重新绑定
            $MEQ->where('shop_id='.$id)->delete();
            self::_enterprise_qualification($id,$enterprise_qualification);
            $result = true;
        }

        return $result;
    }

    /**
     * @var 增加企业资质记录
     */
    static private function _enterprise_qualification($id,$enterprise_qualification){
        $MEQ = M("EnterpriseQualification");
        $enterprise_qualification_data = array();
        $enterprise_qualification_data['shop_id'] = $id;
        foreach ($enterprise_qualification as $key => $value) {
            $enterprise_qualification_data['src'] = $value;
            $result_eq = $MEQ->add($enterprise_qualification_data);
            if(!$result_eq){
                throw new Exception("绑定企业资质失败", 1);
            }
        }
    }

    /**
     * @var 新增分公司
     */
    static private function _companyInsert($data){
        //企业资质
        $enterprise_qualification = $data['enterprise_qualification'];
        unset($data['enterprise_qualification']);

        $data['create_time'] = time();
        $id = D("Access")->addAdmin($data);
        if($id>1){
            //绑定企业资质
            if(!empty($enterprise_qualification) && is_array($enterprise_qualification)){
                self::_enterprise_qualification($id,$enterprise_qualification);
            }
        }else{
            throw new Exception("新增分公司失败", 1);
        }

        return $id;
    }

    /**
     * @var 修改分公司
     */
    static private function _companyUpdate($id,$data){
        //企业资质
        $enterprise_qualification = $data['enterprise_qualification'];
        unset($data['enterprise_qualification']);
        unset($data['supplier_id']);

        //判断是否更新企业资质
        $MEQ = M("EnterpriseQualification");
        $result_eq = $MEQ->where('shop_id='.$id)->select();

        $result = D("Access")->editAdmin($id,$data);

        $src_array = arrayColumn($result_eq,'src');
        sort($src_array);
        sort($enterprise_qualification);
        if($src_array != $enterprise_qualification){
            //先删除再重新绑定
            $MEQ->where('shop_id='.$id)->delete();
            self::_enterprise_qualification($id,$enterprise_qualification);
            $result = true;
        }

        return $result;
    }

    /**
     * @var 新增供应商
     */
    static private function _supplierInsert($data){
        //企业资质
        $enterprise_qualification = $data['enterprise_qualification'];
        unset($data['enterprise_qualification']);

        $data['create_time'] = time();
        $id=  D("Access")->addAdmin($data);
        if($id>1){
            //绑定企业资质
            if(!empty($enterprise_qualification) && is_array($enterprise_qualification)){
                self::_enterprise_qualification($id,$enterprise_qualification);
            }
        }else{
            throw new Exception("新增供应商失败", 1);
        }

        return $id;
    }

    /**
     * @var 修改供应商
     */
    static private function _supplierUpdate($id,$data){
        //企业资质
        $enterprise_qualification = $data['enterprise_qualification'];
        unset($data['enterprise_qualification']);

        //判断是否更新企业资质
        $MEQ = M("EnterpriseQualification");
        $result_eq = $MEQ->where('shop_id='.$id)->select();

        $result = D("Access")->editAdmin($id,$data);

        $src_array = arrayColumn($result_eq,'src');
        sort($src_array);
        sort($enterprise_qualification);
        if($src_array != $enterprise_qualification){
            //先删除再重新绑定
            $MEQ->where('shop_id='.$id)->delete();
            self::_enterprise_qualification($id,$enterprise_qualification);
            $result = true;
        }



        return $result;
    }

    /**
     * @var 新增平台管理员
     */
    static private function _platformInsert($data){
        $data['create_time'] = time();
        return D("Access")->addAdmin($data);
    }

    /**
     * @var 平台管理员更新
     */
    static private function _platformUpdate($id,$data){
        return D("Access")->editAdmin($id,$data);
    }

    static private function _checkUpdateCommon($operator_id,$data){
        $result = array();
        //检查用户角色组
        if($operator_id >= $data['role_id']){
            $result['error_msg'] = '没有修改此角色的权限';
            return $result;
        }
        $result['role_id'] = $data['role_id'];

        //检查状态
        $D = D("Access");
        $status= $D->getStatus();
        if(!isset($status[$data['status']])){
            $result['error_msg'] = '角色状态选择异常';
            return $result;
        }
        $result['status'] = $data['status'];

        //检查姓名
        if(empty($data['name'])){
            $result['error_msg'] = '负责人姓名不能为空';
            return $result;
        }
        $result['name'] = $data['name'];

        //检查邮箱
        if(empty($data['email'])){
            $result['error_msg'] = '负责人邮箱不能为空';
            return $result;
        }
        $result['email'] = $data['email'];

        //检查手机号
        if(empty($data['tel'])){
            $result['error_msg'] = '负责人手机号不能为空';
            return $result;
        }

        $user = $D->getOne('id,tel',array('tel'=>$data['tel']));
        if(isset($user['id']) && $user['tel'] != $data['tel']){
            $result['error_msg'] = '负责人手机号不能重复';
            return $result;
        }
        $result['tel'] = $data['tel'];

        return $result;
    }

    /**
     * @var 处理公共插入字段
     */
    static private function _checkInsertCommon($operator_id,$data){
        $result = array();
        //检查用户角色组
        if($operator_id >= $data['role_id']){
            $result['error_msg'] = '没有新增此角色的权限';
            return $result;
        }
        $result['role_id'] = $data['role_id'];

        //检查状态
        $D = D("Access");
        $status= $D->getStatus();
        if(!isset($status[$data['status']])){
            $result['error_msg'] = '角色状态选择异常';
            return $result;
        }
        $result['status'] = $data['status'];

        //检查姓名
        if(empty($data['name'])){
            $result['error_msg'] = '负责人姓名不能为空';
            return $result;
        }
        $result['name'] = $data['name'];

        //检查邮箱
        if(empty($data['email'])){
            $result['error_msg'] = '负责人邮箱不能为空';
            return $result;
        }
        $result['email'] = $data['email'];

        //检查手机号
        if(empty($data['tel'])){
            $result['error_msg'] = '负责人手机号不能为空';
            return $result;
        }
        $user = $D->getOne('id',array('tel'=>$data['tel'],'status'=>array('gt',0)));
        if(isset($user['id'])){
            $result['error_msg'] = '负责人手机号不能重复';
            return $result;
        }
        $result['tel'] = $data['tel'];

        //检查密码
        $pwd = strlen($data['pwd']);
        if($pwd < 6 || $pwd > 18){
            $result['error_msg'] = '密码必须为6-18位英文或数字';
            return $result;
        }
        $result['pwd'] = encrypt($data['pwd']);


        //print_r($result);exit;

        return $result;
    }

    /**
     * @var                         获取要禁用的id数组
     * @param    int    $id         要禁用的id
     * @param    int    $role_id    要禁用的id所属角色组id
     *                              1   为超级管理员  只禁用自己
     *                              2   为平台管理员  只禁用自己
     *                              3   为供应商      禁用自己及supplier_id为id的
     *                              4   为分公司      禁用自己及company_id为id的
     *                              5   为实体店      只禁用自己
     * @return   array              返回要禁用的数组
     */
    static function getStatusForbidden($id,$role_id){
        $access_search = C('ACCESS_SEARCH.access');
        $result = array();
        if(isset($access_search['forbidden'][$role_id]) && !empty($access_search['forbidden'][$role_id])){
            $D = D("Access");
            $field = 'id';
            $where[$access_search['forbidden'][$role_id]] = $id;
            $result_list = $D->getList($field,$where);
            if(!empty($result_list)){
                $result = arrayColumn($result_list,'id');
            }

            $result[] = $id;

        }else{
            $result[] = $id;
        }

        return $result;
    }

    /**
     * @var 用户上级判断,如果上级是禁用的,也禁止启用
     */
    static public function checkIsAllowStatus($id,$role_id){
        $access_search = C('ACCESS_SEARCH.access');
        $result = array();
        if(isset($access_search['normal'][$role_id]) && !empty($access_search['normal'][$role_id])){
            $D = D("Access");
            $field = $access_search['normal'][$role_id];
            $where['id'] = $id;


            $result = $D->getOne($field,$where);
            if(empty($result)){
                throw new Exception("不存在用户", 1);
            }
            $field = 'id';
            foreach ($result as $key => $value) {
                $where['id'] = $value;
                $where['status'] = AccessModel::STATUS_NORMAL;
                $result = $D->getOne($field,$where);
                if(empty($result)){
                    throw new Exception("请将上级启用,在启用此用户", 1);
                }
            }
        }


    }

    /**
     * @var 根据id,role_name获取编辑用户的信息
     */
    static public function getEditInfoByIdAndRoleName($id,$role_name){
        $field = self::getFields($role_name);
        $where['id'] = $id;
        $D = D("Access");
        $user_info = $D->getOne($field,$where);

        //获取其他表扩展信息
        if(!empty($user_info)){
            $function = '_get'.ucfirst($role_name).'Ext';
            $user_info['ext'] = self::$function($id,$user_info);
        }
        return $user_info;
    }

    /**
     * @var 获取相应角色的字段
     */
    static private function getFields($role_name){
        $function = '_get'.ucfirst($role_name).'Fields';
        return self::$function();
    }

    /**
     * @var 根据id获取企业资质
     */
    static private function _getEnterpriseQualification($id){
        $result = array();
        //获取企业资质
        $MEQ = M("EnterpriseQualification");
        $result_eq = $MEQ->field('src')->where('shop_id='.$id)->select();
        if(!empty($result_eq)){
            $arr_src = array();
            $arr_src_all_path = array();
            $arr_src_big_all_path = array();
            $model = D('Photo', 'LogicModel');
            foreach ($result_eq as $k=>$v){
                $pic = explode(".",$v['src']);
                $arr_src[$k]              = $v['src'];
                $arr_src_all_path[$k]     = $model->url($pic[0], $pic[1],'100X100');
                $arr_src_big_all_path[$k] = $model->url($pic[0], $pic[1],'1000X1000');
            }
                $enterprise_qualification['src'] = implode(',',$arr_src);
                $enterprise_qualification['src_all_path'] = implode(',',$arr_src_all_path);
                $enterprise_qualification['src_big_all_path'] = implode(',',$arr_src_big_all_path);
            $result['enterprise_qualification'] = $enterprise_qualification;
        }

        return $result;
    }

    /**
     * @var 获取平台管理员字段
     */
    static private function _getPlatformFields(){
        return 'id,role_id,status,name,email,tel,department';
    }

    /**
     * @var 获取平台管理员扩展字段
     */
    static private function _getPlatformExt($id,$user_info){
        return array();
    }
    /**
     * @var 获取供应商字段
     */
    static private function _getSupplierFields(){
        return 'id,role_id,status,business_name,city_id,local_station_code,platform_rate,office_address,name,email,tel,bank_type,bank_deposit,bank_province,bank_city,bank_branch,bank_name,bank_num';
    }

    /**
     * @var 获取供应商扩展字段
     */
    static private function _getSupplierExt($id,$user_info){
        //获取企业资质
        $result = array();
        $result = self::_getEnterpriseQualification($id);
        return $result;
    }

    /**
     * @var 获取分公司字段
     */
    static private function _getCompanyFields(){
        return 'id,role_id,status,supplier_id,business_name,city_id,office_address,name,email,tel';
    }

    /**
     * @var 获取分公司扩展字段
     */
    static private function _getCompanyExt($id,$user_info){
        //获取企业资质
        $result = array();
        $result = self::_getEnterpriseQualification($id);

        //获取所属供应商名称
        $field = 'business_name';
        $where['id'] = $user_info['supplier_id'];
        $D = D("Access");
        $parent_data = $D->getOne($field,$where);
        if(isset($parent_data['business_name'])){
            $result['supplier_id_name'] = $parent_data['business_name'];
        }

        return $result;
    }
    /**
     * @var 获取实体店字段
     */
    static private function _getShopFields(){
        return 'id,role_id,status,supplier_id,company_id,business_name,city_id,office_address,longitude,latitude,advertising_map,shop_sort,name,email,tel';
    }

    /**
     * @var 获取实体店扩展字段
     */
    static private function _getShopExt($id,$user_info){
        //获取企业资质
        $result = array();
        $result = self::_getEnterpriseQualification($id);

        //获取所属供应商名称
        $field = 'business_name';
        $where['id'] = $user_info['supplier_id'];
        $D = D("Access");
        $parent_data = $D->getOne($field,$where);
        if(isset($parent_data['business_name'])){
            $result['supplier_id_name'] = $parent_data['business_name'];
        }

        //获取所属分公司名称
        $where['id'] = $user_info['company_id'];
        $parent_data = $D->getOne($field,$where);
        if(isset($parent_data['business_name'])){
            $result['company_id_name'] = $parent_data['business_name'];
        }

        //经纬度
        $result['longitude_latitude'] = $user_info['longitude'] . ',' . $user_info['latitude'];

        //经营项目
        $DSC = D('ShopCate');
        $result_sc = $DSC->where('shop_id='.$id)->field('cate_id')->select();
        $result['shop_cate'] = $result_sc;
        $DC = D('Cate');
        foreach ($result['shop_cate'] as $key => $value) {
            $result['shop_cate'][$key]['cate'] = $DC->fetchParents($value['cate_id']);
        }

        //经营品牌
        $DSB = D('ShopBrand');
        $result_sb = $DSB->where('shop_id='.$id)->field('brand_id')->select();
        $result['shop_brand'] = arrayColumn($result_sb,'brand_id');

        //实体店宣传图
        $model = D('Photo', 'LogicModel');
        $pic = explode(".",$user_info['advertising_map']);
        $result['advertising_map_url']['src']              = $user_info['advertising_map'];
        $result['advertising_map_url']['src_all_path']     = $model->url($pic[0], $pic[1],'100X100');
        $result['advertising_map_url']['src_big_all_path'] = $model->url($pic[0], $pic[1],'1000X1000');

        return $result;
    }

    /**
     * @var 根据用户信息获取供应商优惠设置里供应商列表
     */
    static public function getSaleSupplierListByUserInfo($user_info){
        $result = array(
            'list' => array(),
            'is_disabled' => 1,
            );

        if($user_info['role_id'] == AccessModel::SUPPLIER){
            $result['list'][$user_info['id']] = $user_info['business_name'];
        }else{
            $DA = D("Access");
            $field = 'id,business_name';
            $where = array(
                'status'  => AccessModel::STATUS_NORMAL,
                'role_id' => AccessModel::SUPPLIER,
                );
            $list = $DA->getList($field,$where);
            if(!empty($list)){
                $result['list'] = arrayColumn($list,'business_name','id');
            }
            $result['is_disabled'] = 0;
        }

        return $result;
    }

    /**
     * @var 获取相应的用户初始列表
     */
    static public function getInitUserListForOrderAction($access,$id){
        $data = array();
        if(current($access) == 1){
            $role_name = key($access);
            $function = '_'.$role_name . 'ForOrderActionById';
            $data[$role_name] = self::$function($id);
        }
        return $data;
    }

    /**
     * @var 获取所有供应商
     */
    static private function _supplierForOrderActionById($id){
        $DA = D("Access");
        $field = 'id,business_name';
        $where = array(
            'role_id' => AccessModel::SUPPLIER,
            'status'  => array('gt',0)
            );

        $list = $DA->getList($field,$where);
        if(!empty($list)){
            return arrayColumn($list,'business_name','id');
        }else{
            return array();
        }
    }

    /**
     * @var 获取所有分公司
     */
    static private function _companyForOrderActionById($id){
        $DA = D("Access");
        $field = 'id,business_name';
        $where = array(
            'role_id'     => AccessModel::COMPANY,
            'supplier_id' => $id,
            'status'      => array('gt',0)
            );

        $list = $DA->getList($field,$where);
        if(!empty($list)){
            return arrayColumn($list,'business_name','id');
        }else{
            return array();
        }
    }

    /**
     * @var 获取所有分公司
     */
    static private function _shopForOrderActionById($id){
        $DA = D("Access");
        $field = 'id,business_name';
        $where = array(
            'role_id'    => AccessModel::SHOP,
            'company_id' => $id,
            'status'     => array('gt',0)
            );

        $list = $DA->getList($field,$where);
        if(!empty($list)){
            return arrayColumn($list,'business_name','id');
        }else{
            return array();
        }
    }
}
