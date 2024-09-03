<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once __DIR__.'/../../../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('You can not do this');
    exit;
}
$plugin = AVideoPlugin::loadPluginIfEnabled('UserConnections');
if(empty($plugin)){
    forbiddenPage('Plugin UserConnections is disabled');
}
$_page = new Page(array('UserConnections'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js'));
include $global['systemRootPath'] . 'plugin/UserConnections/View/Users_connections/index_body.php';
$_page->print();
?>