<?php

$debug=true; //дебаг
$production=!$debug; //продакшн
ini_set('magic_quotes_gpc',0);
date_default_timezone_set('Europe/Kiev');

$path=str_replace('//', '/', dirname(__FILE__).'/');
$prefix=str_replace(rtrim($_SERVER['DOCUMENT_ROOT'],'/'), '', $path);

include 'db_config.php';