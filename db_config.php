<?php

$dbcfg = array(
    'host' => '192.168.1.2',
    'username' => 'root',
    'password' => 'FB19root',
    'dbname' => 'u_restore_saas',
    'charset' => 'utf8',
    '_debug' => false,
    '_prefix' => 'restore4_',
);

if (file_exists(__DIR__ . '/db_config-local.php')) {
    $dbcfg = array_merge($dbcfg, require_once(__DIR__ . '/db_config-local.php'));
}

require_once __DIR__ . '/goDB/autoload.php';
\go\DB\autoloadRegister();
$db = go\DB\DB::create($dbcfg, 'mysql');