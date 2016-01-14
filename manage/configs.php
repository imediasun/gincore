<?php

// в мастер конфиге находится основной конфиг для саас
require_once 'master_configs.php'; 

// устанавливает функцию для расширения или перезаписи конфига
//Configs::$configs_extend_function = function(&$config){
//    /*
//     * блок конфига для админки рестора
//     */
//    $config['manage-print-default-service-restore'] = true;
//    $config['manage-show-terminal-cashbox'] = true; // показать или скрыть кассу терминал
//    $config['manage-show-phones-btn'] = true; // показать или скрыть кнопку смены аварийных телефонов
//    $config['manage-active-modules'] = array('*'); // активные модуле в админке
//    $config['manage-reset-access'] = false; // доступен ли сброс в модуле дебаг
//    $config['settings-master-enabled'] = false; // мастер настрйоки при регистрации новой админки
//    $config['currencies'] = array(
//        1 => array('rutils' => array('words' => array(l('гривна'), l('гривны'), l('гривен')), 'gender' => 'female'), 'name' => l('Гривна'), 'shortName' => 'UAH', 'viewName' => l('грн.'), 'symbol' => '₴', 'currency-name' => 'grn-cash'),
//        2 => array('rutils' => array('words' => array(l('евро'), l('евро'), l('евро')), 'gender' => 'male'), 'name' => l('ЕВРО'), 'shortName' => 'EUR', 'viewName' => '€', 'symbol' => '€', 'currency-name' => ''),
//        3 => array('rutils' => array('words' => array(l('доллар'), l('доллара'), l('долларов')), 'gender' => 'male'), 'name' => l('Доллар США'), 'shortName' => 'USD', 'viewName' => '$', 'symbol' => '$', 'currency-name' => 'price'),
//        4 => array('rutils' => array('words' => array(l('рубль'), l('рубля'), l('рублей')), 'gender' => 'male'), 'name' => l('Российский рубль'), 'shortName' => 'RUB', 'viewName' => l('руб.'), 'symbol' => '<i class="fa fa-rub"></i>', 'currency-name' => ''),
//    );
//};
