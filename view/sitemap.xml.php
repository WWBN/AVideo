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
if (file_exists($lockFile) && filemtime($lockFile) > strtotime('-10 minutes')) {
    _error_log('Please wait we are creating the sitemap');
    $sitemap = ObjectYPT::getCache($name, 0);
    if(empty($sitemap)){
        echo "<!-- please wait -->";
        exit;
    }else{
        echo $sitemap;
        exit;
    }
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
