<?php
$limitVideos = 50;
global $global, $config;
session_write_close();
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
<script src="<?php echo getURL('node_modules/chart.js/dist/chart.min.js'); ?>" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>view/css/DataTables/datatables.min.css"/>
<style>
    /* Custom Colored Panels */
    .dashboard .panel-heading {
        color: #fff;
    }
    .dashboard .loading {
        color: #FFFFFF55;
    }

    .huge {
        font-size: 30px;
    }

    <?php
    $cssPanel = [
        'green' => ['5cb85c', '3d8b3d'],
        'red' => ['d9534f', 'b52b27'],
        'yellow' => ['f0ad4e', 'df8a13'],
        'orange' => ['f26c23', 'bd4a0b'],
        'purple' => ['5133ab', '31138b'],
        'wine' => ['ac193d', '9c091d'],
        'blue' => ['2672ec', '0252ac'],
    ];
    foreach ($cssPanel as $key => $value) {
        ?>
        .panel-<?php echo $key; ?> {
            border-color: #<?php echo $value[0]; ?>;
            background-color: #<?php echo $value[0]; ?>;
        }

        .panel-<?php echo $key; ?> > a {
            color: #<?php echo $value[0]; ?>;
        }

        .panel-<?php echo $key; ?> > a:hover {
            color: #<?php echo $value[1]; ?>;
        }
        <?php
    }
    ?>

</style>