<?php
require_once '../videos/configuration.php';

$poster = Video::getPoster($_REQUEST['videos_id']);

header("Location: {$poster}");
exit;
