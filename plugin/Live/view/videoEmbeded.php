<?php
global $isLive;
$isLive = 1;
$isEmbed = 1;
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';

if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}
$customizedAdvanced = AVideoPlugin::getObjectDataIfEnabled('CustomizeAdvanced');

$livet = LiveTransmition::getFromRequest();
setLiveKey($livet['key'], Live::getLiveServersIdRequest(), @$_REQUEST['live_index']);

Live::checkIfPasswordIsGood($livet['key']);

if(empty($livet['live_schedule'])){
    $lt = new LiveTransmition($livet['id']);
}else{
    $lt = new Live_schedule($livet['id']);
}

if (!$lt->userCanSeeTransmition()) {
    forbiddenPage("You are not allowed see this streaming");
}
$uuid = LiveTransmition::keyNameFix($livet['key']);
$p = AVideoPlugin::loadPlugin("Live");
$objSecure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
if (!empty($objSecure)) {
    $objSecure->verifyEmbedSecurity();
}
$u = new User(0, $_GET['u'], false);
$user_id = $u->getBdId();
$video['users_id'] = $user_id;
AVideoPlugin::getModeYouTubeLive($user_id);
$_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();

if (!empty($_REQUEST['live_schedule'])) {
    $ls = new Live_schedule($_REQUEST['live_schedule']);
    $liveTitle = $ls->getTitle();
    global $getLiveKey;
    $getLiveKey = ['key' => $ls->getKey(), 'live_servers_id' => intval($ls->getLive_servers_id()), 'live_index' => '', 'cleanKey' => ''];
}
$poster = Live::getRegularPosterImage($livet['users_id'], $_REQUEST['live_servers_id'], @$_REQUEST['live_schedule'], @$_REQUEST['ppv_schedule_id']);
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="<?php echo getURL('view/img/favicon.ico'); ?>">
        <title><?php echo @$liveTitle; ?></title>
        <link href="<?php echo getURL('view/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getURL('node_modules/@fortawesome/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <script src="<?php echo getURL('node_modules/jquery/dist/jquery.min.js'); ?>" type="text/javascript"></script>
        <link href="<?php echo getURL('node_modules/video.js/dist/video-js.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <?php
        echo AVideoPlugin::afterVideoJS();
        ?>
        <style>
            body {
                padding: 0 !important;
                margin: 0 !important;
                overflow:hidden;
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
        <div class="">
            <video poster="<?php echo getURL($poster); ?>" controls controlsList="nodownload" <?php echo PlayerSkins::getPlaysinline(); ?> 
                   class="video-js vjs-default-skin vjs-big-play-centered"
                   id="mainVideo" style="width: 100%; height: 100%; position: absolute;">
                <source src="<?php echo Live::getM3U8File($uuid); ?>" type='application/x-mpegURL'>
            </video>
        </div>

        <div style="z-index: 999; position: absolute; top:5px; left: 5px; opacity: 0.8; filter: alpha(opacity=80);" class="liveEmbed">
            <?php
            $streamName = $uuid;
            include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
            echo getLiveUsersLabel();
            ?>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/video.min.js.php';
        ?>
        <?php
        echo AVideoPlugin::afterVideoJS();
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/bootstrap.js.php';
        ?>
        <script src="<?php echo getURL('view/js/script.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>node_modules/js-cookie/dist/js.cookie.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>node_modules/jquery-toast-plugin/dist/jquery.toast.min.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>node_modules/sweetalert/dist/sweetalert.min.js" type="text/javascript"></script>
        <script>
<?php
echo PlayerSkins::getStartPlayerJS();
?>
        </script>
        <?php
        require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
        ?>
        <!-- getFooterCode start -->
        <?php
        echo AVideoPlugin::getFooterCode();
        showCloseButton();
        ?>  
        <!-- getFooterCode end -->
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
