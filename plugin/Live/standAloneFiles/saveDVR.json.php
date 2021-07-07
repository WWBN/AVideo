<?php

// this file MUST be on the same directory as getRecordedFile.php

$hls_path = "/HLS/live/"; //update this URL
$streamerURL = "http://192.168.1.4/YouPHPTube/"; // change it to your streamer URL

/*
 * DO NOT EDIT AFTER THIS LINE
 */
$configFile = '../../../videos/configuration.php';
if (file_exists($configFile)) {
    include_once $configFile;
    $streamerURL = $global['webSiteRootURL'];
}

//die("Remove the line ".__LINE__." to use this script "); // remove this line so the script will work
error_log("saveDVR: Start ");
if (empty($_REQUEST['saveDVR'])) {
    error_log("saveDVR: saveDVR hash not found {$_REQUEST['saveDVR']} ");
    die('saveDVR: key not found');
}

$verifyURL = "{$streamerURL}plugin/SendRecordedToEncoder/verifyDVRTokenVerification.json.php?saveDVR={$_REQUEST['saveDVR']}";
$result = file_get_contents($verifyURL);

if (empty($result)) {
    error_log("saveDVR: We could not verify {$verifyURL} ");
    die('saveDVR: We could not verify ' . $verifyURL);
}
$result = json_decode($result);
if (!isset($result->error)) {
    error_log("saveDVR: {$result->msg}");
    die('saveDVR: ' . $result->msg);
}

if (!empty($result->error)) {
    error_log("saveDVR: ERROR " . json_encode($result));
    die('saveDVR: ERROR ' . $result->msg);
}

$key = $result->response->key;

$file = preg_replace("/[^0-9a-z_:-]/i", "", $key);

ini_set('memory_limit', '-1');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$filename = $record_path . $file . '_' . (date('Y-m-d-H-i-s')) . ".mp4";
$DVRFile = "{$hls_path}{$key}";
$tmpDVRDir = $record_path . $file . uniqid();

$isAdaptive = !is_dir($DVRFile);

if (!$isAdaptive) {
    $copyDir = "cp -R {$DVRFile} {$tmpDVRDir} && chmod -R 777 {$tmpDVRDir} ";
    error_log("saveDVR: copy dir 1 [{$copyDir}]");
    $DVRFile = "{$tmpDVRDir}" . DIRECTORY_SEPARATOR . 'index.m3u8';
    //$DVRFile .= DIRECTORY_SEPARATOR . 'index.m3u8';
} else {
    $copyDir = "mkdir {$tmpDVRDir} && cp -R {$DVRFile}* {$tmpDVRDir} && chmod -R 777 {$tmpDVRDir} ";
    error_log("saveDVR: copy dir 2 [{$copyDir}]");
    $DVRFile = "{$tmpDVRDir}" . DIRECTORY_SEPARATOR . "{$key}.m3u8";
    //$DVRFile .= ".m3u8";
}
exec($copyDir);
error_log("saveDVR: copy dir done");

if (!$isAdaptive) {
    //file_put_contents(PHP_EOL . '#EXT-X-ENDLIST', $DVRFile, FILE_APPEND);
    $endLine = PHP_EOL . '#EXT-X-ENDLIST';
    $appendCommand = "echo \"{$endLine}\" >> {$DVRFile}";
    error_log("saveDVR: append [{$appendCommand}]");
    exec($appendCommand);
} else {
    $dir = $tmpDVRDir . DIRECTORY_SEPARATOR;
    error_log("saveDVR: adaptive {$dir}");

    $list = scandir($dir);
    foreach ($list as $value) {
        if ($value != '..' && $value != ".") {
            $indexFile = $dir . $value . DIRECTORY_SEPARATOR . 'index.m3u8';
            error_log("saveDVR: checking {$indexFile}");
            if (file_exists($indexFile)) {
                $endLine = PHP_EOL . '#EXT-X-ENDLIST';
                $appendCommand = "echo \"{$endLine}\" >> {$indexFile}";
                error_log("saveDVR: append [{$appendCommand}]");
                exec($appendCommand);
                //file_put_contents(PHP_EOL . '#EXT-X-ENDLIST', $indexFile, FILE_APPEND);
            }
        }
    }
}

if (!file_exists($DVRFile)) {
    error_log("saveDVR: m3u8 File does not exists {$DVRFile} ");
    die("saveDVR: m3u8 File does not exists {$DVRFile} ");
}


$ffmpeg = "ffmpeg -i {$DVRFile} -c copy -bsf:a aac_adtstoasc {$filename} -y";

error_log("saveDVR: FFMPEG {$ffmpeg}");
exec($ffmpeg);

error_log("saveDVR: FFMPEG done");

$removeDir = "rm -R {$tmpDVRDir} ";
error_log("saveDVR: remove dir {$removeDir}");
exec($removeDir);
