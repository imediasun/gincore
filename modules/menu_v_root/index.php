<?php
#модуль по умолчанию


global $url_all_levels, $mod, $prefix;


/**
 * Создание корневого меню
 *
 * @return string
 */
function gen_root_menu (){
    global $arrequest, $cfg, $prefix, $db;
        #map
    $pattern='SELECT * FROM {map} WHERE state=1 AND is_page=1 AND parent=?i AND url != "" ORDER BY prio';
    $sql=$db->query($pattern, array(0), 'assoc');
    $out='<ul>';
    $submenus = '';
    $nth = 1;
    foreach ($sql AS $pp){
        $op = 0;
        $activeclass=((isset($arrequest[0]) && $pp['url']==$arrequest[0]) || (!isset($arrequest[0]) && $pp['url'] == '' ))?' class="active_menu"':'';
        $a_class='';
        $parent=$prefix;
        
        $out .= '<li><a'.$activeclass.' href="'.$prefix.$pp['url'].'">'.$pp['name'].'</a></li>';
//
//        $sql1 = $db->query('SELECT * FROM {map} as map WHERE state=1
//                            AND (SELECT count(*) FROM {map_module} WHERE page_id=map.id AND module="content_news_album")=0
//                            AND is_page=1 AND parent=?i ORDER BY prio', array($pp['id']), 'assoc');
//        
//        $out = gen_menu_a_tag($nth, $pp, $activeclass ? 'class="'.$activeclass.'"' : '', $parent, $a_class, $op);
//        $out .= $menu_a_tag[0];
//        $nth ++;
//        $submenus .= $menu_a_tag[1];

    }
    $out.='</ul>';

    return $out;
}



//генерация элемента в меню карты сайта
function gen_menu_a_tag($nth, $pp, $a_class, $parent, $href_class='', $op = 0){
    GLOBAL $prefix;
    $a_notpage=$pp['is_page']==0?' notpage':'';
    //$out='<li '.$a_class.'>'.($op?'<small>▼</small> ':'').'<a title="'.$pp['fullname'].'" '.$href_class.' href="'.$parent.$pp['url'].'">'.$pp['name'].'</a>';
    
    $submenu = gen_second_menu($pp);
    $submenu = $submenu[0] ? '<div style="width: '.$submenu[1].'%;" data-submenu_id="'.$pp['id'].'" class="submenu">'.$submenu[0].'</div>' : '';
    
    $target = '';
    
    $href = $parent.$pp['url'];
    if(strpos($pp['url'], 'http://') !== false){
        $href = $pp['url'];
        $target = ' target="_blank" ';
    }
    
    $out = '<li '.$a_class.'>'.
                '<a data-nth="'.$nth.'" data-menu_id="'.$pp['id'].'" '.$href_class.' href="#" '.$target.'>'.
                    '<div class="root_menu_image" id="rmi_'.$pp['id'].'"></div>'.
                    $pp['name'].
                '</a>'.
                ($submenu ? '<div class="arrow_menu"></div>' : '').
            '</li>';
    return array($out, $submenu);
}


/**
 * Создание второго меню
 *
 * submenu
 * 
 * @return string
 */

function gen_second_menu(){
    global $arrequest, $cfg, $prefix, $mod, $url_all_levels, $db;
    
    $menus = '';
    
    if(isset($url_all_levels[0])){
        $submenu = $db->query("SELECT id, parent, page_type, name, url, gallery, picture FROM {map} WHERE parent = ?i AND state = 1 AND is_page = 1 ORDER BY prio", array($url_all_levels[0]['id']), 'assoc');
        if($submenu){
            $menus = '<div id="accordeoncheg"><ul class="accordeon_ul">';
            foreach($submenu as $menu){
                $order = "ORDER BY prio";
                if(in_array($menu['page_type'], array(10, 11))){
                    $order = "ORDER BY uxt DESC, id DESC LIMIT 10";
                }
                $subs = $db->query("SELECT name, url, gallery, picture
                                    FROM {map} 
                                    WHERE parent = ?i AND state = 1 AND is_page = 1
                                    ?q", array($menu['id'], $order), 'assoc');
                $current = isset($arrequest[1]) && $arrequest[1] == $menu['url'];
                $subs_html = '';
                if($subs){
                    $subs_html = '<ul class="menu_cats"'.($current ? ' style="display: block"' : '').'>';
                    foreach($subs as $sub){
                        $image = '';
                        if($sub['picture']){
                            $img1 = pathinfo($sub['picture']);
                            $sub['picture'] = $img1['filename'].'_m.'.$img1['extension'];
                            $image = 'data-image="'.$prefix.'images/'.$sub['gallery'].'/'.$sub['picture'].'" ';
                        }
                        $active = $current && isset($arrequest[2]) && $arrequest[2] == $sub['url'] ? ' class="active_sub"' : '';
                        $subs_html .= '
                            <li'.$active.'><a '.$image.'class="pjax" href="'.$prefix.$arrequest[0].'/'.$menu['url'].'/'.$sub['url'].'">'.$sub['name'].'</a></li>
                        ';
                    }
                    $subs_html .= '</ul>';
                }
                $image = '';
                if($menu['picture']){
                    $img = pathinfo($menu['picture']);
                    $menu['picture'] = $img['filename'].'_m.'.$img['extension'];
                    $image = 'data-image="'.$prefix.'images/'.$menu['gallery'].'/'.$menu['picture'].'" ';
                }
                $menus .= '
                    <li>
                        <a '.$image.'class="acc_tag'.($current ? ' open_tag active_cat' : '').''.(!$subs_html ? ' no_bg pjax' : '').'" href="'.$prefix.$arrequest[0].'/'.$menu['url'].'">'.$menu['name'].'</a>
                        '.$subs_html.'
                    </li>
                ';
            }
            $menus .= '</ul></div>';
        }
    }
    
    return $menus;
}

$input_html['root_menu'] = gen_root_menu();

$input_html['second_menu'] = gen_second_menu();


