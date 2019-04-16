<?php

/**
 * Log日志类
 * write函数为私有函数，所有需要写日志的地方都需要新加一个函数来显示调用write函数。
 * 代码虽然会有冗余，但易于对日志逻辑的管理。
 */
class Log_File extends \Yaf_Controller_Abstract {

    // PHP系统内置错误代码及定义
    protected static $errorCodes = array(
        1 => "E_ERROR: Fatal run-time errors.",
        2 => "E_WARNING: Run-time warnings(non-fatal errors).",
        4 => "E_PARSE: Compile-time parse errors.",
        8 => "E_NOTICE: Run-time notices. Indicate something that could have an error.",
        16 => "E_CORE_ERROR: Fatal errors that occur during PHP's initial startup.",
        32 => "E_CORE_WARNING: Warnings(non-fatal errors) that occur during PHP's initial startup.",
        64 => "E_COMPILE_ERROR: Fatal compile-time errors(generated by Zend Scripting Engine).",
        128 => "E_COMPILE_WARNING: Compile-time warnings(non-fatal errors, generated by Zend Scripting Engine).",
        256 => "E_USER_ERROR: User-generated error message(generated by using trigger_error()).",
        512 => "E_USER_WARNING: User-generated warning message(generated by using trigger_error()).",
        1024 => "E_USER_NOTICE: User-generated notice message(generated by using trigger_error()).",
        2048 => "E_STRICT: PHP suggest changes to your code ensure the best interoperability and forward compatibility.",
        4096 => "E_RECOVERABLE_ERROR: Catchable fatal error.",
        8192 => "E_DEPRECATED: Run-time notices(code will not work in future versions).",
        16384 => "E_USER_DEPRECATED: User-generated warning message(generated by using trigger_error()).",
        30719 => "E_ALL: All errors and warnings, except of level E_STRICT in PHP < 6."
    );

    /**
     * 根据错误代码获取对应的信息
     *
     * @param <int> $codemask
     *            PHP预定义的错误代码
     * @return <string> 对应的错误信息
     */
    protected static function getErrorCode($codemask) {
        if (empty($codemask)) {
            return "";
        }
        return self::$errorCodes[$codemask];
    }

    /**
     * 获取最后一次错误信息, 并对格式进行编排
     *
     * @return <string> 最后一次错误信息
     */
    protected static function getLastErrorMessage() {
        $error = error_get_last();
        if (empty($error)) {
            return "";
        }
        // var_dump($error);exit;
        $lastErrorMsg = "last error: [type]=(" . $error["type"] . ")" . self::getErrorCode($error["type"]) . "\r\n";
        $lastErrorMsg .= "\t[message]=" . $error["message"] . "\r\n";
        $lastErrorMsg .= "\t[file]=" . $error["file"] . " (" . $error["line"] . ")\r\n";
        return $lastErrorMsg;
    }

    /**
     * 初始化错误日志的信息和格式
     */
    private static function getFormat($msg) {
        $txt = "\r\n" . "time: " . date("Y-m-d H:i:s") . "\r\n";
        if (!empty($_SERVER)) {
            $txt .= "server ip: " . $_SERVER['SERVER_ADDR'] . "\r\n";
            $txt .= "user ip: " . Util_Http::getIP() . "\r\n";
            $txt .= "browser: " . Util_Http::getBrowserInfo(true) . "\r\n";
            $txt .= "http url : " . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"] . "\r\n";
        }
        if (!empty($msg) && is_array($msg)) {
            $msg = json_encode($msg);
        }
        $txt .= empty($msg) ? "" : $msg . "\r\n";
        return $txt;
    }

    private static function formatMsg($params) {
        $msgInfo = $baseInfo = array();
        $baseInfo = array(
            date('c'),
            'API',
            $_SERVER['SERVER_ADDR'],
            'API_SERVICE',
        );
        if ($params['type'] == 'errorlog') {
            unset($params['type']);
            $msgInfo = $params;
        } else {
            $msgInfo['method'] = $params['path'];
            $msgInfo['identity'] = $params['identity'];
            $msgInfo['httpUrl'] = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            $msgInfo['content'] = $params['msg'];
            $msgInfo['paramList'] = $_SERVER['REQUEST_URI'];
            $msgInfo['code'] = $params['code'];
            $msgInfo['OPID'] = $params['OPID'];
        }

        $msg = '';
        foreach ($msgInfo as $k => $v) {
            $msg .= $k . '=' . urlencode($v) . "\t";
        }
        $msg = trim($msg, "\t");
        $msg = implode("\t", $baseInfo) . "\t" . $msg . "\n";
        return $msg;
    }

    /**
     * 将日志写入文件，所有写日志的函数最终都会调用此私有函数
     *
     * @param <string> $type
     *            日志的类型，主要用于对日志进行分类，比如数据库日志放入db目录下，$type的值就是db
     * @param <string> $msg
     *            日志信息
     * @return <bool> 是否写入成功，是返回true，否则返回false
     */
    private static function write($type, $msg, $appendLastError = false, $status = "", $format = false) {
        if (empty($type)) {
            return false;
        }
        $status = empty($status) ? "" : DIRECTORY_SEPARATOR . $status;
        $sysConfig = Yaf_Registry::get('sysConfig');
        if (!$sysConfig->application->log->path) {
            return false;
        }
        $type = explode("_", $type);
        $path = $sysConfig->application->log->path . date("Y-m") . DIRECTORY_SEPARATOR . date("d") . DIRECTORY_SEPARATOR;
        if ($status) {
            $path .= $status . DIRECTORY_SEPARATOR;
        }
        if (count($type) > 1) {
            for ($i = 0; $i < count($type) - 1; $i++) {
                $path .= $type[$i] . DIRECTORY_SEPARATOR;
            }
        }

        $file = end($type) . ".txt";
        $filepath = $path . $file;
        // 创建目录
        $isCreated = Util_Directory::CreateServerFolder($path);

        // 写文件，如果文件不存在则创建新文件
        $mode = "a+";
        if (!file_exists($filepath)) {
            $mode = "w+";
        }
        if (!$fp = @fopen($filepath, $mode)) {
            return false;
        }

        // 日志内容
        if ($format) {
            $msg = self::formatMsg($msg);
        } else {
            $msg = self::getFormat($msg);
        }
        // 追加最后一次错误信息
        if ($appendLastError) {
            $msg .= self::getLastErrorMessage();
        }
        // 加锁写入数据到文件尾部
        flock($fp, LOCK_EX);
        fwrite($fp, $msg);
        // 解锁关闭文件
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    /**
     * 用于记录用户自定义的错误信息，置于Log目录下的sys_error文件
     *
     * @param <string> $msg
     *            要写入日志的信息
     * @param <bool> $appendLastError
     *            是否追加最后一次错误的信息，默认为false
     * @return <bool> 是否写入成功，是返回true，否则返回false
     */
    public static function writeSysErrorLog($errno, $msg, $errorfile, $errline, $appendLastError = true) {
        if (empty($msg) || trim($msg) == "") {
            return FALSE;
        }
        $msg = "file: " . $errorfile . ' on line ' . $errline . "\r\n" . self::$errorCodes[$errno] . "\r\n" . $msg;
        $msg = array(
            'type' => 'errorlog',
            'errfile' => $errorfile,
            'errline' => $errline,
            'errno' => $errno,
            'errmsg' => $msg,
        );
        return self::write("sys_error", $msg, false, '', false);
    }

    /**
     * 自定义日志文件名和内容
     *
     * @param string $name
     *            日志文件名
     * @param <string> $msg
     *            日志内容
     * @return <bool> 是否写入成功，是返回true，否则返回false
     */
    public static function writeLog($name, $msg, $status = "", $paramList = array()) {
        if (empty($msg) || trim($msg) == "") {
            return FALSE;
        }
        if (empty($name) || trim($name) == "") {
            return FALSE;
        }
        $msg = "content: " . trim($msg) . "\r\n";
        if ($paramList) {
            $msg .= "paramList:" . http_build_query($paramList);
        }
        return self::write($name, $msg, true, $status);
    }

    public static function writeOneLineLog($path, $msg) {
        return self::write($path, $msg, false, "", true);
    }

    public static function writeSimpleLog($name, $msg, $paramList = array())
    {
        if (empty($msg) || trim($msg) == "") {
            return FALSE;
        }
        if (empty($name) || trim($name) == "") {
            return FALSE;
        }
        $msg = "content: " . trim($msg) . "\r\n";
        if ($paramList) {
            $msg .= "paramList:" . http_build_query($paramList);
        }
        return self::write($name, $msg, false);
    }

}