<?php
/*
* 从接口中获取闪购供应商结算列表
* 接口/Apibusiness/FlashSaleOrder/getSupplierBalanceList
* 使用方法：
* @param int $page 页数
* @param int $page_size 每页条数
* @param int @start_up_time 更新开始时间
* @param int $end_up_time 更新结束时间
* @linkUrl http://shg.jiaju.com/Cron/ShopOrder/
*/
class ShopOrderAction extends BaseAction
{
    //拉取订单数据
    function index()
    {
        
         $param                     = array();
         $param['page']             = 1;//页数
         $param['page_size']        = 50;//每页条数
         $start_up_time             =$this->GetStartTime();
         $from                      =I('from',0,'htmlspecialchars');
         if(IS_AJAX)
         {
             $param['start_up_time']    = time()-400;//如果是后台请求的请求4分钟的数据
         }
         elseif($from=='crond')
         {
             $param['start_up_time']    = time()-600;//如果是Crond请求的更新开始时间当前时间的前10分钟     
         }
         elseif(!empty($start_up_time))
         {
            $param['start_up_time']    = $start_up_time;//手工拉取指定时间 
         }
         $param['end_up_time']      = time();//更新结束时间(当前时间)
         $OrderModel=D('ShopOrder','LogicModel');
         //拉取订单
         $orderRest=$OrderModel->OrderPayList($param);
         //拉取结算
         $BalanceList=$OrderModel->BalanceList($param);
         echo json_encode($orderRest)."\n";
         echo json_encode($BalanceList)."\n";
    } 
    
    /*手工拉取订单数据
    *验证传递时间参数的正确性
    *2016-12-01 01:01:01
    */
    private function GetStartTime()
    {
        $startTime                 =I('startTime',0,'htmlspecialchars'); 
        $mTimeStamp                =strtotime($startTime);
        $mYear                     =date('Y',$mTimeStamp);
        $mCurYear                  =date('Y');
        if($mYear >= $mCurYear) 
        {
            return $mTimeStamp;
        }
        return 0;
    }
     
}