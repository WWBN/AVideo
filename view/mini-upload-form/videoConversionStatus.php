<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
$data = Video::getVideoConversionStatus($_GET['filename']);
header('Content-Type: application/json');
echo json_encode($data);