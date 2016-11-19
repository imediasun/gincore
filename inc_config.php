<?php

$debug=true; //дебаг
$production=!$debug; //продакшн
ini_set('magic_quotes_gpc',0);
date_default_timezone_set('Europe/Kiev');

$path=str_replace('//', '/', dirname(__FILE__).'/');
$prefix=str_replace(rtrim($_SERVER['DOCUMENT_ROOT'],'/'), '', $path);

$dbcfg = include __DIR__ . '/db_config.php';

if (file_exists(__DIR__ . '/db_config-local.php')) {
    $dbcfg = array_merge($dbcfg, require(__DIR__ . '/db_config-local.php'));
}

require_once __DIR__ . '/gincore/bootstrap/autoload.php';
$db = go\DB\DB::create($dbcfg, 'mysql');


