<?php
//streamer config
require_once '../videos/configuration.php';

if(!isCommandLineInterface()){
    return die('Command Line only');
}

function rsearch($folder, $pattern) {
    $dir = new RecursiveDirectoryIterator($folder);
    $ite = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
    $fileList = array();
    foreach($files as $file) {
        foreach ($file as $key => $value) {
            $file[$key] = "{$folder}{$dir}/{$value}";
        }
        $fileList = array_merge($fileList, $file);
    }
    return $fileList;
}

$files = rsearch("{$global['systemRootPath']}plugin/", "/install\/install.sql$/i");
$templine = '';
foreach ($files as $value) {
    $lines = file($value);
    foreach ($lines as $line) {
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;
        $templine .= $line;
        if (substr(trim($line), -1, 1) == ';') {
            if (!$global['mysqli']->query($templine)) {
                echo ($value.' Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br />');
                die(json_encode($obj));
            }
            $templine = '';
        }
    }
}