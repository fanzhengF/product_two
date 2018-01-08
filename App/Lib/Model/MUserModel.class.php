<?php

/**
 * 校外人员模型
 */

class MUserModel extends Model{

    /**
     * @var 查找
     */
    public function getList($where, $firstRow = 0, $listRows = 20,$onlyCount=0){

        //获取用户搜索条件
        $where = $this->_search($where);
        //print_r($where);
        $pre = C("DB_PREFIX");
        $new_where = array();
        if(!empty($where)){
            foreach ($where as $key => $value) {
                $new_where[$pre.'m_user.'.$key] = $value;
            }
        }
        $new_where['u_type'] = 1;
        $this->where($new_where);
        if($onlyCount){
            return $this->count();
        }


        $fields = '*';
        $list   = $this->field($fields)
                       ->order('u_id desc')
                       ->limit($firstRow , $listRows)
                       ->select();
        return $list;
    }

    /**
     * @var 搜索条件
     *  搜索
     */
    private function _search($post){
        $map = array();

        //教师编号
        if($post['u_phone']){
            $map['u_phone'] = $post['u_phone'];
        }

        //教师名字
        if($post['u_name']){
            $map['u_name'] = array('like', '%'.trim($post['u_name']).'%');
        }


        //导入时间开始
        if($post['start_time']){
            $map['u_time'][] = array('egt', strtotime(trim($post['start_time'])));
        }
        //导入时间结束
        if($post['end_time']){
            $_end_time = trim($post['end_time']);
            $map['u_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }
        return $map;
    }




}