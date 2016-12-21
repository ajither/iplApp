<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief
 */

namespace library\IPL\Common;

class Logger {

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function access_logger($data) {
        self::log(print_r($data, true), getenv('LOG.ACCESS'));
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function debug_logger($data) {
        self::log(print_r($data, true), getenv('LOG.DEBUG'));
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function error_logger($data) {
        self::log(print_r($data, true), getenv('LOG.ERROR'));
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    private static function log($data, $file) {
        try {
            $filename = $file . "_" . date("Y-m-d") . ".log";
            $fp = fopen($filename, "a");
            chmod($filename, 0777);
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
        fwrite($fp, date("Y-m-d H:i:s") . "-" . $data . PHP_EOL);
        fclose($fp);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function setAccessLogString($request) {
        $serevrParams = $request->getServerParams();
        $userAgent = "-";
        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $userAgent = $_SERVER ['HTTP_USER_AGENT'];
        }
        $_SESSION['access_log_string'] = '[' . $serevrParams['REMOTE_ADDR'] . ']--['
                . $serevrParams['REQUEST_METHOD'] . ' ' . $serevrParams['REQUEST_URI']
                . ']--[' . $userAgent . ']';
    }

}
