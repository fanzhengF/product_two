<?php

return array(
    //'配置项'=>'配置值'
    'URL_MODEL' => '2', //URL模式
    'APP_GROUP_LIST' => 'Home,Admin,Cron,Api,Wechat', //项目分组设定
    'LOAD_EXT_CONFIG' => 'access', // 加载扩展配置文件
    'DEFAULT_GROUP' => 'Home', //默认分组
    //分布式数据库配置定义
    'DB_DEPLOY_TYPE' => 1,
    'DB_RW_SEPARATE' => true, //默认第一个数据库配置是主服务器
    'DB_TYPE' => 'mysql', //分布式数据库类型必须相同
    /*
    'DB_HOST' => $_SERVER['SINASRV_DB_HOST'] . ',' . $_SERVER['SINASRV_DB_HOST_R'],
    'DB_NAME' => $_SERVER['SINASRV_DB_NAME'] . ',' . $_SERVER['SINASRV_DB_NAME_R'], //如果相同可以不用定义多个
    'DB_USER' => $_SERVER['SINASRV_DB_USER'] . ',' . $_SERVER['SINASRV_DB_USER_R'],
    'DB_PWD' => $_SERVER['SINASRV_DB_PASS'] . ',' . $_SERVER['SINASRV_DB_PASS_R'],
    'DB_PORT' => $_SERVER['SINASRV_DB_PORT'] . ',' . $_SERVER['SINASRV_DB_PORT_R'],  */
    
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'cumtb_yjs', //如果相同可以不用定义多个
    'DB_USER' => 'root',
    'DB_PWD' => 'root',
    'DB_PORT' => '3306',
    'DB_PREFIX' => 'cumtb_',
    /* 数据缓存设置 */
    'DATA_CACHE_TIME' => 180, // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS' => true, // 数据缓存是否压缩缓存
    'DATA_CACHE_PREFIX' => $_SERVER['SINASRV_MEMCACHED_KEY_PREFIX'], // 缓存前缀
    'DATA_CACHE_TYPE' => 'File', // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'MEMCACHE_HOST' => $_SERVER['SINASRV_MEMCACHED_SERVERS'] . ',' . $_SERVER['SINASRV_MEMCACHED_MIRROR_SERVERS'],
    
    /*
    //SessionRedis类方式session共享设置
    'SESSION_PREFIX'  => 'PHPREDIS_SESSION:SHG.JIAJU.COM:',
    'SESSION_TYPE'    => 'Redis',
    'SESSION_OPTIONS' => array(
        'name'=>'PHPSESSID',
        ),
    'SESSION_REDIS_HOST'=>$_SERVER['SINASRV_REDIS_HOST'] . ',' . $_SERVER['SINASRV_REDIS_HOST_R'],
  */
    'LOG_RECORD' => false, // 记录日志
    //默认错误跳转对应的模板文件
    'TMPL_ACTION_ERROR' => '../Admin:Common:show',
    //默认成功跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => '../Admin:Common:show',
    'TMPL_CACHE_ON' => false,
    'URL_CASE_INSENSITIVE' => true,
    'SESSION_AUTO_START' => true, //是否开启session
    'API_PRE_KEY' => 'XxZzBbQq123', //api接口前置加密key
    'API_SUF_KEY' => 'AaBbCc123', //api接口后置加密key
    'API_HASH_METHOD' => 'sha256', //api接口加密方法
    'MD5_KEY'=>'JKLM',
    'HOME_NAME' => '中国矿业大学研究院（北京）研究生院',
/*
    'GZ_DOMAIN' => 'http://www.7gz.com/', //抢工长域名
    'MOBILE_SITE_DOMAIN' => 'http://shg.jiaju.com',//闪购页面
    'BASE_PIC_URL' =>'http://src.leju.com/imp/imp/deal/',
    'SHG_PAYPAL_URL' =>'http://www.7gz.com/Apibusiness/FlashSaleOrder/paypal',//下单页面第一步请求
    'SHG_TRANSORDER_URL' =>'http://www.7gz.com/Apibusiness/FlashSaleOrder/getTransOrder',//下单页面第二步请求
    'FLASHSALEORDER_GETPAYLIST_URL'=>'http://www.7gz.com/Apibusiness/FlashSaleOrder/getPaylist',//获取获取闪购订单列表
    'FLASHSALEORDER_GETSUPPLIERBALANCELIST_URL'=>'http://www.7gz.com/Apibusiness/FlashSaleOrder/getSupplierBalanceList',//获取闪购供应商结算列表
    'SHG_LOCAL_STATION_URL'=>'http://www.7gz.com/Apibusiness/FlashSaleOrder/city',//供应商所属地方站
*/
);
