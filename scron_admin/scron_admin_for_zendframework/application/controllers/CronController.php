<?php

require_once 'Zend/Controller/Action.php';

class CronController extends Zend_Controller_Action {

    public $TREE_RESOURCE = '';
    public $passwd = 'ff1ccf57e98c817df1efcd9fe44a8aeb';

    public function init() {
        if(!$this->access()){
            die;
        }
        $this->initView();
        $this->_helper->layout->setLayout('bootstrap');
    }

    public function access(){
        $preKey = 'cron_session';
        $sessionId = session_id();
        $key = $preKey . ':' . $sessionId;
        $redis = Model_Redis::getRedis();
        if($redis->exists($key)){
            return TRUE;
        }
        if(isset($_GET['passwd']) && ($this->passwd == md5($_GET['passwd']))){
            $saveOk = $redis->set($key, 1, 60*20);
            return $saveOk ? TRUE : FALSE;
        }
        return false;
    }

    public function noLayout() {
        $this->_helper->layout->disableLayout();
    }

    public function indexAction() {
        $this->view->headTitle('定时任务管理', Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);
        $modelCron = Model_Cron::model();
        $crons = $modelCron->getCronsFromCache();
        $cmdList = [];
        foreach ($crons as $k => $v) {
            $crons[$k]['runAt'] = $v['runAt']>0 ? date('Y-m-d H:i:s', $v['runAt']) : '--';
            $crons[$k]['logFile'] = $modelCron->getLogFile($v);
            $cmdList[$v['command']] = $v['task'];
        }
        $m = Model_Cron::model()->getProcess();
        foreach ($m as $k => $item) {
            $m[$k]['title'] = isset($cmdList[$item['cmd']]) ? $cmdList[$item['cmd']] : '';
        }
        $this->view->ps = json_encode($m);
        $this->view->crons = json_encode($crons);
        $this->view->logDir = 'commandlog/' . Model_Cron::$logDir;
        if('ajax-reList' == $this->getRequest()->getParam('ajax')){
            $res = array(
                'code' => 1,
                'data' => $crons,
                'ps' => $m,
            );
            die(json_encode($res));
        }
    }

    public function activeAction() {
        $id = (int)$this->getRequest()->getParam('id');
        $opt = (int)$this->getRequest()->getParam('opt');
        $active = $opt === 1 ? 1 : 0;
        $modelCron = Model_Cron::model();
        $saveOk = $modelCron->save(array('active'=>$active), 'cronId = '.$id);
        $json = array('code'=>$saveOk ? 1 : 0);
        die(json_encode($json));
    }

    public function createAction() {
        $data = array();
        $where = NULL;
        $model = Model_Cron::model();
        $id = (int)$this->getRequest()->getParam('cronId');
        $title = '创建定时任务';
        if($id){
            $title = '修改定时任务';
            $data = $model->findByPk($id);
            $where = 'cronId=' . $id;
        }
        if($this->getRequest()->isPost()){
            $post = $this->getRequest()->getParams();
            $data = $post['cron'];
            $data['active'] = 0;        //修改信息后默认变为未激活状态
            $saveOk = $model->save($data, $where);
            if($saveOk){
                $this->_redirect('/cron/index');
                die();
            }
        }
        $this->view->headTitle($title, Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);
        $this->view->data = $data;
        $this->view->isNewRecord = $id ? 0 : 1;
    }

    public function deleteAction() {
        $id = (int)$this->getRequest()->getParam('id');
        $modelCron = Model_Cron::model();
        $saveOk = $modelCron->deleteByPk($id);
        $json = array('code'=>$saveOk ? 1 : 0);
        die(json_encode($json));
    }

    public function pcloseAction() {
        $id = (int)$this->getRequest()->getParam('id');
        Model_Cron::model()->lpushAPclosePid($id);
        $json = array('code'=>1);
        die(json_encode($json));
    }

}
