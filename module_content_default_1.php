<?php
/**
 * модуль вывода обычной статьи
 * 
 */

global $db, $prefix, $url_lang, $arrequest, $mod, $url_all_levels, $lang, $def_lang, $tpl_translates;

// определить главный раздел в котором есть вложенные категории со статьями
$is_category = $db->query("SELECT id, url FROM {map} WHERE parent = ?i LIMIT 1", array($mod['id']), 'row');
$is_main_category = false;
if($is_category){
    $is_main_category = $db->query("SELECT url FROM {map} WHERE parent = ?i LIMIT 1", array($is_category['id']), 'el');
}

// если страница главный раздел то вывести по одной последней статья из подразделов
if($is_category){
    $categories_list = array();
    $current_level_link = implode('/', $arrequest);
    
    $categories = $db->query("SELECT id, url FROM {map} WHERE parent = ?i", array($mod['id']), 'assoc:id');
    $category_ids = implode(',', array_keys($categories));
    $translates = get_few_translates(
        'map', 
        'map_id', 
        $db->makeQuery("map_id IN (?q)", array($category_ids))
    );
    foreach($categories as $category){
        $category = translates_for_page($lang, $def_lang, $translates[$category['id']], $category, true);
        $categories_list[] = '<a href="'.$prefix.$url_lang.$current_level_link.'/'.$category['url'].'">'.$category['name'].'</a>';
    }
    
    $last_articles = '';
    if($is_main_category){
        $last_articles = gen_articles(
            "parent IN (?q) AND state = 1 AND uxt = (SELECT MAX(uxt) FROM {map} WHERE parent = m.parent)",
            array($category_ids)
        );
    }
    
    
    $input['content'] = '
        <div id="category" class="post">
            <div class="entry">
                <h2>«'.$mod['name'].'»</h2>
                '.$mod['content'].'
                <p>Категории в этом разделе: '.implode(', ', $categories_list).'</p>
                '.($last_articles ? '<p>'.$tpl_translates['last_publication_in_this_category'].'</p>' : '').'
            </div>
        </div>
        '.$last_articles.'
    ';
    
}else{
    
    $book = isset($is_book);
    $parent_type = $db->query("SELECT page_type FROM {map} WHERE id = ?i", array($mod['parent']), 'el');

    // записуем просмотры если это статья галерея или книга
    $article_views = 0;
    if(in_array($parent_type, array(1, 2, 4)) || $book){
        $db->query("INSERT INTO {article_views}(article_id, views) VALUES(?i, 1)
                    ON DUPLICATE KEY UPDATE views = views + 1", array($mod['id']));
        $article_views = $db->query("SELECT views FROM {article_views} WHERE article_id = ?i", array($mod['id']), 'el');
    }

    $tags_data = '';
    $tags_top = '';

    if(!$book){
        $tags = get_page_tags($mod['id']);

        if($tags){
            function add_tag_class($val){
                return str_replace('<a', '<a class="buttn buttn_red"', $val);
            }
            $tags_b = array_map('add_tag_class', $tags);
            $tags_top = '<li class="tags">'.implode(', ', $tags).'</li>';
            $tags_data = '
                <div class="tagsdata">
                    <p class="tags longtags">
                        <strong>'.$tpl_translates['tags_title'].':</strong>
                        '.implode('', $tags_b).'
                    </p>            
                </div>
            ';
        }

    }

    $cat = '';

    $url_level = count($arrequest) - 1;
    if($url_level >= 3){
        $arrequest_parents = $arrequest;
        unset($arrequest_parents[$url_level]);
        $cat = '
            <a href="'.$prefix.$url_lang.implode('/', $arrequest_parents).'" rel="bookmark" class="article_post_category">
                <span class="catch_headline">'.$url_all_levels[$url_level-1]['name'].'</span>
            </a>
        ';
    }

    $input['content'] = '
    <article class="post">
        '.$cat.'
        <h2><a href="#" rel="bookmark">'.$mod['name'].'</a></h2>
        <ul class="postmetadata clearfix">
            <li id="logoedtarget" class="inline"></li>            
            <li class="date">'.format_date($mod['uxt']).'</li>
            '.$tags_top.'
            '.($article_views ? '<li class="views">'.$tpl_translates['views'].': '.$article_views.'</li>': '').'
        </ul>
    ';

    $book_btns = '';
    $picture = '';
    if($book){
        if($mod['picture']){
            $picture = '<img class="article_image" src="'.$prefix.'images/'.$mod['gallery'].'/'.str_replace('_m.', '.', $mod['picture']).'" alt=" ">';
        }
        $book_btns = get_book_link($db->query("SELECT * FROM {book} WHERE book_id = ?i", array($mod['id']), 'row'));
    }

    // video
    if(strpos($mod['content'], '{-page_video-}') !== false){

        $video = $db->query("SELECT * FROM {video2page} WHERE map_id = ?i", array($mod['id']), 'row');

        $video_frame = '';
        if($video['file']){
            $bn = pathinfo($video['file']);
            $ext = $bn['extension'];

            $video_path = $prefix.'videos/'.md5($video['file']).'.'.$ext;

            if($ext == 'mp3'){
                $video_frame = '
                    <a id="mb" style="display:block;width:100%;height:30px;text-decoration:none;padding:0;margin:0;"
                        href="'.$video_path.'"></a>

                        <script src="http://releases.flowplayer.org/js/flowplayer-3.2.12.min.js"></script>

                        <script>
                            $(function(){
                                $f("mb", "http://releases.flowplayer.org/swf/flowplayer-3.2.16.swf", {

                                    // fullscreen button not needed here
                                    plugins: {
                                        controls: {
                                            fullscreen: false,
                                            height: 30,
                                            autoHide: false
                                        }
                                    },

                                    clip: {
                                        autoPlay: false,
                                        // optional: when playback starts close the first audio playback
                                        onBeforeBegin: function() {
                                            $f("player").close();
                                        }

                                    }

                                });
                            });
                        </script>

                ';
            }else{
                $video_frame = '
                    <object width="16" height="9" id="flowplayer" data="'.$prefix.'extra/flowplayer-3.2.7.swf" type="application/x-shockwave-flash"  >
                        <param name="movie" value="'.$prefix.'extra/flowplayer-3.2.7.swf">
                        <param name="allowfullscreen" value="true">
                        <param name="bgcolor" value="#000000">
                        <param name="flashvars" value="config={
                            \'clip\':{
                                \'url\':\''.$video_path.'\',
                                \'autoPlay\':false,
                                \'scaling\': \'orig\'
                             }
                        }">
                    </object>
                ';
            }
            


        }elseif($video['link']){

            $video_info = getimagesize('http://img.youtube.com/vi/'.$video['link'].'/mqdefault.jpg');
            $video_frame = '
                <iframe '.$video_info[3].' src="http://www.youtube.com/embed/'.$video['link'].'" frameborder="0" allowfullscreen></iframe>
            ';

        }

        $mod['content'] = str_replace('{-page_video-}', $video_frame, $mod['content']);

    }

    // $gallery_to_page c модуля "Галерея к странице"
    if(isset($gallery_to_page)){
        $input['content'] .= '
            '.str_replace('{-page_gallery-}', $gallery_to_page, $mod['content']).'
        ';
    }else{
        // gallery album
        if($parent_type == 1){

            $input['content'] .= $mod['content'].'<div class="gallery_album" id="gallery_album">'.gallery($mod['gallery'], '_m2', 'gallery_album_preview').'</div>';

        }else{

            $input['content'] .= $picture.$mod['content'].'<br>'.$book_btns;

        }
    }

    
    /* yandex share */
    $yasha = '<script type="text/javascript" src="//yandex.st/share/share.js"
                charset="utf-8"></script>
                <div class="yashare-auto-init" data-yashareL10n="ru"
                    data-yashareType="none" data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir">
                 </div> ';
    
    $input['content'] .= '
            '.$tags_data.'
            '.$yasha.'
        </article>	
    ';
}









//<!--
//    <div class="related-articles">
//        <h2>Related Articles</h2>
//        <div class="related-article">
//            <a class="post-picture" href="#"><img src="#" alt="Usability And User Experience Testing Tools" /></a>
//            <h3><a href="#">Usability And User Experience Testing Tools</a></h3>
//        </div>
//        <div class="related-article">
//            <a class="post-picture" href="#"><img src="#" alt="A Field Guide To Mobile App Testing" /></a>
//            <h3><a href="#">A Field Guide To Mobile App Testing</a></h3>
//        </div>
//        <div class="related-article">
//            <a class="post-picture" href="#"><img src="#" alt="Better User Experience With Storytelling" /></a>
//            <h3><a href="#">Better User Experience With Storytelling</a></h3>
//        </div>
//    </div>
//<div class="bio clearfix">
//    <div class="gravatar">
//        <img alt="" src="#" class="avatar avatar-78 photo" height="78" width="78" />        </div>
//    <div class="about">
//        <a rel="author" href="#" title="Posts by Test Test"  class="post-author">Test Test</a> 
//        <p>Damian is a Director at Experience Solutions a
//        <a href="#">user experience design agency</a>
//        supporting leading UK brands. Test has 13 years experience as a UI design 
//        specialist for companies like BBC and National Air Traffic Services 
//        researching &amp; designing websites, apps, voice recognition, and air traffic control interfaces. 
//        Test regularly writes for the
//        <a href="#">Experience Solutions Blog</a> and tweets for 
//        <a href="#">#</a></p>
//    </div>
//</div>
//-->
//<!--

//-->

?>