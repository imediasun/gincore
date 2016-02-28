<?php

class import_helper{
    
    // форматируем дату
    protected function format_date($date){
        $date = str_replace('/', '-', $date);
        return date('Y-m-d H:i:s', strtotime($date));
    }
    
    // чистит телефон, оставляет только цифры
    protected function clear_phone($phone){
        return preg_replace("/[^0-9\+]/", "", $phone);
    }
    
    // чистит лишние пробелы между словами + с конца и начала
    protected function remove_whitespace($string){
        return trim(preg_replace('/\s+/', ' ', $string));
    }
    
}