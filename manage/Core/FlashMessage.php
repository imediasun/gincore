<?php

require_once __DIR__ . '/Session.php';

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
        $flash = Session::getInstance()->get('flash');
        if (empty($flash)) {
            $flash = array(
                $type => $value
            );
        } else {
            $flash[$type] = $value;
        }
        Session::getInstance()->set('flash', $flash);
    }

    /**
     * @param $type
     * @return string
     */
    public static function get($type)
    {
        $flash = Session::getInstance()->get('flash');
        return isset($flash[$type]) ? $flash[$type] : '';
    }

    /**
     * @param $type
     */
    public static function clear($type)
    {
        $flash = Session::getInstance()->get('flash');
        if($flash[$type]) {
            unset($flash[$type]);
            Session::getInstance()->set('flash', $flash);
        }
    }

    /**
     * @return string
     */
    public static function show()
    {
        $out = '';
        $flash = Session::getInstance()->get('flash');
        if (!empty($flash) && is_array($flash)) {
            foreach ($flash as $type => $message) {
                if (!empty($message)) {
                    $out .= "<div class='alert alert-{$type}'><a class='close' title='close' aria-label='close' data-dismiss='alert' href='#'>×</a><strong>" . l(ucfirst($type)) . "</strong>&nbsp;{$message}</div>";
                    self::clear($type);
                }
            }
            $out = "<div class='flash-messages'>{$out}</div>";
        }
        return $out;
    }
}
