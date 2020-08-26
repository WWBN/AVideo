<?php
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

//$forceMeetDomain = "meet.wwbn.com";

$objM = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($objM)) {
    die("Plugin disabled");
}

if (empty($_GET['roomName'])) {
    die(json_encode("Empty Room"));
}

$meetDomain = Meet::getDomain();
if (empty($meetDomain)) {
    header("Location: {$global['webSiteRootURL']}plugin/Meet/?error=The Server is Not ready");
    exit;
}


$meet_schedule_id = intval($_GET['meet_schedule_id']);

if (empty($meet_schedule_id)) {
    die("meet schedule id cannot be empty");
}

$canJoin = Meet::canJoinMeetWithReason($meet_schedule_id);
if (!$canJoin->canJoin) {
    header("Location: {$global['webSiteRootURL']}plugin/Meet/?error=". urlencode($canJoin->reason));
    exit;
}

$meet = new Meet_schedule($meet_schedule_id);

if(empty($meet->getPublic()) && !User::isLogged()){
    header("Location: {$global['webSiteRootURL']}user?redirectUri=". urlencode($meet->getMeetLink())."&msg=". urlencode(__("Please, login before join a meeting")));
    exit;
}

$objLive = AVideoPlugin::getObjectData("Live");
Meet_join_log::log($meet_schedule_id);

$apiExecute = array();
$readyToClose = User::getChannelLink($meet->getUsers_id());
if (Meet::isModerator($meet_schedule_id)) {
    $readyToClose = "{$global['webSiteRootURL']}plugin/Meet/";
    if ($meet->getPassword()) {
        $apiExecute[] = "api.executeCommand('password', '" . $meet->getPassword() . "');";
    }
    if ($meet->getLive_stream()) {
        $apiExecute[] = "api.executeCommand('startRecording', {
        mode: 'stream',
        youtubeStreamKey: '" . Live::getRTMPLink() . "',
    });";
    }
}
/*
  $obj->link = Meet::getMeetRoomLink($_GET['roomName']);
  if ($obj->link) {
  $obj->error = false;
  }
  die(json_encode($obj));
 * 
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Meet::<?php echo $_GET['roomName']; ?></title>
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $config->getFavicon(true); ?>">
        <link rel="icon" type="image/png" href="<?php echo $config->getFavicon(true); ?>">
        <link rel="shortcut icon" href="<?php echo $config->getFavicon(); ?>" sizes="16x16,24x24,32x32,48x48,144x144">
        <meta name="msapplication-TileImage" content="<?php echo $config->getFavicon(true); ?>">
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.5.1.min.js"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/Meet/external_api.js" type="text/javascript"></script>
        <script>
            var getRTMPLink = '<?php echo Live::getRTMPLink(); ?>';
        </script>
        <style>
            html, body {
                height: 100%;
                margin: 0px;
                overflow: hidden;
            }
            #meet {
                height: 100%;
                background: #000;
            }
        </style>
    </head>
    <body>
        <div id="meet"></div> 
        <script>
            const domain = '<?php echo $meetDomain; ?>?getRTMPLink=<?php echo urlencode(Live::getRTMPLink()); ?>';
                const options = {
                    roomName: '<?php echo $meet->getName(); ?>',
                    jwt: '<?php echo Meet::getToken($meet_schedule_id); ?>',
                    parentNode: document.querySelector('#meet'),
                    userInfo: {
                        email: '<?php echo User::getEmail_(); ?>',
                        displayName: '<?php echo User::getNameIdentification(); ?>'
                    },
                    interfaceConfigOverwrite: {
                        TOOLBAR_BUTTONS: <?php echo json_encode(Meet::getButtons($meet_schedule_id)); ?>,
                        //SET_FILMSTRIP_ENABLED: false,
                        //DISABLE_FOCUS_INDICATOR: true,
                        //DISABLE_DOMINANT_SPEAKER_INDICATOR: true,
                        //DISABLE_VIDEO_BACKGROUND: true,
                        DISABLE_JOIN_LEAVE_NOTIFICATIONS: true,
                        disableAudioLevels: true,
                        requireDisplayName: true,
                        enableLayerSuspension: true,
                        channelLastN: 4,
                        startVideoMuted: 10,
                        startAudioMuted: 10,
                    }

                };
                const api = new JitsiMeetExternalAPI(domain, options);

                api.addEventListeners({
                    readyToClose: readyToClose,
                });
<?php
echo implode(PHP_EOL, $apiExecute);
?>

                function readyToClose() {
                    document.location = "<?php echo $readyToClose; ?>";
                }

        </script>
    </body>
</html>