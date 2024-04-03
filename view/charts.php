<?php
$limitVideos = 50;
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
$_page = new Page(array('Dashboard'));
$_page->setIncludeInHead(array('view/charts_head.php'));
include $global['systemRootPath'] . 'view/charts_body.php';
$_page->print();
?>