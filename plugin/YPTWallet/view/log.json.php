<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/Objects/Wallet_log.php';
if (!User::isLogged()) {
    forbiddenPage("You can not do this");
    exit;
}

if(!empty($_GET['users_id']) && User::isAdmin()){
    $users_id = $_GET['users_id'];
}else{
    $users_id = User::getId();
}

header('Content-Type: application/json');

// Normalize the DataTables GET-based order into $_POST['sort'] BEFORE remapping the computed/alias
// column names below. Without this, the remap only ever fired for the legacy Bootgrid POST
// convention; a DataTables sort request fell straight through to ObjectYPT::getSqlOrderByFromPost(),
// which would emit "ORDER BY valueText"/"previousBalance"/"balance" - none of those are real
// wallet_log columns (valueText/previousBalance are aliases, balance is computed in PHP after the
// query runs), so the query failed and the grid silently kept showing the previous page's order.
BootGrid::populateSortFromDataTablesOrder();

if (!empty($_POST['sort']['valueText'])) {
    $_POST['sort']['value'] = $_POST['sort']['valueText'];
    unset($_POST['sort']['valueText']);
} elseif (!empty($_POST['sort']['previousBalance'])) {
    $_POST['sort']['previous_wallet_balance'] = $_POST['sort']['previousBalance'];
    unset($_POST['sort']['previousBalance']);
} elseif (!empty($_POST['sort']['balance'])) {
    // balance = previous_wallet_balance + value, computed in PHP after the fetch - value is the
    // closest real-column proxy for "amount" (the column is not orderable in the UI, this only
    // guards a manually-crafted request from crashing the query).
    $_POST['sort']['value'] = $_POST['sort']['balance'];
    unset($_POST['sort']['balance']);
} elseif (!empty($_POST['sort']['created'])) {
    $_POST['sort']['id'] = 'DESC';
}

$row = WalletLog::getAllFromUser($users_id);
$total = WalletLog::getTotalFromUser($users_id);
echo '{  "current": '.getCurrentPage().',"rowCount": '.getRowCount().', "total": '.$total.', "rows":'. json_encode($row).'}';
