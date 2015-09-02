<?php

// генерим шаблоны и скрипты для видео

function gen_news_block($quantity){
    global $db, $prefix, $url_lang, $configs, $lang, $def_lang;
    $news_section_id = 66;
    $link = '';
    $arr1 = $db->query('SELECT parent, url FROM {map} WHERE `id`=?i',
            array($news_section_id), 'row');
    $arr2 = $db->query('SELECT parent, url FROM {map} WHERE `id`=?i',
            array($arr1['parent']), 'row');
    $link = $arr2['url'].'/'.$arr1['url'];
    $news = $db->query('SELECT url, `id`, picture, gallery, uxt FROM {map} WHERE `parent`=?i AND `state`=1
        ORDER BY `uxt` DESC,`prio` LIMIT 0, ?i', array($news_section_id, $quantity), 'assoc:id');
    $news_html = '';
    if ($news) {
        $translates = get_few_translates(
            'map', 
            'map_id', 
            $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($news))))
        );
        foreach ( $news as $new ) {
            $new = translates_for_page($lang, $def_lang, $translates[$new['id']], $new, true);
//            $content = bite_str(strip_tags(substr($new['content'], 0, strpos($new['content'], '<!-- pagebreak -->'))),0,330);

            if($new['gallery'] && $new['picture']){
                $image = $new['gallery'].'/'.$new['picture'];
            }else{
                $image = 'images/news_default.jpg';
            }
            $new_link = $prefix.$url_lang.$link.'/'.$new['url'];
            $news_html .= '<div class="item" data-link="'.$new_link.'">
                            <a href="'.$new_link.'">
                                <span class="news_img">'
//                                    .'<span style="background-image: url(\''.$prefix.'images/' . $image . '\')">'
                                        .'<img src="'.$prefix.'images/' . $image . '" alt="'.$new['name'].'">'
//                                    .'</span>
                                .'</span>
                                <span class="news_img_shadow"></span>
                                <span class="news_title">'
//                                    .bite_str($new['name'].' '.$new['name'].' '.$new['name'].' '.$new['name'], 0, 64)
                                    .bite_str($new['name'], 0, 64)
                                .'</span>
                                <span class="news_date">'
                                    .date('d.m.Y', strtotime($new['uxt']))
                                .'</span>
                                
                            </a>
                        </div>';
        }
        
        $news_html = '
                    <div class="slider_container" data-left="0">
                        <div data-nav="prev" class="slider_left_arrow"></div>
                        <div class="items_container">
                            <div class="slider_items">'
                                .$news_html
                            .'</div>
                        </div>
                        <div data-nav="next" class="slider_right_arrow"></div>
                    </div>';
    }

    return $news_html;
}
/*
function gen_news_block1($quantity){
    global $db, $prefix, $configs;
    $news_section_id = 66;
    $link = '';
    $arr1 = $db->query('SELECT parent, url FROM {map} WHERE `id`=?i',
            array($news_section_id), 'row');
    $arr2 = $db->query('SELECT parent, url FROM {map} WHERE `id`=?i',
            array($arr1['parent']), 'row');
    $link = $arr2['url'].'/'.$arr1['url'];
    $news = $db->query('SELECT url, `name`, `id`, `content` FROM {map} WHERE `parent`=?i AND `state`=1
        ORDER BY `uxt` DESC,`prio` LIMIT 0, ?i', array($news_section_id, $quantity), 'assoc');
    $news_html = '';
    if ($news) {
        $news_html .= '<div class="news_block">
                        <div class="news_block_title" data-link="'.$prefix.$link.'">
                            <i></i>Новости
                        </div>
                        ';
        foreach ( $news as $new ) {
            $content = bite_str(strip_tags(substr($new['content'], 0, strpos($new['content'], '<!-- pagebreak -->'))),0,330);

            $new_link = $prefix.$link.'/'.$new['url'];
            $news_html .= '<div class="one_news_block" data-link="'.$new_link.'">
                            <div class="news_title">
                                <div><div>'
//                                .bite_str($new['name'], 0, 38)
                                .$new['name']
                                .'</div></div>
                            </div>
                            <div class="news_content">'
                                .$content
                            .'</div>
                        </div>';
        }
        $news_html .= '</div>';
    }

    return $news_html;
}

if ($settings['content_block_news_quantity']) {
    $input_html['news_block']= gen_news_block1($settings['content_block_news_quantity']) ;
}
 */

?>
