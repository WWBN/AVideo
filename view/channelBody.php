<?php
global $global;
require_once $global['systemRootPath'] . 'objects/functionInfiniteScroll.php';
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
$rowCount = 25;
$_REQUEST['rowCount'] = $rowCount;

if (empty($channelPassword) && !$isMyChannel) {
    $status = 'a';
    $showUnlisted = false;
} else {
    $status = Video::SORT_TYPE_VIEWABLE;
    $showUnlisted = true;
}

$ownerCanUplaodVideos = $user->getCanUpload() || $user->getIsAdmin() || AVideoPlugin::userCanUpload($user_id);
//var_dump($ownerCanUplaodVideos, $user_id, $user);exit;
$type = '';
if ($ownerCanUplaodVideos && $advancedCustomUser->showArticlesTab && AVideoPlugin::isEnabledByName('Articles')) {
    $uploadedTotalArticles = Video::getTotalVideos($status, $user_id, !isToHidePrivateVideos(), $showUnlisted, true, false, Video::$videoTypeArticle);
    if (!empty($uploadedTotalArticles)) {
        $uploadedArticles = Video::getAllVideos($status, $user_id, !isToHidePrivateVideos(), [], false, $showUnlisted, true, false, null, Video::$videoTypeArticle);
    }
    $type = 'notArticle';
}
if ($ownerCanUplaodVideos && $advancedCustomUser->showAudioTab) {
    $uploadedTotalAudio = Video::getTotalVideos($status, $user_id, !isToHidePrivateVideos(), $showUnlisted, true, false, Video::$videoTypeAudio);
    if (!empty($uploadedTotalAudio)) {
        $uploadedAudio = Video::getAllVideos($status, $user_id, !isToHidePrivateVideos(), [], false, $showUnlisted, true, false, null, Video::$videoTypeAudio);
    }
    //var_dump($uploadedAudio);exit;
    if (empty($type)) {
        $type = 'notAudio';
    } else {
        $type = 'notArticleOrAudio';
    }
}
if ($ownerCanUplaodVideos && $advancedCustomUser->showImageTab) {
    $uploadedTotalImages = Video::getTotalVideos($status, $user_id, !isToHidePrivateVideos(), $showUnlisted, true, false, Video::$videoTypeImage);
    if (!empty($uploadedTotalImages)) {
        $uploadedImages = Video::getAllVideos($status, $user_id, !isToHidePrivateVideos(), [], false, $showUnlisted, true, false, null, Video::$videoTypeImage);
    }
}
//var_dump($uploadedArticles);exit;
$uploadedVideos = [];
if ($ownerCanUplaodVideos) {
    $uploadedTotalVideos = Video::getTotalVideos($status, $user_id, !isToHidePrivateVideos(), $showUnlisted, true, false, $type);
    if (!empty($uploadedTotalVideos)) {
        $uploadedVideos = Video::getAllVideos($status, $user_id, !isToHidePrivateVideos(), [], false, $showUnlisted, true, false, null, $type);
    }
}
//var_dump($ownerCanUplaodVideos, $uploadedTotalVideos, $uploadedVideos, $lastGetTotalVideos, $user->getCanUpload(), $user->getIsAdmin(), $user_id, $user->getUser());exit;
TimeLogEnd($timeLog, __LINE__);
$totalPages = ceil($uploadedTotalVideos / $rowCount);
//var_dump($totalPages, $uploadedTotalVideos, $rowCount);exit;
unset($_POST['sort']);
unset($_POST['rowCount']);
unset($_POST['current']);

$get = ['channelName' => $_GET['channelName']];
$palyListsObj = AVideoPlugin::getObjectDataIfEnabled('PlayLists');
TimeLogEnd($timeLog, __LINE__);
$obj = AVideoPlugin::getObjectData("YouPHPFlix2");

if ($advancedCustomUser->showChannelLiveTab) {
    $liveVideos = getLiveVideosFromUsers_id($user_id);
}

$showChannelHomeTab = $advancedCustomUser->showChannelHomeTab && $ownerCanUplaodVideos && !empty($uploadedVideos);
$showChannelVideosTab = $advancedCustomUser->showChannelVideosTab && $ownerCanUplaodVideos && !empty($uploadedVideos);
$showChannelProgramsTab = $advancedCustomUser->showChannelProgramsTab && !empty($palyListsObj);

function getChannelTabClass($isTabButton, $isVideoTab = false)
{
    global $_getChannelTabClassCount;
    global $_getChannelTabContentClassCount;
    resetCurrentPage();
    if (!isset($_getChannelTabClassCount)) {
        $_getChannelTabClassCount = 0;
    }
    if (!isset($_getChannelTabContentClassCount)) {
        $_getChannelTabContentClassCount = 0;
    }
    if ($isTabButton) {
        $_getChannelTabClassCount++;
        if ($_getChannelTabClassCount == 1 && getCurrentPage() == 1) {
            return ' active ';
        } else if ($isVideoTab && getCurrentPage() != 1) {
            return ' active ';
        }
        return '';
    } else {
        $_getChannelTabContentClassCount++;
        if ($_getChannelTabContentClassCount == 1 && getCurrentPage() == 1) {
            return ' active fade in ';
        } else if ($isVideoTab && getCurrentPage() != 1) {
            return ' active fade in ';
        }
        return ' fade ';
    }
}

?>

<link href="<?php echo getURL('view/css/social.css'); ?>" rel="stylesheet" type="text/css" />
<style>
    #aboutArea #aboutAreaPreContent {
        max-height: 120px;
        overflow: hidden;
        transition: max-height 0.25s ease-out;
        overflow: hidden;
    }

    #aboutAreaPreContent {
        margin-bottom: 30px;
    }

    #aboutArea.expanded #aboutAreaPreContent {
        max-height: 1500px;
        overflow: auto;
        transition: max-height 0.25s ease-in;
    }

    #aboutAreaShowMoreBtn {
        position: absolute;
        bottom: 0;
    }

    #aboutArea .showMore {
        display: block;
    }

    #aboutArea .showLess {
        display: none;
    }

    #aboutArea.expanded .showMore {
        display: none;
    }

    #aboutArea.expanded .showLess {
        display: block;
    }

    #channelHome {
        background-color: rgb(<?php echo $obj->backgroundRGB; ?>);
        position: relative;
        overflow: hidden;
    }

    .feedDropdown {
        margin-right: 4px;
    }
</style>
<!-- <?php var_dump($uploadedTotalVideos, $user_id, !isToHidePrivateVideos()); ?> -->
<div class="clearfix"></div>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="gallery">
            <div class="row clearfix">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <center style="margin:5px;">
                        <?php
                        $getAdsChannelLeaderBoardTop = getAdsChannelLeaderBoardTop();
                        if (!empty($getAdsChannelLeaderBoardTop)) {
                            echo $getAdsChannelLeaderBoardTop;
                        } else {
                            echo "<!-- ";
                            echo "getAdsChannelLeaderBoardTop is empty ";
                            echo implode(', ', ADs::getAdsCodeReason('channelLeaderBoardTop'));
                            echo " -->";
                        }
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
                if (file_exists($global['systemRootPath'] . $relativePath)) {
            ?>
                    <div class="clearfix" style="clear: both;"></div>
                    <a href="<?php echo User::getWebsite($user_id); ?>" target="_blank">
                        <div class="row bg-info profileBg" style="margin: -10px -10px 20px -10px; background: url('<?php echo getURL($relativePath); ?>')  no-repeat 50% 50%; -webkit-background-size: cover;
                             -moz-background-size: cover;
                             -o-background-size: cover;
                             background-size: cover;">
                            <img src="<?php echo User::getPhoto($user_id); ?>" alt="<?php echo str_replace('"', '', $user->_getName()); ?>" class="img img-responsive img-thumbnail" style="max-width: 100px;" />
                        </div>
                    </a>
                <?php
                } else {
                ?>
                    <div class="clearfix" style="clear: both;"></div>
                    <a href="<?php echo User::getWebsite($user_id); ?>" target="_blank">
                        <img src="<?php echo User::getPhoto($user_id); ?>" alt="<?php echo $user->_getName(); ?>" class="img img-responsive img-thumbnail" style="max-width: 100px;" />
                    </a>
            <?php
                }
            }
            ?>
            <div class="row">
                <div class="col-sm-12" style="display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;">
                    <h2 class="pull-left" style="font-size: 2em;">
                        <?php
                        echo $user->getNameIdentificationBd();
                        ?>
                        <?php
                        echo User::getEmailVerifiedIcon($user_id)
                        ?></h2>
                    <ul class="social-network social-circle">
                        <?php
                        $socialMedia = CustomizeUser::getSocialMedia();
                        foreach ($socialMedia as $platform => $details) {
                            if ($details['isActive']) {
                                $url = User::getSocialMediaURL($platform, $user_id);
                                if (!empty($url)) {
                        ?>

                                    <li>
                                        <a href="<?php echo $url; ?>" target="_blank" class="<?php echo $details['class']; ?>" title="<?php echo $details['label']; ?>" data-toggle="tooltip">
                                            <i class="<?php echo $details['icon']; ?>"></i>
                                        </a>
                                    </li>

                        <?php
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
                <div class="col-sm-12">
                    <span class="pull-right">
                        <?php
                        echo AVideoPlugin::getChannelPageButtons($user_id);
                        ?>
                    </span>
                </div>
            </div>

            <div class="col-md-12" id="aboutArea">
                <?php
                $about = html_entity_decode($user->getAbout());
                if (!empty($advancedCustomUser->showAllAboutTextOnChannel)) {
                    echo $about;
                } else {
                ?>
                    <div id="aboutAreaPreContent">
                        <div id="aboutAreaContent">
                            <?php
                            echo $about;
                            ?>
                        </div>
                    </div>
                    <button onclick="$('#aboutArea').toggleClass('expanded');" class="btn btn-xs btn-default" id="aboutAreaShowMoreBtn" style="display: none; ">
                        <span class="showMore"><i class="fas fa-caret-down"></i> <?php echo __("Show More"); ?></span>
                        <span class="showLess"><i class="fas fa-caret-up"></i> <?php echo __("Show Less"); ?></span>
                    </button>
                <?php
                }
                ?>
            </div>

            <script>
                $(document).ready(function() {
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
                            if (!empty($liveVideos)) {
                            ?>
                                <li class="nav-item <?php echo getChannelTabClass(true, false); ?>">
                                    <a class="nav-link " href="#channelLive" data-toggle="tab" aria-expanded="false">
                                        <span class="glow-flash-icon live-icon"></span> <span class="labelUpperCase"><?php echo __('Live Now'); ?></span>
                                    </a>
                                </li>
                            <?php
                            }
                            if ($showChannelHomeTab) {
                            ?>
                                <li class="nav-item <?php echo getChannelTabClass(true, false); ?>>">
                                    <a class="nav-link " href="#channelHome" data-toggle="tab" aria-expanded="false" onclick="setTimeout(function () {flickityReload();}, 500);">
                                        <i class="fas fa-home"></i> <span class="labelUpperCase"><?php echo __('Home'); ?></span>
                                    </a>
                                </li>
                            <?php
                            }
                            if ($showChannelVideosTab) {
                                echo PHP_EOL.'<!-- showChannelVideosTab -->'.PHP_EOL;
                            ?>
                                <li class="nav-item <?php echo getChannelTabClass(true, true); ?>">
                                    <a class="nav-link " href="#channelVideos" data-toggle="tab" aria-expanded="false">
                                        <i class="fas fa-file-video"></i> <span class="labelUpperCase"><?php echo __('Videos'); ?></span> <span class="badge"><?php echo $uploadedTotalVideos; ?></span>
                                    </a>
                                </li>
                            <?php
                            }else{
                                if(!$advancedCustomUser->showChannelVideosTab){
                                    echo PHP_EOL.'<!-- NOT showChannelVideosTab -->'.PHP_EOL;
                                }
                                if(!$ownerCanUplaodVideos){
                                    echo PHP_EOL.'<!-- NOT ownerCanUplaodVideos -->'.PHP_EOL;
                                }
                                if(empty($uploadedVideos)){
                                    echo PHP_EOL.'<!-- empty uploadedVideos -->'.PHP_EOL;
                                }
                            }
                            if (!empty($uploadedTotalArticles)) {
                            ?>
                                <li class="nav-item <?php echo getChannelTabClass(true, false); ?>">
                                    <a class="nav-link " href="#channelArticles" data-toggle="tab" aria-expanded="false">
                                        <i class="far fa-file-alt"></i> <span class="labelUpperCase"><?php echo __('Articles'); ?></span> <span class="badge"><?php echo $uploadedTotalArticles; ?></span>
                                    </a>
                                </li>
                            <?php
                            }
                            if (!empty($uploadedTotalAudio)) {
                            ?>
                                <li class="nav-item <?php echo getChannelTabClass(true, false); ?>">
                                    <a class="nav-link " href="#channelAudio" data-toggle="tab" aria-expanded="false">
                                        <i class="fas fa-file-audio"></i> <span class="labelUpperCase"><?php echo __('Audio'); ?></span> <span class="badge"><?php echo $uploadedTotalAudio; ?></span>
                                    </a>
                                </li>
                                <?php
                            }
                            if (!empty($uploadedTotalImages)) {
                            ?>
                                <li class="nav-item <?php echo getChannelTabClass(true, false); ?>">
                                    <a class="nav-link " href="#channelImages" data-toggle="tab" aria-expanded="false">
                                    <i class="fa-solid fa-images"></i>
                                    <span class="labelUpperCase"><?php echo __("Images"); ?></span>
                                    <span class="badge"><?php echo $uploadedTotalImages; ?></span>
                                    </a>
                                </li>
                                <?php
                            }
                            if ($showChannelProgramsTab) {
                                $totalPrograms = PlayList::getAllFromUserLight($user_id, true, false, 0, true, true);
                                if ($totalPrograms) {
                                ?>
                                    <li class="nav-item <?php echo getChannelTabClass(true, false); ?>" id="channelPlayListsLi">
                                        <a class="nav-link " href="#channelPlayLists" data-toggle="tab" aria-expanded="true">
                                            <i class="fas fa-list"></i> <span class="labelUpperCase"><?php echo __($palyListsObj->name); ?></span> <span class="badge"><?php echo count($totalPrograms); ?></span>
                                        </a>
                                    </li>
                            <?php
                                }
                            }
                            ?>
                        </ul>
                        <div class="tab-content clearfix">
                            <?php
                            if (!empty($liveVideos)) {
                            ?>
                                <div class="tab-pane  <?php echo getChannelTabClass(false, false); ?> clearfix " id="channelLive">
                                    <?php
                                    createGallerySection($liveVideos, false);
                                    ?>
                                </div>
                            <?php
                            }

                            if ($showChannelHomeTab) {
                                $obj = AVideoPlugin::getObjectData("YouPHPFlix2");
                            ?>
                                <style>
                                    #bigVideo {
                                        top: 0 !important;
                                    }
                                </style>
                                <div class="tab-pane <?php echo getChannelTabClass(false, false); ?>" id="channelHome">
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
                            }
                            if ($showChannelVideosTab) {
                            ?>

                                <div class="tab-pane <?php echo getChannelTabClass(false, true); ?>" id="channelVideos">

                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <?php
                                            if ($isMyChannel) {
                                            ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success ">
                                                    <i class="fa-solid fa-film"></i>
                                                    <i class="fa-solid fa-headphones"></i>
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
                                            //var_dump(getCurrentPage());
                                            if ($advancedCustomUser->showBigVideoOnChannelVideosTab && !empty($uploadedVideos[0])) {
                                                $video = $uploadedVideos[0];
                                                $obj = new stdClass();
                                                $obj->BigVideo = true;
                                                $obj->Description = false;
                                                include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideo.php';
                                                if(empty($suggestedOrPinnedFound)){
                                                    unset($uploadedVideos[0]);
                                                }
                                            }
                                            //var_dump(getCurrentPage());exit;
                                            ?>
                                            <div class="row">
                                                <?php
                                                TimeLogEnd($timeLog, __LINE__);
                                                createGallerySection($uploadedVideos, false);
                                                TimeLogEnd($timeLog, __LINE__);
                                                ?>
                                            </div>
                                        </div>

                                        <div class="panel-footer">
                                            <?php
                                            //var_dump($totalPages);
                                            echo getPagination($totalPages, "{$global['webSiteRootURL']}channel/{$_GET['channelName']}?current=_pageNum_");
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            if (!empty($uploadedTotalArticles)) {
                            ?>

                                <div class="tab-pane <?php echo getChannelTabClass(false, false); ?>" id="channelArticles">

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
                                                createGallerySection($uploadedArticles, false);
                                                TimeLogEnd($timeLog, __LINE__);
                                                ?>
                                            </div>
                                        </div>

                                        <div class="panel-footer">
                                            <?php echo getPagination($totalPages, "{$global['webSiteRootURL']}channel/{$_GET['channelName']}?current=_pageNum_"); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }

                            if (!empty($uploadedTotalAudio)) {
                            ?>

                                <div class="tab-pane <?php echo getChannelTabClass(false, false); ?>" id="channelAudio">

                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <?php
                                            if ($isMyChannel) {
                                            ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success ">
                                                    <i class="fa-solid fa-headphones"></i>
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
                                                createGallerySection($uploadedAudio, false);
                                                TimeLogEnd($timeLog, __LINE__);
                                                ?>
                                            </div>
                                        </div>

                                        <div class="panel-footer">
                                            <?php echo getPagination($totalPages, "{$global['webSiteRootURL']}channel/{$_GET['channelName']}?current=_pageNum_"); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            if (!empty($uploadedTotalImages)) {
                            ?>

                                <div class="tab-pane <?php echo getChannelTabClass(false, false); ?>" id="channelImages">

                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <?php
                                            if ($isMyChannel) {
                                            ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success ">
                                                    <i class="fa-solid fa-images"></i>
                                                    <?php echo __("Images"); ?>
                                                </a>
                                            <?php
                                            } else {
                                                echo __("Images");
                                            }
                                            echo AVideoPlugin::getChannelButton();
                                            ?>
                                        </div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <?php
                                                TimeLogEnd($timeLog, __LINE__);
                                                createGallerySection($uploadedImages, false);
                                                TimeLogEnd($timeLog, __LINE__);
                                                ?>
                                            </div>
                                        </div>

                                        <div class="panel-footer">
                                            <?php echo getPagination($totalPages, "{$global['webSiteRootURL']}channel/{$_GET['channelName']}?current=_pageNum_"); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            if ($showChannelProgramsTab) {
                            ?>
                                <div class="tab-pane <?php echo getChannelTabClass(false, false); ?>" id="channelPlayLists" style="min-height: 800px;">
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
                                            <?php
                                            if (!empty($totalPrograms)) {
                                                include $global['systemRootPath'] . 'view/channelPlaylist.php';
                                            } else {
                                                if ($isMyChannel) {
                                            ?>
                                                    <div class="alert alert-warning" role="alert" style="margin-top: 20px;">
                                                        <h4 class="alert-heading text-center"><?php echo __('No Playlist Found'); ?></h4>
                                                        <p class="text-center">
                                                            <?php echo __('You haven\'t created any') . ' ' . __($palyListsObj->name); ?>
                                                        </p>
                                                        <hr>
                                                        <p class="mb-0 text-center">
                                                            <?php echo __('Once you\'ve created a playlist, it will appear here.'); ?>
                                                        </p>
                                                    </div>
                                                <?php
                                                } else {
                                                ?>
                                                    <div class="alert alert-warning" role="alert" style="margin-top: 20px;">
                                                        <h4 class="alert-heading text-center"><?php echo __('No Playlist Found'); ?></h4>
                                                        <p class="text-center">
                                                            <?php echo __('This user does not have any') . ' ' . __($palyListsObj->name); ?>
                                                        </p>
                                                    </div>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div class="panel-footer">

                                        </div>
                                    </div>

                                </div>
                            <?php
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
<script src="<?php echo getURL('plugin/Gallery/script.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('node_modules/infinite-scroll/dist/infinite-scroll.pkgd.min.js'); ?>" type="text/javascript"></script>
