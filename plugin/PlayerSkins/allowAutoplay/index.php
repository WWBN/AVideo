<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/Mobile_Detect.php';
$detect = new Mobile_Detect;
if (!$detect->isMobile() && !$detect->isTablet()) {
    $browser = get_browser_name();
    if ($browser == ('Chrome')) {
        include $global['systemRootPath'] . 'plugin/PlayerSkins/allowAutoplay/chrome.php';
    } else if ($browser == ('Safari')) {
        include $global['systemRootPath'] . 'plugin/PlayerSkins/allowAutoplay/safari.php';
    } else if ($browser == ('Firefox')) {
        include $global['systemRootPath'] . 'plugin/PlayerSkins/allowAutoplay/firefox.php';
    } else {echo "Nao sei";
        include $global['systemRootPath'] . 'plugin/PlayerSkins/allowAutoplay/chrome.php';
    }
}