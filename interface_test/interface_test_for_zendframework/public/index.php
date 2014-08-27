<?php

define('DS', DIRECTORY_SEPARATOR);
define('PUBLIC_DIR', dirname(__FILE__));
define('WEB_ROOT', PUBLIC_DIR . DS . '..');
//session_start(); //开启session
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('INI_FILE', APPLICATION_PATH . '/configs/application.ini');


// 定义环境变量
define('APPLICATION_ENV_DEV', 'development');       //【开发环境】
define('APPLICATION_ENV_TEST', 'testing');          //【测试环境】
define('APPLICATION_ENV_PRO', 'production');        //【生产环境】

switch (TRUE) {
    case file_exists(WEB_ROOT . DS . '..' . DS . '__test20140701.lock'):    //根据此文件判断是否是【测试环境】
        $applicationEnv = APPLICATION_ENV_TEST;
        break;
    case file_exists(WEB_ROOT . DS . '..' . DS . '__dev20140701.lock'):     //根据此文件判断是否是【开发环境】
        $applicationEnv = APPLICATION_ENV_DEV;
        break;
    default:
        $applicationEnv = APPLICATION_ENV_PRO;      //默认是【生产环境】
        break;
}

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', ($applicationEnv ? $applicationEnv : APPLICATION_ENV_PRO));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

require_once 'Zend/Session.php';
Zend_Session::start();

//判断是否登录

if(empty($_SESSION["user"])){//没有登录
	//echo $_SERVER['REQUEST_URI'];
	if($_SERVER['REQUEST_URI'] != "/user/login" && $_SERVER['REQUEST_URI'] != "/user/loginyz" &&  $_SERVER['REQUEST_URI'] != "/user/register" &&  $_SERVER['REQUEST_URI'] != "/index/forget"){
		//var_dump($_SERVER['REQUEST_URI']);
		//exit;
		header("location:/user/login");
		//var_dump($_GET);
		exit(0);
	}
}else{
	
	//登录以后  判断是否在index,user,game模块，如果不再则判断session中是否有权限
	//还没做
}

$application->bootstrap()->run();

