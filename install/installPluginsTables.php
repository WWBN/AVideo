<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
echo "1 - Install tables and enable plugins\n";
echo "2 - Install tables only\n";
echo "3 - Enable plugins only\n";
echo "Choose an option: ";
ob_flush();
$option = trim(readline(""));

if ($option == 1 || $option == 2) {

    function rsearch($folder, $pattern) {
        $dir = new RecursiveDirectoryIterator($folder);
        $ite = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
        $fileList = array();
        foreach ($files as $file) {
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
                    echo ($value . ' Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br />');
                    die(json_encode($obj));
                } else {
                    echo "Success performing query from $value\n";
                }
                $templine = '';
            }
        }
    }
}
if ($option == 1 || $option == 3) {
    $EnablePlugins = array(
        array('1apicbec-91db-4357-bb10-ee08b0913778','API','API'),
        array('6daca392-7b14-44fb-aa33-51cba620d92e','CookieAlert','CookieAlert'),
        array('55a4fa56-8a30-48d4-a0fb-8aa6b3f69033','CustomizeAdvanced','CustomizeAdvanced'),
        array('55a4fa56-8a30-48d4-a0fb-8aa6b3fuser3','CustomizeUser','CustomizeUser'),
        array('a06505bf-3570-4b1f-977a-fd0e5cab205d','Gallery','Gallery'),
        array('e06b161c-cbd0-4c1d-a484-71018efa2f35','Live','Live'),
        array('5310b394-b54f-48ab-9049-995df4d95239','NextButton','NextButton'),
        array('plist12345-370-4b1f-977a-fd0e5cabtube','Programs','PlayLists'),
        array('b5e223db-785b-4436-8f7b-f297860c9be0','ReportVideo','ReportVideo'),
        array('f7596843-51b1-47a0-8bb1-b4ad91f87d6b','TheaterButton','TheaterButton'),
        array('45432a78-d0c6-47f3-8ac4-8fd05f507386','User_Location','User_Location'),
        array('4c1f4f76-b336-4ddc-a4de-184efe715c09','MobileManager','MobileManager')
    );
    foreach ($EnablePlugins as $value) {
        $obj = new Plugin(0);
        $obj->loadFromUUID($value[0]);
        $obj->setName($value[1]);
        $obj->setDirName($value[2]);
        $obj->setStatus("active");
        if ($obj->save()) {
            echo "Success enable plugin " . $obj->getName() . "\n";
        } else {
            echo "ERROR enable plugin ($value) " . $obj->getName() . "\n";
        }
    }
}
echo "Option {$option} finished \n";