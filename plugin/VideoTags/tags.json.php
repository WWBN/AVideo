<?php
require_once '../../videos/configuration.php';
session_write_close();
require_once $global['systemRootPath'].'plugin/VideoTags/Objects/Tags.php';
header('Content-Type: application/json');

$rows = Tags::getAllTagsList($_GET['tags_types_id']);

die(json_encode($rows));