<?php

// interactive google map




function mod_interactive_map(){
    global $prefix, $settings, $input_js, $template_vars;

    $coordinates = explode(',', $settings['global_interactive_map']);
    $input_js['files_gmap'] = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>'.
            '<script type="text/javascript" src="'.$prefix.'extra/gmap.js?20"></script>';
    $input_js['global_source_gmap'] ='var global_gmap_sitename=\''.$settings['site_name']."';\n".
                             "var global_gmap_lat='".$coordinates[0]."';\n".
                             "var global_gmap_lng='".$coordinates[1]."';\n".
                             "var global_gmap_init=true;\n";

     $out = '<div class="slidedown_map">
                <div id="globalMap">
                </div>
                <div class="slidedown_map_link undertop">
                    <div>'.$template_vars['l_imap_caprion'].'</div>
                </div>
             </div>';

    return $out;
}

// контакты в выпадающем меню в шапке итить колотить!
function mod_interactive_map_2($page_id=''){
    global $prefix, $settings, $input_js, $db, $user_kiev, $input, $lang, $def_lang, $template_vars, $url_lang;

    $content = $gallery = '';
    if(!$page_id) {
        $coordinates = explode(',', $settings['global_interactive_map']);
    } else {
        $el = $db->query('SELECT gallery '
                        .'FROM {map} WHERE id=?i AND state=1', array($page_id), 'row');
        $translates = $db->query("SELECT * 
                              FROM {map_strings} WHERE map_id = ?i", array($page_id), 'assoc:lang');
        $el = translates_for_page($lang, $def_lang, $translates, $el, true);
        $coordinates = array($el['lat'], $el['lng']);

        if($user_kiev){
            $contact_items = $db->query("SELECT id,gallery,picture FROM {map} "
                       ."WHERE parent = ? AND state = 1 ORDER BY prio", array($page_id), 'assoc:id');
            if($contact_items){
                $translates = get_few_translates(
                    'map', 
                    'map_id', 
                    $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($contact_items))))
                );
                $content = '';
                $row_content = '';
                $i = 1;
                $id = 1;
                foreach($contact_items as $item){
                    $item = translates_for_page($lang, $def_lang, $translates[$item['id']], $item);
                    if((int)$item['lat'] && (int)$item['lng']){
                        $img = $item['gallery'] && $item['picture'] ? '<img src="'.$prefix.get_photo_by_lang('images/'.$item['gallery'].'/'.$item['picture']).'" alt="">' : '';
                        if($i == 3){
                            $content .= '<div class="header_contacts_row">'.$row_content.'</div>';
                            $row_content = '';
                            $i = 1;
                        }
                        $row_content .= '
                            <div class="header_contact_item">
                                <div class="header_contact_item_inner">
                                    <a href="'.$prefix.$url_lang.'restore/contacts#tab-'.$id.'">'.$item['name'].'</a>
                                    <div class="contacts_shop '.($img ? 'img_offset' : '').'">
                                        <div class="cs_padding">
                                            <div class="cs_title">'.$item['name'].'</div>
                                            <div class="cs_body">
                                                <div class="cs_body_img">
                                                    '.$img.'
                                                </div>
                                                <div class="cs_body_content">
                                                    '.$item['content'].'
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ';
                        $id ++;
                        $i++;
                    }
                }
                if($row_content){
                    $content .= '<div class="header_contacts_row">'.$row_content.'</div>';
                }
            }

            $content .= '
                    <div class="header_repair_all_content">
                        <div class="header_contact_item_inner">
                            <div class="contacts_shop contacts_shop_full">
                                <div class="cs_padding">
                                    <h3 class="cs_title" style="color:#000">'.$template_vars['l_map_repair_title'].'</h3>
                                    <div class="cs_body">
                                        <div class="cs_body_content">
                                            <p>
                                            '.$template_vars['l_map_repair_text'].'
                                            </p>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                ';
        }else{
            $content .= '<div class="text">'.
                            $el['content'].
                        '</div>';
        }

//        $gallery .= '<div class="mainpage_gallery">';
//        if ($el['gallery']){
//            $gallery .= get_big_pics($el['gallery'], 2);
//        }
//        $gallery .= '</div>';
//        $content .= '<div class="text">'.
//                        $el['content'].
//                    '</div>';

    }
//    $input_js['files_gmap'] = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script><script type="text/javascript" src="'.$prefix.'extra/gmap.js"></script>';
    $input_js['global_source_gmap'] ='var global_gmap_sitename=\''.$settings['site_name']."';\n".
                             "var global_gmap_lat='".$coordinates[0]."';\n".
                             "var global_gmap_lng='".$coordinates[1]."';\n".
                             "var global_gmap_init=true;\n";

  
    $contact = '<div class="global-contact">'.$template_vars['l_contacts_consultaion_caption']. $template_vars['l_content_tel'] . '</div>';

    if(!$user_kiev){
        $contacts =
            '<div class="city_contacts_info">
                <div class="city_restore_steps">
                    <h3>'.$template_vars['l_contacts_repair_regions_title'].'</h3>
                    <div class="restore_step">
                        <div class="icon_box">
                            <img class="icon" src="'.$prefix.'images/01_upakuyte.png" alt=" ">
                            <img class="arrow" src="'.$prefix.'images/strelka.png" alt=" ">
                        </div>
                        <div class="about_step">
                            '.$template_vars['l_contacts_repair_regions_step_1'].'
                        </div>
                    </div>
                    <div class="restore_step">
                        <div class="icon_box">
                            <img class="icon" src="'.$prefix.'images/02_nova_powta.png" alt=" ">
                            <img class="arrow" src="'.$prefix.'images/strelka.png" alt=" ">
                        </div>
                        <div class="about_step">
                            '.$template_vars['l_contacts_repair_regions_step_2'].'
                        </div>
                    </div>
                    <div class="restore_step">
                        <div class="icon_box">
                            <img class="icon" src="'.$prefix.'images/03_diagnostika.png" alt=" ">
                            <img class="arrow" src="'.$prefix.'images/strelka.png" alt=" ">
                        </div>
                        <div class="about_step">
                            '.$template_vars['l_contacts_repair_regions_step_3'].'
                        </div>
                    </div>
                    <div class="restore_step">
                        <div class="icon_box">
                            <img class="icon" src="'.$prefix.'images/04_otpravka_domoy.png" alt=" ">
                        </div>
                        <div class="about_step">
                            '.$template_vars['l_contacts_repair_regions_step_4'].'
                        </div>
                    </div>
                </div>
                '.$content.'
            </div>';
    }else{
//        $contacts = '<div id="globalMap"></div>
//                    <div class="slidedown_map_content">'.
//                        $gallery.
//                        $content.
//                    '</div>';
        $contacts = '<h3 class="headline"><span>'.$template_vars['l_contacts_repair_city_title'].gen_city_select(true).'</span></h3><div class="header_contacts_list">'.$content.'</div>';
    }
    $input['city_delivery_popup'] =
        '<div class="city_delivery sm_content">
            <div class="top">
                <div class="sm_close"></div>
               '.$template_vars['l_contacs_delivery_info_popup_title'].' 
            </div>
            <div class="bottom">
               '.$template_vars['l_contacs_delivery_info_popup_text'].' 
            </div>
        </div>';
    $out = '<div class="slidedown_map">
                <div class="inner">
                    ' . /*$visit > $visitors->visit_max_limit*/$contact . '
                    '.$contacts.'
                </div>
             </div>';
//                    <div class="slidedown_map_link undertop">
//                        <div>Карта</div>
//                    </div>

    return $out;
}

function mod_service_map($page_id=''){
    global $prefix, $settings, $input_js, $db, $template_vars, $lang, $def_lang;

    if(!$page_id) {
        $coordinates = explode(',', $settings['global_interactive_map']);
    } else {
        $el = $db->query('SELECT * FROM {map} WHERE id=?i', array($page_id), 'row');
        $translates = $db->query("SELECT * 
                              FROM {map_strings} WHERE map_id = ?i", array($page_id), 'assoc:lang');
        $el = translates_for_page($lang, $def_lang, $translates, $el, true);
        $coordinates = array($el['lat'], $el['lng']);
        $contact_items = $db->query("SELECT id FROM {map} "
                   ."WHERE parent = ? AND state = 1 ORDER BY prio", array($page_id), 'assoc:id');
        $translates = get_few_translates(
            'map', 
            'map_id', 
            $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($contact_items))))
        );
        $gmap_markers = array();
        foreach($contact_items as $item){
            $item = translates_for_page($lang, $def_lang, $translates[$item['id']], $item, false);
            if($item['lat'] && $item['lng']){
                $gmap_markers[] = array(
                    'lat' => $item['lat'],
                    'lng' => $item['lng']
                );
            }
        }
    }
    $input_js['service_map'] ='var service_gmap_sitename=\''.$settings['site_name']."';\n".
                             (!empty($gmap_markers) ? "var service_gmap_markers=".json_encode($gmap_markers).";\n" : '').
                             "var service_gmap_lat=".$coordinates[0].";\n".
                             "var service_gmap_lng=".$coordinates[1].";\n".
                             "var service_gmap_init=true;\n";

     $out = '<div class="service_map">
                    <div class="service_map_title">
                        <div>'.$template_vars['l_map_caption'].':</div>
                    </div>
                    <div id="serviceMap"></div>
             </div>';

    return $out;
}
$input_html['slidedown_map'] = mod_interactive_map_2(150);
$input_html['service_map'] = mod_service_map(152);