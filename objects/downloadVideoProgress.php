<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$progressFilename = "{$global['systemRootPath']}videos/downloaded/{$_GET['filename']}_downloadProgress.txt";
$content = file_get_contents($progressFilename);
preg_match_all('/\[download\] +([0-9.]+)% of/', $content, $matches, PREG_SET_ORDER);
echo "<pre>";
print_r($matches);
print_r(end($matches));
exit;