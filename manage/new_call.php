<?php

require_once 'inc_config.php';
require_once 'inc_func.php';
require_once 'inc_settings.php';

$phone = '';
$code = '';

#FreePBX
$phone = isset($_GET['t']) ? trim($_GET['t']) : $phone;
$code = isset($_GET['code']) ? trim($_GET['code']) : $code;


#zadarma API
if (isset($_GET['zd_echo'])) exit($_GET['zd_echo']);
$phone = isset($_POST['caller_id']) ? trim($_POST['caller_id']) : $phone;


#telfin
$phone = isset($_GET['CallerIDNum']) ? trim($_GET['CallerIDNum']) : $phone;
$phone = isset($_POST['CallerIDNum']) ? trim($_POST['CallerIDNum']) : $phone;




mail('ragenoir@gmail.com', 'VoIP API 1', 'IP: ' . $_SERVER['REMOTE_ADDR'] . ' phone: ' . $phone
                                . '<hr>GET: <pre>' . print_r($_GET, true) . '</pre>' 
                                . '<hr>POST: <pre>' . print_r($_POST, true)   );


#FreePBX, @TODO set $code to settings
if($phone && $code == 's9djg0s3kc'){
    $call_sercie = get_service('crm/calls');
    $call_sercie->create_call_by_phone($phone, 0);
}

#Zadarma
if ($phone && $_SERVER['REMOTE_ADDR'] == '185.45.152.42') {
    $call_sercie = get_service('crm/calls');
    $call_sercie->create_call_by_phone($phone, 0);
}


#other
if($phone){
    $call_sercie = get_service('crm/calls');
    $call_sercie->create_call_by_phone($phone, 0);
}