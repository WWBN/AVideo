<?php
//die("Remove the line ".__LINE__." to use this script "); // remove this line so the script will work

$record_path = "/var/www/tmp/"; //update this URL

if (empty($_REQUEST['file'])) {
    die('file not found');
}
$file = preg_replace("/[^0-9a-z_:-]/i", "", $_REQUEST['file']);

ini_set('memory_limit', '-1');

$filename = $record_path . $file . ".flv";
if(!file_exists($filename)){
    die('file does not exists');
}
$content = file_get_contents($filename);
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename=' . $file . ".flv");
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Type: video/x-flv');
header('Content-Length: ' . strlen($content));
echo $content;
unlink($filename);
