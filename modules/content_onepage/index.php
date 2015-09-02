<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $url_lang, $url_all_levelsm, $arrequest, $db, $path;

// --------------------------------

$content = $subcontent = $title = 
$image_html = $page_gallery = $head_pics = '';

$page = $db->query("SELECT id, name, content, url, page_color, gallery, picture, fullname 
                           FROM {map} 
                           WHERE url = ? AND state = 1 AND is_page = 1 
                           ORDER BY prio", array((isset($arrequest[1]) ? $arrequest[1] : $arrequest[0])), 'row');

if ($page) {
    $title = $page['name'];
//    $color = ($page['page_color'] ? $page['page_color'] : '#bebebe');


    if($page['picture']){
        $image = 'images/'.$page['gallery'].'/'.str_replace('_m.', '.', $page['picture']);
        $img_info = getimagesize($path.$image);
        $style = 'width: 100%;';
        if($img_info[0] < $img_info[1]){
            $style = 'height: 100%;';
        }
        $image_html = '<img style="'.$style.'" src="'.$prefix.$image.'" alt="'. $title .'">';
    }
    

    if($page['gallery']){
        $page_gallery = '';
        $head_pics = get_big_pics($page['gallery'], 3);
        
    }
    $text = explode('<!-- pagebreak -->', $page['content']);
    $content .= '
                <div class="scroll_text">
                    '.$text[0].'
                </div>
    ';
    $subcontent .= '
                <div class="scroll_text">
                    <p>'. (isset($text[1]) ? $text[1] : '') .' 
                </div>
    ';
}



$input['head_pics'] = $head_pics;
$input['title'] = $title;
//$input['title_1'] = $title1;
$input['content'] = $content;
$input['content_1'] = $subcontent;
$input['content_image'] = '';
$input['content_gallery'] = $page_gallery;
?>