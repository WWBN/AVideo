<?php
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

require_once $global['systemRootPath'] . 'plugin/Meet/validateMeet.php';

if (!Meet::validatePassword($meet_schedule_id, @$_REQUEST['meet_password'])) {
    header("Location: {$global['webSiteRootURL']}plugin/Meet/confirmMeetPassword.php?meet_schedule_id=$meet_schedule_id");
    exit;
}

$objLive = AVideoPlugin::getObjectData("Live");
Meet_join_log::log($meet_schedule_id);

$apiExecute = [];
$readyToClose = User::getChannelLink($meet->getUsers_id()) . "?{$userCredentials}";
if (Meet::isModerator($meet_schedule_id)) {
    $readyToClose = "{$global['webSiteRootURL']}plugin/Meet/?{$userCredentials}";
    if ($meet->getPassword()) {
        //$apiExecute[] = "api.executeCommand('password', '" . $meet->getPassword() . "');";
    }
    if ($meet->getLive_stream()) {
        $apiExecute[] = "api.executeCommand('startRecording', {
        mode: 'stream',
        youtubeStreamKey: '" . Live::getRTMPLink($meet->getUsers_id()) . "',
    });";
    } else {
        $apiExecute[] = "/* getLive_stream = false */";
    }
} else {
    $apiExecute[] = "/* not moderator */";
}

$domain = Meet::getDomainURL();

// for tests
//$domain = str_replace('ca2.ypt.me', 'ca1.ypt.me', $domain);

$nameIdentification = '';
if (!empty($_REQUEST['nameIdentification'])) {
    $nameIdentification = xss_esc($_REQUEST['nameIdentification']);
} else if (User::isLogged()) {
    $nameIdentification = User::getNameIdentification();
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Meet::<?php echo $meet->getName(); ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $config->getFavicon(true); ?>">
    <link rel="icon" type="image/png" href="<?php echo $config->getFavicon(true); ?>">
    <link rel="shortcut icon" href="<?php echo $config->getFavicon(); ?>" sizes="16x16,24x24,32x32,48x48,144x144">
    <meta name="msapplication-TileImage" content="<?php echo $config->getFavicon(true); ?>">
    <script src="<?php echo getURL('node_modules/jquery/dist/jquery.min.js'); ?>"></script>
    <script src="<?php echo getCDN(); ?>node_modules/js-cookie/dist/js.cookie.js" type="text/javascript"></script>
    <?php
    include $global['systemRootPath'] . 'view/include/bootstrap.js.php';
    ?>
    <script src="<?php echo getCDN(); ?>view/js/script.js"></script>
    <script>
        var getRTMPLink = '<?php echo Live::getRTMPLink($meet->getUsers_id()); ?>';
    </script>
    <?php
    if (!$config->getDisable_analytics()) {
        include_once $global['systemRootPath'] . 'view/include/ga.php';
    }
    echo $config->getHead();
    if (!empty($video)) {
        if (!empty($video['users_id'])) {
            $userAnalytics = new User($video['users_id']);
            echo $userAnalytics->getAnalytics();
            unset($userAnalytics);
        }
    }
    ogSite();
    ?>
    <style>
        html,
        body {
            height: 100%;
            margin: 0px;
            overflow: hidden;
        }

        #divMeetToIFrame {
            height: 100%;
            background: #000;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
        }
    </style>
    <?php
    include $global['systemRootPath'] . 'plugin/Meet/api.js.php';
    ?>
</head>

<body>
    <div id="divMeetToIFrame"></div>
    <script>
        aVideoMeetStart('<?php echo $domain; ?>', '<?php echo preg_replace('/[^\00-\255]+/u', '', $meet->getCleanName()); ?>', '<?php echo Meet::getToken($meet_schedule_id, User::getId()); ?>', '<?php echo User::getEmail_(); ?>', '<?php echo $nameIdentification; ?>', <?php echo json_encode(Meet::getButtons($meet_schedule_id)); ?>);

        <?php
        echo implode(PHP_EOL, $apiExecute);
        ?>

        function _readyToClose() {
            document.location = "<?php echo $readyToClose; ?>";
        }
    </script>
</body>

</html>
