<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';


if(!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}user?RedirectUri={$global['webSiteRootURL']}mvideos");
    exit;
}

if (!User::canUpload()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage videos"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Videos"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        include $global['systemRootPath'] . 'view/managerVideos_head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php 
        include $global['systemRootPath'] . 'view/include/navbar.php';
        include $global['systemRootPath'] . 'view/managerVideos_body.php';
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>

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
                                        if ((row.type === 'embed') || (row.type === 'linkVideo') || (row.type === 'linkAudio')) {

                                            $('#videoLink').val(row.videoLink);
                                            $('#videoLinkType').val(row.type);
                                        } else {
                                            $('#videoLinkContent').slideUp();
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
                                            allowedFileExtensions: ["jpg"]
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
                                                        $.ajax({
                                                            url: '<?php echo $global['webSiteRootURL']; ?>objects/videoDelete.json.php',
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
                                            url: "<?php echo $global['webSiteRootURL'] . "objects/videos.json.php"; ?>",
                                            formatters: {
                                                "commands": function (column, row)
                                                {
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
                                                        var suggest = '<button style="color: #C60" type="button" class="btn btn-default btn-xs command-suggest"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Suggest")); ?>"><i class="fas fa-star" aria-hidden="true"></i></button>';
                                                        var unsuggest = '<button style="" type="button" class="btn btn-default btn-xs command-suggest unsuggest"  data-toggle="tooltip" data-placement="left" title="<?php echo str_replace("'", "\\'", __("Unsuggest")); ?>"><i class="far fa-star" aria-hidden="true"></i></button>';
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
                                                        download += '<a href="/fetch/' + k  +'/'+row.clean_title+ '?download=<?=uniqid();?>" class="btn btn-default btn-xs" ><span class="fa fa-download " aria-hidden="true"></span> ' + k + '</a><br>';
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
                                                        return deleteBtn;
                                                    } else {
                                                        return editBtn + deleteBtn;
                                                    }

                                                    var nextIsSet;
                                                    if (row.next_video == null || row.next_video.length == 0) {
                                                        nextIsSet = "<span class='label label-danger'>Next video NOT set</span>";
                                                    } else {
                                                        var nextVideoTitle;
                                                        if (row.next_video.title.length > 20) {
                                                            nextVideoTitle = row.next_video.title.substring(0, 18) + "..";
                                                        } else {
                                                            nextVideoTitle = row.next_video.title;
                                                        }
                                                        nextIsSet = "<span class='label label-success' data-toggle='tooltip' title='" + row.next_video.title + "'>Next video: " + nextVideoTitle + "</span>";
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
                                                    tags += "<span class='label label-primary fix-width'><?php echo __("Type") . ":"; ?> </span><span class=\"label label-default fix-width\">" + row.type + "</span><br>";
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
<?php } else {
    echo "yt='';";
} ?>
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
                                                    } else {
                                                        type = "<span class='fa fa-film' style='font-size:14px;'></span> ";
                                                        is_portrait = (row.rotation === "90" || row.rotation === "270") ? "img-portrait" : "";
                                                        img = "<img class='img img-responsive " + is_portrait + " img-thumbnail pull-left rotate" + row.rotation + "' src='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + ".jpg?" + Math.random() + "'  style='max-height:80px; margin-right: 5px;'> ";
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
                                                                url: '<?php echo $global['webSiteRootURL']; ?>objects/videoDelete.json.php',
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
                                                url: '<?php echo $global['webSiteRootURL']; ?>objects/videoAddNew.json.php',
                                                data: {
                                                    "id": $('#inputVideoId').val(),
                                                    "title": $('#inputTitle').val(),
                                                    "videoLink": $('#videoLink').val(),
                                                    "videoLinkType": $('#videoLinkType').val(),
                                                    "clean_title": $('#inputCleanTitle').val(),
                                                    "description": $('#inputDescription').val(),
                                                    "categories_id": $('#inputCategory').val(),
                                                    "public": isPublic,
                                                    "videoGroups": selectedVideoGroups,
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
