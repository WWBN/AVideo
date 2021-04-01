<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$name = "sitemap.xml";
$lifetime = 43200;

$sitemap = ObjectYPT::getCache($name, $lifetime);

header("Content-type: application/xml");
if (empty($sitemap)) {
    $sitemap = siteMap();
    ObjectYPT::setCache($name, $sitemap);
}else{
    $sitemap .= "<!-- cached -->";
}
echo $sitemap;
exit;
