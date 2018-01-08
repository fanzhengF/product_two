<?php
define('THINK_PATH', './ThinkPHP/');
define('APP_NAME', 'App');
define('APP_PATH', './App/');
define('APP_DEBUG',true);
define('SITE_PATH', getcwd());//网站当前路径
define("RUNTIME_PATH", $_SERVER['SINASRV_CACHE_DIR']); //@todo 以后如何关闭缓存
define('DS',DIRECTORY_SEPARATOR);
define("WEB_ROOT", dirname(__FILE__) . DS);
define('APP_PUBLIC_PATH','/Public');


/*
$redis = new Redis();
$redis->connect("127.0.0.1","6379"); 
//存储一个 值
$redis->set("say","hello223232323 world");
echo $redis->get("say");    

exit;
*/
//redis的php.ini方式session共享
// ini_set("session.save_handler", "redis");
// ini_set("session.save_path", "tcp://".$_SERVER['SINASRV_REDIS_HOST']);
//echo THINK_PATH;exit;
$_SERVER['SCRIPT_NAME'] = '/index.php';
//var_dump($_SERVER);exit;
require THINK_PATH . "ThinkPHP.php";
?>
