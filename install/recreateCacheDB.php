<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
set_time_limit(300);
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sql = 'DROP TABLE IF EXISTS `CachesInDB`';
$global['mysqli']->query($sql);
$file = $global['systemRootPath'] . 'plugin/Cache/install/install.sql';

$lines = file($file);
    foreach ($lines as $line) {
        if (substr($line, 0, 2) == '--' || $line == '') {
            continue;
        }
        $templine .= $line;
        if (substr(trim($line), -1, 1) == ';') {
            try {
                if (!$global['mysqli']->query($templine)) {
                    echo('Error performing query ' . $templine . ': ' . $global['mysqli']->error . PHP_EOL);
                    //exit;
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString(). PHP_EOL;
            } 

            $templine = '';
        }
    }
echo PHP_EOL . " Done! " . PHP_EOL;
die();
