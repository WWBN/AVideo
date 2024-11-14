<?php
require_once __DIR__.'/../videos/configuration.php';

header('Content-Type: application/json');

if(!User::isAdmin()){
    forbiddenPage('You Must be admin');
}

if(!empty($global['disableAdvancedConfigurations'])){
    forbiddenPage('Configuration disabled');
}

$pluginsDir = __DIR__ . '/plugins/';
$files = [];

// Check if directory exists
if (is_dir($pluginsDir)) {
    foreach (glob($pluginsDir . "*.zip") as $file) {
        $files[] = basename($file); // Add only the file name
    }
}

echo json_encode($files);
