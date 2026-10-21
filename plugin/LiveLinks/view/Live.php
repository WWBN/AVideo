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

$user_id = $t['users_id'];
$video = array();
$video['creator'] = Video::getCreatorHTML($user_id);
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
            <?php
            $videoDetails = array('creator' => $video['creator']);
            // Use AVideo's shared buffer, including inside plugin action hooks.
            $videoDetailsPreviousContent = _ob_get_clean();
            ?>
                <h1 itemprop="name"><?php echo getSEOTitle($t['title']); ?></h1>
            <?php
            $videoDetails['header'] = _ob_get_clean();
            ?>
                <div class="thumbsImageContainer">
                    <img src="<?php echo htmlspecialchars($poster, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($t['title'], ENT_QUOTES, 'UTF-8'); ?>" class="img-responsive img-rounded" />
                </div>
            <?php
            $videoDetails['preview'] = _ob_get_clean();
            ?>
            <?php
            if (isShareEnabled()) {
                include $global['systemRootPath'] . 'view/include/videoDetailsShareButton.php';
            }
            echo AVideoPlugin::getWatchActionButton(0);
            $videoDetails['actions'] = _ob_get_clean();
            if (isShareEnabled()) {
                $link = LiveLinks::getLinkToLiveFromId($_GET['link']);
                $linkEmbed = LiveLinks::getLinkToLiveFromId($_GET['link'], true);
                echo getShareMenu($t['title'], $link, $link, $linkEmbed, $img, "row bgWhite list-group-item menusDiv");
            }
            $videoDetails['menus'] = _ob_get_clean();
            ?>
                <?php if (!isHTMLEmpty($t['description'])) { ?>
                    <div class="row">
                        <div class="col-xs-4 col-sm-2 col-lg-2 text-right"><strong><?php echo __('Description'); ?>:</strong></div>
                        <div class="col-xs-8 col-sm-10 col-lg-10 descriptionArea" itemprop="description">
                            <?php echo nl2br(textToLink($t['description'])); ?>
                        </div>
                    </div>
                <?php } ?>
            <?php
            $videoDetails['metadata'] = _ob_get_clean();
            echo $videoDetailsPreviousContent;
            include $global['systemRootPath'] . 'view/include/videoDetails.php';
            include $global['systemRootPath'] . 'view/include/videoDetailsShareScript.php';
            ?>
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12 extraVideos nopadding"></div>
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
