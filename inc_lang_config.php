<?php

function change_key( $array, $old_key, $new_key) {
    if( ! array_key_exists( $old_key, $array ) )
        return $array;
    $keys = array_keys( $array );
    $keys[ array_search( $old_key, $keys ) ] = $new_key;
    return array_combine( $keys, $array );
}

$lang_arr = array();

$langs_arr = $db->query("SELECT url, name, `default`, text_direction FROM {langs} "
                       ."WHERE state = 1 ORDER BY prio")->assoc('url');

foreach($langs_arr as $lngg){
    if($lngg['default']){
        $lang = $lngg['url'];
        $def_lang = $lngg['url'];
    }
    array_push($lang_arr, $lngg['url']);
}

$kiev_arr = $langs_arr['kiev'];
$langs_arr = change_key($langs_arr, 'kiev', 'default_kiev');
$langs_arr['default_kiev'] = $kiev_arr;

$tbl_prefix = $cfg['tbl'];

$hide_def_lang = true;