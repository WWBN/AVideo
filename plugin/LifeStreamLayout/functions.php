<?php

function createLifeStreamLayout($title, $sort, $rowCount, $getName, $mostWord, $lessWord, $orderString, $defaultSort = "ASC") {
    if (!showThis($getName)) {
        return "";
    }
    $getName = str_replace(array("'",'"',"&quot;","&#039;"), array('','','',''), xss_esc($getName));
    if (!empty($_GET['showOnly'])) {
        $rowCount = 60;
    }
    global $global, $args, $url;
    $paggingId = uniqid();
    ?>
    <div class="clear clearfix">
        <h3 class="LifeStreamLayoutTitle">
            <a class="btn-default" href="<?php echo $global['webSiteRootURL']; ?>?showOnly=<?php echo $getName; ?>">
                <i class="glyphicon glyphicon-list-alt"></i>
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
        $countCols = 0;
        unset($_POST['sort']);
        $_POST['sort'][$sort] = $_GET[$getName];
        $_POST['current'] = $_GET['page'];
        $_POST['rowCount'] = $rowCount;

        $total = Video::getTotalVideos("viewable");
        $totalPages = ceil($total / $_POST['rowCount']);
        $page = $_GET['page'];
        if ($totalPages < $_GET['page']) {
            $page = $totalPages;
            $_POST['current'] = $totalPages;
        }
        $videos = Video::getAllVideos("viewable");
        // need to add dechex because some times it return an negative value and make it fails on javascript playlists
        createLifeStreamLayoutSection($videos, dechex(crc32($getName)));
        ?>
        <div class="col-sm-12" style="z-index: 1;">
            <ul id="<?php echo $paggingId; ?>">
            </ul>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#<?php echo $paggingId; ?>').bootpag({
                total: <?php echo $totalPages; ?>,
                page: <?php echo $page; ?>,
                maxVisible: 10
            }).on('page', function (event, num) {
    <?php echo 'var args = "' . $args . '";'; ?>
                window.location.replace("<?php echo $url; ?>" + num + args);
            });
        });
    </script>
    <?php
}


function createLifeStreamLayoutSection($videos, $crc = "", $get = array()) {
    global $global, $config, $obj, $advancedCustom;
    $countCols = 0;
    $obj = YouPHPTubePlugin::getObjectData("LifeStreamLayout");

    foreach ($videos as $value) {

        // that meas auto generate the channelName
        if (empty($get) && !empty($obj->filterUserChannel)) {
            $getCN = array('channelName' => $value['channelName'], 'catName' => @$_GET['catName']);
        }else{
            $getCN = $get;
        }

        $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
        $name = User::getNameIdentificationById($value['users_id']);
        // make a row each 6 cols
        if ($countCols % 4 === 0) {
            echo '</div><div class="row aligned-row ">';
        }

        $countCols ++;
        ?>
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 LifeStreamLayoutVideo thumbsImage fixPadding" style="z-index: 2; min-height: 175px;">
            <a class="LifeStreamLayoutLink" videos_id="<?php echo $value['id']; ?>" href="<?php echo Video::getLink($value['id'], $value['clean_title'], false, $getCN); ?>" title="<?php echo $value['title']; ?>">
                <?php
                $images = Video::getImageFromFilename($value['filename'], $value['type']);
                $imgGif = $images->thumbsGif;
                $poster = $images->thumbsJpg;
                ?>
                <div class="aspectRatio16_9">
                    <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>  <?php echo ($poster != $images->thumbsJpgSmall) ? "blur" : ""; ?>" id="thumbsJPG<?php echo $value['id']; ?>" />
                    <?php if (!empty($imgGif)) { ?>
                        <img src="<?php echo $global['webSiteRootURL']; ?>img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                    <?php } ?>
                </div>
                <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
            </a>
            <a class="h6 LifeStreamLayoutLink" videos_id="<?php echo $value['id']; ?>" href="<?php echo Video::getLink($value['id'], $value['clean_title'], false, $getCN); ?>" title="<?php echo $value['title']; ?>">
                <h2><?php echo $value['title']; ?></h2>
            </a>

            <div class="text-muted galeryDetails">
                <div>
                    <?php if (empty($_GET['catName'])) { ?>
                        <a class="label label-default" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>/">
                            <?php
                            if (!empty($value['iconClass'])) {
                                ?>
                                <i class="<?php echo $value['iconClass']; ?>"></i>
                                <?php
                            }
                            ?>
                            <?php echo $value['category']; ?>
                        </a>
                    <?php } ?>
                    <?php
                    if (!empty($obj->showTags)) {
                        $value['tags'] = Video::getTags($value['id']);
                        foreach ($value['tags'] as $value2) {
                            if (!empty($value2->label) && $value2->label === __("Group")) {
                                ?><span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span><?php
                            }
                        }
                    }
                    ?>
                </div>
                <div>
                    <i class="fa fa-eye"></i>
                    <span itemprop="interactionCount">
                        <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                    </span>
                </div>
                <div>
                    <i class="fa fa-clock-o"></i>
                    <?php echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago'); ?>
                </div>
                <div>
                    <i class="fa fa-user"></i>
                    <a class="text-muted" href="<?php echo User::getChannelLink($value['users_id']); ?>/">
                        <?php echo $name; ?>
                    </a>
                    <?php if ((!empty($value['description'])) && !empty($obj->Description)) { ?>
                        <button type="button" data-trigger="focus" class="label label-danger" data-toggle="popover" data-placement="top" data-html="true" title="<?php echo $value['title']; ?>" data-content="<div> <?php echo str_replace('"', '&quot;', nl2br(textToLink($value['description']))); ?> </div>" ><?php echo __("Description"); ?></button>
                    <?php } ?>
                </div>
                <?php if (Video::canEdit($value['id'])) { ?>
                    <div>
                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $value['id']; ?>" class="text-primary">
                            <i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?>
                        </a>
                    </div>
                <?php }
                ?>
                <div class="">
                    <?php if ((empty($_POST['disableAddTo'])) && (( ($advancedCustom != false) && ($advancedCustom->disableShareAndPlaylist == false)) || ($advancedCustom == false))) { ?>
                        <a href="#" class="text-primary" style="float:right;" id="addBtn<?php echo $value['id'] . $crc; ?>" data-placement="top" onclick="loadPlayLists('<?php echo $value['id'] . $crc; ?>', '<?php echo $value['id']; ?>');">
                            <span class="fa fa-plus"></span> <?php echo __("Add to"); ?>
                        </a>
                        <div class="webui-popover-content" >
                            <?php if (User::isLogged()) { ?>
                                <form role="form">
                                    <div class="form-group">
                                        <input class="form-control" id="searchinput<?php echo $value['id'] . $crc; ?>" type="search" placeholder="<?php echo __("Search"); ?>..." />
                                    </div>
                                    <div id="searchlist<?php echo $value['id'] . $crc; ?>" class="list-group">
                                    </div>
                                </form>
                                <div>
                                    <hr>
                                    <div class="form-group">
                                        <input id="playListName<?php echo $value['id'] . $crc; ?>" class="form-control" placeholder="<?php echo __("Create a New Play List"); ?>"  >
                                    </div>
                                    <div class="form-group">
                                        <?php echo __("Make it public"); ?>
                                        <div class="material-switch pull-right">
                                            <input id="publicPlayList<?php echo $value['id'] . $crc; ?>" name="publicPlayList" type="checkbox" checked="checked"/>
                                            <label for="publicPlayList" class="label-success"></label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-success btn-block" id="addPlayList<?php echo $value['id'] . $crc; ?>" ><?php echo __("Create a New Play List"); ?></button>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <h5><?php echo __("Want to watch this again later?"); ?></h5>
                                <?php echo __("Sign in to add this video to a playlist."); ?>
                                <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary">
                                    <span class="fas fa-sign-in-alt"></span>
                                    <?php echo __("Login"); ?>
                                </a>
                            <?php } ?>
                        </div>
                        <script>
                            $(document).ready(function () {
                                loadPlayLists('<?php echo $value['id'] . $crc; ?>', '<?php echo $value['id']; ?>');
                                $('#addBtn<?php echo $value['id'] . $crc; ?>').webuiPopover();
                                $('#addPlayList<?php echo $value['id'] . $crc; ?>').click(function () {
                                    modal.showPleaseWait();
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistAddNew.json.php',
                                        method: 'POST',
                                        data: {
                                            'videos_id': <?php echo $value['id']; ?>,
                                            'status': $('#publicPlayList<?php echo $value['id'] . $crc; ?>').is(":checked") ? "public" : "private",
                                            'name': $('#playListName<?php echo $value['id'] . $crc; ?>').val()
                                        },
                                        success: function (response) {
                                            if (response.status === "1") {
                                                playList = [];
                                                console.log(1);
                                                reloadPlayLists();
                                                loadPlayLists('<?php echo $value['id'] . $crc; ?>', '<?php echo $value['id']; ?>');
                                                //$('#searchlist<?php echo $value['id'] . $crc; ?>').btsListFilter('#searchinput<?php echo $value['id'] . $name; ?>', {itemChild: 'span'});
                                                $('#playListName<?php echo $value['id'] . $crc; ?>').val("");
                                                $('#publicPlayList<?php echo $value['id'] . $crc; ?>').prop('checked', true);
                                            }
                                            modal.hidePleaseWait();
                                        }
                                    });
                                    return false;
                                });
                            });
                        </script>
                    <?php } ?>
                </div>
                <?php
                if ($config->getAllow_download()) {
                    ?>

                <div style="position: relative; overflow: visible; z-index: 3;" class="dropup">
                        <button type="button" class="btn btn-default btn-sm btn-xs"  data-toggle="dropdown">
                            <i class="fa fa-download"></i> <?php echo!empty($advancedCustom->uploadButtonDropdownText) ? $advancedCustom->uploadButtonDropdownText : ""; ?> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">
                            <?php
                            $files = getVideosURL($value['filename']);
                            //var_dump($files);exit;
                            foreach ($files as $key => $theLink) {
                                if ($theLink['type'] !== 'video') {
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
                ?>


            </div>
        </div>
    <?php } ?>

    <?php
    unset($_POST['disableAddTo']);
}

function mkSubMainPage($catId) {
    global $global;
    unset($_GET['parentsOnly']);
    $subcats = Category::getChildCategories($catId);
    if (!empty($subcats)) {
        //echo "<ul style='margin-bottom: 0px; list-style-type: none;'>";
        foreach ($subcats as $subcat) {
            if (empty($subcat['total'])) {
                continue;
            }
            echo '<li class=" list-group-item ' . ($subcat['clean_name'] == @$_GET['catName'] ? "active" : "") . '">'
            . '&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $global['webSiteRootURL'] . 'cat/' . $subcat['clean_name'] . '" >'
            . '<span class="' . (empty($subcat['iconClass']) ? "fa fa-folder" : $subcat['iconClass']) . '"></span>  ' . $subcat['name'] . ' <span class="badge">' . $subcat['total'] . '</span></a></li>';
            mkSub($subcat['id']);
        }
        //echo "</ul>";
    }
}
?>
