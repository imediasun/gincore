<?php

// генерим список баннеров 
$input_html['flayers'] = '';
$input_html['wide_banner'] = '';
$input_html['mainpage_banner'] = '';

$flayers = $db->query("SELECT * FROM {banners} WHERE active = 1 AND page_id = 0 ORDER BY prio")->assoc('id');
$translates = get_few_translates(
    'banners', 
    'banner_id', 
    $db->makeQuery("banner_id IN (?q)", array(implode(',', array_keys($flayers))))
);

foreach($flayers as $flayer){
    $flayer = translates_for_page($lang, $def_lang, $translates[$flayer['id']], $flayer, true);
    $img = ($flayer && $flayer['image']) ? '<img alt="'.$flayer['name'].'"
            src="'.$prefix.get_photo_by_lang('flayers/'.$flayer['image']).'">' : $flayer['name'];

    if($flayer['is_double'] == 1){
        $link_attr = array(
            'data-title' => $flayer['name'],
            'style' => 'background-image:url(\''.$prefix.get_photo_by_lang('flayers/'.$flayer['image']).'\')'
        );
        $input_html['wide_banner'] .=
            '<div class="image">'.
                gen_link($flayer['url'], $img, $flayer['hidden_link'], $link_attr)
            .'</div>'
        ;
//        $input_html['wide_banner'] .=
//            '<div class="image">'.
//                '<a href="'.$flayer['url'].'"'
//                .' style="background-image:url(\''.$prefix.'flayers/'.$flayer['image'].'\')"'
//                .'>'
//                    .$img
//                .'</a>'
//            .'</div>'
//        ;
    }elseif($flayer['is_double'] == 2){
        $link_attr = array(
            'data-title' => $flayer['name'],
        );
        $input_html['mainpage_banner'] .=
            '<div>'.
                gen_link($flayer['url'], $img, $flayer['hidden_link'], $link_attr)
            .'</div>'
        ;
    }elseif($flayer['is_double'] == 0){
        $link_attr = array(
            'data-title' => $flayer['name'],
        );
        $input_html['flayers'] .=
            '<div '
//                .($flayer['is_double'] ? ' class="double"' : '')
            .'>'.
                gen_link($flayer['url'], $img, $flayer['hidden_link'], $link_attr)
            .'</div>';
    }
}

$input_html['wide_banner'] .= '<div class="inner_fullwidth">'.$template_vars['l_global_banners_text'].'</div>';
