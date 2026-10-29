<?php

// Started by Live::restreamInBackground() from on_publish, so the live recording and the other
// on_publish hooks do not wait for the restreamer to launch every destination.
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
AVideoPlugin::loadPlugin('Live');

$liveTransmitionHistory_id = intval(@$argv[1]);
if (empty($liveTransmitionHistory_id)) {
    _error_log('restreamInBackground.php: liveTransmitionHistory_id is empty');
    exit;
}

_error_log("restreamInBackground.php: start liveTransmitionHistory_id={$liveTransmitionHistory_id}");
$result = Live::restream($liveTransmitionHistory_id);
_error_log("restreamInBackground.php: finished liveTransmitionHistory_id={$liveTransmitionHistory_id} result=" . json_encode($result));
