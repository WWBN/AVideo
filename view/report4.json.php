<?php
header('Content-Type: application/json');
global $global, $config;
require_once __DIR__.'/../videos/configuration.php';

// platform-wide registration counts; admin-only, matching the report4.php page gate
if (!User::isAdmin()) {
    forbiddenPage('Only admin can see the users report');
}

echo json_encode(User::getUsersPerDayJSON());
