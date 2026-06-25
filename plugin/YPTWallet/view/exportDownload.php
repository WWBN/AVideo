<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/Objects/WalletExport.php';

if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}

if (!AVideoPlugin::isEnabledByName('YPTWallet')) {
    forbiddenPage('YPTWallet plugin is disabled', true);
}

$report = !empty($_REQUEST['report']) ? strtolower(trim($_REQUEST['report'])) : 'transactions';
$format = !empty($_REQUEST['format']) ? strtolower(trim($_REQUEST['format'])) : 'csv';
$startDate = !empty($_REQUEST['start_date']) ? $_REQUEST['start_date'] : '';
$endDate = !empty($_REQUEST['end_date']) ? $_REQUEST['end_date'] : '';

$allowedReports = ['transactions', 'balances', 'combined'];
$allowedFormats = ['csv', 'pdf'];

if (!in_array($report, $allowedReports)) {
    forbiddenPage('Invalid report', true);
}

if (!in_array($format, $allowedFormats)) {
    forbiddenPage('Invalid format', true);
}

if ($format === 'csv' && $report === 'combined') {
    forbiddenPage('Combined report is available only in PDF', true);
}

$safeSuffix = preg_replace('/[^a-z0-9_\-]/i', '', $report . '_' . date('Ymd_His'));

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="wallet_' . $safeSuffix . '.csv"');

    $output = fopen('php://output', 'w');
    if ($report === 'transactions') {
        $rows = WalletExport::getWalletTransactionsRows($startDate, $endDate);
        WalletExport::outputTransactionsCSV($output, $rows);
    } else {
        $rows = WalletExport::getWalletBalancesRows();
        WalletExport::outputBalancesCSV($output, $rows);
    }
    fclose($output);
    exit;
}

if ($report === 'transactions') {
    $rows = WalletExport::getWalletTransactionsRows($startDate, $endDate);
    $reportText = WalletExport::buildWalletTransactionsText($rows, $startDate, $endDate);
} elseif ($report === 'balances') {
    $rows = WalletExport::getWalletBalancesRows();
    $reportText = WalletExport::buildWalletBalancesText($rows);
} else {
    $transactionsRows = WalletExport::getWalletTransactionsRows($startDate, $endDate);
    $balancesRows = WalletExport::getWalletBalancesRows();
    $reportText = WalletExport::buildCombinedText($transactionsRows, $balancesRows, $startDate, $endDate);
}

$pdf = WalletExport::buildSimplePDF($reportText);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment;filename="wallet_' . $safeSuffix . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit;
