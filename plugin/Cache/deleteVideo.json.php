<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->result = "";

if (!isCommandLineInterface()) {
    forbiddenPage('Command line only');
}

// Get command line arguments
if ($argc < 4) {
    $obj->msg = "Insufficient arguments. Usage: php script.php <videos_id> <deleteThumbs> <clearFirstPageCache>";
    die(json_encode($obj));
}

$videos_id = intval($argv[1]);
$deleteThumbs = filter_var($argv[2], FILTER_VALIDATE_BOOLEAN);
$clearFirstPageCache = filter_var($argv[3], FILTER_VALIDATE_BOOLEAN);

// Call the clearCache function with the provided arguments
$obj->clearCache = Video::_clearCache($videos_id, $deleteThumbs, $clearFirstPageCache, false);
$obj->error = false;
$obj->msg = "Cache cleared successfully";

echo json_encode($obj);
