<?php

/**
 * 会议内容模型
 */

class MeetingModel extends Model{

    /**
     * @var 查找
     */
    public function getList($where, $firstRow = 0, $listRows = 20,$onlyCount=0,$mtype = 0,$uid = 0,$del=0,$flag = false,$m_a_status = ''){


        //获取用户搜索条件
        $where = $this->_search($where);
       // print_r($where);
        $pre = C("DB_PREFIX");
        //print_r($pre);exit;
        $new_where = array();
        if ($flag) {
            $new_where['m_mtype'] = array('in','0,2');
        }else {
            $new_where['m_mtype'] = array('eq', $mtype);
        }
        if ($uid != NULL) {
            $new_where['m_uid'] = array('eq',$uid);
        }
        if ($uid >0) {
            $new_where['m_del'] = array('eq',0);
        }

        if (!empty($m_a_status)) {
            if ($m_a_status ==2) {
                $new_where['m_a_status'] = array('eq',0);
            }
            if ($m_a_status ==1) {
                $new_where['m_a_status'] = array('eq',1);
            }
        }
        $new_where = array_merge($new_where, $where);
        $this->where($new_where);
       // echo $this->getLastSql();
        if($onlyCount){
            return $this->count();
        }
       // $fields = 'm_uid,m_mtype,m_number,m_ziliao,m_images,m_zhujiang,m_zhuanjia,m_jigou,m_bianma,m_end,m_start,m_user,m_id,m_name,m_type,m_address,m_content,time,m_status,m_a_status,m_time,m_grade';
        $fields = '*';
        $list   = $this->field($fields)
                       ->order('m_id desc')
                       ->limit($firstRow , $listRows)
                       ->select();

       // echo $this->getLastSql();

        return $list;
    }

    /**
     * @var 搜索条件
     *  搜索
     */
    private function _search($post){
        $map = array();

        //会议编码
        if($post['m_bianma']){
            $map['m_bianma'] =  array('like', '%'.trim($post['m_bianma']).'%');
        }

        //会议名称
        if($post['m_name']){
            $map['m_name'] = array('like', '%'.trim($post['m_name']).'%');
        }

        //举办者
        if($post['m_user']){
            $map['m_user'] = array('like', '%'.trim($post['m_user']).'%');
        }

        //时间开始
        if($post['start_time']){
            $map['s_time'][] = array('egt', strtotime(trim($post['start_time'])));
        }
        //时间结束
        if($post['end_time']){
            $_end_time = trim($post['end_time']);
            $map['s_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }

        return $map;
    }



}