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


$content = '
    <div class="date">'.date('d.m.Y', strtotime($mod['uxt'])).'</div>
    '. $mod['content'];

// $gallery_to_page c модуля "Галерея к странице"
if(isset($gallery_to_page)){
    $content = str_replace('{-page_gallery-}', $gallery_to_page, $content);
} else {
    // gallery album
    if($parent_type == 1)
        $input['content_gallery'] = gallery($mod['gallery'], '_gm', 'gallery_album_preview');
}

if ($mod['gallery'])
    $input['head_pics'] = get_big_pics($mod['gallery'], 3);

$input['title'] = $mod['name'];
$input['title_1'] = $cat;
$input['content'] = $content;
$input['content_image'] = $picture;
$input['content_gallery'] = $page_gallery;

?>