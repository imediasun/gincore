<?php

require_once __DIR__.'/../../gincore/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log
{
    protected static $logs = array();

    /**
     * @param string $file
     * @return Logger
     */
    public static function open($file = '')
    {
        if (empty($file)) {
            $file = __DIR__ . '/../../logs/error.log';
        }
        if (empty(self::$logs[$file]) && is_writable(dirname($file))) {
            self::$logs[$file] = new Logger('app');
            self::$logs[$file]->pushHandler(new StreamHandler($file, Logger::WARNING));
        }
        return empty(self::$logs[$file]) ? null : self::$logs[$file];
    }

    /**
     * @param        $message
     * @param string $file
     */
    public static function error($message, $file = '')
    {
        $log = Log::open($file);
        if (!empty($log)) {
            $log->error($message);
        }
    }

    /**
     * @param        $message
     * @param string $file
     */
    public static function warning($message, $file = '')
    {
        $log = Log::open($file);
        if (!empty($log)) {
            $log->warning($message);
        }
    }
}