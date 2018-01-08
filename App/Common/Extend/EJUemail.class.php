<?php

class EJUemail{
	/**
	 * 发送邮件接口
	 * @param   emial   收件人邮箱    例如：a@126.com (暂时不支持群发)
	 * @param   subject 邮件标题
	 * @param   content 邮件内容
	 * 发件人昵称： 工长俱乐部       发送类型： 实时发送     发送邮箱： sinahouse@vip.sina.com
	 */
	public function emailSend($email, $subject, $content){
		if(empty($email) || empty($subject) || empty($content))
			return false;
		$destURL  = 'http://ems.leju.com/api/mail/send';
		$appid = '2014091681';
		$appkey = '628e205a35fd0c0d27e820c4388818c8';
		$params = array(
				'appid' 	=>$appid,
				'email'     =>$email,
				'subject'   =>$subject,
				'content'   =>$content,
				'format'    =>'json',
		);
		$sign = $this->getSign($params,$appkey);
		$paramStr = "appid={$appid}&email={$email}&subject={$subject}&content={$content}&format=json&sign={$sign}";
		$res = $this->curl($destURL, $paramStr,'post');
		$res = json_decode($res,true);
		if($res['status'] != 1){
			return false;
		}
		return true;
	}
	/**
	 * 计算签名函数
	 * 参数说明：
	 *  $data 将除签名以为其它所有参数共同构成的数组，这些参数包括包括通用和非通用参数；
	 *  $key  是appid对应的由平台分配的密钥
	 */
	public function getSign(&$data, $key)
	{
		$string = $this->getPostString($data);
		return md5($string.$key);
	}
	
	/**
	 * 数组系列化成字符串
	 */
	public function getPostString(&$post){
		$string = '';
		if(is_array($post))   {
			foreach($post as $item)  {
				if(is_array($item))
					$string .= getPostString($item);
				else
					$string .= $item;
			}
		}else{
			$string = $post;
		}
		return $string;
	}
	
	//CURL请求
	public static function curl($destURL, $paramStr='',$flag='get'){
		if(!extension_loaded('curl')) exit('php_curl.dll');
		$curl = curl_init();
		if($flag=='post'){//post
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $paramStr);
		}
		curl_setopt($curl, CURLOPT_URL, $destURL);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$str = curl_exec($curl);
		curl_close($curl);
		return $str;
	}
}
