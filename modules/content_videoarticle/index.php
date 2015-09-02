<?php
global $mod, $prefix, $url_lang, $db, $arrequest, $template_vars;

//print_r($mod);


$youtube_videos = $db->query('SELECT youtube_videos, url '
                           . 'FROM {map} WHERE parent=?i AND  youtube_videos != "" AND state = 1 '
                           . 'ORDER BY RAND() LIMIT 4', array(1475))->assoc();

$youtube_videos_html = '';

if (isset($youtube_videos) && is_array($youtube_videos) && count($youtube_videos) > 0) {

    foreach ($youtube_videos as $v) {
        if ($v) {
            $youtube_videos_html .= '<div class="col-sm-3 video_blog">'
                    . '<a href="'.$prefix.$url_lang.'restore/video/'.$v['url'].'" rel="nofollow"><img src="//img.youtube.com/vi/'.trim($v['youtube_videos']).'/sddefault.jpg" alt="'. $mod['name'].'" width="100%"></a>'
                    . '</div>';
            
            
        }
    }
}

$back_link = gen_full_link($mod['category_id']);

$input['back'] = '<a href="'.$prefix.$url_lang.$arrequest[0].'/'.$arrequest[1].'" class="font_size_14">'.$template_vars['l_return_to_videos'].'</a><br><br>';
$input['title'] = ($mod['fullname'] ? $mod['fullname'] : $mod['name']);
$input['video'] = '<div class="row"><div class="col-sm-10 col-sm-offset-1">'
                . '<iframe width="100%" height="430" src="https://www.youtube.com/embed/'.$mod['youtube_videos'].'" frameborder="0" allowfullscreen></iframe>'
                . '<br><br>';
$input['content'] = $mod['content'] . ''
                . ''
                . '</div></div>'
                . '<div class="video_back_link">'
                . ($mod['description_name'] ? '<a href="'.$prefix.$url_lang.$back_link.'">'.$mod['description_name'].'</a>' : '')
                . '</div>'
                . '<h2>'.$template_vars['video_blog_see_other_title'].'</h2><div class="row">' . $youtube_videos_html.'</div><br><br>';
//$input['content'] = print_r($mod, true);
$input['content_image'] = 'image';
$input['content_gallery'] = 'gal';