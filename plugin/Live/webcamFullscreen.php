<?php
$isWebRTC = 1;
require_once '../../videos/configuration.php';

if (!User::canStream()) {
    forbiddenPage('You cannot stream');
}

$lObj = AVideoPlugin::getDataObject('Live');
$iframeURL = Live::getWebRTCPlayer();
$iframeURL = addQueryStringParameter($iframeURL, 'webSiteRootURL', $global['webSiteRootURL']);
$iframeURL = addQueryStringParameter($iframeURL, 'userHash', Live::getUserHash(User::getId()));

$chatURL = '';
$chat = AVideoPlugin::loadPluginIfEnabled('Chat2');
if (!empty($chat) && empty(Chat2::getEmbedURL(User::getId()))) {
    $chatURL = Chat2::getChatRoomLink(User::getId(), 1, 1, 1, true, 1);
    if (!empty($_REQUEST['user'])) {
        $chatURL = addQueryStringParameter($chatURL, 'user', $_REQUEST['user']);
    }
    if (!empty($_REQUEST['pass'])) {
        $chatURL = addQueryStringParameter($chatURL, 'pass', $_REQUEST['pass']);
    }
}
$users_id = User::getId();
$trasnmition = LiveTransmition::createTransmitionIfNeed($users_id);
$live_servers_id = Live::getCurrentLiveServersId();
$forceIndex = "Live" . date('YmdHis');
$liveStreamObject = new LiveStreamObject($trasnmition['key'], $live_servers_id, $forceIndex, 0);
$streamName = $liveStreamObject->getKeyWithIndex($forceIndex, true);
$controls = Live::getAllControlls($streamName);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="<?php echo getCDN(); ?>view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo getCDN(); ?>view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getCDN(); ?>view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getURL('plugin/Live/webRTC.css'); ?>" rel="stylesheet" type="text/css"/>
        <script src="<?php echo getCDN(); ?>view/js/jquery-3.5.1.min.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <style>
            body {
                padding: 0 !important;
                margin: 0 !important;
                width: 100vw;
                height: 100%;
                overflow:hidden;
                background-color: #000;
                position: fixed;
                top: 0;
            }
            iframe{
                width: 100vw;
                height: calc(100% - 100px);
            }
            #chat2Iframe{
                position: absolute;
                top: 45px;
                left: 0;
                /* pointer-events: none; */
            }
            #liveControls{
                position: fixed;
                top: 10px;
                right: 10px;
                opacity: 0.8;
            }

            #controls{
                position: absolute;
                bottom: 10px;
                width: 100%;
            }
            #controls .col{
                padding: 0 5px;
            }
            
        </style>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
            var live_servers_id = '<?php echo $live_servers_id; ?>';
            var player;
            var forceIndex = '<?php echo $forceIndex; ?>';
            var webrtcUser = '<?php echo User::getUserName(); ?>';
            var webrtcPass = '<?php echo User::getUserPass(); ?>';
        </script>
        <?php
        echo AVideoPlugin::getHeadCode();
        ?>
    </head>

    <body>
        <iframe frameBorder="0" 
                src="<?php echo $iframeURL; ?>" 
                allowusermedia allow="camera *;microphone *"></iframe>
                <?php
                if (!empty($chatURL)) {
                    ?>
            <iframe frameBorder="0" 
                    id="chat2Iframe" 
                    src="<?php echo $chatURL; ?>" 
                    ></iframe>
                    <?php
                }
                ?>
        <div style="z-index: 999; position: absolute; top:5px; left: 5px; opacity: 0.8; filter: alpha(opacity=80);" class="liveEmbed">
            <?php
            include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
            echo getLiveUsersLabel();
            ?>
        </div>
        <script src="<?php echo getURL('view/js/script.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>view/js/js-cookie/js.cookie.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>view/js/jquery-toast/jquery.toast.min.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>view/js/seetalert/sweetalert.min.js" type="text/javascript"></script>
        <!-- getFooterCode start -->
        <?php
        echo AVideoPlugin::getFooterCode();
        ?>  
        <!-- getFooterCode end -->
        <div class="" id="controls">
            <div class="col col-xs-9" id="webRTCPleaseWait" >
                <button class="btn btn-warning btn-block" data-toggle="tooltip"  title="<?php echo __("Please Wait"); ?>" disabled="disabled">
                    <i class="fas fa-spinner fa-pulse"></i> <?php echo __("Please Wait"); ?>
                </button>
            </div>
            <div class="col col-xs-9" id="webRTCDisconnect" >
                <button class="btn btn-danger btn-block" onclick="webRTCDisconnect();" data-toggle="tooltip"  title="<?php echo __("Stop"); ?>">
                    <i class="fas fa-stop"></i> <?php echo __("Stop"); ?>
                </button>
            </div>
            <div class="col col-xs-9" id="webRTCConnect" >
                <button class="btn btn-success btn-block" onclick="webRTCConnect();" data-toggle="tooltip" title="<?php echo __("Start Live Now"); ?>">
                    <i class="fas fa-circle"></i> <?php echo __("Go Live"); ?>
                </button>
            </div>
            <div class="col col-xs-3">
                <button class="btn btn-primary btn-block" onclick="webRTCConfiguration();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Configuration"); ?>">
                    <i class="fas fa-cog"></i> <span class="hidden-sm hidden-xs"><?php echo __("Configuration"); ?></span>
                </button>
            </div>
        </div>
        <?php
        echo $controls;
        ?>
        <!-- WebRTC finish -->
        <script src="<?php echo getURL('plugin/Live/webRTC.js'); ?>" type="text/javascript"></script>
        <script>
                    var updateControlStatusLastState;

                    $(document).ready(function () {
                        
                    });

                    function webRTCModalConfigShow() {
                        $('#chat2Iframe').fadeOut();
                    }
                    function webRTCModalConfigHide() {
                        $('#chat2Iframe').fadeIn();
                    }

                    function socketLiveONCallback(json) {
                        console.log('socketLiveONCallback webcamFullscreen', json);
                        if (typeof onlineLabelOnline == 'function') {
                            selector = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
                            onlineLabelOnline(selector);
                            selector = '.liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
                            onlineLabelOnline(selector);
                        }
                        if (json.key == '<?php echo $streamName; ?>') {
                            webRTCisLive();
                        }
                    }

                    function socketLiveOFFCallback(json) {
                        console.log('socketLiveOFFCallback webcamFullscreen', json);
                        if (typeof onlineLabelOffline == 'function') {
                            selector = '#liveViewStatusID_' + json.key + '_' + json.live_servers_id;
                            //console.log('socketLiveOFFCallback 2', selector);
                            onlineLabelOffline(selector);
                            selector = '.liveViewStatusClass_' + json.key + '_' + json.live_servers_id;
                            //console.log('socketLiveOFFCallback 3', selector);
                            onlineLabelOffline(selector);
                            selector = '.liveViewStatusClass_' + json.cleanKey;
                            //console.log('socketLiveOFFCallback 3', selector);
                            onlineLabelOffline(selector);
                        }
                        if (json.key == '<?php echo $streamName; ?>') {
                            webRTCisOffline();
                        }
                    }
        </script>
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
