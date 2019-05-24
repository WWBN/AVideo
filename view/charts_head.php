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
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';

if (!User::isLogged()) {
    header("Location: " . $global['webSiteRootURL']);
}

if (empty($_POST['rowCount'])) {
    $_POST['rowCount'] = $limitVideos;
}
$times = array();
$start = microtime(true);
if ($config->getAuthCanViewChart() == 0) {
    if (User::isAdmin()) {
        $videos = Video::getAllVideosAsync("viewable", true, true, array(), true);
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalVideos = Video::getTotalVideos("viewable");
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalUsers = User::getTotalUsers();
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalSubscriptions = Subscribe::getTotalSubscribes();
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalComents = Comment::getTotalComments();
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        unset($_POST['rowCount']);
        $totalInfos = Video::getTotalVideosInfoAsync("viewable", false, false, array(), false);
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
    } else {
        $videos = Video::getAllVideosAsync("viewable", true, true, array(), true);
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalVideos = Video::getTotalVideos("", true);
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalUsers = User::getTotalUsers();
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalSubscriptions = Subscribe::getTotalSubscribes(User::getId());
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalComents = Comment::getTotalComments(0, 'NULL', User::getId());
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        unset($_POST['rowCount']);
        $totalInfos = Video::getTotalVideosInfoAsync("", true, false, array(), false);
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
    }
} else if ($config->getAuthCanViewChart() == 1) {
    // mode 1 means selected users see admin-charts.
    if ((!empty($_SESSION['user']['canViewChart'])) || (User::isAdmin())) {
        $videos = Video::getAllVideosAsync("viewable", true, true, array(), false);
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalVideos = Video::getTotalVideos("viewable");
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalUsers = User::getTotalUsers(true);
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalSubscriptions = Subscribe::getTotalSubscribes(true);
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        $totalComents = Comment::getTotalComments(true);
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
        unset($_POST['rowCount']);
        $totalInfos = Video::getTotalVideosInfoAsync("viewable", false, false, array(), false);
        $times[__LINE__] = microtime(true) - $start;
        $start = microtime(true);
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
$statistc_lastToday = VideoStatistic::getTotalTodayAsync("");
$times[__LINE__] = microtime(true) - $start;
$start = microtime(true);
$statistc_last7Days = VideoStatistic::getTotalLastDaysAsync("", 7);
$times[__LINE__] = microtime(true) - $start;
$start = microtime(true);
$statistc_last30Days = VideoStatistic::getTotalLastDaysAsync("", 30);
$times[__LINE__] = microtime(true) - $start;
$start = microtime(true);
$statistc_last90Days = VideoStatistic::getTotalLastDaysAsync("", 90);
$times[__LINE__] = microtime(true) - $start;
$start = microtime(true);

$bg = $bc = $labels = $labelsFull = $datas = $datas7 = $datas30 = $datasToday = $datasUnique = array();
foreach ($videos as $value) {
    $value = (array) $value;
    $labelsFull[] = xss_esc($value["title"]);
    $labels[] = substr(xss_esc($value["title"]), 0, 20);
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
$times[__LINE__] = microtime(true) - $start;
$start = microtime(true);

arsort($times);
?>
<!--
<?php
foreach ($times as $key => $value) {
    echo "Line: {$key} -> {$value}\n";
}
?>
-->
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