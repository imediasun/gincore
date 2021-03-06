<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

require_once 'inc_config.php';
require_once 'inc_func.php';
require_once 'inc_settings.php';

$phone = '';
$code = '';
$log = '';
$data = '';

#FreePBX
$phone = isset($_GET['t']) ? trim($_GET['t']) : $phone;
$code = isset($_GET['code']) ? trim($_GET['code']) : $code;


#zadarma API
if (isset($_GET['zd_echo'])) exit($_GET['zd_echo']);
$phone = isset($_POST['caller_id']) ? trim($_POST['caller_id']) : $phone;


#telfin
$phone = isset($_GET['CallerIDNum']) ? trim($_GET['CallerIDNum']) : $phone;
$phone = isset($_POST['CallerIDNum']) ? trim($_POST['CallerIDNum']) : $phone;


#mangosip.ru vpbx json
if (isset($_POST['vpbx_api_key']) && isset($_POST['json'])) {
    $arr = json_decode($_POST['json'], true);
    if ($arr['call_state'] == 'Appeared' 
            && isset($arr['from']['number']) 
            //&& isset($arr['from']['taken_from_call_id'])
            ) {
        $phone = $arr['from']['number'];
        if(preg_match('#sip:(.*?)@#', $phone, $id)){
            $phone = $id[1];
        }
        $log = 'mangosip.ru phone: ' . $phone;

    }
}

#binotel - не верный?
#if (isset($_POST['requestType']) && $_POST['requestType'] == 'gettingCallSettings'){
#    $phone = isset($_POST['srcNumber']) ? trim($_POST['srcNumber']) : $phone;
#}

//Binotel
if (isset($_POST['callType']) && $_POST['callType'] == '0'
        && isset($_POST['requestType']) && $_POST['requestType'] == 'receivedTheCall') {
    $phone = $_POST['srcNumber'];
    $log = 'Binotel, phone: ' . $phone;
}



#FreePBX, @TODO set $code to settings
if($phone && $code == 's9djg0s3kc'){
    $call_sercie = get_service('crm/calls');
    $call_sercie->create_call_by_phone($phone, 0);
    exit;
}

#Zadarma
if ($phone && $_SERVER['REMOTE_ADDR'] == '185.45.152.42') {
    $call_sercie = get_service('crm/calls');
    $call_sercie->create_call_by_phone($phone, 0);
    exit;
}

//Uiscom
if (isset($_GET['event']) && $_GET['event'] == 'incoming_call') {
    $phone = $_GET['numa'];
    $log = 'Uiscom, phone: ' . $phone;
}
if (isset($_GET['direction']) && $_GET['direction'] == 'in') {
    $phone = $_GET['contact_phone_number'];
    $log = 'Uiscom, phone: ' . $phone;
}

//Binotel
if (isset($_POST['callType']) && $_POST['callType'] == '0'
        && isset($_POST['requestType']) && $_POST['requestType'] == 'receivedTheCall') {
    $phone = $_POST['srcNumber'];
    $log = 'Binotel, phone: ' . $phone;
}


//body post
if (!$phone) {
    $data = json_decode(file_get_contents('php://input'), true);
}

//phonet.com.ua
if ($data && isset($data['event']) && $data['event'] == 'call.dial') {
    $phone = $data['otherLegs'][0]['num'];
    $log = 'Phonet, phone: ' . $phone;
}


#other
if($phone){
    $call_sercie = get_service('crm/calls');
    $call_sercie->create_call_by_phone($phone, 0);
    exit;
}

exit;

mail('ragenoir@gmail.com', 'VoIP API 1', 'IP: ' . $_SERVER['REMOTE_ADDR'] . ' phone: ' . $phone
                    . '<hr>GET: <pre>' . print_r($_GET, true) . '</pre>' 
                    . '<hr>POST: <pre>' . print_r($_POST, true) .'</pre>'
                    . '<hr>' . $log
        );
