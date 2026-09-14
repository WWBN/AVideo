<?php
$limitVideos = 50;
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
$_page = new Page(array('Analytics'));
$_page->setExtraScripts(['view/css/DataTables/datatables.min.js', 'view/js/reportDashboard.js']);
$_page->setExtraStyles(['view/css/DataTables/datatables.min.css', 'view/css/reportDashboard.css']);
$_page->setIncludeInHead(array('view/charts_head.php'));
include $global['systemRootPath'] . 'view/charts_body.php';
$_page->print();
?>
