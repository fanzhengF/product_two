<?php

/**
 * 报名模型
 */

class MSignupModel extends Model{

    /**
     * @var 查找
     */
    public function getList($where, $firstRow = 0, $listRows = 20,$onlyCount=0,$id= 0){

        //获取用户搜索条件
        $where = $this->_search($where);
        $pre = C("DB_PREFIX");
        //print_r($pre);exit;
        $new_where = array();

       /* if(!empty($where)){
            foreach ($where as $key => $value) {
                $new_where[$pre.'m_student.'.$key] = $value;
            }
        }*/



        if ($id > 0) {
            $new_where['e_mid'] = array('eq',$id);
            $this->where($new_where);
        }
        $new_where = array_merge($where, $new_where);

        if($onlyCount){
            return $this->where($new_where)->count();
        }
        $fields = 's_id,s_uid,s_mid,s_time';
        $list   = $this->field($fields)
                        ->where($new_where)
                       ->order('s_id desc')
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
       // if($post['s_mid']){
        //    $map['s_mid'] = $post['s_mid'];
        //}

        //会议名称
        if($post['s_meet_name']){
            $map['s_meet_name'] = array('like', '%'.trim($post['s_meet_name']).'%');
        }
       /* //学生名称
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
        } */

        //提交时间开始
        if($post['start_time']){
            $map['s_time'][] = array('egt', strtotime(trim($post['start_time'])));
        }
        //提交时间结束
        if($post['end_time']){
            $_end_time = trim($post['end_time']);
            $map['s_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }
        return $map;
    }



}