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

    public static function _whereExpr($where) {
        if (empty($where)) {
            return $where;
        }
        if (!is_array($where)) {
            $where = array($where);
        }
        foreach ($where as $cond => &$term) {
            // is $cond an int? (i.e. Not a condition)
            if (is_int($cond)) {
                // $term is the full condition
                if ($term instanceof Zend_Db_Expr) {
                    $term = $term->__toString();
                }
            } else {
                // $cond is the condition with placeholder,
                // and $term is quoted into the condition
                $term = self::$_defaultDb->quoteInto($cond, $term);
            }
            $term = '(' . $term . ')';
        }
        $where = implode(' AND ', $where);
        return strlen($where) > 0 ? "where {$where}" : "";
    }

    /**
     * 分页控件简化版
     *
     * @param Integer $pageIndex
     *            页号 从0开始
     * @param Integer $pageSize
     *            分页大小
     * @param Integer $totalRecords
     *            记录总数
     */
    public static function PagerRender($pageIndex, $pageSize, $totalRecords, $returnHTML = false) {
        return self::PagerRenderEx($pageIndex, $pageSize, $totalRecords, $returnHTML, 'pg', 'ps');
    }

    /**
     * 分页控件
     *
     * @param Integer $pageIndex
     *            页号
     * @param Integer $pageSize
     *            分页大小
     * @param Integer $totalRecords
     *            总记录数
     * @param Bool $returnHtml
     *            返回HTML内容
     * @param String $strPageIndexKey
     *            页号关键字
     * @param String $strPageSizeKey
     *            分页大小关键字
     */
    public static function PagerRenderEx($pageIndex, $pageSize, $totalRecords, $returnHtml = false, $strPageIndexKey = 'pg', $strPageSizeKey = 'ps') {
        $html_pager = '
		
		';
        if ($pageSize > 0 && $totalRecords > 0) {
            if ($totalRecords % $pageSize) {
                $pageCount = (int) ($totalRecords / $pageSize) + 1;
            } else {
                $pageCount = $totalRecords / $pageSize;
            }
            $pageNum = ($pageIndex + 1);
            //共有26233条数据  当前1页/共525页
            $html_pager = "
		                                    <div class=\"dataTables_info\" >共有 {$totalRecords} 条数据  当前 {$pageNum} 页/共 {$pageCount} 页</div>
			<div class=\"dataTables_paginate paging_full_numbers\">

<span>
			";
            for ($p = 0; $p < $pageCount; $p ++) {
                if (($pageIndex - 5) == $p) {
                    $_GET[$strPageIndexKey] = $p;
                    $_GET[$strPageSizeKey] = $pageSize;
                    $html_pager .= "<a tabindex='0' style='color:white' class='paginate_button' href='" .
                            str_replace("?" . $_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']) . "?" . http_build_query($_GET) .
                            "'> &lt;&lt;</a>";
                } else {
                    if ($p == $pageIndex) {
                        $html_pager .= "<a tabindex='0' class='paginate_active' >" . ($p + 1) . "</a>";
                    } else
                    if ($p > ($pageIndex - 5)) {
                        $_GET[$strPageIndexKey] = $p;
                        $_GET[$strPageSizeKey] = $pageSize;
                        $html_pager .= "<a tabindex='0' style='color:white' class='paginate_button' href='" .
                                str_replace("?" . $_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']) . "?" .
                                http_build_query($_GET) . "'>" . ($p + 1) . "</a>";
                    }
                }
                if (($p - $pageIndex) == 4) {
                    $_GET[$strPageIndexKey] = $p + 1;
                    $_GET[$strPageSizeKey] = $pageSize;
                    $html_pager .= "<a tabindex='0' style='color:white' class='paginate_button' href='" .
                            str_replace("?" . $_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']) . "?" . http_build_query($_GET) .
                            "'>&gt;&gt;</a>";
                    break;
                }
                /*
                 * if($p<($pageIndex-10)) { $_GET['pg'] = $p; $_GET['ps'] =
                 * $pageSize; echo "[<a
                 * href='".$_SERVER['PHP_SELF']."?".http_build_query($_GET)."'>前10页</a>]";
                 * }
                 */
            }
            $html_pager .="</span> 
			</div>
			";
        } else {
            
        }
        if ($returnHtml)
            return $html_pager;
        else
            echo $html_pager;
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

    public static function getUsernameFromCPUsername($username, &$cpid = -1) {
        try {
            $pusename = $username;
            $pos = (int) strpos($username, ".");
            if ($pos != false) {
                if (($pos > 0) && ($pos < 8)) {
                    $username = substr($username, $pos + 1);
                    $cpid = substr($pusename, 0, $pos);
                }
                return $username;
            } else {
                return $username;
            }
        } catch (Exception $e) {
            return $username;
        }
    }

    public static function toXml($data, $rootNodeName = 'root', $xml = null) {
        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }

        if ($xml == null) {
            $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
        }

        // loop through the data passed in.
        foreach ($data as $key => $value) {
            // no numeric keys in our xml please!
            if (is_numeric($key)) {
                // make string key...
                //$key = "bill". (string) $key;
                $key = "bill";
            }

            // replace anything not alpha numeric
            $key = preg_replace('/[^a-z][_]/i', '', $key);

            // if there is another array found recrusively call this function
            if (is_array($value)) {
                $node = $xml->addChild($key);
                // recrusive call.
                self::toXml($value, $rootNodeName, $node);
            } else {
                // add single node.
                //$value = htmlentities($value);
                $xml->addChild($key, $value);
            }
        }
        // pass back as string. or simple xml object if you want!
        return $xml->asXML();
    }

    public static function fopen_url($url) {
        if (function_exists('file_get_contents')) {
            $file_content = @file_get_contents($url);
        } elseif (ini_get('allow_url_fopen') && ($file = @fopen($url, 'rb'))) {
            $i = 0;
            while (!feof($file) && $i ++ < 1000) {
                $file_content .= strtolower(fread($file, 4096));
            }
            fclose($file);
        } elseif (function_exists('curl_init')) {
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $url);
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_handle, CURLOPT_FAILONERROR, 1);
            curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Trackback Spam Check');
            $file_content = curl_exec($curl_handle);
            curl_close($curl_handle);
        } else {
            $file_content = '';
        }
        return $file_content;
    }

    /**
     * 发送下行短信
     * @param string $mobile
     * @param string $message
     */
    /*
      public static function sendMsg($mobile,$message){

      if(strlen(trim($message))==0||strlen(trim($mobile))==0)
      {
      return false;
      }
      $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'staging');
      $config->You->settings->sms->loginname;

      $user = $config->You->settings->sms->loginname;
      $password = $config->You->settings->sms->password;
      $url = $config->You->settings->sms->url;
      //$port = $config->You->settings->sms->port;
      $message = iconv( "UTF-8", "GBK" , $message);
      $message = urlencode($message);

      $postdata = "loginname={$user}&password={$password}&tele={$mobile}&msg={$message}";
      $url = $url."?".$postdata;

      //echo $url;
      $result= file_get_contents($url);
      //如果发送成功，则返回：success:本次发送短信编号 如果发送失败，则返回：error:错误描述

      $result = explode(':',$result);
      if(isset($result[0])&&$result[0]=="success")
      {
      return true;
      }
      return false;
      }
     */

    /**
     * 发送下行短信
     * @param string $mobile
     * @param string $message
     */
    public static function sendMsg($mobile, $message) {

        require_once APPLICATION_PATH . "/../library/You/Nusoaplib/class.nusoap_base.php";
        require_once APPLICATION_PATH . "/../library/You/Nusoaplib/class.soapclient.php";
        require_once APPLICATION_PATH . "/../library/You/Nusoaplib/Client.php";
        require_once APPLICATION_PATH . "/../library/You/Nusoaplib/class.soap_val.php";
        require_once APPLICATION_PATH . "/../library/You/Nusoaplib/class.soap_transport_http.php";
        require_once APPLICATION_PATH . "/../library/You/Nusoaplib/class.soap_parser.php";

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'staging');
        $gwUrl = $config->You->settings->sms->gwUrl;
        $serialNumber = $config->You->settings->sms->serialNumber;
        $password = $config->You->settings->sms->password;
        $sessionKey = $config->You->settings->sms->sessionKey;
        $connectTimeOut = $config->You->settings->sms->connectTimeOut;
        $readTimeOut = $config->You->settings->sms->readTimeOut;
        $proxyhost = false;
        $proxyport = false;
        $proxyusername = false;
        $proxypassword = false;

        $client = new Client($gwUrl, $serialNumber, $password, $sessionKey, $proxyhost, $proxyport, $proxyusername, $proxypassword, $connectTimeOut, $readTimeOut);
        $client->setOutgoingEncoding("UTF-8");
        $statusCode = $client->sendSMS(array($mobile), $message);
        if ($statusCode == 0) {
            return true;
        }
        return false;
    }

    public static function random($length, $numeric = 0) {
        mt_srand();
        $seed = base_convert(md5(print_r($_SERVER, 1) . microtime()), 16, $numeric ? 10 : 35 );
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        $hash = '';
        $max = strlen($seed) - 1;

        for ($i = 0; $i < $length; $i ++) {
            $hash .= $seed [mt_rand(0, $max)];
        }

        return strtoupper($hash);
    }

    private function get_url($url, $host, $port) {

        $fp = fsockopen($host, $port);

        $header = "GET $url HTTP/1.1\r\n";

        $header .= "Host: " . $host . "\r\n";

        $header .= "Connection: Close\r\n\r\n";

        return fputs($fp, $header);
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
    
 

    public static function getWeekFirstDay($day) {
        $firstday=date('Ymd',strtotime("last Monday",strtotime($day))); 
        return $firstday;
    }
    
    public static function getWeekLastDay($day) {
        $lastday=date('Ymd',strtotime("Sunday",strtotime($day)));
        return $lastday;
    }
   
    public static function getMonthFirstDay($day) {
        $beginThismonth =date('Ym01',strtotime($day));
        return $beginThismonth;
    }
    
    public static function getMonthLastDay($day) {
        $firstday = date('Ym01',strtotime($day)); 
        $lastday = date('Ymd',strtotime("$firstday +1 month -1 day")); 
        return $lastday;
    }
    
    public static function getNextMonthFirstDay($day) {
        $firstday = date('Ym01',strtotime($day));
        $lastday = date('Ymd',strtotime("$firstday +1 month"));
        return $lastday;
    }
    
    public static function getNextMonthLastDay($day) {
        $firstday = date('Ym01',strtotime($day)); 
        $lastday = date('Ymd',strtotime("$firstday +2 month -1 day")); 
        return $lastday;
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
    
    /**
     * 根据where获取key
     * @param type $where
     * @return type
     */
    public  static function getKey($where) {
        return 'bx_rep:'.$where['gameID'].':'.$where['partnerID'].':'.$where['gameserverID'].':'.$where['date'];
    }
    public  static function getWeekKey($where) {
        $weekTo=Model_Common::getWeekLastDay($where['date']);
        return 'bx_rep:'.$where['gameID'].':'.$where['partnerID'].':'.$where['gameserverID'].':'.$where['date'].'-'.$weekTo;
    }
    
     public  static function getMonthKey($where) {
         $monthTo=  Model_Common::getMonthLastDay($where['date']) ;
        return 'bx_rep:'.$where['gameID'].':'.$where['partnerID'].':'.$where['gameserverID'].':'.$where['date'].'-'.$monthTo;
    }
    
    public  static function getPayHabitChargeNumKey($where) {
        if($where['num']=='week'){
            $weekTo=Model_Common::getWeekLastDay($where['date']);
            return 'bx_rep:'.$where['gameID'].':'.$where['partnerID'].':'.$where['gameserverID'].':'.$where['date'].'-'.$weekTo.'ChargeNum';
        }else{
            $monthTo=  Model_Common::getMonthLastDay($where['date']) ;
            return 'bx_rep:'.$where['gameID'].':'.$where['partnerID'].':'.$where['gameserverID'].':'.$where['date'].'-'.$monthTo.':ChargeNum';
        }
    }
    
    public  static function getPayHabitChargeTimesKey($where) {
        if($where['num']=='week'){
            $weekTo=Model_Common::getWeekLastDay($where['date']);
            return 'bx_rep:'.$where['gameID'].':'.$where['partnerID'].':'.$where['gameserverID'].':'.$where['date'].'-'.$weekTo.'ChargeTimes';
        }else{
            $monthTo=  Model_Common::getMonthLastDay($where['date']) ;
            return 'bx_rep:'.$where['gameID'].':'.$where['partnerID'].':'.$where['gameserverID'].':'.$where['date'].'-'.$monthTo.':ChargeTimes';
        }
        
    }
    
    public  static function getChargeIntervalKey($where) {
        return 'bx_rep:'.$where['gameID'].':'.$where['partnerID'].':'.$where['gameserverID'].':'.$where['date'].'interval';
    }
    public  static function getPlayerIDKey($where) {
        return 'bx_rep:'.$where['gameID'].':'.$where['date'].':'.$where['playerID'];
    }
    
    
    public  static function ymdToYmd($date) {
        return '20'.$date;
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
 
    public static function array_add($a,$b){ 
        $arr=array_intersect_key($a, $b); 
        foreach($b as $key=>$value){ 
        if(!array_key_exists($key, $a)){ 
            $a[$key]=$value; 
        } 
        } 
        foreach($arr as $key=>$value){ 
            $a[$key]=$a[$key]+$b[$key]; 
        } 
        return $a; 
    } 


     public  static function getData($request,$var) {
        $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
        $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
        $daysNum=$request->getParam('daysNum');
        $gameID = $request->getParam('productID');
        $where=  Model_Common::getParam($request);
        $start=strtotime($startTime);
        $end=strtotime($endTime);
        $serverIDs=$where['serverID'];
        $channelIDs=$where['channelID'];
        $funName='get'.$var;
        
        $res=array();
        for($i=0;$start+$i*24*60*60<=$end;$i++){
                $date=date('Ymd',$start+$i*24*60*60);
                $sum=0;
                    if($daysNum!=null){    
                               foreach ($serverIDs as $serverID){
                                   foreach ($channelIDs as $channelID){
                                           $where=array(
                                                'dayNum'=>$daysNum,
                                                   'date'=>$date,
                                                   'gameID'=> $gameID ,
                                                   'gameserverID'=> $serverID ,
                                                   'partnerID'=>$channelID  
                                               );
                                           $sum+=Model_CacheData::model()->$funName($where);

                                       }
                               }
                               }
                    else{
                               foreach ($serverIDs as $serverID){
                                   foreach ($channelIDs as $channelID){
                                           $where=array(
                                                   'date'=>$date,
                                                   'gameID'=> $gameID ,
                                                   'gameserverID'=> $serverID ,
                                                   'partnerID'=>$channelID  
                                               );
                                           $sum+=Model_CacheData::model()->$funName($where);
                                       }
                                   }
                               }
                      $res[]=array(
                        'item'=>$date,
                        'value'=>$sum
                    );     
                    }
                    return $res;
        }
    
//        public  static function getActivityPlayer($request) {
//            $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
//            $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
//            $gameID = $request->getParam('productID');
//            $game=  Model_Game::model()->getTest($gameID);
//            $where=Model_Common::getParam($request);
//            $serverIDs=$where['serverID'];
//            $channelIDs=$where['channelID'];
//            $r = Model_UserMap::model()->setGameTalbe($game)->userMapGroup($startTime, $endTime,
//                'day', Model_UserMap::USER_TYPE_ACTIVE_PLAYER, $channelIDs, $serverIDs);
//            return $r;
//        }
        public  static function getActivityPlayer($request,$var) {
            $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
            $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
            $gameID = $request->getParam('productID');
            $game=  Model_Game::model()->getTest($gameID);
            $where=Model_Common::getParam($request);
            $serverIDs=$where['serverID'];
            $channelIDs=$where['channelID'];
            if($var=='day'){
                $r = Model_UserMap::model()->setGameTalbe($game)->userMapGroup($startTime, $endTime,
                     'day', Model_UserMap::USER_TYPE_ACTIVE_PLAYER, $channelIDs, $serverIDs);
            }
            if($var=='weekth'){
                $stime = Model_Common::getWeekth(strtotime(substr($startTime, 0, 4).'-'.substr($startTime, 4, 2).'-'.substr($startTime, 6, 2).' 00:00:00'));
                $etime = Model_Common::getWeekth(strtotime(substr($endTime, 0, 4).'-'.substr($endTime, 4, 2).'-'.substr($endTime, 6, 2).' 00:00:00'));
                $r = Model_UserMap::model()->setGameTalbe($game)->userMapGroup($stime, $etime,
                'weekth', Model_UserMap::USER_TYPE_ACTIVE_PLAYER, $channelIDs, $serverIDs);
            }
            if($var=='month'){
                $stime = date('Ym',  strtotime($startTime));
                $etime = date('Ym',  strtotime($endTime));
                $r = Model_UserMap::model()->setGameTalbe($game)->userMapGroup($stime, $etime,
                'month', Model_UserMap::USER_TYPE_ACTIVE_PLAYER, $channelIDs, $serverIDs);
            }
            return $r;
        }
        
        public  static function getPartnerActivityPlayer($request) {
            $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
            $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
            $gameID = $request->getParam('productID');
            $game=  Model_Game::model()->getTest($gameID);
            $where=Model_Common::getParam($request);
            $serverIDs=$where['serverID'];
            $channelIDs=$where['channelID'];
            $res=array();
            foreach($channelIDs as $channelID1){
                $channelID=array($channelID1);
                $r = Model_UserMap::model()->setGameTalbe($game)->userMapGroup($startTime, $endTime,
                     'day', Model_UserMap::USER_TYPE_ACTIVE_PLAYER, $channelID, $serverIDs);
                $res[]=array(
                    'item'=>Model_UserInfoType::getTitleFromRedis($game, Model_UserInfoType::TYPE_CHANNEL, $channelID1),
                    'value'=>$r
                );
            }   
            return $res;
        }
        
//         public  static function getWeekActivityPlayer($request) {
//            $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
//            $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
//            $stime = Model_Common::getWeekth(strtotime(substr($startTime, 0, 4).'-'.substr($startTime, 4, 2).'-'.substr($startTime, 6, 2).' 00:00:00'));
//            $etime = Model_Common::getWeekth(strtotime(substr($endTime, 0, 4).'-'.substr($endTime, 4, 2).'-'.substr($endTime, 6, 2).' 00:00:00'));
//            $gameID = $request->getParam('productID');
//            $game=  Model_Game::model()->getTest($gameID);
//            $where=Model_Common::getParam($request);
//            $serverIDs=$where['serverID'];
//            $channelIDs=$where['channelID'];
//            $r = Model_UserMap::model()->setGameTalbe($game)->userMapGroup($stime, $etime,
//                'weekth', Model_UserMap::USER_TYPE_ACTIVE_PLAYER, $channelIDs, $serverIDs);
//            return $r;
//        }
        
    
      public  static function getArrayData($request,$var) {
        $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
        $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
        $gameID = $request->getParam('productID');
        $where=Model_Common::getParam($request);
        $start=strtotime($startTime);
        $end=strtotime($endTime);
        $serverIDs=$where['serverID'];
        $channelIDs=$where['channelID'];
         
         $funName='get'.$var;
        
        $data=array();
        for($i=0;$start+$i*24*60*60<=$end;$i++){
            $sum=array();
                $date=date('Ymd',$start+$i*24*60*60);
                foreach ($serverIDs as $serverID){
                    foreach ($channelIDs as $channelID){
                            $where=array(
                                    'date'=>$date,
                                    'gameID'=> $gameID ,
                                    'gameserverID'=> $serverID ,
                                    'partnerID'=>$channelID  
                                );
                           $sum=Model_Common::array_add($sum, Model_CacheData::model()->$funName($where));
                          }
                    }
               $data=Model_Common::array_add($sum, $data);
        }
         return  $data;
      }
    
     
    
    public  static function getWeekData($request,$var) {
        
        $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
        echo $startTime;
        $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
        echo $endTime;
        $gameID = $request->getParam('productID');
        $where=Model_Common::getParam($request);
        $funName='getWeek'.$var;
        $serverIDs=$where['serverID'];
        $channelIDs=$where['channelID'];
        echo Model_Common::getWeekFirstDay($startTime);
        $start=strtotime(Model_Common::getWeekFirstDay($startTime));
        echo Model_Common::getWeekFirstDay($endTime);
        $end=strtotime(Model_Common::getWeekFirstDay($endTime));
        for($i=0;$start+$i*7*24*60*60<=$end;$i++){
            $weekFrom=date('Ymd',$start+$i*7*24*60*60);
            echo $weekFrom;
            if($start+($i+1)*7*24*60*60-1<  strtotime(date('Ymd'))){
                $weekEnd=date('Ymd',$start+($i+1)*7*24*60*60-1);
                echo $weekEnd;
            }else{
                $weekEnd=date('Ymd');
                echo $weekEnd;
            }
            
            $sum=0;
            foreach ($serverIDs as $serverID){
                foreach ($channelIDs as $channelID){
                        $where=array(
                            'date'=>$weekFrom,
                            'gameID'=> $gameID ,
                            'gameserverID'=> $serverID ,
                            'partnerID'=>$channelID  
                        );
                        $sum+=Model_CacheData::model()->$funName($where);
                        
                }
            }
            
             $data[]=array(
                            'item'=>$weekFrom.'-'.$weekEnd,
                            'value'=> $sum     
                        );
        }
        return $data;
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

    
    public  static function getMonthData($request,$var) {
        $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
        $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
        $gameID = $request->getParam('productID');
        $where=Model_Common::getParam($request);
        $funName='getMonth'.$var;
        $serverIDs=$where['serverID'];
        $channelIDs=$where['channelID'];
        $start=strtotime(Model_Common::getMonthFirstDay($startTime));
        $end=strtotime(Model_Common::getMonthFirstDay($endTime));
        while($start<=$end)
        {
            $monthFrom=date('Ym01',$start);
            $sum=0;
            foreach ($serverIDs as $serverID){
                foreach ($channelIDs as $channelID){
                        $where=array(
                            'date'=>$monthFrom,
                            'gameID'=> $gameID ,
                            'gameserverID'=> $serverID ,
                            'partnerID'=>$channelID  
                        );
                        $sum+=Model_CacheData::model()->$funName($where);
                        
                }
            }
             $data[]=array(
                            'item'=>date('m',$monthFrom),
                            'value'=> $sum     
                        );
             $start=  strtotime(Model_Common::getNextMonthFirstDay(date('Ymd',$start)));
        }
        return $data;
    }
    
    public  static function getPartnerData($request,$var) {
        $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
        $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
        $gameID = $request->getParam('productID');
        $game=  Model_Game::model()->getTest($gameID);
        $gameserverID = $request->getParam('gameserverID'); 
        $partnerID = $request->getParam('partnerID');
        $serverIDs=array();
        $channelIDs=array();
        $data=array();
        $items=array();
        $items = Model_UserInfoType::model()->setGameTalbe($game)->getListByType(Model_UserInfoType::TYPE_CHANNEL);
        foreach ($items as $item) {
            $res[] = [
                $item->id,
                  ];
        }
        $c = array_map('array_shift', $res); 
        $funName='get'.$var;
        if($gameserverID!=null){
            if(strstr($gameserverID, ',')!=null){
                $serverIDs=  explode (',', $gameserverID);
            }
            else {
                $serverIDs=array($gameserverID);
              }
        }
        else{
            $serverIDs=array('');
        }
        if($partnerID!=null){
            if(strstr($partnerID, ',')!=null){
                $channelIDs=  explode (',', $partnerID);
            }
            else {
                $channelIDs=array($partnerID);
              }
        }
        else{
            $channelIDs = $c;
        }
        $start=strtotime($startTime);
        $end=strtotime($endTime);
        $result=array();
        for($i=0;$start+$i*24*60*60<=$end;$i++){
            $time=date('Ymd',$start+$i*24*60*60);
            echo $time;
            foreach ($channelIDs as $channelID){
                $sum=0;
                foreach ($serverIDs as $serverID){
                    if($request->getParam('daysNum')!=null){    
                            $where=array(
                                    'dayNum'=>$request->getParam('daysNum'),
                                    'date'=>$time,
                                    'gameID'=> $gameID ,
                                    'gameserverID'=> $serverID ,
                                    'partnerID'=>$channelID  
                                );
                        }else{
                            $where=array(
                                    'date'=>$time,
                                    'gameID'=> $gameID ,
                                    'gameserverID'=> $serverID ,
                                    'partnerID'=>$channelID  
                                );
                        }
                      $sum+=Model_CacheData::model()->$funName($where);  
                      echo $sum;
                }
                
                $data[]=array(
                            'item'=>Model_UserInfoType::getTitleFromRedis($game, Model_UserInfoType::TYPE_CHANNEL, $channelID),
                            'value'=> $sum     
                        );
            }
            $result=Model_Common::array_add($result, $data);
            return $result;
        }
    }
    
    public  static function getPartnerDataArray($request,$var) {
        $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
        $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
        $gameID = $request->getParam('productID');
        $game=  Model_Game::model()->getTest($gameID);
        $gameserverID = $request->getParam('gameserverID'); 
        $partnerID = $request->getParam('partnerID');
        $serverIDs=array();
        $channelIDs=array();
        $data=array();
        $items=array();
        $items = Model_UserInfoType::model()->setGameTalbe($game)->getListByType(Model_UserInfoType::TYPE_CHANNEL);
        foreach ($items as $item) {
            $res[] = [
                $item->id,
                  ];
        }
        $c = array_map('array_shift', $res); 
        $funName='get'.$var;
        if($gameserverID!=null){
            if(strstr($gameserverID, ',')!=null){
                $serverIDs=  explode (',', $gameserverID);
            }
            else {
                $serverIDs=array($gameserverID);
              }
        }
        else{
            $serverIDs=array('');
        }
        if($partnerID!=null){
            if(strstr($partnerID, ',')!=null){
                $channelIDs=  explode (',', $partnerID);
            }
            else {
                $channelIDs=array($partnerID);
              }
        }
        else{
            $channelIDs = $c;
        }
        $start=strtotime($startTime);
        $end=strtotime($endTime);
        $result=array();
        
            foreach ($channelIDs as $channelID){
                $sum=0;
                 for($i=0;$start+$i*24*60*60<=$end;$i++){
                    $time=date('Ymd',$start+$i*24*60*60);
                foreach ($serverIDs as $serverID){
                   
                    if($request->getParam('daysNum')!=null){    
                            $where=array(
                                    'dayNum'=>$request->getParam('daysNum'),
                                    'date'=>$time,
                                    'gameID'=> $gameID ,
                                    'gameserverID'=> $serverID ,
                                    'partnerID'=>$channelID  
                                );
                        }else{
                            $where=array(
                                    'date'=>$time,
                                    'gameID'=> $gameID ,
                                    'gameserverID'=> $serverID ,
                                    'partnerID'=>$channelID  
                                );
                        }
                      $sum+=Model_CacheData::model()->$funName($where);  
                }
                echo $sum;
                $data[]=array(
                            'item'=>$time,
                            'value'=> $sum     
                        );
                        print_r($data);
                 }
                $result[]=array(
                'item'=>Model_UserInfoType::getTitleFromRedis($game, Model_UserInfoType::TYPE_CHANNEL, $channelID),
                'value'=>$data
                );
                
            }
            
            return $result;
       
        
    }
    public  static function getWeekCharge($request,$var) {
        $funName='getPayHabit'.$var;
        $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
        $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
        $gameID = $request->getParam('productID');
        $where=Model_Common::getParam($request);
        $serverIDs=$where['serverID'];
        $channelIDs=$where['channelID'];
        $start=strtotime(Model_Common::getWeekFirstDay($startTime));
        $end=strtotime(Model_Common::getWeekFirstDay($endTime));
        
        for($i=0;$start+$i*7*24*60*60<=$end;$i++){
            $weekFrom=date('ymd',$start+$i*7*24*60*60);
            
            $sum=array();
            foreach ($serverIDs as $serverID){
                foreach ($channelIDs as $channelID){
                        $where=array(
                            'date'=>$weekFrom,
                            'num'=>'week',
                            'gameID'=> $gameID ,
                            'gameserverID'=> $serverID ,
                            'partnerID'=>$channelID  
                        );
                        $sum=Model_Common::array_add($sum, Model_CacheData::model()->$funName($where));
                        
                }
            }
            
             $data=Model_Common::array_add($sum,$data );
        }
        return $data;
    } 
    
    
    public  static function getMonthCharge($request,$var) {
        $funName='getPayHabit'.$var;
        $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
        $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
        $where=Model_Common::getParam($request);
        $serverIDs=$where['serverID'];
        $channelIDs=$where['channelID'];
        $gameID=$request->getParam('productID');
        $start=strtotime(Model_Common::getMonthFirstDay($startTime));
        $end=strtotime(Model_Common::getMonthFirstDay($endTime));
        while($start<=$end)
        {
            $monthFrom=date('ymd',$start);
            $monthTo=Model_Common::getMonthLastDay($monthFrom);
            $sum=array();
            foreach ($serverIDs as $serverID){
                foreach ($channelIDs as $channelID){
                        $where=array(
                            'date'=>$monthFrom,
                            'num'=>'month',
                            'gameID'=> $gameID ,
                            'gameserverID'=> $serverID ,
                            'partnerID'=>$channelID  
                        );
                        $sum=Model_Common::array_add($sum, Model_CacheData::model()->$funName($where));
                        
                }
            }
             $data=Model_Common::array_add($sum,$data );
             $start=Model_Common::getNextMonthFirstDay($start);
        }
        return $data;
    }
    
    public  static function getDataInterval($request,$var) {
        $funName='getPayHabit'.$var;
        $startTime = Model_Common::ymdToYmd($request->getParam('startTime'));
        $endTime = Model_Common::ymdToYmd($request->getParam('endTime'));
        $gameID = $request->getParam('productID');
        $where=Model_Common::getParam($request);
        $serverIDs=$where['serverID'];
        $channelIDs=$where['channelID'];
        $start=strtotime($startTime);
        $end=strtotime($endTime);
        
        for($i=0;$start+$i*24*60*60<=$end;$i++){
                $time=date('ymd',$start+$i*24*60*60);
                $sum=array();
                foreach ($serverIDs as $serverID){
                    foreach ($channelIDs as $channelID){
                        
                            $where=array(
                                    'date'=>$time,
                                    'gameID'=> $gameID ,
                                    'gameserverID'=> $serverID ,
                                    'partnerID'=>$channelID  
                                );
                       $sum=Model_Common::array_add($sum, Model_CacheData::model()->$funName($where));
                           

                    }
                }
                 $data=Model_Common::array_add($sum,$data );
            }
          
        
        return $data;
        
        
    }
    
     public  static function getParam($request) {
        $gameserverID = $request->getParam('gameserverID'); 
        $partnerID = $request->getParam('partnerID');
        $serverIDs=array();
        $channelIDs=array();
        if($gameserverID!=null){
            if(strstr($gameserverID, ',')!=null){
                $serverIDs=  explode (',', $gameserverID);
            }
            else {
                $serverIDs=array($gameserverID);
              }
        }
        else{
            $serverIDs=array('');
        }
        if($partnerID!=null){
            if(strstr($partnerID, ',')!=null){
                $channelIDs=  explode (',', $partnerID);
            }
            else {
                $channelIDs=array($partnerID);
              }
        }
        else{
            $channelIDs=array('');
        }
        return array(
            
            'serverID'=>$serverIDs,
            'channelID'=>$channelIDs
        );
        
     }
    
    }
