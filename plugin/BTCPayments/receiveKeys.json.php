<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
header('Content-Type: application/json');
$resp = new stdClass();
$resp->error = true;
$resp->msg = '';
$resp->url = '';

die(json_encode($resp));
