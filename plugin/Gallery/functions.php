<?php

function showThis($who)
{
    if (empty($_GET['showOnly'])) {
        return true;
    }
    if ($_GET['showOnly'] === $who) {
        return true;
    }
    return false;
}

function createGallery($title, $sort, $rowCount, $getName, $mostWord, $lessWord, $orderString, $defaultSort = "ASC", $ignoreGroup = false, $icon = "fas fa-bookmark", $infinityScroll = false)
{
    if (!showThis($getName)) {
        return "";
    }
    global $contentSearchFound;
    $title = __($title);
    $getName = str_replace(array("'", '"', "&quot;", "&#039;"), array('', '', '', ''), xss_esc($getName));
    if (!empty($_GET['showOnly'])) {
        $rowCount = 24;
    }
    global $global, $args, $url;
    $paggingId = uniqid();
    $uid = "gallery" . uniqid();
?>
    <div class="clear clearfix galeryRowElement" id="<?php echo $uid; ?>">
        <?php
        if (canPrintCategoryTitle($title)) {
        ?>
            <h3 class="galleryTitle">
                <a href="<?php echo $global['webSiteRootURL']; ?>?showOnly=<?php echo $getName; ?>">
                    <i class="<?php echo $icon; ?>"></i>
                    <?php
                    if (empty($_GET[$getName])) {
                        $_GET[$getName] = $defaultSort;
                    }
                    if (!empty($orderString)) {
                        $info = createOrderInfo($getName, $mostWord, $lessWord, $orderString);
                        echo "{$title} (" . $info[2] . ") (Page " . $_GET['page'] . ") <a href='" . $info[0] . "' >" . $info[1] . "</a>";
                    } else {
                        echo "{$title}";
                    }
                    ?>
                </a>
            </h3>
        <?php
        }
        $countCols = 0;
        unset($_POST['sort']);
        if (empty($_GET['page'])) {
            $_GET['page'] = 1;
        }
        $_POST['sort'][$sort] = $_GET[$getName];
        $_REQUEST['current'] = $_GET['page'];
        $_REQUEST['rowCount'] = $rowCount;

        $videoStatus = 'viewableNotUnlisted';

        if ($getName == 'privateContentOrder') {
            $videoStatus = 'privateOnly';
            $ignoreGroup = true;
        }

        $total = Video::getTotalVideos($videoStatus, false, $ignoreGroup);
        if (empty($contentSearchFound)) {
            $contentSearchFound = !empty($total);
        }
        $totalPages = ceil($total / $_REQUEST['rowCount']);
        $page = $_GET['page'];
        if ($totalPages < $_GET['page']) {
            if ($infinityScroll) {
                echo '</div>';
                return 0;
            }
            $page = $totalPages;
            $_REQUEST['current'] = $totalPages;
        }
        $videos = Video::getAllVideos($videoStatus, false, $ignoreGroup);
        // need to add dechex because some times it return an negative value and make it fails on javascript playlists
        ?>
        <div class="gallerySectionContent">
            <?php
            $countCols = createGallerySection($videos);
            ?>
        </div>
        <?php
        if ($countCols) {
        ?>
            <!-- createGallery -->
            <div class="col-sm-12" style="z-index: 1;">
                <?php
                $infinityScrollGetFromSelector = "";
                $infinityScrollAppendIntoSelector = "";
                if ($infinityScroll) {
                    $infinityScrollGetFromSelector = ".gallerySectionContent";
                    $infinityScrollAppendIntoSelector = ".gallerySectionContent";
                }

                echo getPagination($totalPages, $page, "{$url}{page}{$args}", 10, $infinityScrollGetFromSelector, $infinityScrollAppendIntoSelector);
                ?>
            </div>
        <?php
        }
        ?>
    </div>
    <?php
    if (empty($countCols)) {
    ?>
        <style>
            #<?php echo $uid; ?> {
                display: none;
            }
        </style>
    <?php
    }
}

function createOrderInfo($getName, $mostWord, $lessWord, $orderString)
{
    $upDown = "";
    $mostLess = "";
    $tmpOrderString = $orderString;
    if ($_GET[$getName] == "DESC") {
        if (strpos($orderString, $getName . "=DESC")) {
            $tmpOrderString = substr($orderString, 0, strpos($orderString, $getName . "=DESC")) . $getName . "=ASC" . substr($orderString, strpos($orderString, $getName . "=DESC") + strlen($getName . "=DESC"), strlen($orderString));
        } else {
            $tmpOrderString .= $getName . "=ASC";
        }

        $upDown = "<span class='glyphicon glyphicon-arrow-up' >" . __("Up") . "</span>";
        $mostLess = $mostWord;
    } else {
        if (strpos($orderString, $getName . "=ASC")) {
            $tmpOrderString = substr($orderString, 0, strpos($orderString, $getName . "=ASC")) . $getName . "=DESC" . substr($orderString, strpos($orderString, $getName . "=ASC") + strlen($getName . "=ASC"), strlen($orderString));
        } else {
            $tmpOrderString .= $getName . "=DESC";
        }

        $upDown = "<span class='glyphicon glyphicon-arrow-down'>" . __("Down") . "</span>";
        $mostLess = $lessWord;
    }

    if (substr($tmpOrderString, strlen($tmpOrderString) - 1, strlen($tmpOrderString)) == "&") {
        $tmpOrderString = substr($tmpOrderString, 0, strlen($tmpOrderString) - 1);
    }

    return array($tmpOrderString, $upDown, $mostLess);
}

function createGallerySection($videos, $showChannel = true, $ignoreAds = false, $screenColsLarge = 0, $screenColsMedium = 0, $screenColsSmall = 0, $screenColsXSmall = 0, $galeryDetails = true)
{
    global $global, $config, $obj, $advancedCustom, $advancedCustomUser;
    $countCols = 0;
    $obj = AVideoPlugin::getObjectData("Gallery");
    $zindex = 1000;
    $program = AVideoPlugin::loadPluginIfEnabled('PlayLists');

    $videoCount = count($videos);
    $screenColsLarge = 0;
    $screenColsMedium = 0;
    $screenColsSmall = 0;
    $screenColsXSmall = 0;
    if ($videoCount < 5) {
        switch ($videoCount) {
            case 4:
                $screenColsLarge = 4;
                $screenColsMedium = 4;
                $screenColsSmall = 2;
                $screenColsXSmall = 1;
                break;
            case 3:
                $screenColsLarge = 3;
                $screenColsMedium = 3;
                $screenColsSmall = 2;
                $screenColsXSmall = 1;
                break;
            case 2:
                $screenColsLarge = 3;
                $screenColsMedium = 3;
                $screenColsSmall = 2;
                $screenColsXSmall = 1;
                break;
            case 1:
                $screenColsLarge = 3;
                $screenColsMedium = 3;
                $screenColsSmall = 1;
                $screenColsXSmall = 1;
                break;
        }
    }
    //var_dump($screenColsLarge, $screenColsMedium, $screenColsSmall, $screenColsXSmall);
    foreach ($videos as $video) {
        if (!empty($video['isLive'])) {
            createGalleryLiveSectionVideo($video, $zindex, $screenColsLarge, $screenColsMedium, $screenColsSmall, $screenColsXSmall);
        } else {
            createGallerySectionVideo($video, $showChannel, $screenColsLarge, $screenColsMedium, $screenColsSmall, $screenColsXSmall, $galeryDetails, $zindex);
        }

        $countCols++;
        $zindex--;
        if ($countCols > 1) {
            if ($countCols % $obj->screenColsLarge === 0) {
                echo "<div class='clearfix hidden-md hidden-sm hidden-xs'></div>";
            }
            if ($countCols % $obj->screenColsMedium === 0) {
                echo "<div class='clearfix hidden-lg hidden-sm hidden-xs'></div>";
            }
            if ($countCols % $obj->screenColsSmall === 0) {
                echo "<div class='clearfix hidden-lg hidden-md hidden-xs'></div>";
            }
            if ($countCols % $obj->screenColsXSmall === 0) {
                echo "<div class='clearfix hidden-lg hidden-md hidden-sm'></div>";
            }
        }
    }
    ?>
    <div class="col-xs-12  text-center clear clearfix" style="padding: 10px;">
        <?php
        if (empty($ignoreAds)) {
            echo getAdsLeaderBoardMiddle();
        }
        ?>
    </div>
    <?php
    unset($_POST['disableAddTo']);
    return $countCols;
}

function getLabelTags($video)
{
    global $global;
    $obj = AVideoPlugin::getObjectData("Gallery");
    if (empty($_REQUEST['catName']) && !empty($obj->showCategoryTag)) {
        $iconClass = 'fas fa-folder';
        if (!empty($video['iconClass'])) {
            $iconClass = $video['iconClass'];
        }
        $icon = '<i class="' . $iconClass . '"></i>';
    ?>
        <a class="label label-default videoCategoryLabel" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $video['clean_category']; ?>" data-toggle="tooltip" title="<?php echo htmlentities($icon . ' ' . $video['category']); ?>" data-html="true">
            <?php
            echo $icon;
            ?>
        </a>
    <?php } ?>
    <!-- plugins tags -->
    <?php
    echo Video::getTagsHTMLLabelIfEnable($video['id']);
    ?>
    <!-- end plugins tags -->
<?php
}

function getGalleryColsCSSClass($screenColsLarge = 0, $screenColsMedium = 0, $screenColsSmall = 0, $screenColsXSmall = 0)
{
    global $objGallery;
    if (empty($objGallery)) {
        $objGallery = AVideoPlugin::getObjectData("Gallery");
    }
    $objGalleryScreenColsLarge = $objGallery->screenColsLarge;
    $objGalleryScreenColsMedium = $objGallery->screenColsMedium;
    $objGalleryScreenColsSmall = $objGallery->screenColsSmall;
    $objGalleryScreenColsXSmall = $objGallery->screenColsXSmall;

    if (!empty($screenColsLarge)) {
        $objGalleryScreenColsLarge = $screenColsLarge;
    }
    if (!empty($screenColsMedium)) {
        $objGalleryScreenColsMedium = $screenColsMedium;
    }
    if (!empty($screenColsSmall)) {
        $objGalleryScreenColsSmall = $screenColsSmall;
    }
    if (!empty($screenColsXSmall)) {
        $objGalleryScreenColsXSmall = $screenColsXSmall;
    }
    $colsClass = "col-lg-" . (12 / $objGalleryScreenColsLarge) . " col-md-" . (12 / $objGalleryScreenColsMedium) . " col-sm-" . (12 / $objGalleryScreenColsSmall) . " col-xs-" . (12 / $objGalleryScreenColsXSmall);
    return $colsClass;
}

function createGallerySectionVideo($video, $showChannel = true, $screenColsLarge = 0, $screenColsMedium = 0, $screenColsSmall = 0, $screenColsXSmall = 0, $galeryDetails = true, $zindex = 1000)
{
    global $global, $advancedCustom, $_lastCanDownloadVideosFromVideoReason;
    $nameId = User::getNameIdentificationById($video['users_id']);
    $name = $nameId . " " . User::getEmailVerifiedIcon($video['users_id']);
    $colsClass = getGalleryColsCSSClass($screenColsLarge, $screenColsMedium, $screenColsSmall, $screenColsXSmall);
    if(!$showChannel){
        $colsClass .= ' notShowChannel';
    }
?>
    <!-- createGallerySection -->
    <div class=" <?php echo $colsClass; ?> galleryVideo galleryVideo<?php echo $video['id']; ?> fixPadding" style="z-index: <?php echo $zindex; ?>; min-height: 175px;">
        <?php
        $img = Video::getVideoImagewithHoverAnimationFromVideosId($video, true, true, true);
        if (empty($img)) {
            //var_dump($video);
        } else {
            echo $img;
        }
        ?>
        <?php
        if ($galeryDetails) {
        ?>
            <div class="galeryDetailsContent clearfix">
                <div class="clearfix"></div>
                <?php
                if (!empty($advancedCustom->showChannelPhotoOnVideoItem) && $showChannel) {
                ?>
                    <a href="<?php echo User::getChannelLink($video['users_id']); ?>" class=" pull-left " data-toggle="tooltip" title="<?php echo $nameId; ?>">
                        <img src="<?php echo User::getPhoto($video['users_id']); ?>" class="img img-responsive  img-rounded pull-left channelPhoto" />
                    </a>
                <?php
                }
                if (!empty($advancedCustom->showEllipsisMenuOnVideoItem)) {
                ?>
                    <!-- Dropdown trigger -->
                    <div class="dropdown pull-right">
                        <!-- Dropdown Menu -->
                        <div class="dropdown-menu" id="videoButtonOptions">
                            <?php
                            echo AVideoPlugin::getGalleryActionButton($video['id']);
                            ?>
                        </div>
                        <!-- Trigger button -->
                        <button class="btn btn-link btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                <?php
                }
                ?>
                <div class="pull-left channelPhotoDescription">
                    <div class="galeryDetails pull-left">
                        <div class="galleryTags  pull-left">
                            <!-- category tags -->
                            <?php
                            getLabelTags($video);
                            ?>
                            <!-- end category tags -->
                        </div>
                        <?php
                        if (empty($advancedCustom->doNotDisplayViews)) {
                            if (AVideoPlugin::isEnabledByName('LiveUsers')) {
                                echo getLiveUsersLabelVideo($video['id'], $video['views_count'], "", "");
                            } else {
                        ?>
                                <div class="videoViews">
                                    <i class="fa fa-eye"></i>
                                    <span itemprop="interactionCount">
                                        <?php echo number_format($video['views_count'], 0); ?> <?php echo __("Views"); ?>
                                    </span>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="clearfix"></div>

                    <?php
                    if (!empty($advancedCustom->showChannelNameOnVideoItem) && $showChannel) {
                    ?>
                        <div class="videoChannel pull-left">
                            <a href="<?php echo User::getChannelLink($video['users_id']); ?>">
                                <?php echo $name; ?>
                            </a>
                        </div>
                    <?php
                    }
                    ?>
                    <?php
                    if (empty($advancedCustom->showEllipsisMenuOnVideoItem)) {
                        echo AVideoPlugin::getGalleryActionButton($video['id']);
                    }
                    ?>
                    <?php
                    if (!empty($advancedCustom->showCreationTimeOnVideoItem)) {
                        $humanTiming = humanTiming(strtotime($video['videoCreation']), 0, true, true);
                    ?>
                        <time datetime="<?php echo $video['videoCreation']; ?>" class="videoHumanTime pull-right">
                            <i class="far fa-clock"></i>
                            <?php echo $humanTiming; ?>
                        </time>
                    <?php
                    }
                    ?>
                </div>
                <?php
                if (CustomizeUser::canDownloadVideosFromVideo($video['id'])) {
                    $files = getVideosURL($video['filename']);
                    if (!empty($files['mp4']) || !empty($files['mp3'])) {
                ?>
                        <div style="position: relative; overflow: visible; z-index: 3;" class="dropup">
                            <button type="button" class="btn btn-default btn-sm btn-xs btn-block" data-toggle="dropdown">
                                <i class="fa fa-download"></i> <?php echo __('Download'); ?> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                <?php
                                //var_dump($files);exit;
                                foreach ($files as $key => $theLink) {
                                    if (($theLink['type'] !== 'video' && $theLink['type'] !== 'audio') || $key == "m3u8") {
                                        continue;
                                    }
                                    $path_parts = pathinfo($theLink['filename']);
                                ?>
                                    <li>
                                        <a href="<?php echo $theLink['url']; ?>?download=1&title=<?php echo urlencode($video['title'] . "_{$key}_.{$path_parts['extension']}"); ?>">
                                            <?php echo __("Download"); ?> <?php echo $key; ?>
                                        </a>
                                    </li>
                                <?php }
                                ?>
                            </ul>
                        </div>
                <?php
                    } else {
                        echo "<!-- canDownloadVideosFromVideo you can only download MP3 or MP4 -->";
                    }
                } else {
                    echo "<!-- canDownloadVideosFromVideo {$_lastCanDownloadVideosFromVideoReason} -->";
                }
                ?>
            </div>
        <?php
        }
        ?>
    </div>

<?php
}

function createGalleryLiveSection($videos)
{
    global $global, $config, $obj, $advancedCustom, $advancedCustomUser;
    $countCols = 0;
    $obj = AVideoPlugin::getObjectData("Gallery");
    $liveobj = AVideoPlugin::getObjectData("Live");
    $zindex = 1000;
    $program = AVideoPlugin::loadPluginIfEnabled('PlayLists');
    foreach ($videos as $video) {

        createGalleryLiveSectionVideo($video, $zindex);

        $name = User::getNameIdentificationById($video['users_id']);
        $name .= " " . User::getEmailVerifiedIcon($video['users_id']);

        $countCols++;
        $zindex--;
        if ($countCols > 1) {
            if ($countCols % $obj->screenColsLarge === 0) {
                echo "<div class='clearfix hidden-md hidden-sm hidden-xs'></div>";
            }
            if ($countCols % $obj->screenColsMedium === 0) {
                echo "<div class='clearfix hidden-lg hidden-sm hidden-xs'></div>";
            }
            if ($countCols % $obj->screenColsSmall === 0) {
                echo "<div class='clearfix hidden-lg hidden-md hidden-xs'></div>";
            }
            if ($countCols % $obj->screenColsXSmall === 0) {
                echo "<div class='clearfix hidden-lg hidden-md hidden-sm'></div>";
            }
        }
        if (!empty($video['galleryCallback'])) {
            $video['galleryCallback'] = addcslashes($video['galleryCallback'], '"');
            echo '<!-- galleryCallback --><script>$(document).ready(function () {eval("' . $video['galleryCallback'] . '")});</script>';
        }
    }
?>
    <div class="col-xs-12  text-center clear clearfix" style="padding: 10px;">
        <?php
        if (empty($ignoreAds)) {
            echo getAdsLeaderBoardMiddle();
        }
        ?>
    </div>
<?php
    unset($_POST['disableAddTo']);
    return $countCols;
}

function createGalleryLiveSectionVideo($video, $zindex, $screenColsLarge = 0, $screenColsMedium = 0, $screenColsSmall = 0, $screenColsXSmall = 0)
{
    global $global, $config, $objGallery, $advancedCustom, $advancedCustomUser;
    $objGallery = AVideoPlugin::getObjectData("Gallery");
    $name = User::getNameIdentificationById($video['users_id']);
    $name .= " " . User::getEmailVerifiedIcon($video['users_id']);

    $colsClass = getGalleryColsCSSClass($screenColsLarge, $screenColsMedium, $screenColsSmall, $screenColsXSmall);

    if (!empty($video['className'])) {
        $colsClass .= " {$video['className']}";
    }

    $liveNow = '<span class="label label-danger liveNow faa-flash faa-slow animated" style="position: absolute;
    bottom: 5px;
    right: 5px;">' . __("LIVE NOW") . '</span>';
?>
    <!-- createGalleryLiveSection start -->
    <div class=" <?php echo $colsClass; ?> galleryVideo galleryVideo<?php echo $video['id']; ?> fixPadding" style="z-index: <?php echo $zindex; ?>; min-height: 175px;">
        <a class="galleryLink" videos_id="<?php echo $video['id']; ?>" href="<?php echo $video['href']; ?>" embed="<?php echo $video['link']; ?>" alternativeLink="<?php echo @$video['alternativeLink']; ?>" title="<?php echo htmlentities($video['title']); ?>">
            <div class="aspectRatio16_9">
                <?php
                $relativePathHoverAnimation = @$video['imgGif'];
                echo getVideoImagewithHoverAnimation($video['poster'], $relativePathHoverAnimation, $video['title']);
                echo $liveNow;
                ?>
            </div>
        </a>
        <a class="h6 galleryLink" videos_id="<?php echo $video['id']; ?>" href="<?php echo $video['href']; ?>" embed="<?php echo $video['link']; ?>" alternativeLink="<?php echo @$video['alternativeLink']; ?>" title="<?php echo htmlentities(getSEOTitle($video['title'])); ?>">
            <strong class="title"><?php echo getSEOTitle($video['title']) ?></strong>
        </a>

        <div class="galeryDetails">
            <div class="galleryTags">
                <?php if (empty($_REQUEST['catName']) && !empty($objGallery->showCategoryTag)) { ?>
                    <a class="label label-default" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $video['clean_category']; ?>">
                        <?php
                        if (!empty($video['iconClass'])) {
                        ?>
                            <i class="<?php echo $video['iconClass']; ?>"></i>
                        <?php
                        }
                        ?>
                        <?php echo $video['category']; ?>
                    </a>
                <?php } ?>
            </div>
            <div>
                <i class="fa fa-user"></i>
                <a href="<?php echo User::getChannelLink($video['users_id']); ?>">
                    <?php echo $name; ?>
                </a>
            </div>
            <?php
            if ((!empty($video['description'])) && !empty($objGallery->Description)) {
                $desc = str_replace(array('"', "'", "#", "/", "\\"), array('``', "`", "", "", ""), preg_replace("/\r|\n/", " ", nl2br(trim($video['description']))));
                if (!isHTMLEmpty($desc)) {
                    $titleAlert = str_replace(array('"', "'"), array('``', "`"), $video['title']);
            ?>
                    <div>
                        <a href="#" onclick='avideoAlert("<?php echo $titleAlert; ?>", "<div style=\"max-height: 300px; overflow-y: scroll;overflow-x: hidden;\"><?php echo $desc; ?></div>", "info");return false;' data-toggle="tooltip" title="<?php echo __("Description"); ?>"><i class="far fa-file-alt"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Description"); ?></span></a>
                    </div>
            <?php
                }
            }
            ?>
        </div>
    </div>
    <!-- createGalleryLiveSection end -->
<?php
}

function createChannelItem($users_id, $photoURL = "", $identification = "", $rowCount = 12)
{
    $total = Video::getTotalVideos("viewable", $users_id);
    if (empty($total)) {
        return false;
    }
    if (empty($photoURL)) {
        $photoURL = User::getPhoto($users_id);
    }
    if (empty($identification)) {
        $identification = User::getNameIdentificationById($users_id);
    }
?>
    <div class="clear clearfix">
        <h3 class="galleryTitle">
            <img src="<?php echo $photoURL; ?>" class="img img-circle img-responsive pull-left" style="max-height: 20px;" alt="Channel Owner">
            <span style="margin: 0 5px;">
                <?php
                echo $identification;
                ?>
            </span>
            <a class="btn btn-xs btn-default" href="<?php echo User::getChannelLink($users_id); ?>" style="margin: 0 10px;">
                <i class="fas fa-external-link-alt"></i>
            </a>
            <?php
            echo Subscribe::getButton($users_id);
            ?>
        </h3>
        <div class="">
            <?php
            $countCols = 0;
            unset($_POST['sort']);
            $_POST['sort']['created'] = "DESC";
            $_REQUEST['current'] = 1;
            $_REQUEST['rowCount'] = $rowCount;
            $videos = Video::getAllVideos("viewable", $users_id);
            createGallerySection($videos);
            ?>
        </div>
    </div>
<?php
}

$search = "";
$searchPhrase = "";

function clearSearch()
{
    global $search, $searchPhrase;
    $search = $_GET['search'];
    $searchPhrase = $_POST['searchPhrase'];
    unset($_GET['search']);
    unset($_POST['searchPhrase']);
}

function reloadSearch()
{
    global $search, $searchPhrase;
    $_GET['search'] = $search;
    $_POST['searchPhrase'] = $searchPhrase;
}

function getTrendingVideos($rowCount = 12, $screenColsLarge = 0, $screenColsMedium = 0, $screenColsSmall = 0, $screenColsXSmall = 0)
{
    global $global;
    $countCols = 0;
    unset($_POST['sort']);
    $_GET['sort']['trending'] = 1;
    $_REQUEST['current'] = getCurrentPage();
    $_REQUEST['rowCount'] = $rowCount;
    $videos = Video::getAllVideos("viewableNotUnlisted");
    // need to add dechex because some times it return an negative value and make it fails on javascript playlists
    echo "<link href=\"" . getURL('plugin/Gallery/style.css') . "\" rel=\"stylesheet\" type=\"text/css\"/><div class='row gallery '>";
    $countCols = createGallerySection($videos, true, false, $screenColsLarge, $screenColsMedium, $screenColsSmall, $screenColsXSmall);
    echo "</div>";
    return $countCols;
}

function canPrintCategoryTitle($title)
{
    global $doNotRepeatCategoryTitle;
    if (!isset($doNotRepeatCategoryTitle)) {
        $doNotRepeatCategoryTitle = array();
    }
    if (in_array($title, $doNotRepeatCategoryTitle)) {
        return false;
    }
    $doNotRepeatCategoryTitle[] = $title;
    return true;
}
?>