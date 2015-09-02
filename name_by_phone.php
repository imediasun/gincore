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
    
    $name_restore = $db->query($query_tpl, array($phone, $phone))->el();
    $name_yabloko = '';
    
    // достаем имя с яблока
    if(!$name_restore){
        $y_dbcfg = array(
            'host'     => $cfg['server'],
            'username' => 'admin',
            'password' => 'DtubyiAA',
            'dbname'   => 'admin_yabloko',
            'charset'  => 'utf8',
            '_debug'   => false,
            '_prefix'  => 'yabloko_',
        );
        $y_db = go\DB\DB::create($y_dbcfg, 'mysql');
        
        $name_yabloko = $y_db->query($query_tpl, array($phone, $phone))->el();
    }
    
    $name = $name_restore ?: ($name_yabloko ?: '(не указан)');
    
    header("Content-Type: text/plain; charset=utf-8"); 
    echo $name;
}