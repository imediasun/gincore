<?php
/**
 * модуль вывода списка галерей
 * 
 */


//echo 'content_default';

global $mod, $prefix, $arrequest, $db, $url_all_levels, $lang, $def_lang;

$albums = gen_articles(
    "parent = ?i AND state = 1", 
    array($mod['id']),
    false,
    'albums'
);

if($mod['gallery']){
    $input['head_pics'] = get_big_pics($mod['gallery'], 3);
}

$input['content'] = '
    <div class="albums_wrapper" id="albums_wrapper">
        <h2>'.$mod['name'].'</h2>
        <div class="category_about">'.$mod['content'].'</div>
        '.$albums.'
    </div>
';
    
?>