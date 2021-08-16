<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';

if (!isCommandLineInterface()) {
    die("Command line only");
}

$force = @$argv[1];
echo 'Restart socket server' . PHP_EOL;
if($force == 'force'){
    echo 'Kill it if need' . PHP_EOL;
    restartServer();
}else{
    echo 'Do not kill if is running' . PHP_EOL;
    restartServerIfIsDead();
}