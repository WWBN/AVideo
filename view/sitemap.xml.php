<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$name = "sitemap.xml";
$lifetime = 43200;

$sitemap = ObjectYPT::getCache($name, $lifetime);
$videosDir = getVideosDir();
$lockFile = "{$videosDir}cache/sitemap.lock";

header("Content-type: application/xml");
if (file_exists($lockFile) && filemtime($filename) > strtotime('-10 minutes')) {
    echo "<!-- please wait -->";
    exit;
}
if (empty($sitemap)) {
    file_put_contents($lockFile, time());
    $sitemap = siteMap();
    ObjectYPT::setCache($name, $sitemap);
    unlink($lockFile);
} else {
    $sitemap .= "<!-- cached -->";
}
echo $sitemap;
exit;
