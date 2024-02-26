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

function currentVersionLowerThen($currentversion, $oldversion)
{
    return version_compare($currentversion, $oldversion) > 0;
}

$updateDir = $global['systemRootPath'] . "updatedb/";
$currentVersion = $config->getVersion();

echo "Searching on ({$updateDir}) for updates greater then {$currentVersion}" . PHP_EOL;
global $global;
$files1 = scandir($updateDir);
$updateFiles = [];
foreach ($files1 as $value) {
    preg_match("/updateDb.v([0-9.]*).sql/", $value, $match);
    if (!empty($match)) {
        if (currentVersionLowerThen($match[1], $currentVersion)) {
            $updateFiles[] = ['filename' => $match[0], 'version' => $match[1]];
        }
    }
}

if (empty($updateFiles)) {
    echo "No new update files found on ({$updateDir})" . PHP_EOL;
} else {
    echo "Found ".count($updateFiles)." updaets" . PHP_EOL;
}

foreach ($updateFiles as $value) {
    echo "Updating version " . $value['version'] . PHP_EOL;

    $lines = file("{$updateDir}{$value['filename']}");
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
}

echo PHP_EOL . " Done! " . PHP_EOL;
die();
