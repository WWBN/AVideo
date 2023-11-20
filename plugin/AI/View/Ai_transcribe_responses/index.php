<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('You can not do this');
    exit;
}
$plugin = AVideoPlugin::loadPluginIfEnabled('AI');
if(empty($plugin)){
    forbiddenPage('Plugin AI is disabled');
}
$_page = new Page(array('AI'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js'));
include $global['systemRootPath'] . 'plugin/AI/View/{$classname}/index_body.php';
$_page->print();
?>