<?php
/**
 * модуль вывода простого контента
 *
 */


//echo 'content_default';

global $mod, $prefix, $url_lang, $arrequest, $db, $txt, $gmap_markers, $gmap_zoom, $settings, $lang, $def_lang, $user_kiev, $template_vars;

$text = gen_content_array($mod['content']);

$items = $db->query("SELECT id,picture,gallery FROM {map} "
                   ."WHERE parent = ? AND state = 1 ORDER BY prio", array($mod['id']), 'assoc:id');

$input['content'] = '';
$translates = get_few_translates(
    'map', 
    'map_id', 
    $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($items))))
);
$tabs = '';
$tabs_content = '';
$i = 0;
$id = 1;
foreach($items as $item){
    $item = translates_for_page($lang, $def_lang, $translates[$item['id']], $item, false);
    if($item['lat'] && $item['lng']){
        $img = $item['gallery'] && $item['picture'] ? '<img src="'.$prefix.get_photo_by_lang('images/'.$item['gallery'].'/'.$item['picture']).'" alt="">' : '';
        $data = array(
            'lat' => $item['lat'],
            'lng' => $item['lng'],
            'content' => $item['content']
        );
        $tabs .= '<li data-content=\''.json_encode($data).'\'><a href="#tab-'.$id.'">'.$item['name'].'</a></li>';
        $id ++;
//        $tabs_content .= '
//            <div id="tab-'.$item['id'].'"'.$active.'>
//                <div class="contacts_shop '.($img ? 'img_offset' : '').'">
//                    <div class="cs_padding">
//                        <div class="cs_title">'.$item['name'].'</div>
//                        <div class="cs_body clearfix">
//                            <div class="cs_body_img">
//                                '.$img.'
//                            </div>
//                            <div class="cs_body_content">
//                                '.$item['content'].'
//                            </div>
//                            <div class="clearfix"></div>
//                        </div>
//                    </div>
//                </div>
//            </div>
//        ';
    }
    $i++;
}
$input['content'] .= '
    <ul class="tabs">
        '.$tabs.'
    </ul>
    <script>var is_contacts = true;</script>
    <div id="contacts_city_map" class="contacts_city_map"></div>
';

// достаем все точки всех городов
$gmap_zoom = 6;
$gmap_markers = array();
$all_markers = $db->query("SELECT lat,lng FROM {map_strings} as s LEFT JOIN {map} as m ON m.id = s.map_id
                           WHERE m.parent = ?i", array($mod['id']), 'assoc');
foreach($all_markers as $marker){
    if($marker['lat'] && $marker['lng']){
        $gmap_markers[] = array(
            'lat' => $marker['lat'],
            'lng' => $marker['lng']
        );
    }
}

//if(!$user_kiev){
    $input['content'] .= '
    <h1 class="headline" style="margin-top:15px"><span>'.$template_vars['l_contacts_repair_regions_title'].'</span></h1>
    <div class="row">
        <div class="col-sm-3">
            <div class="restore_step clearfix">
                <div class="icon_box">
                    <img class="icon" src="'.$prefix.'images/01_upakuyte.png" alt=" ">
                    <img class="arrow" src="'.$prefix.'images/strelka.png" alt=" ">
                </div>
                <div class="about_step">
                    '.$template_vars['l_contacts_repair_regions_step_1'].'
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="restore_step clearfix">
                <div class="icon_box">
                    <img class="icon" src="'.$prefix.'images/02_nova_powta.png" alt=" ">
                    <img class="arrow" src="'.$prefix.'images/strelka.png" alt=" ">
                </div>
                <div class="about_step">
                    '.$template_vars['l_contacts_repair_regions_step_2'].'
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="restore_step clearfix">
                <div class="icon_box">
                    <img class="icon" src="'.$prefix.'images/03_diagnostika.png" alt=" ">
                    <img class="arrow" src="'.$prefix.'images/strelka.png" alt=" ">
                </div>
                <div class="about_step">
                    '.$template_vars['l_contacts_repair_regions_step_3'].'
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="restore_step clearfix">
                <div class="icon_box">
                    <img class="icon" src="'.$prefix.'images/04_otpravka_domoy.png" alt=" ">
                </div>
                <div class="about_step">
                    '.$template_vars['l_contacts_repair_regions_step_4'].'
                </div>
            </div>
        </div>
    </div>
    ';
//}
//$input['content'] = $text[0].( isset($text[1]) ? $text[1] :'');


    $contact = '<div class="global-contact">'.$template_vars['l_contacts_consultaion_caption'] . $template_vars['l_content_tel'] . '</div>';

$input['title'] = $contact . $template_vars['l_contacts_page_title'].gen_city_select(true);

if ($mod['gallery']){
    $input['mainpage_gallery'] = get_big_pics($mod['gallery'], 3);
} else {
    $input['mainpage_gallery'] = '';
}

$content = '';
/*
foreach($articles as $article){
    $content .= '<div class="contacts_row">';
    $picture = '';
    if($article['picture']) {
        $picture = '<img class="article_image" src="'.$prefix.'images/'.$article['gallery'].'/'.str_replace('_m.', '.', $article['picture']).'" alt=" ">';
    }

    $content .= $article['content'];

    if ($article['is_gmap'] && $article['lat']>0 && $article['lng']>0)
        $content .= '<a href="https://maps.google.com/maps?q='. $article['lat'] .','. $article['lng'] .'"
            class="map_link"
            data-lng = "'. $article['lng'] .'"
            data-lat = "'. $article['lat'] .'"
            data-obj = "'. $article['name'] .'"
            target="blank">Показать на карте</a>';

    $content .= '</div>';
}
*/
if ($mod['gallery']){
    $input['head_pics'] = get_big_pics($mod['gallery'], 3);
}

//$input['content'] = $mod['content'];

// full width page
templater_no_left_block();

?>