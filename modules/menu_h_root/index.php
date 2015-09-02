<?php

global $arrequest, $url_all_levels, $mod, $url_lang, $prefix, $template_vars;

/**
 * Создание корневого меню
 *
 * @return string
 */
function gen_root_menu($id = 0, $with_pics = false, $is_fotter = false, $no_submenu = false){
    global $arrequest, $cfg, $prefix, $url_lang, $db, $submenu_function, $input, $settings, $lang, $def_lang, $template_vars;

    $parent_url = '';
    if($id)
        $parent_url = $db->query('SELECT url FROM {map} WHERE id=?i', array($id), 'el');

    $pattern = 'SELECT * FROM {map} WHERE state=1 AND is_page=1 AND parent=?i ORDER BY prio';
    $sql = $db->query($pattern, array($id), 'assoc:id');

    $out = '
        <ul class="'.(!$is_fotter ? 'nav navbar-nav"' : 'clearfix').'">
            '.(!$is_fotter ? '
                <li class="visible-xs mobile_menu_contacts">
                    <a href="'.$prefix.$url_lang.'restore/contacts">
                        <span class="title">'.$template_vars['l_contacts_menu_mobile_name'].'</span>
                    </a>
                </li>
            ' : '').'
    ';
    if($with_pics){
//        $out .= '<li><a href="'.$prefix.$url_lang.'">
//                    <span class="logo">
//                    </span>
//                </a>
//                </li>';
    }

    $first = true;
    $html_active_menu = 'menu_active';
    $translates = get_few_translates(
        'map', 
        'map_id', 
        $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($sql))))
    );
    foreach($sql AS $pp){
        $pp = translates_for_page($lang, $def_lang, $translates[$pp['id']], $pp, true);
        $activeclass = (
                (isset($arrequest[0]) && $pp['url'] == $arrequest[0]) || (isset($arrequest[0]) && $arrequest[0] == $parent_url && isset($arrequest[1]) && $pp['url'] == $arrequest[1])
                ) ? $html_active_menu : '';
        $parent = ($parent_url ? $parent_url.'/' : '').$pp['url'];
        if(!$no_submenu){
            $submenu = $submenu_function($pp['id'], $parent, $with_pics, $is_fotter);
        }else{
            $submenu = '';
        }
        
        $link_inner = 
            (($with_pics) ?
                    '<span class="main_menu_icon" style="'
                    .(($pp['gallery'] && $pp['picture']) ? 'background-image: url(\''.$prefix.'images/'.$pp['gallery'].'/'.$pp['picture'].'\')' : '')
                    .'"></span>' : '' )
            .'<span class="title">'.$pp['name'].'</span>'
            .'<span class="menu_arrow"></span>'
        ;
        $out .= '<li class="'.$activeclass
                .($submenu ? ' submenu_present' : '').'">'
//                .(!$first ? '<div class="menu_delimiter md_left"></div>' : '')
                .'<div class="menu_delimiter md_left"></div>'
                .gen_link($prefix.$url_lang.$parent, $link_inner, $is_fotter)
//             . '<div class="menu_delimiter"></div>'
                .$submenu.'</li>';
        $first = false;
        // for contacts button
        if($pp['url'] == 'contacts')
            $input['contacts_link'] = $prefix.$url_lang.$parent;
    }
    if($with_pics){
        $out .= '<li><a class="slidedown_map_link" href="'.$prefix.$url_lang.'restore/contacts">
                <span class="contacts_big">
                    <span>
                        <span class="contacts_phone">'
                .(isset($template_vars['l_content_tel']) ? $template_vars['l_content_tel'] : '')
                .'</span><br>
                        <span class="contacts_text">'
                .$template_vars['l_menu_contacts_btn'].'</i>'
                .'</span>
                    </span>
                </span><span class="contacts_mobile title">'.$template_vars['l_contacts_menu_mobile_name'].'</span></a>
                </li>';
    }
    $out.='</ul>';

    return $out;
}

/**
 * Создание меню 2-го уровня
 * 
 * @param integer $id - родительский элемент
 * @param string $parent - URL родительского элемента
 * 
 * @return string
 */
function gen_submenu($id, $parent = '', $with_pics = false, $is_fotter = false){
    global $arrequest, $prefix, $url_lang, $db;
    $first = true;
    $activeclass = '';
    $active = 'active_submenu';

    $pattern = 'SELECT * FROM {map} WHERE state=1 AND is_page=1 AND parent=?i ORDER BY prio';
    $sql = $db->query($pattern, array($id), 'assoc');

    if(count($sql) < 1)
        return '';

    $out = '<div class="submenu">
                <ul class="submenu_inner">';
    $first = true;
    foreach($sql AS $pp){
        if(isset($arrequest[1]) && isset($arrequest[0])){
            $activeclass = ($parent == $arrequest[0] && $pp['url'] == $arrequest[1]) ? $active : '';
        }
        $out .='<li class="'.$activeclass.'">'
                .'<a href="'.$prefix.$url_lang.$parent.'/'.$pp['url'].'">'
                .'<span class="title">'.$pp['name'].'</span>'
                .($pp['picture'] && $with_pics ? '<span class="image"><img src="'.$prefix.'images/'.$pp['gallery'].'/'.$pp['picture'].'" alt=" "></span>' : '')
                .'</a></li>';
    }
    $out.='</ul></div>';

    return $out;
}

if($arrequest){
    $input_html['root_menu'] = gen_root_menu(13, false, true, true);
}
//$input_html['left_menu'] = '<div class="left_menu">'.gen_root_menu(0, true).'</div>';
$input_html['top_menu'] = gen_root_menu(0, true);


$input_html['breadcrumbs'] = gen_breadcrumbs();
