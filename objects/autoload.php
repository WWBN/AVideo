<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Upgraded installations may contain the bundled PSR-7 implementation without
// its namespace in the generated Composer map. Notifications needs its PSR-17 factory.
if (!class_exists('Nyholm\\Psr7\\Factory\\Psr17Factory')
    && is_dir(__DIR__ . '/../vendor/nyholm/psr7/src')) {
    // Use a separate loader because Composer caches negative class lookups.
    $avideoPsr7Loader = new \Composer\Autoload\ClassLoader();
    $avideoPsr7Loader->addPsr4('Nyholm\\Psr7\\', __DIR__ . '/../vendor/nyholm/psr7/src');
    $avideoPsr7Loader->register();
    unset($avideoPsr7Loader);
}
