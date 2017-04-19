<?php

header('Content-Type: application/json');
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$obj = new stdClass();
if (empty($_POST['filename'])) {
    $obj->error = __("POST Progress File name cannot be empty");
    echo json_encode($obj);
    exit;
}
$progressFilename = "{$global['systemRootPath']}videos/downloaded/{$_POST['filename']}_downloadProgress.txt";
if (!file_exists($progressFilename)) {
    $obj->error = __("Progress File does not exists") . " [{$progressFilename}]";
    echo json_encode($obj);
    exit;
}
$content = file_get_contents($progressFilename);
preg_match_all('/\[download\] +([0-9.]+)% of/', $content, $matches, PREG_SET_ORDER);
$m = end($matches);
$obj->progress = empty($m[1]) ? 0 : intval($m[1]);
echo json_encode($obj);
exit;
