<?php

/**
 * https://php.net/manual/ru/function.printf.php#51763
 */
function sprintf_array($format, $arr) 
{ 
    return call_user_func_array('sprintf', array_merge((array)$format, $arr)); 
} 

function get_manage_translates(){
    global $manage_lang, $manage_def_lang;
    $manage_translates = array();
    $vars = db()->query("SELECT id, var FROM {admin_translates}")->vars();
    $translates = db()->query("SELECT CONCAT(var_id, '_', lang) as id, var_id, text, lang "
                            ."FROM {admin_translates_strings} "
                            ."WHERE lang IN (?,?)", array($manage_lang, $manage_def_lang), 'assoc:id');
    $manage_translates_js = array();
    foreach($vars as $var_id => $var){
        $k_cur = $var_id.'_'.$manage_lang;
        $k_def = $var_id.'_'.$manage_def_lang;
        $manage_translates[$var] = !empty($translates[$k_cur]['text']) ? $translates[$k_cur]['text'] : (!empty($translates[$k_def]['text'])?$translates[$k_def]['text']:'');
        if(strpos($var, 'js_') === 0){
            $manage_translates_js[substr($var,3)] = $manage_translates[$var];
        }
    }
    global $input;
    $input['manage_translates_js'] = json_encode($manage_translates_js);
    return $manage_translates;
}

function l($param, $placeholders = array(), $default_wrap = true)
{
    global $manage_translates;
    if (!empty($manage_translates[$param])) {
        $text = $manage_translates[$param];
    }elseif($default_wrap){
        $text = '{'.$param.'}';
    }else{
        $text = $param;
    }
    
    if (count($placeholders)) {
        return sprintf_array($text, $placeholders);
    }
    return $text;
}
// функция l() для вставки перевода в sql запрос
function lq($param, $placeholders = array()){
    return l($param, $placeholders, false);
}