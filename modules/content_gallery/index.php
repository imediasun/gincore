<?php
/**
 * модуль вывода обычной статьи
 * 
 */

global $db, $prefix, $url_lang, $arrequest, $mod, $url_all_levels, $lang, $def_lang, $tpl_translates;

// определить parent раздел
$is_category = $db->query("SELECT id, url FROM {map} WHERE parent = ?i LIMIT 1", array($mod['id']), 'row');

$cat = $picture = $article_views = $page_gallery = '';

$parent_type = $db->query("SELECT page_type FROM {map} WHERE id = ?i", array($mod['parent']), 'el');

if($mod['picture']) {
    $picture = '<div><img class="article_image" src="'.$prefix.'images/'.$mod['gallery'].'/'.str_replace('_m.', '.', $mod['picture']).'" alt="'.$mod['name'].'"></div>';
}


$url_level = count($arrequest) - 1;
if($url_level > 1) {
    $arrequest_parents = $arrequest;
    unset($arrequest_parents[$url_level]);
    $cat = '
        <a href="'.$prefix.$url_lang.implode('/', $arrequest_parents).'">
            <span class="catch_headline">'.$url_all_levels[$url_level-1]['name'].'</span>
        </a>
    ';
}


$text = explode('<!-- pagebreak -->', $mod['content']);
$content = '
            <div class="scroll_text">
                '.$text[0].'
            </div>
';
$subcontent = '
            <div class="scroll_text">
                '. (isset($text[1]) ? $text[1] : '') .' 
            </div>
';
    
if ($mod['gallery']) {
    $input['head_pics'] = get_big_pics($mod['gallery'], 3);
    $input['content_gallery'] = gallery($mod['gallery'], '_gm', 'gallery_album_preview');
}

$input['title'] = $mod['name'];
$input['title_1'] = $cat;
$input['content'] = $content;
$input['content_image'] = $picture;
$input['content_1'] = $subcontent;
$input_html['css_extra'] = '<link type="text/css" rel="stylesheet" href="'. $prefix .'extra/fancy/jquery.fancybox.css">';
$input_js['extra_files'] = '<script type="text/javascript" src="'. $prefix .'extra/fancy/jquery.fancybox.js"></script>';
?>