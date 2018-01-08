<?php

/**
 * 教师模型
 */

class MTeacherModel extends Model{

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
                $new_where[$pre.'m_teacher.'.$key] = $value;
            }
        }
        //默认显示status>0的.-1为删除
        //$new_where[$pre.'user.status'] = array('gt',0);

        $this->where($new_where)->join($pre . 'm_teacher_info ON ' . $pre . 'm_teacher_info.t_no = ' . $pre . 'm_teacher.t_no ');
        if($onlyCount){
            return $this->count();
        }

        $fields = 't_num,t_phone,t_weixin,t_id,t_no,t_name,t_department,t_sex,t_birth,t_title,t_post,t_email,t_time';
        $fields_array = explode(',', $fields);
        foreach ($fields_array as $key => $value) {
            $fields_array[$key] = $pre . 'm_teacher.'.$value;
        }


        $fields_info = 't_admin,t_pass';
        $fields_array_info = explode(',', $fields_info);
        foreach ($fields_array_info as $key => $value) {
            $fields_array_info[$key] = $pre . 'm_teacher_info.'.$value;
        }


        $fields = implode(',', array_merge($fields_array, $fields_array_info));

        $list   = $this->field($fields)
                       ->order($pre . 'm_teacher.t_id desc')
                       ->limit($firstRow , $listRows)
                       ->select();
        //echo $this->getLastSql();exit;
        return $list;
    }

    /**
     * @var 搜索条件
     *  搜索
     */
    private function _search($post){
        $map = array();

        /*
         * 前台发信息搜索
         * 专用
         * */
        if ($post['bianma']) {
            $map['t_no'] = $post['bianma'];
        }
        if($post['name']){
            $map['t_name'] = array('like', '%'.trim($post['name']).'%');
        }
        if ($post['sex']) {
            $map['t_sex'] = $post['sex'];
        }
        if ($post['department'] != '无' && !empty($post['department'])) {
            $map['t_department'] = $post['department'];
        }
        if ($post['zhicheng']) {
            $map['t_title'] = $post['zhicheng'];
        }

        if ($post['phone']) {
            $map['t_phone'] = $post['phone'];
        }

        if ($post['weixin']) {
            $map['t_weixin'] = $post['weixin'];
        }



        //教师编号
        if($post['t_no']){
            $map['t_no'] = $post['t_no'];
        }

        //教师名字
        if($post['t_name']){
            $map['t_name'] = array('like', '%'.trim($post['t_name']).'%');
        }


        //导入时间开始
        if($post['start_time']){
            $map['t_time'][] = array('egt', strtotime(trim($post['start_time'])));
        }
        //导入时间结束
        if($post['end_time']){
            $_end_time = trim($post['end_time']);
            $map['t_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }
        return $map;
    }




}