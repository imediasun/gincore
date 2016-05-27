<?php

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
        if (empty(self::$logs[$file])) {
            self::$logs[$file] = new Logger('app');
            self::$logs[$file]->pushHandler(new StreamHandler($file, Logger::WARNING));
        }
        return self::$logs[$file];
    }

    /**
     * @param        $message
     * @param string $file
     */
    public static function error($message, $file = '')
    {
        Log::open($file)->error($message);
    }

    /**
     * @param        $message
     * @param string $file
     */
    public static function warning($message, $file = '')
    {
        Log::open($file)->warning($message);
    }
}