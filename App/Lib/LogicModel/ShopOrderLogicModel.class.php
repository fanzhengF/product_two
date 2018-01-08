<?php
    /**
     * 从接口中获取支付的订单数据和获取闪购供应商结算列表
     * 接口/Apibusiness/FlashSaleOrder/getPaylist
     * 接口/Apibusiness/FlashSaleOrder/getSupplierBalanceList
     */
set_time_limit(0);
ini_set('max_execution_time', '0');
ini_set('memory_limit','1024M');
class ShopOrderLogicModel extends Model 
{
    protected $tableName='shop_order';
    
    function __construct()
    {
        parent::__construct();   
    }
    /**
    * 下单类型
    * return array
    */
    function GetPayType()
    {
        $array=array(3=>'银联手机',4=>'微信支付',6=>'支付宝');
        return  $array; 
    }
    
    
    /**执行操作得到订单数据
    *@param array
    *@return array
    */
    function OrderPayList($param)
    {
        /**请求接口*/
        $pageNum=$param['page'];
        $retDataList=array();
        $getRest=$this->getPaylist($param['page'],$param['page_size'],$param['start_up_time'],$param['end_up_time']);       
        if($getRest)
        {
            /**把数据写入到库里面*/
            $retDataList=$this->SavePayListToTable($getRest);
            $total_count=0;
            if(isset($getRest['data']['total_count']))
            {
                $total_count=$getRest['data']['total_count'];//获得数据总数
            }
            $totalNum=$param['page_size'];
            $pageNum++;
            /**判断这一次有没有拉完，没有继续*/
            while($totalNum < $total_count)
            {
                    $getRest=$this->getPaylist($pageNum,$param['page_size'],$param['start_up_time'],$param['end_up_time']);
                    $datList=$this->SavePayListToTable($getRest);
                    if($datList)
                    {
                        $retDataList=array_merge($retDataList,$datList);   
                    }
                    $totalNum+=$param['page_size'];
                    $pageNum++;                       
            }
            return $retDataList;
        }
    }
    
    /**执行操作得供应商结算信息
    *@param array
    *@return array
    */
    function BalanceList($param)
    {
        $pageNum=$param['page'];
        $retDataList=array();
        /**请求接口*/
        $getRest=$this->getSupplierBalanceList($param['page'],$param['page_size'],$param['start_up_time'],$param['end_up_time']);       
        if($getRest)
        {
            $retDataList=$this->SaveSupplierBalanceList($getRest);
            $total_count=0;
            if(isset($getRest['data']['total_count']))
            {
                $total_count=$getRest['data']['total_count'];//获得数据总数
            }
            $totalNum=$param['page_size'];
            $pageNum++; 
            while($totalNum < $total_count)
            {
                  $getRest=$this->getSupplierBalanceList($pageNum,$param['page_size'],$param['start_up_time'],$param['end_up_time']);
                  $datList=$this->SaveSupplierBalanceList($getRest);
                  if($datList)
                  {
                        $retDataList=array_merge($retDataList,$datList);   
                  }
                  $totalNum+=$param['page_size'];
                  $pageNum++;                       
            } 
        }
        return $retDataList;
       
    }
    
    
    /**
     * 从接口中获取支付的订单数据
     * 接口/Apibusiness/FlashSaleOrder/getPaylist
     * 使用方法：
     * @param int $page 页数
     * @param int $page_size 每页条数
     * @param int @start_up_time 更新开始时间
     * @param int $end_up_time 更新结束时间
     * @return *@接口数据格式
     {
        "errCode": 1000, 
        "errMsg": "成功", 
        "data": {
        "list": [
          {
            "id": "6", 
            "city": "bj", 
            "supplier_id": "1", 
            "supplier_name": "supplier_name", 
            "order_sn": "16101911131392769172", 
            "pay_type": "2", 
            "pay_status": "1", 
            "pay_time": "1477899174", 
            "rate": "0.10", 
            "total_amount": "1000.00", 
            "order_amount": "900.00", 
            "supplier_amount": "810.00", 
            "ping_amount": "90.00", 
            "create_time": "1477899118", 
            "tax_rate": "3.42", 
            "pay_service": "chinapay", 
            "p_order_sn": "A1012016110110481001827065932365", 
            "tax_type": "POS", 
            "supplier_status": "0", 
            "ping_status": "0", 
            "up_time": "1477969816", 
            "mobile": "13666666", 
            "buyer_name": "a", 
            "company_id": "0", 
            "entity_id": "0", 
            "city_name": "北京", 
            "pay_type_str": "pos"
          }, 
        ], 
        "total_count": "6", 
        "page": 1, 
        "page_size": 2
      }
    }
     */
     function getPaylist($page,$page_size,$start_up_time,$end_up_time)
     {
        $page           =empty($page)           ? 1 : $page; //默认为第一页
        $page_size      =empty($page_size)      ? 10 : $page_size;//默认10条
        $start_up_time  =empty($start_up_time)  ? (time()-3600) : $start_up_time;//上一个小时
        $end_up_time   =empty($end_up_time)     ? time() : $end_up_time;
        $Params=array();
        $Params['page']             = $page;
        $Params['page_size']        = $page_size;
        $Params['start_up_time']    = $start_up_time; 
        $Params['end_up_time']      = $end_up_time;
        $ApiUrl=C('FLASHSALEORDER_GETPAYLIST_URL');
        $getResp=Request::post($ApiUrl,$Params,100,true); 
        return $getResp;
     }
     
     /*把获取的订单数据保存到订单表中
     * param:array $data接口返回的数据
     */
     function SavePayListToTable($data)
     {
         $retDat=array();
         if($data['errCode']==1000)//接口数据返回成功
        {
            $ResultList=$data['data']['list'];
            if($ResultList)
            {
                    foreach($ResultList as $orderKey => $orderInfo)
                    {
                        //状态,0:未支付,1:已支付
                        if($orderInfo['pay_status']!=1) continue;
                        $insertData=array();
                        $insertData['id']               =$orderInfo['id'];//订单唯一的ID
                        $insertData['trade_id']         =$orderInfo['flashsale_sn'];//家居的支付订单号
                        $insertData['supplier_id']      =$orderInfo['supplier_id'];//供应商id 
                        $insertData['company_id']       =$orderInfo['company_id'];//分公司ID
                        $insertData['shop_id']          =$orderInfo['entity_id'];//实体店Id
                        $insertData['pay_id']           =$orderInfo['p_order_sn'];//是支付中心的订单号;
                        $insertData['user_name']        =$orderInfo['buyer_name'];//客户姓名
                        $insertData['user_tel']         =$orderInfo['mobile'];//客户手机
                        $insertData['total_price']      =$orderInfo['total_amount'];//消费总额
                        $insertData['really_price']     =$orderInfo['order_amount'];//实收金额
                        $insertData['sale_type']        =0;//优惠类型
                        $insertData['sale_rule']        =0;//优惠测略
                        $insertData['platform_rate']    =$orderInfo['rate'];//平台扣点
                        $insertData['platform_price']   =$orderInfo['ping_amount'];//平台结算金额
                        $insertData['supplier_price']   =$orderInfo['supplier_amount'];//供应商结算金额
                        $insertData['pay_type']         =$orderInfo['pay_type'];//支付渠道
                        $insertData['pay_time']         =$orderInfo['pay_time'];//交易时间
                        //首先插入数据,插入失败，则更新数据
                        $retMsg='';
                        try
                        {
                            $this->startTrans();
                            $lastId=$this->add($insertData);
                            if($lastId)
                            {
                                 $retMsg='order_add_'.$lastId;//添加是否成功 
                                 $this->commit();//提交事务  
                            }
                            elseif(empty($lastId))
                            {
                                $this->rollback();//回滚事务
                                $lastId=$this->save($insertData);//更新数据
                                $retMsg='order_update_'.$lastId;//是否更新成功
                            }     
                        }
                        catch (Exception $e){ } 
                        $retDat[$orderInfo['id']]=$retMsg; 
                    }
              //更新供应商的优惠类型
              $this->UpdateOrderInfoFromOrdering();
            }
            else 
            {
                $retDat=$data; 
            }     
              
        }
        else 
        {
          $retDat=$data;
        }
        return   $retDat;    
     }
     
     
     
     /**
     * 从接口中获取闪购供应商结算列表
     * 接口/Apibusiness/FlashSaleOrder/getSupplierBalanceList
     * 使用方法：
     * @param int $page 页数
     * @param int $page_size 每页条数
     * @param int @start_up_time 更新开始时间
     * @param int $end_up_time 更新结束时间
     * @返回数据字段说明
     * @|id | int | 结算id |
     * @|pici_id | int | 结算批次  |
     * @| sn | string | 流水号 |
     * @|p_pay_sn | string | 流水号（支付中心） |
     * @|push_result | stirng | 打款状态描述 |
     * @|status | int | 打款状态 -1初始化，1打款中，2打款推送失败，3打款回调失败，4打款成功|
     * @|success_time | int | 打款成功时间 |
     * @|amount | float | 打款金额 |
     * @|account | string | 银行账号 |
     * @|account_name | string | 账户姓名 |
     * @|bank_name | string | 开户行 |
     * @|bank_branch | string | 开户行支行 |
     * @|bank_province | string | 省 |
     * @|bank_city |string | 市 |
     * @|account_type | int | 账户类型 1对公,2对私 |
     * @|remit_type | int | 打款类型  1分账，2汇退,3联退|
     * @|is_repeat | int | 是否再次打款 |
     * @|merge_num | int | 合并笔数 |
     * @|add_time | int | 创建时间 |
     * @|up_time | int | 更新时间 |
     * @|supplier_id | int | 供应商id |
     * @return 
     * @{
          "errCode": 1000,
          "errMsg": "成功",
          "data": {
            "list": [
              {
                "id": "54700",
                "pici_id": "20161154700",
                "sn": "16110314102710127318",
                "p_pay_sn": "A16110314102710127300",
                "push_result": "",
                "status": "4",
                "success_time": "0",
                "amount": "3700.00",
                "account": "123456789",
                "account_name": "闪购供应商",
                "bank_name": "招商银行",
                "bank_branch": "大望路支行",
                "bank_province": "北京",
                "bank_city": "北京",
                "account_type": "2",
                "remit_type": "1",
                "is_repeat": "-1",
                "merge_num": "2",
                "add_time": "1478153427",
                "up_time": "1478153443",
                "supplier_id": "1"
              }
            ],
            "total_count": "2",
            "page": 1,
            "page_size": 20
          }
        }
     */
     
    function  getSupplierBalanceList($page,$page_size,$start_up_time,$end_up_time)
    {
        $page           =empty($page)           ? 1 : $page; //默认为第一页
        $page_size      =empty($page_size)      ? 10 : $page_size;//默认10条
        $start_up_time  =empty($start_up_time)  ? (time()-3600) : $start_up_time;//上一个小时
        $end_up_time   =empty($end_up_time)    ? time() : $end_up_time;
        
        $Params=array();
        $Params['page']             = $page;
        $Params['page_size']        = $page_size;
        $Params['start_up_time']    = $start_up_time; 
        $Params['end_up_time']      = $end_up_time;
        $ApiUrl=C('FLASHSALEORDER_GETSUPPLIERBALANCELIST_URL');//对接接口url
        $getResp=Request::post($ApiUrl,$Params,60,true);  
        return  $getResp; 
     }
     
     /**把从接口获取的供应商信息保存到数据表中
     *@param array 从接口获取到的结算信息
     */
     
     function SaveSupplierBalanceList($data)
     {
        $retDat=array();
        if($data['errCode']==1000)//接口数据返回成功
        {
            $ResultList=$data['data']['list'];
            $supplierIds=array();
            if($ResultList)
            {
                foreach($ResultList as $balanRow)
                {
                    $settlement=array();
                    $settlement['id']                   =$balanRow['id'];//主键ID 
                    $settlement['supplier_id']          =$balanRow['supplier_id'];//供应商ID 
                    //$settlement['supplier_name']        ='';//供应商名称 
                    $settlement['serial_number']        =$balanRow['sn'];//收款流水号
                    $settlement['collect_money']        =$balanRow['amount'];//收入金额
                    $settlement['collect_money_time']   =$balanRow['success_time'];//打款成功时间
                    $settlement['pici_id']              =$balanRow['pici_id']; //结算批次
                    $settlement['p_pay_sn']             =$balanRow['p_pay_sn'];//流水号（支付中心）
                    $settlement['status']               =$balanRow['status'];//打款状态 -1初始化，1打款中，2打款推送失败，3打款回调失败，4打款成功
                    $this->startTrans();//开启事务
                    $lastId=M('supplier_settlement')->add($settlement);
                    $retMsg='';
                    $retMsg='settlement_add_'.$lastId;
                    if(empty($lastId))
                    { 
                        $this->rollback();//没有添加成功
                        $lastId=M('supplier_settlement')->save($settlement);
                        $retMsg='settlement_update_'.$lastId;//是否更新成功
                    }
                    else 
                    {
                      $this->commit();//添加成功
                    }
                    $retDat[$balanRow['id']]=$retMsg;
                    //保存供应商id信息
                    $supplierIds[]=$balanRow['supplier_id'];
                }
                
                //更新供应商结算表中的名称信息
                $this->UpdateSupplierSettlementName($supplierIds);
                
            }
            else 
            {
              $retDat=$data;
            }
            
        }
        else 
        {
            $retDat=$data; 
        }
        return $retDat;
        
             
     }
     
     
     /*把供应商结算信息中的供应商名称数据更新
     *@param array $supplier_ids 供应商IDs
     */
     function UpdateSupplierSettlementName($ids)
     {
            if($ids)
            {
                $where=array();
                $where['id']=array('in',''.implode(",",$ids).'');
                //按ID查询供应商信息
                $result=M('user')->field('business_name,id')->where($where)->select();   
                if($result)
                {
                    //把供应商结算表中供应商名称为空的名称更新
                    foreach($result as $row)
                    {
                        $condition=array();
                        $condition['supplier_id']   =$row['id'];
                        $condition['supplier_name'] =array('eq',' ');
                        $upData=array();
                        $upData['supplier_name']    =$row['business_name']; 
                        $ret=M('supplier_settlement')->where($condition)->save($upData); //更新供应商数据
                    }   
                }
                
            }
     
     }
     
     
     
     
     
     
     
    /*把拉过来的订单数据插入更新到数据表中*/
    function InsertDataToTable($data)
    {
        if($data)
        {
           
            $LastId=$this->add($data);
            if($LastId)
            {
              echo '插入成功';   
            } 
            else 
            {
              echo '插入失败';
            }
              
        }  
    }
    
    /*把支付类型给赋值到订单表中
    *订单表中没有优惠类型和优惠规则
    *需要通过order表中没有sale_type的订单ID去ordering表中得到数据
    *把ordering表中的数据更新到order表中
    */
    function UpdateOrderInfoFromOrdering()
    {
        $where=array();
        $where['sale_type'] =0;
        $sTime              =0;
        $eTime              =time();
        $where['pay_time']  =array('between',array(''.$sTime.'',''.$eTime.'')); 
        $ResultList         =$this->field('id')->where($where)->select();//从订单表中获得没有类型的数据
        if($ResultList)
        {
            $ids=array();
            foreach($ResultList as $row)
            {
                $ids[]=$row['id'];   
            }
            $condition['order_id']=array('in',''.implode(",",$ids).''); 
            $GetResult=M('shop_ordering')->field('order_id,sale_type,sale_rule,pay_type')->where($condition)->select();//从订单流水表中得到数据
            if($GetResult)
            {
                foreach($GetResult as $row)
                {
                    $updata=array();
                    $updata['id']       =$row['order_id'];
                    $updata['sale_type']=$row['sale_type']; 
                    $updata['sale_rule']=$row['sale_rule'];
                    $updata['pay_type'] =$row['pay_type']; 
                    $ret=$this->save($updata);//更新数据
                }   
            }
        }
    }
    
    
    
    
    
       
}
