<?php
/*
获取Html5焦点图
数据表：shg_focusmap
返回:json {"url":"","src":""}
*/
class FocusMapAction extends BaseAction
{
    /*获取数据*/
    function index()
    {
        //查询数据源得到一条数据
        $BaseModel=D("Base");
        $MapArray=D("Focusmap")->field("url,src")->order('id desc')->limit(1)->find();
        $OutPut['data']=array();
        $BaseModel->setErrCode(1003);
        if($MapArray)
        {
            $OutPut['data']=$MapArray; 
            $BaseModel->setErrCode(1000); 
        }
        $OutPut['errCode']=$BaseModel->getErrCode(); 
        $OutPut['errMsg']=$BaseModel->getErrMsg();
        $this->echoJson($OutPut);
    }    
}