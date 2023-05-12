<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
$sitemap_start = microtime(true);
$name = "/sitemap.xml/".md5(json_encode($_GET));
$lifetime = 43200; // 12 hours

$sitemap = ObjectYPT::getCacheGlobal($name, $lifetime);
$videosDir = getVideosDir();
$lockFile = "{$videosDir}cache/sitemap.lock";

header("Content-type: application/xml");
if (file_exists($lockFile) && filemtime($lockFile) > strtotime('-10 minutes')) {
    _error_log('Please wait we are creating the sitemap');
    $sitemap = ObjectYPT::getCacheGlobal($name, 0);
    if (empty($sitemap)) {
        echo "<!-- please wait -->";
        exit;
    } else {
        echo $sitemap;
        exit;
    }
}
if (empty($sitemap)) {
    file_put_contents($lockFile, time());
    $sitemap = siteMap();
    $result = ObjectYPT::setCacheGlobal($name, $sitemap);
    _error_log('sitemap cache created '. json_encode($result));
    unlink($lockFile);
} else {
    $sitemap .= "<!-- cached -->";
}
$sitemap_end = microtime(true) - $sitemap_start;
$sitemap .= "<!-- Created in {$sitemap_end} [". seconds2human($sitemap_end)."] -->";
echo $sitemap;
exit;
