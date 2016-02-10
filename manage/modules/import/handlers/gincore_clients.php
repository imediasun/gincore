<?php

class gincore_clients extends import_helper{
    
    private $cols = array(
        0 => 'id',
        1 => 'phones',
        2 => 'email',
        3 => 'fio',
        4 => 'legal_address',
        5 => 'date_add',
    );
    
    function get_cols(){
        return $this->cols;
    }
    
    function get_phones($data){
        return explode(',', $data[1]);
    }
    
    function get_fio($data){
        return $data[3];
    }
    
    function get_email($data){
        return $data[2];
    }
    
    function get_address($data){
        return $data[4];
    }
    
}