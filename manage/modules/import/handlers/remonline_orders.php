<?php

class remonline_orders extends import_helper{
    
    private $cols = array(
        0 => 'Принят',
        1 => 'Принял',
        2 => '№ заказа',
        3 => 'Тип заказа',
        4 => 'Статус',
        5 => 'Имя клиента',
        6 => 'Телефон',
        7 => 'Адрес',
        8 => 'Email',
        9 => 'Тип устройства',
        10 => 'Бренд',
        11 => 'Модель',
        12 => 'Серийный номер',
        13 => 'Комплектация',
        14 => 'Внешний вид',
        15 => 'Неисправность со слов заказчика',
        16 => 'Крайний срок',
        17 => 'Ориентировочная цена',
        18 => 'Аванс',
        19 => 'Срочно',
        20 => 'Готов',
        21 => 'Инженер',
        22 => 'Выдан',
        23 => 'Выдал',
        24 => 'Себестоимость запчастей',
        25 => 'Оплачено',
        26 => 'Заметки приемщика',
        27 => 'Заметки исполнителя',
        28 => 'Выполненные работы',
        29 => 'Установленные запчасти',
        30 => 'Вердикт / рекомендации клиенту',
        31 => 'Забрать у клиента',
        32 => 'Доставить клиенту',
    );
    
    private $statuses = array(
        'Новый' => 'order-status-new',
        'Согласовано, передано в работу' => 'order-status-work',
<<<<<<< HEAD
        'Мастер назначен' => 'order-status-waits',
=======
        'Мастер назначен' => 'order-status-wait',
>>>>>>> 795d70facdfa34c61ee139f827b0bd474b2fe0bc
        'Клиент отказался' => 'order-status-refused',
        'Не починится' => 'order-status-unrepairable',
//        'order-status-nowork',
//        'order-status-issued',
//        'order-status-rework',
        'На выдачу' => 'order-status-ready',
//        'order-status-service',
        'Согласовать с клиентом' => 'order-status-agreement'
    );
    
    function __construct($all_configs){
        $this->all_configs = $all_configs;
    }
    
    function get_id($data){
        preg_match_all('/A([0-9]+)\/?[0-9]*/', $data[2], $ids);
        $id = $ids[1][0];
        return $id;
    }
    
<<<<<<< HEAD
    function get_client_fio($data){
        return $data[5];
    }
    
    function get_client_phone($data){
        return $data[6];
    }
    
    function get_status_id($data){
        $status_id = $this->all_configs['configs'][$this->statuses[$this->remove_whitespace($data[4])]];
=======
    function get_status_id($data){
        $status_id = $this->all_configs['configs'][$this->statuses[$data[4]]];
>>>>>>> 795d70facdfa34c61ee139f827b0bd474b2fe0bc
        return $status_id;
    }
    
    function get_date_add($data){
        return $data[0];
    }
    
    function get_accepter($data){
        return $data[1];
    }
    
    function get_engineer($data){
        return $data[21];
    }
    
    function get_address($data){
        return $data[7];
    }
    
    function get_category($data){
        return $data[9];
    }
    
    function get_device($data){
        return $data[10].' '.$data[11];
    }
    
    function get_serial($data){
        return $data[12];
    }
    
    function get_equipment($data){
        return $data[13];
    }
    
    function get_appearance($data){
        return $data[14];
    }
    
    function get_defect($data){
        return $data[15];
    }
    
    function get_date_end($data){
        return $data[16];
    }
    
    function get_summ($data){
        return $data[17];
    }
    
    function get_summ_prepaid($data){
        return $data[18];
    }
    
    function get_comments($data){
        $comments = array(
            'acceptor' => array(),
            'engineer' => array()
        );
        if($data[26]){
            $comments['acceptor'][] = $data[26];
        }
        if($data[27]){
            $comments['engineer'][] = $data[27];
        }
        if($data[28]){
            $comments['engineer'][] = $data[28];
        }
        if($data[31] === 'true'){
            $comments['acceptor'][] = lq('Забрать у клиента');
        }
        return $comments;
    }
    
}