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

$livet = LiveTransmition::getFromRequest();
//var_dump($livet, $_REQUEST);exit;
setLiveKey($livet['key'], Live::getLiveServersIdRequest(), @$_REQUEST['live_index']);
Live::checkIfPasswordIsGood($livet['key']);

if (empty($livet['live_schedule'])) {
    $lt = new LiveTransmition($livet['id']);
} else {
    $lt = new Live_schedule($livet['id']);
}

if (!$lt->userCanSeeTransmition()) {
    $url = "{$global['webSiteRootURL']}buy";
    if (empty($livet['live_schedule'])) {
        $url = addQueryStringParameter($url, 'live_transmitions_id', $livet['id']);
    } else {
        $url = addQueryStringParameter($url, 'live_schedule', $livet['id']);
    }
    header("Location: {$url}");
    exit;
    //forbiddenPage("You are not allowed see this streaming");
}
$uuid = LiveTransmition::keyNameFix($livet['key']);

$u = new User(0, $_GET['u'], false);
$user_id = $u->getBdId();
$video['users_id'] = $user_id;
$subscribe = Subscribe::getButton($user_id);
$name = $u->getNameIdentificationBd();
$name = "<a href='" . User::getChannelLink($user_id) . "' class='btn btn-xs btn-default'>{$name} " . User::getEmailVerifiedIcon($user_id) . "</a>";

$liveTitle = Live::getTitleFromKey($livet['key_with_index'], $livet['title']);
$liveDescription = Live::getDescriptionFromKey($livet['key_with_index'], $livet['description']);
$liveImg = User::getPhoto($user_id);
$liveUrl = Live::getLinkToLiveFromUsers_id($user_id);

$img = "{$global['webSiteRootURL']}plugin/Live/getImage.php?u={$_GET['u']}&format=jpg";
$imgw = 640;
$imgh = 360;

if (!empty($_REQUEST['playlists_id_live'])) {
    $liveTitle = PlayLists::getNameOrSerieTitle($_REQUEST['playlists_id_live']);
    $liveDescription = PlayLists::getDescriptionIfIsSerie($_REQUEST['playlists_id_live']);
    //$liveImg = PlayLists::getImage($_REQUEST['playlists_id_live']);
}

if (!empty($_REQUEST['live_schedule'])) {
    $ls = new Live_schedule($_REQUEST['live_schedule']);
    $liveTitle = $ls->getTitle();
    $liveDescription = $ls->getDescription();
    //$liveImg = Live_schedule::getPosterURL($_REQUEST['live_schedule'], 0);
    $liveUrl = addQueryStringParameter($liveUrl, 'live_schedule', intval($_REQUEST['live_schedule']));
    $img = addQueryStringParameter($img, 'live_schedule', intval($_REQUEST['live_schedule']));
    $img = addQueryStringParameter($img, 'cache', uniqid());
    global $getLiveKey;
    $getLiveKey = ['key' => $ls->getKey(), 'live_servers_id' => intval($ls->getLive_servers_id()), 'live_index' => '', 'cleanKey' => ''];

    if (!empty($ls->getUsers_id_company())) {
        $user_id = $ls->getUsers_id_company();
        //var_dump($user_id);exit;
        $u = new User($user_id);
        $video['users_id'] = $user_id;
        $subscribe = Subscribe::getButton($user_id);
        $name = $u->getNameIdentificationBd();
        $name = "<a href='" . User::getChannelLink($user_id) . "' class='btn btn-xs btn-default'>{$name} " . User::getEmailVerifiedIcon($user_id) . "</a>";
        $liveImg = User::getPhoto($user_id);
    }
}

$video['creator'] = '<div class="pull-left"><img src="' . $liveImg . '" alt="User Photo" class="img img-responsive img-circle" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName text-muted"><strong>' . $name . '</strong><br>' . $subscribe . '</div></div>';

$liveDO = AVideoPlugin::getObjectData("Live");
$video['type'] = 'video';
AVideoPlugin::getModeYouTubeLive($user_id);

$isCompressed = AVideoPlugin::loadPluginIfEnabled('TheaterButton') && TheaterButton::isCompressed();

$sideAd = getAdsSideRectangle();

$modeYoutubeBottomClass1 = "col-sm-7 col-md-7 col-lg-6";
$modeYoutubeBottomClass2 = "col-sm-5 col-md-5 col-lg-4 ";

if (isHTMLEmpty($sideAd)) {
    $modeYoutubeBottomClass1 = "col-sm-12 col-md-12 col-lg-10";
    $modeYoutubeBottomClass2 = "hidden ";
}
// to fix the unfinished lives
$liveInfo = Live::getInfo($livet['key'], Live::getLiveServersIdRequest());
$_page = new Page(array('Live'));
$_page->setExtraScripts(
    array(
        'view/js/webui-popover/jquery.webui-popover.min.js',
        'view/js/bootstrap-list-filter/bootstrap-list-filter.min.js'
    )
);
$_page->setExtraStyles(
    array(
        'node_modules/video.js/dist/video-js.min.css',
        'view/js/webui-popover/jquery.webui-popover.min.css'
    )
);
?>
<!-- Live modeYoutubeLive.php -->
<div class="container-fluid principalContainer" style="padding: 0; overflow: hidden;" id="modeYoutubePrincipal">
    <?php
    if (!$isCompressed) {
    ?>
        <div class="" id="modeYoutubeTop">
            <div class="col-md-12">
                <center style="margin:5px;">
                    <?php echo getAdsLeaderBoardTop(); ?>
                </center>
            </div>
            <div class="col-md-12">
                <?php require "{$global['systemRootPath']}plugin/Live/view/liveVideo.php"; ?>
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
                <div class="" id="modeYoutubeTop">
                    <div class="col-md-12">
                        <center style="margin:5px;">
                            <?php echo getAdsLeaderBoardTop(); ?>
                        </center>
                    </div>
                    <div class="col-md-12">
                        <?php require "{$global['systemRootPath']}plugin/Live/view/liveVideo.php"; ?>
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
            <div class="panel panel-default">
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
                        <span class="title_liveKey_<?php echo $livet['key'] ?>"><?php echo getSEOTitle($liveTitle); ?></span>
                        <small class="text-muted">
                            <?php
                            echo $liveInfo['displayTime'];
                            ?>
                        </small>
                    </h1>
                    <div class="col-xs-12 col-sm-12 col-lg-12"><?php echo $video['creator']; ?></div>
                    <p><?php echo nl2br(textToLink($liveDescription)); ?></p>
                    <div class="row">
                        <div class="col-md-12 watch8-action-buttons text-muted">
                            <?php if (isShareEnabled()) { ?>
                                <a href="#" class="btn btn-default no-outline" id="shareBtn">
                                    <span class="fa fa-share"></span> <?php echo __("Share"); ?>
                                </a>
                            <?php
                            }
                            ?>
                            <script>
                                $(document).ready(function() {
                                    $("#shareDiv").slideUp();
                                    $("#shareBtn").click(function() {
                                        $(".menusDiv").not("#shareDiv").slideUp();
                                        $("#shareDiv").slideToggle();
                                        return false;
                                    });
                                });
                            </script>
                            <?php
                            echo AVideoPlugin::getWatchActionButton(0);
                            Live::getLiveControls($livet['key_with_index'], $livet['live_servers_id']);
                            ?>
                        </div>
                    </div>
                    <?php
                    $link = Live::getLinkToLiveFromUsers_id($user_id);
                    if (!empty($_REQUEST['live_schedule'])) {
                        $link = addQueryStringParameter($link, 'live_schedule', intval($_REQUEST['live_schedule']));
                    }
                    if (isShareEnabled()) {
                        echo getShareMenu($liveTitle, $link, $link, addQueryStringParameter($link, 'embed', 1), $img, "row bgWhite list-group-item menusDiv");
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
echo AVideoPlugin::afterVideoJS();
$_page->print();
?>
