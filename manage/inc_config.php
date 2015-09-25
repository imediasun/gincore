<?php

date_default_timezone_set('Europe/Kiev');

$def_lang = 'ru';
$lang = 'ru';

// дефолтные шаблоны админки
$html_header = 'html_header.html';
$html_template = 'html_template.html';

#page1
$pre_title = 'Manage';
$all_configs = array();
$all_configs['path'] = str_replace('//', '/', dirname(__FILE__).'/');
$all_configs['prefix'] = str_replace(rtrim($_SERVER['DOCUMENT_ROOT'],'/'), '', $all_configs['path']);

$all_configs['siteprefix'] = str_replace('manage/', '', $all_configs['prefix']);
$all_configs['sitepath'] = str_replace('manage/', '', $all_configs['path']);

$debug = true;

if ($debug) {
    error_reporting(E_ALL); 
} else {
    error_reporting(0); 
}

include $all_configs['sitepath'].'db_config.php';
$all_configs['db'] = $db;

