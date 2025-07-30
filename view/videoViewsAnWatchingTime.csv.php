<?php
require_once '../videos/configuration.php';
header('Content-Type: application/json');

_session_write_close();
$from = date("Y-m-d 00:00:00", strtotime($_REQUEST['dateFrom']));
$to = date('Y-m-d 23:59:59', strtotime($_REQUEST['dateTo']));
$fromDate = date("Y-m-d", strtotime($_REQUEST['dateFrom']));
$toDate = date('Y-m-d', strtotime($_REQUEST['dateTo']));
$users_id = 0;
if ($config->getAuthCanViewChart() == 0) {
    // list all channels
    if (User::isAdmin()) {
        if(empty($_REQUEST['users_id'])){
            $users_id = 'all';
        }else{
            $users_id = $_REQUEST['users_id'];
        }
    } elseif (User::isLogged()) {
        $users_id = User::getId();
    }
} elseif ($config->getAuthCanViewChart() == 1) {
    if ((!empty($_SESSION['user']['canViewChart']))||(User::isAdmin())) {
        if(empty($_REQUEST['users_id'])){
            $users_id = 'all';
        }else{
            $users_id = $_REQUEST['users_id'];
        }
    }
}

$obj = new stdClass();
$obj->data = [];

if(empty($users_id)){
    die(json_encode($obj));
}

if($users_id === 'all'){
    $users_id = 0;
}

$obj->data = VideoStatistic::getStatisticTotalViewsAndSecondsWatchingFromUser($users_id, $from, $to);

// Collect all unique externalOptions keys to create consistent columns
$allExternalOptionsKeys = [];
$processedData = [];

foreach ($obj->data as $value) {
    $externalOptions = [];
    if (!empty($value['externalOptions'])) {
        $decoded = json_decode($value['externalOptions'], true);
        if (is_array($decoded)) {
            $externalOptions = $decoded;
            if(isset($externalOptions['encoderLog'])){
                unset($externalOptions['encoderLog']);
            }
            // Collect all keys for column headers
            $allExternalOptionsKeys = array_merge($allExternalOptionsKeys, array_keys($externalOptions));
        }
    }

    $processedData[] = [
        'videos_id' => $value['videos_id'],
        'title' => $value['title'],
        'type' => $value['type'],
        'total_views' => $value['total_views'],
        'seconds_watching_video' => intval($value['seconds_watching_video']),
        'externalOptions' => $externalOptions
    ];
}

// Remove duplicates and sort keys for consistent column order
$allExternalOptionsKeys = array_unique($allExternalOptionsKeys);
sort($allExternalOptionsKeys);

// Build rows with consistent columns
$rows = [];
foreach ($processedData as $value) {
    $row = [
        $value['videos_id'],
        $value['title'],
        $value['type'],
        $value['total_views'],
        $value['seconds_watching_video']
    ];
    // Add externalOptions values in consistent column order
    foreach ($allExternalOptionsKeys as $key) {
        $optionValue = isset($value['externalOptions'][$key]) ? $value['externalOptions'][$key] : '';
        // Handle nested objects/arrays by converting to JSON string
        if (is_array($optionValue) || is_object($optionValue)) {
            $optionValue = json_encode($optionValue);
        }
        $row[] = $optionValue;
    }

    $rows[] = $row;
}

$filename = "{$users_id}_{$fromDate}_{$toDate}";
$output = fopen("php://output", 'w') or die("Can't open php://output");
$identification = 'All Users';
if(!empty($users_id)){
    $identification = User::getNameIdentificationById($users_id);
}

fputcsv($output, ['From', $fromDate, 'To', $toDate, 'User', "[{$users_id}] {$identification}"]);

// Build field headers including externalOptions columns
$fields = ['videos_id', 'title', 'type', 'total views', 'seconds watching video'];
foreach ($allExternalOptionsKeys as $key) {
    $fields[] = $key;
}

fputcsv($output, $fields);
foreach ($rows as $row) {
    fputcsv($output, $row);
}
header("Content-Type:application/csv");
header("Content-Disposition:attachment;filename={$filename}.csv");
fclose($output) or die("Can't close php://output");
