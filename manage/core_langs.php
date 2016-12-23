<?php

// массив языков админки
$manage_langs = $all_configs['configs']['manage-langs']['list'];
// дефолтный язык админки
$manage_def_lang = $all_configs['configs']['manage-langs']['default'];

// текущий язык админки
if(isset($_GET['l']) && isset($all_configs['configs']['manage-langs']['list'][$_GET['l']])){
    $manage_lang = $_GET['l'];
}else{
    if(!empty($all_configs['configs']['settings-system-lang-select-enabled']) && !empty($all_configs['settings']['lang'])){
        $manage_lang = $all_configs['settings']['lang'];
    }else{
        $cookie_lang = isset($_COOKIE['manage_lang']) && isset($manage_langs[$_COOKIE['manage_lang']]) ? $_COOKIE['manage_lang'] : '';
        $manage_lang = $cookie_lang ?: $all_configs['configs']['manage-langs']['current'];
    }
}

$input_html['manage_lang'] = $manage_lang;

$manage_translates = get_manage_translates();