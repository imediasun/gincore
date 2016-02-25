<?php

include_once 'suppliers.class.php';
include_once 'managemodel.class.php';
include_once 'chains.class.php';

include_once 'class_auth.php';

require_once $all_configs['sitepath'] . 'shop/access_system.class.php';
require_once 'configs.php';

#parse url
if($all_configs['prefix'] != '/') {
    $request = str_replace($all_configs['prefix'], '', $_SERVER['REQUEST_URI']);
} else {
    $request = $_SERVER['REQUEST_URI'];
}

$all_configs['arrequest'] = clear_empty_inarray(explode('/', $request));

$all_configs['configs'] = Configs::get();
$all_configs['settings'] = $all_configs['db']->query("SELECT name, value FROM {settings}")->vars();

$all_configs['db']->query('SET @@session.time_zone = ?;', array($all_configs['settings']['time_zone']))->ar();


/* определяем языки админки */
require_once 'core_langs.php';

// переводим конфиг на язык
Configs::getInstance()->set_configs();
$all_configs['configs'] = Configs::get();

$all_configs['oRole'] = new Role($all_configs, $dbcfg['_prefix']);

$all_configs['manageModel'] = new manageModel($all_configs);
$all_configs['suppliers_orders'] = new Suppliers($all_configs);
$all_configs['suppliers_orders']->suppliers_orders = $all_configs['settings']['currency_suppliers_orders'];
$all_configs['suppliers_orders']->currency_clients_orders = $all_configs['settings']['currency_orders'];

$all_configs['chains'] = new Chains($all_configs);