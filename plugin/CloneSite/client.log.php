<?php
$time_start = microtime(true);
$doNotConnectDatabaseIncludeConfig = 1;
$config = '../../videos/configuration.php';
require_once $config;
session_write_close();
//_mysql_close();
ob_end_flush();
header('Content-Type: plain/text');
if(User::isAdmin()){
    include $global['systemRootPath'] . 'videos/cache/clones/client.log';
}