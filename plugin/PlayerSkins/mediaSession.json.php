<?php

if (!isset($global['systemRootPath'])) {
    $closeSessionEarlyIncludeConfig = 1;
    require_once '../../videos/configuration.php';
}
header('Content-Type: application/json');

echo _json_encode(getMediaSession());
exit;
?>
