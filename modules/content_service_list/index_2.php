<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $arrequest, $db;

//$input['page_tree'] = gen_page_tree();

$content = '';
$picture1 = '';
$id = $mod['parent'];


include 'consult/inc_consult.php';

if(!isset($arrequest[1])){
    $text = gen_content_array($mod['content']);
    $content .= '
                <div class="article_block_inner service_desc '.(($arrequest[0]==$mod['url']) ? 'active' : '').'" data-rel="'.$mod['url'].'">
                    <h1>'.$mod['name'].'</h1>'
                    .$text[0]
                .'</div>
                ';
    $id = $mod['id'];
    $arrequest[1] = '';
}


$articles = $db->query("SELECT url, uxt, picture, gallery, name, fullname, content FROM {map} WHERE parent = ?i AND state = 1 ORDER BY prio", array($id), 'assoc');
//print_r($mod);


foreach($articles as $article){
    
    $picture_url = '';
    if($article['picture']) {
            $picture_url = $prefix.'images/'.$article['gallery'].'/'.str_replace('_m.', '.', $article['picture']);
    }

        $picture1 .= '<a href="'.$prefix.$arrequest[0].'/'.$article['url'].'" class="remont pjax'.(($arrequest[1]==$article['url']) ? ' active' : '').'"
                    data-rel="'.$article['url'].'" data-title="'.($article['fullname'] ? $article['fullname'] : $article['name']).'">
                    <span class="remont_image"'
                    .($picture_url ? ' style="background-image: url(\''.$picture_url.'\')"' : '').'></span>
                    <span>'.$article['name'].'</span>
                </a>';

    if($arrequest[1]==$article['url']) {
        $text = gen_content_array($article['content']);
        $content .= '
                    <div class="article_block_inner '.(($arrequest[1]==$article['url']) ? 'active' : '').'" data-rel="'.$article['url'].'">
                        <h1>Ремонт '.$article['name'].'</h1>'
                        .$text[0]
                    .'</div>
                    ';
           
    }
}

$picture_block = '';
if ($picture1)
$picture_block = '<div class="service_container">
    <div class="fotos_left_arrow" data-nav="prev"></div>
        <div class="fotos_container">
            <div class="content_images">
            '.$picture1.'
            </div>
        </div>
    <div class="fotos_right_arrow" data-nav="next"></div>
</div>';




$content_block = $content;
            
/*
if ($mod['gallery']){
    $input['head_pics'] = get_big_pics($mod['gallery'], 3);
} 
*/
$input['content_images'] = $picture_block;
$input['content'] = $content_block;

$input_html['css_extra'] = '<link type="text/css" rel="stylesheet" href="'. $prefix .'extra/fancy/jquery.fancybox.css">';
$input_js['extra_files'] = '<script type="text/javascript" src="'. $prefix .'extra/fancy/jquery.fancybox.js"></script>';

?>