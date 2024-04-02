<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once dirname(__FILE__) . '/../../../../videos/configuration.php';
}
if (!User::canStream()) {
    forbiddenPage();
    exit;
}

$_page = new Page(array('Live'));
include $global['systemRootPath'] . 'plugin/Live/view/Live_schedule/panel.php';
$_page->print();
?>