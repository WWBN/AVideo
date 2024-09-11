<?php
global $isLive;
$isLive = 1;
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

$plugin = AVideoPlugin::loadPluginIfEnabled('LiveLinks');
$p = AVideoPlugin::loadPluginIfEnabled('Live');

if (empty($plugin)) {
    die('Plugin disabled');
}

$_GET['link'] = intval($_GET['link']);
if (!empty($_GET['link'])) {
    $liveLink = new LiveLinksTable($_GET['link']);

    $isLiveLink = $liveLink->getId();
    if ($liveLink->getType() == 'logged_only' && !User::isLogged()) {
        die('Link for logged only');
    }

    $uuid = $_GET['link'];
    $t['id'] = $uuid;
    $t['users_id'] = $liveLink->getUsers_id();
    $t['title'] = $liveLink->getTitle();
    $t['link'] = $liveLink->getLink();
    $t['description'] = $liveLink->getDescription();

    AVideoPlugin::getModeLiveLink($liveLink->getId());
    $date = convertFromDefaultTimezoneTimeToMyTimezone($liveLink->getStart_date());
    $toTime = strtotime($date);
} else {
    $isLiveLink = uniqid();
    $uuid = $isLiveLink;
    $t = LiveLinks::decodeDinamicVideoLink();
    $toTime = time();
}

if (empty($t['users_id'])) {
    die('Link not found');
}

if ($toTime > time()) {
    $link = LiveLinks::getLinkToLiveFromId($_GET['link']);
    //$linkEmbed = LiveLinks::getLinkToLiveFromId($_GET['link'], true);
    //$share = getShareMenu($t['title'], $link, $link, $linkEmbed, $img, "row");
    $share = getShareSocialIcons($t['title'], $link);
    $message = "<strong>{$t['title']}</strong><div>{$share}</div>{$t['description']}";
    $image = User::getPhoto($t['users_id']);
    $bgImage = LiveLinks::getImage($t['id']);
    $title = $t['title'];
    countDownPage($toTime, $message, $image, $bgImage, $title);
}

$u = new User($t['users_id']);
$user_id = $u->getBdId();
$subscribe = Subscribe::getButton($user_id);
$name = $u->getNameIdentificationBd();
$name = "<a href='" . User::getChannelLink($user_id) . "' class='btn btn-xs btn-default'>{$name} " . User::getEmailVerifiedIcon($user_id) . "</a>";

$video = array();
$video['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($user_id) . '" alt="User Photo" class="img img-responsive img-circle" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName text-muted"><strong>' . $name . '</strong><br>' . $subscribe . '</div></div>';
$video['type'] = "liveLink";
$video['title'] = $t['title'];
$video['description'] = $t['description'];
$video['users_id'] = $t['users_id'];
$poster = $img = LiveLinks::getImage($t['id']);
$imgw = 400;
$imgh = 255;

if (isAVideoMobileApp()) {
    $_GET['embed'] = 1;
}

if (!empty($_GET['embed'])) {
    $video['videoLink'] = LiveLinks::getSourceLink($t['id']);
    include $global['systemRootPath'] . 'view/videoEmbeded.php';
    return false;
}

$isCompressed = AVideoPlugin::loadPluginIfEnabled('TheaterButton') && TheaterButton::isCompressed();

$sideAd = getAdsSideRectangle();

$modeYoutubeBottomClass1 = "col-sm-7 col-md-7 col-lg-6";
$modeYoutubeBottomClass2 = "col-sm-5 col-md-5 col-lg-4 ";

if (isHTMLEmpty($sideAd)) {
    $modeYoutubeBottomClass1 = "col-sm-12 col-md-12 col-lg-10";
    $modeYoutubeBottomClass2 = "hidden ";
}
$_page = new Page(array($t['title']));
$_page->setExtraStyles(array('node_modules/video.js/dist/video-js.min.css'));
$_page->setExtraScripts(array('node_modules/videojs-contrib-ads/dist/videojs.ads.min.js'));
?>
<div class="container-fluid principalContainer" style="padding: 0;overflow: hidden;" id="modeYoutubePrincipal">
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
                <?php
                require "{$global['systemRootPath']}plugin/LiveLinks/view/liveVideo.php";
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
                <div class="" id="modeYoutubeTop">

                    <div class="col-md-12">
                        <center style="margin:5px;">
                            <?php echo getAdsLeaderBoardTop(); ?>
                        </center>
                    </div>
                    <div class="col-md-12">
                        <?php
                        require "{$global['systemRootPath']}plugin/LiveLinks/view/liveVideo.php";
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
            <div class="panel panel-default">
                <div class="panel-body">
                    <h1 itemprop="name"><i class="fas fa-video"></i> <?php echo getSEOTitle($t['title']); ?></h1>
                    <div class="col-xs-12 col-sm-12 col-lg-12"><?php echo $video['creator']; ?></div>
                    <p><?php echo nl2br(textToLink($t['description'])); ?></p>

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
                            <?php echo AVideoPlugin::getWatchActionButton(0); ?>
                        </div>
                    </div>
                    <?php
                    if (isShareEnabled()) {
                        /**
                         * @var string $link
                         * @var string $linkEmbed
                         */
                        $link = LiveLinks::getLinkToLiveFromId($_GET['link']);
                        $linkEmbed = LiveLinks::getLinkToLiveFromId($_GET['link'], true);
                        echo getShareMenu($t['title'], $link, $link, $linkEmbed, $img, "row");
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
?>
<?php
$_page->print();
?>