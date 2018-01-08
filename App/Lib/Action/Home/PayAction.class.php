<?php
/**
*订单页面和支付页
*支付类型支付宝6，银联手机3，微信支付4
*/
class PayAction extends BaseAction
{
    
    /**
     * 初始化数据
     * 使用方法:
     * @param int $id 实体店的Id
     */
    function _initialize()
    {
        $this->errMsg='页面数据有误，请重新提交';
        $this->PayErrMsg='支付出错了，请重新尝试';
        $this->ForbiddenMsg ='抱歉，本商场暂不支持此服务';
        $this->weChatOpenId='weChatH5_openid';
        /*判断是不是微信支付*/
        $this->isWeiXin=D('Tool','LogicModel')->CheckWeiXinAgent();
        $this->assign('isWeiXin',$this->isWeiXin);
        $id=I('id',0,'intval');//获取店铺Id
        $openid=I('openid');//获取weChatopenID
        if($openid && $this->isWeiXin)
        {
            cookie($this->weChatOpenId,$openid,time() + 15552000,'/'); //把OpenId放到cookie里面
        }
        
        if(!empty($id))
        {
            //查询实体的数据得到实体店名称和供应商对应的优惠
            $ShopModel=D('ApiShop');
            $param['ShopId']=$id;
            $param['role_id']=$ShopModel::SHOP_ROLE_ID;
            $result=$ShopModel->GetRecommendShop($param); 
            if(!$result)
            {
                $this->ShowErrMsg($this->ForbiddenMsg,'/');    
            }
            $this->shopRest=$result[0];    
        }
        else 
        {
             $shopId=I('get.entId',0,'intval');//这个ID用于判断断网情况下返回上一次购买页面
            if(!empty($shopId))
            {
                echo '<script>window.history.back();</script>';        
                exit();
            }
            else 
            {
                $this->ShowErrMsg($this->ForbiddenMsg,'/');  
            }
             
        }
        $this->assign('WebTitle','-抢工长闪购'); //网页标题
        
                  
    }
    /**
     * 订单表单用户信息输入页面
     * 使用方法:
     * @param int $id 实体店的Id
     * @return 下单页面
     */
    function index()
    {
        if($this->shopRest)
        {
            $this->assign('result',$this->shopRest);
        }
        else
        {
           $this->ShowErrMsg($this->errMsg);
        }
        $this->display('order');   
    }
    
     /**
     * 订单选择支付方式页面及订单信息确认页面
     * 使用方法:
     * @param int $id 实体店的Id
     * @return 支付方式选择页面
     */
    function payment()
    {
        if($this->shopRest)
        {
            $this->CheckFormVerify(I());//验证表单数据是否正确
           
            if(!$this->CheckReallyPriceVerify($this->shopRest['supplierSale'],I('total_price'),I('really_price')))//检查真实价格和优惠是否被篡改
            {
               $JumpUrl='/Pay/?id='.I('id',0,'intval').'';
               $this->ShowErrMsg($this->errMsg,$JumpUrl);  
            }
            $postData=$this->FilterFormData(I('post.',0,'htmlspecialchars'));
            //通过实体店找到对应的供应商信息
            $supplierParam=array();
            $supplierModel=D('ApiShop');
            $supplierParam['status']    =$supplierModel::SHOP_ROLE_STATUS;
            $supplierParam['id']        =$this->shopRest['supplier_id'];
            $supplierParam['role_id']   =$supplierModel::SUPPLIER_ROLE_ID;
            $supplierInfo=$supplierModel->GetShopUserInfo($supplierParam);
            //$cityInfo=D('ApiShopCity')->GetCityIdOrCode(array('id' =>$this->shopRest['city_id']));
            //查询供应商的信息
            if($supplierInfo && $supplierInfo['id']==$this->shopRest['supplier_id'])
            {
                    
                    $payData=array();           //支付中心数据的数据
                    $payData['entity_id']       =$this->shopRest['id'];//实体店Id
                    $payData['supplier_id']     =$this->shopRest['supplier_id'];//供应商ID 
                    $payData['supplier_name']   =$supplierInfo['business_name'];      
                    $payData['company_id']      =$this->shopRest['company_id'];
                    $platform_rate              =$supplierInfo['platform_rate'];//平台扣点
                    $platform_rate              =empty($platform_rate) ? 0 : $platform_rate;
                    $payData['rate']            =$platform_rate;//平台扣点
                    $really_price               =$postData['really_price'];//实收金额
                    $really_price               =empty($really_price) ? 0 : $really_price;   
                    $payData['total_amount']    =$postData['total_price'];//总金额
                    $payData['order_amount']    =$really_price;//实收金额
                    /*ping_amount平台结算金额公式
                    *@平台平台结算金额=实际结算金额*平台扣点
                    */
                    $ping_amount=0;
                    $ping_amount=round($really_price*$platform_rate,2);  
                    $payData['ping_amount']     =$ping_amount;
                    /*supplier_amount供应商结算金额公式
                    *@供应商结算金额=实际金额-平台结算金额
                    */
                    $supplier_amount=0;
                    $supplier_amount=$really_price-$payData['ping_amount'];
                    $payData['supplier_amount'] =$supplier_amount;
                    $payData['mobile']          =$postData['user_tel'];
                    $payData['buyer_name']      =urldecode($postData['user_name']); 
                    $payData['city']            =$supplierInfo['local_station_code'];
                   /*第一步请求支付接口*/
                    $getResp=Request::post(C('SHG_PAYPAL_URL'),$payData,60,true);
                    $errCode=$getResp['errCode'];//返回错误码
                    $respData=$getResp['data']; //返回的数据
                    /*{
                      "errCode": 1000, 
                      "errMsg": "成功", 
                      "data": {
                        "tid": "1", 
                        "order_id": 1,
                        "t_amount":900,
                        "flashsale_sn":"20161125560001"
                      }
                    }*/
                    
                    if($getResp['errCode']==1000)//请求成功
                    {
                        $tid        =$respData['tid'];//支付id
                        $order_id   =$respData['order_id']; //order_id
                        $t_amount   =$respData['t_amount']; //t_amount
                        if(!empty($tid) && !empty($order_id)) //支付Id和订单ID不能为空
                        {
                            //保存订单历史数据
                            $this->SaveDataToOrdering($postData,$respData,$supplierInfo);
                            $sign=genSign(genString($respData));//把提交的参数加密
                            $this->assign('sign',$sign);
                            $this->assign('postData',$respData); 
                        }
                        else 
                        {
                             //请求接口失败显示失败信息
                            $this->ShowErrMsg($this->PayErrMsg); 
                        } 
                    }
                    else 
                    {
                        //请求接口失败显示失败信息
                        $this->ShowErrMsg($this->PayErrMsg);        
                    }
                    //$this->assign('pay_url',C('SHG_TRANSORDER_URL'));  
            }
            else 
            {
                 $this->ShowErrMsg($this->PayErrMsg);
            } 
            
            $this->assign('result',$this->shopRest); 
        }
        else
        {
           $this->ShowErrMsg($this->errMsg);
        }
        $this->display('pay');   
    }
    
    
    /**支付失败页面回来得新支付
    *@param int $tid 支付id
    *@order_id $order_id订单id
    *@t_amount $t_amount t_amount 
    */
    function repay() 
    {
        $info=I('get.info');
        if($info)
        {
            $jsonArr=$this->Base64JsonDecode($info);
            $respData=$this->FilterPayData($jsonArr);
            $sign=genSign(genString($respData));//把提交的参数加密
            $this->assign('sign',$sign);
            $this->assign('postData',$respData);
        }
        $this->assign('result',$this->shopRest);
        $this->display('pay');   
    }
     /**
     * 选择支付方式提交到支付中心
     * 使用方法:
     * @param int $id 实体店的Id
     * @return 支付中心页面
     */
    function paypal()
    {
                $PostParam=I();
                $payType=I('pay_type');//支付类型
                //获取微信认证回调过来的地址
                $mRebackData=$this->ParseWeChatParam();
                $VerifyFalse=true;//是否需要验证数据正确
                if($mRebackData)
                {
                    $payType=$mRebackData['pay_type']; 
                    $PostParam=$mRebackData; 
                    $VerifyFalse=false;  //微信过来的不再验证数据
                }
                $orderData=$this->FilterPayData($PostParam);
                $VerifySign=I('sign');//得到加密字符串
                $sign=genSign(genString($orderData));//把提交的参数加密
                if($VerifySign!=$sign && $VerifyFalse)
                {
                    //验证提交的数据是否和下单的数据一致
                     $this->ShowErrMsg($this->errMsg);      
                }

                $orderData['type']=$payType;
                
                
                 /*更新历史表中订单的支付类类型状态*/  
                $this->UpdateOrdering($orderData);
                //如果是微信支付没有认证需要先去认证
                if($payType==4)
                {
                    //检测微信是否已经认证
                    $this->CheckWeChatLoginAuth($PostParam);
                    
                    $orderData['openid']     =cookie($this->weChatOpenId);//获取微信的openId
                }
                //第二次请求支付接口，得到一个URL，跳转到这个URL
                $getResp=Request::post(C('SHG_TRANSORDER_URL'),$orderData,60,true);
                
                if($getResp['errCode']==1000)
                {
                     $respUrl=$getResp['data']['url'];
                     //测试支付地址：
                     //$respUrl='/PayBack/succeed?order_amount='.$orderData['t_amount'].'&flashsale_sn='.$orderData['flashsale_sn'].'&order_sn='.$orderData['order_id'].'&entity_id='.$PostParam['id'].'';
                     //最后一次请求支付链接 
                     header('Location: '.$respUrl.'');
                     exit(0);
                }
                else 
                {
                    //请求接口失败显示失败信息
                    
                    $this->ShowErrMsg($this->PayErrMsg);  
                }
    }
    
    
       /**
       * 把订单详细数据保存在ordering表中
       * @param orderData postData
       */
        private function SaveDataToOrdering($postData,$orderData,$supplierInfo)
        {
             //下单数据本地记录一份
                $postData['order_id']       =$orderData['order_id'];//订单号
                $postData['tid']            =$orderData['tid'];//支付id
                $postData['flashsale_sn']   =$orderData['flashsale_sn'];//闪购订单sn
                $postData['supplier_id']    =$this->shopRest['supplier_id'];
                $postData['company_id']     =$this->shopRest['company_id'];
                $postData['shop_id']        =$this->shopRest['id'];
                $postData['user_name']      =urldecode($postData['user_name']);
                $postData['user_address']   =urldecode($postData['user_address']);
                $postData['sale_type']      =$this->shopRest['supplierSale']['type'];
                $postData['sale_rule']      =json_encode($this->shopRest['supplierSale']['discount']);
                $postData['platform_rate']  =empty($supplierInfo['platform_rate']) ? 0 : $supplierInfo['platform_rate'];
                $postData['pay_time']       =time();
                
                //把提交的订单信息保存在自己的数据流水表中
                try { 
                        unset($postData['id']);
                        M('shop_ordering')->add($postData);
                        
                }catch(Exception $e){}    
        }
        
        /**更新下单历史表中订单的支付类型
        *@param array $orderData
        *@return boolean 
        */
        private function UpdateOrdering($orderData)
        {
            $ret=0; 
            try {  
                    $update['pay_update_time']=time();
                    $update['pay_type']=$orderData['type'];
                    $ret=M('shop_ordering')->where(array('tid' => $orderData['tid'],'order_id' => $orderData['order_id']))->save($update);  
                } 
                catch(Exception $e) {}
             return $ret;    
        }
        
       
        /**微信支付如果没有认证需要先去认证*/
        private function CheckWeChatLoginAuth($param)
        {
                $tid        =$param['tid'];
                $order_id   =$param['order_id'];
                $t_amount   =$param['t_amount'];
                $shopid     =$param['id'];
                $pay_type   =$param['pay_type'];
                
                //参数规则tid|order_id|t_amount|pay_type|shopid
                $baseString=$tid.'|'.$order_id.'|'.$t_amount.'|'.$pay_type.'|'.$shopid;
                
                $base64Str=base64_encode($baseString);
                $base64Str=strrev($base64Str);//反转字符串
                $openid=I('openid');//参数里面获得openId
                if(empty($openid))
                {
                    $openid=cookie($this->weChatOpenId);//从cookie里面取出openId
                }
                if($this->isWeiXin && empty($openid))//如果是微信和OpenId为空则请求微信认证接口
                {
                    $returl=C('MOBILE_SITE_DOMAIN').'/Pay/paypal/id/'.$shopid.'/reback/'.$base64Str.'';//回调回来的url
                    redirect('http://www.7gz.com/SHG/index?returl='.urlencode($returl).'');exit;      
                }
                /**判断结束*/
        }
        
    
     /**
     * 验证下单数据是否正确
     * @param array $param 表单数据
     */
     private function CheckFormVerify($param)
     {
            $param['really_price']=empty($param['really_price']) ? 0 : $param['really_price'];
            if(empty($param['user_name']))
            {
                $this->ShowErrMsg($this->errMsg);    
            }
            elseif(!is_numeric($param['total_price']) || !is_numeric($param['really_price']))
            {
                $this->ShowErrMsg($this->errMsg);    
            }
            elseif(!$this->CheckMobileVerify($param['user_tel']))
            {
                $this->ShowErrMsg($this->errMsg);    
            }   
     }
     
     /**
     * 验证真实价格是否正确
     * 使用方法:
     * @param array $supplierSale 供应商的优惠方案
     * @param double $total_price 消费总额
     * @param double $really_price 前台页面传过来的计算好实会金额
     * @return boolean
     */
     
   private function CheckReallyPriceVerify($supplierSale,$total_price,$really_price)
    {
        //如果没有优惠信息则总价格等于实际价格
        if(!isset($supplierSale['discount']))
        {
            if($really_price>=$total_price)
            {
                return true;   
            }  
            return false;
        }
        
        if($supplierSale['type']==1)    //满减
        {
            foreach($supplierSale['discount'] as $original => $minus)
            {
                //总价减-满减价
                if($total_price >= $original)
                {
                    $sale=($total_price/$original)*$minus;
                    $sale=$total_price-round($sale,1);
                    //以下数据对比是取整对比，小数点后面的数据没有对比
                    $sale=intval($sale);
                    $really_price=intval($really_price);
                    if($really_price>=$sale) return true;   
                }
                elseif($really_price>=$total_price) 
                {
                  return true;     
                }
            }  
        }
        elseif($supplierSale['type']==2) //折扣
        {
            $disCount=$supplierSale['discount'];
            //优惠价等总价*折扣 
            $sale=$total_price*$disCount;
            //数据也是取整对比
            $sale=intval($sale);
            $really_price=intval($really_price);
            if($really_price>=$sale) return true;
        }
        return false;
    }
     
    /**
     * 错误信息提示页面
     * 使用方法:
     * @param string $erMsg 变量的名称 支持指定类型
     * @param string $url 要跳转的页面，0代表空为history.back()
     * @param int $waitSecond 秒数
     * @return string 页面内容
     */
    private function ShowErrMsg($erMsg,$url=0,$waitSecond=3)
    {
            $errMsg['error']=$erMsg;
            $jumpUrl=I('server.HTTP_REFERER');
            if(!empty($url))
            {
                $jumpUrl=$url;  
            }
            if(empty($url))
            {
                $jumpUrl='';
            }
            $errMsg['jumpUrl']=$jumpUrl;
            $errMsg['waitSecond']=$waitSecond;
            $this->assign($errMsg);
            $this->display('Common/show');
             exit();       
    }
    

    /**
     * 验证手机号是否正确
     * 使用方法:
     * @param string $mobile 要验证的手机号
     * @param string $role 验证规则
     * @return boolean
     */
     private function CheckMobileVerify($mobile,$rule='mobile')
     {
        return D()->regex($mobile,$rule); 
     }
     
     
     
     /**
     * 把表单数据过滤得到需要提交的字段
     * 使用方法:
     * @param array $postData 表单Post的数据
     * @param boolean $encod 是否需要urlencode
     * @return array 过滤后的数据
     */
     private function FilterFormData($PostData,$encod=true)
     {
        $retData=array();
        $AllowArray=array('id','user_name','user_tel','user_address','total_price','really_price');
        foreach($AllowArray as $keys)
        {
            $keyValues=$PostData[$keys];
            if($encod) $keyValues=is_string($keyValues) ? urlencode($keyValues) : $keyValues;
            $retData[$keys]=$keyValues;   
        }
        unset($PostData);
        return  $retData;
   
    }
    
    /**过滤支付订单字段
    *@param array $payData 第二个支付接口需要的数据
    *@return array 过滤后的数据
    */
    private function FilterPayData($payData)
    {
        $retData=array();
        $AllowArray=array('order_id','tid','t_amount','type','flashsale_sn');
        foreach($AllowArray as $key => $val)
        {
            if(isset($payData[$val]))
            {
                $retData[$val]=$payData[$val];    
            }    
        } 
        return $retData; 
    }
    
    /**解码
    *把base64_encode()字符串解析出来
    *把json_encode()字符串转换成数组
    */
    private function Base64JsonDecode($Base64Encode)
    {
        if(!empty($Base64Encode))
        {
            $base64String   =base64_decode($Base64Encode);//解码base64_encode
            $jsonArr        =json_decode($base64String,true); //解码json_encode
            return  $jsonArr;  
        }   
    }
    
    /**解析微信支付回调回来的地址
    *回调的回来的地址http://shg.jiaju.com/Pay/paypal/id/43/reback/ODgwN3w5N3wyODA0fDR8NDM=这个样子
    */
    private function ParseWeChatParam()
    {
        $reback=I('reback');
        if($reback)
        {
            ////参数规则tid|order_id|t_amount|pay_type|shopid
            $mVal=strrev($reback);
            $mVal=base64_decode($mVal);
            if(!empty($mVal))
            {
                $mArr=explode('|',$mVal);
                $mDat=array();
                $mDat['tid']         =$mArr[0];
                $mDat['order_id']    =$mArr[1];
                $mDat['t_amount']    =$mArr[2];
                $mDat['pay_type']    =$mArr[3];
                $mDat['id']          =$mArr[4];
                return $mDat; 
            }
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    

      
}