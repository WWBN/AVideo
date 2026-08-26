<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
// SECURITY REVIEW: this lands in the public/web-served videos/ tree with a guessable name
// (timestamp only, no random component); anyone who learns/guesses the URL can download the zipped
// log. CSRF is closed below, but the output path itself is still not access-controlled - a future
// improvement would write it outside the web root and serve it via an authenticated download endpoint.
$obj->filename = $global['systemRootPath'].'videos'.DIRECTORY_SEPARATOR.'avideo.log_'.date('Ymd-His').'.zip';

if (!Permissions::canSeeLogs()) {
    $obj->msg = __("You cannot see the logs");
    die(json_encode($obj));
}

// mutating action (archives + truncates the live log); block GET-only CSRF (e.g. <img src=...>)
forbidIfNotPost();
forbidIfInvalidToken();

if (!empty($global['disableAdvancedConfigurations'])) {
    $obj->msg = __("This page is disabled");
    die(json_encode($obj));
}

$zip = new ZipArchive();
if ($zip->open($obj->filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    $zip->addFile($global['logfile'], 'avideo.log');
    $zip->close();
    file_put_contents($global['logfile'], '');
    $obj->error = false;
    die(json_encode($obj));
} else {
    $obj->msg = __("Error on create file");
    die(json_encode($obj));
}
