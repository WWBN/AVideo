<?php
global $global;
$isMyChannel = false;
if (User::isLogged() && $user_id == User::getId()) {
    $isMyChannel = true;
}
$user = new User($user_id);

if ($user->getBdId() != $user_id) {
    header("Location: {$global['webSiteRootURL']}channels");
    exit;
}

$global['isChannel'] = $user_id;

$_GET['channelName'] = $user->getChannelName();
$timeLog = __FILE__ . " - channelName: {$_GET['channelName']}";
TimeLogStart($timeLog);
$_POST['sort']['created'] = "DESC";

if (empty($_GET['current'])) {
    $_POST['current'] = 1;
} else {
    $_POST['current'] = $_GET['current'];
}
$current = $_POST['current'];
$rowCount = 25;
$_REQUEST['rowCount'] = $rowCount;

if (empty($channelPassword) && !$isMyChannel) {
    $status = 'a';
    $showUnlisted = false;
} else {
    $status = 'viewable';
    $showUnlisted = true;
}


$type = '';
if($advancedCustomUser->showArticlesTab && AVideoPlugin::isEnabledByName('Articles')){
    $uploadedTotalArticles = Video::getTotalVideos($status, $user_id, !isToHidePrivateVideos(), $showUnlisted, true, false, 'article');
    if(!empty($uploadedTotalArticles)){
        $uploadedArticles = Video::getAllVideos($status, $user_id, !isToHidePrivateVideos(), array(), false, $showUnlisted, true, false, null, 'article');
    }
    $type = 'notArticle';
}
if($advancedCustomUser->showAudioTab){
    $uploadedTotalAudio = Video::getTotalVideos($status, $user_id, !isToHidePrivateVideos(), $showUnlisted, true, false, 'audio');
    if(!empty($uploadedTotalAudio)){
        $uploadedAudio = Video::getAllVideos($status, $user_id, !isToHidePrivateVideos(), array(), false, $showUnlisted, true, false, null, 'audio');
    }
    //var_dump($uploadedAudio);exit;
    if(empty($type)){
        $type = 'notAudio';
    }else{
        $type = 'notArticleOrAudio';
    }
}
//var_dump($uploadedArticles);exit;
$uploadedVideos = array();
$uploadedTotalVideos = Video::getTotalVideos($status, $user_id, !isToHidePrivateVideos(), $showUnlisted, true, false, $type);
if(!empty($uploadedTotalVideos)){
    $uploadedVideos = Video::getAllVideos($status, $user_id, !isToHidePrivateVideos(), array(), false, $showUnlisted, true, false, null, $type);
}
TimeLogEnd($timeLog, __LINE__);
$totalPages = ceil($uploadedTotalVideos / $rowCount);

unset($_POST['sort']);
unset($_POST['rowCount']);
unset($_POST['current']);

$get = ['channelName' => $_GET['channelName']];
$palyListsObj = AVideoPlugin::getObjectDataIfEnabled('PlayLists');
TimeLogEnd($timeLog, __LINE__);
$obj = AVideoPlugin::getObjectData("YouPHPFlix2");

if($advancedCustomUser->showChannelLiveTab){
    $liveVideos = getLiveVideosFromUsers_id($user_id);
}
?>

<style>
    #aboutArea #aboutAreaPreContent{
        max-height: 120px;
        overflow: hidden;
        transition: max-height 0.25s ease-out;
        overflow: hidden;
    }
    #aboutAreaPreContent{
        margin-bottom: 30px;
    }
    #aboutArea.expanded #aboutAreaPreContent{
        max-height: 1500px;
        overflow: auto;
        transition: max-height 0.25s ease-in;
    }
    #aboutAreaShowMoreBtn{
        position: absolute;
        bottom: 0;
    }
    #aboutArea .showMore{
        display: block;
    }
    #aboutArea .showLess{
        display: none;
    }
    #aboutArea.expanded .showMore{
        display: none;
    }
    #aboutArea.expanded .showLess{
        display: block;
    }
    #channelHome{
        background-color: rgb(<?php echo $obj->backgroundRGB; ?>);
        position: relative;
        overflow: hidden;
    }
    .feedDropdown{
        margin-right: 4px;
    }
</style>
<!-- <?php var_dump($uploadedTotalVideos, $user_id, !isToHidePrivateVideos()); ?> -->
<div class="clearfix"></div>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="gallery" >
            <div class="row clearfix">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <center style="margin:5px;">
                        <?php
                        echo getAdsChannelLeaderBoardTop();
                        ?>
                    </center>
                </div>
            </div>
            <?php
            if (empty($advancedCustomUser->doNotShowTopBannerOnChannel)) {
                if (isMobile()) {
                    $relativePath = $user->getBackgroundURL(User::$channel_artDesktopMin);
                } else {
                    $relativePath = $user->getBackgroundURL(User::$channel_artDesktopMax);
                }
                ?>
                <div class="clearfix" style="clear: both;"></div>
                <a href="<?php echo User::getWebsite($user_id); ?>" target="_blank">
                    <div class="row bg-info profileBg" style="margin: 20px -10px; background: url('<?php echo getURL($relativePath); ?>')  no-repeat 50% 50%; -webkit-background-size: cover;
                         -moz-background-size: cover;
                         -o-background-size: cover;
                         background-size: cover;">
                        <img src="<?php echo User::getPhoto($user_id); ?>" alt="<?php echo $user->_getName(); ?>" class="img img-responsive img-thumbnail" style="max-width: 100px;"/>
                    </div>
                </a>
                <?php
            }
            ?>
            <div class="row">
                <div class="col-sm-12">
                    <h2 class="pull-left">
                        <?php
                        echo $user->getNameIdentificationBd();
                        ?>
                        <?php
                        echo User::getEmailVerifiedIcon($user_id)
                        ?></h2>
                    <span class="pull-right">
                    <?php
                    echo getUserOnlineLabel($user_id, 'pull-right', 'padding: 0 5px;');
                    ?>
                        <?php
                        $urlChannel = addLastSlash(User::getChannelLink($user_id));
                        $rss =  "{$urlChannel}rss";
                        $mrss =  "{$urlChannel}mrss";
                        $roku =  "{$urlChannel}roku.json";
                        echo getFeedButton($rss, $mrss, $roku);
                        echo User::getAddChannelToGalleryButton($user_id);
                        echo User::getBlockUserButton($user_id);
                        echo Subscribe::getButton($user_id);
                        ?>
                    </span>
                </div>
            </div>

            <div class="col-md-12" id="aboutArea">
                <div id="aboutAreaPreContent">
                    <div id="aboutAreaContent">
                        <?php
                        $about =  html_entity_decode($user->getAbout());
                        echo $about;
                        ?>
                    </div>
                </div>
                <button onclick="$('#aboutArea').toggleClass('expanded');" class="btn btn-xs btn-default" id="aboutAreaShowMoreBtn" style="display: none; ">
                    <span class="showMore"><i class="fas fa-caret-down"></i> <?php echo __("Show More"); ?></span>
                    <span class="showLess"><i class="fas fa-caret-up"></i> <?php echo __("Show Less"); ?></span>
                </button>
            </div>

            <script>
                $(document).ready(function () {
                    if ($('#aboutArea').height() < $('#aboutAreaContent').height()) {
                        $('#aboutAreaShowMoreBtn').show();
                    }
                });
            </script>
            <?php
            if (!User::hasBLockedUser($user_id)) {
                ?>
                <div class="tabbable-panel">
                    <div class="tabbable-line">
                        <ul class="nav nav-tabs">
                            <?php
                            $active = "active";
                            if (!empty($liveVideos)) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = '';
                                }
                                ?>
                                <li class="nav-item <?php echo $active; ?>">
                                    <a class="nav-link " href="#channelLive" data-toggle="tab" aria-expanded="false">
                                        <i class="fas fa-broadcast-tower"></i> <?php echo strtoupper(__("Live Now")); ?>
                                    </a>
                                </li>
                                <?php
                                $active = '';
                            }
                            if ($advancedCustomUser->showChannelHomeTab) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = '';
                                }
                                ?>
                                <li class="nav-item <?php echo $active; ?>">
                                    <a class="nav-link " href="#channelHome" data-toggle="tab" aria-expanded="false">
                                        <i class="fas fa-home"></i> <?php echo strtoupper(__("Home")); ?>
                                    </a>
                                </li>
                                <?php
                                $active = '';
                            }
                            if ($advancedCustomUser->showChannelVideosTab) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = "active";
                                }
                                ?>
                                <li class="nav-item <?php echo $active; ?>">
                                    <a class="nav-link " href="#channelVideos" data-toggle="tab" aria-expanded="false">
                                        <i class="fas fa-file-video"></i> <?php echo strtoupper(__("Videos")); ?> <span class="badge"><?php echo $uploadedTotalVideos; ?></span>
                                    </a>
                                </li>
                                <?php
                                $active = '';
                            }
                            if (!empty($uploadedTotalArticles)) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = "";
                                }
                                ?>
                                <li class="nav-item <?php echo $active; ?>">
                                    <a class="nav-link " href="#channelArticles" data-toggle="tab" aria-expanded="false">
                                        <i class="far fa-file-alt"></i> <?php echo strtoupper(__("Articles")); ?> <span class="badge"><?php echo $uploadedTotalArticles; ?></span>
                                    </a>
                                </li>
                                <?php
                                $active = '';
                            }
                            if (!empty($uploadedTotalAudio)) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = "";
                                }
                                ?>
                                <li class="nav-item <?php echo $active; ?>">
                                    <a class="nav-link " href="#channelAudio" data-toggle="tab" aria-expanded="false">
                                        <i class="fas fa-file-audio"></i> <?php echo strtoupper(__("Audio")); ?> <span class="badge"><?php echo $uploadedTotalAudio; ?></span>
                                    </a>
                                </li>
                                <?php
                                $active = '';
                            }
                            if ($advancedCustomUser->showChannelProgramsTab && !empty($palyListsObj)) {
                                $totalPrograms = PlayList::getAllFromUserLight($user_id, true, false, 0, true, true);
                                if ($totalPrograms) {
                                    ?>
                                    <li class="nav-item <?php echo $active; ?>" id="channelPlayListsLi">
                                        <a class="nav-link " href="#channelPlayLists" data-toggle="tab" aria-expanded="true">
                                            <i class="fas fa-list"></i> <?php echo strtoupper(__($palyListsObj->name)); ?> <span class="badge"><?php echo count($totalPrograms); ?></span>
                                        </a>
                                    </li>
                                    <?php
                                    $active = '';
                                }
                            }
                            ?>
                        </ul>
                        <div class="tab-content clearfix">
                            <?php
                            $active = "active fade in";
                            if(!empty($liveVideos)){
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = '';
                                }
                                ?>
                                <div class="tab-pane  <?php echo $active; ?>" id="channelLive" >
                                    <?php
                                    //createGallerySection($videos, $crc = "", $get = array(), $ignoreAds = false, $screenColsLarge = 0, $screenColsMedium = 0, $screenColsSmall = 0, $screenColsXSmall = 0, $galeryDetails = true)
                                    //var_dump($screenColsLarge, $screenColsMedium);exit;
                                    //createGallerySection($videos, $crc = "", $get = array(), $ignoreAds = false, $screenColsLarge = 0, $screenColsMedium = 0, $screenColsSmall = 0, $screenColsXSmall = 0, $galeryDetails = true)
                                    createGallerySection($liveVideos);
                                    ?>
                                </div>
                                <?php
                                $active = "fade";
                            }
                            
                            if ($advancedCustomUser->showChannelHomeTab) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = '';
                                }
                                $obj = AVideoPlugin::getObjectData("YouPHPFlix2");
                                ?>
                                <style>#bigVideo{
                                        top: 0 !important;
                                    }</style>
                                <div class="tab-pane  <?php echo $active; ?>" id="channelHome" >
                                    <?php
                                    $obj->BigVideo = true;
                                    $obj->PlayList = false;
                                    $obj->Channels = false;
                                    $obj->Trending = false;
                                    $obj->pageDots = false;
                                    $obj->TrendingAutoPlay = false;
                                    $obj->maxVideos = 12;
                                    $obj->Suggested = false;
                                    $obj->paidOnlyLabelOverPoster = false;
                                    $obj->DateAdded = true;
                                    $obj->DateAddedAutoPlay = true;
                                    $obj->MostPopular = false;
                                    $obj->MostWatched = false;
                                    $obj->SortByName = false;
                                    $obj->Categories = false;
                                    $obj->playVideoOnFullscreen = false;
                                    $obj->titleLabel = true;
                                    $obj->RemoveBigVideoDescription = true;

                                    include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/modeFlixBody.php';
                                    ?>
                                </div>
                                <?php
                                $active = "fade";
                            }
                            if ($advancedCustomUser->showChannelVideosTab) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = "active fade in";
                                }
                                ?>

                                <div class="tab-pane <?php echo $active; ?>" id="channelVideos">

                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <?php
                                            if ($isMyChannel) {
                                                ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success ">
                                                    <span class="glyphicon glyphicon-film"></span>
                                                    <span class="glyphicon glyphicon-headphones"></span>
                                                    <?php echo __("My videos"); ?>
                                                </a>
                                                <?php
                                            } else {
                                                echo __("My videos");
                                            }
                                            echo AVideoPlugin::getChannelButton();
                                            ?>
                                        </div>
                                        <div class="panel-body">
                                            <?php
                                            $video = false;
                                            if ($advancedCustomUser->showBigVideoOnChannelVideosTab && !empty($uploadedVideos[0])) {
                                                $video = $uploadedVideos[0];
                                                $obj = new stdClass();
                                                $obj->BigVideo = true;
                                                $obj->Description = false;
                                                include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideo.php';
                                                unset($uploadedVideos[0]);
                                            }
                                            ?>
                                            <div class="row">
                                                <?php
                                                TimeLogEnd($timeLog, __LINE__);
                                                createGallerySection($uploadedVideos, "", $get);
                                                TimeLogEnd($timeLog, __LINE__);
                                                ?>
                                            </div>
                                        </div>

                                        <div class="panel-footer">
                                            <?php echo getPagination($totalPages, $current, "{$global['webSiteRootURL']}channel/{$_GET['channelName']}?current={page}"); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $active = "fade";
                            }
                            if (!empty($uploadedTotalArticles)) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = "";
                                }
                                ?>

                                <div class="tab-pane <?php echo $active; ?>" id="channelArticles">

                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <?php
                                            if ($isMyChannel) {
                                                ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success ">
                                                    <i class="far fa-newspaper"></i>
                                                    <?php echo __("Articles"); ?>
                                                </a>
                                                <?php
                                            } else {
                                                echo __("Articles");
                                            }
                                            echo AVideoPlugin::getChannelButton();
                                            ?>
                                        </div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <?php
                                                TimeLogEnd($timeLog, __LINE__);
                                                createGallerySection($uploadedArticles, "", $get);
                                                TimeLogEnd($timeLog, __LINE__);
                                                ?>
                                            </div>
                                        </div>

                                        <div class="panel-footer">
                                            <?php echo getPagination($totalPages, $current, "{$global['webSiteRootURL']}channel/{$_GET['channelName']}?current={page}"); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $active = "fade";
                            }
                            
                            if (!empty($uploadedTotalAudio)) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = "";
                                }
                                ?>

                                <div class="tab-pane <?php echo $active; ?>" id="channelAudio">

                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <?php
                                            if ($isMyChannel) {
                                                ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success ">
                                                    <i class="far fa-newspaper"></i>
                                                    <?php echo __("Audio"); ?>
                                                </a>
                                                <?php
                                            } else {
                                                echo __("Audio");
                                            }
                                            echo AVideoPlugin::getChannelButton();
                                            ?>
                                        </div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <?php
                                                TimeLogEnd($timeLog, __LINE__);
                                                createGallerySection($uploadedAudio, "", $get);
                                                TimeLogEnd($timeLog, __LINE__);
                                                ?>
                                            </div>
                                        </div>

                                        <div class="panel-footer">
                                            <?php echo getPagination($totalPages, $current, "{$global['webSiteRootURL']}channel/{$_GET['channelName']}?current={page}"); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $active = "fade";
                            }
                            if (!empty($totalPrograms)) {
                                ?>
                                <div class="tab-pane <?php echo $active; ?>" id="channelPlayLists" style="min-height: 800px;">
                                    <div class="panel panel-default">
                                        <div class="panel-heading text-right">
                                            <?php
                                            if ($isMyChannel) {
                                                ?>
                                                <a class="btn btn-default btn-xs " href="<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/managerPlaylists.php">
                                                    <i class="fas fa-edit"></i> <?php echo __('Organize') . ' ' . __($palyListsObj->name); ?>
                                                </a>
                                            <?php }
                                            ?>
                                        </div>
                                        <div class="panel-body">
                                            <?php include $global['systemRootPath'] . 'view/channelPlaylist.php'; ?>
                                        </div>
                                        <div class="panel-footer">

                                        </div>
                                    </div>

                                </div>
                                <?php
                                $active = "fade";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<script src="<?php echo getCDN(); ?>plugin/Gallery/script.js" type="text/javascript"></script>
<script src="<?php echo getCDN(); ?>node_modules/infinite-scroll/dist/infinite-scroll.pkgd.min.js" type="text/javascript"></script>