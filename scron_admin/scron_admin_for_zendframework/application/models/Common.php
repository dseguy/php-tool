<?php

/**
 * 公共类
 * @author wang_peng
 *
 */
class Model_Common {

    /**
     * 获取客户端 IP
     * @param string $username
     * @param int $userid
     */
    public static function getClientIP() {
        $client_ip = "";
        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
            $client_ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }
        return $client_ip;
    }

    /**
     * 创建票据
     * @param string $username
     * @param int $userid
     */
    public function createTicket($nick_name, $user_id) {
        $encryption = new Model_Encryption();

        //票据超时时间 1个小时
        $str = $nick_name . "**" . $user_id . "**" . (time() + 60 * 60);

        return rawurlencode($encryption->authCode($str, 'ENCODE'));
    }

    /**
     * 解密用户票据
     * @param string $ticket
     */
    public function packTicket($ticket) {

        $ticket = rawurldecode($ticket);

        $encryption = new Model_Encryption();
        $decodestr = $encryption->authCode($ticket, 'DECODE');

        return $decodestr;
    }

    /**
     * 验证票据
     * @param string $ticket 票据
     * @param int $gameID 游戏编号
     */
    public function validateTicket($ticket, $gameid = 0, &$data) {



        $decodestr = self::packTicket($ticket);

        if (strlen($decodestr) > 0) {
            $decodearray = explode('**', $decodestr);


            if (count($decodearray) > 0 && $decodearray[2] > time()) {//票据在有效期范围内


                $data = array(
                    //'user_name' => $decodearray[0],
                    'user_id' => $decodearray[1],
                    'nick_name' => $decodearray[0],
                    'adult' => 1
                );
            } else {
                return -1;
            }
        } else {
            return 0;
        }

        return 1;
    }

    /**
     * 通过用户ID 获取数据所在表
     * @param int $uid
     * @param int $s
     */
    public function getTableIDByUserID($uid, $s = 16) {
        $h1 = intval(fmod($uid, $s));
        return $h1;
    }

    /**
     * 获取数据分表后ID
     * @param $u 用户名
     * @param $s 分表个数 默认为16
     */
    public function getTableID($u, $s = 16) {
        $h = sprintf("%u", crc32($u));
        $h1 = intval(fmod($h, $s));
        return $h1;
    }

    /**
     * return table name
     * @param unknown_type $basename
     * @param unknown_type $loginname
     */
    public function getTableName($basename, $loginname) {
        return $basename . $this->getTableID($loginname);
    }

    /**
     * 通过UserID获取当前用户所在数据库
     * @param unknown_type $basename
     * @param unknown_type $loginname
     */
    public function getTableNameByUserID($basename, $userid) {
        return $basename . $this->getTableIDByUserID($userid);
    }

    /**
     * 发送邮件
     * @param $sendto
     * @param $sendfrom
     * @param $subject
     * @param $content
     */
    public static function sendMail($sendto, $subject, $content) {
        try {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
            $mailTransport = new Zend_Mail_Transport_Smtp($config->You->settings->mail->smtp, array('auth' => 'login',
                'username' => $config->You->settings->mail->account,
                'password' => $config->You->settings->mail->password));
            $sendfrom = $config->You->settings->mail->account;
            $mail = new Zend_Mail('utf-8');
            $mail->addTo($sendto);
            $mail->setFrom($sendfrom);
            $mail->setSubject($subject);
            $mail->setBodyHtml($content);
            $mail->send($mailTransport);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获得密码安全系数
     * @param unknown_type $password
     * @return number
     */
    public function getPasswordCoefficient($password) {
        $basepoint = 0;
        if (strlen($password) >= 10)
            $basepoint = $basepoint + 1;
        //包含字母和数字
        if ((preg_match("([0-9]+)", $password)) && (preg_match("([a-zA-Z]+)", $password)))
            $basepoint = $basepoint + 1;

        //包含一个特殊字
        if (preg_match("([!@#$%\^\*()]+)", $password))
            $basepoint = $basepoint + 1;
        return $basepoint;
    }

    /**
     * 身份证验证
     * @param $id_card
     */
    public function isValidId($id_card) {
        $id_card = trim($id_card);
        if ((strlen($id_card) != 18 && strlen($id_card) != 15) || false !== stripos($id_card, ' ')) {
            return false;
        }

        if (strlen($id_card) == 15) {
            $id_card = self::idcard_15to18($id_card);
        }

        return self::idcard_checksum18($id_card);
    }

    // 计算身份证校验码，根据国家标准GB 11643-1999
    public function idcard_verify_number($idcard_base) {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;

        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;

        return $verify_number_list[$mod];
    }

    // 将15位身份证升级到18位 
    public function idcard_15to18($idcard) {
        if (strlen($idcard) != 15) {
            return false;
        }

        $addNum = '19';
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码 
        if (in_array(substr($idcard, 12, 3), array('996', '997', '998', '999'))) {
            $addNum = '18';
        }

        $newId = substr($idcard, 0, 6) . $addNum . substr($idcard, 6, 9);
        return $newId . self::idcard_verify_number($newId);
    }

    // 18位身份证校验码有效性检查 
    public function idcard_checksum18($idcard) {
        if (strlen($idcard) != 18) {
            return false;
        }

        $idcard_base = substr($idcard, 0, 17);

        if (self::idcard_verify_number($idcard_base) === strtoupper(substr($idcard, 17, 1))) {
            return true;
        }

        return false;
    }

    /**
     * 是否是成年
     * @param unknown_type $idcard
     */
    public function isAdult($idcard) {
        $beginDate = "";
        if (strlen($idcard) < 15) {
            return 0;
        }
        if (strlen($idcard) == 18) {
            $beginDate = substr($idcard, 6, 8);
        } else if (strlen($idcard) == 15) {
            $beginDate = "19" . substr($idcard, 6, 6);
        }

        $d1 = strtotime($beginDate);
        $d2 = time();
        $Days = round(($d2 - $d1) / 3600 / 24);
        if (($Days / 365) >= 18) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 获得一段时间内月份
     *
     * @param unknown_type $beginDate
     * @param unknown_type $endDate
     * @return string
     */
    public static function getMonth($begin_time, $end_time) {
        $begin = explode("-", $begin_time);
        $end = explode("-", $end_time);
        if ($end[0] == $begin[0]) {
            for ($i = intval($begin[1]); $i <= $end[1]; $i ++) {
                if ($i < 10) {
                    $month[] = $begin[0] . "0" . $i;
                } else {
                    $month[] = $begin[0] . "" . $i;
                }
            }
        } else {
            for ($i = intval($begin[1]); $i <= 12; $i ++) {
                if ($i < 10) {
                    $month[] = $begin[0] . "0" . $i;
                } else {
                    $month[] = $begin[0] . "" . $i;
                }
            }
            /*
              for ($j = intval($begin[0]); $j < $end[0]; $j ++) {
              for ($i = 1; $i <= 12; $i ++) {
              if ($i < 10) {
              $month[] = $j . "0" . $i;
              } else {
              $month[] = $j . "" . $i;
              }
              }
              } */
            for ($i = 1; $i <= $end[1]; $i ++) {
                if ($i < 10) {
                    $month[] = $end[0] . "0" . $i;
                } else {
                    $month[] = $end[0] . "" . $i;
                }
            }
        }
        return $month;
    }

    /**
     * 根据时间戳获取哪一年的第几周（年末年初有交叉的算是下一年的第一周）
     * @param <int> $time
     * @return <array>  array('Y'=>年份, 'W'=>第几周)
     * @author liuxiaobo
     * @since 2014-1-17
     */
    public static function getWeekth($time) {
        $week = date('W', $time);
        $month = date('m', $time);
        $year = date('Y', $time);
        if (1 == $week && 12 == $month) {
            $year += 1;
        }
        return array(
            'Y' => $year,
            'W' => $week,
        );
    }

    /**
     * 把时间段按照自然日、周、月 分组，得到组数
     * @param string $timeGroup day|weekth|month
     * @param datetime $sTime
     * @param datetime $eTime
     * @return int
     */
    public static function getTimeGroupCount($timeGroup = 'day', $sTime, $eTime) {
        if ($timeGroup == 'weekth') {
            $count = Model_Common::getWeekCount(strtotime($sTime), strtotime($eTime));
        } else if ($timeGroup == 'month') {
            $count = Model_Common::getMonthCount(strtotime($sTime), strtotime($eTime));
        } else {
            $sTime = date('Y-m-d 00:00:00', strtotime($sTime));
            $count = round((strtotime($eTime) - strtotime($sTime)) / 86400);
        }
        return $count;
    }

    /**
     * 把时间段分组并分页，获取当前页面的开始、结束时间
     * @param sting $timeGroup  day|weekth|month
     * @param datetime $sTime
     * @param datetime $eTime
     * @param int $page
     * @param int $pageSize
     * @return array    array('start'=>20140701, 'end'=>20140723)
     */
    public static function getTimeGroup($timeGroup = 'day', $sTime, $eTime, $page, $pageSize) {
        $res = array('start' => 0, 'end' => 0);
        $sTime = strtotime($sTime);
        $eTime = strtotime($eTime);
        if ($timeGroup == 'weekth') {
            $start = Model_Common::getFirstDayInWeek($sTime) + ($page - 1) * $pageSize * 86400 * 7;
            $end = $start + $pageSize * 86400 * 7 - 1;
            $ed = Model_Common::getLastDayInWeek($eTime);
            if ($end - 86400 * 7 > $ed) {
                $end = $ed;
            }
        } else if ($timeGroup == 'month') {
            $start = mktime(0, 0, 0, date('m', $sTime) + (($page - 1) * $pageSize), 1, date('Y', $sTime));
            $end = mktime(0, 0, 0, date('m', $start) + $pageSize, 1, date('Y', $start));
            if ($end - 31 * 86400 > $eTime) {
                $end = mktime(0, 0, 0, date('m', $eTime) + 1, 0, date('Y', $eTime));
            }
        } else {
            $start = strtotime(date('Y-m-d 00:00:00', $sTime)) + ($page - 1) * $pageSize * 86400;
            $end = $start + $pageSize * 86400 - 1;
            if ($end > $eTime) {
                $end = $eTime;
            }
        }
        $res['start'] = date('Y-m-d', $start);
        $res['end'] = date('Y-m-d', $end);
        return $res;
    }

    public static function getDaysInWeek($weekth) {
        $year = substr($weekth, 0, 4);
        $week = substr($weekth, 4, 2);
        $time = mktime(0, 0, 0, 1, $week * 7, $year);
        $fd = date('Y-m-d', Model_Common::getFirstDayInWeek($time));
        $ld = date('Y-m-d', Model_Common::getLastDayInWeek($time));
        return array($fd, $ld);
    }

    public static function getDaysInMonth($month) {
        $year = substr($month, 0, 4);
        $month = substr($month, 4, 2);
        $time = mktime(0, 0, 0, $month, 1, $year);
        $fd = date('Y-m-d', $time);
        $ld = date('Y-m-d', mktime(0, 0, 0, $month + 1, 1, $year) - 1);
        return array($fd, $ld);
    }

    /**
     * 获取时间段内有多少个月
     * @param int $sTime
     * @param int $eTime
     * @return int
     */
    public static function getMonthCount($sTime, $eTime) {
        $sTime = strtotime(date('Y-m-01 00:00:00', $sTime));
        $eMonth = strtotime(date('Ym', $eTime));
        $i = 0;
        while ($eMonth >= mktime(0, 0, 0, date('m', $sTime), date('d', $sTime), date('Y', $sTime))) {
            $sTime = mktime(0, 0, 0, date('m', $sTime) + 1, date('d', $sTime), date('Y', $sTime));
            $i++;
        }
        return $i;
    }

    /**
     * 获取时间段内周的数量
     * @param int $sTime
     * @param int $eTime
     * @return int
     */
    public static function getWeekCount($sTime, $eTime) {
        return (Model_Common::getLastDayInWeek($eTime) + 86400 - Model_Common::getFirstDayInWeek($sTime)) / (86400 * 7);
    }

    /**
     * 获取时间所在周的第一天
     * @param int $time
     * @return int
     */
    public static function getFirstDayInWeek($time) {
        return strtotime(date('Y-m-d 00:00:00', $time + (1 - date('N', $time)) * 86400));
    }
      /**
     * 获取时间所在周的最后一天
     * @param int $time
     * @return int
     */
    public static function getLastDayInWeek($time) {
        return strtotime(date('Y-m-d 00:00:00', $time + (7 - date('N', $time)) * 86400));
    }

  

    /**
     * 二维数组以某个属性的值作为数组的键
     * @param array $arr
     * @param string $key
     * @return array
     */
    public static function reindexArr($arr, $key) {
        $res = array();
        foreach ($arr as $v) {
            $res[$v[$key]] = $v;
        }
        return $res;
    }

    /**
     * 二维数组以某些属性的值作为数组的键
     * @param array $arr
     * @param string $keys
     * @return array
     */
    public static function reindexesArr($arr, $keys) {
        $res = array();
        foreach ($arr as $v) {
            $k = 'k';   //为了防止出现比较长的数字
            foreach ($keys as $key) {
                $k .= $v[$key];
            }
            $res[$k] = $v;
        }
        return $res;
    }

    /**
     * 判断两个时间戳是不是同一周
     * @param <int> $time1
     * @param <int> $time2
     * @return <bool>
     * @author liuxiaobo
     * @since 2014-1-17
     */
    public function isSameWeek($time1, $time2) {
        $week1 = Common::getWeekth($time1);
        $week2 = Common::getWeekth($time2);
        return ($week1['Y'] == $week2['Y'] && $week1['W'] == $week2['W']) ? TRUE : FALSE;
    }

    /**
     * 用 mb_strimwidth 来截取字符，使中英尽量对齐。
     * @author liuxiaobo
     * @since 2014-1-17
     */
    public static function wsubstr($str, $start, $width, $trimmarker = '...') {
        $_encoding = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        return mb_strimwidth($str, $start, $width, $trimmarker, $_encoding);
    }

    /**
     * 获取字符串形式的毫秒时间戳
     * @param type $inms    是否去除小数点
     * @return type
     */
    public static function utime($inms) {
        $match = array();
        $utime = preg_match("/^(.*?) (.*?)$/", microtime(), $match);
        $utime = $match[2] + $match[1];
        if ($inms) {
            $utime *= 1000000;
        }
        return sprintf("%01.0f", $utime);
    }

    /**
     * 加工versionName（数字前面加v）
     * @param type $vName
     * @return type
     */
    public function processVName($vName) {
        if (preg_match('/^[0-9]+/', $vName)) {
            return 'v' . $vName;
        }
        return $vName;
    }
   
    

    public static function listData($models, $valueField, $textField, $groupField = '') {
        $listData = array();
        if ($groupField === '') {
            foreach ($models as $model) {
                $value = self::value($model, $valueField);
                $text = self::value($model, $textField);
                $listData[$value] = $text;
            }
        } else {
            foreach ($models as $model) {
                $group = self::value($model, $groupField);
                $value = self::value($model, $valueField);
                $text = self::value($model, $textField);
                $listData[$group][$value] = $text;
            }
        }
        return $listData;
    }

    public static function value($model, $attribute, $defaultValue = null) {
        foreach (explode('.', $attribute) as $name) {
            if (is_object($model))
                $model = $model->$name;
            else if (is_array($model) && isset($model[$name]))
                $model = $model[$name];
            else
                return $defaultValue;
        }
        return $model;
    }

    /**
     * 获取指定日期$day倒推$spaceDays天后是否是指定周期类型的最后一天
     * @param type $day         指定日期
     * @param type $spaceDays   倒推多少天
     * @param type $type        指定获取的周期类型
     * @param type $count       最大循环次数
     * @return type
     */
    public static function getLimitDateFromDay($day, $spaceDays, $type, $count) {
        $res = [];
        $dayTime = strtotime(substr($day, 0, 4) . '-' . substr($day, 4, 2) . '-' . substr($day, 6, 2).' 00:00:00');
        if($type == 'day'){
            for($i=1; $i<=$count; $i++){
                $t = $dayTime-$spaceDays*$i*86400;
                $res[$i] = date('Ymd', $t);
            }
        }
        if($type == 'weekth'){
            for($i=1; $i<=$count; $i++){
                $t = $dayTime-$spaceDays*$i*86400;
                if(7 == date('N', $t)){
                    $week = self::getWeekth($t);
                    $res[$i] = $week['Y'].$week['W'];
                }
            }
        }
        if($type == 'month'){
            for($i=1; $i<=$count; $i++){
                $t = $dayTime-$spaceDays*$i*86400;
                if(1 == date('d', $t+86400)){
                    $res[$i] = date('Ym', $t);
                }
            }
        }
        return $res;
    }
    
    }
