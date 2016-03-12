<?php

class FlashMessage
{
    const SUCCESS = 'success';
    const INFO = 'info';
    const DANGER = 'danger';
    const WARNING = 'warning';

    /**
     * @param $value
     * @param $type
     */
    public static function set($value, $type = self::SUCCESS)
    {
        session_start();
        $_SESSION['flash'][$type] = $value;
    }

    /**
     * @param $type
     * @return string
     */
    public static function get($type)
    {
        session_start();
        return isset($_SESSION['flash'][$type]) ? $_SESSION['flash'][$type] : '';
    }

    /**
     * @param $type
     */
    public static function clear($type)
    {
        session_start();
        if (isset($_SESSION['flash'][$type])) {
            unset($_SESSION['flash'][$type]);
        }
    }

    /**
     * @return string
     */
    public static function show()
    {
        $out = '';
        if (isset($_SESSION['flash']) && is_array($_SESSION['flash'])) {
            foreach ($_SESSION['flash'] as $type => $message) {
                if (!empty($message)) {
                    $out .= "<div class='alert alert-{$type}'><a class='close' title='close' aria-label='close' data-dismiss='alert' href='#'>×</a><strong>" . l(ucfirst($type)) . "</strong>{$message}</div>";
                    self::clear($type);
                }
            }
            $out = "<div class='flash-messages'>{$out}</div>";
        }
        return $out;
    }
}
