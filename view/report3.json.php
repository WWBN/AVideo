<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

_session_write_close();

$reportScope = null;
if ($config->getAuthCanViewChart() == 0) {
    // list all channels
    if (User::isAdmin()) {
        $reportScope = 0;
    } elseif (User::isLogged()) {
        $reportScope = User::getId();
    } else {
        $reportScope = null;
    }
} elseif ($config->getAuthCanViewChart() == 1) {
    if ((!empty($_SESSION['user']['canViewChart']))||(User::isAdmin())) {
        $reportScope = 0;
    }
}
require_once __DIR__ . '/../objects/reportMetrics.php';
$rows = [];
try {
    if ($reportScope !== null) {
        foreach (ReportMetrics::reactions($reportScope) as $value) {
            $identification = htmlspecialchars(User::getNameIdentificationById($value['id']), ENT_QUOTES, 'UTF-8');
            $link = htmlspecialchars(User::getChannelLink($value['id']), ENT_QUOTES, 'UTF-8');
            $rows[] = [
                'thumbsUp' => (int) $value['thumbsUp'],
                'thumbsDown' => (int) $value['thumbsDown'],
                'channel' => "<a href='{$link}'>{$identification}</a>"
            ];
        }
    }

} catch (Throwable $e) {
    _error_log('Analytics query failed: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => true, 'msg' => __('Unable to load this report. Please try again.'), 'data' => []]));
}

$obj = new stdClass();

$obj->data = $rows;

echo json_encode($obj);
