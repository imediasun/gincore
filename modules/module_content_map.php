<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $input, $path, $db;


################################################################################

$page_gallery = $mod['gallery'];

$input['content_gallery'] = gallery('', $page_gallery, true);

$input['title'] = $mod['name'];

$input['image'] = '<img src="'.$prefix.'images/'.$mod['gallery'].'/'.str_replace('_m.', '.', $mod['picture']).'" alt=" ">';

?>