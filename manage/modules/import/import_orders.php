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
        // тут мы имеем айди приемщиков $this->accepters и инженеров $this->engineers
//        print_r($this->accepters);
//        print_r($this->engineers);
        
        foreach($this->rows as $row){
            $order = new order();
            $id = $this->provider->get_id($row);
            $date_add = $this->provider->get_date_add($row);
            $accepter = $this->remove_whitespace($this->provider->get_accepter($row));
            if($accepter){
                $accepter_id = $this->accepters[$accepter];
            }else{
                $order->set_error(l('Не указан приемщик'));
            }
            $engineer = $this->remove_whitespace($this->provider->get_engineer($row));
            if($engineer){
                $engineer_id = $this->engineers[$engineer];
            }else{
                $order->set_error(l('Не указан инженер'));
            }
            $status_id = $this->provider->get_status_id($row);
            echo $status_id."\n";
        }
    }
    
    private function scan_accepters_and_engineers(){
        $this->accepters = array();
        $this->engineers = array();
        $not_found_accepters = array();
        $not_found_engineers = array();
        foreach($this->rows as $row){
            $accepter = $this->remove_whitespace($this->provider->get_accepter($row));
            if($accepter && !array_key_exists($accepter, $this->accepters)){
                // проверить есть ли чувак в базе, если не то добавляем в сообщение юзеру шоб добавил
                $a_id = $this->all_configs['db']->query("SELECT id FROM {users} WHERE fio = ?", array($accepter), 'el');
                if(!$a_id){
                    echo $accepter;
                    $not_found_accepters[] = $accepter;
                }
                $this->accepters[$accepter] = $a_id;
            }
            $engineer = $this->remove_whitespace($this->provider->get_engineer($row));
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
            if($not_found_accepters){
                $message .= '<label>'.l('Добавьте приемщиков').'</label>:'.
                            '<ol><li>'.implode('</li><li>', $not_found_accepters).'</li></ol>';
            }
            if($not_found_engineers){
                $message .= '<label>'.l('Добавьте инженеров').'</label>:'.
                            '<ol><li>'.implode('</li><li>', $not_found_engineers).'</li></ol>';
            }
            return array('state' => false, 'message' => $message);
        }else{
            return array('state' => true);
        }
    }
    
    private function remove_whitespace($string){
        return trim(preg_replace('/\s+/', ' ', $string));
    }
    
}