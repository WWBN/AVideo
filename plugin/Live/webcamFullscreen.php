<?php
$isWebRTC = 1;
require_once '../../videos/configuration.php';

if (!User::canStream()) {
    forbiddenPage('You cannot stream');
}


$lObj = AVideoPlugin::getDataObject('Live');
$iframeURL = $lObj->webRTC_player;
$iframeURL = addQueryStringParameter($iframeURL, 'webSiteRootURL', $global['webSiteRootURL']);
$iframeURL = addQueryStringParameter($iframeURL, 'userHash', Live::getUserHash(User::getId()));

$chatURL = '';
$chat = AVideoPlugin::loadPluginIfEnabled('Chat2');
if (!empty($chat)) {
    $chatURL = Chat2::getChatRoomLink(User::getId(), 1, 1, 1, true, 1);
    if(!empty($_REQUEST['user'])){
        $chatURL = addQueryStringParameter($chatURL, 'user', $_REQUEST['user']);
    }
    if(!empty($_REQUEST['pass'])){
        $chatURL = addQueryStringParameter($chatURL, 'pass', $_REQUEST['pass']);
    }
}
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
        <link href="<?php echo getCDN(); ?>view/css/player.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo getCDN(); ?>view/js/jquery-3.5.1.min.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <style>
            body {
                padding: 0 !important;
                margin: 0 !important;
                width: 100vw;
                height: 100vh;
                overflow:hidden;
                background-color: #000;
            }
            iframe{
                width: 100vw;
                height: calc(100vh - 45px);
            }
            #chat2Iframe{
                position: absolute;
                top: 0;
                left: 0;
                /* pointer-events: none; */
            }
            #controls{
                position: absolute;
                bottom: 5px;
                width: 100%;
            }
            #controls .col{
                padding: 0 5px;
            }
        </style>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
            var live_servers_id = '<?php echo Live::getCurrentLiveServersId(); ?>';
            var player;
        </script>
        <?php
        echo AVideoPlugin::getHeadCode();
        ?>
    </head>

    <body>
        <iframe frameBorder="0" 
                src="<?php echo $iframeURL; ?>" 
                allowusermedia allow="feature_name allow_list;feature_name allow_list;camera *;microphone *"></iframe>
        <?php
        if(!empty($chatURL)){
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
            $streamName = $uuid;
            include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
            echo getLiveUsersLabel();
            ?>
        </div>
        <script src="<?php echo getCDN(); ?>view/js/script.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>view/js/js-cookie/js.cookie.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>view/js/jquery-toast/jquery.toast.min.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>view/js/seetalert/sweetalert.min.js" type="text/javascript"></script>
        <!-- getFooterCode start -->
        <?php
        echo AVideoPlugin::getFooterCode();
        ?>  
        <!-- getFooterCode end -->
        <div class="" id="controls">
            <div class="col col-xs-8" id="webRTCDisconnect" style="display: none;" >
                <button class="btn btn-danger btn-block" onclick="webRTCDisconnect();" data-toggle="tooltip"  title="<?php echo __("Stop"); ?>">
                    <i class="fas fa-stop"></i> <?php echo __("Stop"); ?>
                </button>
            </div>
            <div class="col col-xs-8" id="webRTCConnect" style="display: none;" >
                <button class="btn btn-success btn-block" onclick="webRTCConnect();" data-toggle="tooltip" title="<?php echo __("Start Live Now"); ?>">
                    <i class="fas fa-circle"></i> <?php echo __("Go Live"); ?>
                </button>
            </div>
            <div class="col col-xs-4">
                <button class="btn btn-primary btn-block" style="" onclick="webRTCConfiguration();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Configuration"); ?>">
                    <i class="fas fa-cog"></i> <span class="hidden-sm hidden-xs"><?php echo __("Configuration"); ?></span>
                </button>
            </div>
        </div>
        <script src="<?php echo getCDN(); ?>plugin/Live/webRTC.js" type="text/javascript"></script>
        <script>
                    var updateControlStatusLastState;

                    function updateControlStatus() {
                        var hasclass = $('.liveOnlineLabel').hasClass('label-danger');
                        if (updateControlStatusLastState === hasclass) {
                            return false;
                        }
                        updateControlStatusLastState = hasclass;
                        if (hasclass) {
                            $('#webRTCDisconnect').hide();
                            $('#webRTCConnect').show();
                        } else {
                            $('#webRTCDisconnect').show();
                            $('#webRTCConnect').hide();
                        }
                    }

                    $(document).ready(function () {
                        updateControlStatus();
                        setInterval(function () {
                            updateControlStatus();
                        }, 500);
                    });
                    
                    function webRTCModalConfigShow(){
                        $('#chat2Iframe').fadeOut();
                    }
                    function webRTCModalConfigHide(){
                        $('#chat2Iframe').fadeIn();
                    }
        </script>
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
