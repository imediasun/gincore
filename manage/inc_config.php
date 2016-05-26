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

define('DEBUG', true);

if (file_exists(__DIR__ . '/inc_config-local.php')) {
    require(__DIR__ . '/inc_config-local.php');
}

$debug = DEBUG;

if ($debug) {
    error_reporting(E_ALL);
    ini_set('error_reporting', E_ALL & ~E_NOTICE);
    ini_set('display_errors', 1);
} else {
    error_reporting(0); 
}

include $all_configs['sitepath'].'db_config.php';
$all_configs['db'] = $db;
$all_configs['dbcfg'] = $dbcfg;

