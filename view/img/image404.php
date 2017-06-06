<?php
if(empty($_GET['rand'])){
    header("Location: ?rand=".rand());
    exit;
}
$file = 'static2.gif';
$type = 'image/gif';
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Pragma-directive: no-cache");
header("Cache-directive: no-cache");
header("Expires: 0");
header("HTTP/1.0 404 Not Found");
header('Content-Type:' . $type);
header('Content-Length: ' . filesize($file));
readfile($file);
