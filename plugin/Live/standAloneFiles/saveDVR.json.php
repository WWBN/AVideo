<?php

// Define the copyDirectory function
function copyDirectory($src, $dst) {
    if (!file_exists($src)) {
        error_log("copyDirectory: Source file or directory does not exist: $src");
        return false;
    }
    if (is_dir($src)) {
        @mkdir($dst, 0777, true);
        $dir = opendir($src);
        if (!$dir) {
            error_log("copyDirectory: Failed to open directory: $src");
            return false;
        }
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                $srcPath = $src . DIRECTORY_SEPARATOR . $file;
                $dstPath = $dst . DIRECTORY_SEPARATOR . $file;
                if (is_dir($srcPath)) {
                    if (!copyDirectory($srcPath, $dstPath)) {
                        return false;
                    }
                } else {
                    if (!copy($srcPath, $dstPath)) {
                        error_log("copyDirectory: Failed to copy file $srcPath to $dstPath");
                        return false;
                    }
                    chmod($dstPath, 0777);
                }
            }
        }
        closedir($dir);
        return true;
    } else {
        // $src is a file
        @mkdir(dirname($dst), 0777, true);
        if (copy($src, $dst)) {
            chmod($dst, 0777);
            return true;
        } else {
            error_log("copyDirectory: Failed to copy file $src to $dst");
            return false;
        }
    }
}

function setLastSegments($DVRFile, $total)
{
    $parts = explode(DIRECTORY_SEPARATOR, $DVRFile);
    array_pop($parts);
    $dir = implode(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR;

    $text = file_get_contents($DVRFile);

    error_log("setLastSegments 1 $dir $DVRFile, $total " . json_encode($text));
    if (empty($total)) {
        return $text;
    }

    $array = preg_split('/$\R?^/m', $text);
    for ($i = count($array) - 1; $i >= 0; $i--) {
        if (preg_match('/[0-9]+.ts$/', $array[$i])) {
            if ($total) {
                $total--;
            } else {
                unset($array[$i]);
                unset($array[$i - 1]);
            }
            $i--;
        }
    }

    $newcontent = implode(PHP_EOL, $array);
    error_log("setLastSegments 2 " . json_encode($newcontent));
    $bytes = file_put_contents($DVRFile, $newcontent);
    error_log("setLastSegments 3 " . $bytes);
}

// this file MUST be on the same directory as getRecordedFile.php

$hls_path = "/HLS/live/"; //update this URL
$streamerURL = ""; // change it to your streamer URL

/*
 * DO NOT EDIT AFTER THIS LINE
 */
$configFile = '../../../videos/configuration.php';
if (file_exists($configFile)) {
    include_once $configFile;
    $streamerURL = $global['webSiteRootURL'];
}

if (empty($streamerURL) && !empty($_REQUEST['webSiteRootURL'])) {
    $streamerURL = $_REQUEST['webSiteRootURL'];
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
error_reporting(E_ALL & ~E_DEPRECATED);

$filename = $record_path . $file . '_' . (date('Y-m-d-H-i-s')) . ".mp4";
$DVRFile = "{$hls_path}{$key}";
$tmpDVRDir = $record_path . $file . uniqid();

$isAdaptive = !is_dir($DVRFile);

$escaped_tmpDVRDir = escapeshellarg($tmpDVRDir);

if (!$isAdaptive) {
    $escaped_DVRFile = escapeshellarg($DVRFile);
    $copyDir = "cp -R {$escaped_DVRFile} {$escaped_tmpDVRDir} && chmod -R 777 {$escaped_tmpDVRDir}";
    error_log("saveDVR: copy dir 1 [{$copyDir}]");
    $DVRFileTarget = "{$tmpDVRDir}" . DIRECTORY_SEPARATOR . 'index.m3u8';
} else {
    $escaped_DVRFile = escapeshellarg($DVRFile);
    // Append '*' after escaping the path
    $copyDir = "mkdir -p {$escaped_tmpDVRDir} && cp -R {$escaped_DVRFile}* {$escaped_tmpDVRDir} && chmod -R 777 {$escaped_tmpDVRDir}";
    error_log("saveDVR: copy dir 2 [{$copyDir}]");
    $DVRFileTarget = "{$tmpDVRDir}" . DIRECTORY_SEPARATOR . "{$key}.m3u8";
}

// Initialize output and return code variables
$output = [];
$return_var = 0;

// Execute the command and capture the output and return code
exec($copyDir, $output, $return_var);

// Check if exec failed
if ($return_var !== 0) {
    error_log("saveDVR: ERROR executing command: {$copyDir}");
    error_log("saveDVR: Command output: " . implode("\n", $output));
    error_log("saveDVR: Command return code: {$return_var}");
    
    // Try using copyDirectory function
    error_log("saveDVR: Trying to copy directory using PHP functions");
    if (copyDirectory($DVRFile, $tmpDVRDir)) {
        error_log("saveDVR: Directory copied successfully using PHP functions");
    } else {
        error_log("saveDVR: ERROR copying directory using PHP functions");
    }
} else {
    error_log("saveDVR: Command executed successfully");
}

// Check if the target directory and file exist
if (!is_dir($tmpDVRDir)) {
    error_log("saveDVR: ERROR dir does not exist $tmpDVRDir");
} else {
    error_log("saveDVR: SUCCESS dir exists $tmpDVRDir");
}

if (!is_file($DVRFileTarget)) {
    error_log("saveDVR: ERROR file does not exist $DVRFileTarget");
} else {
    error_log("saveDVR: SUCCESS file exists $DVRFileTarget");
}


$howManySegments = 0;
if (!empty($_REQUEST['howManySegments'])) {
    $howManySegments = intval($_REQUEST['howManySegments']);
}

error_log("saveDVR: copy dir done howManySegments = {$howManySegments}");
if (!$isAdaptive) {
    //file_put_contents(PHP_EOL . '#EXT-X-ENDLIST', $DVRFileTarget, FILE_APPEND);
    if (!empty($howManySegments)) {
        error_log("saveDVR: howManySegments [{$howManySegments}]");
        setLastSegments($DVRFileTarget, $howManySegments);
    }
    $endLine = PHP_EOL . '#EXT-X-ENDLIST';
    $appendCommand = "echo \"{$endLine}\" >> {$DVRFileTarget}";
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
                if (!empty($howManySegments)) {
                    error_log("saveDVR: howManySegments [{$howManySegments}]");
                    setLastSegments($indexFile, $howManySegments);
                }

                $endLine = PHP_EOL . '#EXT-X-ENDLIST';
                $appendCommand = "echo \"{$endLine}\" >> {$indexFile}";
                error_log("saveDVR: append [{$appendCommand}]");
                exec($appendCommand);
                //file_put_contents(PHP_EOL . '#EXT-X-ENDLIST', $indexFile, FILE_APPEND);
            }
        }
    }
}

if (!file_exists($DVRFileTarget)) {
    error_log("saveDVR: m3u8 File does not exists {$DVRFileTarget} ");
    die("saveDVR: m3u8 File does not exists {$DVRFileTarget} ");
}

$ffmpeg = "ffmpeg -i {$DVRFileTarget} -c copy -bsf:a aac_adtstoasc {$filename} -y";

error_log("saveDVR: FFMPEG {$ffmpeg}");
exec($ffmpeg);

error_log("saveDVR: FFMPEG done");

$removeDir = "rm -R {$tmpDVRDir} ";
error_log("saveDVR: remove dir {$removeDir}");
exec($removeDir);
