<?php

/**
 * 学生模型
 */

class MStudentModel extends Model{

    /**
     * @var 查找
     */
    public function getList($where, $firstRow = 0, $listRows = 20,$onlyCount=0){

        //获取用户搜索条件
        $where = $this->_search($where);
        $pre = C("DB_PREFIX");
        //print_r($pre);exit;
        $new_where = array();

        if(!empty($where)){
            foreach ($where as $key => $value) {
                $new_where[$pre.'m_student.'.$key] = $value;
            }
        }
        //默认显示status>0的.-1为删除
        //$new_where[$pre.'user.status'] = array('gt',0);

        $this->where($new_where)->join($pre . 'm_student_info ON ' . $pre . 'm_student_info.s_no = ' . $pre . 'm_student.s_no ');
        if($onlyCount){
            return $this->count();
        }

        $fields = 's_num,s_phone,s_weixin,s_id,s_no,s_name,s_sex,s_birth,s_card,s_grade,s_department,s_domain,s_nation,s_mode,s_mobile,s_teacher,s_time';
        $fields_array = explode(',', $fields);
        foreach ($fields_array as $key => $value) {
            $fields_array[$key] = $pre . 'm_student.'.$value;
        }


        $fields_info = 's_admin,s_pass,s_major';
        $fields_array_info = explode(',', $fields_info);
        foreach ($fields_array_info as $key => $value) {
            $fields_array_info[$key] = $pre . 'm_student_info.'.$value;
        }


        $fields = implode(',', array_merge($fields_array, $fields_array_info));

        $list   = $this->field($fields)
                       ->order($pre . 'm_student.s_id desc')
                       ->limit($firstRow , $listRows)
                       ->select();
       // echo $this->getLastSql();exit;
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
            $map['s_no'] = $post['bianma'];
        }
        if($post['name']){
            $map['s_name'] = array('like', '%'.trim($post['name']).'%');
        }
        if ($post['sex']) {
            $map['s_sex'] = $post['sex'];
        }

        if ($post['department'] != '无' && !empty($post['department'])) {
            $map['s_department'] = $post['department'];
        }

        if ($post['major'] != '无' && !empty($post['major'])) {
            $map['s_domain'] = array('like', '%'.trim($post['major']).'%');
        }


        if ($post['grade']) {
            $map['s_grade'] = $post['grade'];
        }
        if ($post['phone']) {
            $map['s_phone'] = $post['phone'];
        }

        if ($post['weixin']) {
            $map['s_weixin'] = $post['weixin'];
        }






        //学生号
        if($post['s_no']){
            $map['s_no'] = $post['s_no'];
        }

        //学生名称
        if($post['s_name']){
            $map['s_name'] = array('like', '%'.trim($post['s_name']).'%');
        }

        //学生身份证号
        if($post['s_card']){
            $map['s_card'] = array('like', '%'.trim($post['s_card']).'%');
        }
        //导师名字
        if($post['s_teacher']){
            $map['s_teacher'] = array('like', '%'.trim($post['s_teacher']).'%');
        }

        //导入时间开始
        if($post['start_time']){
            $map['s_time'][] = array('egt', strtotime(trim($post['start_time'])));
        }
        //导入时间结束
        if($post['end_time']){
            $_end_time = trim($post['end_time']);
            $map['s_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }

        return $map;
    }



}