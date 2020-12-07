<?php
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

require_once $global['systemRootPath'].'plugin/Meet/validateMeet.php';

if (!Meet::validatePassword($meet_schedule_id, @$_POST['meet_password'])) {
   header("Location: {$global['webSiteRootURL']}plugin/Meet/confirmMeetPassword.php?meet_schedule_id=$meet_schedule_id");
   exit;
}

$objLive = AVideoPlugin::getObjectData("Live");
Meet_join_log::log($meet_schedule_id);

$apiExecute = array();
$readyToClose = User::getChannelLink($meet->getUsers_id())."?{$userCredentials}";
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
    }
}

$domain = Meet::getDomainURL();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Meet::<?php echo $meet->getName(); ?></title>
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $config->getFavicon(true); ?>">
        <link rel="icon" type="image/png" href="<?php echo $config->getFavicon(true); ?>">
        <link rel="shortcut icon" href="<?php echo $config->getFavicon(); ?>" sizes="16x16,24x24,32x32,48x48,144x144">
        <meta name="msapplication-TileImage" content="<?php echo $config->getFavicon(true); ?>">
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.5.1.min.js"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/script.js"></script>
        <script>
            var getRTMPLink = '<?php echo Live::getRTMPLink($meet->getUsers_id()); ?>';
        </script>
        <?php
        if (!$config->getDisable_analytics()) {
            ?>
            <script>
                // AVideo Analytics
                (function (i, s, o, g, r, a, m) {
                    i['GoogleAnalyticsObject'] = r;
                    i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
                    a = s.createElement(o),
                            m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m)
                })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

                ga('create', 'UA-96597943-1', 'auto', 'aVideo');
                ga('aVideo.send', 'pageview');
            </script>
            <?php
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
            html, body {
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
            aVideoMeetStart('<?php echo $domain; ?>', '<?php echo preg_replace('/[^\00-\255]+/u', '', $meet->getCleanName()); ?>', '<?php echo Meet::getToken($meet_schedule_id); ?>', '<?php echo User::getEmail_(); ?>', '<?php echo User::getNameIdentification(); ?>', <?php echo json_encode(Meet::getButtons($meet_schedule_id)); ?>);

<?php
echo implode(PHP_EOL, $apiExecute);
?>

            function _readyToClose() {
                document.location = "<?php echo $readyToClose; ?>";
            }

        </script>
    </body>
</html>