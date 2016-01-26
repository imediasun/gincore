<?php


ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

/*
 * временно убрал проверки
 * */
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

#### first setup
if ($data->act == 'setApiKeyAndCreateRemoteFirstUsername') {
    //Если пустой ключ в БД и нет юзеров, значит это первоначальная установка.
    //Устанавливаем ключ и юзера
    
    //echo $_POST['key'];
    print_r($data);
    print_r($all_configs['settings']);
    
    echo $data->firstUsername;
    echo $data->firstPass;
    exit;
}


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