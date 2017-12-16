<?php

require_once dirname(__FILE__) . '/../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';

if (empty($_GET['file'])) {
    die('GET file not found');
}

$path_parts = pathinfo($_GET['file']);
$file = $path_parts['basename'];
$path = "{$global['systemRootPath']}videos/{$file}";
YouPHPTubePlugin::xsendfilePreVideoPlay();
header("X-Sendfile: {$path}");
header("Content-type: " . mime_content_type($path));
header('Content-Length: ' . filesize($path));
die();
