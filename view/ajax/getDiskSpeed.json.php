<?php
require_once '../../videos/configuration.php';
require_once '../../admin/health_check_functions.php';

// Security check - only admins can access this
if (!User::isAdmin()) {
    forbiddenPage('Only admins can access this endpoint');
    exit;
}

header('Content-Type: application/json');

$result = getDiskIOSpeedImproved();

// Evaluate disk performance
$diskType = getDiskType(getVideosDir());
$warnings = [];
$recommendations = [];

if (isset($result['readSpeed']) && isset($result['writeSpeed'])) {
    $evaluation = evaluateDiskPerformance($diskType, $result['readSpeed'], $result['writeSpeed']);
    $warnings = $evaluation['warnings'];
    $recommendations = $evaluation['recommendations'];
}

$result['diskType'] = $diskType;
$result['warnings'] = $warnings;
$result['recommendations'] = $recommendations;
$result['status'] = 'success';

echo json_encode($result);
