<?php
$limitVideos = 50;
global $global, $config;
_session_write_close();
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/comment.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

if (!User::isLogged()) {
    forbiddenPage('');
}
AVideoPlugin::getDataObject('VideosStatistics');
$users_id = User::getId();
if (!User::isAdmin()) {
    if ($config->getAuthCanViewChart() == 0 && !User::canUpload()) {
        forbiddenPage("Only video uploaders can see charts");
    }
    if ($config->getAuthCanViewChart() == 1) {
        // mode 1 means selected users see admin-charts.
        if (empty($_SESSION['user']['canViewChart'])) {
            forbiddenPage("Admin did not give you right to see the chart");
        }
    }
}

?>
<script src="<?php echo getURL('node_modules/chart.js/dist/chart.umd.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('node_modules/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('node_modules/chartjs-plugin-zoom/dist/chartjs-plugin-zoom.min.js'); ?>" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>view/css/DataTables/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo getURL('view/css/reportDashboard.css'); ?>"/>
