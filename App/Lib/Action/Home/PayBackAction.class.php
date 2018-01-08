<?php
/**
*支付成功回调页面
*由支付中心回调
*传过来的参数
*/
class PayBackAction extends BaseAction
{
    /**
     * 初始化数据
     */
    function _initialize()
    {
        $this->assign('WebTitle','-抢工长闪购'); //网页标题 
        $this->param=I('request.','','htmlspecialchars');
    }
    /*支付成功页面
    *需要显示的数据支付金额,订单号,商家名称
    *继续购物按钮需要跳转到之前的下单页面
    *请求过来的地址
    *http://shg.jiaju.com/PayBack/succeed?order_amount={$order_amount}&order_sn={$order_sn}&entity_id={$entity_id}
    */   
    function succeed()
    {
        $this->assign($this->param);
        //查询商家名称
        if($this->param['entity_id'])
        {
            $rest=D('ApiShop')->GetShopUserInfo(array('role_id'=>ApiShopModel::SHOP_ROLE_ID,'id' => $this->param['entity_id']));
            $business_name='';
            if($rest)
            {
                $business_name=$rest['business_name'];    
            }
            $this->assign('business_name',$business_name);
        }
        $this->display();
        
    }
    /*支付失败页面
    *重新支付按钮需要跳转到之前的那个下单页面
    *http://shg.jiaju.com/PayBack/fail?tid={$tid}&order_id={$order_id}&t_amount={$t_amount}
    *传过来的参数有tid,order_id,t_amout
    *通过tid,order_id去shg_ording里面查到对应的商家
    *把参数json_encode() 后再base64_encode() 
    *取时把参数base64_decode() 再json_decode()得到想要的数组
    */
    function fail()
    {  
        $tid     = $this->param['tid'];        //支付id
        $order_id= $this->param['order_id'];   //订单id
        $payParam=$this->param;               
        $CreateUrl='/';
        $shop_id=1;//初始值
        if($tid && $order_id)
        {
            $where=array();
            $where['tid']       =$tid;
            $where['order_id']  =$order_id;
            $rest=M('shop_ordering')->field('shop_id,really_price')->where($where)->find();
            if($rest)
            { 
                $shop_id=$rest['shop_id']; 
                if(intval($rest['really_price'])!=intval($payParam['t_amount'])) //判断价格是否相等，如果不等重新赋值
                {
                    $payParam['t_amount']=$rest['really_price'];    
                }      
            }
        }
        //生成需要的参数
        $info=json_encode($payParam);//把参数json
        $info=base64_encode($info);
        $CreateUrl='/Pay/repay?id='.$shop_id.'&info='.$info.'';
       $this->assign('repay',$CreateUrl);
       $this->display();    
    }
    
}