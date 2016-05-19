<?php

class InfoPopover{
    
    private static $instance = null;
    
    private $all_configs;
    private $view;
    
    public function createQuestion($text_var)
    {
        $text = l($text_var);
        return $this->view->renderFile('InfoPopover/question', array(
                   'content' => $text
               ));
    }
    
    public function createOnHoverAttr($text_var)
    {
        $text = l($text_var);
        return $this->view->renderFile('InfoPopover/onHoverAttr', array(
                   'content' => $text
               ));
    }
    
    public function createOnLoad($text_var, $oneTime = true)
    {
        if($oneTime && !$this->oneTimePopoverEnabled($text_var)){
            return '';
        }else{
            $text = l($text_var);
            return $this->view->renderFile('InfoPopover/onLoad'.($oneTime?'OneTime':''), array(
                'content' => $text,
                'id' => $text_var
            ));
        }
    }
    
    public function createInfoModal($text_var, $has_confirm = true)
    {
        if($has_confirm && !$this->oneTimePopoverEnabled($text_var)){
            return '';
        }else{
            $text = l($text_var);
            return $this->view->renderFile('InfoPopover/infoModal', array(
                'content' => $text,
                'id' => $text_var
            ));
        }
    }
    
    private function getPopoverSettings(){
        $s = !empty($this->all_configs['settings']['info_popovers_settings']) 
                ? json_decode($this->all_configs['settings']['info_popovers_settings'], true) 
                    : array();
        return $s;
    }
    
    private function oneTimePopoverEnabled($text_var)
    {
        $settings = $this->getPopoverSettings();
        return empty($settings[$text_var]);
    }
    
    public function oneTimePopoverToggle($text_var, $hide = true)
    {
        if(!$this->popoverExists($text_var)){
            return false;
        }
        $settings = $this->getPopoverSettings();
        if($hide){
            $settings[$text_var] = 1;
        }elseif(isset($settings[$text_var])){
            unset($settings[$text_var]);
        }
        $json = json_encode($settings);
        $this->all_configs['db']->query("INSERT INTO {settings}(name,value,ro) "
                                       ."VALUES('info_popovers_settings',?:settings,1) "
                                       ."ON DUPLICATE KEY UPDATE value = ?:settings", array(
                                           'settings' => $json
                                       ));
        $this->all_configs['settings']['info_popovers_settings'] = $json;
    }
        
    public function popoverExists($text_var)
    {
        return isset($this->manage_translates[$text_var]);
    }
    
    private function setConfigs($all_configs, $manage_translates)
    {
        $this->all_configs = $all_configs;
        $this->manage_translates = $manage_translates;
        $this->view = new View($this->all_configs);
    }
    
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            global $all_configs, $manage_translates;
            self::$instance->setConfigs($all_configs, $manage_translates);
        }
        return self::$instance;
    }
    private function __construct(){}
}