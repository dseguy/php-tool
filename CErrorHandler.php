<?php

/**
 * This file contains the error handler application component.
 * @example 在程序入口位置添加自定义错误处理
 *  require_once 'components/CErrorHandler.php';
  $errorHandle = CErrorHandler::instance();
  set_error_handler(array($errorHandle, 'log_error'));
  set_exception_handler(array($errorHandle, 'log_exception'));
  register_shutdown_function(array($errorHandle, 'emailError'));
  error_reporting( E_ALL );
 */
class CErrorHandler {

    private $errorMessages = array();
    public $theSameErrorBlockTime = 900;   //相同错误短时间内不上报邮件
    static $instance = null;
    public $emailSubTitle = 'php执行命令行出错了！（此邮件为系统发送，请勿回复）';
    public $emails = array(
        'abc@example.com',
    );

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new CErrorHandler();
        }
        return self::$instance;
    }

    public function addErrorMessage($echo) {
        $key = 'sys:error:' . md5($echo);
        $hasTheError = Model_Redis::exists($key);
        $hasTheError || Model_Redis::set($key, $echo, $this->theSameErrorBlockTime);     //同样的错误15分钟内不再上报邮件
        $hasTheError || $this->errorMessages[] = $echo;
    }

    public function emailError() {
        if (empty($this->errorMessages)) {
            return;
        }
        $echo = '<div>' . implode('</div><div>', $this->errorMessages) . '</div>';
        foreach ($this->emails as $email) {
            Model_Common::sendMail($email, $this->emailSubTitle, $echo);
        }
    }

    public function log_error($num, $str, $file, $line, $context = null) {
        $this->log_exception(new ErrorException($str, 0, $num, $file, $line));
    }

    /**
     * Uncaught exception handler.
     */
    public function log_exception(Exception $e) {
        $trace = '';
        foreach ($e->getTrace() as $k => $v) {
            $s = $k % 2 ? 'background-color:rgb(185,248,172);' : 'background-color:rgb(240,240,240);';
            $trace .= '<span style="' . $s . '"><span style="background-color:rgb(255,165,107);">#' . $k . '</span> ' . $v['file'] . '(' . $v['line'] . '):' . $v['class'] . '::' . $v['function'] . '(' . json_encode($v['args']) . ')</span><br />';
        }

        $a = '';
        $a .= "<div style='text-align: center;width:900px;word-break: break-all;'>";
        $a .= "<h2 style='color: rgb(190, 50, 50);'>Exception Occured:</h2>";
        $a .= "<table style='width: 1030px; display: inline-block;'>";
        $a .= "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Type</th><td>" . get_class($e) . "</td></tr>";
        $a .= "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td>{$e->getMessage()}</td></tr>";
        $a .= "<tr style='background-color:rgb(230,230,230);'><th>File</th><td>{$e->getFile()}</td></tr>";
        $a .= "<tr style='background-color:rgb(240,240,240);'><th>Line</th><td>{$e->getLine()}</td></tr>";
        $a .= "<tr style='background-color:rgb(240,240,240);'><th>Trace</th><td style='text-align: left;'>{$trace}</td></tr>";
        $a .= "</table></div>";
        $this->addErrorMessage($a);
        echo $e->getMessage();
        return $a;
    }

}
