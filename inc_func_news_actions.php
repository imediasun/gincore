<?php

function get_timeleft_txt($time){
    $days = (int)($time / 86400);
    $time %= 86400;
    $hours = floor($time / 3600);
    $time %= 3600;
    $minutes = floor($time / 60);
    
    return ($days ? $days." ".get_txt_end($days, 'день', 'дня', 'дней')." " : '').
           ($hours ? $hours." ".get_txt_end($hours, 'час', 'часа', 'часов')." " : '').
           ($minutes ? $minutes." ".get_txt_end($minutes, 'минута', 'минуты', 'минут') : '');
}

function generate_news($mod, $type){ // 1 - news, 2 - actions
    
    global $prefix, $url_lang, $db, $url_all_levels, $arrequest, $configs, $lang, $def_lang, $template_vars, $input_html;
    
    $is_news = $type == 1;
    $is_actions = $type == 2;
    
//    $configs = Configs::get();

    $count_on_page = 4; // количество news на страничке

    if( isset($_GET['p']) ){
        cannonical_page_for_pagination('p');
        $current_page = $_GET['p']-1;
    }else{
        $current_page = 0;
    }

    $all_news = $db->query('SELECT count(*) FROM {map}  WHERE `parent`=?i AND `state`=1', array($mod['id']), 'el');

    // количество страниц
    $count_pages = ceil($all_news/$count_on_page);
    // выбераем товары по странице

    include('shop/products.class.php');
    $products = new Products();
    $pages = '';
    if($count_pages > 1){
        $pages .= $products->page_block($count_pages);
    }

    $start = $current_page*$count_on_page;

    // достаем все новости
    $news = $db->query('SELECT `uxt`, url, picture, gallery,  `id` FROM {map} WHERE `parent`=?i AND `state`=1
        ORDER BY `uxt` DESC,`prio` LIMIT ?i, ?i', array($mod['id'], $start, $count_on_page), 'assoc:id');
    $news_html = '';
    if($news){
        $i = 0;
//        $news_html = '<div class="news_row">';
        $translates = get_few_translates(
            'map', 
            'map_id', 
            $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($news))))
        );
        foreach ( $news as $new ) {
            $new = translates_for_page($lang, $def_lang, $translates[$new['id']], $new, true);
            if ( strtotime($new['uxt']) > time() && $is_news ) {
                continue;
            }
            if ( strtotime($new['uxt']) < time() && $is_actions ) {
                continue;
            }
//            $i ++;
//            if($i == 3){
//                $i = 1;
//                $news_html .= '<div class="clear_both"></div></div><div class="news_row">';
//            }
            $content_parts = explode('<!-- pagebreak -->', $new['content']);
            if($new['gallery'] && $new['picture']){
                $image = $new['gallery'].'/'.$new['picture'];
            }else{
                if($is_actions){
                    $image = 'actions/default.jpg';
                }else{
                    $image = 'news/default.jpg';
                }
            }
            
            if($is_actions){
                $end = strtotime($new['uxt']) - time();
                $date =
                        '<div class="pn_time">До окончания осталось <span>'.get_timeleft_txt($end).'</span></div>';
            }else{
                $date =
                        '<div class="pn_time">'.date('d.m.Y', strtotime($new['uxt'])).'</div>';
            }
            $link = $prefix.$url_lang.$arrequest[0].'/'.$arrequest[1].'/'.$new['url'];
            $news_html .= 
                '<div class="page_news">'.
                    '<div class="page_news_inner">'
                        .$date
                        .'<div><a href="'.$link.'" class="pn_title">'.$new['name'].'</a></div>'
                        .'<a href="'.$link.'" class="pn_image"><img src="'.$prefix.'images/' . $image . '" alt=" "></a>'
                        //.'<div class="pn_about">' . strip_tags($content_parts[0]) . '</div>'
                        .'<div class="clear_both"></div>'
                    .'</div>'.
                '</div>';
            
        }
//        $news_html .= '<div class="clear_both"></div></div>';
    }else{
        if($is_actions){
            $news_html = $template_vars['l_no_actions_text'];
        }else{
            $news_html = $template_vars['l_no_news_text'];
        }
    }

    $js = '
        <script>
            $(function(){
                $.fn.maxHeight = function() {
                    var max = 0;
                    this.each(function() {
                      max = Math.max( max, $(this).height() );
                    });
                    return max;
                  };
                var news = $(".page_news");
                $(window).resize(function(){
                   news.css({height: ""});
                   var max_h = news.maxHeight();
                   news.css({height: max_h});
                });
                $(window).resize();
            });
        </script>
    ';
    $js = '';
    return 
        '<div class="page_news_list">'.
            $news_html.
            '<div class="clear_both"></div>'.
        '</div>'.
        '<div class="page_news_pages">'.
            $pages.
        '</div>'.$js;
    
}