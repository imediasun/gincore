<?php

include '../inc_config.php';
include '../class_visitors.php';
include '../inc_func.php';
include './inc_func.php';
include '../inc_sources.php';
$settings=$db->query("SELECT name, value FROM {settings}")->vars();

header("Content-Type: application/json; charset=UTF-8");

$act = isset($_GET['act']) ? $_GET['act'] : '';
$out = array();

if($act == 'new_consult'){
//    $from = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'null';
    
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $device = isset($_POST['device']) ? trim($_POST['device']) : '';
//    $ip = $_SERVER['REMOTE_ADDR'];

    $is_credit = isset($_POST['credit']) ? 1 : 0;
    $service = isset($_POST['service']) ? htmlspecialchars(urldecode($_POST['service'])) : '';
    
    $phone = preg_replace('~[^0-9]+~', '', $phone);
    date_default_timezone_set('Europe/Kiev');
    $time = date('Y-m-d H:i:s', time());

    Visitors::getInstance()->init_visitors();
    $code = Visitors::getInstance()->get_code(true);

    if($phone){
       
        $source = $utm_source == 'city' ? 'Наружка' : 'прямой заход';
        $source = $utm_source == 'adw' ? 'КМС' : $source;
        
        
        $msgtxt = 'Посетитель сайта '.$settings['site_name'].'
            хочет проконсультироваться'.($is_credit ? ' по кредиту на услугу "'.$service.'"' : '').'<br><br>
            Время заявки: '.htmlspecialchars($time).' <br><br>
            Телефон: '.htmlspecialchars($phone).' <br><br>
            Код на скидку: '.$code.' <br><br>
            Устройство: '.htmlspecialchars($device).' <br><br><br>
                
            Источник: <b>'.$source.'</b>
        ';
        
        send_mail($settings['consult_email'], 'Проконсультируйте'.($is_credit ? ' по кредиту' : '').' посетителя сайта '.$settings['site_name'], $msgtxt);
        
        if($settings['sms_telephones']){
            include '../class_sms.php';
            $admin_sms_text = $settings['site_name']
                    .' консультация '
                    .htmlspecialchars($phone)
                    .' '.htmlspecialchars($device);
            $sms_len = strlen($admin_sms_text);
            if($sms_len>70)
                $admin_sms_text = str_replace (' консультация', '', $admin_sms_text);
            $cnt_sms = $sms_len / 70;
            $cnt_sms = is_float($cnt_sms) ? (int)$cnt_sms + 1 : $cnt_sms;
            $tel = explode(',', $settings['sms_telephones']);
            $sms = new Sms();
//            $sms->SendSms($admin_sms_text, $settings['sms_telephones'], $cnt_sms * count($tel));
        }
        
        $out['state'] = true;
        $out['msg'] = 'that\'s Ok';
    }else{
        $out['state'] = false;
        $out['msg'] = "Укажите ваш телефон";
    }
}


echo json_encode($out);

?>
