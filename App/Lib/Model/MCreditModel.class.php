<?php

/**
 * 学分模型
 */

class MCreditModel extends Model{

    /**
     * @var 查找
     */
    public function getList($where, $firstRow = 0, $listRows = 20,$onlyCount=0,$id=0,$c_status=0){

        //获取用户搜索条件
        $where = $this->_search($where);
        $pre = C("DB_PREFIX");
        //print_r($pre);exit;
        $new_where = array();
        $new_where['c_status'] = array('eq',$c_status);

        /*
        if (!empty($c_status)) {
            if ($c_status ==2) {
                $new_where['c_status'] = array('eq',0);
            }
            if ($c_status ==1) {
                $new_where['c_status'] = array('eq',1);
            }
        }
*/

        if(!empty($where)){
            foreach ($where as $key => $value) {
                $new_where[$pre.'m_credit.'.$key] = $value;
            }
        }

        if ($id >0) {
            $new_where['e_mid'] = array('eq',$id);
            $this->where($new_where);
        }
        if($onlyCount){
            return $this->where($new_where)->count();
        }
        $fields = '*';
        $list   = $this->field($fields)
                        ->where($new_where)
                        ->order('c_id desc')
                       ->limit($firstRow , $listRows)
                       ->select();



        //echo $this->getLastSql();
        return $list;
    }

    /**
     * @var 搜索条件
     *  搜索
     */
    private function _search($post){
        $map = array();

        //学生号
        if($post['c_no']){
            $map['c_no'] = $post['c_no'];
        }

        //学生名称
        if($post['c_user']){
            $map['c_user'] = array('like', '%'.trim($post['c_user']).'%');
        }



        //时间开始
        if($post['start_time']){
            $map['c_time'][] = array('egt', strtotime(trim($post['start_time'])));
        }
        //时间结束
        if($post['end_time']){
            $_end_time = trim($post['end_time']);
            $map['c_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }
        return $map;
    }



}