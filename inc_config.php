<?php

/**
* Fonbrand company.
* Fon CMS (goDB ready)
* Файл конфига, разный в разных сайтах
* 
* @category   CMS
* @package    Functions
* @copyright  Copyright 2010-2011 Fon (http://fonbrand.com)
* @license    Fon
* @version    2011.05.31
* @author     Anatoliy Khutornoy <ragenoir@gmail.com>
* 
*/


$debug=true; //дебаг
$production=!$debug; //продакшн
ini_set('magic_quotes_gpc',0);
date_default_timezone_set('Europe/Kiev');


$path=str_replace('//', '/', dirname(__FILE__).'/');
$prefix=str_replace(rtrim($_SERVER['DOCUMENT_ROOT'],'/'), '', $path);

// 1
//$prefix = '/fon/euromedoptika/';
//$path = '/home/192.168.1.20/www'.$prefix;

#table prefix  //Временное, для совместимости
//$cfg['server']='localhost';
$cfg['server']='192.168.1.2';
$cfg['mysql_login']='root';
//$cfg['mysql_password']='rootFBroot';
$cfg['mysql_password']='rootFB1root';
$cfg['mysql_db']='u_restore';

$cfg['tbl']='restore4_';
$cfg['tbl_map']=$cfg['tbl'].'map';
$cfg['tbl_users']=$cfg['tbl'].'users';
$cfg['tbl_section']=$cfg['tbl'].'section';

#goDB
$dbcfg = array(
    'host'     => $cfg['server'],
    'username' => $cfg['mysql_login'],
    'password' => $cfg['mysql_password'],
    'dbname'   => $cfg['mysql_db'],
    'charset'  => 'utf8',
    '_debug'   => false,
    '_prefix'  => $cfg['tbl'],
);


//goDB
require_once('goDB/autoload.php'); // укажите нужный путь до goDB
\go\DB\autoloadRegister();
$db = go\DB\DB::create($dbcfg, 'mysql');


