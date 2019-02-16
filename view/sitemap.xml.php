<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$time = 600; //10 minutes

if (!file_exists($sitemapFile) || (time() - filemtime($sitemapFile)) > $time) {
    $sitemap = siteMap();
    file_put_contents($sitemapFile, $sitemap);
}
header("Content-type: text/xml");
echo file_get_contents($sitemapFile);
exit;
