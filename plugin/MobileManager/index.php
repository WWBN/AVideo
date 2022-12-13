<?php
global $global, $config;
$global['isIframe'] = 1;
// is online
// recorder
// live users

$global['ignoreUserMustBeLoggedIn'] = 1;
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    require_once $configFile;
}
$html = '';
if (!empty($_REQUEST['user']) && !empty($_REQUEST['pass'])) {
    User::loginFromRequest();
    $html .= 'loginFromRequest ';
    if (User::isLogged()) {
        $html .= 'is Logged ';
    } else {
        $html .= 'is NOT Logged ';
    }
} else if (User::isLogged()) {
    $users_id = User::getId();
    $livet = LiveTransmition::createTransmitionIfNeed($users_id); 
    $_REQUEST['live_transmitions_id'] = $livet['id'];
    $getLiveKey = setLiveKey($livet['key'], $livet['live_servers_id'], $livet['live_index']);
    //var_dump($livet, $getLiveKey, isLive());exit;
    if (AVideoPlugin::isEnabledByName('Chat2')) {
        $room_users_id = $users_id;
        $latest = LiveTransmitionHistory::getLatestFromUser($users_id);
        $live_transmitions_history_id = $latest['id'];
        $iframe = 1;
        $noFade = 1;
        $bubblesOnly = 1;
        $getLogin = 1;
        $addChatTextBox = 1;

        $iframeURL = Chat2::getChatRoomLinkWithParameters($room_users_id, $live_transmitions_history_id, $iframe, $noFade, $bubblesOnly, $getLogin, $addChatTextBox);

        $html = '<iframe 
        id="yptchat2Iframe"
        src="' . $iframeURL . '" 
        frameborder="0" scrolling="no" title="chat widget" 
        allowtransparency="true" 
        sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-presentation allow-top-navigation"
        style="
        outline: none; 
        visibility: visible; 
        resize: none; 
        box-shadow: none; 
        overflow: visible; 
        background: none transparent; 
        opacity: 1; 
        padding: 0px; 
        margin: 0px; 
        transition-property: none; 
        transform: none; 
        width: 100%; 
        z-index: 999999; 
        cursor: auto; 
        float: none; 
        border-radius: unset; 
        pointer-events: auto; 
        display: block; 
        height: 100vh;"></iframe>';

        //include "{$global['systemRootPath']}plugin/Chat2/index.php";
        //return false;
    }
    if (AVideoPlugin::isEnabledByName('LiveUsers')) {
        $html .= getLiveUsersLabelHTML();
        $html .= '<div id="LiveUsersLabelLive">'.getLiveUsersLabelLive($livet['key'], $livet['live_servers_id']).'</div>';
        //$html .= '<div id="LiveUsersLabelLive">'.getLiveUsersLabelLive($lt['key'], $lt['live_servers_id']).'</div>';
        //$html .= getIncludeFileContent($global['systemRootPath'] . 'plugin/Live/view/menuRight.php');
        
        //var_dump($lt);exit;
    }
} else {
    $html .= 'nothing to do ';
    if (User::isLogged()) {
        $html .= 'is Logged ';
    } else {
        $html .= 'is NOT Logged ';
    }
}
?>
<!DOCTYPE html>
<html lang="">
    <head>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            #accessibility-toolbar, footer, #socket_info_container{
                display: none !important;
            }
            body {
                padding: 0;
            }
            
            .liveUsersLabel, #LiveUsersLabelLive{
                position: fixed;
                top: 80px !important;
            }
            .liveUsersLabel{
                left: 20px !important;
            }
            #LiveUsersLabelLive{
                left: 80px !important;
            }
            #recorderToEncoderActionButtons{
                position: absolute;
                top: 0;
            }
        </style>
    </head>

    <body style="background-color: transparent;">
        <?php
        echo $html;
        if(AVideoPlugin::isEnabledByName('SendRecordedToEncoder')){
            include $global['systemRootPath'] . 'plugin/SendRecordedToEncoder/actionButtonLive.php';
            
        }
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            window.addEventListener("flutterInAppWebViewPlatformReady", function (event) {
                window.flutter_inappwebview.callHandler('AVideoMobileLiveStreamer', 'Loaded app');
            });
        </script>
    </body>
</html>