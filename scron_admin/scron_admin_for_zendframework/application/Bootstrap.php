<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initAutoload() {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath' => APPLICATION_PATH));

        $moduleLoader->addResourceType('model', 'models', 'Model');
        $moduleLoader->addResourceType('helper', 'helpers', 'Application_Helpers');

        /* 实现自动加载Models下的类 */
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);
        return $moduleLoader;
    }

    function _initViewHelpers() {

        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();

        $view->doctype('XHTML1_STRICT');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
        $view->headTitle()->setSeparator(' - ');
        $view->headTitle('xxx');
    }

    public function run() {
        //加载多数据库信息
        $this->bootstrap('dbs');
        $dbs = $this->getPluginResource('dbs');
        //后台系统库
        Zend_Registry::set('system', $dbs->getDbAdapter('system'));



        $this->bootstrap('frontController');

        $this->frontController->dispatch();
    }

    /**
     * 引入Smarty 以替代原来的模板显示机制, 即设置新的view renderer!!! 
     */
//    function _initSmarty()   
//    {  
//        require_once 'Templater.php';  
//        $vr = new Zend_Controller_Action_Helper_ViewRenderer();  
//        $vr->setView(new Templater());  
//        $vr->setViewSuffix('html');    //指定模板文件的后缀  
//        Zend_Controller_Action_HelperBroker::addHelper($vr);
//    }
}
