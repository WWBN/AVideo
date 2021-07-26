<?php
$isWebRTC = 1;
require_once '../../videos/configuration.php';

if(!User::canStream()){
    forbiddenPage('You cannot stream');
}


$lObj = AVideoPlugin::getDataObject('Live');
$iframeURL = $lObj->webRTC_player;
$iframeURL = addQueryStringParameter($iframeURL, 'webSiteRootURL', $global['webSiteRootURL']);
$iframeURL = addQueryStringParameter($iframeURL, 'userHash', Live::getUserHash(User::getId()));

$chatURL = '';
$chat = AVideoPlugin::loadPluginIfEnabled('Chat2');
if(!empty($chat)){
    Chat2::getChatRoomLink(User::getId(), 1, 1, 1, true);
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
                height: 100vh;
            }
            #chat2Iframe{
                position: absolute;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                
            }
        </style>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
            var player;
        </script>
        <?php
        echo AVideoPlugin::getHeadCode();
        ?>
    </head>

    <body>
        <iframe frameBorder="0" src="<?php echo $iframeURL; ?>" style="width: 100%; height: 100%;" allowusermedia allow="feature_name allow_list;feature_name allow_list;camera *;microphone *"></iframe>
        <iframe frameBorder="0" id="chat2Iframe" src="http://192.168.1.4/YouPHPTube/plugin/Chat2/?room_users_id=1&live_transmitions_history_id=1724&iframe=1&noFade=1&bubblesOnly=1" 
                style="width: 100%; height: 100%;" ></iframe>

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
        <?php
        require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
        ?>
        <!-- getFooterCode start -->
        <?php
        echo AVideoPlugin::getFooterCode();
        ?>  
        <!-- getFooterCode end -->
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
