<?php

include_once 'suppliers.class.php';
include_once 'managemodel.class.php';
include_once 'chains.class.php';

include_once 'class_auth.php';

require_once 'langs.php';
require_once $all_configs['sitepath'] . 'shop/access_system.class.php';
require_once $all_configs['sitepath'] . 'configs.php';

#parse url
if($all_configs['prefix'] != '/') {
    $request = str_replace($all_configs['prefix'], '', $_SERVER['REQUEST_URI']);
} else {
    $request = $_SERVER['REQUEST_URI'];
}

$all_configs['arrequest'] = clear_empty_inarray(explode('/', $request));

$all_configs['configs'] = Configs::get();
$all_configs['settings'] = $all_configs['db']->query("SELECT name, value FROM {settings}")->vars();
$all_configs['oRole'] = new Role($all_configs, $config['sql_tbl_prefix']);

$all_configs['manageModel'] = new manageModel($all_configs);
$all_configs['suppliers_orders'] = new Suppliers($all_configs);
$all_configs['chains'] = new Chains($all_configs);
