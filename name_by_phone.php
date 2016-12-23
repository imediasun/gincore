<?php

include 'inc_config.php';

$phone = isset($_GET['t']) ? trim($_GET['t']) : '';

if($phone){

    $phone = substr($phone, -9);

    $query_tpl = "SELECT c.fio "
                ."FROM {clients} as c "
                ."LEFT JOIN {clients_phones} as p ON p.client_id = c.id "
                ."WHERE c.phone LIKE '%?e' OR p.phone LIKE '%?e'"
                ."LIMIT 1";
    
    $name = $db->query($query_tpl, array($phone, $phone))->el() ?: '(не указан)';
    
    header("Content-Type: text/plain; charset=utf-8"); 
    echo $name;
}