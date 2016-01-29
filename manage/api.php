<?php


ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);


if (!isset($_POST['data'])) {
    http_response_code(404);
    exit;
}

$data = json_decode($_POST['data']);


if (!$data) {
    http_response_code(404);
    exit;
}

 

require_once 'inc_config.php';
require_once 'inc_func.php';
require_once 'inc_settings.php';


#### ПРОВЕРКА КЛЮЧА НЕОБХОДИМА ТУТ
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


//if ($_GET['act'] == 'backup') {
if ($data->act == 'backup') {
    
    require_once 'services/api/backup.php';
    $bc = new BackupLocal;
    $result = $bc->startBackup($all_configs);
    
    $data=array('act' => 'backup');
    
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



#### first setup. Not used already.
//if ($data->act == 'setApiKeyAndCreateRemoteFirstUsername') {
//    //Если пустой ключ в БД и нет юзеров, значит это первоначальная установка.
//    //Устанавливаем ключ и юзера
//    
//    //echo $_POST['key'];
//    print_r($data);
//    print_r($all_configs['settings']);
//    
//    echo $data->firstUsername;
//    echo $data->firstPass;
//    exit;
//}


### return
function returnSuccess($data) {
    http_response_code(200);
    
    $data['status'] = '1';
    
    echo json_encode($data);
    return true;
    
}

function returnError($data) {
    http_response_code(200);
    
    $data['status'] = '0';
    
    echo json_encode($data);
    return true;
    
}

//header("HTTP/1.0 404 Not Found");