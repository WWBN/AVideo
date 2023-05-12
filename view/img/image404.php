<?php

$configFile = dirname(__FILE__) . '/../../videos/configuration.php';
require_once $configFile;
session_write_close();

//$file = 'static2.gif';
//$type = 'image/gif';
$file = 'video-placeholder-gray.png';
$type = 'image/png';

$imageURL = $_SERVER["REQUEST_URI"];
if (!empty($_GET['image'])) {
    $imageURL = $_GET['image'];
}
if (empty($advancedCustom)) {
    $advancedCustom = AVideoPlugin::loadPlugin("CustomizeAdvanced");
}
// if the thumb is not ready yet, try to find the default image
if (preg_match('/videos\/(.*\/)?(.*)_thumbs(V2)?.jpg/', $imageURL, $matches) && !empty($matches[2])) {
    $jpg = Video::getPathToFile("{$matches[2]}.jpg");
    if (file_exists($jpg)) {
        $file = $jpg;
        if (preg_match('/_thumbsV2/', $imageURL)) {
            _error_log("1 Image not found for {$imageURL} converting $jpg,{$global['systemRootPath']}{$imageURL}");
            convertThumbsIfNotExists($jpg, $global['systemRootPath'] . $imageURL);
        } else {
            _error_log("2 Image not found for {$imageURL} we are using {$jpg} instead ");
        }
        $type = 'image/jpg';
        //header("HTTP/1.0 404 Not Found");
        header('Content-Type:' . $type);
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
} else if (preg_match('/videos\/(.*\/)?(.*)_roku.jpg/', $imageURL, $matches) && !empty($matches[2])) {
    $jpg = Video::getPathToFile("{$matches[2]}.jpg");
    if (file_exists($jpg)) {
        $file = $jpg;
        if (preg_match('/_roku/', $imageURL)) {
            _error_log("3 Image not found for {$imageURL} converting $jpg,{$global['systemRootPath']}{$imageURL} ");
            convertImageToRoku($jpg, $global['systemRootPath'] . $imageURL);
            //convertImageIfNotExists($jpg, $global['systemRootPath'] . $imageURL, $advancedCustom->thumbsWidthPortrait / 2, $advancedCustom->thumbsHeightPortrait / 2, true);
        } else {
            _error_log("4 Image not found for {$imageURL} we are using {$jpg} instead ");
        }
        $type = 'image/jpg';
        //header("HTTP/1.0 404 Not Found");
        header('Content-Type:' . $type);
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

if (empty($_GET['notFound'])) {
    header("Location: " . getCDN() . "view/img/image404.php?notFound=1");
    exit;
}

/*
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");
  header("Pragma-directive: no-cache");
  header("Cache-directive: no-cache");
  header("Expires: 0");
 *
 */
header("HTTP/1.0 404 Not Found");
header('Content-Type:' . $type);
header('Content-Length: ' . filesize($file));
readfile($file);
