<?php

if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
header('Content-Type: application/json');
if(empty($_REQUEST['videos_id'])){
    forbiddenPage('Videos ID is empty');
}


echo _json_encode(Video::getMediaSession($_REQUEST['videos_id']));
exit;
?>