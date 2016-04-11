<?php

require_once 'inc_config.php';
require_once 'inc_func.php';
require_once 'inc_settings.php';

function make_request($action, $data = array()){
    global $all_configs;
    $api_key = !empty($all_configs['settings']['api_key']) ? $all_configs['settings']['api_key'] : NULL;
    if($api_key){
        $data['act'] = $action;
        $data['key'] = $api_key;
        $data['signature'] = md5($api_key.implode(';', $data).$api_key);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://192.168.1.20/fon/unify/manage/modules/gincore/api.php');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($curl);
//        var_dump($res);
//        exit;
        $result = json_decode($res, true);
        curl_close($curl);
        return $result;
    }
    return false;
}

$data = make_request('get_link_to_tariffs_page');

if(!empty($data['link'])){
    header('Location: '.$data['link']);
    exit;
}