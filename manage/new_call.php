<?php

require_once 'inc_config.php';
require_once 'inc_func.php';

$phone = isset($_GET['t']) ? trim($_GET['t']) : '';
$code = isset($_GET['code']) ? trim($_GET['code']) : '';

if($phone && $code == 's9djg0s3kc'){
    
    $call_sercie = get_service('crm/calls');
    $call_sercie->create_call_by_phone($phone, 0);
    
}