<?php

$time_start = microtime(true);
$config = '../../videos/configuration.php';
session_write_close();
if (!file_exists($config)) {
    [$scriptPath] = get_included_files();
    $path = pathinfo($scriptPath);
    $config = $path['dirname'] . "/" . $config;
}
header('Content-Type: application/json');
require_once $config;
set_time_limit(0);
require_once $global['systemRootPath'] . 'objects/plugin.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/CloneSite.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/CloneLog.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/functions.php';

$totalSteps = 7;
$total2 = $total = 0;
$resp = new stdClass();
$resp->error = true;
$resp->msg = "";

$log = new CloneLog();

$log->add("Clone: Clone Start");

$objClone = AVideoPlugin::getObjectDataIfEnabled("CloneSite");

if (empty($objClone)) {
    $resp->msg = "Your Clone Site Plugin is not enabled";
    $log->add("Clone: {$resp->msg}");
    die(json_encode($resp));
}

if (empty($objClone->cloneSiteURL)) {
    $resp->msg = "Your Clone Site URL is empty, please click on the Edit parameters buttons and place an AVideo URL";
    _error_log("{$resp->msg} (" . json_encode($objClone) . ")");
    $log->add("Clone: {$resp->msg}");
    die(json_encode($resp));
}

$objClone->cloneSiteURL = rtrim($objClone->cloneSiteURL, "/") . '/';
$objCloneOriginal = $objClone;
$argv[1] = preg_replace("/[^A-Za-z0-9 ]/", '', @$argv[1]);

if (empty($objClone) || empty($argv[1]) || $objClone->myKey !== $argv[1]) {
    if (!User::isAdmin()) {
        $resp->msg = "You can't do this";
        $log->add("Clone: {$resp->msg}");
        echo "$objClone->myKey !== $argv[1]";
        die(json_encode($resp));
    }
}

$videosSite = "{$objClone->cloneSiteURL}videos/";
$videosDir = Video::getStoragePath() . "";
$clonesDir = "{$videosDir}cache/clones/";
$photosDir = "{$videosDir}userPhoto/";
$photosSite = "{$videosSite}userPhoto/";
if (!file_exists($clonesDir)) {
    mkdir($clonesDir, 0777, true);
    file_put_contents($clonesDir . "index.html", '');
}
if (!file_exists($photosDir)) {
    mkdir($photosDir, 0777, true);
}

$url = $objClone->cloneSiteURL . "plugin/CloneSite/cloneServer.json.php?url=" . urlencode($global['webSiteRootURL']) . "&key={$objClone->myKey}&useRsync=" . intval($objClone->useRsync);
// check if it respond
$log->add("Clone (1 of {$totalSteps}): Asking the Server the database and the files");
$content = url_get_contents($url, "", 3600, true);
_error_log("Clone: url_get_contents($url) respond: ($content)");
//var_dump($url, $content);exit;
$json = _json_decode($content);

if (empty($json)) {
    $resp->msg = "Clone Server Unknow ERROR";
    $log->add("Clone: Server Unknow ERROR");
    die(json_encode($resp));
}

if (!empty($json->error)) {
    $resp->msg = "Clone Server message: {$json->msg}";
    $log->add("Clone: {$resp->msg}");
    die(json_encode($resp));
}

$log->add("Clone: Good start! the server has answered");



$json->sqlFile = escapeshellarg(preg_replace('/[^a-z0-9_.-]/i', '', $json->sqlFile));
$json->videoFiles = escapeshellarg(preg_replace('/[^a-z0-9_.-]/i', '', $json->videoFiles));
$json->photoFiles = escapeshellarg(preg_replace('/[^a-z0-9_.-]/i', '', $json->photoFiles));

// get dump file
$cmd = "wget -O {$clonesDir}{$json->sqlFile} {$objClone->cloneSiteURL}videos/cache/clones/{$json->sqlFile}";
$log->add("Clone (2 of {$totalSteps}): Geting MySQL Dump file");
exec($cmd . " 2>&1", $output, $return_val);
if ($return_val !== 0) {
    $log->add("Clone Error: " . print_r($output, true));
}
$log->add("Clone: Nice! we got the MySQL Dump file");

// remove the first warning line
$file = "{$clonesDir}{$json->sqlFile}";
$contents = file($file, FILE_IGNORE_NEW_LINES);
$first_line = array_shift($contents);
file_put_contents($file, implode("\r\n", $contents));

$log->add("Clone (3 of {$totalSteps}): Overwriting our database with the server database");
// restore dump
$cmd = "mysql -u {$mysqlUser} -p{$mysqlPass} --host {$mysqlHost} {$mysqlDatabase} < {$clonesDir}{$json->sqlFile}";
exec($cmd . " 2>&1", $output, $return_val);
if ($return_val !== 0) {
    $log->add("Clone Error try again: " . end($output));
    $cmd2 = "sed -i 's/COLLATE=utf8mb4_0900_ai_ci/ /g' {$clonesDir}{$json->sqlFile} ";
    $log->add("Clone try again this command: {$cmd2}");
    exec($cmd2 . " 2>&1", $output2, $return_val2);
    if ($return_val2 !== 0) {
        $log->add("Clone Error: " . print_r($output2, true));
    }
    $cmd2 = "sed -i 's/COLLATE utf8mb4_0900_ai_ci/ /g' {$clonesDir}{$json->sqlFile} ";
    $log->add("and also this command: {$cmd2}");
    exec($cmd2 . " 2>&1", $output2, $return_val2);
    if ($return_val2 !== 0) {
        $log->add("Clone Error: " . end($output2));
    }
    exec($cmd . " 2>&1", $output, $return_val);
    if ($return_val !== 0) {
        $log->add("Clone Error: " . end($output));
    }
}
$log->add("Clone: Great! we overwrite it with success.");

if (empty($objClone->useRsync)) {
    $videoFiles = getCloneFilesInfo($videosDir);
    $newVideoFiles = detectNewFiles($json->videoFiles, $videoFiles);
    $photoFiles = getCloneFilesInfo($photosDir, "userPhoto/");
    $newPhotoFiles = detectNewFiles($json->photoFiles, $photoFiles);

    $total = count($newVideoFiles);
    $count = 0;

    if (!empty($total)) {
        $log->add("Clone (4 of {$totalSteps}): Now we will copy {$total} new video files, usually this takes a while.");
        // copy videos
        foreach ($newVideoFiles as $value) {
            $query = parse_url($value->url, PHP_URL_QUERY);
            if ($query) {
                $value->url .= '&ignoreXsendfilePreVideoPlay=1';
            } else {
                $value->url .= '?ignoreXsendfilePreVideoPlay=1';
            }
            $count++;
            $log->add("Clone: Copying Videos {$count} of {$total} {$value->url}");
            file_put_contents("{$videosDir}{$value->filename}", fopen("$value->url", 'r'));
        }
        $log->add("Clone: Copying video files done.");
    } else {
        $log->add("Clone (4 of {$totalSteps}): There is no new video file to copy.");
    }

    $total2 = count($newPhotoFiles);
    $count2 = 0;

    if (!empty($total2)) {
        $log->add("Clone (5 of {$totalSteps}): Now we will copy {$total2} new user photo files.");
        // copy Photos
        foreach ($newPhotoFiles as $value) {
            $count2++;
            $log->add("Clone: Copying Photos {$count2} of {$total2} {$value->url}");
            file_put_contents("{$photosDir}{$value->filename}", fopen("$value->url", 'r'));
        }
        $log->add("Clone: Copying user photo files done.");
    } else {
        $log->add("Clone (5 of {$totalSteps}): There is no new user photo file to copy.");
    }
} else {
    // decrypt the password now
    $objClone = Plugin::decryptIfNeed($objClone);
    $port = intval($objClone->cloneSiteSSHPort);
    if (empty($port)) {
        $port = 22;
    }
    $rsync = "sshpass -p '{password}' rsync -av -e 'ssh  -p {$port} -o StrictHostKeyChecking=no' --exclude '*.php' --exclude 'cache' --exclude '*.sql' --exclude '*.log' {$objClone->cloneSiteSSHUser}@{$objClone->cloneSiteSSHIP}:{$json->videosDir} " . Video::getStoragePath() . " --log-file='{$log->file}' ";
    $cmd = str_replace("{password}", $objClone->cloneSiteSSHPassword->value, $rsync);
    $log->add("Clone (4 of {$totalSteps}): execute rsync ({$rsync})");

    exec($cmd . " 2>&1", $output, $return_val);
    if ($return_val !== 0) {
        //$log->add("Clone Error: " . print_r($output, true));
    }
    $log->add("Clone (5 of {$totalSteps}): rsync finished");
}

// notify to delete dump
$url = $url . "&deleteDump={$json->sqlFile}";
// check if it respond
$log->add("Clone (6 of {$totalSteps}): Notify Server to Delete Dump");
$content2 = url_get_contents($url);
//var_dump($url, $content);exit;
$json2 = _json_decode($content);
if (!empty($json2->error)) {
    $log->add("Clone: Dump NOT deleted");
} else {
    $log->add("Clone: Dump DELETED");
}


$log->add("Clone (7 of {$totalSteps}): Resotre the Clone Configuration");
// restore clone plugin configuration
$plugin = new CloneSite();
$p = new Plugin(0);
$p->loadFromUUID($plugin->getUUID());
$p->setObject_data(addcslashes(json_encode($objCloneOriginal), '\\'));
$p->setStatus('active');
$p->save();

echo json_encode($json);
$log->add("Clone: Complete, Database, {$total} Videos and {$total2} Photos");

$cmd = "chmod -R 777 {$videosDir}";
exec($cmd);    
    
$time_end = microtime(true);
//dividing with 60 will give the execution time in minutes otherwise seconds
$execution_time = ($time_end - $time_start);
$timeStr = "Seconds";
if ($execution_time > 60) {
    $execution_time = $execution_time / 60;
    $timeStr = "Minutes";
}
//execution time of the script
$log->add('Total Execution Time: ' . $execution_time . ' ' . $timeStr);
