<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$time = 43200; //12 hours

if (!file_exists($sitemapFile) || (time() - filemtime($sitemapFile)) > $time) {
    $sitemap = siteMap();
    file_put_contents($sitemapFile, $sitemap);
}
header("Content-type: application/xml");
echo file_get_contents($sitemapFile);
exit;
