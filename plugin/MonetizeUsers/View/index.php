<?php
require_once '../../../videos/configuration.php';
// report.php filters by User::getId(), and getRewards() treats 0 as "every creator" (GHSA-923j-cj44-f9g8)
if (!User::isLogged()) {
    forbiddenPage('You must login to see your rewards');
}
// the MonetizeUsers and YPTWallet classes are only loaded for enabled plugins; report.php uses both
if (!AVideoPlugin::isEnabledByName('MonetizeUsers')) {
    forbiddenPage('MonetizeUsers is disabled');
}
AVideoPlugin::loadPlugin("YPTWallet");
AVideoPlugin::loadPlugin("VideosStatistics");
$_page = new Page(array('Monetize user'));
include $global['systemRootPath'] . 'plugin/MonetizeUsers/View/report.php';
$_page->print();
?>