<?php

$dbcfg = array(
    'host'     => '192.168.1.2',
    'username' => 'root',
    'password' => 'rootFB1root',
    'dbname'   => 'u_restore',
    'charset'  => 'utf8',
    '_debug'   => false,
    '_prefix'  => 'restore4_',
);

require_once __DIR__.'/goDB/autoload.php';
\go\DB\autoloadRegister();
$db = go\DB\DB::create($dbcfg, 'mysql');