<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $arrequest, $db;

$input['content'] = $mod['content'];

$input['title'] = $mod['name'];

if ($mod['gallery']){
    $input['head_pics'] = get_big_pics($mod['gallery'], 3);
} else {
    $input['head_pics'] = '';
}


$text = explode('<!-- pagebreak -->', $mod['content']);
$content = '
            <div class="scroll_text">
                '.$text[0].'
            </div>
';
$subcontent = '
            <div class="scroll_text">
                <p>'. (isset($text[1]) ? $text[1] : '') .' 
            </div>
';
    
$input['content'] = $content;
$input['content_1'] = $subcontent;

if ($mod['picture']){
    $input['image'] = '<img src="'.$prefix.'images/'.$mod['gallery'].'/'.str_replace('_m.', '.', $mod['picture']).'" alt="'.$input['title'].'">';
} else {
    $input['image'] = '';
}
