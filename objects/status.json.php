<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
header('Access-Control-Allow-Origin: *');
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once 'functions.php';
header('Content-Type: application/json');
$obj = new stdClass();
$obj->max_file_size = get_max_file_size();
$obj->file_upload_max_size = file_upload_max_size();
$obj->videoStorageLimitMinutes = $global['videoStorageLimitMinutes'];
$obj->currentStorageUsage = getSecondsTotalVideosLength();
echo json_encode($obj);
