<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/Mobile_Detect.php';
$detect = new Mobile_Detect;
if (!$detect->isMobile() && !$detect->isTablet()) {
    if ($detect->is('Chrome')) {
        include $global['systemRootPath'] . 'plugin/PlayerSkins/allowAutoplay/chrome.php';
    } else if ($detect->is('Safari')) {
        include $global['systemRootPath'] . 'plugin/PlayerSkins/allowAutoplay/safari.php';
    } else if ($this->is('Firefox')) {
        include $global['systemRootPath'] . 'plugin/PlayerSkins/allowAutoplay/firefox.php';
    } else {
        include $global['systemRootPath'] . 'plugin/PlayerSkins/allowAutoplay/chrome.php';
    }
}