<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

header("Content-type: text/plain");
?>
Sitemap: <?php echo $global['webSiteRootURL']; ?>sitemap.xml
User-Agent: *
Disallow: 
Disallow: /user
Disallow: /plugin
Disallow: /mvideos
Disallow: /usersGroups
Disallow: /charts
Disallow: /upload
Disallow: /comments
Disallow: /subscribes
Disallow: /update
Disallow: /locale
Disallow: /objects/*