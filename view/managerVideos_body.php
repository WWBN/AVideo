<style>
    .bootgrid-table td {
        -ms-text-overflow: initial;
        -o-text-overflow: initial;
        text-overflow: initial;
    }
    .viewsDetails{
        color: #FFF;
    }

    .viewsDetails:hover{
        color: #AAF;
    }

    .progress-bar {
        -webkit-transition: width 2.5s ease;
        transition: width 2.5s ease;
    }
    .modal-dialog {
        width: 90%;
    }
    <?php
    if (!empty($_GET['iframe'])) {
        ?>
        body{
            padding: 0;   
        }
        footer{
            display: none;
        }
        <?php
    }
    ?>
</style>
<div class="container">
    <?php include $global['systemRootPath'] . 'view/include/updateCheck.php'; ?>
    <?php
    if (empty($_GET['iframe'])) {
        ?>
        <div class="panel panel-default">
            <div class="panel-body">

                <div class="btn-group" style="width: 100%;" >
                    <?php if (User::isAdmin()) { ?>
                        <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups" class="btn  btn-sm btn-xs btn-warning">
                            <span class="fa fa-users"></span> <?php echo __("User Groups"); ?>
                        </a>
                        <a href="<?php echo $global['webSiteRootURL']; ?>users" class="btn btn-sm btn-xs btn-primary">
                            <span class="fa fa-user"></span> <?php echo __("Users"); ?>
                        </a>
                    <?php } ?>
                    <a href="<?php echo $global['webSiteRootURL']; ?>charts" class="btn btn-sm btn-xs btn-info">
                        <span class="fa fa-bar-chart"></span>
                        <?php echo __("Video Chart"); ?>
                    </a>
                    <?php
                    if (User::isAdmin()) {
                        ?>
                        <a href="<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/" class="btn btn-sm btn-xs btn-danger">
                            <span class="far fa-money-bill-alt"></span> <?php echo __("Advertising Manager"); ?>
                        </a>
                        <?php
                    }
                    ?>
                    <?php
                    $categories = Category::getAllCategories(true);
                    if ((isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && $advancedCustomUser->onlyVerifiedEmailCanUpload && User::isVerified()) || (isset($advancedCustomUser->onlyVerifiedEmailCanUpload) && !$advancedCustomUser->onlyVerifiedEmailCanUpload) || !isset($advancedCustomUser->onlyVerifiedEmailCanUpload)) {
                        if (empty($advancedCustom->doNotShowEncoderButton)) {
                            if (!empty($config->getEncoderURL())) {
                                ?>
                                <form id="formEncoder" method="post" action="<?php echo $config->getEncoderURL(); ?>" target="encoder">
                                    <input type="hidden" name="webSiteRootURL" value="<?php echo $global['webSiteRootURL']; ?>" />
                                    <input type="hidden" name="user" value="<?php echo User::getUserName(); ?>" />
                                    <input type="hidden" name="pass" value="<?php echo User::getUserPass(); ?>" />
                                </form>
                                <a href="#" onclick="$('#formEncoder').submit();return false;" class="btn btn-sm btn-xs btn-default">
                                    <span class="fa fa-cog"></span> <?php echo __("Encode video and audio"); ?>
                                </a>
                                <?php
                            }
                        }
                        if (empty($advancedCustom->doNotShowUploadMP4Button)) {
                            ?>
                            <button class="btn btn-sm btn-xs btn-default" onclick="newVideo();" id="uploadMp4">
                                <span class="fa fa-upload"></span>
                                <?php echo __("Upload a File"); ?>
                            </button>
                            <?php
                        }
                        if (empty($advancedCustom->doNotShowEmbedButton)) {
                            ?>
                            <button class="btn btn-sm btn-xs btn-default" id="linkExternalVideo">
                                <span class="fa fa-link"></span>
                                <?php echo __("Embed a video link"); ?>
                            </button>
                            <?php
                        }
                        if (YouPHPTubePlugin::isEnabledByName("Articles")) {
                            ?>
                            <button class="btn btn-sm btn-xs btn-default" id="addArticle" onclick="newArticle()">
                                <i class="far fa-newspaper"></i>
                                <?php echo __("Add Article"); ?>
                            </button>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-body"><?php echo YouPHPTubePlugin::getVideoManagerButton(); ?></div>
        </div>
        <small class="text-muted clearfix">
            <?php
            $secondsTotal = getSecondsTotalVideosLength();
            $seconds = $secondsTotal % 60;
            $minutes = ($secondsTotal - $seconds) / 60;
            printf(__("You are hosting %d minutes and %d seconds of video"), $minutes, $seconds);
            ?>
        </small>
        <?php
        if (!empty($global['videoStorageLimitMinutes'])) {
            $secondsLimit = $global['videoStorageLimitMinutes'] * 60;
            if ($secondsLimit > $secondsTotal) {

                $percent = intval($secondsTotal / $secondsLimit * 100);
            } else {
                $percent = 100;
            }
            ?> and you have <?php echo $global['videoStorageLimitMinutes']; ?> minutes of storage
            <div class="progress">
                <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar"
                     aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%">
                    <?php echo $percent; ?>% of your storage limit used
                </div>
            </div>
            <?php
        }
    }
    ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="btn-group">
                <button class="btn btn-secondary" id="checkBtn">
                    <i class="far fa-square" aria-hidden="true" id="chk"></i>
                </button>
                <?php if (!$config->getDisable_youtubeupload()) { ?>
                    <button class="btn btn-danger" id="uploadYouTubeBtn">
                        <i class="fab fa-youtube" aria-hidden="true"></i> <?php echo __('Upload to YouTube'); ?>
                    </button>
                    <?php
                }
                if (empty($advancedCustomUser->userCanNotChangeCategory) || User::isAdmin()) {
                    ?>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            <?php echo __('Categories'); ?> <span class="caret"></span></button>
                        <ul class="dropdown-menu" role="menu">
                            <?php
                            foreach ($categories as $value) {
                                echo "<li><a href=\"#\"  onclick=\"changeCategory({$value['id']});return false;\" ><i class=\"{$value['iconClass']}\"></i> {$value['name']}</a></li>";
                            }
                            ?>
                        </ul>
                    </div>
                <?php } ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <?php echo __('Status'); ?> <span class="caret"></span></button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#" onclick="changeStatus('a'); return false;"><i class="fas fa-eye"></i> <?php echo __('Active'); ?></a></li>
                        <li><a href="#" onclick="changeStatus('i'); return false;"><i class="fas fa-eye-slash"></i></span> <?php echo __('Inactive'); ?></a></li>
                        <li><a href="#" onclick="changeStatus('u'); return false;"><i class="fas fa-eye" style="color: #BBB;"></i> <?php echo __('Unlisted'); ?></a></li>
                        <!--
                        <li><a href="#" onclick="changeStatus('p'); return false;"><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span> <?php echo __('Private'); ?></a></li>
                        -->
                    </ul>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <?php echo __('Add User Group'); ?> <span class="caret"></span></button>                        
                    <ul class="dropdown-menu" role="menu">
                        <?php
                        foreach ($userGroups as $value) {
                            ?>
                            <li>
                                <a href="#"  onclick="userGroupSave(<?php echo $value['id']; ?>, 1);return false;">
                                    <span class="fa fa-lock"></span>
                                    <span class="label label-info"><?php echo $value['total_users'] . " "; ?><?php echo __("Users linked"); ?></span>
                                    <?php echo $value['group_name']; ?>
                                </a>  
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <?php echo __('Remove User Group'); ?> <span class="caret"></span></button>                        
                    <ul class="dropdown-menu" role="menu">
                        <?php
                        foreach ($userGroups as $value) {
                            ?>
                            <li>
                                <a href="#"  onclick="userGroupSave(<?php echo $value['id']; ?>, 0);return false;">
                                    <span class="fa fa-lock"></span>
                                    <span class="label label-info"><?php echo $value['total_users'] . " " . __("Users linked"); ?></span>
                                    <?php echo $value['group_name']; ?>
                                </a>  
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
                <button class="btn btn-danger" id="deleteBtn">
                    <i class="fa fa-trash" aria-hidden="true"></i> <?php echo __('Delete'); ?>
                </button>
            </div>
            <table id="grid" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-formatter="checkbox" data-width="25px" ></th>
                        <th data-column-id="title" data-formatter="titleTag" ><?php echo __("Title"); ?></th>
                        <th data-column-id="tags" data-formatter="tags" data-sortable="false" data-width="210px"><?php echo __("Tags"); ?></th>
                        <th data-column-id="duration" data-width="100px"><?php echo __("Duration"); ?></th>
                        <th data-column-id="created" data-order="desc" data-width="100px"><?php echo __("Created"); ?></th>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false"  data-width="200px"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div id="videoFormModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo __("Video Form"); ?></h4>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: scroll;">
                    <div id="postersImage">
                        <ul class="nav nav-tabs">
                            <li class="active uploadFile"><a data-toggle="tab" href="#pmedia">Upload File</a></li>
                            <li><a data-toggle="tab" href="#pimages">Images</a></li>
                            <li><a data-toggle="tab" href="#pmetadata">Meta Data</a></li>
                            <?php
                            echo YouPHPTubePlugin::getManagerVideosTab();
                            ?>
                        </ul>

                        <div class="tab-content">
                            <div id="pmedia" class="tab-pane fade in active">
                                <form id="upload" method="post" action="<?php echo $global['webSiteRootURL'] . "view/mini-upload-form/upload.php"; ?>" enctype="multipart/form-data">
                                    <div id="drop">
                                        <?php echo __("Drop Here"); ?>

                                        <a><?php echo __("Browse"); ?></a>
                                        <input type="file" name="upl" />
                                        <input type="hidden" name="videos_id" id="fileUploadVideos_id" />
                                    </div>

                                    <ul>
                                        <!-- The file uploads will be shown here -->
                                    </ul>
                                </form>
                            </div>
                            <div id="pimages" class="tab-pane fade">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a data-toggle="tab" href="#jpg">Poster (JPG)</a></li>
                                    <li><a data-toggle="tab" href="#pjpg">Portrait Poster (JPG)</a></li>
                                    <li><a data-toggle="tab" href="#gif">Mouse Over Poster (GIF)</a></li>
                                    <li><a data-toggle="tab" href="#pgif">Mouse Over Portrait Poster (GIF)</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div id="jpg" class="tab-pane fade in active">
                                        <input id="input-jpg" type="file" class="file-loading" accept="image/jpg">
                                    </div>
                                    <div id="pjpg" class="tab-pane fade">
                                        <input id="input-pjpg" type="file" class="file-loading" accept="image/jpg">
                                    </div>
                                    <div id="gif" class="tab-pane fade">
                                        <input id="input-gif" type="file" class="file-loading" accept="image/gif">
                                    </div>
                                    <div id="pgif" class="tab-pane fade">
                                        <input id="input-pgif" type="file" class="file-loading" accept="image/gif">
                                    </div>
                                </div>
                            </div>
                            <div id="pmetadata" class="tab-pane fade">

                                <form class="form-compact"  id="updateCategoryForm" onsubmit="">
                                    <input type="hidden" id="inputVideoId"  >
                                    <div class="titles">
                                        <label for="inputTitle"><?php echo __("Title"); ?></label>
                                        <input type="text" id="inputTitle" class="form-control" placeholder="<?php echo __("Title"); ?>" required>
                                        <label for="inputCleanTitle" ><?php echo __("Clean Title"); ?></label>
                                        <input type="text" id="inputCleanTitle" class="form-control" placeholder="<?php echo __("Clean Title"); ?>" required>
                                    </div>
                                    <?php
                                    echo YouPHPTubePlugin::getManagerVideosEditField();
                                    ?>
                                    <label for="inputDescription" ><?php echo __("Description"); ?></label>
                                    <textarea id="inputDescription" class="form-control" placeholder="<?php echo __("Description"); ?>" required></textarea>
                                    <?php
                                    if (empty($advancedCustomUser->userCanNotChangeCategory) || User::isAdmin()) {
                                        ?>
                                        <label for="inputCategory" ><?php echo __("Category"); ?></label>
                                        <select class="form-control last" id="inputCategory" required>
                                            <?php
                                            foreach ($categories as $value) {
                                                echo "<option value='{$value['id']}'>{$value['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                        <?php
                                    }
                                    ?>

                                    <label for="inputRrating" ><?php echo __("R Rating"); ?></label>
                                    <select class="form-control last" id="inputRrating">
                                        <?php
                                        foreach (Video::$rratingOptions as $value) {
                                            if (empty($value)) {
                                                $label = "Not Rated";
                                            } else {
                                                $label = strtoupper($value);
                                            }
                                            echo "<option value='{$value}'>{$label}</option>";
                                        }
                                        ?>
                                    </select>

                                    <div class="row" <?php if (!User::isAdmin()) { ?> style="display: none;" <?php } ?>>
                                        <h3><?php echo __("Media Owner"); ?></h3>
                                        <div class="col-md-2">
                                            <img id="inputUserOwner-img" src="view/img/userSilhouette.jpg" class="img img-responsive img-circle" style="max-height: 60px;">
                                        </div>
                                        <div class="col-md-10">
                                            <input id="inputUserOwner" placeholder="<?php echo __("Media Owner"); ?>" class="form-control">
                                            <input type="hidden" id="inputUserOwner_id">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row" >
                                        <div class="col-md-12" >
                                            <ul class="list-group">
                                                <?php
                                                if ($advancedCustomUser->userCanAllowFilesDownloadSelectPerVideo && CustomizeUser::canDownloadVideosFromUser(User::getId())) {
                                                    ?>
                                                    <li class="list-group-item">
                                                        <span class="fa fa-download"></span> <?php echo __("Allow Download This media"); ?>
                                                        <div class="material-switch pull-right">
                                                            <input id="can_download" type="checkbox" value="0" class="userGroups"/>
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
                                                            <input id="can_share" type="checkbox" value="0" class="userGroups"/>
                                                            <label for="can_share" class="label-success"></label>
                                                        </div>
                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                                <?php
                                                if ($advancedCustom->paidOnlyUsersTellWhatVideoIs || User::isAdmin()) {
                                                    ?>
                                                    <li class="list-group-item">
                                                        <i class="fas fa-money-check-alt"></i> <?php echo __("Only Paid Users Can see"); ?>
                                                        <div class="material-switch pull-right">
                                                            <input id="only_for_paid" type="checkbox" value="0" class="userGroups"/>
                                                            <label for="only_for_paid" class="label-success"></label>
                                                        </div>
                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                                <li class="list-group-item">
                                                    <span class="fa fa-globe"></span> <?php echo __("Public Media"); ?>
                                                    <div class="material-switch pull-right">
                                                        <input id="public" type="checkbox" value="0" class="userGroups"/>
                                                        <label for="public" class="label-success"></label>
                                                    </div>
                                                </li>
                                                <li class="list-group-item active non-public">
                                                    <?php echo __("Groups that can see this video"); ?>
                                                    <a href="#" class="btn btn-info btn-xs pull-right" data-toggle="popover" title="<?php echo __("What is User Groups"); ?>" data-placement="bottom"  data-content="<?php echo __("By linking groups to this video, it will no longer be public and only users in the same group will be able to watch this video"); ?>"><span class="fa fa-question-circle" aria-hidden="true"></span> <?php echo __("Help"); ?></a>
                                                </li>
                                                <?php
                                                foreach ($userGroups as $value) {
                                                    ?>
                                                    <li class="list-group-item non-public">
                                                        <span class="fa fa-lock"></span>
                                                        <?php echo $value['group_name']; ?>
                                                        <span class="label label-info"><?php echo $value['total_users'] . " " . __("Users linked"); ?></span>
                                                        <div class="material-switch pull-right">
                                                            <input id="videoGroup<?php echo $value['id']; ?>" type="checkbox" value="<?php echo $value['id']; ?>" class="videoGroups"/>
                                                            <label for="videoGroup<?php echo $value['id']; ?>" class="label-warning"></label>
                                                        </div>
                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <div id="videoExtraDetails">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <?php echo __("Autoplay Next Video"); ?>
                                                <button class="btn btn-danger btn-sm btn-xs pull-right" id="removeAutoplay"><i class="fa fa-trash"></i> <?php echo __("Remove Autoplay Next Video"); ?></button>
                                            </div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <img id="inputNextVideo-poster" src="view/img/notfound.jpg" class="ui-state-default" alt="">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input id="inputNextVideo" placeholder="<?php echo __("Autoplay Next Video"); ?>" class="form-control first">
                                                        <input id="inputNextVideoClean" placeholder="<?php echo __("Autoplay Next Video URL"); ?>" class="form-control last" readonly="readonly">
                                                        <input type="hidden" id="inputNextVideo-id">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <label for="inputTrailer"><?php echo __("Embed code for trailer"); ?></label>
                                        <input type="text" id="inputTrailer" class="form-control" placeholder="<?php echo __("Embed code for trailer"); ?>" required>

                                        <div>
                                            <label for="videoStartSecond" ><?php echo __("Start video at:"); ?></label>
                                            <input type="text" id="videoStartSeconds" class="form-control externalOptions" placeholder="00:00:00" value="00:00:00" required>
                                        </div>
                                    </div>
                                    <script>
                                        $(function () {
                                            $("#inputNextVideo").autocomplete({
                                                minLength: 0,
                                                source: function (req, res) {
                                                    $.ajax({
                                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/videos.json.php',
                                                        type: "POST",
                                                        data: {
                                                            searchPhrase: req.term
                                                        },
                                                        success: function (data) {
                                                            res(data.rows);
                                                        }
                                                    });
                                                },
                                                focus: function (event, ui) {
                                                    $("#inputNextVideo").val(ui.item.title);
                                                    return false;
                                                },
                                                select: function (event, ui) {
                                                    $("#inputNextVideo").val(ui.item.title);
                                                    $("#inputNextVideoClean").val('<?php echo $global['webSiteRootURL']; ?>video/' + ui.item.clean_title);
                                                    $("#inputNextVideo-id").val(ui.item.id);
                                                    $("#inputNextVideo-poster").attr("src", "videos/" + ui.item.filename + ".jpg");
                                                    return false;
                                                }
                                            }).autocomplete("instance")._renderItem = function (ul, item) {
                                                return $("<li>").append("<div>" + item.title + "<br><?php echo __("Uploaded By"); ?>: " + item.user + "</div>").appendTo(ul);
                                            };

                                            $("#inputUserOwner").autocomplete({
                                                minLength: 0,
                                                source: function (req, res) {
                                                    $.ajax({
                                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/users.json.php',
                                                        type: "POST",
                                                        data: {
                                                            searchPhrase: req.term
                                                        },
                                                        success: function (data) {
                                                            res(data.rows);
                                                        }
                                                    });
                                                },
                                                focus: function (event, ui) {
                                                    $("#inputUserOwner").val(ui.item.user);
                                                    return false;
                                                },
                                                select: function (event, ui) {
                                                    console.log(ui.item);
                                                    $("#inputUserOwner").val(ui.item.user);
                                                    $("#inputUserOwner_id").val(ui.item.id);
                                                    var photoURL = '<?php echo $global['webSiteRootURL']; ?>img/userSilhouette.jpg'
                                                    if (ui.item.photoURL) {
                                                        photoURL = '<?php echo $global['webSiteRootURL']; ?>' + ui.item.photoURL + '?rand=' + Math.random();
                                                    }
                                                    $("#inputUserOwner-img").attr("src", photoURL);
                                                    return false;
                                                }
                                            }).autocomplete("instance")._renderItem = function (ul, item) {
                                                return $("<li>").append("<div>" + item.name + "<br>" + item.email + "<br>" + item.user + "</div>").appendTo(ul);
                                            };
                                        });
                                    </script>

                                </form>
                            </div>

                            <?php
                            echo YouPHPTubePlugin::getManagerVideosBody();
                            ?>
                        </div>
                    </div>
                    <div id="videoLinkContent">
                        <label for="videoLink" ><?php echo __("Video Link"); ?></label>
                        <input type="text" id="videoLink" class="form-control" placeholder="<?php echo __("Video Link"); ?> http://www.your-embed-link.com/video" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                    <button type="button" class="btn btn-primary" id="saveVideoBtn"><?php echo __("Save changes"); ?></button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <div id="videoViewFormModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo __("Video Views"); ?></h4>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: scroll;">
                    <div class="progress" id="progress25" style="width: 100%;">
                        <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar"
                             aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:0">
                            0%
                        </div>
                    </div>
                    <div class="progress" id="progress50" style="width: 100%;">
                        <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0">
                            0%
                        </div>
                    </div>
                    <div class="progress" id="progress75" style="width: 100%;">
                        <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0">
                            0%
                        </div>
                    </div>
                    <div class="progress" id="progress100" style="width: 100%;">
                        <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0">
                            0%
                        </div>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <div class="btn-group pull-right" role="group">
        <a href="<?php echo $global['webSiteRootURL']; ?>objects/videos.txt.php?type=seo" target="_blank" class="btn btn-default btn-sm">
            <i class="fas fa-download"></i> <?php echo __("Download your videos list"); ?> (SEO .txt file)
        </a>
        <a href="<?php echo $global['webSiteRootURL']; ?>objects/videos.txt.php" target="_blank" class="btn btn-default btn-sm">
            <i class="fas fa-download"></i> <?php echo __("Download your videos list"); ?> (Permalink .txt file)
        </a>
    </div>
    <?php
    if ((User::isAdmin()) && (!$config->getDisable_youtubeupload())) {
        ?>
        <div class="alert alert-info">
            <h1><span class="fab fa-youtube-square"></span> Let us upload your video to YouTube</h1>
            <h2>Before you start</h2>
            <ol>
                <li>
                    <a href="<?php echo $global['webSiteRootURL']; ?>siteConfigurations" class="btn btn-info btn-xs">Enable Google Login</a> and get your google ID and Key
                </li>
                <li>
                    Go to https://console.developers.google.com
                    on <a href="https://console.developers.google.com/apis/dashboard" class="btn btn-info btn-xs" target="_blank">dashboard</a> Enable <strong>YouTube Data API v3</strong>
                </li>
                <li>
                    In credentials authorized this redirect URIs <code><?php echo $global['webSiteRootURL']; ?>objects/youtubeUpload.json.php</code>
                </li>
                <li>
                    You can find more help on <a href="https://developers.google.com/youtube/v3/getting-started" class="btn btn-info btn-xs"  target="_blank">https://developers.google.com/youtube/v3/getting-started </a>
                </li>
            </ol>

        </div>
        <?php
    }
    ?>
</div><!--/.container-->

<script src="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>

<!-- JavaScript Includes -->
<script src="<?php echo $global['webSiteRootURL']; ?>view/mini-upload-form/assets/js/jquery.knob.js"></script>

<!-- jQuery File Upload Dependencies -->
<script src="<?php echo $global['webSiteRootURL']; ?>view/mini-upload-form/assets/js/jquery.ui.widget.js"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>view/mini-upload-form/assets/js/jquery.iframe-transport.js"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>view/mini-upload-form/assets/js/jquery.fileupload.js"></script>
<?php
echo YouPHPTubePlugin::getManagerVideosJavaScripts();
if (empty($advancedCustom->disableHTMLDescription)) {
    ?>
    <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/js/tinymce/tinymce.min.js"></script>
    <script>
                                            tinymce.init({
                                                selector: '#inputDescription', // change this value according to your HTML
                                                plugins: 'code print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern help ',
                                                //toolbar: 'fullscreen | formatselect | bold italic strikethrough forecolor backcolor permanentpen formatpainter | link image media pageembed | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | addcomment',
                                                toolbar: 'fullscreen | formatselect | bold italic strikethrough | link image media pageembed | numlist bullist | removeformat | addcomment',
                                                height: 400,
                                                convert_urls: false,
                                                images_upload_handler: function (blobInfo, success, failure) {
                                                    var xhr, formData;
                                                    if (!videos_id) {
                                                        $('#inputTitle').val("Article automatically booked");
                                                        saveVideo(false);
                                                    }
                                                    xhr = new XMLHttpRequest();
                                                    xhr.withCredentials = false;
                                                    xhr.open('POST', '<?php echo $global['webSiteRootURL']; ?>objects/uploadArticleImage.php?video_id=' + videos_id);

                                                    xhr.onload = function () {
                                                        var json;

                                                        if (xhr.status != 200) {
                                                            failure('HTTP Error: ' + xhr.status);
                                                            return;
                                                        }

                                                        json = xhr.responseText;
                                                        json = JSON.parse(json);
                                                        console.log(json);
                                                        if (json.error === false && json.url) {
                                                            success(json.url);
                                                        } else if (json.msg) {
                                                            swal("<?php echo __("Error!"); ?>", json.msg, "error");
                                                        } else {
                                                            swal("<?php echo __("Error!"); ?>", "<?php echo __("Unknown Error!"); ?>", "error");
                                                        }

                                                    };

                                                    formData = new FormData();
                                                    formData.append('file_data', blobInfo.blob(), blobInfo.filename());

                                                    xhr.send(formData);
                                                }
                                            });
    </script>
    <?php
}
?>
<script>
    var timeOut;
    var encodingNowId = "";
    var waitToSubmit = true;
    // make sure the video was uploaded, delete in case it was not uploaded
    var videoUploaded = false;
    var videos_id = <?php echo intval(@$_GET['video_id']); ?>;
    var isArticle = 0;

    function saveVideoOnPlaylist(videos_id, add, playlists_id) {
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/playListAddVideo.json.php',
            method: 'POST',
            data: {
                'videos_id': videos_id,
                'add': add,
                'playlists_id': playlists_id
            },
            success: function (response) {
                modal.hidePleaseWait();
            }
        });
    }

    function changeStatus(status) {
        modal.showPleaseWait();
        var vals = [];
        $(".checkboxVideo").each(function (index) {
            if ($(this).is(":checked")) {
                vals.push($(this).val());
            }
        });
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/videoStatus.json.php',
            data: {"id": vals, "status": status},
            type: 'post',
            success: function (response) {
                console.log(response);
                modal.hidePleaseWait();
                if (!response.status) {
                    swal({
                        title: "<?php echo __("Sorry!"); ?>",
                        text: response.msg,
                        type: "error",
                        html: true
                    });
                } else {
                    $("#grid").bootgrid('reload');
                }
            }
        });
    }
    function changeCategory(category_id) {
        modal.showPleaseWait();
        var vals = [];
        $(".checkboxVideo").each(function (index) {
            if ($(this).is(":checked")) {
                vals.push($(this).val());
            }
        });
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/videoCategory.json.php',
            data: {"id": vals, "category_id": category_id},
            type: 'post',
            success: function (response) {
                console.log(response);
                modal.hidePleaseWait();
                if (!response.status) {
                    swal({
                        title: "<?php echo __("Sorry!"); ?>",
                        text: response.msg,
                        type: "error",
                        html: true
                    });
                } else {
                    $("#grid").bootgrid('reload');
                }
            }
        });
    }


    function userGroupSave(users_groups_id, add) {
        modal.showPleaseWait();
        var vals = [];
        $(".checkboxVideo").each(function (index) {
            if ($(this).is(":checked")) {
                vals.push($(this).val());
            }
        });
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/userGroupSave.json.php',
            data: {"id": vals, "users_groups_id": users_groups_id, "add": add},
            type: 'post',
            success: function (response) {
                console.log(response);
                modal.hidePleaseWait();
                if (!response.status) {
                    swal({
                        title: "<?php echo __("Sorry!"); ?>",
                        text: response.msg,
                        type: "error",
                        html: true
                    });
                } else {
                    $("#grid").bootgrid('reload');
                }
            }
        });
    }

    function checkProgress() {
        $.ajax({
            url: '<?php echo $config->getEncoderURL(); ?>status',
            success: function (response) {
                if (response.queue_list.length) {
                    for (i = 0; i < response.queue_list.length; i++) {
                        if ('<?php echo $global['webSiteRootURL']; ?>' !== response.queue_list[i].streamer_site) {
                            continue;
                        }
                        createQueueItem(response.queue_list[i], i);
                    }

                }
                if (response.encoding && '<?php echo $global['webSiteRootURL']; ?>' === response.encoding.streamer_site) {
                    var id = response.encoding.id;
                    // if start encode next before get 100%
                    if (id !== encodingNowId) {
                        $("#encodeProgress" + encodingNowId).slideUp("normal", function () {
                            $(this).remove();
                        });
                        encodingNowId = id;
                    }

                    $("#downloadProgress" + id).slideDown();
                    if (response.download_status && !response.encoding_status.progress) {
                        $("#encodingProgress" + id).find('.progress-completed').html("<strong>" + response.encoding.name + " [Downloading ...] </strong> " + response.download_status.progress + '%');
                    } else {
                        $("#encodingProgress" + id).find('.progress-completed').html("<strong>" + response.encoding.name + "[" + response.encoding_status.from + " to " + response.encoding_status.to + "] </strong> " + response.encoding_status.progress + '%');
                        $("#encodingProgress" + id).find('.progress-bar').css({'width': response.encoding_status.progress + '%'});
                    }
                    if (response.download_status) {
                        $("#downloadProgress" + id).find('.progress-bar').css({'width': response.download_status.progress + '%'});
                    }
                    if (response.encoding_status.progress >= 100) {
                        $("#encodingProgress" + id).find('.progress-bar').css({'width': '100%'});
                        clearTimeout(timeOut);
                        timeOut = setTimeout(function () {
                            $("#grid").bootgrid('reload');
                        }, 5000);
                    } else {

                    }

                    setTimeout(function () {
                        checkProgress();
                    }, 3000);
                } else if (encodingNowId !== "") {
                    $("#encodeProgress" + encodingNowId).slideUp("normal", function () {
                        $(this).remove();
                    });
                    encodingNowId = "";
                    setTimeout(function () {
                        checkProgress();
                    }, 10000);
                } else {
                    setTimeout(function () {
                        checkProgress();
                    }, 10000);
                }

            }
        });
    }

    function deleteVideo(videos_id) {
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/videoDelete.json.php',
            data: {"id": videos_id},
            type: 'post',
            success: function (response) {
                if (response.status === "1") {
                    $("#grid").bootgrid("reload");
                } else if (response.status === "") {
                    $("#grid").bootgrid("reload");
                } else {
                    swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your video has NOT been deleted!"); ?>", "error");
                }
                modal.hidePleaseWait();
            }
        });
    }

    function editVideo(row) {
        console.log(row.id);
        if (!row.id) {
            row.id = videos_id;
        }

        $(".externalOptions").val("");
        try {
            externalOptionsObject = JSON.parse(row.externalOptions);
            for (var key in externalOptionsObject) {
                if (externalOptionsObject.hasOwnProperty(key)) {
                    $('#' + key).val(externalOptionsObject[key]);
                }
            }
        } catch (e) {

        }

        $('.uploadFile').hide();
        $('.nav-tabs a[href="#pmetadata"]').tab('show');
        waitToSubmit = true;
        $('#postersImage, #videoIsAdControl, .titles, #videoExtraDetails').slideDown();
        if (row.type === 'article') {
            isArticle = 1;
            $('.nav-tabs a[href="#pmedia"], #pmedia').hide();
            $('.nav-tabs a[href="#pmetadata"]').tab('show');
            reloadFileInput();
            $('#videoIsAdControl, #videoExtraDetails, #videoLinkContent').slideUp();
            $('#postersImage').slideDown();
        } else {
            isArticle = 0;
            if ((row.type === 'embed') || (row.type === 'linkVideo') || (row.type === 'linkAudio')) {
                $('#videoLink').val(row.videoLink);
                $('#videoLinkType').val(row.type);
            } else {
                $('#videoLinkContent').slideUp();
            }
        }


        $('#inputVideoId').val(row.id);
        $('#inputTitle').val(row.title);
        $('#inputTrailer').val(row.trailer1);
        $('#inputCleanTitle').val(row.clean_title);
        $('#inputDescription').val(row.description);
<?php
if (empty($advancedCustom->disableHTMLDescription)) {
    ?>
            tinymce.get('inputDescription').setContent(row.description);
    <?php
}
?>
        $('#inputCategory').val(row.categories_id);
        $('#inputRrating').val(row.rrating);
<?php
echo YouPHPTubePlugin::getManagerVideosEdit();
?>

        if (row.next_id) {
            $('#inputNextVideo-poster').attr('src', "<?php echo $global['webSiteRootURL']; ?>videos/" + row.next_filename + ".jpg");
            $('#inputNextVideo').val(row.next_title);
            $('#inputNextVideoClean').val("<?php echo $global['webSiteRootURL']; ?>video/" + row.next_clean_title);
            $('#inputNextVideo-id').val(row.next_id);
        }
        if (row.next_video && row.next_video.id) {
            $('#inputNextVideo-poster').attr('src', "<?php echo $global['webSiteRootURL']; ?>videos/" + row.next_video.filename + ".jpg");
            $('#inputNextVideo').val(row.next_video.title);
            $('#inputNextVideoClean').val("<?php echo $global['webSiteRootURL']; ?>video/" + row.next_video.clean_title);
            $('#inputNextVideo-id').val(row.next_video.id);
        } else {
            $('#removeAutoplay').trigger('click');
        }


        var photoURL = '<?php echo $global['webSiteRootURL']; ?>img/userSilhouette.jpg'
        if (row.photoURL) {
            photoURL = '<?php echo $global['webSiteRootURL']; ?>' + row.photoURL + '?rand=' + Math.random();
        }
        $("#inputUserOwner-img").attr("src", photoURL);
        $('#inputUserOwner').val(row.user);
        $('#inputUserOwner_id').val(row.users_id);

        $('.videoGroups').prop('checked', false);
        if (row.groups.length === 0) {
            $('#public').prop('checked', true);
        } else {
            $('#public').prop('checked', false);
            for (var index in row.groups) {
                $('#videoGroup' + row.groups[index].id).prop('checked', true);
            }
        }

        if (row.can_download) {
            $('#can_download').prop('checked', true);
        } else {
            $('#can_download').prop('checked', false);
        }

        if (row.can_share) {
            $('#can_share').prop('checked', true);
        } else {
            $('#can_share').prop('checked', false);
        }

        if (row.only_for_paid) {
            $('#only_for_paid').prop('checked', true);
        } else {
            $('#only_for_paid').prop('checked', false);
        }

        if (row.only_for_paid) {
            $('#only_for_paid').prop('checked', true);
        } else {
            $('#only_for_paid').prop('checked', false);
        }

        $('#public').trigger("change");
        $('#videoIsAd').prop('checked', false);
        $('#videoIsAd').trigger("change");
        reloadFileInput(row);
        $('#input-jpg, #input-gif,#input-pjpg, #input-pgif').on('fileuploaded', function (event, data, previewId, index) {
            $("#grid").bootgrid("reload");
        })
        waitToSubmit = true;
        setTimeout(function () {
            waitToSubmit = false;
        }, 3000);
        $('#videoFormModal').modal();
        videoUploaded = true;
    }

    function reloadFileInput(row) {
        if (!row || typeof row === 'undefined') {
            row = {id: 0, filename: "filename", clean_title: "blank"};
        }
        console.log(row);
        console.log(videos_id);
        if (!row.id && videos_id) {
            row.id = videos_id;
        }
        if (!row.id) {
            setTimeout(function () {
                reloadFileInput(row);
            }, 500);
            return false;
        }
        $('#input-jpg, #input-gif, #input-pjpg, #input-pgif').fileinput('destroy');
        $("#input-jpg").fileinput({
            uploadUrl: "<?php echo $global['webSiteRootURL']; ?>objects/uploadPoster.php?video_id=" + row.id + "&type=jpg",
            autoReplace: true,
            overwriteInitial: true,
            showUploadedThumbs: false,
            maxFileCount: 1,
            initialPreview: [
                "<img style='height:160px' src='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + ".jpg'>",
            ],
            initialCaption: row.clean_title + '.jpg',
            initialPreviewShowDelete: false,
            showRemove: false,
            showClose: false,
            layoutTemplates: {actionDelete: ''}, // disable thumbnail deletion
            allowedFileExtensions: ["jpg", "jpeg"]
        });
        $("#input-pjpg").fileinput({
            uploadUrl: "<?php echo $global['webSiteRootURL']; ?>objects/uploadPoster.php?video_id=" + row.id + "&type=pjpg",
            autoReplace: true,
            overwriteInitial: true,
            showUploadedThumbs: false,
            maxFileCount: 1,
            initialPreview: [
                "<img style='height:160px' src='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + "_portrait.jpg'>",
            ],
            initialCaption: row.clean_title + '_portrait.jpg',
            initialPreviewShowDelete: false,
            showRemove: false,
            showClose: false,
            layoutTemplates: {actionDelete: ''}, // disable thumbnail deletion
            allowedFileExtensions: ["jpg", "jpeg"]
        });
        $("#input-gif").fileinput({
            uploadUrl: "<?php echo $global['webSiteRootURL']; ?>objects/uploadPoster.php?video_id=" + row.id + "&type=gif",
            autoReplace: true,
            overwriteInitial: true,
            showUploadedThumbs: false,
            maxFileCount: 1,
            initialPreview: [
                "<img style='height:160px' src='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + ".gif'>",
            ],
            initialCaption: row.clean_title + '.gif',
            initialPreviewShowDelete: false,
            showRemove: false,
            showClose: false,
            layoutTemplates: {actionDelete: ''}, // disable thumbnail deletion
            allowedFileExtensions: ["gif"]
        });
        $("#input-pgif").fileinput({
            uploadUrl: "<?php echo $global['webSiteRootURL']; ?>objects/uploadPoster.php?video_id=" + row.id + "&type=pgif",
            autoReplace: true,
            overwriteInitial: true,
            showUploadedThumbs: false,
            maxFileCount: 1,
            initialPreview: [
                "<img style='height:160px' src='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + "_portrait.gif'>",
            ],
            initialCaption: row.clean_title + '_portrait.gif',
            initialPreviewShowDelete: false,
            showRemove: false,
            showClose: false,
            layoutTemplates: {actionDelete: ''}, // disable thumbnail deletion
            allowedFileExtensions: ["gif"]
        });
    }

    function saveVideo(closeModal) {
        if (waitToSubmit) {
            return false;
        }
        waitToSubmit = true;
        var isPublic = $('#public').is(':checked');
        var selectedVideoGroups = [];
        $('.videoGroups:checked').each(function () {
            selectedVideoGroups.push($(this).val());
        });
        if (!isPublic && selectedVideoGroups.length === 0) {
            isPublic = true;
        }
        if (isPublic) {
            selectedVideoGroups = [];
        }
        modal.showPleaseWait();

        var externalOptionsObject = {};
        $('.externalOptions').each(function (i, obj) {
            var name = $(this).attr('id');
            eval('externalOptionsObject.' + name + '="' + $(this).val() + '"');
        });

        var externalOptions = JSON.stringify(externalOptionsObject);

        $.ajax({
        url: '<?php echo $global['webSiteRootURL']; ?>objects/videoAddNew.json.php',
                data: {
                "externalOptions":externalOptions,
<?php
echo YouPHPTubePlugin::getManagerVideosAddNew();
?>
                "id": $('#inputVideoId').val(),
                        "title": $('#inputTitle').val(),
                        "trailer1": $('#inputTrailer').val(),
                        "videoLink": $('#videoLink').val(),
                        "videoLinkType": $('#videoLinkType').val(),
                        "clean_title": $('#inputCleanTitle').val(),
<?php
if (empty($advancedCustom->disableHTMLDescription)) {
    ?>
                    "description": tinymce.get('inputDescription').getContent(),
<?php } else { ?>
                    "description": $('#inputDescription').val(),
<?php } ?>
                "categories_id": $('#inputCategory').val(),
                        "rrating": $('#inputRrating').val(),
                        "public": isPublic,
                        "videoGroups": selectedVideoGroups,
                        "next_videos_id": $('#inputNextVideo-id').val(),
                        "users_id": $('#inputUserOwner_id').val(),
                        "can_download": $('#can_download').is(':checked'),
                        "can_share": $('#can_share').is(':checked'),
                        "isArticle": isArticle,
                        "only_for_paid": $('#only_for_paid').is(':checked')
                },
                type: 'post',
                success: function (response) {
                    if (response.status === "1" || response.status === true) {
                        if (response.video.id) {
                            videos_id = response.video.id;
                        }
                        if (response.video.type === 'embed' || response.video.type === 'linkVideo' || response.video.type === 'article') {
                            videoUploaded = true;
                        }
                        if (closeModal && videoUploaded) {
                            $('#videoFormModal').modal('hide');
                        }
                        $("#grid").bootgrid("reload");
                        $('#fileUploadVideos_id').val(response.videos_id);
                        $('#inputVideoId').val(response.videos_id);
                        videos_id = response.videos_id;
                    } else {
                        if (response.error) {
                            swal("<?php echo __("Sorry!"); ?>", response.error, "error");
                        } else {
                            swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your video has NOT been saved!"); ?>", "error");
                        }
                    }
                    modal.hidePleaseWait();
                    setTimeout(function () {
                        waitToSubmit = false;
                    }, 3000);
                    }
        });
                return false;
    }

    function resetVideoForm() {
        isArticle = 0;
        $('.nav-tabs a[href="#pmedia"], #pmedia').show();
        $("#pmedia").css("display", "");
        $("#pmedia").attr("style", "");
        $('.nav-tabs a[href="#pmedia"]').tab('show');
        $('#postersImage, #videoIsAdControl, .titles, #videoExtraDetails').slideDown();
        $('#videoLinkContent').slideUp();
        $('#inputVideoId').val(0);
        $('#inputTitle').val("");
        $('#inputTrailer').val("");
        $('#inputCleanTitle').val("");
        $('#inputDescription').val("");
<?php
if (empty($advancedCustom->disableHTMLDescription)) {
    ?>
            tinymce.get('inputDescription').setContent("");
<?php } ?>
        $('#inputCategory').val("");
        $('#inputRrating').val("");
        $('#removeAutoplay').trigger('click');

<?php
echo YouPHPTubePlugin::getManagerVideosReset();
?>
        var photoURL = '<?php echo User::getPhoto(); ?>';
        $("#inputUserOwner-img").attr("src", photoURL);
        $('#inputUserOwner').val('<?php echo User::getUserName(); ?>');
        $('#inputUserOwner_id').val(<?php echo User::getId(); ?>);

        $('.videoGroups').prop('checked', false);
        $('#can_download').prop('checked', false);
        $('#can_share').prop('checked', false);
        $('#only_for_paid').prop('checked', false);
        $('#public').prop('checked', true);
        $('#public').trigger("change");
        $('#videoIsAd').prop('checked', false);
        $('#videoIsAd').trigger("change");
        reloadFileInput();
        $('#input-jpg, #input-gif,#input-pjpg, #input-pgif').on('fileuploaded', function (event, data, previewId, index) {
            $("#grid").bootgrid("reload");
        });
        videos_id = 0;
    }

    function newVideo() {
        $('.uploadFile').show();
        videos_id = 0;
        resetVideoForm();
        waitToSubmit = false;
        $('#inputTitle').val("Video automatically booked");
        saveVideo(false);
        waitToSubmit = true;
        setTimeout(function () {
            waitToSubmit = false;
        }, 3000);
        reloadFileInput({});
        $('#videoFormModal').modal();
    }


    function resetArticleForm() {
        isArticle = 1;
        $('#inputVideoId').val("");
        $('#inputTitle').val("");
        $('#inputTrailer').val("");
        $('#inputCleanTitle').val("");
        $('#inputDescription').val("");
<?php
if (empty($advancedCustom->disableHTMLDescription)) {
    ?>
            tinymce.get('inputDescription').setContent("");
<?php } ?>
        $('#inputCategory').val($('#inputCategory option:first').val());
        $('#inputRrating').val("");
        $('.videoGroups').prop('checked', false);
        $('#can_download').prop('checked', false);
        $('#only_for_paid').prop('checked', false);
        $('#can_share').prop('checked', false);
        $('#public').prop('checked', true);
        $('#public').trigger("change");
        $('#videoIsAd').prop('checked', false);
        $('#videoIsAd').trigger("change");
        $('.nav-tabs a[href="#pmedia"], #pmedia').hide();
        $('.nav-tabs a[href="#pmetadata"]').tab('show');
        reloadFileInput();
        $('#videoIsAdControl, #videoExtraDetails, #videoLinkContent').slideUp();
        $('#postersImage').slideDown();
        $('#videoLink').val('');
        $('#videoStartSecond').val('00:00:00');
<?php
echo YouPHPTubePlugin::getManagerVideosReset();
?>

        setTimeout(function () {
            waitToSubmit = false;
        }, 2000);
        $('#videoFormModal').modal();
    }


    function newArticle() {
        $('.uploadFile').show();
        videos_id = 0;
        resetArticleForm();
        waitToSubmit = false;
        $('#inputTitle').val("Article automatically booked");
        saveVideo(false);
        waitToSubmit = true;
        setTimeout(function () {
            waitToSubmit = false;
        }, 3000);
        reloadFileInput({});
        $('#videoFormModal').modal();
    }

    function getEmbedCode(id) {
        copyToClipboard($('#embedInput' + id).val());
        $('#copied' + id).fadeIn();
        setTimeout(function () {
            $('#copied' + id).fadeOut();
        }, 2000);
    }

    function createQueueItem(queueItem, position) {
        var id = queueItem.return_vars.videos_id;
        if ($('#encodeProgress' + id).children().length) {
            return false;
        }
        var item = '<div class="progress progress-striped active " id="encodingProgress' + queueItem.id + '" style="margin: 0;">';
        item += '<div class="progress-bar  progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;"><span >0% Complete</span></div>';
        item += '<span class="progress-type"><span class="badge "><?php echo __("Queue Position"); ?> ' + position + '</span></span><span class="progress-completed">' + queueItem.name + '</span>';
        item += '</div><div class="progress progress-striped active " id="downloadProgress' + queueItem.id + '" style="height: 10px;"><div class="progress-bar  progress-bar-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;"></div></div> ';
        $('#encodeProgress' + id).html(item);
    }

    function viewsDetails(views_count, views_count_25, views_count_50, views_count_75, views_count_100) {
        viewsDetailsReset();
        $("#videoViewFormModal .modal-title").html("Total views: " + views_count);
        var p25 = (views_count_25 / views_count) * 100;
        var p50 = (views_count_50 / views_count) * 100;
        var p75 = (views_count_75 / views_count) * 100;
        var p100 = (views_count_100 / views_count) * 100;
        $('#videoViewFormModal').modal();

        $("#progress25 .progress-bar")
                .css("width", p25 + "%")
                .attr("aria-valuenow", p25)
                .text("25/100: " + p25 + "%");


        $("#progress50 .progress-bar")
                .css("width", p50 + "%")
                .attr("aria-valuenow", p50)
                .text("Half: " + p50 + "%");


        $("#progress75 .progress-bar")
                .css("width", p75 + "%")
                .attr("aria-valuenow", p75)
                .text("75/100: " + p75 + "%");


        $("#progress100 .progress-bar")
                .css("width", p100 + "%")
                .attr("aria-valuenow", p100)
                .text("End: " + p100 + "%");

    }

    function viewsDetailsReset() {
        $("#videoViewFormModal .modal-title").html("Loading ... ");
        $("#progress25 .progress-bar")
                .css("width", "0")
                .attr("aria-valuenow", "0")
                .text("Loading ...");


        $("#progress50 .progress-bar")
                .css("width", "0")
                .attr("aria-valuenow", "0")
                .text("Loading ...");


        $("#progress75 .progress-bar")
                .css("width", "0")
                .attr("aria-valuenow", "0")
                .text("Loading ...");


        $("#progress100 .progress-bar")
                .css("width", "0")
                .attr("aria-valuenow", "0")
                .text("Loading ...");

    }



    $(document).ready(function () {

        $('#videoFormModal').on('hidden.bs.modal', function () {
            var videos_id = $('#fileUploadVideos_id').val();
            if (!videoUploaded && videos_id) {
                deleteVideo(videos_id);
            }
            videoUploaded = false;
        });

        $('#videoFormModal').on('shown.bs.modal', function () {
            $(document).off('focusin.modal');
        });

        var ul = $('#upload ul');

        $('#drop a').click(function () {
            // Simulate a click on the file input button
            // to show the file browser dialog
            $(this).parent().find('input').click();
        });

        // Initialize the jQuery File Upload plugin
        $('#upload').fileupload({
            // This element will accept file drag/drop uploading
            dropZone: $('#drop'),
            // This function is called when a file is added to the queue;
            // either via the browse button, or via drag/drop:
            add: function (e, data) {
                console.log('add');
                var tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"' +
                        ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p style="color:#AAA;" class="action">Uploading...</p><p class="filename"></p><span></span></li>');

                // Append the file name and file size
                tpl.find('p.filename').text(data.files[0].name)
                        .append('<i>' + formatFileSize(data.files[0].size) + '</i>');

                // Add the HTML to the UL element
                data.context = tpl.appendTo(ul);

                // Initialize the knob plugin
                tpl.find('input').knob();

                // Listen for clicks on the cancel icon
                tpl.find('span').click(function () {

                    if (tpl.hasClass('working')) {
                        jqXHR.abort();
                    }

                    tpl.fadeOut(function () {
                        tpl.remove();
                    });

                });

                // Automatically upload the file once it is added to the queue
                var jqXHR = data.submit();
                videoUploaded = true;
            },
            progress: function (e, data) {

                // Calculate the completion percentage of the upload
                var progress = parseInt(data.loaded / data.total * 100, 10);

                // Update the hidden input field and trigger a change
                // so that the jQuery knob plugin knows to update the dial
                data.context.find('input').val(progress).change();

                if (progress == 100) {
                    data.context.removeClass('working');
                }
            },
            fail: function (e, data) {
                // Something has gone wrong!
                data.context.addClass('error');
            },
            done: function (e, data) {
                if (data.result.status === "error") {
                    if (typeof data.result.msg === 'string') {
                        msg = data.result.msg;
                    } else {
                        msg = data.result.msg[data.result.msg.length - 1];
                    }
                    swal({
                        title: "Sorry!",
                        text: msg,
                        html: true,
                        type: "error"
                    });
                    data.context.addClass('error');
                    data.context.find('p.action').text("Error");
                } else {
                    data.context.find('p.action').html("Upload done");
                    data.context.addClass('working');
                    $("#grid").bootgrid("reload");
                }
            }

        });

        // Prevent the default action when a file is dropped on the window
        $(document).on('drop dragover', function (e) {
            e.preventDefault();
        });

        // Helper function that formats the file sizes
        function formatFileSize(bytes) {
            if (typeof bytes !== 'number') {
                return '';
            }

            if (bytes >= 1000000000) {
                return (bytes / 1000000000).toFixed(2) + ' GB';
            }

            if (bytes >= 1000000) {
                return (bytes / 1000000).toFixed(2) + ' MB';
            }

            return (bytes / 1000).toFixed(2) + ' KB';
        }
<?php
if (!empty($row)) {
    $json = json_encode($row);
    if (!empty($json)) {
        ?>
                waitToSubmit = true;
                editVideo(<?php echo $json; ?>);
        <?php
    } else {
        echo "/*Json error for Video ID*/";
    }
}
?>

        $('#linkExternalVideo').click(function () {
            isArticle = 0;
            $('#inputVideoId').val("");
            $('#inputTitle').val("");
            $('#inputTrailer').val("");
            $('#inputCleanTitle').val("");
            $('#inputDescription').val("");
<?php
if (empty($advancedCustom->disableHTMLDescription)) {
    ?>
                tinymce.get('inputDescription').setContent("");
<?php } ?>
            $('#inputCategory').val($('#inputCategory option:first').val());
            $('#inputRrating').val("");
            $('.videoGroups').prop('checked', false);
            $('#can_download').prop('checked', false);
            $('#only_for_paid').prop('checked', false);
            $('#can_share').prop('checked', false);
            $('#public').prop('checked', true);
            $('#public').trigger("change");
            $('#videoIsAd').prop('checked', false);
            $('#videoIsAd').trigger("change");
            $('#input-jpg, #input-gif, #input-pjpg, #input-pgif').fileinput('destroy');
            $('#postersImage, #videoIsAdControl, .titles').slideUp();
            $('#videoLinkContent').slideDown();
            $('#videoLink').val('');
            $('#videoStartSecond').val('00:00:00');
<?php
echo YouPHPTubePlugin::getManagerVideosReset();
?>

            setTimeout(function () {
                waitToSubmit = false;
            }, 2000);
            $('#videoFormModal').modal();
        });

        $("#checkBtn").click(function () {
            var chk = $("#chk").hasClass('fa-check-square');
            $(".checkboxVideo").each(function (index) {
                if (chk) {
                    $("#chk").removeClass('fa-check-square');
                    $("#chk").addClass('fa-square');
                } else {
                    $("#chk").removeClass('fa-square');
                    $("#chk").addClass('fa-check-square');
                }
                $(this).prop('checked', !chk);
            });
        });
<?php if (!$config->getDisable_youtubeupload()) { ?>
            $("#uploadYouTubeBtn").click(function () {
                modal.showPleaseWait();
                var vals = [];
                $(".checkboxVideo").each(function (index) {
                    if ($(this).is(":checked")) {
                        vals.push($(this).val());
                    }
                });
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/youtubeUpload.json.php',
                    data: {"id": vals},
                    type: 'post',
                    success: function (response) {
                        console.log(response);
                        modal.hidePleaseWait();
                        if (!response.success) {
                            swal({
                                title: "<?php echo __("Sorry!"); ?>",
                                text: response.msg,
                                type: "error",
                                html: true
                            });
                        } else {
                            swal({
                                title: "<?php echo __("Success!"); ?>",
                                text: response.msg,
                                type: "success",
                                html: true
                            });
                        }
                    }
                });
            });
<?php } ?>
        $("#deleteBtn").click(function () {
            swal({
                title: "<?php echo __("Are you sure?"); ?>",
                text: "<?php echo __("You will not be able to recover these videos!"); ?>",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                closeOnConfirm: false
            },
                    function () {
                        swal.close();
                        modal.showPleaseWait();
                        var vals = [];
                        $(".checkboxVideo").each(function (index) {
                            if ($(this).is(":checked")) {
                                vals.push($(this).val());
                            }
                        });
                        deleteVideo(vals);
                    });
        });
        $('.datepicker').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            autoclose: true
        });
        $('#public').change(function () {
            if ($('#public').is(':checked')) {
                $('.non-public').slideUp();
            } else {
                $('.non-public').slideDown();
            }
        });
        $('#videoIsAd').change(function () {
            if (!$('#videoIsAd').is(':checked')) {
                $('.videoIsAdContent').slideUp();
            } else {
                $('.videoIsAdContent').slideDown();
            }
        });
        $('[data-toggle="tooltip"]').tooltip();
        $('#removeAutoplay').click(function () {
            $('#inputNextVideo-poster').attr('src', "view/img/notfound.jpg");
            $('#inputNextVideo').val("");
            $('#inputNextVideoClean').val("");
            $('#inputNextVideo-id').val("");
        });
        var grid = $("#grid").bootgrid({
            labels: {
                noResults: "<?php echo __("No results found!"); ?>",
                all: "<?php echo __("All"); ?>",
                infos: "<?php echo __("Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries"); ?>",
                loading: "<?php echo __("Loading..."); ?>",
                refresh: "<?php echo __("Refresh"); ?>",
                search: "<?php echo __("Search"); ?>",
            },
            ajax: true,
            url: "<?php echo $global['webSiteRootURL'] . "objects/videos.json.php?showAll=1"; ?>",
            formatters: {
                "commands": function (column, row)
                {
                    var embedBtn = '<button type="button" class="btn btn-xs btn-default command-embed" id="embedBtn' + row.id + '"  onclick="getEmbedCode(' + row.id + ')" data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Copy embed code")); ?>"><span class="fa fa-copy" aria-hidden="true"></span> <span id="copied' + row.id + '" style="display:none;"><?php echo str_replace("'", "\\'", __("Copied")); ?></span></button>'
                    embedBtn += '<input type="hidden" id="embedInput' + row.id + '" value=\'<iframe width="640" height="480" style="max-width: 100%;max-height: 100%;" src="<?php echo $global['webSiteRootURL']; ?>vEmbed/' + row.id + '" frameborder="0" allowfullscreen="allowfullscreen" allow="autoplay"></iframe>\'/>';

                    var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Edit")); ?>"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                    var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Delete")); ?>"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
                    var activeBtn = '<button style="color: #090" type="button" class="btn btn-default btn-xs command-active"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Inactivate")); ?>"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></button>';
                    var inactiveBtn = '<button style="color: #A00" type="button" class="btn btn-default btn-xs command-inactive"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Activate")); ?>"><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span></button>';
                    var unlistedBtn = '<button style="color: #BBB" type="button" class="btn btn-default btn-xs command-unlisted"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Unlisted")); ?>"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></button>';
                    var rotateLeft = '<button type="button" class="btn btn-default btn-xs command-rotate"  data-row-id="left"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Rotate LEFT")); ?>"><span class="fa fa-undo" aria-hidden="true"></span></button>';
                    var rotateRight = '<button type="button" class="btn btn-default btn-xs command-rotate"  data-row-id="right"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Rotate RIGHT")); ?>"><span class="fas fa-redo " aria-hidden="true"></span></button>';
                    var rotateBtn = "<br>" + rotateLeft + rotateRight;
                    var suggestBtn = "";
<?php
if (User::isAdmin()) {
    ?>
                        var suggest = '<button style="color: #C60" type="button" class="btn btn-default btn-xs command-suggest"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Unsuggest")); ?>"><i class="fas fa-star" aria-hidden="true"></i></button>';
                        var unsuggest = '<button style="" type="button" class="btn btn-default btn-xs command-suggest unsuggest"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Suggest")); ?>"><i class="far fa-star" aria-hidden="true"></i></button>';
                        suggestBtn = unsuggest;
                        if (row.isSuggested == "1") {
                            suggestBtn = suggest;
                        }
    <?php
}
?>
                    if (row.type == "audio") {
                        rotateBtn = "";
                    }
                    var status;
                    var pluginsButtons = '<br><?php echo YouPHPTubePlugin::getVideosManagerListButton(); ?>';
                    var download = "";
                    for (var k in row.videosURL) {
                        if (typeof row.videosURL[k].url === 'undefined' || !row.videosURL[k].url) {
                            continue;
                        }
                        var url = row.videosURL[k].url;
                        console.log(url);
                        if (url.indexOf('?') > -1) {
                            //url += "&download=1";
                        } else {
                            url += "?download=1";
                        }
                        download += '<a href="' + url + '" class="btn btn-default btn-xs" target="_blank" ><span class="fa fa-download " aria-hidden="true"></span> ' + k + '</a><br>';
                    }

                    if (row.status == "i") {
                        status = inactiveBtn;
                    } else if (row.status == "a") {
                        status = activeBtn;
                    } else if (row.status == "u") {
                        status = unlistedBtn;
                    } else if (row.status == "x") {
                        return editBtn + deleteBtn;
                    } else if (row.status == "d") {
                        return editBtn + deleteBtn;
                    } else {
                        return editBtn + deleteBtn;
                    }

                    var nextIsSet;
                    if (row.next_video == null || row.next_video.length == 0) {
                        nextIsSet = "<span class='label label-danger'> <?php echo __("Next video NOT set"); ?> </span>";
                    } else {
                        var nextVideoTitle;
                        if (row.next_video.title.length > 20) {
                            nextVideoTitle = row.next_video.title.substring(0, 18) + "..";
                        } else {
                            nextVideoTitle = row.next_video.title;
                        }
                        nextIsSet = "<span class='label label-success' data-toggle='tooltip' title='" + row.next_video.title + "'>Next video: " + nextVideoTitle + "</span>";
                    }
                    return embedBtn + editBtn + deleteBtn + status + suggestBtn + rotateBtn + pluginsButtons + "<br>" + download + nextIsSet;

                },
                "tags": function (column, row) {
                    var tags = "";
                    for (var i in row.tags) {
                        if (typeof row.tags[i].type == "undefined") {
                            continue;
                        }
                        tags += "<span class='label label-primary fix-width'>" + row.tags[i].label + ": </span><span class=\"label label-" + row.tags[i].type + " fix-width\">" + row.tags[i].text + "</span><br>";
                    }
                    tags += "<span class='label label-primary fix-width'><?php echo __("Type") . ":"; ?> </span><span class=\"label label-default fix-width\">" + row.type + "</span><br>";
                    tags += "<span class='label label-primary fix-width'><?php echo __("Views") . ":"; ?> </span><span class=\"label label-default fix-width\">" + row.views_count + " <a href='#' class='viewsDetails' onclick='viewsDetails(" + row.views_count + ", " + row.views_count_25 + "," + row.views_count_50 + "," + row.views_count_75 + "," + row.views_count_100 + ");'>[<i class='fas fa-info-circle'></i> Details]</a></span><br>";
                    tags += "<span class='label label-primary fix-width'><?php echo __("Format") . ":"; ?> </span>" + row.typeLabels;
                    return tags;
                },
                "checkbox": function (column, row) {
                    var tags = "<input type='checkbox' name='checkboxVideo' class='checkboxVideo' value='" + row.id + "'>";
                    return tags;
                },
                "titleTag": function (column, row) {
                    var tags = "";
                    var youTubeLink = "", youTubeUpload = "";
<?php if (!$config->getDisable_youtubeupload()) { ?>
                        youTubeUpload = '<button type="button" class="btn btn-danger btn-xs command-uploadYoutube"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Upload to YouTube")); ?>"><span class="fa fa-upload " aria-hidden="true"></span></button>';

                        if (row.youtubeId) {
                            //youTubeLink += '<a href=\'https://youtu.be/' + row.youtubeId + '\' target=\'_blank\'  class="btn btn-primary" data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Watch on YouTube")); ?>"><span class="fas fa-external-link-alt " aria-hidden="true"></span></a>';
                        }
                        var yt = '<br><div class="btn-group" role="group" ><a class="btn btn-default  btn-xs" disabled><span class="fab fa-youtube" aria-hidden="true"></span> YouTube</a> ' + youTubeUpload + youTubeLink + ' </div>';
                        if (row.status == "d" || row.status == "e") {
                            yt = "";
                        }
    <?php
} else {
    echo "yt='';";
}
?>
                    if (row.status !== "a") {
                        tags += '<div id="encodeProgress' + row.id + '"></div>';
                    }
                    if (/^x.*$/gi.test(row.status) || row.status == 'e') {
                        //tags += '<div class="progress progress-striped active" style="margin:5px;"><div id="encodeProgress' + row.id + '" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0px"></div></div>';


                    } else if (row.status == 'd') {
                        tags += '<div class="progress progress-striped active" style="margin:5px;"><div id="downloadProgress' + row.id + '" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0px"></div></div>';
                    }
                    var type, img, is_portrait;
                    if (row.type === "audio") {
                        type = "<span class='fa fa-headphones' style='font-size:14px;'></span> ";
                        img = "<img class='img img-responsive img-thumbnail pull-left rotate" + row.rotation + "' src='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + ".jpg?" + Math.random() + "' style='max-height:80px; margin-right: 5px;'> ";
                        if (typeof row.videosURL.pjpg !== 'undefined' && row.videosURL.pjpg.url) {
                            img = "<img class='img img-responsive img-thumbnail pull-left' src='" + row.videosURL.pjpg.url + "?" + Math.random() + "'  style='max-height:80px; margin-right: 5px;'> ";
                        } else if (typeof row.videosURL.jpg !== 'undefined' && row.videosURL.jpg.url) {
                            img = "<img class='img img-responsive img-thumbnail pull-left' src='" + row.videosURL.jpg.url + "?" + Math.random() + "'  style='max-height:80px; margin-right: 5px;'> ";
                        } else {
                            is_portrait = (row.rotation === "90" || row.rotation === "270") ? "img-portrait" : "";
                            img = "<img class='img img-responsive " + is_portrait + " img-thumbnail pull-left rotate" + row.rotation + "' src='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + ".jpg?" + Math.random() + "'  style='max-height:80px; margin-right: 5px;'> ";
                        }
                    } else {
                        type = "<span class='fa fa-film' style='font-size:14px;'></span> ";
                        if (typeof row.videosURL.pjpg !== 'undefined' && row.videosURL.pjpg.filename == 'notfound_portrait.jpg' && row.videosURL.jpg.filename == 'notfound.jpg') {
                            img = "<img class='img img-responsive img-thumbnail pull-left' src='" + row.videosURL.pjpg.url + "?" + Math.random() + "'  style='max-height:80px; margin-right: 5px;'> ";
                        } else if (typeof row.videosURL.pjpg !== 'undefined' && row.videosURL.pjpg.url && row.videosURL.pjpg.filename !== 'notfound_portrait.jpg' && row.videosURL.pjpg.filename !== 'notfound_portrait.jpg') {
                            img = "<img class='img img-responsive img-thumbnail pull-left' src='" + row.videosURL.pjpg.url + "?" + Math.random() + "'  style='max-height:80px; margin-right: 5px;'> ";
                        } else if (typeof row.videosURL.jpg !== 'undefined' && row.videosURL.jpg.url && row.videosURL.jpg.filename !== 'notfound.jpg') {
                            img = "<img class='img img-responsive img-thumbnail pull-left' src='" + row.videosURL.jpg.url + "?" + Math.random() + "'  style='max-height:80px; margin-right: 5px;'> ";
                        } else {
                            is_portrait = (row.rotation === "90" || row.rotation === "270") ? "img-portrait" : "";
                            img = "<img class='img img-responsive " + is_portrait + " img-thumbnail pull-left rotate" + row.rotation + "' src='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + ".jpg?" + Math.random() + "'  style='max-height:80px; margin-right: 5px;'> ";
                        }
                    }
<?php
if (YouPHPTubePlugin::isEnabledByName('PlayLists')) {
    ?>
                        var playList = "<hr><div class='videoPlaylist' videos_id='" + row.id + "' style='height:100px; overflow-y: scroll; padding:10px 5px;'></div>";
    <?php
} else {
    ?>
                        var playList = '';
    <?php
}
?>
                    return img + '<a href="<?php echo $global['webSiteRootURL']; ?>video/' + row.clean_title + '" class="btn btn-default btn-xs">' + type + row.title + "</a>" + tags + "" + yt + playList;
                }


            },
            post: function () {
                var page = $("#grid").bootgrid("getCurrentPage");
                if (!page) {
                    page = 1;
                }
                var ret = {current: page};
                return ret;
            },
        }).on("loaded.rs.jquery.bootgrid", function () {


            $('.videoPlaylist').each(function (i, obj) {
                var $this = this;
                var videos_id = $($this).attr('videos_id');
                //$(this).html($(this).attr('videos_id'));
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistsFromUserVideos.json.php',
                    data: {"users_id": <?php echo User::getId(); ?>, "videos_id": videos_id},
                    type: 'post',
                    success: function (response) {
                        var lists = "";
                        for (var x in response) {
                            if (typeof response[x] !== 'object') {
                                continue;
                            }

                            lists += '<div class="material-small material-switch"><input onchange="saveVideoOnPlaylist(' + videos_id + ', $(this).is(\':checked\'), ' + response[x].id + ')" data-toggle="toggle" type="checkbox" id="playlistVideo' + videos_id + "_" + response[x].id + '" value="1" ' + (response[x].isOnPlaylist ? "checked" : "") + ' videos_id="' + videos_id + '" ><label for="playlistVideo' + videos_id + "_" + response[x].id + '" class="label-primary"></label>  ' + response[x].name + '</div>';

                        }
                        $($this).html(lists);
                    }
                });
            });
            /* Executes after data is loaded and rendered */
            grid.find(".command-edit").on("click", function (e) {
                waitToSubmit = true;
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                editVideo(row);
            }).end().find(".command-delete").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                console.log(row);
                swal({
                    title: "<?php echo __("Are you sure?"); ?>",
                    text: "<?php echo __("You will not be able to recover this video!"); ?>",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                    closeOnConfirm: false
                },
                        function () {
                            swal.close();
                            deleteVideo(row.id);
                        });
            })
                    .end().find(".command-refresh").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/videoRefresh.json.php',
                    data: {"id": row.id},
                    type: 'post',
                    success: function (response) {
                        $("#grid").bootgrid("reload");
                        modal.hidePleaseWait();
                    }
                });
            })
                    .end().find(".command-unlisted").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/videoStatus.json.php',
                    data: {"id": row.id, "status": "i"},
                    type: 'post',
                    success: function (response) {
                        $("#grid").bootgrid("reload");
                        modal.hidePleaseWait();
                    }
                });
            })
                    .end().find(".command-active").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/videoStatus.json.php',
                    data: {"id": row.id, "status": "u"},
                    type: 'post',
                    success: function (response) {
                        $("#grid").bootgrid("reload");
                        modal.hidePleaseWait();
                    }
                });
            })
                    .end().find(".command-inactive").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/videoStatus.json.php',
                    data: {"id": row.id, "status": "a"},
                    type: 'post',
                    success: function (response) {
                        $("#grid").bootgrid("reload");
                        modal.hidePleaseWait();
                    }
                });
            })
                    .end().find(".command-rotate").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/videoRotate.json.php',
                    data: {"id": row.id, "type": $(this).attr('data-row-id')},
                    type: 'post',
                    success: function (response) {
                        $("#grid").bootgrid("reload");
                        modal.hidePleaseWait();
                    }
                });
            })
                    .end().find(".command-reencode").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/videoReencode.json.php',
                    data: {"id": row.id, "status": "i", "type": $(this).attr('data-row-id')},
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                        if (response.error) {
                            swal("<?php echo __("Sorry!"); ?>", response.error, "error");
                        } else {
                            $("#grid").bootgrid("reload");
                        }
                    }
                });
            })
                    .end().find(".command-uploadYoutube").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/youtubeUpload.json.php',
                    data: {"id": row.id},
                    type: 'post',
                    success: function (response) {
                        console.log(response);
                        modal.hidePleaseWait();
                        if (!response.success) {
                            swal({
                                title: "<?php echo __("Sorry!"); ?>",
                                text: response.msg,
                                type: "error",
                                html: true
                            });
                        } else {
                            swal({
                                title: "<?php echo __("Success!"); ?>",
                                text: response.msg,
                                type: "success",
                                html: true
                            });
                            $("#grid").bootgrid("reload");
                        }
                    }
                });
            });
<?php
if (User::isAdmin()) {
    ?>
                grid.find(".command-suggest").on("click", function (e) {
                    var row_index = $(this).closest('tr').index();
                    var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                    var isSuggested = $(this).hasClass('unsuggest');
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/videoSuggest.php',
                        data: {"id": row.id, "isSuggested": isSuggested},
                        type: 'post',
                        success: function (response) {
                            $("#grid").bootgrid("reload");
                            modal.hidePleaseWait();
                        }
                    });
                });
    <?php
}
?>
            setTimeout(function () {
                checkProgress()
            }, 500);
        });
        $('#inputCleanTitle').keyup(function (evt) {
            $('#inputCleanTitle').val(clean_name($('#inputCleanTitle').val()));
        });
        $('#inputTitle').keyup(function (evt) {
            $('#inputCleanTitle').val(clean_name($('#inputTitle').val()));
        });
        $('#addCategoryBtn').click(function (evt) {
            $('#inputCategoryId').val('');
            $('#inputName').val('');
            $('#inputCleanName').val('');
            $('#videoFormModal').modal();
        });
        $('#saveVideoBtn').click(function (evt) {
            $('#updateCategoryForm').submit();
        });
        $('#updateCategoryForm').submit(function (evt) {
            evt.preventDefault();
            saveVideo(true);
            return false;
        });
<?php
if (!empty($_GET['link'])) {
    ?>
            $('#linkExternalVideo').trigger('click');
    <?php
} else if (!empty($_GET['article'])) {
    ?>
            $('#addArticle').trigger('click');
    <?php
} else if (!empty($_GET['upload'])) {
    ?>
            setTimeout(function () {
                $('#uploadMp4').trigger('click');
            }, 500);
    <?php
}
?>

    });

</script>
