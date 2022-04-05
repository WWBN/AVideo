<?php

if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
header('Content-Type: application/json');

echo _json_encode(getMediaSession());
exit;
?>