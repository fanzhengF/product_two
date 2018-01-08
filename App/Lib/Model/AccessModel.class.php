<?php

class AccessModel extends Model {
    //正常账号
    const STATUS_NORMAL    = 1;

    //禁用账号
    const STATUS_FORBIDDEN = 2;

    //删除账号
    const STATUS_DELETE    = -1;

    //系统管理员
    const SUPPLER          = 1;

    /*
    //平台管理员
    const PLATFORM         = 2;

    //供应商
    const SUPPLIER         = 3;

    //分公司
    const COMPANY          = 4;

    //实体店
    const SHOP             = 5;
    //老师
    const TEACHER             = 6;
*/

    //机构
    const JIGOU         = 2;

    //学生
    const STUDENT         = 3;

    //老师
    const TEACHER          = 4;

    //校外
    const XIAOWAI             = 5;
    //会议室
    const HUIYISHI             = 6;

    //会议
    const HUIYI             = 7;
    //其他
    const QITA             = 8;
    /**
     * @var 判断是否是闪惠购操作者(管理员,超级管理员)
     */
    public function checkIsShgOperator($role_id){
        if($role_id == AccessModel::SUPPLER || $role_id == AccessModel::PLATFORM){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @var 获取排序方式
     */
    public function getOrderBy(){
        return array(
            1=>array(
                'name'=>'实体店序号正序',
                'order_by' => 'shop_sort asc',
                ),
            2=>array(
                'name'=>'实体店序号倒序',
                'order_by' => 'shop_sort desc',
                ),
            3=>array(
                'name'=>'创建时间正序',
                'order_by' => 'create_time asc',
                ),
            4=>array(
                'name'=>'创建时间倒序',
                'order_by' => 'create_time desc',
                ),
            );
    }

    /**
     * @var 获取状态,暂时不用语言包方式
     */
    public function getStatus($language = 'zh-cn'){
        return  array(
            self::STATUS_NORMAL    => $language == 'zh-cn' ? '启用' : 'StatusNormal',
            self::STATUS_FORBIDDEN => $language == 'zh-cn' ? '禁用' : 'StatusForbidden'
            );
    }

    /**
     * @var 获取角色组名称,暂时不用语言包方式
     */
    public function getRoleName($language = 'zh-cn'){
        return  array(
            /*
            self::PLATFORM => $language == 'zh-cn' ? '平台管理员' : 'platform',
            self::SUPPLIER => $language == 'zh-cn' ? '供应商' : 'supplier',
            self::COMPANY  => $language == 'zh-cn' ? '分公司' : 'company',
            self::SHOP     => $language == 'zh-cn' ? '实体店' : 'shop', */
            self::TEACHER => $language == 'zh-cn' ? "教师管理" : 'teacher',
            self::JIGOU => $language == 'zh-cn' ? "机构管理" : 'jigou',
            self::STUDENT => $language == 'zh-cn' ? "学生管理" : 'student',
            self::TEACHER => $language == 'zh-cn' ? "教师管理" : 'teacher',
            self::XIAOWAI => $language == 'zh-cn' ? "校外管理" : 'xiaowai',
            self::HUIYISHI => $language == 'zh-cn' ? "会议室管理" : 'huiyishi',
            self::HUIYI => $language == 'zh-cn' ? "会议管理" : 'huiyi',
            self::QITA => $language == 'zh-cn' ? "其他" : 'qita',
            );
    }

    /**
     * @var     根据角色组id获取相应的显示角色组,用于检索框的生成
     * @param   int     $roles_id 角色组id
     * @return  array   [
     *                       [角色组id=>角色组中文名称],
     *                       [角色组id=>角色组中文名称],
     *                  ]
     */
    public function getAccessView($roles_id){
        $access_view = C('ACCESS_VIEW');
        if(isset($access_view[$roles_id])){
            return $access_view[$roles_id];
        }else{
            return array();
        }

    }

    /**
     * @var 根据条件返回一条相应字段的记录
     */
    public function getOne($field,$where){
        $M = M("User");
        return $M->field($field)->where($where)->find();
    }

    /**
     * @var 根据条件返回所有相应字段的记录
     */
    public function getList($field,$where){
        $M = M("User");
        $result = $M->field($field)->where($where)->select();
        return $result ? $result : array();
    }

    /**
     * @var 根据ids,修改相应的状态
     */
    public function changeStatus($ids,$status){
        $M = M("User");
        $where['id']    = array('in',$ids);
        $data['status'] = $status;
        return $M->where($where)->save($data);
    }

    /**
     * @var     判断id其相应的所属用户id是否相等
     * @param   int     $id         要操作的id
     * @param   int     $role_id    操作者的角色id
     * @param   int     $uid        操作者的id
     * @return  boolean
     */
    public function checkUpdateAccessByRoleIdAndId($id,$role_id,$uid){
        $access_search = C('ACCESS_SEARCH.access');
        $access_update = $access_search['update'];
        $result = false;
        switch ($access_update[$role_id]) {
            case 'allow':
                $result = true;
                break;
            case 'unallow':
                $result = false;
                break;
            default:
                $M = M("User");
                $where = array(
                    'id' => $id
                    );
                $list = $this->getOne($access_update[$role_id],$where);
                //如果操作者的id和其操作的用户所属相应的id一致则可以操作
                // $result = $uid == $list[$access_update[$role_id]];
                if($uid == $list[$access_update[$role_id]]){
                    $result = true;
                }else{
                    $result = false;
                }

                break;
        }

        return $result;
    }

    public function nodeList() {
        import("Category");
        $cat = new Category('Node', array('id', 'pid', 'title', 'fullname'));
        $temp = $cat->getList();               //获取分类结构
        $level = array("1" => "项目（GROUP_NAME）", "2" => "模块(MODEL_NAME)", "3" => "操作（ACTION_NAME）");
        foreach ($temp as $k => $v) {
            $temp[$k]['statusTxt'] = $v['status'] == 1 ? "启用" : "禁用";
            $temp[$k]['chStatusTxt'] = $v['status'] == 0 ? "启用" : "禁用";
            $temp[$k]['level'] = $level[$v['level']];
            $list[$v['id']] = $temp[$k];
        }
        unset($temp);
        return $list;
    }

    public function roleList() {
        $M = M("Role");
        $list = $M->select();
        foreach ($list as $k => $v) {
            $list[$k]['statusTxt'] = $v['status'] == 1 ? "启用" : "禁用";
            $list[$k]['chStatusTxt'] = $v['status'] == 0 ? "启用" : "禁用";
        }
        return $list;
    }

    public function opStatus($op = 'Node') {
        $M = M("$op");
        $datas['id'] = (int) $_GET["id"];
        $datas['status'] = $_GET["status"] == 1 ? 0 : 1;
        if ($M->save($datas)) {
            return array('status' => 1, 'info' => "处理成功", 'data' => array("status" => $datas['status'], "txt" => $datas['status'] == 1 ? "禁用" : "启动"));
        } else {
            return array('status' => 0, 'info' => "处理失败");
        }
    }

    public function editNode() {
        $M = M("Node");
        return $M->save($_POST) ? array('status' => 1, info => '更新节点信息成功', 'url' => U('/Admin/Access/nodeList')) : array('status' => 0, info => '更新节点信息失败');
    }

    public function addNode() {
        $M = M("Node");
        return $M->add($_POST) ? array('status' => 1, info => '添加节点信息成功', 'url' => U('/Admin/Access/nodeList')) : array('status' => 0, info => '添加节点信息失败');
    }

    /**
     * @var 搜索条件
     */
    private function _search($post){
        $map = array();
        //角色组ID
        if($post['role_id']){
            $map['role_id'] = $post['role_id'];
        }
        //状态
        if($post['status']){
            $map['status'][] = array('eq',$post['status']);
        }
        //用户id
        if($post['id']){
            $map['id'] = $post['id'];
        }
        //负责人姓名
        if($post['business_name']){
            $map['business_name'] = array('like', '%'.trim($post['business_name']).'%');
        }
        //负责人姓名
        if($post['name']){
            $map['name'] = array('like', '%'.trim($post['name']).'%');
        }
        //负责人手机号
        if($post['tel']){
            $map['tel'] = array('like', '%'.trim($post['tel']).'%');
        }
        // 排序方式
        if($post['order_by']){
            $map['order_by'] = $post['order_by'];
        }

        return $map;
    }

    /**
     * @var 一个默认的权限条件
     */
    private function _accessSearch($id, $role_id){
        $where = array();
        $access_search = C('ACCESS_SEARCH');
        if(isset($access_search['access']['select'][$role_id])){

            //暂时只支持一维数组规则
            $key   = key($access_search['access']['select'][$role_id]);
            $value = current($access_search['access']['select'][$role_id]);

            //%s这种需要替换的
            if('%s' === $value){
                $where[$key] = $id;
            }else{
                $where[$key] = $value;
            }
        }

        return $where;
    }

    /**
     * 管理员列表
     * @param array $where 查询条件
     * @param type $page    第几页
     * @param type $perPageNum 每页记录数
     */
    public function adminList($where, $page = 1, $perPageNum = 20, $id, $role_id) {
        //获取用户权限的内置条件
        $access_search = $this->_accessSearch($id, $role_id);
        //获取用户搜索条件
        $search = $this->_search($where);
        $where  = array_merge($search,$access_search);

        //默认显示status>0的.-1为删除
        $where['status'][] = array('gt',0);

        $M = M("User");
        $list = array();
        $list['num'] = $M->where($where)->count();

        $fields = 'id,role_id,name,tel,status,business_name,shop_sort,create_time';

        $M->where($where)->field($fields);
        $order_by = $this->getSqlOrderBy($where);
        $list['list'] = $M->order($order_by)
                          ->limit(($page - 1) * $perPageNum, $perPageNum)
                          ->select();

        return $list;
    }

    /**
     * @var 排序方式orderby的sql
     */
    private function getSqlOrderBy($where){
        $order_by = 'id desc';

        $order_by_allow_key = array_keys($this->getOrderBy());
        if(isset($where['order_by']) && in_array($where['order_by'], $order_by_allow_key)){
            $order_by = $this->getOrderBy()[$where['order_by']]['order_by'];
        }

        return $order_by;
    }

    /**
     * @var 添加用户
     */
    public function addAdmin($data) {
        $M = M("User");
        return $M->add($data);
    }


    /**
     * @var 编辑用户
     */
    public function editAdmin($id,$data) {
        $M = M("User");
        return $M->where('id='.$id)->save($data);
    }


    public function editRole() {
        $M = M("Role");
        if ($M->save($_POST)) {
            return array('status' => 1, 'info' => "成功更新", 'url' => U("/Admin/Access/roleList"));
        } else {
            return array('status' => 0, 'info' => "更新失败，请重试");
        }
    }


    public function addRole() {
        $M = M("Role");
        if ($M->add($_POST)) {
            return array('status' => 1, 'info' => "成功添加", 'url' => U("/Admin/Access/roleList"));
        } else {
            return array('status' => 0, 'info' => "添加失败，请重试");
        }
    }

    public function changeRole() {
        $M = M("Access");
        $role_id = (int) $_POST['id'];
       // echo $role_id;exit;
        $M->where("role_id=" . $role_id)->delete();
        $data = $_POST['data'];
        //print_r($data);exit;
        if (count($data) == 0) {
            return array('status' => 1, 'info' => "清除所有权限成功", 'url' => U("Access/roleList"));
        }
        $datas = array();
        foreach ($data as $k => $v) {
            $tem = explode(":", $v);
            $datas[$k]['role_id'] = $role_id;
            $datas[$k]['node_id'] = $tem[0];
            $datas[$k]['level'] = $tem[1];
            $datas[$k]['pid'] = $tem[2];
        }
        if ($M->addAll($datas)) {
            //echo $M->getLastSql();exit;
            return array('status' => 1, 'info' => "设置成功", 'url' => U("Access/roleList"));
        } else {
            return array('status' => 0, 'info' => "设置失败，请重试");
        }
    }



}

?>
