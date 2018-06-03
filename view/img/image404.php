<?php
$configFile = dirname(__FILE__).'/../../videos/configuration.php';
require_once $configFile;
session_write_close();

//$file = 'static2.gif';
//$type = 'image/gif';
$file = 'video-placeholder.png';
$type = 'image/png';

// if the thumb is not ready yet, try to find the default image
if(preg_match('/videos\/(.*)_thumbs.jpg$/', $_SERVER["REQUEST_URI"], $matches)){
    $jpg = "{$global['systemRootPath']}videos/{$matches[1]}.jpg";
    if(file_exists($jpg)){
        $file = $jpg;
        $type = 'image/jpg';
        header("HTTP/1.0 404 Not Found");
        header('Content-Type:' . $type);
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

if(empty($_GET['notFound'])){
    header("Location: {$global['webSiteRootURL']}img/image404.php?notFound=1");
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
