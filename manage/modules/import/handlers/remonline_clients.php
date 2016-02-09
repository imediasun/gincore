<?php

class remonline_clients extends import_helper{
    
    private $cols = array(
        0 => 'Имя',
        1 => 'Телефон',
        2 => 'Адрес',
        3 => 'Email',
    );
    
    function get_phones($data){
        return explode(',', $data[1]);
    }
    
    function get_fio($data){
        return $data[0];
    }
    
    function get_email($data){
        return $data[3];
    }
    
    function get_address($data){
        return $data[2];
    }
    
}