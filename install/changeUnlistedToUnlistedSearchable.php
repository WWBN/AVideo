<?php
//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
$sql = "UPDATE videos SET status = ? WHERE status = ? ";
sqlDAL::writeSql($sql, 'ss', array(Video::STATUS_UNLISTED_BUT_SEARCHABLE,  Video::STATUS_UNLISTED));
