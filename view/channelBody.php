<?php
$isMyChannel = false;
if (User::isLogged() && $user_id == User::getId()) {
    $isMyChannel = true;
}
$user = new User($user_id);

if ($user->getBdId() != $user_id) {
    header("Location: {$global['webSiteRootURL']}channels");
    exit;
}

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

$uploadedVideos = Video::getAllVideos("a", $user_id, !isToHidePrivateVideos());
$uploadedTotalVideos = Video::getTotalVideos("a", $user_id, !isToHidePrivateVideos());
TimeLogEnd($timeLog, __LINE__);
$totalPages = ceil($uploadedTotalVideos / $rowCount);

unset($_POST['sort']);
unset($_POST['rowCount']);
unset($_POST['current']);

$get = array('channelName' => $_GET['channelName']);
$palyListsObj = AVideoPlugin::getObjectDataIfEnabled('PlayLists');
TimeLogEnd($timeLog, __LINE__);
?>
<!-- <?php var_dump($uploadedTotalVideos, $user_id, !isToHidePrivateVideos()); ?> -->
<div class="clearfix"></div>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="gallery" >
            <div class="row">
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
                ?>
                <div class="row bg-info profileBg" style="margin: 20px -10px; background: url('<?php echo $global['webSiteRootURL'], $user->getBackgroundURL(), "?", @filemtime($global['systemRootPath'] . $user->getBackgroundURL()); ?>')  no-repeat 50% 50%;">
                    <img src="<?php echo User::getPhoto($user_id); ?>" alt="<?php echo $user->_getName(); ?>" class="img img-responsive img-thumbnail" style="max-width: 100px;"/>
                </div>    
                <?php
            }
            ?>
            <div class="row"><div class="col-6 col-md-12">
                    <h2 class="pull-left">
                        <?php
                        echo $user->getNameIdentificationBd();
                        ?>
                        <?php
                        echo User::getEmailVerifiedIcon($user_id)
                        ?></h2>
                    <span class="pull-right">
                        <?php
                        echo User::getBlockUserButton($user_id);
                        echo Subscribe::getButton($user_id);
                        ?>
                    </span>
                </div></div>
            <div class="col-md-12">
                <?php echo nl2br($user->getAbout()); ?>
            </div>


            <?php
            if (!User::hasBLockedUser($user_id)) {
                ?>
                <div class="tabbable-panel">
                    <div class="tabbable-line">
                        <ul class="nav nav-tabs">
                            <?php
                            $active = "active";
                            if ($advancedCustomUser->showChannelHomeTab) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = "";
                                }
                                ?>
                                <li class="nav-item <?php echo $active; ?>">
                                    <a class="nav-link " href="#channelHome" data-toggle="tab" aria-expanded="false">
                                        <?php echo strtoupper(__("Home")); ?>
                                    </a>
                                </li>
                                <?php
                                $active = "";
                            }
                            if ($advancedCustomUser->showChannelVideosTab) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = "active";
                                }
                                ?>
                                <li class="nav-item <?php echo $active; ?>">
                                    <a class="nav-link " href="#channelVideos" data-toggle="tab" aria-expanded="false">
                                        <?php echo strtoupper(__("Videos")); ?> <span class="badge"><?php echo $uploadedTotalVideos; ?></span>
                                    </a>
                                </li>
                                <?php
                                $active = "";
                            }
                            if ($advancedCustomUser->showChannelProgramsTab && !empty($palyListsObj)) {
                                ?>
                                <li class="nav-item <?php echo $active; ?>" id="channelPlayListsLi">
                                    <a class="nav-link " href="#channelPlayLists" data-toggle="tab" aria-expanded="true">
                                        <?php echo strtoupper(__("Playlists")); ?>
                                    </a>
                                </li>
                                <?php
                                $active = "";
                            }
                            ?>
                        </ul>
                        <div class="tab-content clearfix">
                            <?php
                            $active = "active fade in";
                            if ($advancedCustomUser->showChannelHomeTab) {
                                if (!empty($_GET['current'])) { // means you are paging the Videos tab
                                    $active = "";
                                }
                                $obj = AVideoPlugin::getObjectData("YouPHPFlix2");
                                ?>
                                <style>#bigVideo{top: 0 !important;}</style>
                                <div class="tab-pane  <?php echo $active; ?>" id="channelHome" style="min-height: 800px; background-color: rgb(<?php echo $obj->backgroundRGB; ?>);position: relative; overflow: hidden;">
                                    <?php
                                    $obj->BigVideo = true;
                                    $obj->Trending = true;
                                    $obj->pageDots = false;
                                    $obj->TrendingAutoPlay = true;
                                    $obj->maxVideos = 12;
                                    $obj->Suggested = false;
                                    $obj->paidOnlyLabelOverPoster = false;
                                    $obj->DateAdded = false;
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
                                            <?php
                                            echo getPagination($totalPages, $current, "{$global['webSiteRootURL']}channel/{$_GET['channelName']}?current={page}");
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $active = "fade";
                            }
                            if ($advancedCustomUser->showChannelProgramsTab && !empty($palyListsObj)) {
                                ?>
                                <div class="tab-pane <?php echo $active; ?>" id="channelPlayLists" style="min-height: 800px;">
                                    <?php
                                    include $global['systemRootPath'] . 'view/channelPlaylist.php';
                                    ?>
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
<script src="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/script.js" type="text/javascript"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>view/js/infinite-scroll.pkgd.min.js" type="text/javascript"></script>