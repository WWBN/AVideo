<?php
global $isLive;
$isLive = 1;
require_once '../../videos/configuration.php';

if (!empty($_GET['embed'])) {
    include $global['systemRootPath'] . 'plugin/Live/view/videoEmbeded.php';
    return false;
}

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';

if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}

$livet = LiveTransmition::getFromDbByUserName($_GET['u']);
setLiveKey($livet['key'], Live::getLiveServersIdRequest(), @$_REQUEST['live_index']);
$lt = new LiveTransmition($livet['id']);
if (!$lt->userCanSeeTransmition()) {
    forbiddenPage("You are not allowed see this streaming");
}

$uuid = LiveTransmition::keyNameFix($livet['key']);

$u = new User(0, $_GET['u'], false);
$user_id = $u->getBdId();
$video['users_id'] = $user_id;
$subscribe = Subscribe::getButton($user_id);
$name = $u->getNameIdentificationBd();
$name = "<a href='" . User::getChannelLink($user_id) . "' class='btn btn-xs btn-default'>{$name} " . User::getEmailVerifiedIcon($user_id) . "</a>";

$liveTitle = $livet['title'];
$liveDescription = $livet['description'];
$liveImg = User::getPhoto($user_id);
if (!empty($_REQUEST['playlists_id_live'])) {
    $liveTitle = PlayLists::getNameOrSerieTitle($_REQUEST['playlists_id_live']);
    $liveDescription = PlayLists::getDescriptionIfIsSerie($_REQUEST['playlists_id_live']);
    $liveImg = PlayLists::getImage($_REQUEST['playlists_id_live']);
}


$video['creator'] = '<div class="pull-left"><img src="' . $liveImg . '" alt="User Photo" class="img img-responsive img-circle" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName text-muted"><strong>' . $name . '</strong><br>' . $subscribe . '</div></div>';

$img = "{$global['webSiteRootURL']}plugin/Live/getImage.php?u={$_GET['u']}&format=jpg";
$imgw = 640;
$imgh = 360;

$liveDO = AVideoPlugin::getObjectData("Live");
$video['type'] = 'video';
AVideoPlugin::getModeYouTubeLive($user_id);

$isCompressed = AVideoPlugin::loadPluginIfEnabled('TheaterButton') && TheaterButton::isCompressed();

$sideAd = getAdsSideRectangle();

$modeYoutubeBottomClass1 = "col-sm-7 col-md-7 col-lg-6";
$modeYoutubeBottomClass2 = "col-sm-5 col-md-5 col-lg-4 ";
if (empty($sideAd) && !AVideoPlugin::loadPluginIfEnabled("Chat2")) {
    $modeYoutubeBottomClass1 = "col-sm-12 col-md-12 col-lg-10";
    $modeYoutubeBottomClass2 = "hidden ";
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $liveTitle . $config->getPageTitleSeparator() . __("Live") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo getCDN(); ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getCDN(); ?>css/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getCDN(); ?>js/webui-popover/jquery.webui-popover.min.css" rel="stylesheet" type="text/css"/>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

        <meta property="fb:app_id"             content="774958212660408" />
        <meta property="og:url"                content="<?php echo Live::getLinkToLiveFromUsers_id($user_id); ?>" />
        <meta property="og:type"               content="video.other" />
        <meta property="og:title"              content="<?php echo str_replace('"', '', $liveTitle); ?> - <?php echo $config->getWebSiteTitle(); ?>" />
        <meta property="og:description"        content="<?php echo str_replace('"', '', $liveTitle); ?>" />
        <meta property="og:image"              content="<?php echo $img; ?>" />
        <meta property="og:image:width"        content="<?php echo $imgw; ?>" />
        <meta property="og:image:height"       content="<?php echo $imgh; ?>" />
        <?php
        //echo AVideoPlugin::getHeadCode();
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid principalContainer" style="padding: 0;" id="modeYoutubePrincipal">
            <?php
            if (!$isCompressed) {
                ?>
                <div class="" id="modeYoutubeTop" >
                    <div class="col-md-12">
                        <center style="margin:5px;">
                            <?php echo getAdsLeaderBoardTop(); ?>
                        </center>
                    </div>  
                    <div class="col-md-12">
                        <?php
                        require "{$global['systemRootPath']}plugin/Live/view/liveVideo.php";
                        ?>
                    </div>  
                    <div class="col-md-12">
                        <center style="margin:5px;">
                            <?php echo getAdsLeaderBoardTop2(); ?>
                        </center>
                    </div>  
                </div>
                <?php
            }
            ?>
            <div class="row" id="modeYoutubeBottom" style="margin: 0;">
                <div class="col-lg-1"></div>
                <div class="<?php echo $modeYoutubeBottomClass1; ?>" id="modeYoutubeBottomContent">
                    <?php
                    if ($isCompressed) {
                        ?>
                        <div class="" id="modeYoutubeTop" >
                            <div class="col-md-12">
                                <center style="margin:5px;">
                                    <?php echo getAdsLeaderBoardTop(); ?>
                                </center>
                            </div>  
                            <div class="col-md-12">
                                <?php
                                require "{$global['systemRootPath']}plugin/Live/view/liveVideo.php";
                                ?>
                            </div>  
                            <div class="col-md-12">
                                <center style="margin:5px;">
                                    <?php echo getAdsLeaderBoardTop2(); ?>
                                </center>
                            </div>  
                        </div>
                        <?php
                    }
                    ?>
                    <div class="panel">
                        <div class="panel-body">
                            <h1 itemprop="name">
                                <?php
                                if ($lt->isAPrivateLive()) {
                                    ?>
                                    <i class="fas fa-lock"></i> 
                                    <?php
                                } else {
                                    ?>
                                    <i class="fas fa-video"></i> 
                                    <?php
                                }
                                ?>
                                <?php echo $liveTitle; ?>

                            </h1>
                            <div class="col-xs-12 col-sm-12 col-lg-12"><?php echo $video['creator']; ?></div>
                            <p><?php echo nl2br(textToLink($liveDescription)); ?></p>
                            <div class="row">
                                <div class="col-md-12 watch8-action-buttons text-muted">
                                    <?php if (empty($advancedCustom->disableShareAndPlaylist) && empty($advancedCustom->disableShareOnly)) { ?>
                                    <a href="#" class="btn btn-default no-outline" id="shareBtn">
                                        <span class="fa fa-share"></span> <?php echo __("Share"); ?>
                                    </a>
                                    <?php
                                    }
                                    ?>
                                    <script>
                                        $(document).ready(function () {
                                            $("#shareDiv").slideUp();
                                            $("#shareBtn").click(function () {
                                                $(".menusDiv").not("#shareDiv").slideUp();
                                                $("#shareDiv").slideToggle();
                                                return false;
                                            });
                                        });
                                    </script>
                                    <?php 
                                    echo AVideoPlugin::getWatchActionButton(0); ?>
                                </div>
                            </div>
                            <?php
                            $link = Live::getLinkToLiveFromUsers_id($user_id);
                            if (empty($advancedCustom->disableShareAndPlaylist) && empty($advancedCustom->disableShareOnly)) {
                                getShareMenu($liveTitle, $link, $link, addQueryStringParameter($link, 'embed', 1), $img,"row bgWhite list-group-item menusDiv");
                            }
                            ?>
                            <div class="row">

                                <div class="col-lg-12 col-sm-12 col-xs-12 extraVideos nopadding"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="<?php echo $modeYoutubeBottomClass2; ?> rightBar" id="yptRightBar">
                    <div class="list-group-item ">
                        <?php
                        echo $sideAd;
                        ?>
                    </div>
                </div>
                <div class="col-lg-1"></div>
            </div>  

        </div>


        <?php
        include $global['systemRootPath'] . 'view/include/video.min.js.php';
        ?>
        <?php
        echo AVideoPlugin::afterVideoJS();
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>  
        <script src="<?php echo getCDN(); ?>js/webui-popover/jquery.webui-popover.min.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>js/bootstrap-list-filter/bootstrap-list-filter.min.js" type="text/javascript"></script>

    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
