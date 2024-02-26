<?php
$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$isCDNEnabled = AVideoPlugin::isEnabledByName('AI');

if (empty($isCDNEnabled)) {
    return die('Plugin disabled');
}

echo PHP_EOL . " Start! " . PHP_EOL;

AI:: deleteAllRecords();

echo PHP_EOL . " Done! " . PHP_EOL;
die();
