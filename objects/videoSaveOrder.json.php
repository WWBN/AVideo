<?php
//error_reporting(0);
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

if (!Permissions::canAdminVideos()) {
    forbiddenPage('Permission denied');
}

$obj = new stdClass();

$obj->error = true;
$obj->msg = '';
$obj->responses = array();
mysqlBeginTransaction();
Video::resetOrder();
foreach ($_REQUEST['videos'] as $key => $value) {
    $obj->responses[] = Video::updateOrder($value['videos_id'], $value['order']);
}
$obj->error = empty($obj->responses) && !empty($_REQUEST['videos']);
mysqlCommit();
echo json_encode($obj);
