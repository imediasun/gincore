<?php

class InfoPopover
{

    private static $instance = null;

    private $all_configs;
    /** @var  View */
    private $view;

    /**
     * @param        $text_var
     * @param string $class
     * @return mixed
     */
    public function createQuestion($text_var, $class = '')
    {
        return $this->view->renderFile('helpers/InfoPopover/question', array(
            'content' => $this->getText($text_var),
            'class' => $class
        ));
    }

    /**
     * @param $text_var
     * @return mixed
     */
    public function createOnHoverAttr($text_var)
    {
        return $this->view->renderFile('helpers/InfoPopover/onHoverAttr', array(
            'content' => $this->getText($text_var)
        ));
    }

    /**
     * @param      $text_var
     * @param bool $oneTime
     * @return string
     */
    public function createOnLoad($text_var, $oneTime = true)
    {
        if ($oneTime && !$this->oneTimePopoverEnabled($text_var)) {
            return '';
        }
        return $this->view->renderFile('helpers/InfoPopover/onLoad' . ($oneTime ? 'OneTime' : ''), array(
            'content' => $this->getText($text_var),
            'id' => $text_var
        ));
    }

    /**
     * @param      $text_var
     * @param bool $has_confirm
     * @return string
     */
    public function createInfoModal($text_var, $has_confirm = true)
    {
        if ($has_confirm && !$this->oneTimePopoverEnabled($text_var)) {
            return '';
        }
        return $this->view->renderFile('helpers/InfoPopover/infoModal', array(
            'content' => $this->getText($text_var),
            'id' => $text_var
        ));
    }

    /**
     * @param $text_var
     * @return string
     */
    private function getText($text_var){
        return h(l($text_var));
    }

    /**
     * @return array|mixed
     */
    private function getPopoverSettings()
    {
        return !empty($this->all_configs['settings']['info_popovers_settings'])
            ? json_decode($this->all_configs['settings']['info_popovers_settings'], true)
            : array();
    }

    /**
     * @param $text_var
     * @return bool
     */
    private function oneTimePopoverEnabled($text_var)
    {
        $settings = $this->getPopoverSettings();
        return empty($settings[$text_var]);
    }

    /**
     * @param      $text_var
     * @param bool $hide
     * @return bool
     */
    public function oneTimePopoverToggle($text_var, $hide = true)
    {
        if (!$this->popoverExists($text_var)) {
            return false;
        }
        $settings = $this->getPopoverSettings();
        if ($hide) {
            $settings[$text_var] = 1;
        } elseif (isset($settings[$text_var])) {
            unset($settings[$text_var]);
        }
        $json = json_encode($settings);
        $this->all_configs['db']->query("INSERT INTO {settings}(name,value,ro) "
            . "VALUES('info_popovers_settings',?:settings,1) "
            . "ON DUPLICATE KEY UPDATE value = ?:settings", array(
            'settings' => $json
        ));
        $this->all_configs['settings']['info_popovers_settings'] = $json;
    }

    /**
     * @param $text_var
     * @return bool
     */
    public function popoverExists($text_var)
    {
        return isset($this->manage_translates[$text_var]);
    }

    /**
     * @return InfoPopover|null
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            global $all_configs, $manage_translates;
            self::$instance = new self($all_configs, $manage_translates);
        }
        return self::$instance;
    }

    /**
     * InfoPopover constructor.
     * @param $all_configs
     * @param $manage_translates
     */
    private function __construct(&$all_configs, $manage_translates)
    {
        $this->all_configs = $all_configs;
        $this->manage_translates = $manage_translates;
        $this->view = new View($this->all_configs);
    }
}