<?php
/**
 * 报表管理
 */

class ReportAction extends AdminAction
{
    const  PAGE_PER_NUM = 20; //每页记录数

    /**
     * @var 报表列表
     */
    public function index()
    {
        $user = M('MUser');
        $meeting = M('Meeting');
        $M = M('MMt');
        $Mg = M('MGrade');
        $MM = D("MMechanism");
        $map = array();

        if ($_GET['m_u_name']) {
            $map['m_u_name'] = array('like', '%' . trim($_GET['m_u_name']) . '%');
        }

        if ($_GET['m_type']) {
            $map['m_type'] = array('like', '%' . trim($_GET['m_type']) . '%');
        }

        if($_GET['m_name']){
            $map['m_name'] = array('like', '%'.trim($_GET['m_name']).'%');
        }

        if ($_GET['m_grade']) {
            $map['m_grade'] = array('like', '%' . trim($_GET['m_grade']) . '%');
        }
        if ($_GET['m_jigou']) {
            $map['m_jigou'] = array('like', '%' . trim($_GET['m_jigou']) . '%');
        }

        //时间开始
        if ($_GET['start_time']) {
            $map['m_time'][] = array('egt', trim($_GET['start_time']));
        }
        //时间结束
        if ($_GET['end_time']) {
            $map['m_time'][] = array('elt', trim($_GET['end_time']));
        }

        $count = $meeting->where($map)->count();
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count, self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $meeting->where($map)->limit($page->firstRow, $page->listRows)->select();
        foreach ($list as $k => $v) {
            $list[$k]['u_name'] = $user->where(array('u_id' => $v['m_uid']))->find()['u_name'];
            $list[$k]['m_type'] = $M->where(array('mt_id' => $v['m_type']))->find()['mt_name'];
            $list[$k]['m_grade'] = $Mg->where(array('g_id' => $v['m_grade']))->find()['g_name'];
            $list[$k]['m_jigou'] = $MM->where(array('m_id' => $v['m_jigou']))->find()['m_name'];
            if ($v['m_range'] == '1') {
                $na = '校内';
            }else if ($v['m_range'] == '2'){
                $na = '校外';
            }
            $list[$k]['m_range'] = $na;
              $list[$k]['m_time'] = $v['m_time'].' '.$v['m_start'].'-'.$v['m_end'];
        }


            if (isset($_GET['daochu'])) {
               // print_r($list);exit;
                $title = array(
                    'm_name'=> array(
                        'title'=>'会议名称'
                    ),
                    'u_name'=> array(
                        'title'=>'用户'
                    ),
                    'm_type'=> array(
                        'title'=>'会议类型'
                    ),
                    'm_grade'=> array(
                        'title'=>'会议等级'
                    ),
                    'm_jigou'=> array(
                        'title'=>'举办机构'
                    ),
                    'm_range'=> array(
                        'title'=>'参会人员类型'
                    ),
                    'm_time'=> array(
                        'title'=>'会议时间'
                    )
                );
                $this->commonExportCvs($list,$title);
            }


        /*所属机构*/
        $MMaLL = $MM->where(array('m_parent' => 0))->select();
        $Mr = D("MRoom"); //所属会议室
        $arr = array();
        foreach ($MMaLL as $k => $v) {
            $MMaLLSub = $MM->where(array('m_parent' => $v['m_id']))->select();
            $MMaLL[$k]['m_sub'] = $MMaLLSub;

        }
        /*院系*/
        $MD = M("MDepartmentInfo");
        $MDaLL = $MD->select();
        //专业
        $Msp = M("MStudentProfe");
        $MspaLL = $Msp->select();
        $mtAll = $M->select();
        $gradeAll = $Mg->select();
        $this->assign('MMall', $MMaLL);
        $this->assign('mtAll', $mtAll);
        $this->assign('gradeAll', $gradeAll);
        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->assign('MDaLL', $MDaLL);
        $this->assign('MspaLL', $MspaLL);
        $this->display();
    }








}