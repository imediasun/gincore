<?php

class TariffMessages
{
    private static $instance = null;
    const SUCCESS = 'success';
    const INFO = 'info';
    const DANGER = 'danger';
    const WARNING = 'warning';
    
    private $tariff;
    private $all_configs;
    private $admin;
    private $manage_translates;
    private $view;
    
    private function setTariff()
    {
        $this->tariff = Tariff::current();
    }
    private function setConfigs($all_configs, $admin, $manage_translates)
    {
        $this->all_configs = $all_configs;
        $this->admin = $admin;
        $this->manage_translates = $manage_translates;
        $this->view = new View($this->all_configs);
    }
    
    private function getFio(){
        return mb_ucfirst($this->admin['fio']?:$this->admin['login']);
    }
    
    private function usersMessage()
    {
        $current_users = Tariff::getCurrentUsers();
        $blocked_users = $this->all_configs['db']->query("SELECT count(*) FROM {users} "
                                         ."WHERE blocked_by_tariff>=1 AND avail=1")->el();
//        $blocked_users = 0;
        if($blocked_users > 0){
            // l_tariff_message_blocked_users
            $text = str_replace('%fio%', $this->getFio(), $this->manage_translates['l_tariff_message_blocked_users']);
            $text = str_replace('%blocked_users%', $blocked_users, $text);
            return $this->makeHtml($text, self::DANGER);
        }elseif($current_users == $this->tariff['number_of_users']){
            // l_tariff_message_limit_users
            $text = str_replace('%fio%', $this->getFio(), $this->manage_translates['l_tariff_message_limit_users']);
            return $this->makeHtml($text, self::INFO);
        }
        return false;
    }
    
    private function ordersMessage()
    {
        $current_orders = Tariff::getCurrentOrders(Tariff::current());
//        $current_orders = 9990;
        if(($current_orders / $this->tariff['number_of_orders']) >= 0.66){ // заказов больше чем 2/3
            $left = $this->tariff['number_of_orders'] - $current_orders;
            // l_tariff_message_limit_orders
            $text = str_replace('%fio%', $this->getFio(), $this->manage_translates['l_tariff_message_limit_orders']);
            $text = str_replace('%current_orders%', $current_orders, $text);
            $text = str_replace('%left%', $left, $text);
            return $this->makeHtml($text, self::DANGER);
        }
        return false;
    }
    
    private function tariffDateMessage()
    {
        $date_diff = date_diff(date_create(), date_create($this->tariff['period']));
        $days_left = $date_diff->days;
//        $days_left = 5;
        if($days_left <= 5){
            // l_tariff_message_date_limit
            $text = str_replace('%fio%', $this->getFio(), $this->manage_translates['l_tariff_message_date_limit']);
            $text = str_replace('%days_left%', $days_left, $text);
            return $this->makeHtml($text, self::WARNING);
        }
        return false;
    }
    
    public function getMessage()
    {
        $message = false;
        switch($this->all_configs['curmod']){
            case 'users':
                $message = $this->usersMessage();
            break;
            case 'orders':
                $message = $this->ordersMessage();
            break;
        }
        // юзеры и ордеры в приоритете, если нету то проверяем дату тарифа
        if($message === false){ 
            $message = $this->tariffDateMessage();
        }
        // если нет никаких уведомлений и есть заблок сотрудники,
        // то показываем сообщение о заблок сотрудниках везде
        if($message === false && $this->all_configs['curmod'] != 'users'){ 
            $message = $this->usersMessage();
        }
        return $message;
    }
    
    private function makeHtml($text, $type = self::SUCCESS)
    {
        return $this->view->renderFile('TariffMessages/message', array(
                   'text' => $text,
                   'type' => $type
               ));
    }
    
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->setTariff();
            global $all_configs, $ifauth, $manage_translates;
            self::$instance->setConfigs($all_configs, $ifauth, $manage_translates);
        }
        return self::$instance;
    }
    private function __construct(){}
}
