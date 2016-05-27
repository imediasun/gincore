<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log
{
    public static $log = array();

    /**
     * @param string $file
     * @return Logger
     */
    public static function open($file = '')
    {
        if (empty($file)) {
            $file = __DIR__ . '/../../logs/error.log';
        }
        if (empty(self::$log[$file])) {
            self::$log[$file] = new Logger('app');
            self::$log[$file]->pushHandler(new StreamHandler($file, Logger::WARNING));
        }
        return self::$log[$file];
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