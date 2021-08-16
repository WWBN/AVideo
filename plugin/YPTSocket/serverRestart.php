<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';

if (!isCommandLineInterface()) {
    die("Command line only");
}

$force = @$argv[1];

if($force = 'force'){
    restartServer();
}else{
    restartServerIfIsDead();
}