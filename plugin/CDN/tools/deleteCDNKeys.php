<?php

$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$isCDNEnabled = AVideoPlugin::isEnabledByName('CDN');

if (empty($isCDNEnabled)) {
    return die('Plugin disabled');
}

require_once './functions.php';

set_time_limit(300);
ini_set('max_execution_time', 300);

getConnID(0);

$list = ftp_rawlist($conn_id[0], '/', true);

var_dump($list);