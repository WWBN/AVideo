<?php
header('Content-Type: application/json');
global $global, $config;
require_once __DIR__.'/../videos/configuration.php';

echo json_encode(User::getUsersCumulativePerDayJSON());
