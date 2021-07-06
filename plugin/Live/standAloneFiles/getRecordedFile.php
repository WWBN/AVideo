<?php
//die("Remove the line ".__LINE__." to use this script "); // remove this line so the script will work
error_log("getRecordedFile: Start ");
$record_path = "/var/www/tmp/"; //update this URL

ini_set('memory_limit', '-1');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (empty($_REQUEST['saveDVR'])) {
    if (empty($_REQUEST['file'])) {
        error_log("getRecordedFile: file not found {$_REQUEST['file']} ");
        die('file not found');
    }
    $file = preg_replace("/[^0-9a-z_:-]/i", "", $_REQUEST['file']);
    $filename = $record_path . $file . ".flv";
    if (!file_exists($filename)) {
        error_log("getRecordedFile: file does not exists {$filename} ");
        die('file does not exists');
    }
    $contentType = 'video/x-flv';
    $attachmentName = $file . '.flv';
} else {
    require_once './saveDVR.json.php';
    $contentType = 'video/mp4';
    $attachmentName = $file . '_' . (date('Y-m-d-H-i-s')) . '.mp4';
}

$size = filesize($filename);
error_log("getRecordedFile: $filename " . filesize($filename));

header('Content-Description: File Transfer');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $size);
header('Content-Type: '.$contentType);
header('Content-Disposition: attachment; filename=' . $attachmentName);

// stream the file
$fp = fopen($filename, 'rb');
fpassthru($fp);
error_log("getRecordedFile: $filename finish ");
//unlink($filename); // uncomment this for autodelete, or create a crontab to delete old files
// Auto delete files older than 7 days
//@daily root find /var/www/tmp/*.flv -mtime +6 -type f -delete