<?php

require_once __DIR__.'/../inc_config.php';
require_once __DIR__.'/../inc_func.php';
include __DIR__.'/../inc_settings.php';

$langs = get_langs();

$auth = new Auth($all_configs['db'], $langs['lang'], $langs['def_lang'], $langs['langs']);
$auth->cookie_session_name = $config['sql_tbl_prefix'].'cid';
if(!$auth->IfAuth()){
    exit('access denied');
}

$data = array('state' => false);

if(isset($_POST['service']) && isset($_POST['action'])){
    $service = get_service($_POST['service']);
    if(!is_null($service) && method_exists($service, 'ajax')){
        $data = $service->ajax($_POST);
    }
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($data);
exit;