<?php

class Model_Cron extends Model_Abstract{
    
    public static $_models = array();
    public $_name = 'sys_cron';
    
    public static $logDir = 'cron';
    public static $logExtName = '.log';
    public static $cronCacheKey = 'sys:cron';
    public static $pcloseCronCacheKey = 'sys:cron_pclose';

    public static function model($className = __CLASS__) {
        $model = null;
        if (isset(self::$_models[$className]))
            $model = self::$_models[$className];
        else {
            $model = self::$_models[$className] = new $className();
        }
        return $model;
    }

    public function getCronsFromCache(){
        $key = self::$cronCacheKey;
        $redis = Model_Redis::getRedis();
        if(!$redis->exists($key)){
            $this->reloadCronRedis();
        }
        return unserialize($redis->get($key));
    }
    
    public function reloadCronRedis(){
        $key = self::$cronCacheKey;
        $redis = Model_Redis::getRedis();
        $crons = $this->getCrons();
        return $redis->set($key, serialize($crons));
    }
    
    public function getLogFile($model){
        return $model['logFile'] ? $model['logFile'] : $model['cronId'];
    }

    public function getCrons(){
        $sql = 'SELECT * FROM `sys_cron`';
        $data = $this->getAdapter()->fetchAll($sql);
        return $data;
        
        $crons = array(
            array(
                'cronId' => '11',
                'task' => '测试',
                'active' => '1',
                'mhdmd' => '*/3 * * * *',
                'command' => 'php D:\wamp\www\_zdtone\analytics\bx_analytics\public\cmd.php test d',
                'runAt' => '1373821262',
                'timeout' => '0',
                'logFile' => '',
            ),
            array(
                'cronId' => '12',
                'task' => '测试a',
                'active' => '0',
                'mhdmd' => '*/2 * * * *',
                'command' => 'php D:\wamp\www\_zdtone\analytics\bx_analytics\public\cmd.php testa d',
                'runAt' => '1373821262',
                'timeout' => '0',
                'logFile' => '',
            ),
        );
        
        return $crons;
    }

    public static function cronHandle($cmd, $logFileName) {
        $time = time();
        $sys = (PATH_SEPARATOR == ':') ? 'Unix' : 'Win';
        $logPath = self::getDatePath($time);
        if (!is_dir($logPath)) {
            @mkdir($logPath, 0777, TRUE);
        }
        $logFile = self::getLogDateFile($logFileName, $time);
        $exec = $cmd . " >> " . $logFile . " 2>&1 &";
        if ($sys == 'Win') {
            $unixPhp = '/usr/local/php/bin/php /var/www/analytics/analytics/bx_analytics/public/cmd.php';
            $winPhp = 'php D:\wamp\www\_zdtone\analytics\bx_analytics\public\cmd.php';
            $exec = str_replace($unixPhp, $winPhp, $exec);
            pclose(popen("start /B " . $exec, "r"));
        } else {
            system('nohup ' . $exec);
        }
        
        return $exec;
    }
    
    public static function getLogRootPath(){
        return PUBLIC_DIR . DS . 'commandlog';
    }
    
    public static function getDatePath($time){
        return self::getLogRootPath() . DS . self::$logDir . DS . date('Ymd', $time);
    }
    
    public static function getLogDateFile($logFile, $time){
        return self::getDatePath($time) . DS . $logFile . self::$logExtName;
    }
    
    public function setRunAt($id, $time){
        return $this->save(array('runAt'=>$time), 'cronId = ' . $id);
    }
    
    /**
     * 保存
     * 如果$where有值，则是修改数据；如果$where为null，则是插入数据；
     * 注意：为了修改时更新缓存，所有修改数据需要调用这个函数
     * @param type $data
     * @param type $where
     */
    public function save($data, $where = NULL){
//        return true;
        $saveOk = $where === NULL ? $this->insert($data) : $this->update($data, $where);
        if($saveOk){
            $this->reloadCronRedis();
        }
        return $saveOk;
    }
    
    public function findByPk($pk){
        $sql = 'SELECT * FROM `sys_cron` WHERE cronId='.(int)$pk;
        return $this->getAdapter()->fetchRow($sql);
    }
    
    public function deleteByPk($pk){
        $delOk = $this->delete('cronId='.$pk);
        $this->reloadCronRedis();
        return $delOk;
    }
    
    public function rpopAPclosePid() {
        $key = self::$pcloseCronCacheKey;
        return Model_Redis::getRedis()->sPop($key);
    }
    
    public function lpushAPclosePid($pid) {
        $key = self::$pcloseCronCacheKey;
        return Model_Redis::getRedis()->sAdd($key, $pid);
    }
    
    public function isInPclosePidSets($pid) {
        $key = self::$pcloseCronCacheKey;
        return Model_Redis::getRedis()->sIsMember($key, $pid);
    }
    
    public function inPclosePidSetsCount() {
        $key = self::$pcloseCronCacheKey;
        return Model_Redis::getRedis()->sCard($key);
    }
    
    public function getProcess() {
        $sys = (PATH_SEPARATOR == ':') ? 'Unix' : 'Win';
        $m = [];
        if ($sys == 'Unix') {
            $cmd = 'ps -ef | grep "/usr/local/php/bin/php /var/www/analytics/analytics/bx_analytics/public/cmd.php"';
            $ps = [];
            exec($cmd, $ps);
            foreach ($ps as $p) {
                $res = [];
                preg_match('/(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)/', $p, $res);
                $res['inPcloseSets'] = 0;
                if(isset($res[8]) && strpos($res[8], '/usr/local/php/bin/php') === 0){
                    if($this->isInPclosePidSets($res[2])){
                        $res['inPcloseSets'] = 1;
                    }
                    $res['pid'] = $res[2];
                    $m[] = $res;
                }
            }
        }else{
            $ps = [];
            exec("tasklist /V /FO CSV /NH", $ps);
            foreach ($ps as $p) {
                $p = mb_convert_encoding($p, 'utf-8', 'gbk');
                $p = substr($p, 1, -1);
                $res = explode('","', $p);
                $res['inPcloseSets'] = 0;
                if(isset($res[0]) && strpos($res[0], 'php') === 0){
                    if($this->isInPclosePidSets($res[1])){
                        $res['inPcloseSets'] = 1;
                    }
                    $res['pid'] = $res[1];
                    $m[] = $res;
                }
            }
        }
        return $m;
    }
    
    public function pclose($pid) {
        $sys = (PATH_SEPARATOR == ':') ? 'Unix' : 'Win';
        if ($sys == 'Unix') {
            $cmd = 'kill '.$pid;
            $logFile = Model_Cron::cronHandle($cmd, 'kill_pid');
        } else {
            $cmd = 'taskkill /pid '.$pid;
            $logFile = Model_Cron::cronHandle($cmd, 'kill_pid');
        }
        return TRUE;
    }
    
}