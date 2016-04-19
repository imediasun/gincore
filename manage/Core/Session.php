<?php

class Session
{
    /** @var Session */
    private static $instance = null;

    /**
     * @return Session|null
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->check($key) ? $_SESSION[$key] : null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function check($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @param $key
     */
    public function clear($key)
    {
        if ($this->check($key)) {
            unset($_SESSION[$key]);
        }
    }

    private function __construct()
    {
        @session_start();
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }
}