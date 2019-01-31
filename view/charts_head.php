<?php
$limitVideos = 50;
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/comment.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';

if (!User::isLogged()) {
    header("Location: " . $global['webSiteRootURL']);
}

if (empty($_POST['rowCount'])) {
    $_POST['rowCount'] = $limitVideos;
}
if ($config->getAuthCanViewChart() == 0) {
    if (User::isAdmin()) {
        $videos = Video::getAllVideos("viewable", true, true, array(), true);
        $totalVideos = Video::getTotalVideos("viewable");
        $totalUsers = User::getTotalUsers();
        $totalSubscriptions = Subscribe::getTotalSubscribes();
        $totalComents = Comment::getTotalComments();
        unset($_POST['rowCount']);
        $totalInfos = Video::getTotalVideosInfo("viewable", false, false, array(), true);
    } else {
        $videos = Video::getAllVideos("viewable", true, true, array(), true);
        $totalVideos = Video::getTotalVideos("", true);
        $totalUsers = User::getTotalUsers();
        $totalSubscriptions = Subscribe::getTotalSubscribes(User::getId());
        $totalComents = Comment::getTotalComments(0, 'NULL', User::getId());
        unset($_POST['rowCount']);
        $totalInfos = Video::getTotalVideosInfo("", true, false, array(), true);
    }
} else if ($config->getAuthCanViewChart() == 1) {
    // mode 1 means selected users see admin-charts.
    if ((!empty($_SESSION['user']['canViewChart'])) || (User::isAdmin())) {
        $videos = Video::getAllVideos("viewable", true, true, array(), true);
        $totalVideos = Video::getTotalVideos("viewable");
        $totalUsers = User::getTotalUsers(true);
        $totalSubscriptions = Subscribe::getTotalSubscribes(true);
        $totalComents = Comment::getTotalComments(true);
        unset($_POST['rowCount']);
        $totalInfos = Video::getTotalVideosInfo("viewable", false, false, array(), true);
    } else {
        die("403 - You have no access here!");
    }
}
$labelToday = array();
for ($i = 0; $i < 24; $i++) {
    $labelToday[] = "{$i} h";
}
$label7Days = array();
for ($i = 7; $i >= 0; $i--) {
    $label7Days[] = date("Y-m-d", strtotime("-{$i} days"));
}
$label30Days = array();
for ($i = 30; $i >= 0; $i--) {
    $label30Days[] = date("Y-m-d", strtotime("-{$i} days"));
}
$label90Days = array();
for ($i = 90; $i >= 0; $i--) {
    $label90Days[] = date("Y-m-d", strtotime("-{$i} days"));
}
$statistc_lastToday = VideoStatistic::getTotalToday("");
$statistc_last7Days = VideoStatistic::getTotalLastDays("", 7);
$statistc_last30Days = VideoStatistic::getTotalLastDays("", 30);
$statistc_last90Days = VideoStatistic::getTotalLastDays("", 90);

$bg = $bc = $labels = $labelsFull = $datas = $datas7 = $datas30 = $datasToday = $datasUnique = array();
foreach ($videos as $value) {
    $labelsFull[] = ($value["title"]);
    $labels[] = substr($value["title"], 0, 20);
    $datas[] = $value["statistc_all"];
    $datasToday[] = $value["statistc_today"];
    $datas7[] = $value["statistc_week"];
    $datas30[] = $value["statistc_month"];
    $datasUnique[] = $value["statistc_unique_user"];
    $r = rand(0, 255);
    $g = rand(0, 255);
    $b = rand(0, 255);
    $bg[] = "rgba({$r}, {$g}, {$b}, 0.5)";
    $bc[] = "rgba({$r}, {$g}, {$b}, 1)";
}
?>
<script src="<?php echo $global['webSiteRootURL']; ?>view/js/Chart.bundle.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
<style>
    /* Custom Colored Panels */
    .dashboard .panel-heading {
        color: #fff;
    }
    .dashboard .loading {
        color: #FFFFFF55;
    }

    .huge {
        font-size: 40px;
    }

    <?php
    $cssPanel = array(
        'green' => array('5cb85c', '3d8b3d'),
        'red' => array('d9534f', 'b52b27'),
        'yellow' => array('f0ad4e', 'df8a13'),
        'orange' => array('f26c23', 'bd4a0b'),
        'purple' => array('5133ab', '31138b'),
        'wine' => array('ac193d', '9c091d'),
        'blue' => array('2672ec', '0252ac')
    );
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