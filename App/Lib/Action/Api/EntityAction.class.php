<?php
/*
查询实体店信息
数据表：shg_user
返回:
*/
class EntityAction extends ApiBaseAction
{
    /*
    搜索供应商信息
    */
    function index()
    { 
       $param=I("request.");
       $param['role_id']=ApiShopModel::SHOP_ROLE_ID;
       $retVal=D("ApiShop")->GetSupplierCompanyEntity($param);//得到供应商的信息
       self::echoJson($retVal);      
    }
      
}