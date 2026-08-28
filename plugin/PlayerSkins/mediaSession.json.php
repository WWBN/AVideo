<?php

if (!isset($global['systemRootPath'])) {
    $closeSessionEarlyIncludeConfig = 1;
    require_once '../../videos/configuration.php';
}
header('Content-Type: application/json');

// standalone endpoint, not gated by the video page load like plugin/PlayerSkins/mediaSession.php
forbiddenPageIfCannotWatchVideo(getVideos_id());

echo _json_encode(getMediaSession());
exit;
?>
