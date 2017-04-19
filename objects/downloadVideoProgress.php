<?php
header('Content-Type: application/json');
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$progressFilename = "{$global['systemRootPath']}videos/downloaded/{$_GET['filename']}_downloadProgress.txt";
$content = file_get_contents($progressFilename);
preg_match_all('/\[download\] +([0-9.]+)% of/', $content, $matches, PREG_SET_ORDER);
$m = end($matches);
$obj = new stdClass();
$obj->progress = empty($m[1])?0:intval($m[1]);
echo json_encode($obj);
exit;