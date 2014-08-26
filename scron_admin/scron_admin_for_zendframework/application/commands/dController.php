<?php

require_once 'components/commandController.php';

class dController extends commandController{
    
    /**
     * 测试redis、mongo
     * @example php d:\wamp\www\_zdtone\analytics\bx_analytics\public\cmd.php test d >> d:\d_test_20140526.log
     * @param type $params
     */
    public function testAction($params){
        echo "\n===[start]=====" . date('Y-m-d H:i:s') . "=={d, test}===\n";
        
        Model_Redis::getRedis();
//        $m = new Model_Mongo_Statistics();
        echo $a;
        echo 'test' . "\n";
        sleep(100);
        
        echo "\n===[end]=====" . date('Y-m-d H:i:s') . "==\n";
    }

    /**
     * 测试redis、mongo
     * @example php d:\wamp\www\_zdtone\analytics\bx_analytics\public\cmd.php test d >> d:\d_test_20140526.log
     * @param type $params
     */
    public function testaAction($params){
        echo "\n===[start]=====" . date('Y-m-d H:i:s') . "=={d, testa}===\n";
        
        Model_Redis::getRedis();
//        $m = new Model_Mongo_Statistics();
//        echo $a;
        echo 'testa' . "\n";
        sleep(100);
        
        echo "\n===[end]=====" . date('Y-m-d H:i:s') . "==\n";
    }

    /**
     * 定时任务入口
     * @example php d:\wamp\www\_zdtone\analytics\bx_analytics\public\cmd.php cron d
     * @param type $params
     */
    public function cronAction($params){
        echo "\n===[start]=====" . date('Y-m-d H:i:s') . "=={d, cron}===\n";
        
        $modelCron = Model_Cron::model();
        $crons = $modelCron->getCronsFromCache();
        foreach ($crons as $cron) {
            if($cron['active'] != 1){   //判断状态
                continue;
            }
            if($cron['timeout'] > 0 && (time() - $cron['runAt'] > $cron['timeout'])){
                //超时处理
            }
            $cronTime = $cron['mhdmd'];
            if (You_Application_Utils_CronEntry::timeOk(trim($cronTime))) {
                $cmd = $cron['command'];
                $logFileName = $cron['logFile'];
                $exec = Model_Cron::cronHandle($cmd, $modelCron->getLogFile($cron));
                $modelCron->setRunAt($cron['cronId'], time());
                echo "\n" . 'exec -- ' . $exec;
            }
        }
        
        echo "\n===[end]=====" . date('Y-m-d H:i:s') . "==\n";
    }
    
    public function pcloseAction($param) {
        $stime = time();
        while (1) {
            if (time() - $stime >= 55) {
                break;
            }
            if(Model_Cron::model()->inPclosePidSetsCount() > 0){
                $process = Model_Cron::model()->getProcess();
                while ($pid = Model_Cron::model()->rpopAPclosePid()) {
                    foreach ($process as $item) {
                        if ($pid == $item[2]) {
                            Model_Cron::model()->pclose($pid);
                            echo "\n" . 'exec -- ' . "pclose($pid)" . (isset($item[8]) ? "[$item[8]]" : '');
                        }
                    }
                }
            }
            sleep(5);
        }
    }
}