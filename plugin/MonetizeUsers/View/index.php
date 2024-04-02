<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("VideosStatistics");
$_page = new Page(array('Monetize user'));
include $global['systemRootPath'] . 'plugin/MonetizeUsers/View/report.php';
$_page->print();
?>