<?php
/*ApiBaseAction公共类
*增加签名验证
*/
class ApiBaseAction extends BaseAction 
{
    /*构造函数*/
    public function __construct() 
    {
        parent::__construct();
    }
    
    /*在此方法验证签名*/
    function _initialize() 
    {
        $MyGet=I('get.');//得到请求的URL参数
        $sign = genSign(genString($MyGet));//参数签名
        //如果签名不正确输出错误
        if (!verify(genString($MyGet), I('sign'))) 
         {
            $BaseM=D('Base');
            $BaseM->setErrCode(3001);
            $outPut['data']=array();
            $outPut['errCode']=$BaseM->getErrCode();
            $outPut['errMsg']=$BaseM->getErrMsg();
            //self::echoJson($outPut);内部接口取消签名
         }
    }
}