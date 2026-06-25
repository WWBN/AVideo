<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = __DIR__ . '/../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/Objects/WalletExport.php';

if (!isCommandLineInterface()) {
    forbiddenPage('Command line only');
}

if (!AVideoPlugin::isEnabledByName('YPTWallet')) {
    die("Error: YPTWallet plugin is disabled\n");
}

$args = getopt('', ['report::', 'format::', 'output::', 'start-date::', 'end-date::', 'help']);
$interactive = isCommandLineInterface() && empty($args['help']);

if (isset($args['help'])) {
    echo "Usage:\n";
    echo "php plugin/YPTWallet/tools/export.php --report=transactions --format=csv --output=wallet_transactions.csv [--start-date=YYYY-MM-DD] [--end-date=YYYY-MM-DD]\n";
    echo "php plugin/YPTWallet/tools/export.php --report=balances --format=pdf --output=wallet_balances.pdf\n";
    echo "php plugin/YPTWallet/tools/export.php --report=combined --format=pdf --output=wallet_combined.pdf [--start-date=YYYY-MM-DD] [--end-date=YYYY-MM-DD]\n";
    exit(0);
}

$report = !empty($args['report']) ? strtolower(trim($args['report'])) : '';
$format = !empty($args['format']) ? strtolower(trim($args['format'])) : '';
$startDate = !empty($args['start-date']) ? trim($args['start-date']) : '';
$endDate = !empty($args['end-date']) ? trim($args['end-date']) : '';

$allowedReports = ['transactions', 'balances', 'combined'];
$allowedFormats = ['csv', 'pdf'];

if ($interactive) {
    echo "AVideo Wallet Export\n";
    echo "This tool exports stored wallet transactions, current balances, and PDF summaries.\n\n";

    if (empty($report)) {
        $report = askOption('Choose a report type', $allowedReports, 'transactions');
    }
    $formatOptions = ($report === 'combined') ? ['pdf'] : $allowedFormats;
    if (empty($format)) {
        $format = askOption('Choose an output format', $formatOptions, ($report === 'combined') ? 'pdf' : 'csv');
    }

    if ($report === 'transactions' || $report === 'combined') {
        if (empty($startDate)) {
            $startDate = askText('Start date (YYYY-MM-DD) or leave blank for full period', '');
        }
        if (empty($endDate)) {
            $endDate = askText('End date (YYYY-MM-DD) or leave blank for full period', '');
        }
    }

    $defaultName = 'wallet_' . $report . '_' . date('Ymd_His') . '.' . $format;
    $output = empty($args['output']) ? askText('Output file path', $defaultName) : trim($args['output']);
} else {
    $defaultName = 'wallet_' . $report . '_' . date('Ymd_His') . '.' . $format;
    $output = !empty($args['output']) ? trim($args['output']) : $defaultName;
}

if (empty($report)) {
    $report = 'transactions';
}

if (empty($format)) {
    $format = ($report === 'combined') ? 'pdf' : 'csv';
}

if (!in_array($report, $allowedReports)) {
    die("Error: invalid report. Allowed: transactions, balances, combined\n");
}

if (!in_array($format, $allowedFormats)) {
    die("Error: invalid format. Allowed: csv, pdf\n");
}

if ($format === 'csv' && $report === 'combined') {
    die("Error: combined report is available only in PDF\n");
}

$output = !empty($output) ? trim($output) : $defaultName;

$outputDir = dirname($output);
if (!empty($outputDir) && $outputDir !== '.' && !is_dir($outputDir)) {
    make_path($outputDir);
}

if ($format === 'csv') {
    $file = fopen($output, 'w');
    if (!$file) {
        die("Error: unable to open output file {$output}\n");
    }

    if ($report === 'transactions') {
        $rows = WalletExport::getWalletTransactionsRows($startDate, $endDate);
        WalletExport::outputTransactionsCSV($file, $rows);
    } else {
        $rows = WalletExport::getWalletBalancesRows();
        WalletExport::outputBalancesCSV($file, $rows);
    }

    fclose($file);
    echo "CSV generated: {$output}\n";
    exit(0);
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
if (file_put_contents($output, $pdf) === false) {
    die("Error: unable to write PDF file {$output}\n");
}

echo "PDF generated: {$output}\n";
exit(0);

function askText($question, $default = '')
{
    $prompt = $question;
    if ($default !== '') {
        $prompt .= ' [' . $default . ']';
    }
    $prompt .= ': ';

    echo $prompt;
    $answer = readConsoleLine();
    $answer = trim($answer);
    if ($answer === '') {
        return $default;
    }
    return $answer;
}

function askOption($question, $options, $default = '')
{
    echo $question . "\n";
    foreach ($options as $index => $option) {
        $number = $index + 1;
        $suffix = ($option === $default) ? ' (default)' : '';
        echo "  {$number}) {$option}{$suffix}\n";
    }

    $defaultIndex = array_search($default, $options, true);
    $defaultIndex = ($defaultIndex === false) ? 1 : ($defaultIndex + 1);
    echo 'Select an option [' . $defaultIndex . ']: ';

    $answer = trim(readConsoleLine());
    if ($answer === '') {
        return $default;
    }

    if (ctype_digit($answer)) {
        $choiceIndex = intval($answer) - 1;
        if (isset($options[$choiceIndex])) {
            return $options[$choiceIndex];
        }
    }

    $answer = strtolower($answer);
    foreach ($options as $option) {
        if (strtolower($option) === $answer) {
            return $option;
        }
    }

    echo "Invalid option, using default.\n";
    return $default;
}

function readConsoleLine()
{
    if (function_exists('readline')) {
        $line = readline();
        if (function_exists('readline_add_history') && $line !== false && $line !== '') {
            readline_add_history($line);
        }
        return $line === false ? '' : $line;
    }

    $line = fgets(STDIN);
    if ($line === false) {
        return '';
    }
    return $line;
}
