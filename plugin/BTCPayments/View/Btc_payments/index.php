<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once __DIR__.'/../../../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('You can not do this');
    exit;
}
$plugin = AVideoPlugin::loadPluginIfEnabled('BTCPayments');
if(empty($plugin)){
    forbiddenPage('Plugin BTCPayments is disabled');
}
$_page = new Page(array('BTCPayments'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js'));
include $global['systemRootPath'] . 'plugin/BTCPayments/View/Btc_payments/index_body.php';
$_page->print();
?>