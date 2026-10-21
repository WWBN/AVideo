<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

// Set before bootstrap: loading configuration may itself write object caches.
define('AVIDEO_OBJECT_CACHE_MAINTENANCE', true);
require_once dirname(__DIR__) . '/videos/configuration.php';
require_once dirname(__DIR__) . '/objects/ObjectCacheMaintenance.php';
$root = ObjectYPT::getTmpCacheDir();
$age = isset($global['objectCacheMaxAge']) ? max(60, intval($global['objectCacheMaxAge'])) : ObjectCacheMaintenance::DEFAULT_MAX_AGE;
$result = ObjectCacheMaintenance::cleanup($root, $age);
if ($result === false) {
    exit(1);
}
echo json_encode($result) . PHP_EOL;
