<?php

global $url_all_levels, $mod, $prefix, $url_lang, $submenu_function;


$submenu_function = 'gen_top_submenu';

function gen_left_submenu($id, $parent = '', $with_pics = false, $is_footer = false)
{
    global $arrequest, $prefix,  $url_lang,$db, $lang, $def_lang;
    $first = true;
    $activeclass= '';
    $active = 'active_submenu';

    $pattern = 'SELECT * FROM {map} WHERE state=1 AND is_page=1 AND parent=?i ORDER BY prio';
    $sql = $db->query($pattern, array($id), 'assoc:id');

    if (count($sql) < 1)
        return '';

    $out = '<div class="submenu">
                <ul class="submenu_inner">';
    $translates = get_few_translates(
        'map', 
        'map_id', 
        $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($sql))))
    );
    foreach ($sql AS $pp) {
        $pp = translates_for_page($lang, $def_lang, $translates[$pp['id']], $pp, true);
        if(isset($arrequest[2]) && isset($arrequest[1]) && isset($arrequest[0])){
            $activeclass = ($parent == ($arrequest[0].'/'.$arrequest[1]) && $pp['url'] == $arrequest[2])
                            ? $active : '';
        }

        $out .='<li class="'.$activeclass.'">
                    <a href="'. $prefix .$url_lang. $parent .'/'. $pp['url'] .'">
                    <span class="title">'. $pp['name'] .'</span>
                    <span class="image"'
                    . ($pp['picture'] && $with_pics ? ' style="background-image: url(\'' . $prefix . 'images/' . $pp['gallery'] . '/' . $pp['picture'] .'\')"' : '')
                    .'></span>
                    </a></li>';

        $first = false;
    }
    $out.='</ul></div>';

    return $out;
}

function gen_top_submenu1($id, $parent = '', $with_pics = false, $is_footer = false)
{
    global $arrequest, $prefix, $url_lang, $db;
    $first = true;
    $activeclass= '';
    $active = 'active_submenu';

    $pattern = 'SELECT * FROM {map} WHERE state=1 AND is_page=1 AND parent=?i ORDER BY prio';
    $sql = $db->query($pattern, array($id), 'assoc');

    if (count($sql) < 1)
        return '';

    $out = '<div class="top_menu_2">
                <div class="center_block">
                <div class="menu_container inner">
                    <div class="content_images">
                        ';
    foreach ($sql AS $pp) {
        if(isset($arrequest[1]) && isset($arrequest[0])){
            $activeclass = ($parent == $arrequest[0] && $pp['url'] == $arrequest[1])
                            ? $active : '';
        }
        $submenu = gen_left_submenu($pp['id'], $parent .'/'. $pp['url'], $with_pics, $is_footer);

        $out .= ''
//                .($first?'':'<div class="menu_delimiter"></div>')
                .'<div class="'.$activeclass. ($submenu ? ' submenu_present' : '') . '">'
                    .'<a href="'. $prefix .$url_lang. $parent .'/'. $pp['url'] .'"'
//                        .' class="'. ($submenu ? ' submenu_present' : '').'"'
                        . '>'
                    .'<span class="image"'
                    . ($pp['picture'] && $with_pics ? ' style="background-image: url(\'' . $prefix . 'images/' . $pp['gallery'] . '/' . $pp['picture'] .'\')"' : '')
                    .'></span>'
                    .'<span class="title">'. $pp['name'] .'</span>'
                    .'</a>'
                    .$submenu
                .'</div>'
                ;

        $first = false;
    }
    $out.='
                </div>
            </div>
            </div>
        </div>';

    return $out;
}

function gen_top_submenu($id, $parent = '', $with_pics = false, $is_footer = false)
{
    global $arrequest, $prefix, $url_lang, $db, $lang, $def_lang;
    $first = true;
    $activeclass= '';
    $active = 'active_submenu';

    $pattern = 'SELECT * FROM {map} WHERE state=1 AND is_page=1 AND parent=?i ORDER BY prio';
    $sql = $db->query($pattern, array($id), 'assoc:id');

    if (count($sql) < 1)
        return '';

    $out = '<div class="top_menu_2">
                <div class="center_block">
                <div class="menu_container inner_fullwidth">
                    <div class="content_images">
                    <div class="row">
                        ';
    $i = 0;
    $translates = get_few_translates(
        'map', 
        'map_id', 
        $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($sql))))
    );
    foreach ($sql AS $pp) {
        $pp = translates_for_page($lang, $def_lang, $translates[$pp['id']], $pp, true);
        if($i && !($i%4)){
            $out .= '</div><div class="row">';
        }
        $i++;
        if(isset($arrequest[1]) && isset($arrequest[0])){
            $activeclass = ($parent == $arrequest[0] && $pp['url'] == $arrequest[1])
                            ? $active : '';
        }
        $submenu = gen_left_submenu($pp['id'], $parent .'/'. $pp['url']);


        $link_inner =
            '<span class="image"'
            . ($pp['picture'] && $with_pics ? ' style="background-image: url(\'' . $prefix . 'images/' . $pp['gallery'] . '/' . $pp['picture'] .'\')"' : '')
            .'></span>'
            .'<span class="title">'. $pp['name'] .'</span>'
        ;

        $out .= ''
//                .($first?'':'<div class="menu_delimiter"></div>')
                .'<div class="'.$activeclass. ($submenu ? ' submenu_present' : '') . '">'
                    .gen_link($prefix .$url_lang. $parent .'/'. $pp['url'], $link_inner, $is_footer)
                    .$submenu
                .'</div>'
                ;

        $first = false;
    }
    $out.='     </div>
                </div>
            </div>
            </div>
        </div>';

    return $out;
}

// подключаем модуль генератора меню
include_once 'modules/menu_h_root/index.php';