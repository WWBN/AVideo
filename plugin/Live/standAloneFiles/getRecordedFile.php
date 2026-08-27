<?php

//die("Remove the line ".__LINE__." to use this script "); // remove this line so the script will work
error_log("getRecordedFile: Start ");

// this file is deployed standalone on the media/live-server host (no configuration.php,
// no User/session access), so settings live in a sibling .env file instead of here —
// copy .env.example to .env and set RECORD_PATH / SECRET_KEY there
$record_path = "/var/www/tmp/"; //fallback if .env is missing
$secretKey = ""; //fallback if .env is missing (empty = deny all, fail closed)

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        list($envKey, $envValue) = array_map('trim', explode('=', $line, 2));
        if ($envKey === 'RECORD_PATH' && $envValue !== '') {
            $record_path = $envValue;
        } elseif ($envKey === 'SECRET_KEY') {
            $secretKey = $envValue;
        }
    }
}

ini_set('memory_limit', '-1');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

// fail closed: this script has no session/DB/User auth (it runs standalone on the
// media host), so it MUST NOT serve files unless SECRET_KEY is configured in .env
if (empty($secretKey) || !hash_equals($secretKey, (string) ($_REQUEST['secret'] ?? ''))) {
    error_log("getRecordedFile: invalid or missing secret");
    die('forbidden');
}

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
