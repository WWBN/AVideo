<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
$global['printLogs'] = 1;
AVideoPlugin::loadPlugin('Live');

$key = $argv[1];
$live_servers_id = intval(@$argv[2]);
$start = intval(@$argv[3]);

_error_log("controlRecording.php: Arguments received - key={$key}, live_servers_id={$live_servers_id}, start={$start}");
_error_log("controlRecording.php: Raw argv=" . json_encode($argv));

$result = Live::controlRecording($key, $live_servers_id, $start);
_error_log("controlRecording.php: Result=" . json_encode($result));
