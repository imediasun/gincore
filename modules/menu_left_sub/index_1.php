<?php

global $url_all_levels, $mod, $prefix, $submenu_function;


$submenu_function = 'gen_left_submenu';

function gen_left_submenu($id, $parent = '')
{
    global $arrequest, $prefix, $db;
    $first = true;
    $activeclass= '';
    $active = 'active_submenu';

    $pattern = 'SELECT * FROM {map} WHERE state=1 AND is_page=1 AND parent=?i ORDER BY prio';
    $sql = $db->query($pattern, array($id), 'assoc');

    if (count($sql) < 1)
        return '';

    $out = '<div class="submenu">
                <ul class="submenu_inner">';

    foreach ($sql AS $pp) {
        if(isset($arrequest[1]) && isset($arrequest[0])){
            $activeclass = ($parent == $arrequest[0] && $pp['url'] == $arrequest[1])
                            ? $active : '';
        }

        $out .='<li class="'.$activeclass.'">
                    <a href="'. $prefix . $parent .'/'. $pp['url'] .'">
                    <span class="title">'. $pp['name'] .'</span>
                    <span class="image"'
                    . ($pp['picture'] ? ' style="background-image: url(\'' . $prefix . 'images/' . $pp['gallery'] . '/' . $pp['picture'] .'\')"' : '')
                    .'></span>
                    </a></li>';

        $first = false;
    }
    $out.='</ul></div>';

    return $out;
}

// подключаем модуль генератора меню
include_once 'modules/menu_h_root/index.php';