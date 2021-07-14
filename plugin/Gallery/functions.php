<?php

function showThis($who) {
    if (empty($_GET['showOnly'])) {
        return true;
    }
    if ($_GET['showOnly'] === $who) {
        return true;
    }
    return false;
}

function createGallery($title, $sort, $rowCount, $getName, $mostWord, $lessWord, $orderString, $defaultSort = "ASC", $ignoreGroup = false, $icon = "fas fa-bookmark", $infinityScroll = false) {
    if (!showThis($getName)) {
        return "";
    }
    $getName = str_replace(array("'", '"', "&quot;", "&#039;"), array('', '', '', ''), xss_esc($getName));
    if (!empty($_GET['showOnly'])) {
        $rowCount = 24;
    }
    global $global, $args, $url;
    $paggingId = uniqid();
    $uid = "gallery" . uniqid();
    ?>
    <div class="row clear clearfix galeryRowElement" id="<?php echo $uid; ?>">
        <?php
        if (canPrintCategoryTitle($title)) {
            ?>
            <h3 class="galleryTitle">
                <a class="btn-default" href="<?php echo $global['webSiteRootURL']; ?>?showOnly=<?php echo $getName; ?>">
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

        $total = Video::getTotalVideos("viewable");
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
        $videos = Video::getAllVideos("viewableNotUnlisted", false, $ignoreGroup);
        // need to add dechex because some times it return an negative value and make it fails on javascript playlists
        ?>
        <div class="gallerySectionContent">
            <?php
            $countCols = createGallerySection($videos, dechex(crc32($getName)));
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
            #<?php echo $uid; ?>{
                display: none;
            }
        </style>
        <?php
    }
}

function createOrderInfo($getName, $mostWord, $lessWord, $orderString) {
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

function createGallerySection($videos, $crc = "", $get = array(), $ignoreAds = false, $screenColsLarge = 0, $screenColsMedium = 0, $screenColsSmall = 0, $screenColsXSmall = 0) {
    global $global, $config, $obj, $advancedCustom, $advancedCustomUser;
    $countCols = 0;
    $obj = AVideoPlugin::getObjectData("Gallery");
    $zindex = 1000;
    $startG = microtime(true);
    $program = AVideoPlugin::loadPluginIfEnabled('PlayLists');
    foreach ($videos as $value) {

        // that meas auto generate the channelName
        if (empty($get) && !empty($obj->filterUserChannel)) {
            $getCN = array('channelName' => $value['channelName'], 'catName' => @$_GET['catName']);
        } else {
            $getCN = $get;
        }

        $img_portrait = (@$value['rotation'] === "90" || @$value['rotation'] === "270") ? "img-portrait" : "";
        $name = User::getNameIdentificationById($value['users_id']);
        $name .= " " . User::getEmailVerifiedIcon($value['users_id']);
        // make a row each 6 cols
        if ($countCols % $obj->screenColsLarge === 0) {
            echo '<div class="clearfix "></div>';
        }

        $countCols++;

        if (!empty($screenColsLarge)) {
            $obj->screenColsLarge = $screenColsLarge;
        }
        if (!empty($screenColsMedium)) {
            $obj->screenColsMedium = $screenColsMedium;
        }
        if (!empty($screenColsSmall)) {
            $obj->screenColsSmall = $screenColsSmall;
        }
        if (!empty($screenColsXSmall)) {
            $obj->screenColsXSmall = $screenColsXSmall;
        }

        $colsClass = "col-lg-" . (12 / $obj->screenColsLarge) . " col-md-" . (12 / $obj->screenColsMedium) . " col-sm-" . (12 / $obj->screenColsSmall) . " col-xs-" . (12 / $obj->screenColsXSmall);
        $isserie = Video::isSerie($value['id']);

        $isserieClass = "";
        if ($isserie) {
            $isserieClass = "isserie";
        }
        ?>
        <div class=" <?php echo $colsClass; ?> galleryVideo thumbsImage fixPadding" style="z-index: <?php echo $zindex--; ?>; min-height: 175px;" itemscope itemtype="http://schema.org/VideoObject">
            <a class="galleryLink <?php echo $isserieClass; ?>" videos_id="<?php echo $value['id']; ?>" 
               href="<?php echo Video::getLink($value['id'], $value['clean_title'], false, $getCN); ?>"  
               embed="<?php echo Video::getLink($value['id'], $value['clean_title'], true, $getCN); ?>" title="<?php echo $value['title']; ?>">
                   <?php
                   @$timesG[__LINE__] += microtime(true) - $startG;
                   $startG = microtime(true);
                   $images = Video::getImageFromFilename($value['filename'], $value['type']);
                   @$timesG[__LINE__] += microtime(true) - $startG;
                   if (!is_object($images)) {
                       $images = new stdClass();
                       $images->thumbsGif = "";
                       $images->poster = "" . getCDN() . "view/img/notfound.jpg";
                       $images->thumbsJpg = "" . getCDN() . "view/img/notfoundThumbs.jpg";
                       $images->thumbsJpgSmall = "" . getCDN() . "view/img/notfoundThumbsSmall.jpg";
                   }
                   if ($value['type'] === 'serie' && !empty($value['serie_playlists_id']) && stripos($images->thumbsJpg, 'notfound') !== false) {
                       $images = PlayList::getRandomImageFromPlayList($value['serie_playlists_id']);
                   }
                   $startG = microtime(true);
                   $imgGif = $images->thumbsGif;
                   $poster = $images->thumbsJpg;
                   ?>
                <div class="aspectRatio16_9">
                    <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>  <?php echo ($poster != $images->thumbsJpgSmall && !empty($advancedCustom->usePreloadLowResolutionImages)) ? "blur" : ""; ?>" id="thumbsJPG<?php echo $value['id']; ?>" />
                    <?php if (!empty($imgGif)) { ?>
                        <img src="<?php echo getCDN(); ?>img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                    <?php } ?>
                    <?php
                    echo AVideoPlugin::thumbsOverlay($value['id']);
                    @$timesG[__LINE__] += microtime(true) - $startG;
                    $startG = microtime(true);
                    if (!empty($program) && $isserie) {
                        ?>
                        <div class="gallerySerieOverlay">
                            <div class="gallerySerieOverlayTotal">
                                <?php
                                $plids = PlayList::getVideosIDFromPlaylistLight($value['serie_playlists_id']);
                                echo count($plids);
                                ?>
                                <br><i class="fas fa-list"></i>
                            </div>
                            <i class="fas fa-play"></i>
                            <?php
                            echo __("Play All");
                            ?>
                        </div>
                        <?php
                    } else
                    if (!empty($program) && User::isLogged()) {
                        ?>
                        <div class="galleryVideoButtons">
                            <?php
                            //var_dump($value['isWatchLater'], $value['isFavorite']);
                            if ($value['isWatchLater']) {
                                $watchLaterBtnAddedStyle = "";
                                $watchLaterBtnStyle = "display: none;";
                            } else {
                                $watchLaterBtnAddedStyle = "display: none;";
                                $watchLaterBtnStyle = "";
                            }
                            if ($value['isFavorite']) {
                                $favoriteBtnAddedStyle = "";
                                $favoriteBtnStyle = "display: none;";
                            } else {
                                $favoriteBtnAddedStyle = "display: none;";
                                $favoriteBtnStyle = "";
                            }
                            ?>

                            <button onclick="addVideoToPlayList(<?php echo $value['id']; ?>, false, <?php echo $value['watchLaterId']; ?>);return false;" class="btn btn-dark btn-xs watchLaterBtnAdded watchLaterBtnAdded<?php echo $value['id']; ?>" data-toggle="tooltip" data-placement="left" title="<?php echo __("Added On Watch Later"); ?>" style="color: #4285f4;<?php echo $watchLaterBtnAddedStyle; ?>" ><i class="fas fa-check"></i></button> 
                            <button onclick="addVideoToPlayList(<?php echo $value['id']; ?>, true, <?php echo $value['watchLaterId']; ?>);return false;" class="btn btn-dark btn-xs watchLaterBtn watchLaterBtn<?php echo $value['id']; ?>" data-toggle="tooltip" data-placement="left" title="<?php echo __("Watch Later"); ?>" style="<?php echo $watchLaterBtnStyle; ?>" ><i class="fas fa-clock"></i></button>
                            <br>
                            <button onclick="addVideoToPlayList(<?php echo $value['id']; ?>, false, <?php echo $value['favoriteId']; ?>);return false;" class="btn btn-dark btn-xs favoriteBtnAdded favoriteBtnAdded<?php echo $value['id']; ?>" data-toggle="tooltip" data-placement="left" title="<?php echo __("Added On Favorite"); ?>" style="color: #4285f4; <?php echo $favoriteBtnAddedStyle; ?>"><i class="fas fa-check"></i></button>  
                            <button onclick="addVideoToPlayList(<?php echo $value['id']; ?>, true, <?php echo $value['favoriteId']; ?>);return false;" class="btn btn-dark btn-xs favoriteBtn favoriteBtn<?php echo $value['id']; ?>" data-toggle="tooltip" data-placement="left" title="<?php echo __("Favorite"); ?>" style="<?php echo $favoriteBtnStyle; ?>" ><i class="fas fa-heart" ></i></button>    

                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
                if (isToShowDuration($value['type'])) {
                    ?>
                    <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                    <div class="progress" style="height: 3px; margin-bottom: 2px;">
                        <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $value['progress']['percent'] ?>%;" aria-valuenow="<?php echo $value['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div> 
                    <?php
                }
                ?>
            </a>
            <a class="h6 galleryLink <?php echo $isserieClass; ?>" videos_id="<?php echo $value['id']; ?>" 
               href="<?php echo Video::getLink($value['id'], $value['clean_title'], false, $getCN); ?>"  
               embed="<?php echo Video::getLink($value['id'], $value['clean_title'], true, $getCN); ?>" title="<?php echo $value['title']; ?>">
                <h2><?php echo $value['title']; ?></h2>
            </a>

            <div class="text-muted galeryDetails" style="overflow: hidden;">
                <div class="galleryTags">
                    <!-- category tags -->
                    <?php
                    if (empty($_GET['catName']) && !empty($obj->showCategoryTag)) {
                        $iconClass = 'fas fa-folder';
                        if (!empty($value['iconClass'])) {
                            $iconClass = $value['iconClass'];
                        }
                        $icon = '<i class="' . $iconClass . '"></i>';
                        ?>
                        <a class="label label-default" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>" 
                           data-toggle="tooltip" title="<?php echo htmlentities($icon . ' ' . $value['category']); ?>"  data-html="true">
                               <?php
                               echo $icon;
                               ?>
                        </a>
                    <?php } ?>
                    <!-- plugins tags -->
                    <?php
                    @$timesG[__LINE__] += microtime(true) - $startG;
                    $startG = microtime(true);
                    if (!empty($obj->showTags)) {
                        echo implode('', Video::getTagsHTMLLabelArray($value['id']));
                    }
                    @$timesG[__LINE__] += microtime(true) - $startG;
                    $startG = microtime(true);
                    ?>
                </div>
                <?php
                if (empty($advancedCustom->doNotDisplayViews)) {
                    if (AVideoPlugin::isEnabledByName('LiveUsers')) {
                        echo getLiveUsersLabelVideo($value['id'], $value['views_count'], "", "");
                    } else {
                        ?>
                        <div>
                            <i class="fa fa-eye"></i>
                            <span itemprop="interactionCount">
                                <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                            </span>
                        </div>
                        <?php
                    }
                }
                ?>
                <div>
                    <i class="far fa-clock"></i>
                    <?php echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago'); ?>
                </div>
                <div>
                    <i class="fa fa-user"></i>
                    <a class="text-muted" href="<?php echo User::getChannelLink($value['users_id']); ?>">
                        <?php echo $name; ?>
                    </a>
                </div>
                <?php
                if ((!empty($value['description'])) && !empty($obj->Description)) {
                    //$desc = str_replace(array('"', "'", "#", "/", "\\"), array('``', "`", "", "", ""), preg_replace("/\r|\n/", " ", nl2br(trim($value['description']))));
                    $desc = nl2br(trim($value['description']));
                    if (!empty($desc)) {
                        $duid = uniqid();
                        $titleAlert = str_replace(array('"', "'"), array('``', "`"), $value['title']);
                        ?>
                        <div>
                            <a href="#" onclick='avideoAlert("<?php echo $titleAlert; ?>", "<div style=\"max-height: 300px; overflow-y: scroll;overflow-x: hidden;\" id=\"videoDescriptionAlertContent<?php echo $duid; ?>\" ></div>", "");$("#videoDescriptionAlertContent<?php echo $duid; ?>").html($("#videoDescription<?php echo $duid; ?>").html());return false;' class="text-primary" data-toggle="tooltip" title="<?php echo __("Description"); ?>"><i class="far fa-file-alt"></i> <span  class="hidden-sm hidden-xs"><?php echo __("Description"); ?></span></a>
                            <div id="videoDescription<?php echo $duid; ?>" style="display: none;"><?php echo $desc; ?></div>
                        </div>
                        <?php
                    }
                }
                ?>
                <?php if (Video::canEdit($value['id'])) { ?>
                    <div>
                        <a href="#" onclick="avideoModalIframe('<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $value['id']; ?>');return false;" class="text-primary" data-toggle="tooltip" title="<?php echo __("Edit Video"); ?>">
                            <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?php echo __("Edit Video"); ?></span>
                        </a>
                    </div>
                <?php }
                ?>
                <?php if (!empty($value['trailer1'])) { ?>
                    <div>
                        <span onclick="showTrailer('<?php echo parseVideos($value['trailer1'], 1); ?>'); return false;" class="text-primary cursorPointer" >
                            <i class="fa fa-video"></i> <?php echo __("Trailer"); ?>
                        </span>
                    </div>
                <?php }
                ?>
                <?php
                echo AVideoPlugin::getGalleryActionButton($value['id']);
                ?>
            </div>
            <?php
            @$timesG[__LINE__] += microtime(true) - $startG;
            $startG = microtime(true);
            if (CustomizeUser::canDownloadVideosFromVideo($value['id'])) {

                @$timesG[__LINE__] += microtime(true) - $startG;
                $startG = microtime(true);
                $files = getVideosURL($value['filename']);
                @$timesG[__LINE__] += microtime(true) - $startG;
                $startG = microtime(true);
                if (!empty($files['mp4']) || !empty($files['mp3'])) {
                    ?>

                    <div style="position: relative; overflow: visible; z-index: 3;" class="dropup">
                        <button type="button" class="btn btn-default btn-sm btn-xs btn-block"  data-toggle="dropdown">
                            <i class="fa fa-download"></i> <?php echo!empty($advancedCustom->uploadButtonDropdownText) ? $advancedCustom->uploadButtonDropdownText : ""; ?> <span class="caret"></span>
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
                                    <a href="<?php echo $theLink['url']; ?>?download=1&title=<?php echo urlencode($value['title'] . "_{$key}_.{$path_parts['extension']}"); ?>">
                                        <?php echo __("Download"); ?> <?php echo $key; ?>
                                    </a>
                                </li>
                            <?php }
                            ?>
                        </ul>
                    </div>
                    <?php
                }
            }
            @$timesG[__LINE__] += microtime(true) - $startG;
            $startG = microtime(true);
            //getLdJson($value['id']);
            //getItemprop($value['id']);
            ?>
        </div>

        <?php
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
    <!--
    createGallerySection
    <?php
    $timesG[__LINE__] = microtime(true) - $startG;
    $startG = microtime(true);
    foreach ($timesG as $key => $value) {
        echo "Line: {$key
        } -> {$value
        }\n";
    }
    ?>
    -->
    <?php
    unset($_POST['disableAddTo']);
    return $countCols;
}

function createGalleryLiveSection($videos) {
    global $global, $config, $obj, $advancedCustom, $advancedCustomUser;
    $countCols = 0;
    $obj = AVideoPlugin::getObjectData("Gallery");
    $liveobj = AVideoPlugin::getObjectData("Live");
    $zindex = 1000;
    $program = AVideoPlugin::loadPluginIfEnabled('PlayLists');
    foreach ($videos as $video) {
        $name = User::getNameIdentificationById($video['users_id']);
        $name .= " " . User::getEmailVerifiedIcon($video['users_id']);
        // make a row each 6 cols
        if ($countCols % $obj->screenColsLarge === 0) {
            echo '<div class="clearfix "></div>';
        }

        $countCols++;

        if (!empty($screenColsLarge)) {
            $obj->screenColsLarge = $screenColsLarge;
        }
        if (!empty($screenColsMedium)) {
            $obj->screenColsMedium = $screenColsMedium;
        }
        if (!empty($screenColsSmall)) {
            $obj->screenColsSmall = $screenColsSmall;
        }
        if (!empty($screenColsXSmall)) {
            $obj->screenColsXSmall = $screenColsXSmall;
        }
        $colsClass = "col-lg-" . (12 / $obj->screenColsLarge) . " col-md-" . (12 / $obj->screenColsMedium) . " col-sm-" . (12 / $obj->screenColsSmall) . " col-xs-" . (12 / $obj->screenColsXSmall);

        if (!empty($video['className'])) {
            $colsClass .= " {$video['className']}";
        }

        $liveNow = '<span class="label label-danger liveNow faa-flash faa-slow animated" style="position: absolute;
    bottom: 5px;
    right: 5px;">' . __("LIVE NOW") . '</span>';
        ?>
        <div class=" <?php echo $colsClass; ?> galleryVideo thumbsImage fixPadding" style="z-index: <?php echo $zindex--; ?>; min-height: 175px;">
            <a class="galleryLink" videos_id="<?php echo $video['id']; ?>" 
               href="<?php echo $video['href']; ?>"  
               embed="<?php echo $video['link']; ?>" title="<?php echo $video['title']; ?>">
                <div class="aspectRatio16_9">
                    <img src="<?php echo $video['poster']; ?>" alt="<?php echo $video['title'] ?>" class="thumbsJPG img img-responsive" id="thumbsJPG<?php echo $video['id']; ?>" />
                    <?php if (!empty($video['imgGif'])) { ?>
                        <img src="<?php echo getCDN(); ?>img/loading-gif.png" data-src="<?php echo $video['imgGif']; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $video['title']; ?>" id="thumbsGIF<?php echo $video['id']; ?>" class="thumbsGIF img-responsive " height="130" />
                        <?php
                    }
                    echo $liveNow;
                    ?>
                </div>
            </a>
            <a class="h6 galleryLink" videos_id="<?php echo $video['title']; ?>" 
               href="<?php echo $video['href']; ?>"  
               embed="<?php echo $video['link']; ?>" title="<?php echo $video['title']; ?>">
                <h2><?php echo $video['title'] ?></h2>
            </a>

            <div class="text-muted galeryDetails" style="overflow: hidden;">
                <div class="galleryTags">
                    <?php if (empty($_GET['catName']) && !empty($obj->showCategoryTag)) { ?>
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
                    <a class="text-muted" href="<?php echo User::getChannelLink($video['users_id']); ?>">
                        <?php echo $name; ?>
                    </a>
                </div>
                <?php
                if ((!empty($video['description'])) && !empty($obj->Description)) {
                    $desc = str_replace(array('"', "'", "#", "/", "\\"), array('``', "`", "", "", ""), preg_replace("/\r|\n/", " ", nl2br(trim($video['description']))));
                    if (!empty($desc)) {
                        $titleAlert = str_replace(array('"', "'"), array('``', "`"), $video['title']);
                        ?>
                        <div>
                            <a href="#" onclick='avideoAlert("<?php echo $titleAlert; ?>", "<div style=\"max-height: 300px; overflow-y: scroll;overflow-x: hidden;\"><?php echo $desc; ?></div>", "info");return false;' data-toggle="tooltip" title="<?php echo __("Description"); ?>"><i class="far fa-file-alt"></i> <span  class="hidden-sm hidden-xs"><?php echo __("Description"); ?></span></a>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>

        <?php
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
    if (!empty($video['galleryCallback'])) {
        $video['galleryCallback'] = addcslashes($video['galleryCallback'], '"');
        echo '<!-- galleryCallback --><script>$(document).ready(function () {eval("' . $video['galleryCallback'] . '")});</script>';
    }


    unset($_POST['disableAddTo']);
    return $countCols;
}

function createChannelItem($users_id, $photoURL = "", $identification = "", $rowCount = 12) {
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

function clearSearch() {
    global $search, $searchPhrase;
    $search = $_GET['search'];
    $searchPhrase = $_POST['searchPhrase'];
    unset($_GET['search']);
    unset($_POST['searchPhrase']);
}

function reloadSearch() {
    global $search, $searchPhrase;
    $_GET['search'] = $search;
    $_POST['searchPhrase'] = $searchPhrase;
}

function getTrendingVideos($rowCount = 12, $screenColsLarge = 0, $screenColsMedium = 0, $screenColsSmall = 0, $screenColsXSmall = 0) {
    global $global;
    $countCols = 0;
    unset($_POST['sort']);
    $_GET['sort']['trending'] = 1;
    $_REQUEST['current'] = getCurrentPage();
    $_REQUEST['rowCount'] = $rowCount;
    $videos = Video::getAllVideos("viewableNotUnlisted");
    // need to add dechex because some times it return an negative value and make it fails on javascript playlists
    echo "<link href=\"" . getCDN() . "plugin/Gallery/style.css\" rel=\"stylesheet\" type=\"text/css\"/><div class='row gallery '>";
    $countCols = createGallerySection($videos, "", array(), false, $screenColsLarge, $screenColsMedium, $screenColsSmall, $screenColsXSmall);
    echo "</div>";
    return $countCols;
}

function canPrintCategoryTitle($title) {
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
