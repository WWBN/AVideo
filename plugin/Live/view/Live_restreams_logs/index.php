<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage("You can not do this");
    exit;
}

$_page = new Page(array('Live Restream'));
$_page->setIncludeInHead(array('plugin/Live/view/Live_restreams_logs/index_head.phpp'));
$_page->setIncludeInBody(array('plugin/Live/view/Live_restreams_logs/index_body.php'));
$_page->print();
?>