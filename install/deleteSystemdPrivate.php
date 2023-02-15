<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

set_time_limit(300);
ini_set('max_execution_time', 300);
$glob = glob("/tmp/systemd-private-*-apache2.service-*/tmp/*");
$totalItems = count($glob);
echo "Found total of {$totalItems} items " . PHP_EOL;
$countItems = 0;
foreach ($glob as $file) {
    
    $countItems++;
    echo "$file".PHP_EOL;
}