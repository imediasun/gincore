<?php

$dbcfg = array(
    'host'     => '192.168.1.2',
    'username' => 'root',
    'password' => 'rootFB1root',
    'dbname'   => 'u_restore_saas',
    'charset'  => 'utf8',
    '_debug'   => false,
    '_prefix'  => 'restore4_',
);

require_once 'goDB/autoload.php';
\go\DB\autoloadRegister();
$db = go\DB\DB::create($dbcfg, 'mysql');