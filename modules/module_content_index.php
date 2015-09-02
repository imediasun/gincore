<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $input, $path, $arrequest, $db;


################################################################################

$page_gallery = $mod['gallery'];

$input['content_gallery'] = gallery('', $page_gallery, true);

$input['title'] = $mod['name'];

$input['image'] = '<img src="'.$prefix.'images/'.$mod['gallery'].'/'.str_replace('_m.', '.', $mod['picture']).'" alt=" ">';



//$input['page_tree'] = gen_page_tree();





?>