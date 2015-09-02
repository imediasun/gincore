<?php

function get_timezone_offset($remote_tz = 'UTC', $origin_tz = null) {
    if($origin_tz === null) {
        if(!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}

function get_admin_online(){
    global $db;
    
    $admin_time = (int)$db->query("SELECT value FROM {chat_settings} WHERE param = 'admin_online'")->el();
    $now_time = time();
    
    $diff = $now_time - $admin_time;
    
    $status = $diff < 5 ;
    
    $text_status = $status ? '<span style="color: green">онлайн</span>' 
                           : '<span style="color: red">офлайн</span>';
    return array($status, $text_status);
}

function add_new_message($cid, $message, $owner, $date, $field = 'cid'){
    global $db;
    $out = array();
    $chat = $db->query("SELECT id, state, user_name FROM {chat} WHERE ?q = ?", array($field, $cid), 'row');
    if($chat){
        if($chat['state'] < 4){
            if($message){
                $db->query("INSERT INTO {chat_messages}(message, chat_id, date, state, owner)
                            VALUES(?, ?i, ?, 0, ?i)", array($message, $chat['id'], $date, $owner));
                $out['state'] = true;
            }else{
                $out['state'] = false;
                $out['msg'] = 'Введите ваше сообщение';
            }
        }else{
            $out['state'] = false;
            $out['msg'] = 'Чат завершён';
        }
    }else{
        $out['state'] = false;
        $out['msg'] = 'Ошибка';
    }
    return $out;
}

?>