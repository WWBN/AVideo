<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/Objects/Wallet_log.php';
if (!User::isAdmin()) {
    forbiddenPage("You can not do this");
    exit;
}


header('Content-Type: application/json');

// Normalize the DataTables GET-based order into $_POST['sort'] BEFORE remapping the valueText
// alias below - see plugin/YPTWallet/view/log.json.php for the same fix and full rationale.
BootGrid::populateSortFromDataTablesOrder();

if (!empty($_POST['sort']['valueText'])) {
    $_POST['sort']['value'] = $_POST['sort']['valueText'];
    unset($_POST['sort']['valueText']);
}

$row = WalletLog::getAllFromWallet(0,true,'pending');
$total = WalletLog::getTotalFromWallet(0,true,'pending');
echo '{  "current": '.getCurrentPage().',"rowCount": '.getRowCount().', "total": '.$total.', "rows":'. json_encode($row).'}';
