<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
if(isset($_GET['yptDeviceID'])){
    unset($_GET['yptDeviceID']);
}
$sitemap_start = microtime(true);
$name = "sitemap.xml." . md5(json_encode($_GET));
$lifetime = 43200; // 12 hours

$videosDir = getVideosDir();
$lockFile = "{$videosDir}cache/sitemap.lock";
$sitemapFile = "{$videosDir}cache/{$name}";

header("Content-type: application/xml");
if (file_exists($sitemapFile)) {
    $sitemap = file_get_contents($sitemapFile);
    if (filemtime($sitemapFile) > strtotime("-{$lifetime} seconts")) {
        if (!empty($sitemap)) {
            $sitemap_end = microtime(true) - $sitemap_start;
            $sitemap .= "<!-- Created in ".number_format($sitemap_end, 4)." seconds size=".humanFileSize(strlen($sitemap))." -->";
            echo $sitemap;
            debugSiteMap(__LINE__);
            exit;
        }
    }
}

if (file_exists($lockFile) && filemtime($lockFile) > strtotime('-10 minutes')) {
    _error_log('Please wait we are creating the sitemap');
    if (empty($sitemap)) {
        echo "<!-- please wait -->";
        debugSiteMap(__LINE__);
        exit;
    } else {
        echo $sitemap;
        debugSiteMap(__LINE__);
        exit;
    }
}
if (empty($sitemap)) {
    file_put_contents($lockFile, time());
    $sitemap = siteMap();
    $result = file_put_contents($sitemapFile, $sitemap);
    _error_log('sitemap cache created ' . json_encode($result));
    
    unlink($lockFile);
} else {
    $sitemap .= "<!-- cached -->";
}
$sitemap_end = microtime(true) - $sitemap_start;
$sitemap .= "<!-- Created in {$sitemap_end} [" . seconds2human($sitemap_end) . "] -->";
echo $sitemap;
debugSiteMap(__LINE__);
exit;

function debugSiteMap($line){
    _error_log("sitemap.xml debugSiteMap($line)");
    /*
    $headers = headers_list();
    foreach ($headers as $header) {
        _error_log("sitemap.xml headers {$header}");
    }
    foreach ($_GET as $key=>$value) {
        _error_log("sitemap.xml _GET $key=>$value");
    }
    */
}