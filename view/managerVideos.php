<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage videos"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/category.php';
require_once $global['systemRootPath'] . 'objects/video.php';
$categories = Category::getAllCategories();

require_once $global['systemRootPath'] . 'objects/userGroups.php';
$userGroups = UserGroups::getAllUsersGroups();


if (!empty($_GET['video_id'])) {
    if (Video::canEdit($_GET['video_id'])) {
        $row = Video::getVideo($_GET['video_id']);
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Videos"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-fileinput/css/fileinput.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-fileinput/js/fileinput.min.js" type="text/javascript"></script>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
        <script>
            /*** Handle jQuery plugin naming conflict between jQuery UI and Bootstrap ***/
            $.widget.bridge('uibutton', $.ui.button);
            $.widget.bridge('uitooltip', $.ui.tooltip);
        </script>

        <style>
            #inputNextVideo-poster {
                height: 90px;
                width: 160px;
            }
            .ui-autocomplete{
                z-index: 9999999;
            }
            .krajee-default.file-preview-frame {
                min-width: 300px;
            }
        </style>

    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
        <?php
        include 'include/updateCheck.php';
        ?>
            <div class="btn-group" >
                <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups" class="btn btn-warning">
                    <span class="fa fa-users"></span> <?php echo __("User Groups"); ?>
                </a>
                <a href="<?php echo $global['webSiteRootURL']; ?>users" class="btn btn-primary">
                    <span class="fa fa-user"></span> <?php echo __("Users"); ?>
                </a>
                <a href="<?php echo $global['webSiteRootURL']; ?>charts" class="btn btn-info">
                    <span class="fa fa-bar-chart"></span>
                    <?php echo __("Video Chart"); ?>
                </a>
                <?php
                if (empty($advancedCustom->doNotShowEncoderButton)) {
                    if (!empty($config->getEncoderURL())) {
                        ?>
                        <a href="<?php echo $config->getEncoderURL(), "?webSiteRootURL=", urlencode($global['webSiteRootURL']), "&user=", urlencode(User::getUserName()), "&pass=", urlencode(User::getUserPass()); ?>" class="btn btn-default">
                            <span class="fa fa-cog"></span>
                            <?php echo __("Encode video and audio"); ?>
                        </a>
                        <?php
                    }
                }
                if (empty($advancedCustom->doNotShowUploadMP4Button)) {
                    ?>
                    <a href="<?php echo $global['webSiteRootURL']; ?>upload" class="btn btn-default">
                        <span class="fa fa-upload"></span>
                        <?php echo __("Upload a MP4 File"); ?>
                    </a>
                    <?php
                }
                if (empty($advancedCustom->doNotShowEmbedButton)) {
                    ?>                                    
                    <button class="btn btn-default" id="linkExternalVideo">
                        <span class="fa fa-link"></span>
                        <?php echo __("Embed a video link"); ?>
                    </button>
                    <?php
                }
                ?>




                <?php
                if (User::isAdmin()) {
                    ?>
                    <a href="<?php echo $global['webSiteRootURL']; ?>ads" class="btn btn-danger">
                        <span class="fa fa-money"></span> <?php echo __("Advertising Manager"); ?>
                    </a>
                    <?php
                }
                ?>
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
            ?>
            <div class="pull-left btn-group">       
                <button class="btn btn-secondary" id="checkBtn">
                    <i class="fa fa-square-o" aria-hidden="true" id="chk"></i>
                </button>
                <?php if (!$config->getDisable_youtubeupload()) { ?>
                <button class="btn btn-danger" id="uploadYouTubeBtn">
                    <i class="fa fa-youtube-play" aria-hidden="true"></i> <?php echo __('Upload to YouTube'); ?>
                </button>
                <?php } ?>
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
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <?php echo __('Status'); ?> <span class="caret"></span></button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#" onclick="changeStatus('a'); return false;"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <?php echo __('Active'); ?></a></li>
                        <li><a href="#" onclick="changeStatus('i'); return false;"><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span> <?php echo __('Inactive'); ?></a></li>
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
                                    <li class="active"><a data-toggle="tab" href="#jpg">Poster (JPG)</a></li>
                                    <li><a data-toggle="tab" href="#gif">Mouse Over Poster (GIF)</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div id="jpg" class="tab-pane fade in active">
                                        <input id="input-jpg" type="file" class="file-loading" accept="image/jpg">
                                    </div>
                                    <div id="gif" class="tab-pane fade">
                                        <input id="input-gif" type="file" class="file-loading" accept="image/gif">
                                    </div>
                                </div>
                            </div>
                            <div id="videoLinkContent">                                
                                <label for="videoLink" class="sr-only"><?php echo __("Video Link"); ?></label>
                                <input type="text" id="videoLink" class="form-control first" placeholder="<?php echo __("Video Link"); ?> http://www.your-embed-link.com/video" required>
                            </div>
                            <hr>
                            <form class="form-compact"  id="updateCategoryForm" onsubmit="">
                                <input type="hidden" id="inputVideoId"  >
                                <div class="titles">
                                    <label for="inputTitle" class="sr-only"><?php echo __("Title"); ?></label>
                                    <input type="text" id="inputTitle" class="form-control first" placeholder="<?php echo __("Title"); ?>" required autofocus>
                                    <label for="inputCleanTitle" class="sr-only"><?php echo __("Clean Title"); ?></label>
                                    <input type="text" id="inputCleanTitle" class="form-control" placeholder="<?php echo __("Clean Title"); ?>" required>
                                </div>
                                <label for="inputDescription" class="sr-only"><?php echo __("Description"); ?></label>
                                <textarea id="inputDescription" class="form-control" placeholder="<?php echo __("Description"); ?>" required></textarea>
                                <label for="inputCategory" class="sr-only"><?php echo __("Category"); ?></label>
                                <select class="form-control last" id="inputCategory" required>
                                    <?php
                                    foreach ($categories as $value) {
                                        echo "<option value='{$value['id']}'>{$value['name']}</option>";
                                    }
                                    ?>
                                </select>

                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <span class="fa fa-globe"></span> <?php echo __("Public Video"); ?>
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
                                            <span class="label label-info"><?php echo $value['total_users']; ?> Users linked</span>
                                            <div class="material-switch pull-right">
                                                <input id="videoGroup<?php echo $value['id']; ?>" type="checkbox" value="<?php echo $value['id']; ?>" class="videoGroups"/>
                                                <label for="videoGroup<?php echo $value['id']; ?>" class="label-warning"></label>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>

                                <?php
                                if (User::isAdmin()) {
                                    ?>

                                    <ul class="list-group" id="videoIsAdControl">
                                        <li class="list-group-item">
                                            <a href="#" class="btn btn-info btn-xs" data-toggle="popover" title="<?php echo __("What is this"); ?>" data-placement="bottom"  data-content="<?php echo __("This video will work as an advertising and will no longer appear on videos list"); ?>"><span class="fa fa-question-circle" aria-hidden="true"></span> <?php echo __("Help"); ?></a>
                                            <?php echo __("Create an Advertising"); ?>
                                            <div class="material-switch pull-right">
                                                <input id="videoIsAd" type="checkbox" value="0" class="userGroups"/>
                                                <label for="videoIsAd" class="label-success"></label>
                                            </div>
                                        </li>
                                        <li class="list-group-item videoIsAdContent" style="display: none">
                                            <label for="inputAdTitle" class="sr-only"><?php echo __("Advertising Title"); ?></label>
                                            <input type="text" id="inputAdTitle" class="form-control first" placeholder="<?php echo __("Advertising Title"); ?>" required autofocus>
                                            <label for="inputAdUrlRedirect" class="sr-only"><?php echo __("URL"); ?></label>
                                            <input type="url" id="inputAdUrlRedirect" class="form-control last" placeholder="<?php echo __("URL"); ?>" required autofocus>

                                            <label for="inputAdStarts" class="sr-only"><?php echo __("Starts on"); ?></label>
                                            <input type="text" id="inputAdStarts" class="form-control datepicker" placeholder="<?php echo __("Starts on"); ?>" required autofocus>
                                            <small>Leave Blank for Right Now</small>
                                            <label for="inputAdFinish" class="sr-only"><?php echo __("Finish on"); ?></label>
                                            <input type="text" id="inputAdFinish" class="form-control datepicker" placeholder="<?php echo __("Finish on"); ?>" required autofocus>
                                            <small>Leave Blank for Never</small>

                                            <label for="inputAdSkip" class="sr-only"><?php echo __("Skip Button appears after (X) seconds"); ?></label>
                                            <input type="number" id="inputAdSkip" class="form-control " placeholder="<?php echo __("Skip Button appears after (X) seconds"); ?>" required autofocus>
                                            <small>Leave blank for since begin or put a number of seconds bigger the the ad for never</small>


                                            <label for="inputAdClick" class="sr-only"><?php echo __("Stop ad after (X) clicks"); ?></label>
                                            <input type="number" id="inputAdClick" class="form-control " placeholder="<?php echo __("Stop ad after (X) clicks"); ?>" required autofocus>
                                            <small>Leave Blank for Never</small>

                                            <label for="inputAdPrints" class="sr-only"><?php echo __("Stop ad after (X) prints"); ?></label>
                                            <input type="number" id="inputAdPrints" class="form-control " placeholder="<?php echo __("Stop ad after (X) prints"); ?>" required autofocus>
                                            <small>Leave Blank for Never</small>

                                            <label for="inputAdCategory" class="sr-only"><?php echo __("Category to display this Ad"); ?></label>
                                            <select class="form-control last" id="inputAdCategory" required>
                                                <?php
                                                foreach ($categories as $value) {
                                                    echo "<option value='{$value['id']}'>{$value['name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </li>
                                    </ul>

                                    <?php
                                }
                                ?>

                                <div class="row">
                                    <h3><?php echo __("Autoplay Next Video"); ?> <button class="btn btn-danger btn-sm" id="removeAutoplay"><i class="fa fa-trash"></i> <?php echo __("Remove Autoplay Next Video"); ?></button></h3>
                                    <div class="col-md-4">
                                        <img id="inputNextVideo-poster" src="img/notfound.jpg" class="ui-state-default" alt="">
                                    </div>
                                    <div class="col-md-8">                                        
                                        <input id="inputNextVideo" placeholder="<?php echo __("Autoplay Next Video"); ?>" class="form-control">
                                        <input id="inputNextVideoClean" placeholder="<?php echo __("Autoplay Next Video URL"); ?>" class="form-control" readonly="readonly">
                                        <input type="hidden" id="inputNextVideo-id">                                        
                                    </div>
                                </div>
                                <script>
                                    $(function () {


                                        $("#inputNextVideo").autocomplete({
                                            minLength: 0,
                                            source: function (req, res) {
                                                $.ajax({
                                                    url: '<?php echo $global['webSiteRootURL']; ?>videos.json',
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
                                            ;
                                        };
                                    });
                                </script>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                            <button type="button" class="btn btn-primary" id="saveCategoryBtn"><?php echo __("Save changes"); ?></button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
            <?php
            if ((User::isAdmin())&&(!$config->getDisable_youtubeupload())) {
                ?>
                <div class="alert alert-info">
                    <h1><span class="fa fa-youtube"></span> Let us upload your video to YouTube</h1>
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

        <?php
        include 'include/footer.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>

        <script>
                                    var timeOut;
                                    var encodingNowId = "";
                                    var waitToSubmit = true;

                                    function changeStatus(status) {
                                        modal.showPleaseWait();
                                        var vals = [];
                                        $(".checkboxVideo").each(function (index) {
                                            if ($(this).is(":checked")) {
                                                vals.push($(this).val());
                                            }
                                        });
                                        $.ajax({
                                            url: 'setStatusVideo',
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
                                            url: 'setCategoryVideo',
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
                                                        }, 2000);
                                                    } else {

                                                    }

                                                    setTimeout(function () {
                                                        checkProgress();
                                                    }, 1000);
                                                } else if (encodingNowId !== "") {
                                                    $("#encodeProgress" + encodingNowId).slideUp("normal", function () {
                                                        $(this).remove();
                                                    });
                                                    encodingNowId = "";
                                                    setTimeout(function () {
                                                        checkProgress();
                                                    }, 5000);
                                                } else {
                                                    setTimeout(function () {
                                                        checkProgress();
                                                    }, 5000);
                                                }

                                            }
                                        });
                                    }

                                    function editVideo(row) {
                                        waitToSubmit = true;
                                        $('#postersImage, #videoIsAdControl, .titles').slideDown();
                                        if (row.type !== 'embed') {
                                            $('#videoLinkContent').slideUp();
                                            $('#videoLink').val(row.videoLink);
                                        }
                                        $('#inputVideoId').val(row.id);
                                        $('#inputTitle').val(row.title);
                                        $('#inputCleanTitle').val(row.clean_title);
                                        $('#inputDescription').val(row.description);
                                        $('#inputCategory').val(row.categories_id);
                                        if (row.next_video && row.next_video.id) {
                                            $('#inputNextVideo-poster').attr('src', "<?php echo $global['webSiteRootURL']; ?>videos/" + row.next_video.filename + ".jpg");
                                            $('#inputNextVideo').val(row.next_video.title);
                                            $('#inputNextVideoClean').val("<?php echo $global['webSiteRootURL']; ?>video/" + row.next_video.clean_title);
                                            $('#inputNextVideo-id').val(row.next_video.id);
                                        } else {
                                            $('#removeAutoplay').trigger('click');
                                        }

                                        $('.videoGroups').prop('checked', false);
                                        if (row.groups.length === 0) {
                                            $('#public').prop('checked', true);
                                        } else {
                                            $('#public').prop('checked', false);
                                            for (var index in row.groups) {
                                                $('#videoGroup' + row.groups[index].id).prop('checked', true);
                                            }
                                        }
                                        $('#public').trigger("change");
                                        $('#videoIsAd').prop('checked', false);
                                        $('#videoIsAd').trigger("change");
                                        $('#input-jpg, #input-gif').fileinput('destroy');
                                        $("#input-jpg").fileinput({
                                            uploadUrl: "uploadPoster/" + row.id + "/jpg",
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
                                            allowedFileExtensions: ["jpg"]
                                        });
                                        $("#input-gif").fileinput({
                                            uploadUrl: "uploadPoster/" + row.id + "/gif",
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
                                        $('#input-jpg, #input-gif').on('fileuploaded', function (event, data, previewId, index) {
                                            $("#grid").bootgrid("reload");
                                        })
                                        waitToSubmit = true;
                                        setTimeout(function () {
                                            waitToSubmit = false;
                                        }, 3000);
                                        $('#videoFormModal').modal();
                                    }

                                    function createQueueItem(queueItem, position) {
                                        var id = queueItem.return_vars.videos_id;
                                        if ($('#encodeProgress' + id).children().length) {
                                            return false;
                                        }
                                        var item = '<div class="progress progress-striped active " id="encodingProgress' + queueItem.id + '" style="margin: 0;">';
                                        item += '<div class="progress-bar  progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;"><span class="sr-only">0% Complete</span></div>';
                                        item += '<span class="progress-type"><span class="badge "><?php echo __("Queue Position"); ?> ' + position + '</span></span><span class="progress-completed">' + queueItem.name + '</span>';
                                        item += '</div><div class="progress progress-striped active " id="downloadProgress' + queueItem.id + '" style="height: 10px;"><div class="progress-bar  progress-bar-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;"></div></div> ';
                                        $('#encodeProgress' + id).html(item);
                                    }
                                    $(document).ready(function () {
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
                                            $('#inputVideoId').val("");
                                            $('#inputTitle').val("");
                                            $('#inputCleanTitle').val("");
                                            $('#inputDescription').val("");
                                            $('#inputCategory').val($('#inputCategory option:first').val());
                                            $('.videoGroups').prop('checked', false);
                                            $('#public').prop('checked', true);
                                            $('#public').trigger("change");
                                            $('#videoIsAd').prop('checked', false);
                                            $('#videoIsAd').trigger("change");
                                            $('#input-jpg, #input-gif').fileinput('destroy');
                                            $('#postersImage, #videoIsAdControl, .titles').slideUp();
                                            $('#videoLinkContent').slideDown();
                                            $('#videoLink').val('');
                                            setTimeout(function () {
                                                waitToSubmit = false;
                                            }, 2000);
                                            $('#videoFormModal').modal();
                                        });
                                        $("#checkBtn").click(function () {
                                            var chk = $("#chk").hasClass('fa-check-square-o');
                                            $(".checkboxVideo").each(function (index) {
                                                if (chk) {
                                                    $("#chk").removeClass('fa-check-square-o');
                                                    $("#chk").addClass('fa-square-o');
                                                } else {
                                                    $("#chk").removeClass('fa-square-o');
                                                    $("#chk").addClass('fa-check-square-o');
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
                                                url: 'youtubeUpload',
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
                                                        $.ajax({
                                                            url: 'deleteVideo',
                                                            data: {"id": vals},
                                                            type: 'post',
                                                            success: function (response) {
                                                                if (response.status === "1") {
                                                                    $("#grid").bootgrid("reload");
                                                                } else {
                                                                    swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your videos have NOT been deleted!"); ?>", "error");
                                                                }
                                                                modal.hidePleaseWait();
                                                            }
                                                        });
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
                                            $('#inputNextVideo-poster').attr('src', "img/notfound.jpg");
                                            $('#inputNextVideo').val("");
                                            $('#inputNextVideoClean').val("");
                                            $('#inputNextVideo-id').val("");
                                        });
                                        var grid = $("#grid").bootgrid({
                                            ajax: true,
                                            url: "<?php echo $global['webSiteRootURL'] . "videos.json"; ?>",
                                            formatters: {
                                                "commands": function (column, row)
                                                {
                                                    var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Edit")); ?>"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                                                    var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Delete")); ?>"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
                                                    var inactiveBtn = '<button style="color: #090" type="button" class="btn btn-default btn-xs command-inactive"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Inactivate")); ?>"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></button>';
                                                    var activeBtn = '<button style="color: #A00" type="button" class="btn btn-default btn-xs command-active"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Activate")); ?>"><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span></button>';
                                                    var rotateLeft = '<button type="button" class="btn btn-default btn-xs command-rotate"  data-row-id="left"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Rotate LEFT")); ?>"><span class="fa fa-undo" aria-hidden="true"></span></button>';
                                                    var rotateRight = '<button type="button" class="btn btn-default btn-xs command-rotate"  data-row-id="right"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Rotate RIGHT")); ?>"><span class="fa fa-repeat " aria-hidden="true"></span></button>';
                                                    var rotateBtn = "<br>" + rotateLeft + rotateRight;
                                                    var suggestBtn = "";
                                                    <?php
                                                    if(User::isAdmin()){
                                                    ?>
                                                    var suggest = '<button style="color: #C60" type="button" class="btn btn-default btn-xs command-suggest"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Suggest")); ?>"><i class="fa fa-star" aria-hidden="true"></i></button>';
                                                    var unsuggest = '<button style="" type="button" class="btn btn-default btn-xs command-suggest unsuggest"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Unsuggest")); ?>"><i class="fa fa-star-o" aria-hidden="true"></i></button>';
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
                                                        download += '<a href="' + row.videosURL[k].url + '?download=1" class="btn btn-default btn-xs" ><span class="fa fa-download " aria-hidden="true"></span> ' + k + '</a><br>';
                                                    }

                                                    if (row.status == "i") {
                                                        status = activeBtn;
                                                    } else if (row.status == "a") {
                                                        status = inactiveBtn;
                                                    } else if (row.status == "x") {
                                                        return editBtn + deleteBtn;
                                                    } else if (row.status == "d") {
                                                        return deleteBtn;
                                                    } else {
                                                        return editBtn + deleteBtn;
                                                    }

                                                    var nextIsSet;
                                                    if(row.next_video == null || row.next_video.length==0){
                                                            nextIsSet="<span class='label label-danger'>Next video NOT set</span>";
                                                    } else {
                                                        var nextVideoTitle;
                                                        if(row.next_video.title.length>20){
                                                            nextVideoTitle = row.next_video.title.substring(0,18)+"..";
                                                        } else {
                                                           nextVideoTitle = row.next_video.title; 
                                                        }
                                                        nextIsSet="<span class='label label-success' data-toggle='tooltip' title='"+row.next_video.title+"'>Next video: "+nextVideoTitle+"</span>";
                                                    }
                                                    return editBtn + deleteBtn + status + suggestBtn + rotateBtn + pluginsButtons + "<br>" + download + nextIsSet;

                                                },
                                                "tags": function (column, row) {
                                                    var tags = "";
                                                    for (var i in row.tags) {
                                                        if (typeof row.tags[i].type == "undefined") {
                                                            continue;
                                                        }
                                                        tags += "<span class='label label-primary fix-width'>" + row.tags[i].label + ": </span><span class=\"label label-" + row.tags[i].type + " fix-width\">" + row.tags[i].text + "</span><br>";
                                                    }
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
                                                        //youTubeLink += '<a href=\'https://youtu.be/' + row.youtubeId + '\' target=\'_blank\'  class="btn btn-primary" data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Watch on YouTube")); ?>"><span class="fa fa-external-link " aria-hidden="true"></span></a>';
                                                    }
                                                    var yt = '<br><div class="btn-group" role="group" ><a class="btn btn-default  btn-xs" disabled><span class="fa fa-youtube-play" aria-hidden="true"></span> YouTube</a> ' + youTubeUpload + youTubeLink + ' </div>';
                                                    if (row.status == "d" || row.status == "e") {
                                                        yt = "";
                                                    }
                                                    <?php } else { echo "yt='';"; } ?>
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
                                                        img = "<img class='img img-responsive img-thumbnail pull-left rotate" + row.rotation + "' src='<?php echo $global['webSiteRootURL']; ?>view/img/audio_wave.jpg' style='max-height:80px; margin-right: 5px;'> ";
                                                    } else {
                                                        type = "<span class='fa fa-film' style='font-size:14px;'></span> ";
                                                        is_portrait = (row.rotation === "90" || row.rotation === "270") ? "img-portrait" : "";
                                                        img = "<img class='img img-responsive " + is_portrait + " img-thumbnail pull-left rotate" + row.rotation + "' src='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + ".jpg'  style='max-height:80px; margin-right: 5px;'> ";
                                                    }
                                                    return img + '<a href="<?php echo $global['webSiteRootURL']; ?>video/' + row.clean_title + '" class="btn btn-default btn-xs">' + type + row.title + "</a>" + tags + "" + yt;
                                                }


                                            },
                                            post: function () {
                                                var page = $("#grid").bootgrid("getCurrentPage");
                                                console.log('prepare post');
                                                if (!page) {
                                                    page = 1;
                                                }
                                                var ret = {current: page};
                                                return ret;
                                            },
                                        }).on("loaded.rs.jquery.bootgrid", function () {
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
                                                            modal.showPleaseWait();
                                                            $.ajax({
                                                                url: 'deleteVideo',
                                                                data: {"id": row.id},
                                                                type: 'post',
                                                                success: function (response) {
                                                                    if (response.status === "1") {
                                                                        $("#grid").bootgrid("reload");
                                                                    } else {
                                                                        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your video has NOT been deleted!"); ?>", "error");
                                                                    }
                                                                    modal.hidePleaseWait();
                                                                }
                                                            });
                                                        });
                                            })
                                                    .end().find(".command-refresh").on("click", function (e) {
                                                var row_index = $(this).closest('tr').index();
                                                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                                                modal.showPleaseWait();
                                                $.ajax({
                                                    url: 'refreshVideo',
                                                    data: {"id": row.id},
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
                                                    url: 'setStatusVideo',
                                                    data: {"id": row.id, "status": "a"},
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
                                                    url: 'setStatusVideo',
                                                    data: {"id": row.id, "status": "i"},
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
                                                    url: 'rotateVideo',
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
                                                    url: 'reencodeVideo',
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
                                                    url: 'youtubeUpload',
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
                                                if(User::isAdmin()){
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
                                        $('#saveCategoryBtn').click(function (evt) {
                                            $('#updateCategoryForm').submit();
                                        });
                                        $('#updateCategoryForm').submit(function (evt) {
                                            evt.preventDefault();
                                            if (waitToSubmit) {
                                                return false;
                                            }
                                            var isPublic = $('#public').is(':checked');
                                            var selectedVideoGroups = [];
                                            var isAd = $('#videoIsAd').is(':checked');
                                            var adElements = {};
                                            if (isAd) {
                                                adElements = {
                                                    title: $('#inputAdTitle').val(),
                                                    starts: $('#inputAdStarts').val(),
                                                    finish: $('#inputAdFinish').val(),
                                                    skipSeconds: $('#inputAdSkip').val(),
                                                    clicks: $('#inputAdClick').val(),
                                                    prints: $('#inputAdPrints').val(),
                                                    categories_id: $('#inputAdCategory').val(),
                                                    redirect: $('#inputAdUrlRedirect').val()
                                                }
                                            }
                                            $('.videoGroups:checked').each(function () {
                                                selectedVideoGroups.push($(this).val());
                                            });
                                            if (!isPublic && selectedVideoGroups.length === 0) {
                                                //swal("<?php echo __("Sorry!"); ?>", "<?php echo __("You must make this video public or select a group to see your video!"); ?>", "error");
                                                //return false;
                                                isPublic = true;
                                            }
                                            if (isPublic) {
                                                selectedVideoGroups = [];
                                            }
                                            modal.showPleaseWait();
                                            $.ajax({
                                                url: 'addNewVideo',
                                                data: {
                                                    "id": $('#inputVideoId').val(),
                                                    "title": $('#inputTitle').val(),
                                                    "videoLink": $('#videoLink').val(),
                                                    "clean_title": $('#inputCleanTitle').val(),
                                                    "description": $('#inputDescription').val(),
                                                    "categories_id": $('#inputCategory').val(),
                                                    "public": isPublic,
                                                    "videoGroups": selectedVideoGroups,
                                                    "isAd": isAd,
                                                    "adElements": adElements,
                                                    "next_videos_id": $('#inputNextVideo-id').val()
                                                },
                                                type: 'post',
                                                success: function (response) {
                                                    if (response.status === "1") {
                                                        $('#videoFormModal').modal('hide');
                                                        $("#grid").bootgrid("reload");
                                                    } else {
                                                        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your video has NOT been saved!"); ?>", "error");
                                                    }
                                                    modal.hidePleaseWait();
                                                    waitToSubmit = true;
                                                }
                                            });
                                            return false;
                                        });
<?php
if (!empty($_GET['link'])) {
    ?>
                                            $('#linkExternalVideo').trigger('click');
    <?php
}
?>

                                    });

        </script>
    </body>
</html>
