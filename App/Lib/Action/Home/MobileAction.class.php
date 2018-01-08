<?php
/**
 *
 * 手机端
 * 前端获取经度和纬度
 */
class MobileAction extends BaseAction
{
    const NEW_CITY = 'new_city';
    const OLD_CITY = 'old_city';
    const MOBILE_EXPIRE = 15552000;
    const  PAGE_PER_NUM = 10; //每页记录数

    public $cookie_domain='';
    public function __construct(){
        parent::__construct();
        $home_name =  C('HOME_NAME');
        $this->assign('home_name',$home_name);
    }

    //登录
    public function login(){
        if (IS_AJAX) {
            $u_name = I('post.u_name');
            $u_pwd = I('post.u_pwd');
            $u_type = I('post.u_type');
            $muser = M('MUser');
            $msi = M('MStudentInfo');
            $mti = M('MTeacherInfo');
            $u_pwd =   md5(C("AUTH_CODE") . md5($u_pwd));
            //print_r($_POST);exit;
            if ($u_type == 1) {  //校外
                $_oneUser =  $muser->field('u_name,u_pwd')->where(array('u_phone'=>$u_name,'u_pwd'=>$u_pwd,'u_type'=>$u_type))->find();
            }else if ($u_type ==2) {  //教师
                $user =  $mti->where(array('t_no'=>$u_name,'t_pass'=>I('post.u_pwd')))->find();
                if (empty($user)) {
                    die('-2');
                }
                $_oneUser = $muser->field('u_name,u_pwd')->where(array('u_name'=>$u_name,'u_pwd'=>$u_pwd,'u_type'=>$u_type))->find();
                // echo $muser->getLastSql();exit;
                if (empty($_oneUser)) {
                    $data['u_name'] = $user['t_no'];
                    $data['u_time'] = time();
                    $data['u_type'] = 2;
                    $data['u_pwd'] = md5(C("AUTH_CODE") . md5($user['t_pass']));
                    $u_id = $muser->add($data);
                    $_oneUser = $muser->field('u_name,u_pwd')->where(array('u_name' => $u_name, 'u_pwd' => $u_pwd, 'u_type' => $u_type))->find();
                }

            }else if ($u_type == 3) { //学生
                $user =  $msi->where(array('s_no'=>$u_name,'s_pass'=>I('post.u_pwd')))->find();
                if (empty($user)) {
                    die('-2');
                }
                $_oneUser = $muser->field('u_name,u_pwd')->where(array('u_name'=>$u_name,'u_pwd'=>$u_pwd,'u_type'=>$u_type))->find();
                // echo $muser->getLastSql();exit;
                if (empty($_oneUser)) {
                    $data['u_name'] = $user['s_no'];
                    $data['u_time'] = time();
                    $data['u_type'] = 3;
                    $data['u_pwd'] = md5(C("AUTH_CODE") . md5($user['s_pass']));
                    $u_id = $muser->add($data);
                    $_oneUser = $muser->field('u_name,u_pwd')->where(array('u_name' => $u_name, 'u_pwd' => $u_pwd, 'u_type' => $u_type))->find();
                }


            }


            if (empty($_oneUser)) {
                die('-2');
            }

            $u =  encrypt_en($u_name,c('MD5_KEY'));
            $p =  encrypt_en($u_pwd,c('MD5_KEY'));
            $t =  encrypt_en($u_type,c('MD5_KEY'));
            $jsonUser =  json_encode(array('u'=>$u,'p'=>$p,'t'=>$t));
            cookie('auth', "$jsonUser", 60*60*24);
            unset($u_pwd);
            die('-1');
        }

        if (empty(cookie('auth'))) {
            $this->display('Mobile/login');
        }
    }



    //首页
    public function index(){
        $this->display('Mobile:index');
    }

    public function show (){
        $Mt = M('Meeting');
        $Ms = M('MSignup');
        $M = M('MMt');  //会议类型
        $Mf = M('MFormat');
        $count = $Mt->where(array('m_mtype'=>array('neq', 1),'m_status'=>array('in','0,1'),'m_a_status'=>1))->count();
        $page = $_POST['page'];
        $allPage = $this->downmore($page,$count);
        $_count = $allPage['count'];
        $_pagesize = $allPage['pagesize'];
        $_limit = $allPage['limit'];
        $MtAll = $Mt->field("({$_count}) as count,m_start,m_end,m_address,m_name,m_type,m_id,m_time")->where(array('m_mtype'=>array('neq', 1),'m_status'=>array('in','0,1'),'m_a_status'=>1))->order('time DESC')->limit($_limit, $_pagesize)->select();
        //echo $Mt->getLastSql();exit;
        $json = '';
        foreach ($MtAll as $k=>$v){
            $MtAll[$k]['m_type_name'] = $M->where(array('mt_id'=>$v['m_type']))->find()['mt_name'];
            $MtAll[$k]['m_baoming'] = $Ms->where(array('s_mid'=>$v['m_id']))->count();
            $exi = strtotime($v['m_time'].' '.$v['m_start']);
            if ( time() >= $exi) {
                $Mt->where(array('m_id' => $v['m_id']))->save(array('m_status' => 1));
            }
            $exi2 = strtotime($v['m_time'].' '.$v['m_end']);
            if ( time() >= $exi2) {
                $Mt->where(array('m_id' => $v['m_id']))->save(array('m_status' => 2));
            }
        }
        foreach ($MtAll as $k1=>$v1) {
            foreach ( $v1 as $key => $value ) {
                $v1[$key] = urlencode(str_replace("\n","", $value));
            }
            $json .= urldecode(json_encode($v1)).',';
        }

        echo '['.substr($json, 0, strlen($json) - 1).']';exit;
    }



    //加载更多
    private function downmore($page,$count){
        $_pagesize = self::PAGE_PER_NUM;
        $_count = ceil($count / $_pagesize);
        $_page = 1;
        if (!isset($page)) {
            $_page = 1;
        } else {
            $_page = $page;
            if ($_page > $_count) {
                $_page = $_count;
            }
        }
        $_limit = ($_page - 1) * $_pagesize;
        return array('count'=>$_count,'pagesize'=>$_pagesize,'limit'=>$_limit);
    }


    //报名成功
    public function baoming(){
        if (I('get.flag') == true) {
            $this->display('Mobile:baoming');
        }
    }






    //会议详情
    public function details(){
        $D = D("MEvaluate");//评论
        $ME = D("MExperience");
        $user = M('MUser');
        $Mp = M('MPoster');
        $Ms = M('MSignup');
        $Mf = M('MFormat');
        $Mt = M('Meeting');
        $Mg = M('MGrade');
        $Mc = M('MCredit');
        $ms =  M('MStudent');
        $mt =  M('MTeacher');
        $cookieUser = $this->getUser();
        if ($cookieUser['type'] !=1) {
            $uid = $user->where(array('u_name' => $cookieUser['user'], 'u_pwd' => $cookieUser['pwd']))->find()['u_id'];
        }else {
            $uid = $user->where(array('u_pwd' => $cookieUser['pwd']))->find()['u_id'];
        }
        if (IS_AJAX) {
            try {
                $_POST['e_content'] = htmlspecialchars($_POST['e_content']);
                $_POST['e_time'] = time();
                $_POST['e_uid'] = $uid;
                if($data = $D->create(I('post.'))){
                    $last = $D->where(array('e_uid'=>$uid,'e_mid'=>$_POST['e_mid']))->order('e_time DESC')->find();
                    $_time = time() -$last['e_time'];
                    if ($_time < 300) {
                        die('-4');
                    }
                    $re = $D->add($data);
                    if($re){

                        //参加并且评论一条的得到学分
                        $oneMe =  $D->where(array('e_uid'=>$uid,'e_mid'=>$_POST['e_mid']))->find();
                        $oneMeCount =  $D->where(array('e_uid'=>$uid,'e_mid'=>$_POST['e_mid']))->count();
                        $oneMs = $Ms->where(array('s_uid'=>$uid,'s_mid'=>$_POST['e_mid']))->find();
                        $oneMsCount = $Ms->where(array('s_uid'=>$uid,'s_mid'=>$_POST['e_mid']))->count();
                        $number = $Mc->where(array('c_uid'=>$uid))->find()['c_number'];
                        if (!empty($oneMe) && !empty($oneMs) && $oneMsCount == 1 && $oneMeCount == 1) {
                            $grade = $Mt->field('m_grade')->where(array('m_id'=>$_POST['e_mid']))->find()['m_grade'];
                            $xuefen = $Mg->field('g_xuefen')->where(array('g_id'=>$grade))->find()['g_xuefen'];
                            $data['c_number'] = $number + $xuefen;
                            $Mc->where(array('c_uid'=>$uid))->save($data);

                        }


                        die('-1');
                    }else{
                        die('-2');
                    }
                }else{
                    die('-2');
                }
            } catch (Exception $e) {
                die('-2');
            }

        }
        $id = I('get.id');


        if ($id >0) {
            // $Mt =  M('Meeting');
            //$Mg = M('MGrade');
            $MtAll = $Mt->where(array('m_id'=>$id,'m_status'=>array('in','0,1,2')))->find();
            if ($MtAll['m_a_status'] == 2) $this->error('会议正在审核中！');
            if (empty($MtAll) && $MtAll['m_a_status'] == 1) $this->error('会议已经结束不能查看！');
            $MtAll['m_type_name'] = M('MMt')->where(array('mt_id'=>$MtAll['m_type']))->find()['mt_name'];
            $MtAll['m_grade_name'] = M('MGrade')->where(array('g_id'=>$MtAll['m_grade']))->find()['g_name'];
            $MtAll['m_xuefen'] = $Mg->where(array('g_id'=>$MtAll['m_grade']))->find()['g_xuefen'];
            $MMaLL = D("MMechanism")->where(array('m_id'=>$MtAll['m_jigou']))->find()['m_name'];
            $MtAll['m_jigou'] = $MMaLL;
            $MtAll['p_images'] = $Mp->where(array('p_mid'=>$MtAll['m_id']))->find()['p_images'];
            $MtAll['m_baoming'] = $Ms->where(array('s_mid'=>$MtAll['m_id']))->count();
            /*会议评价*/
            $count = $D->getList(I('get.'), 0, 0,1,$id);
            import("ORG.Util.Page"); //载入分页类
            $page = new Page($count,self::PAGE_PER_NUM);
            $showPage = $page->show();
            $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,$id);

            foreach ($list as $k=>$v){
                $u_name = $user->where(array('u_id'=>$v['e_uid']))->find()['u_name'];
                $msName =  $ms->where(array('s_no'=>$u_name))->find()['s_name'];
                $mt_Name =  $mt->where(array('t_no'=>$u_name))->find()['t_name'];
                if (!empty($msName)) {
                    $_name1 = $msName;
                }
                if (!empty($mt_Name)) {
                    $_name1 = $mt_Name;
                }
                $list[$k]['u_name'] =  $_name1 ;

            }
            $this->assign("MEvaluatePage", $showPage);
            $this->assign("list", $list);
            $this->assign("MEvaluatecount", $count);
            /*会议评价*/
            /*会议心得*/
            $MEcount = $ME->getList(I('get.'), 0, 0,1,$id);
            $MEpage = new Page($MEcount,self::PAGE_PER_NUM);
            $MEshowPage = $MEpage->show();
            $MElist = $ME->getList(I('get.'),$MEpage->firstRow, $MEpage->listRows,0,$id);
            foreach ($MElist as $k=>$v){
                $MElist[$k]['u_name'] = $user->where(array('u_id'=>$v['e_uid']))->find()['u_name']; ;
            }
            //海报图
            $fShow =  $Mf->field('f_show,f_show1,f_show2')->where(array('f_confirm'=>1))->find();
            $this->assign("MExperiencePage", $MEshowPage);
            $this->assign("MElist", $MElist);
            $this->assign("MExperiencecount", $MEcount);
            /*会议心得*/
            //查询当前用户只能参加才能评论
            $oneSign = $Ms->where(array('s_mid'=>$MtAll['m_id'],'s_uid'=>$uid))->find();

            //print_r($MtAll);exit;
            $this->assign('auth',cookie('auth'));
            $this->assign('MtAll',$MtAll);
            $this->assign('id',$id);
            $this->assign('fshow',$fShow);
            $this->assign('user',$this->u_name);
            $this->assign('oneSign',$oneSign);
            $this->display('Mobile:details');
        }





    }

    //自筹会议室
    public function zichou(){
        $D = D("MRoom");
        $Msh = M("MShape");
        if(IS_POST) {

            try {
                if (empty($_POST['room_name'])) {
                    $this->error('会议室名称不能为空！');
                }
                if (empty($_POST['room_address'])) {
                    $this->error('会议室地点不能为空！');
                }
                if (empty($_POST['room_number'])) {
                    $this->error('会议室是容纳人数不能为空！');
                }
                $_POST['room_time'] = time();
                if ($data = $D->create(I('post.'))) {
                    $re = $D->add($data);
                    if ($re) {
                        //$this->success("确认成功！", U("/Mobile/writemeet/id/$re"));
                        echo "<script type='text/javascript'>location.href='/Mobile/writemeet/id/".$re.".html'</script>";
                        return;
                    } else {
                        throw new Exception('确认失败', 1);
                    }
                } else {
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(), U("/Index/zichou1"));
            }
        }
        $this->assign('msh',$Msh->select());
        $this->assign('auth', cookie('auth'));
        $this->display('Mobile:zichou');
    }

    //发送电子邮件
    public function sendEmail (){
        if (IS_POST) {
            $Me = M('MEmail');
            $cookieUser = $this->getUser();
            try {
                $file_name = $this->upload($_FILES['e_fujian'],'fujian','',1);
                $_POST['e_fujian'] = $file_name;
                $_POST['e_time'] = time();
                if($data = $Me->create(I('post.'))){
                    $strpos =  strpos($data['e_sid'],',');
                    if ($strpos > 0) {
                        $_arr_str = explode(',',$data['e_sid']);
                        foreach ($_arr_str as $v){
                            $data['e_sid'] = $v;
                            $time = time();
                            $Me->query("insert into cumtb_m_email(e_sid,e_uid,e_zhuti,e_zhengwen,e_fujian,e_time) VALUES ('" . $v . "',  '" . $_POST['e_uid'] . "' ,  '" . $_POST['e_zhuti'] . "', '" . $_POST['e_zhengwen'] . "','" . $_POST['e_fujian'] . "','" . $time . "' )");
                            $re = true;
                        }
                    }else {
                        $re = $Me->add($data);
                    }
                    if($re){
                        $this->success("发送成功",U("/Mobile/emails"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($Me->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }else {
            $this->error('错误！');
        }
    }
    //电子邮件

    public function emails(){

        if (!empty(cookie('auth'))) {
            $cookieUser = $this->getUser();
            if ($cookieUser['type'] == 1) $this->error('您不能查看消息列表！');
            $teacher =  D('MTeacher'); //教师表
            $student =  D('MStudent'); //学生表
            $user =  M('MUser');
            $Me = M('MEmail');
            $ms =  M('MStudent');
            $mt =  M('MTeacher');
            $mu =  M('MUser');
            import("ORG.Util.Page"); //载入分页类
            //学生列表
            $countS = $student->getList(I('get.'), 0, 0,1);
            $pageS = new Page($countS,self::PAGE_PER_NUM);
            $showPageS = $pageS->show();
            $listS = $student->getList(I('get.'),$pageS->firstRow, $pageS->listRows,0);
            //教师列表
            $countT = $teacher->getList(I('get.'), 0, 0,1);
            $pageT = new Page($countT,self::PAGE_PER_NUM);
            $showPageT = $pageT->show();
            $listT = $teacher->getList(I('get.'),$pageT->firstRow, $pageT->listRows,0);

            /*获取用户信息*/
            $cookieUser = $this->getUser();
            $uid = $user->where(array('u_name'=>$cookieUser['user'],'u_pwd'=>$cookieUser['pwd']))->find()['u_id'];
            /*获取用户信息*/
            //获取发件
            $map_fa['e_uid'] = $cookieUser['user'];
            if ($_GET['e_zhengwen']) {
                $map_fa['e_zhengwen'] = array('like','%'.trim($_GET['e_zhengwen']).'%');
            }
            $countM =  $Me->where($map_fa)->count();
            $pageM = new Page($countM,self::PAGE_PER_NUM);
            $showPageM = $pageM->show();
            $listM =   $Me->where($map_fa)->limit($pageM->firstRow, $pageM->listRows)->select();
            foreach($listM as $k=>$v){

                $msName =  $ms->where(array('s_no'=>$v['e_sid']))->find()['s_name'];
                $mtName =  $mt->where(array('t_no'=>$v['e_sid']))->find()['t_name'];
                if (!empty($msName)) {
                    $_name = $msName;
                }
                if (!empty($mtName)) {
                    $_name = $mtName;
                }
                $listM[$k]['username'] = $_name;
            }
            //获取收件
            $map_shou['e_sid'] = $cookieUser['user'];
            if ($_GET['e_zhengwen']) {
                $map_shou['e_zhengwen'] = array('like','%'.trim($_GET['e_zhengwen']).'%');
            }
            $countM_s =  $Me->where($map_shou)->count();
            $pageM_s = new Page($countM_s,self::PAGE_PER_NUM);
            $showPageM_s = $pageM_s->show();
            $listM_s =   $Me->where($map_shou)->limit($pageM_s->firstRow, $pageM_s->listRows)->select();
            foreach($listM_s as $k=>$v){

                $msName =  $ms->where(array('s_no'=>$v['e_uid']))->find()['s_name'];
                $mtName =  $mt->where(array('t_no'=>$v['e_uid']))->find()['t_name'];
                $muName =  $mu->where(array('u_id'=>$v['e_uid']))->find()['u_name'];
                if (!empty($msName)) {
                    $_name = $msName;
                }
                if (!empty($mtName)) {
                    $_name = $mtName;
                }
                if (!empty($muName)) {
                    $_name = $muName;
                }
                $listM_s[$k]['username'] = $_name;
            }

            $this->assign("pageM_s", $showPageM_s);
            $this->assign("listM_s", $listM_s);
            $this->assign("pageM", $showPageM);
            $this->assign("listM", $listM);
            $this->assign("pageT", $showPageT);
            $this->assign("listT", $listT);
            $this->assign("pageS", $showPageS);
            $this->assign("listS", $listS);
            $this->assign("uid", $uid);
            $this->assign("userid", $cookieUser['user']);
            $this->assign('auth',cookie('auth'));
            $this->display('Mobile:emails');
        }else {
            die('1');
        }
    }
    //参加会议成功
    public function huiyidetail(){
        $this->display('Mobile:huiyidetail');
    }




    //校外参加会
    public function outbao(){
        $id = I('get.id');
        $Mt =  M('Meeting');
        $Muser =  M('MUser');
        $Ms =  M('MSignup');
        if (IS_AJAX) {

            if (empty($_POST['u_name'])) {
                die('-3');
            }


            if (empty($_POST['u_phone'])) {
                die('-5');

            }
            if (!empty($Muser->where(array('u_phone'=>$_POST['u_phone']))->find())) {
                die('-6');
            }


            try {
                $_POST['u_time'] = time();
                $_POST['u_pwd'] = md5(C("AUTH_CODE") . md5($_POST['u_phone']));
                $_POST['u_type'] = I('post.u_type');

                if($data = $Muser->create(I('post.'))){
                    $s_uid = $Muser->add($data);
                    /*写入cookie*/
                    //$u_name = I('post.u_name');
                    $u_pwd = $_POST['u_pwd'];
                    $u =  encrypt_en($u_pwd,c('MD5_KEY'));
                    $p =  encrypt_en($u_pwd,c('MD5_KEY'));
                    $t =  encrypt_en(I('post.u_type'),c('MD5_KEY'));
                    $jsonUser =  json_encode(array('u'=>$u,'p'=>$p,'t'=>$t));
                    cookie('auth', "$jsonUser", 60*60*24);
                    /*写入cookie*/
                    //   echo $D->getLastSql();exit;
                    if($s_uid){
                        $data1['s_uid'] = $s_uid;
                        $data1['s_mid'] = $_POST['m_id'];
                        $data1['s_time'] = time();
                        $Ms->add($data1);
                        die('-1');
                    }else{
                        die('-2');
                    }
                }else{
                    die('-2');
                }

            } catch (Exception $e) {
                die('-2');
            }

        }
        if ($id > 0) {
            $MtAll = $Mt->where(array('m_id'=>$id,'m_a_status'=>1))->find();
            if (empty($MtAll)) $this->error('错误！');


            $this->assign('id',$id);
            $this->assign('auth', cookie('auth'));
            $this->display('Mobile:outbao');
        }
    }




    //发送消息
    public function sendmess(){
        if (IS_POST) {
            $Me = M('MEmail');
            $cookieUser = $this->getUser();
            try {
                if (empty ($_POST['e_sid'])) {
                    $this->error('收件人不能为空！');exit;
                }
                if (empty ($_POST['e_zhuti'])) {
                    $this->error('主题不能为空！');exit;
                }
                if (empty ($_POST['e_zhengwen'])) {
                    $this->error('正文不能为空！');exit;
                }


                $file_name = $this->upload($_FILES['e_fujian'],'fujian','',1);
                $_POST['e_fujian'] = $file_name;
                $_POST['e_time'] = time();

                if($data = $Me->create(I('post.'))){
                    $strpos =  strpos($data['e_sid'],',');
                    if ($strpos > 0) {
                        $_arr_str = explode(',',$data['e_sid']);
                        foreach ($_arr_str as $v){
                            $data['e_sid'] = $v;
                            $time = time();
                            $Me->query("insert into cumtb_m_email(e_sid,e_uid,e_zhuti,e_zhengwen,e_fujian,e_time) VALUES ('" . $v . "',  '" . $_POST['e_uid'] . "' ,  '" .  ['e_zhuti'] . "', '" . $_POST['e_zhengwen'] . "','" . $_POST['e_fujian'] . "','" . $time . "' )");
                            $re = true;
                        }

                    }else {
                        $re = $Me->add($data);
                    }
                    if($re){
                        $this->success("发送成功",U("/Mobile/sendmess"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($Me->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
        if (!empty(cookie('auth'))) {
            $cookieUser = $this->getUser();
            if ($cookieUser['type'] == 1) $this->error('您不能查看消息列表！');
            $this->assign("userid", $cookieUser['user']);
            if ($_GET['e_sid']) {
                $e_sid = implode(',', $_GET['e_sid']);
                $this->assign('e_sid',$e_sid);
            }
            $this->display('Mobile:sendmess');
        }




    }




    //查看收件箱信息
    public function messdetail(){
        if (!empty(cookie('auth'))) {
            $cookieUser = $this->getUser();
            if ($cookieUser['type'] == 1) $this->error('您不能查看消息列表！');
            //获取邮件
            $Me = M('MEmail');
            $ms =  M('MStudent');
            $mt =  M('MTeacher');
            $mu =  M('MUser');
            $oneMe =  $Me->where(array('e_id'=>I('get.id')))->find();
            $Me->where(array('e_id'=>I('get.id')))->save(array('e_type'=>1));
            if (empty($oneMe)) $this->error('错误！');
            $msName =  $ms->where(array('s_no'=>$oneMe['e_uid']))->find()['s_name'];
            $mtName =  $mt->where(array('t_no'=>$oneMe['e_uid']))->find()['t_name'];
            $muName =  $mu->where(array('u_id'=>$oneMe['e_uid']))->find()['u_name'];
            $ms_Name =  $ms->where(array('s_no'=>$oneMe['e_sid']))->find()['s_name'];
            $mt_Name =  $mt->where(array('t_no'=>$oneMe['e_sid']))->find()['t_name'];
            $mu_Name =  $mu->where(array('u_id'=>$oneMe['e_sid']))->find()['u_name'];
            if (!empty($msName)) {
                $_name = $msName;
            }
            if (!empty($mtName)) {
                $_name = $mtName;
            }
            if (!empty($muName)) {
                $_name = $muName;
            }

            if (!empty($ms_Name)) {
                $name = $ms_Name;
            }
            if (!empty($mt_Name)) {
                $name = $mt_Name;
            }
            if (!empty($mu_Name)) {
                $name = $mu_Name;
            }
            $oneMe['e_uusername'] = $_name; //发件人
            $oneMe['e_susername'] = $name; //收件人
            $this->assign('oneMe', $oneMe);
            $this->display('Mobile:messdetail');
        }
    }


    //学生和教师列表
    public function tAstudent(){
        if (!empty(cookie('auth'))) {
            $cookieUser = $this->getUser();
            if ($cookieUser['type'] == 1) $this->error('您不能查看消息列表！');
            $teacher =  D('MTeacher'); //教师表
            $student =  D('MStudent'); //学生表
            $ms =  M('MStudent');
            $mt =  M('MTeacher');
            $mu =  M('MUser');
            //获取发件
            $Me = M('MEmail');
            /*获取用户信息*/
            $user =  M('MUser');
            $MD = M("MDepartmentInfo");    /*院系*/

            import("ORG.Util.Page"); //载入分页类
            //教师
            if ($_GET['user'] == 2) {
                //教师列表
                $countT = $teacher->getList(I('get.'), 0, 0,1);
                $pageT = new Page($countT,10000);
                $showPageT = $pageT->show();
                $listT = $teacher->getList(I('get.'),$pageT->firstRow, $pageT->listRows,0);
                //print_r($listT);
            }
            if ($_GET['user'] == 3) {
                //学生列表
                $countS = $student->getList(I('get.'), 0, 0, 1);
                $pageS = new Page($countS, 10000);
                $showPageS = $pageS->show();
                $listS = $student->getList(I('get.'), $pageS->firstRow, $pageS->listRows, 0);
                // print_r($listS);exit;
            }

            $cookieUser = $this->getUser();
            $uid = $user->where(array('u_name'=>$cookieUser['user'],'u_pwd'=>$cookieUser['pwd']))->find()['u_id'];
            /*获取用户信息*/

            $countM =  $Me->where(array('e_uid'=>$cookieUser['user']))->count();
            $pageM = new Page($countM,self::PAGE_PER_NUM);
            $showPageM = $pageM->show();
            $listM =   $Me->where(array('e_uid'=>$cookieUser['user']))->order('e_time desc')->limit($pageM->firstRow, $pageM->listRows)->select();
            foreach($listM as $k=>$v){
                $msName =  $ms->where(array('s_no'=>$v['e_sid']))->find()['s_name'];
                $mtName =  $mt->where(array('t_no'=>$v['e_sid']))->find()['t_name'];
                if (!empty($msName)) {
                    $_name = $msName;
                }
                if (!empty($mtName)) {
                    $_name = $mtName;
                }
                $listM[$k]['username'] = $_name;
            }
            //获取收件
            $countM_s =  $Me->where(array('e_sid'=>$cookieUser['user']))->count();
            $pageM_s = new Page($countM_s,self::PAGE_PER_NUM);
            $showPageM_s = $pageM_s->show();
            $listM_s =   $Me->where(array('e_sid'=>$cookieUser['user']))->limit($pageM_s->firstRow, $pageM_s->listRows)->select();
            foreach($listM_s as $k=>$v){

                $msName =  $ms->where(array('s_no'=>$v['e_uid']))->find()['s_name'];
                $mtName =  $mt->where(array('t_no'=>$v['e_uid']))->find()['t_name'];
                $muName =  $mu->where(array('u_id'=>$v['e_uid']))->find()['u_name'];
                if (!empty($msName)) {
                    $_name = $msName;
                }
                if (!empty($mtName)) {
                    $_name = $mtName;
                }
                if (!empty($muName)) {
                    $_name = $muName;
                }
                $listM_s[$k]['username'] = $_name;
            }
            $MDaLL = $MD->select();
            $this->assign('MDaLL', $MDaLL);
            $this->assign("pageM_s", $showPageM_s);
            $this->assign("listM_s", $listM_s);
            $this->assign("pageM", $showPageM);
            $this->assign("listM", $listM);
            $this->assign("pageT", $showPageT);
            $this->assign("listT", $listT);
            $this->assign("pageS", $showPageS);
            $this->assign("listS", $listS);
            $this->assign("uid", $uid);
            $this->assign("userid", $cookieUser['user']);
            $this->assign('auth',cookie('auth'));
            $this->display('Mobile:tAstudent');
        }else {
            $this->error('请登录！');
        }


    }

    //补登会议删除更改状态
    public function budengdel(){
        if (empty(cookie('auth'))) $this->error('请登录！');
        /*获取用户信息*/
        $user =  M('MUser');
        $D = D("Meeting");
        $cookieUser = $this->getUser();
        if ($cookieUser['type'] == 2 || $cookieUser['type'] == 1) $this->error('您不能删除补登会议！');
        $meeting = $D->where(array('m_id'=>I('get.id')))->find();
        if (empty($meeting)) $this->error('没有找到补登会议！');
        $del = $D->where(array('m_id'=>I('get.id')))->save(array('m_del'=>1));
        if ($del >0) die('1');
    }

    //我的会议
    public function mymeet(){
        if (empty(cookie('auth'))) die('1');
        $user =  M('MUser');
        $Mt = $D =M('Meeting');
        $Ms =  M('MSignup');
        $M = M('MMt');
        $Mg = M('MGrade');
        $cookieUser = $this->getUser();
        if ($cookieUser['type'] !=1) {
            $uid = $user->where(array('u_name' => $cookieUser['user'], 'u_pwd' => $cookieUser['pwd']))->find()['u_id'];
        }else {
            $uid = $user->where(array('u_pwd' => $cookieUser['pwd']))->find()['u_id'];
        }

        //得到报名的会议
        $Ms_all =  $Ms->where(array('s_uid'=>$uid))->field('s_mid')->select();
        foreach ($Ms_all as $k=>$v) {
            $Ms_all[$k] = $v['s_mid'];
        }
        $Ms_all_im = implode(',', $Ms_all);
        $count = $Mt->where(array('m_a_status'=>1,'m_id'=>array('in',$Ms_all_im)))->count();
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $MtAll = $Mt->where(array('m_a_status'=>1,'m_id'=>array('in',$Ms_all_im)))->order('m_time DESC')->limit($page->firstRow , $page->listRows)->select();

        foreach ($MtAll as $k=>$v){
            $MtAll[$k]['m_type'] = $M->where(array('mt_id'=>$v['m_type']))->find()['mt_name'];
            $MtAll[$k]['m_address'] = M('MRoom')->where(array('room_name'=>$v['m_rname']))->find()['room_address'];
        }
        $this->assign('MtAll',$MtAll);
        $this->assign("page", $showPage);


        /*获取用户信息*/
        $count2 = $Mt->where(array('m_mtype'=>array('neq', 1),'m_uid'=>$uid))->count();
        import("ORG.Util.Page"); //载入分页类
        $page2 = new Page($count2,self::PAGE_PER_NUM);
        $showPage2 = $page2->show();
        $MtAll2 = $Mt->where(array('m_mtype'=>array('neq', 1),'m_uid'=>$uid))->order('m_time DESC')->limit($page2->firstRow , $page2->listRows)->select();
        foreach ($MtAll2 as $k=>$v){
            $MtAll2[$k]['m_type'] = $M->where(array('mt_id'=>$v['m_type']))->find()['mt_name'];
            $MtAll2[$k]['m_address'] = M('MRoom')->where(array('room_name'=>$v['m_rname']))->find()['room_address'];
        }
        $this->assign('MtAll2',$MtAll2);
        //$this->assign("page", $showPage);


        /*获取用户信息*/
        if ($cookieUser['type'] == 2 || $cookieUser['type'] == 1) {
            $this->assign('flag',true);
        }
        //$this->error('您不能查看补登会议！');
        /*获取用户信息*/
        $count = $D->getList(I('get.'), 0, 0,1,1,$uid,1);
        import("ORG.Util.Page"); //载入分页类
        $page = new Page($count,self::PAGE_PER_NUM);
        $showPage = $page->show();
        $list = $D->getList(I('get.'),$page->firstRow, $page->listRows,0,1,$uid,1);

        foreach ($list as $k=>$v){
            $list[$k]['m_type'] = $M->where(array('mt_id'=>$v['m_type']))->find()['mt_name'];
            $list[$k]['m_xuefen'] = $Mg->where(array('g_id'=>$v['m_grade']))->find()['g_xuefen'];
        }
        $this->assign("page", $showPage);
        $this->assign("list", $list);
        $this->display('Mobile:mymeet');
    }


/*
    public function recemess(){
        $this->display('Mobile:recemess');
    }**/


    public function zhujiang(){
        $this->display('Mobile:zhujiang');
    }

    //标准会议室
    public function baosuc(){
        if (empty(cookie('auth'))) $this->error('请登录！');
        $id =  I('get.id');
        $date = I('get.date');
        if ($id > 0) {
            $user = M('MUser');//获取用户信息
            $D = M("Meeting");
            $M = M('MMt');
            $Mg = M('MGrade');
            $Mu = M('MUser');
            $Mr = M('MRoom');
            $Msh = M("MShape");
            $MM = M("MMechanism"); //所属机构
            $cookieUser = $this->getUser();
            if ($cookieUser['type'] == 1) $this->error('您不能查看已有会议！');
            $uid = $user->where(array('u_name' => $cookieUser['user'], 'u_pwd' => $cookieUser['pwd']))->find()['u_id'];
            $list =  $Mr->where(array('room_id'=>$id))->limit('0,1')->find();
            if (empty($list)) $this->error('错误！');
            $MMaLL = $MM->where(array('m_id' => $list['room_jigou']))->find()['m_name'];
            $list['room_jigou'] = $MMaLL;
            $list['room_shape'] = $Msh->where(array('s_id' => $list['room_shape']))->find()['s_name'];
            $list['meeting'] = $D->where(array('m_rname' => $list['room_name'],'m_time'=>$date))->select();
            $list['time'] = $D->field('m_start,m_end')->where(array('m_rname' => $list['room_name'],'m_time'=>$date))->select();
            $list['time'] = json_encode($list['time']);
            //过期时间清0
            if (!empty($list['room_expire']) && $list['room_expire'] > 0) {
                $ex =  time() - $list['room_expire'];
                if ($ex >= 1200) {
                    M('MRoom')->where(array('room_id' => $id))->save(array('room_expire' => 0));
                }else if ($ex <= 1200) {
                   // $this->error('有别人正在添加会议');
                }
            }
            //print_r($list);exit;
            $this->assign("list", $list);
            $this->assign('date', $date);
            $this->display('Mobile:baosuc');
        }else {
            $this->error('错误！');
        }
    }



    //会议室
    public function chosemeet(){

        if (empty(cookie('auth'))) die('1');
        //列出会议室
        $Mr =  M('MRoom');
        $Me = M('Meeting');
        $MM = M("MMechanism"); /*所属机构*/
        $MD = M("MDepartmentInfo");/*院系*/
        $map['room_type'] = 1;

        //会议室名称搜索
        if($_GET['room_name']) {
            $map['room_name'] = array('like', '%'.trim($_GET['room_name']).'%');
        }

        //会议发布时间排序
        if($_GET['start_date']){
            $map['room_time'][] = array('egt', strtotime(trim($_GET['start_date'])));
        }

        if($_GET['end_date']){
            $_end_time = trim($_GET['end_date']);
            $map['room_time'][] = array('elt', strtotime($_end_time)+(24*60*60-1));
        }

        //会议类型
        if($_GET['room_number']){
            if ($_GET['room_number'] == 1) {
                $map['room_number'] = array('between', '1,5');
            }else if ($_GET['room_number'] == 2){
                $map['room_number'] = array('between', '5,10');
            }else if ($_GET['room_number'] == 3){
                $map['room_number'] = array('between', '10,20');
            }else if ($_GET['room_number'] == 4){
                $map['room_number'] = array('between', '20,50');
            }else if ($_GET['room_number'] == 5){
                $map['room_number'] = array('egt', '50');
            }
        }
        $MrInfo =  $Mr->where($map)->select();
        //echo $Mr->getLastSql();
        foreach ($MrInfo as $k=>$v) {
            $MMaLL = $MM->where(array('m_id'=>$v['room_jigou']))->find()['m_name'];
            $MDaLL = $MD->where(array('d_id'=>$v['room_yx']))->find()['d_name'];
            $MrInfo[$k]['room_jigou'] = $MMaLL;
            $MrInfo[$k]['room_yx'] = $MDaLL;
            $MrInfo[$k]['meeting_jinxing'] = $Me->field('m_id,m_name,m_start,m_end,m_time,m_rname')->where(array('m_rname'=>$v['room_name']))->order('m_time ASC')->select();
            foreach ($MrInfo[$k]['meeting_jinxing'] as $k1=>$v2) {
                $MrInfo[$k]['meeting_jinxing'][$k1] = $v2;
            }
        }


        $cur = $this->getmonsun();
        $ThisDate = array();
        $new = array();
        $newDate = array();
        for($i = strtotime(date('Y-m-d',$cur['mon'])); $i <= strtotime(date('Y-m-d',$cur['sun'])); $i += 86400) {
            $ThisDate[date("Y-m-d",$i)]=date("Y-m-d",$i);
        }

        foreach ($ThisDate as $k=>$v){
            foreach ($MrInfo as $k1=>$v1) {
                $MrInfo[$k1]['meeting_jinxing2'][$k] = array();
                foreach ($MrInfo[$k1]['meeting_jinxing'] as $_k=>$_v){
                    if ( $k === $_v['m_time']) {
                        $new[] = $_v;
                        //print_r($new);
                        $MrInfo[$k1]['meeting_jinxing2'][$k] = $MrInfo[$k1]['meeting_jinxing'];
                    }
                }
            }
        }


        foreach ($MrInfo as $k11=>$v11) {
            foreach ($MrInfo[$k11]['meeting_jinxing2'] as $k1 => $v1) {
                foreach ($v1 as $_k1 => $_v1) {
                    if ($k1 != $_v1['m_time']){
                        unset($MrInfo[$k11]['meeting_jinxing2'][$k1][$_k1]);
                    }
                }
            }
        }

        //print_r($MrInfo);exit;

        $dateAll = array(1,2,3,4,5,6,7);
        $this->assign('dateAll', $dateAll);
        $this->assign('mon', $cur['mon']);
        $this->assign('sun', $cur['sun']);
        $this->assign('MrAll', $MrInfo);
        $this->display('Mobile:chosemeet');
    }
//获取周一到周日
    private function getmonsun($start = 0,$end = 14){
        //$start = 0 7 14 21
        //$end =14 7 0 -7 -14
        $curtime=time();
        $curweekday = date('w');
        $curweekday = $curweekday?$curweekday:7;
        $curmon = $curtime - (($curweekday-1)+$start)*86400;
        $cursun = $curtime + (($end-7) - $curweekday)*86400;
        $cur['mon'] = $curmon;
        $cur['sun'] = $cursun;
        return $cur;
    }

    //校外人员进入
    public function myMeeting(){
        $this->display('Mobile:myMeeting');
    }


    //添加补登会议
    public function budeng(){
        $D = D("Meeting");
        /*获取用户信息*/
        $user =  M('MUser');
        $cookieUser = $this->getUser();
        if ($cookieUser['type'] == 2 || $cookieUser['type'] == 1) $this->error('您不能添加补登会议！');
        $uid = $user->where(array('u_name'=>$cookieUser['user'],'u_pwd'=>$cookieUser['pwd']))->find()['u_id'];
        /*获取用户信息*/
        if(IS_POST){
            if (empty($_POST['m_name'])) {
                $this->error('会议名称不能为空！');
            }

            if (empty($_POST['m_time'])) {
                $this->error('会议时间不能为空！');
            }

            if (empty($_POST['m_address'])) {
                $this->error('会议地点不能为空！');
            }

            if (empty($_POST['m_content'])) {
                $this->error('会议内容不能为空！');
            }

            try {
                $_POST['time'] = time();


                $user =  M('MUser'); //用户表
                $uid = $user->where(array('u_name'=>$cookieUser['user'],'u_pwd'=>$cookieUser['pwd'],'u_type'=>$cookieUser['type']))->find()['u_id'];
                $ms =  M('MStudent');
                $mt =  M('MTeacher');
                $msName =  $ms->where(array('s_no'=>$cookieUser['user']))->find()['s_name'];
                $mt_Name =  $mt->where(array('t_no'=>$cookieUser['user']))->find()['t_name'];
                if (!empty($msName)) {
                    $_name = $msName;
                }
                if (!empty($mt_Name)) {
                    $_name = $mt_Name;
                }




                if($data = $D->create(I('post.'))){
                    $data['m_xingming'] = $_name;
                    $data['m_no'] = $cookieUser['user'];
                    $re = $D->add($data);
                    if($re){
                       // $this->success("添加成功",U("/Mobile/mymeet"));
                        echo "<script type='text/javascript'>location.href='/Mobile/mymeet.html'</script>";
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage(),U("/Index/addbudeng"));
            }

        }
        $M = M('MMt');
        $mtAll = $M->select();
        $this->assign('mtAll', $mtAll);
        $this->assign("uid", $uid);
        $this->display('Mobile:budeng');
    }




    //会议资料申请成功
    public function shensuc(){
        $Mf = M('MFormat');
        //海报图
        $fShow =  $Mf->field('f_show,f_show2')->where(array('f_confirm'=>1))->find();
        $this->assign('fshow', $fShow);
        $this->display('Mobile:shensuc');
    }

    //自筹会议资料添加
    public function writemeet(){

        $id = I('get.id');
        $type = I('get.type');
        $D = D("Meeting");
        /*获取用户信息*/
        $user =  M('MUser');
        $cookieUser = $this->getUser();
        $uid = $user->where(array('u_name'=>$cookieUser['user'],'u_pwd'=>$cookieUser['pwd']))->find()['u_id'];

        /*获取用户信息*/
        if(IS_POST){
            if (empty($_POST['m_name'])) {
                $this->error('会议标题不能为空！');
            }


            if (empty($_POST['m_time'])) {
                $this->error('会议日期不能为空！');
            }

            if (empty($_POST['m_start'])) {
                $this->error('会议间隔开始时间不能为空！');
            }
            if (empty($_POST['m_end'])) {
                $this->error('会议间隔结束时间不能为空！');
            }

            if (empty($_POST['m_number'])) {
                $this->error('会议人数不能为空！');
            }



            if (empty($_POST['m_phone'])) {
                $this->error('电话不能为空！');
            }
            try {
                $file_name = $this->upload($_FILES['m_images'],'images');
                $file_name_zilaio = $this->upload($_FILES['m_ziliao'],'ziliao','file');
                $_POST['m_images'] = $file_name;
                $_POST['m_ziliao'] = $file_name_zilaio;
                $_POST['time'] = time();
                $roomid = $_POST['id'];
                $roomexpire =  M('MRoom')->field('room_expire')->where(array('room_id'=>$roomid))->find();
                if (!empty($roomexpire['room_expire']) && $roomexpire['room_expire'] > 0) {
                    $ex =  time() - $roomexpire['room_expire'];
                    if ($ex >= 1200) {
                        M('MRoom')->where(array('room_id'=>$roomid))->save(array('room_expire'=>0));
                        $this->error('会议室选择20分钟后，会议申请未提交，从新选择！',U("/Index/shentable"));
                        return ;
                    }
                }

                if($data = $D->create(I('post.'))){
                    $re = $D->add($data);
                    // echo $D->getLastSql();exit;
                    if($re){
                        $Ms =  M('MSignup');
                        $data1['s_uid'] = $uid;
                        $data1['s_mid'] = $re;
                        $data1['s_time'] = time();
                        $Ms->add($data1);
                        $D->where(array('m_id'=>$re))->save(array('m_bianma'=>'hy00'.$re));
                        $this->success("提交成功",U("/Mobile/shensuc"));
                        return ;
                    }else{
                        throw new Exception('添加失败', 1);
                    }
                }else{
                    throw new Exception($D->getError(), 1);
                }

            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

        }





        if ($id>0) {
            $Mr = M("MRoom"); //会议室表
            $M = M('MMt'); //会议类型
            $Mg = M('MGrade'); //会议等级
            $oneMr = $Mr->where(array('room_id'=>$id))->find();
            if (empty($oneMr)) $this->error('错误！');
            $mtAll = $M->select();
            $gradeAll = $Mg->select();
            $this->assign('gradeAll', $gradeAll);
            $this->assign('mtAll', $mtAll);
            $this->assign('oneMr', $oneMr);
            $this->assign('uid', $uid);
            $this->assign('type', $type);
            $this->assign('id', $id);
            $this->assign('date', I('get.date'));
            $this->assign('m_start', I('get.m_start'));
            $this->assign('m_end', I('get.m_end'));
            $this->display('Mobile:writemeet');
        }else {
            $this->error('错误！');
        }


    }



    //解密cookie--auth
    private function getUser (){
        $deUser =   json_decode(cookie('auth'));
        $user =  decrypt_de($deUser->u,c('MD5_KEY'));
        $pwd =  decrypt_de($deUser->p,c('MD5_KEY'));
        $type =  decrypt_de($deUser->t,c('MD5_KEY'));
        return array('user'=>$user,'pwd'=>$pwd,'type'=>$type);
    }

    //立即参会
    public function canjia (){
        $Ms = M('MSignup');
        $user=M('MUser');
        $Me = M('Meeting');
        $cookieUser = $this->getUser();
        if ($cookieUser['type'] !=1) {
            $uid = $user->where(array('u_name' => $cookieUser['user'], 'u_pwd' => $cookieUser['pwd']))->find()['u_id'];
            $u_name = $user->where(array('u_name' => $cookieUser['user'], 'u_pwd' => $cookieUser['pwd']))->find()['u_name'];
        }else {
            $uid = $user->where(array('u_pwd' => $cookieUser['pwd']))->find()['u_id'];
            $u_name = $user->where(array('u_pwd' => $cookieUser['pwd']))->find()['u_name'];
        }
        if (IS_AJAX) {
            try {
                if (I('post.baoming') == I('post.number')) {
                    $Me->where(array('m_id'=>I('post.s_mid')))->save(array('m_n_exist'=>I('post.baoming')));
                    die('-3');
                }
                $_POST['s_no'] = $u_name;
                $_POST['s_name'] = $this->u_name;
                $_POST['s_time'] = time();
                $_POST['s_uid'] = $uid;
                $_POST['s_mtype'] = $_POST['m_mtype'];
                $_POST['s_meet_name'] = $Me->where(array('m_id'=>$_POST['s_mid']))->find()['m_name'];
                if($data = $Ms->create(I('post.'))){
                    $_ms = $Ms->where(array('s_uid'=>$uid,'s_mid'=>$_POST['s_mid']))->find();
                    if (!empty($_ms)) {
                        die('-2');
                    }
                    $re = $Ms->add($data);
                    if($re){
                        die('-1');
                    }
                }else{
                    die('-2');
                }
            } catch (Exception $e) {
                die('-2');
            }

        }
    }

    //上传
    private function upload($file='',$path = 'images',$type = 'img',$flag = 0){
        $tmp_file = $file['tmp_name'];
        $file_types = explode(".", $file['name'] );
        $file_type = $file_types[count($file_types) - 1];
        if ($flag == 0) {
            if ($type == 'img') {
                if ($file_type != 'png' && $file_type != 'jpg' && $file_type != 'gif' && $file_type != 'jpeg') {
                    // $this->error('图片格式错误！');
                }
            } else if ($type == 'file') {
                if ($file_type != 'doc' && $file_type != 'docx') {
                    //$this->error('文件格式错误！');
                }
            }
        }
        $savePath = SITE_PATH . '/public/upfile/'.$path.'/';
        $str = date ( 'Ymdhis' );
        $file_name = $str . "." . $file_type;
        if (!copy($tmp_file, $savePath . $file_name )) {
            // $this->error('上传失败' );
        }
        return $file_name;
    }


    /**
     * 前端掉此接口，传入经度和纬度
     * @param double $x 纬度
     * @param double $y 经度
     * @return json
     */
    public function getLoction()
    {
        	$expire = time()+self::MOBILE_EXPIRE;
        	$lat = I('param.x');//纬度
        	$lng = I('param.y');//经度
        	if(!$_GET['x'] && !$_GET['y'])
        	{
    		        $this->jsonReturn(0,"获取位置失败");;
    	    }
    	    //北京的城市的code
    	   $location_city = ApiShopModel::DEFAULT_CITYCOD;
    	   $location_city_name = '北京';
    	   //实例工具类
    	   $ToolModel=D('Tool','LogicModel');
    	   //获取经度纬度所在的城市
    	   $CityName=$ToolModel->GetLngLatLocation($lng,$lat);
    	   $retArr=array();
        $retArr['lng']      =$lng;//经度
        $retArr['lat']      =$lat;//纬度     
    	   if(empty($CityName))
        {
            
            $CityName=$location_city_name;//如果为空则默认北京      
        }
        //取得经度和纬度
        $cityRow=D('ApiShopCity')->GetCityIdOrCode(array('name' => $CityName));
        if($cityRow)
        {
            $location_city=$cityRow['code'];
            $retArr['cityId']=$cityRow['id'];
            $retArr['cityName'] =$CityName;//城市名
            $location_city_name=$CityName;
        }
        else 
        {
            $retArr['cityId']=ApiShopModel::DEFAULT_CITYID;
            $retArr['cityName'] =$CityName;//城市名     
        }
        $ToolModel->SetCityCookie($retArr);
        	setcookie('new_cityname', $location_city_name, $expire, '/', $this->cookie_domain);
        	setcookie(self::NEW_CITY, $location_city, $expire, '/', $this->cookie_domain);
        	setcookie(self::OLD_CITY, $location_city, $expire, '/', $this->cookie_domain);
        	$this->jsonReturn(1,array("city_code"=>$location_city,"city_name"=>$location_city_name,"link"=>U('ChangeCity/change?code='.$location_city)));
    }
    
     /**
     * 返回json结果
     */
    protected function jsonReturn($errorNum = 0, $data = array(), $msg = '') {
        $message['result'] = $errorNum;
        $message['data'] = $data;
        $message['message'] = $msg;
        die($this->ajaxReturn($message, 'json'));
    }

    /**获取用户的经度和纬度
     *@完成则跳转到/Index/shop
     */
    /* function index()
     {

         //$this->display('loading');
     }*/

    /*获取推荐商家数据
    *参数：CityCode城市名称
    *经度：lng
    *纬度：lat
    *页数：当前第几页
    */
    function shop()
    {
        $lng    =I("lng");//loading.html传过来的经度
        $lat    =I("lat");//loading.html传过来的纬度
        $from   =I("from");//来源
        $page   =I('page');//请求第几页

        //实例化实体店Model
        $ShopModel=D('ApiShop');
        //设置默认城市北京
        $param=array();
        $param['lat']    =$ShopModel::DEFAULT_CITY_LAT;//北京的纬度
        $param['lng']    =$ShopModel::DEFAULT_CITY_LNG;//北京的经度
        $param['city_id']=$ShopModel::DEFAULT_CITYID;//北京的城市Id
        $param['page']  =empty($page) ? 1 : $page;
        //经度纬度不为空则调用接口得到所在城市
        if(!IS_AJAX)//如果不是Ajax请求
        {
            if(!empty($lng) && !empty($lat))
            {
                //请求抢工长http://m.7gz.com/Touch/getLoction得到经度纬度所在的城市
                $CityName=D('Tool','LogicModel')->GetLngLatLocation($lng,$lat);
                //判断结果
                if($CityName)
                {
                    $cityId=0; //如果CityId是空则默认为北京
                    $cityId=array('name' =>$CityName);

                    //定位成功，该城市没有开通工长业务，则默认为北京
                    $cityId=$ShopModel->GetCityId($cityId);
                    if(!empty($cityId))
                    {
                        $param['city_id']=$cityId;
                        $param['lng']=$lng;
                        $param['lat']=$lat;
                    }
                    else
                    {
                        //没有开通这个城市默认也是北京
                        $CityName='北京';
                        $cityId=$ShopModel::DEFAULT_CITYID;
                    }
                }
                else
                {
                    //定位失败
                    $CityName='北京';
                    $cityId=$ShopModel::DEFAULT_CITYID;
                }

                $this->assign('CityName',$CityName);

                /**设置Cookie*/
                $cookie['cityName']=$CityName;
                $cookie['lng']=$lng;
                $cookie['lat']=$lat;
                $cookie['cityId']=$cityId;
                $toolModel=D('Tool','LogicModel')->SetCityCookie($cookie);
            }
            else
            {

                //检查看一下Cookie是否存在
                //如果城市Cookie存在则用Cookie里面的数据
                $ToolModel=D('Tool','LogicModel');
                $cookCityId=$ToolModel->GetCookieValue('cityId');
                if(!empty($cookCityId))
                {
                    $param['city_id']=$cookCityId;
                    $param['lng']=$ToolModel->GetCookieValue('lng');
                    $param['lat']=$ToolModel->GetCookieValue('lat');
                    $this->assign('CityName',$ToolModel->GetCookieValue('cityName'));
                }
            }

        }
        else
        {
            //如果是Ajax请求则会把这几个参数传过来
            $param['city_id']=I('city_id');
            $param['lng']=$lng;
            $param['lat']=$lat;
            //如果是Ajax请求把页数增加+1，前台页面是从第一页开始加载
            $param['page']  =intval($page)+1;
        }
        $result=$ShopModel->GetRecommendShop($param);
        if(IS_AJAX)
        {
            $OutPut['data']=array();
            $ShopModel->setErrCode(1003);
            if($result)
            {
                $this->CombinBrandName($result);
                $OutPut['data']=$result;
                $ShopModel->setErrCode(1000);
            }
            $OutPut['errCode']=$ShopModel->getErrCode();
            $OutPut['errMsg']=$ShopModel->getErrMsg();
            $this->echoJson($OutPut);

        }
        $this->assign('  ',$result);
        $this->assign('Param',json_encode($param));
        $this->assign("FocusMap",$this->GetFocusMap());//获取焦点图信息
        $this->display("index");
    }

    /*实体店所在地图*/
    function map()
    {
        $id=I('id',0,'intval');
        if(!empty($id))
        {
            $ShopKey='SHG:JIAJU:CacheFileMap'.$id;   //缓存Id
            $ShopData=ApiShopModel::CacheAndData($ShopKey,0);
            if($ShopData)
            {
                $ShopRow=$ShopData;
            }
            else
            {
                $ShopInfo=D('ApiShop')->GetRecommendShop(array('ShopId' =>$id));
                if($ShopInfo)
                {
                    $ShopRow=$ShopInfo[0];
                    ApiShopModel::CacheAndData($ShopKey,$ShopRow);
                }
            }
            $this->assign('data',$ShopRow);
        }
        $this->display();
    }

    /**取焦点图
     *@return array
     */
    private function GetFocusMap()
    {
        $FocusMapCacheKey='SHG:JIAJU:FOCUSMAP';
        //实例化Cache
        $CacheFocusMap=ApiShopModel::CacheAndData($FocusMapCacheKey,0);
        if($CacheFocusMap)
        {
            return $CacheFocusMap;
        }
        $BaseModel=D("Base");
        $MapArray=D("Focusmap")->field("url,src")->order('id desc')->limit(1)->find();
        $MapArray['src']=D('Tool','LogicModel')->GetImgUrl($MapArray['src'],'1059X558');
        ApiShopModel::CacheAndData($FocusMapCacheKey,$MapArray);
        return   $MapArray;
    }

    /**
     *把品牌数据拼成一个字符串
     *@param array $result 查询出来的数据
     */
    private function CombinBrandName(&$result)
    {
        foreach($result as $rkey => $row)
        {
            $brandListName='暂无';
            if(isset($row['brandMap']))
            {
                $brandList=array();
                foreach($row['brandMap'] as $brand)
                {
                    $brandList[]=$brand['brand_name'];
                }
                if($brandList)
                {
                    $brandListName=implode('，',$brandList);
                }
            }
            $result[$rkey]['brandMap']=array('brand_name' => $brandListName);
            if(isset($row['supplierSale']['type']))
            {
                if($row['supplierSale']['type']==2)
                {
                    $result[$rkey]['supplierSale']['discount']=$row['supplierSale']['discount']*10;
                }

            }
            else
            {
                $result[$rkey]['supplierSale']['type']=3; //没有优惠信息
            }

        }
    }


    /*需要展示的数据：
    商家图片:
    商家名称:
    商家地址：
    主营品牌：
    优惠信息:
    */

}