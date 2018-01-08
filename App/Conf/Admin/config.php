<?php
return array(
    'SITE_INFO' => array(
        'name' => '会议信息系统',
        ),
    'AUTH_CODE' => 'SElgVY',
    'ADMIN_AUTH_KEY' => 'adminadmin',
    'TOKEN' => array(
        'admin_marked' => 'xxx.com',
        'admin_timeout' => 3600,
        'member_marked' => 'http://' . C('WEB_HOST'),
        'member_timeout' => 3600,
    ),
    'admin_big_menu' => array(
        'Index/index' => '首页',
        'Order/statistics' => '网站',
        // 'Access/index' => '网站',
    ),
    'admin_sub_menu' => array(
        'Order/statistics' => array(


            'account_number' => array(
                'name' => '账号管理',

            ),

            'hyshenqing' => array(
                'name' => '会议申请审批',
                'Shenqing/wei' => '未审批',
                'Shenqing/yi' => '已审批'
            ),

            'bdshenqing' => array(
                'name' => '补登会议审批',
                'Bd/wei' => '未审批',
                'Bd/yi' => '已审批'
            ),


            'xfshenqing' => array(
                'name' => '学生学分审批',
                'Credit/index' => '未审批',
                'Credit/index2' => '已审批',

                //'Xf/wei' => '未审批',
                //'Xf/yi' => '已审批'
            ),



            'supplier_settlement' => array(
                'name' => '学术会议列表',
                'Meeting/index' => '会议内容查看',
                // 'Yiyou/index' => '已有会议列表',
                'Budeng/index' => '补登会议查看',
                // 'Zichou/index' => '自筹会议列表',
                //'Experience/index' => '会议心得列表',
                'Evaluate/index' => '会议评价查看',
                // 'Poster/index' => '会议海报列表',
                // 'Grade/index' => '会议等级列表',
                'Mt/index' => '会议类型查看',
                'Signup/index' => '会议报名查看',
                'Canjia/index' => '会议参加查看'
            ),
            'room' => array(
                'name' => '会议室列表',
                'Room/index' => '标准会议室列表',
                'Room/raised_index' => '自筹会议室列表',
            ),
            'access_list' => array(
                'name' => '用户管理',
                'Access/roleList' => '角色管理',
                'Access/index' => '用户管理',
                'Access/addAdmin' => '新增用户',
                 //'Access/nodeList' => '节点管理',
                //'Access/addNode' => '添加节点',
                // 'Access/addRole' => '添加角色',
            ),
            'student' => array(
                'name' => '学生管理',
                'Student/index' => '学生列表',
                'Xf/wei' => '学分列表',
                'Student/add' => '学生添加',
            ),
            'teacher' => array(
                'name' => '教师管理',
                'Teacher/index' => '教师列表',
                'Teacher/add' => '教师添加'
            ),
            'xiaowai' => array(
                'name' => '校外管理',
                'Xiaowai/index' => '校外人员列表'
            ),


            'mechanism' => array(
                'name' => '基础数据设置',
                'Mechanism/index' => '机构设置',
                'DepartmentInfo/index' => '院系设置',
                'StudentProfe/index' => '专业设置',
                'Grade/index' => '学分设置',
                'Format/index' => '海报设置',
                'Shape/index' => '会议室形状设置',
                'Facilities/index'=>'会议室设施设置'
            ),

            'focusmap' => array(
                'name'=> '系统工具',
                'Report/index' => '生成报表',
                'Index/myInfo' => '修改密码',
                'Index/index' => '',

            )
        )
    ),

    /*
     * 以下是RBAC认证配置信息
     */
    'USER_AUTH_ON' => true,
    'USER_AUTH_TYPE' => 2, // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY' => 'authId', // 用户认证SESSION标记
//    'ADMIN_AUTH_KEY' => '281978297@qq.com',
    'USER_AUTH_MODEL' => 'User', // 默认验证数据表模型
    'AUTH_PWD_ENCODER' => 'md5', // 用户认证密码加密方式encrypt
    //'USER_AUTH_GATEWAY' => '/system.php/Public/index', // 默认认证网关
    'NOT_AUTH_MODULE' => 'Public,Index', // 默认无需认证模块
    'REQUIRE_AUTH_MODULE' => '', // 默认需要认证模块
    'NOT_AUTH_ACTION' => 'ajax,ajaxSearch', // 默认无需认证操作
    'REQUIRE_AUTH_ACTION' => '', // 默认需要认证操作
    'GUEST_AUTH_ON' => false, // 是否开启游客授权访问
    'GUEST_AUTH_ID' => 0, // 游客的用户ID
    'RBAC_ROLE_TABLE' => C('DB_PREFIX') . 'role',
    'RBAC_USER_TABLE' => C('DB_PREFIX') . 'user',
    'RBAC_ACCESS_TABLE' => C('DB_PREFIX') . 'access',
    'RBAC_NODE_TABLE' => C('DB_PREFIX') . 'node',
    'URL_CASE_INSENSITIVE' => false, //关闭忽略大小写
    //默认错误跳转对应的模板文件
    'TMPL_ACTION_ERROR' => '../Admin:Common:show',
    //默认成功跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => '../Admin:Common:show',
);
