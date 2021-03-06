<?php

define('GINCORE_ROOT', __DIR__ . '/../');

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

define('DISCOUNT_TYPE_PERCENT', 1);
define('DISCOUNT_TYPE_CURRENCY', 2);

define('SALE_TYPE_QUICK', 1);
define('SALE_TYPE_ESHOP', 2);

define('DELIVERY_BY_SELF', 1);
define('DELIVERY_BY_COURIER', 2);
define('DELIVERY_BY_POST', 3);

define('CONTRACTOR_TYPE_CONTRACTOR', 1);
define('CONTRACTOR_TYPE_PROVIDER', 2);
define('CONTRACTOR_TYPE_BUYER', 3);
define('CONTRACTOR_TYPE_EMPLOYER', 4);

define('GOODS_TYPE_ITEM', 0);
define('GOODS_TYPE_SERVICE', 1);

define('CATEGORIES_TYPE_ACTIVE', 0b001);
define('CATEGORIES_TYPE_DELETED', 0b101);
define('CASHBOX_SYSTEM', 1);
define('CASHBOX_NOT_SYSTEM', 2);

define('CLIENT_IS_PERSONAL', 1);
define('CLIENT_IS_LEGAL', 2);

define('CLIENT_ACT_CALL', 1);
define('CLIENT_ACT_REQUEST', 2);
define('CLIENT_ACT_ORDER', 3);

define('ORDERS_GOODS_PRICE_TYPE_RETAIL',1);
define('ORDERS_GOODS_PRICE_TYPE_WHOLESALE',2);
define('ORDERS_GOODS_PRICE_TYPE_MANUAL',3);

define('PURCHASE_INVOICE_STATE_NEW', 0);
define('PURCHASE_INVOICE_STATE_CAPITALIZED', 1);

define('SYSTEM_CASHBOX_MAIN_ID', 1);
define('SYSTEM_CASHBOX_TRANSIT_ID', 2);
define('SYSTEM_CASHBOX_TERMINAL_ID', 3);

define('LOGISTIC_TYPE_IS_ORDER', 1);
define('LOGISTIC_TYPE_IS_ITEM', 2);

include_once 'suppliers.class.php';
include_once 'managemodel.class.php';
include_once 'chains.class.php';
include_once 'transactions.class.php';

include_once 'class_auth.php';

require_once $all_configs['sitepath'] . 'shop/access_system.class.php';
require_once 'configs.php';
require_once 'Models/Status.php';

#parse url
if ($all_configs['prefix'] != '/') {
    $request = str_replace($all_configs['prefix'], '', $_SERVER['REQUEST_URI']);
} else {
    $request = $_SERVER['REQUEST_URI'];
}

$all_configs['arrequest'] = clear_empty_inarray(explode('/', $request));

$all_configs['configs'] = Configs::getInstance()->get();
$all_configs['settings'] = $all_configs['db']->query("SELECT name, value FROM {settings}")->vars();

$systemTimeZone = isset($all_configs['settings']['time_zone']) ? $all_configs['settings']['time_zone'] : 'Europe/Kiev';
//$all_configs['db']->query('SET @@session.time_zone = ?;', array($systemTimeZone))->ar();
if (strpos($systemTimeZone, ':') !== false && strlen($systemTimeZone) <= 6) {
    list($hours, $minutes) = explode(':', $systemTimeZone);
    $seconds = $hours * 60 * 60 + $minutes * 60;
    $tz_abbr = timezone_name_from_abbr('', $seconds, 1);
    if ($tz_abbr === false) {
        $tz_abbr = timezone_name_from_abbr('', $seconds, 0);
    }
    @date_default_timezone_set($tz_abbr);
    $all_configs['db']->query("SET `time_zone`='" . $systemTimeZone . "'");
} else {
    @date_default_timezone_set($systemTimeZone);
    $all_configs['db']->query("SET `time_zone`='" . date('P') . "'");
}


/* определяем языки админки */
require_once 'core_langs.php';

// переводим конфиг на язык
Configs::getInstance()->set_configs();
$all_configs['configs'] = Configs::getInstance()->get();
$Status = new MStatus($all_configs);
$Status->setDefaultIfNeed();
$all_configs['configs']['order-status'] = $Status->getStatus(ORDER_REPAIR);
$all_configs['configs']['sale-order-status'] = $Status->getStatus(ORDER_SELL);

$all_configs['oRole'] = new Role($all_configs, $dbcfg['_prefix']);

$all_configs['manageModel'] = new manageModel($all_configs);
$all_configs['suppliers_orders'] = new Suppliers($all_configs);
$all_configs['suppliers_orders']->suppliers_orders = $all_configs['settings']['currency_suppliers_orders'];
$all_configs['suppliers_orders']->currency_clients_orders = $all_configs['settings']['currency_orders'];

$all_configs['chains'] = new Chains($all_configs);