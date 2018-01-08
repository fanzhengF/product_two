<?php
/**
 * 具体角色筛选,查找配置
 */
return array(
    'ACCESS_VIEW' => array(
        1 => array(1=>'超级管理员',2=>'机构管理',3=>'学生管理',4=>'教师管理',5=>'校外管理',6=>'会议室管理',7=>'会议管理',8=>'其他'),//supper
        2 => array(3=>'供应商',4=>'分公司',5=>'实体店'),//platform
        3 => array(4=>'分公司',5=>'实体店'),//supplier
        4 => array(5=>'实体店'),//company
        5 => array(),//shop
        6 => array(2=>'机构管理',3=>'学生管理',4=>'教师管理',5=>'校外管理',6=>'会议室管理',7=>'会议管理',8=>'其他'),//
        ),
    'ACCESS_SEARCH' => array(
        //订单明细
        'order_index' =>array(
            'select' => array(
                    1 => array(),
                    2 => array(),
                    3 => array('supplier_id'=>'%s'),
                    4 => array('company_id'=>'%s'),
                    5 => array('shop_id'=>'%s')
                ),
            //取第一个键值出来,可以判定是那个级别显示
            'search' => array(
                    1 => array('supplier'=>1,'company'=>1,'shop'=>1),
                    2 => array('supplier'=>1,'company'=>1,'shop'=>1),
                    3 => array('company'=>1,'supplier'=>0,'shop'=>1),
                    4 => array('shop'=>1,'supplier'=>0,'company'=>0),
                    5 => array('supplier'=>0,'company'=>0,'shop'=>0)
                ),
        ),
        //订单统计
        'order_statistics' =>array(
            'select' => array(
                    1 => array(),
                    2 => array(),
                    3 => array('supplier_id'=>'%s'),
                    4 => array('company_id'=>'%s'),
                    5 => array('shop_id'=>'%s')
                ),
            'search' => array(
                    1 => array('supplier'=>1,'company'=>1,'shop'=>1),
                    2 => array('supplier'=>1,'company'=>1,'shop'=>1),
                    3 => array('supplier'=>0,'company'=>1,'shop'=>1),
                    4 => array('supplier'=>0,'company'=>0,'shop'=>1),
                    5 => array('supplier'=>0,'company'=>0,'shop'=>0)
                ),
            'tab_view_default' => array(
                    1 => '',
                    2 => '',
                    3 => 'supplier',
                    4 => 'company',
                    5 => 'shop'
                ),
        ),
        //供应商结算
        'supplier_settlement' =>array(
            'select' => array(
                    1 => array(),
                    2 => array(),
                    3 => array('supplier_id'=>'%s'),
                    4 => array('id'=>0),
                    5 => array('id'=>0)
                ),
        ),
        //供应商优惠设置
        'supplier_sale' =>array(
            'select' => array(
                    1 => array(),
                    2 => array(),
                    3 => array('supplier_id'=>'%s'),
                    4 => array('id'=>0),
                    5 => array('id'=>0)
                ),
        ),
        //用户管理
        'access' =>array(
            'select' => array(
                    1 => array(),
                    2 => array(array('role_id'=>array('egt',3))),
                    3 => array('supplier_id'=>'%s'),
                    4 => array('company_id'=>'%s'),
                    5 => array('id'=>0)
                ),
            'insert'=>array(),//检测是否是其所属
            'update'=>array(
                    1 => 'allow',
                    2 => 'allow',
                    3 => 'supplier_id',
                    4 => 'company_id',
                    5 => 'unallow',
                ),//检测是否是其所属
            'forbidden' => array(
                    3 => 'supplier_id',
                    4 => 'company_id',
                ),
            'normal' => array(
                    4 => 'supplier_id',
                    5 => 'supplier_id,company_id',
                ),
            'delete'=>array(),//检测是否拥有删除权限
            ),
        ),

);
