<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

header("Content-type: text/plain");
?>
Sitemap: <?php echo $global['webSiteRootURL']; ?>sitemap.xml
User-Agent: *
Allow: /plugin/Live/?*
Allow: /plugin/PlayLists/*.css
Allow: /plugin/PlayLists/*.js
Allow: /plugin/TopMenu/*.css
Allow: /plugin/TopMenu/*.js
Allow: /plugin/SubtitleSwitcher/*.css
Allow: /plugin/SubtitleSwitcher/*.js
Allow: /plugin/Gallery/*.css
Allow: /plugin/Gallery/*.js
Allow: /plugin/YouPHPFlix2/*.png
Allow: /plugin/Live/*.css
Allow: /plugin/Live/*.js
Allow: /plugin/*.css
Allow: /plugin/*.js
Allow: .js
Allow: .css
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
Allow: /plugin/Live/?*
Allow: /plugin/PlayLists/*.css
Allow: /plugin/PlayLists/*.js
Allow: /plugin/TopMenu/*.css
Allow: /plugin/TopMenu/*.js
Allow: /plugin/SubtitleSwitcher/*.css
Allow: /plugin/SubtitleSwitcher/*.js
Allow: /plugin/Gallery/*.css
Allow: /plugin/Gallery/*.js
Allow: /plugin/YouPHPFlix2/*.png
Allow: /plugin/Live/*.css
Allow: /plugin/Live/*.js
Allow: /plugin/*.css
Allow: /plugin/*.js
Allow: .js
Allow: .css