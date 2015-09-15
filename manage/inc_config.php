<?php

date_default_timezone_set('Europe/Kiev');

//$config['sql_host'] = 'localhost';

$config['sql_host'] = '192.168.1.2';
$config['sql_login']='root';
$config['sql_pass']='rootFB1root';
//$config['sql_pass']='rootFBroot';
$config['sql_bd']='u_restore';
$config['sql_tbl_prefix']='restore4_';


$def_lang = 'ru';
$lang = 'ru';

// дефолтные шаблоны админки
$html_header = 'html_header.html';
$html_template = 'html_template.html';

#page1
$pre_title = 'Gincore manage';
$all_configs = array();
//$prefix=str_replace('//', '/', dirname(getenv('SCRIPT_NAME')).'/');//работает только из index.php (эта же директория)
$all_configs['path'] = str_replace('//', '/', dirname(__FILE__).'/');
$all_configs['prefix'] = str_replace(rtrim($_SERVER['DOCUMENT_ROOT'],'/'), '', $all_configs['path']);
//$prefix = '/fon/sahara/manage/';
//$path = '/home/192.168.1.20/www'.$prefix;

$all_configs['siteprefix'] = str_replace('manage/', '', $all_configs['prefix']);
$all_configs['sitepath'] = str_replace('manage/', '', $all_configs['path']);

$debug = true;

if ($debug) {
    error_reporting(E_ALL); 
} else {
    error_reporting(0); 
}


#goDB
$dbcfg = array(
    'host'     => $config['sql_host'],
    'username' => $config['sql_login'],
    'password' => $config['sql_pass'],
    'dbname'   => $config['sql_bd'],
    'charset'  => 'utf8',
    '_debug'   => false,
    '_prefix'  => $config['sql_tbl_prefix'],
);


//goDB
require_once($all_configs['path'].'goDB/autoload.php'); // укажите нужный путь до goDB
\go\DB\autoloadRegister();
$all_configs['db'] = go\DB\DB::create($dbcfg, 'mysql');

