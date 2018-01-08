<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends AdminAction
{
	 public function index() {
        /*
         $str = "我的名字是？一般人我不告诉他！";
        //加密内容
         $key = "key:111";
        //密钥
         $cipher = MCRYPT_DES;
        //密码类型
         $modes = MCRYPT_MODE_ECB;
        //密码模式
         $iv = mcrypt_create_iv(mcrypt_get_iv_size
         ($cipher,$modes),MCRYPT_RAND);//初始化向量
         echo "加密明文：".$str."<p>";
         $str_encrypt = mcrypt_encrypt($cipher,
             $key,$str,$modes,$iv);
        //加密函数
         echo "加密密文：".$str_encrypt." <p>";
         $str_decrypt = mcrypt_decrypt($cipher,
             $key,$str_encrypt,$modes,$iv);
        //解密函数
         echo "还原：".$str_decrypt;
*/
         $this->display('Public/share');
    }


    /*系统信息*/
    public function show (){
        //echo phpinfo();exit;

        $this->display();
    }


   /**
    * 修改密码吗
    */
   public function myInfo()
    {
    	$M = M("User");
    	$admin_id = $_SESSION['my_info']['id'];
    	$info = $M->find($admin_id);
    	if (IS_POST)
    	{
    		$data = $this->_post();
    		if ($info['pwd'] != encrypt($this->_post('old_pwd')) )
    		{
    			$this->error('旧密码错误');
    		}
    		if (!$this->_post('new_pwd','trim'))
    		{
    		    $this->error('密码不能为空');
    		}
    		if ($this->_post('new_pwd') != $this->_post('re_pwd'))
    		{
    			$this->error('两次密码输入不一致');
    		}
    		$data = array('pwd' => encrypt($this->_post('new_pwd')));
    		$res = $M->where('id='.$admin_id)->save($data);
    		if ($res)
    		{
    			$this->redirect('Public/loginOut');
    		}else
    		{
    			$this->error('修改失败');
    		}
    	}
    	$this->assign('info',$info);
    	$this->display('Public/myInfo');
    }


}