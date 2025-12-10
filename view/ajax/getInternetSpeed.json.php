<?php
require_once '../../videos/configuration.php';
require_once '../../admin/health_check_functions.php';

// Security: Only allow admin users to access this endpoint
if (!User::isAdmin()) {
    forbiddenPage('You must be an admin to access this resource');
    exit;
}

header('Content-Type: application/json');

$speedTest = getInternetSpeed();

if ($speedTest['status'] === 'success') {
    // Extract numeric values
    $downloadSpeed = floatval($speedTest['download']);
    $uploadSpeed = floatval($speedTest['upload']);
    $pingValue = intval($speedTest['ping']);

    // Evaluate performance using centralized function
    $evaluation = evaluateInternetSpeed($downloadSpeed, $uploadSpeed, $pingValue);

    $speedTest['warnings'] = $evaluation['warnings'];
    $speedTest['recommendations'] = $evaluation['recommendations'];
    $speedTest['performanceLevel'] = getPerformanceLevel($downloadSpeed, $uploadSpeed, $pingValue);

    echo json_encode($speedTest);
} else {
    echo json_encode([
        'error' => true,
        'msg' => 'Could not measure internet speed. Check your internet connection or firewall settings.',
        'download' => 'N/A',
        'upload' => 'N/A',
        'ping' => 'N/A',
        'warnings' => ['Unable to test internet connection'],
        'recommendations' => ['Ensure your server has internet access and firewall allows outbound HTTPS connections.']
    ]);
}

