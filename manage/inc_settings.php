<?php

define('ORDER_REPAIR', 0);
define('ORDER_RETURN', 1);
define('ORDER_SELL', 3);
define('ORDER_WRITE_OFF', 2);

define('USER_ACTIVATED_BY_TARIFF', 0);
define('USER_DEACTIVATED_BY_TARIFF_AUTOMATIC', 1);
define('USER_DEACTIVATED_BY_TARIFF_MANUAL', 2);

define('TRANSACTION_OUTPUT', 1);
define('TRANSACTION_INPUT', 2);
define('TRANSACTION_TRANSFER', 3);

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

$all_configs['configs'] = Configs::getInstance()->get();
$all_configs['settings'] = $all_configs['db']->query("SELECT name, value FROM {settings}")->vars();


$systemTimeZone = isset($all_configs['settings']['time_zone']) ? $all_configs['settings']['time_zone'] : '+00:00'; 
$all_configs['db']->query('SET @@session.time_zone = ?;', array($systemTimeZone))->ar();


/* определяем языки админки */
require_once 'core_langs.php';

// переводим конфиг на язык
Configs::getInstance()->set_configs();
$all_configs['configs'] = Configs::getInstance()->get();

$all_configs['oRole'] = new Role($all_configs, $dbcfg['_prefix']);

$all_configs['manageModel'] = new manageModel($all_configs);
$all_configs['suppliers_orders'] = new Suppliers($all_configs);
$all_configs['suppliers_orders']->suppliers_orders = $all_configs['settings']['currency_suppliers_orders'];
$all_configs['suppliers_orders']->currency_clients_orders = $all_configs['settings']['currency_orders'];

$all_configs['chains'] = new Chains($all_configs);