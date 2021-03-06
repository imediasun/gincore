<?php

define('STDIN', fopen("php://stdin", "r"));

if (!isset($_POST['data'])) {
    http_response_code(404);
    exit;
}

$data = json_decode($_POST['data']);

if (isset($data->subdomain)) {
    $subdomain = $data->subdomain;
}

if (!$data) {
    http_response_code(404);
    exit;
}

require_once 'inc_config.php';
require_once 'inc_func.php';
require_once 'inc_settings.php';


#### ПРОВЕРКА КЛЮЧА 
if (!isset($all_configs['settings']['api_key'])) {
    $d['message'] = 'Client API key is not set.';
    returnError($d);
    exit;
}

if ($data->key != $all_configs['settings']['api_key']) {
    $d['message'] = 'API key is invalid.';
    returnError($d);
    exit;
}


if ($data->act == 'getSystemInfo') {
    /* Получаем инфо о системе:
     * +Количество заказов, дата последнего заказа
     * +Кол-во клиентов, дата добавления последнего
     * +Количество сотрудников
     *
     * +Пройден мастер настройки.
     * +Указанные при регистрации, телефон, эл. адр., название компании,
     * +Выбранная страна, язык системы
     * 
     * +Запчастей на складе (всего)
     * +Количество касс
     * Дата посдежнего заказа поставщика
     * ...
     * 
     */

    $result = true;
    $info = array();

    $info['orders']['count'] = $all_configs['db']->query("SELECT count(*) FROM {orders}")->el();
    $info['orders']['date_last'] = $all_configs['db']->query("SELECT date_add FROM {orders} ORDER BY date_add DESC LIMIT 1")->el();
    $info['clients']['count'] = $all_configs['db']->query("SELECT count(*) FROM {clients}")->el();
    $info['clients']['date_last'] = $all_configs['db']->query("SELECT date_add FROM {clients} ORDER BY date_add DESC LIMIT 1")->el();
    $info['users']['count'] = $all_configs['db']->query("SELECT count(*) FROM {users}")->el();
    $info['warehouses_goods_items']['count_all'] = $all_configs['db']->query("SELECT count(*) FROM {warehouses_goods_items}")->el();
    $info['cashboxes']['count'] = $all_configs['db']->query("SELECT count(*) FROM {cashboxes}")->el();
    $info['users']['date_last'] = date("Y-m-d H:i:s", $all_configs['db']->query("SELECT uxt FROM {users} ORDER BY uxt DESC LIMIT 1")->el());

    $data = array(
        'act' => 'getSystemInfo',
        'settings' => $all_configs['settings'],
        'info' => $info,
    );
    
 /*   
    //один раз прописать всем поддомен
    if (!isset($all_configs['settings']['registered_site_name']) && isset($subdomain)) {
        $all_configs['db']->query("INSERT INTO {settings} (`name`, `value`, `ro`, `title`) 
        VALUES ('registered_site_name', ?, 0, 'Название поддомена')
        ON DUPLICATE KEY UPDATE `value` = ? ", array($subdomain, $subdomain));
    }
*/
    
    $data['message'] = '';
    if ($result) {
        returnSuccess($data);
    } else {
        returnError($data);
    }
    exit;
}

if ($data->act == 'backup') {

    require_once 'services/api/backup.php';
    $bc = new BackupLocal;
    $result = $bc->startBackup($all_configs);

    $data = array('act' => 'backup');

    if ($result) {
        $data['message'] = 'Backup created';
        returnSuccess($data);
    } else {
        $data['message'] = 'Backup NOT created';
        returnError($data);
    }
    exit;
}


#Ручной запуск SQL
if ($data->act == 'runManualSQLQuery') {

    /*
    $all_configs['db']->query("INSERT INTO `restore4_settings` (`name`, `value`, `ro`, `title`) 
        VALUES ('api_key', ?, 1, 'Api key')
        ON DUPLICATE KEY UPDATE `value` = ?", array($data->key, $data->key))->ar();
    */

    if (isset($data->query)) {
        //$sqlResult = $all_configs['db']->plainQuery($data->query);

        //Распаковать бд
        $sql = mysqli_connect($dbcfg['host'], $dbcfg['username'], $dbcfg['password'], '');
        if ($sql) {
            mysqli_query($sql, "USE " . $dbcfg['dbname']); // sql inj :(

            $sqlResult = mysqli_multi_query($sql, 'set names utf8;' . $data->query);

        } else {
            $sqlResult = 'SQL import fail';
        }

        mysqli_close($sql);


    }

    $d['message'] = $data->id . ': Update SQL. System ' . $_SERVER['HTTP_HOST']
        . ' Result: ' . $sqlResult;
    returnSuccess($d);
}


#Ручной апдейт файлов
if ($data->act == 'runManualUpdateFiles') {
    if (!file_exists($all_configs['sitepath'] . 'update/gincore.zip')
        || !file_exists($all_configs['sitepath'] . 'update/updatesources.php')
    ) {
        returnError(array('message' => 'Uploaded files not found.'));
    }

    require_once $all_configs['sitepath'] . 'update/updatesources.php';

    $res = unpackFiles();


    if ($data->migrate == '1') {
        //require __DIR__ . '/../gincore/bootstrap/autoload.php';
        $app = require_once __DIR__ . '/../gincore/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

        $status = $kernel->handle(
            $input = new Symfony\Component\Console\Input\ArgvInput(
                array(
                    'api.php',
                    'migrate',
                    '--force'
                )), $output = new Symfony\Component\Console\Output\BufferedOutput
        );

        $kernel->terminate($input, $status);
        $res .= ' Migration: ' . $output->fetch();
    }

    returnSuccess(array('message' => $res));

}
if ($data->act == 'change-tariff') {
    $ar = db()->query("DELETE FROM {settings} WHERE name='tariff'", array())->ar();
    db()->query('UPDATE {users} SET blocked_by_tariff=0');
    returnSuccess(array('result' => $ar));
}
if ($data->act == 'set-time-zone') {
    db()->query("INSERT INTO {settings} (`name`, `value`, `ro`, `title`, `description`) 
                VALUES ('time_zone', ?, 0, 'Временная зона', ''Временная зона, например Europe/Kiev'')
                ON DUPLICATE KEY UPDATE value=?", array($data->time_zone,$data->time_zone));
    returnSuccess(array('result' => 1));
}


### return
function returnSuccess($data)
{
    http_response_code(200);
    $data['status'] = '1';
    echo json_encode($data);
}

function returnError($data)
{
    http_response_code(200);
    $data['status'] = '0';
    echo json_encode($data);
}

//header("HTTP/1.0 404 Not Found");
