<?php

class AdminPublicModel extends Model {

    public function auth($datas) {
        $datas = $_POST;
        if (cookie('verify') != md5($_POST['verify_code'])) {
           die(json_encode(array('status' => 0, 'info' => "验证码错误啦，再输入吧")));
        }
       
        $M = M("User");
        $where = array(
            'name' => $datas['tel'],
            'status'=> array('gt',0)
            );
        if ($M->where($where)->count() >= 1) {
            $info = $M->where($where)->find();
            if ($info['status'] == 2) {
            	$info = '你的账号被禁用，有疑问联系管理员吧';
                return array('status' => 0, 'info' => $info);
            }

            if ($info['pwd'] == encrypt($datas['pwd'])) {
                $loginMarked = C("TOKEN");
                $loginMarked = md5($loginMarked['admin_marked']);
                $shell = $info['aid'] . md5($info['pwd'] . C('AUTH_CODE'));
                $_SESSION[$loginMarked] = "$shell";
                $shell.= "_" . time();
                setcookie($loginMarked, "$shell", 0, "/");
                unset($info['pwd']);
                $_SESSION['my_info'] = $info;
                return array('status' => 1, 'info' => "登录成功", 'url' => U("/Admin/Index/index"));
            } else {
                return array('status' => 0, 'info' => "账号或密码错误");
            }
        } else {
            return array('status' => 0, 'info' => "不存在：" . $datas["name"] . '的管理员账号！');
        }
    }

    public function findPwd() {
        $datas = $_POST;
        $M = M("Admin");
                if ($_SESSION['verify'] != md5($_POST['verify_code'])) {
            die(json_encode(array('status' => 0, 'info' => "验证码错误啦，再输入吧")));
        }
//        $this->check_verify_code();
        if (trim($datas['pwd']) == '') {
            return array('status' => 0, 'info' => "密码不能为空");
        }
        if (trim($datas['pwd']) != trim($datas['pwd1'])) {
            return array('status' => 0, 'info' => "两次密码不一致");
        }
        $data['aid'] = $_SESSION['aid'];
        $data['pwd'] = md5(C("AUTH_CODE") . md5($datas['pwd']));
        $data['find_code'] = NULL;
        if ($M->save($data)) {
            return array('status' => 1, 'info' => "你的密码已经成功重置", 'url' => U('Access/index'));
        } else {
            return array('status' => 0, 'info' => "密码重置失败");
        }
    }

}

?>
