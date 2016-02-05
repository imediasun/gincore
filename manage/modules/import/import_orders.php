<?php

class import_orders{
    
    private $orders_objects = array();
    private $provider; // 
    
    function __construct($all_configs, $provider){
        $this->all_configs = $all_configs;
        $this->provider = $provider;
    }
    
    function run($rows){
        $this->rows = $rows;
        
        $scan = $this->scan_accepters_and_engineers();
        if(!$scan['state']){
            return $scan;
        }
    }
    
    private function scan_accepters_and_engineers(){
        $this->accepters = array();
        $this->engineers = array();
        $not_found_accepters = array();
        $not_found_engineers = array();
        foreach($this->rows as $row){
            $accepter = trim($this->provider->get_accepter($row));
            if($accepter && !array_key_exists($accepter, $this->accepters)){
                // проверить есть ли чувак в базе, если не то добавляем в сообщение юзеру шоб добавил
                $a_id = $this->all_configs['db']->query("SELECT id FROM {users} WHERE fio = ?", array($accepter), 'el');
                if(!$a_id){
                    $not_found_accepters[] = $accepter;
                }
                $this->accepters[$accepter] = $a_id;
            }
            $engineer = trim($this->provider->get_engineer($row));
            if($engineer && !array_key_exists($engineer, $this->engineers)){
                // проверить есть ли чувак в базе, если не то добавляем в сообщение юзеру шоб добавил
                $e_id = $this->all_configs['db']->query("SELECT id FROM {users} WHERE fio = ?", array($engineer), 'el');
                if(!$e_id){
                    $not_found_engineers[] = $engineer;
                }
                $this->engineers[$engineer] = $e_id;
            }
        }
        if($not_found_accepters || $not_found_engineers){
            $message = '';
            $message .= 'Добавьте приемщиков: <pre>'.print_r($not_found_accepters, true).'</pre>';
            $message .= 'Добавьте инженеров: <pre>'.print_r($not_found_engineers, true).'</pre>';
            return array('state' => false, 'message' => $message);
        }else{
            return array('state' => true);
        }
    }
    
}