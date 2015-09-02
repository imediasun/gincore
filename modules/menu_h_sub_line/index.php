<?php

global $url_all_levels, $mod, $prefix, $url_lang, $submenu_function;


$submenu_function = 'gen_submenu_line';

function gen_submenu_line($id, $parent = '')
{
    global $arrequest, $prefix, $url_lang, $db;
    $first = true;
    $activeclass= '';
    $active = 'active_submenu';
    
    $pattern = 'SELECT * FROM {map} WHERE state=1 AND is_page=1 AND parent=?i ORDER BY prio';
    $sql = $db->query($pattern, array($id), 'assoc');
    
    if (count($sql) < 1)
        return '';
    
    $out = '<div class="submenu horizontal">
                <div class="submenu_inner">';

    foreach ($sql AS $pp) {
        if(isset($arrequest[1]) && isset($arrequest[0])){
            $activeclass = ($parent == $arrequest[0] && $pp['url'] == $arrequest[1]) 
                            ? $active : '';
        }

        $out .='<div class="'.$activeclass.'">
                    <a href="'. $prefix .$url_lang. $parent .'/'. $pp['url'] .'">
                    <span class="title">'. $pp['name'] .'</span>'
                    . ($pp['picture'] ? '<span class="image" style="background-image: url(' . $prefix . 'images/' . $pp['gallery'] . '/' . $pp['picture'] .')"></span>' : '')
                . '</a></div>';

        $first = false;
    }
    $out.='</div></div>';

    return $out;
    
}

// подключаем модуль генератора меню
include_once 'modules/menu_h_root/index.php';