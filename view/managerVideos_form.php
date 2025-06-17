<link href="<?php echo getURL('view/css/colorfulText.css'); ?>" rel="stylesheet" type="text/css" />
<style>
    .modal-content div.row>div {
        padding-bottom: 15px;
    }
    .krajee-default.file-preview-frame .kv-file-content{
        width: 100%;
    }
</style>
<div class="modal-dialog" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <div class="row">
                <div class="col-xs-7 col-sm-9">
                    <button type="button" class="btn btn-success btn-block saveVideoBtn"><i class="far fa-save"></i> <?php echo __("Save"); ?></button>
                </div>
                <div class="col-xs-3 col-sm-2">
                    <button type="button" class="btn btn-danger btn-block" onclick="confirmDeleteVideo($('#inputVideoId').val());"><i class="fas fa-trash"></i>
                        <span class="hidden-xs"><?php echo __("Delete"); ?></span>
                    </button>
                </div>
                <div class="col-xs-2 col-sm-1">
                    <button type="button" class="btn btn-default btn-block" data-dismiss="modal"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <!--
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            -->
        </div>
        <div class="modal-body">
            <div id="postersImage">
                <div class="panel panel-default">
                    <div class="panel-heading tabbable-line">
                        <div class="pull-right">
                            <div class="label label-success showIfIsVideo">Video</div>
                            <div class="label label-warning showIfIsArticle">Article</div>
                            <div class="label label-primary showIfIsEmbedLink">Link/Embed</div>
                        </div>
                        <ul class="nav nav-tabs">
                            <li class="active uploadFile hideIfIsArticle hideIfIsEmbedLink hideIfIsEditing"><a data-toggle="tab" href="#pmedia"><?php echo empty($advancedCustom->uploadMP4ButtonLabel) ? __("Direct upload") : __($advancedCustom->uploadMP4ButtonLabel); ?></a></li>
                            <li class=""><a data-toggle="tab" href="#pimages"><i class="far fa-image"></i> <?php echo __("Images"); ?></a></li>
                            <li class=""><a data-toggle="tab" href="#pmetadata"><i class="fas fa-info-circle"></i> <?php echo __("Meta Data"); ?></a></li>
                            <li class=""><a data-toggle="tab" href="#pSEO"><i class="fab fa-searchengin"></i> SEO</a></li>
                            <li class=""><a data-toggle="tab" href="#pPrivacy"><i class="fas fa-user-lock"></i> <?php echo __("Privacy"); ?></a></li>
                            <li class=""><a data-toggle="tab" href="#padvanced"><i class="fas fa-cog"></i> <?php echo __("Advanced"); ?></a></li>
                            <?php
                            echo AVideoPlugin::getManagerVideosTab();
                            ?>
                        </ul>
                    </div>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div id="pmedia" class="tab-pane fade in active hideIfIsArticle hideIfIsEmbedLink hideIfIsArticle hideIfIsEditing">
                                <form id="upload" method="post" action="<?php echo $global['webSiteRootURL'] . "view/mini-upload-form/upload.php"; ?>" enctype="multipart/form-data">
                                    <div id="drop">
                                        <a><?php echo __("Browse files"); ?></a>
                                        <input type="file" name="upl" />
                                        <input type="hidden" name="videos_id" id="fileUploadVideos_id" />
                                    </div>
                                    <ul>
                                        <!-- The file uploads will be shown here -->
                                    </ul>
                                </form>
                            </div>
                            <div id="pimages" class="tab-pane fade">
                                <div class="panel panel-default">
                                    <div class="panel-heading tabbable-line">
                                        <ul class="nav nav-tabs">
                                            <li class="active"><a data-toggle="tab" href="#jpg"><?php echo __("Poster"); ?></a></li>
                                            <li><a data-toggle="tab" href="#pjpg"><?php echo __("Portrait Poster"); ?></a></li>
                                            <li><a data-toggle="tab" href="#webp"><?php echo __("Mouse Over Poster (WebP)"); ?></a></li>
                                            <li><a data-toggle="tab" href="#gif"><?php echo __("Mouse Over Poster (GIF)"); ?></a></li>
                                            <li><a data-toggle="tab" href="#pgif"><?php echo __("Mouse Over Portrait Poster (GIF)"); ?></a></li>
                                        </ul>
                                    </div>
                                    <div class="panel-body">
                                        <div class="tab-content">
                                            <div id="jpg" class="tab-pane fade in active">
                                                <input id="input-jpg" type="file" class="file-loading" accept="image/jpg, .jpeg, .jpg, .png, .bmp">
                                            </div>
                                            <div id="pjpg" class="tab-pane fade">
                                                <input id="input-pjpg" type="file" class="file-loading" accept="image/jpg, .jpeg, .jpg, .png, .bmp">
                                            </div>
                                            <div id="webp" class="tab-pane fade">
                                                <input id="input-webp" type="file" class="file-loading" accept="image/webp, .webp">
                                            </div>
                                            <div id="gif" class="tab-pane fade">
                                                <input id="input-gif" type="file" class="file-loading" accept="image/gif, .gif">
                                            </div>
                                            <div id="pgif" class="tab-pane fade">
                                                <input id="input-pgif" type="file" class="file-loading" accept="image/gif, .gif">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="pmetadata" class="tab-pane fade">
                                <input type="hidden" id="inputVideoId">
                                <input type="hidden" id="videoLinkType">
                                <div id="videoLinkContent" class="showIfIsEmbedLink">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label class="control-label" for="videoLink"><?php echo __("Video Link"); ?></label>
                                            <input type="text" id="videoLink" class="form-control" placeholder="<?php echo __("Video Link"); ?> http://www.your-embed-link.com/video" required>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="control-label" for="epg_link"><?php echo __("EPG XML Link"); ?> </label>
                                            <input type="url" id="epg_link" class="form-control" placeholder="<?php echo __("EPG XML Link"); ?>">
                                            <small>
                                                <i class="far fa-question-circle"></i>
                                                You MUST use a <a href="http://wiki.xmltv.org/index.php/XMLTVFormat" target="_blank">XMLTV File format</a>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="titles">
                                    <div class="row">
                                        <?php
                                        $showCategory = empty($advancedCustomUser->userCanNotChangeCategory) || Permissions::canAdminVideos();
                                        $divCol1 = $divCol2 = 'col-sm-6';

                                        if (empty($showCategory)) {
                                            $divCol1 = 'col-sm-12';
                                            $divCol2 = 'hidden';
                                        }
                                        ?>
                                        <div class="<?php echo $divCol1; ?>">
                                            <label class="control-label" for="inputTitle"><?php echo __("Title"); ?></label>
                                            <input type="text" id="inputTitle" class="form-control" placeholder="<?php echo __("Title"); ?>" required>
                                        </div>
                                        <div class="<?php echo $divCol2; ?>">
                                            <?php
                                            if ($showCategory) {
                                                $parentsOnly = @$_GET['parentsOnly'];
                                                unset($_GET['parentsOnly']);
                                                //var_dump($categories);exit;
                                            ?>
                                                <label class="control-label" for="inputCategory"><?php echo __("Category"); ?></label>

                                                <?php
                                                echo Layout::getCategorySelect('inputCategory', '', 'inputCategory');
                                                $_GET['parentsOnly'] = $parentsOnly;
                                                ?>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <label class="control-label" for="inputDescription"><?php echo __("Description"); ?></label>
                                <textarea id="inputDescription" class="form-control" placeholder="<?php echo __("Description"); ?>" required></textarea>

                            </div>
                            <div id="pSEO" class="tab-pane fade ">
                                <div class="row">
                                    <div class="col-md-12 titles">
                                        <label class="control-label" for="inputCleanTitle"><?php echo __("Clean Title"); ?></label>
                                        <input type="text" id="inputCleanTitle" class="form-control" placeholder="<?php echo __("Clean Title"); ?>" required>
                                    </div>
                                </div>
                                <?php
                                echo AVideoPlugin::getManagerVideosEditField('SEO');
                                ?>
                            </div>
                            <div id="pPrivacy" class="tab-pane fade ">
                                <?php
                                echo AVideoPlugin::getManagerVideosEditField('Privacy');
                                ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <ul class="list-group">
                                            <?php
                                            if ($advancedCustomUser->userCanAllowFilesDownloadSelectPerVideo && CustomizeUser::canDownloadVideosFromUser(User::getId())) {
                                            ?>
                                                <li class="list-group-item">
                                                    <span class="fa fa-download"></span> <?php echo __("Allow Download This media"); ?>
                                                    <div class="material-switch pull-right">
                                                        <input id="can_download" type="checkbox" value="0" />
                                                        <label for="can_download" class="label-success"></label>
                                                    </div>
                                                </li>
                                            <?php
                                            }
                                            ?>
                                            <?php
                                            if ($advancedCustomUser->userCanAllowFilesShareSelectPerVideo && CustomizeUser::canShareVideosFromUser(User::getId())) {
                                            ?>
                                                <li class="list-group-item">
                                                    <span class="fa fa-share"></span> <?php echo __("Allow Share This media"); ?>
                                                    <div class="material-switch pull-right">
                                                        <input id="can_share" type="checkbox" value="0" />
                                                        <label for="can_share" class="label-success"></label>
                                                    </div>
                                                </li>
                                            <?php
                                            }
                                            ?>
                                            <li class="list-group-item">
                                                <div class="row">
                                                    <?php
                                                    $col = 12;
                                                    if (!empty($advancedCustomUser->userCanProtectVideosWithPassword) || Permissions::canAdminVideos()) {
                                                        $col = 6;
                                                    ?>
                                                        <div class="col-sm-6">
                                                            <label class="control-label" for="inputVideoPassword"><?php echo __("Password Protected"); ?></label>
                                                            <input type="text" id="inputVideoPassword" class="form-control" placeholder="<?php echo __("Password"); ?>">
                                                        </div>
                                                    <?php
                                                    }
                                                    ?>
                                                    <div class="col-sm-<?php echo $col; ?>">
                                                        <label for="videoStatus"><?php echo __('Video Status'); ?></label>
                                                        <select class="form-control last" id="videoStatus">
                                                            <!-- defaultVideoStatus <?php echo $advancedCustom->defaultVideoStatus->value; ?> -->
                                                            <?php
                                                            foreach ($statusThatTheUserCanUpdate as $value) {
                                                                $selected = '';
                                                                if($value[0] == $advancedCustom->defaultVideoStatus->value){
                                                                    $selected = 'selected';
                                                                }

                                                                echo "<option value=\"{$value[0]}\" {$selected}>" . __(Video::$statusDesc[$value[0]]) . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </li>
                                            <?php
                                            if (empty($advancedCustomUser->userCanNotChangeUserGroup) || Permissions::canAdminVideos()) {
                                                if ($advancedCustom->paidOnlyUsersTellWhatVideoIs || Permissions::canAdminVideos()) {
                                            ?>
                                                    <li class="list-group-item">
                                                        <i class="fas fa-money-check-alt"></i> <?php echo __("Only Paid Users Can see"); ?>
                                                        <div class="material-switch pull-right">
                                                            <input id="only_for_paid" type="checkbox" value="0" />
                                                            <label for="only_for_paid" class="label-success"></label>
                                                        </div>
                                                    </li>
                                                <?php }
                                                ?>
                                                <li class="list-group-item">
                                                    <span class="fa fa-globe"></span> <?php echo __("Public Media"); ?>
                                                    <div class="material-switch pull-right">
                                                        <input id="public" type="checkbox" value="0" class="userGroups" />
                                                        <label for="public" class="label-success"></label>
                                                    </div>
                                                </li>
                                                <li class="list-group-item active non-public">
                                                    <?php echo __("Groups that can see this video"); ?>
                                                    <a href="#" class="btn btn-info btn-xs pull-right" data-toggle="popover" title="<?php echo __("What is User Groups"); ?>" data-placement="bottom" data-content="<?php echo __("By linking groups to this video, it will no longer be public and only users in the same group will be able to watch this video"); ?>"><span class="fa fa-question-circle" aria-hidden="true"></span> <?php echo __("Help"); ?></a>
                                                </li>
                                                <?php
                                                $userGroups = UserGroups::getAllUsersGroups();
                                                foreach ($userGroups as $value) {
                                                ?>
                                                    <li class="list-group-item non-public groupSwitch" id="groupSwitch<?php echo $value['id']; ?>">
                                                        <span class="fa fa-lock"></span>
                                                        <?php echo $value['group_name']; ?>
                                                        <span class="label label-info"><?php echo $value['total_users'] . " " . __("Users linked"); ?></span>
                                                        <span class="label label-default categoryGroupSwitchInline"><?php echo __('Category User Group'); ?></span>
                                                        <div class="material-switch pull-right videoGroupSwitch">
                                                            <input id="videoGroup<?php echo $value['id']; ?>" type="checkbox" value="<?php echo $value['id']; ?>" class="videoGroups" />
                                                            <label for="videoGroup<?php echo $value['id']; ?>" class="label-warning"></label>
                                                        </div>
                                                        <div class="material-switch pull-right categoryGroupSwitch">
                                                            <input id="categoryGroup<?php echo $value['id']; ?>" type="checkbox" value="<?php echo $value['id']; ?>" class="categoryGroups" />
                                                            <label for="categoryGroup<?php echo $value['id']; ?>" class="label-default"></label>
                                                        </div>
                                                    </li>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                            <div id="padvanced" class="tab-pane fade ">

                                <?php
                                echo AVideoPlugin::getManagerVideosEditField('Advanced');
                                ?>
                                <div class="row">
                                    <div class="col-md-2 clearfix">
                                        <label class="control-label" for="madeForKids">
                                        <?php
                                            echo createColorfulTextSpans(__("Made for kids"));
                                        ?>
                                        </label>
                                        <br>
                                        <div class="material-switch">
                                            <input id="madeForKids" type="checkbox" value="">
                                            <label for="madeForKids" class="label-success"></label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="control-label" for="inputRrating">
                                            <?php echo __("R Rating"); ?>
                                        </label>
                                        <select class="form-control last" id="inputRrating">
                                            <?php
                                            foreach (Video::$rratingOptions as $value) {
                                                if (empty($value)) {
                                                    $label = __("Not Rated");
                                                } else {
                                                    $label = strtoupper($value);
                                                    $label = "[{$label}] " . Video::$rratingOptionsText[$value];
                                                }
                                                echo "<option value='{$value}'>" . __($label) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <?php
                                        include $global['systemRootPath'] . 'view/managerVideos_owner.php';
                                        ?>
                                    </div>

                                    <div class="col-md-2">
                                        <?php
                                        if (Permissions::canAdminVideos()) {
                                        ?>
                                            <div>
                                                <label class="control-label" for="views_count"><?php echo __("Video Views"); ?></label>
                                                <input type="number" step="1" id="views_count" class="form-control externalOptions">
                                            </div>
                                        <?php
                                        } else {
                                        ?><input type="hidden" id="views_count" value="-1"><?php
                                                                                        }
                                                                                            ?>
                                    </div>
                                </div>

                                <hr>
                                <div class="row" id="videoExtraDetails">

                                    <div class="col-md-6 hideIfIsArticle hideIfIsEmbedLink">

                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <?php echo __("Autoplay Next Video"); ?>
                                                <button class="btn btn-danger btn-sm btn-xs pull-right" id="removeAutoplay" type="button"><i class="fa fa-trash"></i> <?php echo __("Remove Autoplay Next Video"); ?></button>
                                            </div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <img id="inputNextVideo-poster" src="<?php echo ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL); ?>" class="ui-state-default" alt="">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input id="inputNextVideo" placeholder="<?php echo __("Autoplay Next Video"); ?>" class="form-control first" name="inputNextVideo">
                                                        <input id="inputNextVideoClean" placeholder="<?php echo __("Autoplay Next Video URL"); ?>" class="form-control last" readonly="readonly" name="inputNextVideoClean">
                                                        <input type="hidden" id="inputNextVideo-id">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 hideIfIsArticle">

                                        <label class="control-label" for="inputTrailer"><?php echo __("Embed URL for trailer"); ?></label>
                                        <input type="text" id="inputTrailer" class="form-control" placeholder="<?php echo __("Embed URL for trailer"); ?>" required>

                                    </div>
                                    <div class="col-md-3 hideIfIsArticle hideIfIsEmbedLink">
                                        <div>
                                            <label class="control-label" for="videoStartSecond"><?php echo __("Start video at"); ?></label>
                                            <input type="text" id="videoStartSeconds" class="form-control externalOptions" placeholder="00:00:00" value="00:00:00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3 hideIfIsArticle hideIfIsEmbedLink">
                                        <div>
                                            <label class="control-label" for="videoSkipIntroSecond"><?php echo __("Skip intro to"); ?></label>
                                            <input type="text" id="videoSkipIntroSecond" class="form-control externalOptions" placeholder="00:00:00" value="00:00:00" required>
                                        </div>
                                    </div>

                                </div>

                            </div>


                            <script>
                                $(function() {
                                    $("#inputNextVideo").autocomplete({
                                        minLength: 0,
                                        source: function(req, res) {
                                            $.ajax({
                                                url: webSiteRootURL + 'objects/videos.json.php?rowCount=6',
                                                type: "POST",
                                                data: {
                                                    searchPhrase: req.term
                                                },
                                                success: function(data) {
                                                    res(data.rows);
                                                }
                                            });
                                        },
                                        focus: function(event, ui) {
                                            $("#inputNextVideo").val(ui.item.title);
                                            return false;
                                        },
                                        select: function(event, ui) {
                                            $("#inputNextVideo").val(ui.item.title);
                                            $("#inputNextVideoClean").val(ui.item.link);
                                            $("#inputNextVideo-id").val(ui.item.id);
                                            $("#inputNextVideo-poster").attr("src", ui.item.videosURL.jpg.url);
                                            return false;
                                        }
                                    }).autocomplete("instance")._renderItem = function(ul, item) {
                                        return $("<li>").append("<div class='clearfix'><img class='img img-responsive pull-left' style='max-width: 90px;max-height: 35px; margin-right: 10px;' src='" + item.videosURL.jpg.url + "'/>[#" + item.id + "] " + item.title + "<br><?php echo __("Owner"); ?>: " + item.user + "</div>").appendTo(ul);
                                    };
                                });
                            </script>
                            <?php
                            echo AVideoPlugin::getManagerVideosBody();
                            ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="modal-footer">
            <div class="row">
                <div class="col-xs-7 col-sm-9">
                    <button type="button" class="btn btn-success btn-block saveVideoBtn"><i class="far fa-save"></i> <?php echo __("Save"); ?></button>
                </div>
                <div class="col-xs-3 col-sm-2">
                    <button type="button" class="btn btn-danger btn-block" onclick="confirmDeleteVideo($('#inputVideoId').val());"><i class="fas fa-trash"></i>
                        <span class="hidden-xs"><?php echo __("Delete"); ?></span>
                    </button>
                </div>
                <div class="col-xs-2 col-sm-1">
                    <button type="button" class="btn btn-default btn-block" data-dismiss="modal"><i class="fas fa-times"></i></button>
                </div>
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
