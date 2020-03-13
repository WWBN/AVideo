<?php

require_once '../../videos/configuration.php';
session_write_close();
try {
    $global['mysqli']->close();
} catch (Exception $exc) {
    //echo $exc->getTraceAsString();
}


/*
 * this file is to handle HTTP URLs into HTTPS
 */
if (!filter_var($_GET['livelink'], FILTER_VALIDATE_URL)) {
    echo "Invalid Link";
    exit;
}
header("Content-Type: video/vnd.mpegurl");
header("Content-Disposition: attachment;filename=playlist.m3u");
$content = url_get_contents($_GET['livelink']);
foreach (preg_split("/((\r?\n)|(\r\n?))/", $content) as $line) {
    $line = trim($line);
    if (!empty($line) && $line[0] !== "#") {
        if (!filter_var($line, FILTER_VALIDATE_URL)) {
            $pathinfo = pathinfo($_GET['livelink']);
            if(!empty($pathinfo["extension"])){
                $_GET['livelink'] = str_replace($pathinfo["basename"], "", $_GET['livelink']);
            }
            $line = $_GET['livelink'].$line;
        }
    }
    echo $line.PHP_EOL;
} 