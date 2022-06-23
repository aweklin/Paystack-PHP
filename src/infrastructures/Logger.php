<?php

namespace Aweklin\Paystack\Infrastructures;

use Aweklin\Paystack\Abstracts\ILogger;
use Aweklin\Paystack\Infrastructures\Utility;

final class Logger implements ILogger {

    private $_folder;

    private static $_instance;

    private function __construct() {}

    private static function _getInstance() : Logger {
        if (!isset(self::$_instance) || (isset(self::$_instance) && !self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function log(string $text) : void {
        try {
            if (!Utility::isEmpty($text)) {
                $today = new \DateTime('now', new \DateTimeZone('GMT+1'));
                $todayWithTimeFormatted = $today->format('Y-m-d H:i:s');
                $todayFormatted = $today->format('Y-m-d');
                unset($today);

                $logContent = 
                    PHP_EOL . 'Date/Time (GMT+1): ' . $todayWithTimeFormatted . 
                    PHP_EOL . $text . 
                    PHP_EOL . PHP_EOL . '=========================================================' . PHP_EOL;

                $filePath = '';
                if (\defined('APP_PAYSTACK_LOG_LOCATION')) {
                    $filePath = constant('APP_PAYSTACK_LOG_LOCATION');
                } else {
                    $filePath = dirname(__FILE__, 2) . DS . 'logs' . DS . $this->_folder;
                    if (!\file_exists($filePath)) {
                        \mkdir($filePath, 0777, true);
                    }
                }
                $fileName = $filePath . DS . $todayFormatted . '.log';
                //echo "Log path: {$fileName}";
                $handle = @\fopen($fileName, 'a');
                \fwrite($handle, $logContent);
                \fclose($handle);
            }
        } catch (\Exception $e) {}  // nothing to be done if logging fails
    }

    private function _setLogFolder(string $folder) : Logger {
        $this->_folder = $folder;
        return $this;
    }

    public static function logResponse(string $text) : void {
        self::_getInstance()->_setLogFolder('requests')->log($text);
    }

    public static function logError(string $text) : void {
        self::_getInstance()->_setLogFolder('errors')->log($text);
    }

    public static function sendTelemetry(string $url) : void {
        self::logError($url);
    }

}