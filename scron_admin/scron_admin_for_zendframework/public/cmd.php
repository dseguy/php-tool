<?php
/** @todo online 上线时需要修改的参数 **/
/**
 * 命令台工具
 * 第一个参数是action       （如下面的test）
 * 第二个参数是controller   （如下面的d）
 * 第三个及后面的参数是附加参数 以‘-’开头作为参数名，‘=’后面的是参数值      （如下面的-t1=1 -t2=2）
 * @example php d:\wamp\www\_zdtone\_baofeng\public\cmd.php test d -t1=1 -t2=2 >> d:\d_test_20140526.log
 * @license controller存放的目录是 APPLICATION_PATH . '/commands';
 * @author liuxiaobo
 */
if(isset($_SERVER['SERVER_ADDR'])){     //屏蔽通过http访问的用户
    die;
}


define('DS', DIRECTORY_SEPARATOR);
define('PUBLIC_DIR', dirname(__FILE__));
define('WEB_ROOT', PUBLIC_DIR . DS . '..');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
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
    realpath(APPLICATION_PATH . '/commands'),
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));
/** Zend_Application */
require_once 'Zend/Application.php';

require_once 'components/CErrorHandler.php';
$errorHandle = CErrorHandler::instance();
set_error_handler(array($errorHandle, 'log_error'));
set_exception_handler(array($errorHandle, 'log_exception'));
register_shutdown_function(array($errorHandle, 'emailError'));
error_reporting( E_ALL );

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$parm = array();
foreach ($argv as $item) {
    if(strpos($item, '-') === 0){
        $parm[substr($item, 1, strpos($item, '=')-1)] = substr($item, strpos($item, '=')+1);
    }
}

$application->bootstrap();

$r = new Zend_Controller_Request_Simple($argv[1], $argv[2], 'default', $parm);

$c = $r->getControllerName();
$cn = $c . ucfirst($r->getControllerKey());
$a = $r->getActionName();
$an = $a . ucfirst($r->getActionKey());


$controller = new $cn();
$controller->$an($parm);

