<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager videos"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/category.php';
require_once $global['systemRootPath'] . 'objects/video.php';
$categories = Category::getAllCategories();
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();

require_once $global['systemRootPath'] . 'objects/userGroups.php';
$userGroups = UserGroups::getAllUsersGroups();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Videos"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">

            <a href="<?php echo $global['webSiteRootURL']; ?>orphanFiles" class="btn btn-default" id="addUserBtn">
                <span class="glyphicon glyphicon-trash" aria-hidden="true"></span> <?php echo __("Orphan Files"); ?>
            </a>
            <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups" class="btn btn-warning">
                <span class="fa fa-users"></span> <?php echo __("User Groups"); ?>
            </a>
            <a href="<?php echo $global['webSiteRootURL']; ?>users" class="btn btn-primary">
                <span class="fa fa-user"></span> <?php echo __("Users"); ?>
            </a>
            
            <?php
            if(User::isAdmin()){
                ?>
                <a href="<?php echo $global['webSiteRootURL']; ?>ads" class="btn btn-danger">
                    <span class="fa fa-money"></span> <?php echo __("Advertising Manager"); ?>
                </a>
                <?php
            }
            ?>
            
            <table id="grid" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-column-id="title" data-formatter="titleTag" ><?php echo __("Title"); ?></th>
                        <th data-column-id="tags" data-formatter="tags" data-sortable="false" data-width="210px"><?php echo __("Tags"); ?></th>
                        <th data-column-id="duration" data-width="100px"><?php echo __("Duration"); ?></th>
                        <th data-column-id="created" data-order="desc" data-width="100px"><?php echo __("Created"); ?></th>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false"  data-width="350px"></th>
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
                        <div class="modal-body">
                            <form class="form-compact"  id="updateCategoryForm" onsubmit="">
                                <input type="hidden" id="inputVideoId"  >
                                <label for="inputTitle" class="sr-only"><?php echo __("Title"); ?></label>
                                <input type="text" id="inputTitle" class="form-control first" placeholder="<?php echo __("Title"); ?>" required autofocus>
                                <label for="inputCleanTitle" class="sr-only"><?php echo __("Clean Title"); ?></label>
                                <input type="text" id="inputCleanTitle" class="form-control" placeholder="<?php echo __("Clean Title"); ?>" required>
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

                                    <ul class="list-group">
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
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                            <button type="button" class="btn btn-primary" id="saveCategoryBtn"><?php echo __("Save changes"); ?></button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->


        </div><!--/.container-->

        <?php
        include 'include/footer.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
        <script>
            function checkProgressVideo(filename, id, refresh) {
                $.ajax({
                    url: 'uploadStatus?filename=' + filename,
                    success: function (response) {
                        console.log(response);
                        var types = <?php echo json_encode(Video::$types); ?>;
                        var allComplete = true;
                        types.forEach(function (entry) {
                            console.log(entry);
                            var responseType;
                            if (response) {
                                //eval("if(!response."+entry + "){ continue;}");
                                eval("responseType = response." + entry + ";");
                                if (responseType && responseType.progress) {
                                    var txt = entry.toUpperCase() + ": " + responseType.progress + "%";
                                    $('#encoding' + entry + id).html(txt);
                                }
                            }
                            if (responseType && responseType.progress < 100) {
                                if (responseType.progress > 0) {
                                    $('#encoding' + entry + id).removeClass('label-danger');
                                    $('#encoding' + entry + id).addClass('label-warning');
                                }
                                allComplete = false;
                            }
                            if (responseType && responseType.progress === 100) {
                                $('#encoding' + entry + id).removeClass('label-warning');
                                $('#encoding' + entry + id).removeClass('label-danger');
                                $('#encoding' + entry + id).addClass('label-success');
                                $('#encoding' + entry + id).html(entry.toUpperCase() + ': 100%');
                            }
                        });
                        if (refresh && !allComplete) {
                            setTimeout(function () {
                                checkProgressVideo(filename, id, refresh);
                            }, 1000);
                        } else if (refresh && allComplete) {
                            $("#grid").bootgrid("reload");
                        }
                    }
                });
            }
            function checkProgressDownload(filename, id) {
                $.ajax({
                    url: 'getDownloadProgress',
                    data: {"filename": filename},
                    type: 'post',
                    success: function (response) {
                        $("#downloadProgress" + id).css({'width': response.progress + '%'});
                        if (response.progress < 100) {
                            setTimeout(function () {
                                checkProgressDownload(filename, id);
                            }, 1000);
                        } else if (response.progress == 100) {
                            $("#downloadProgress" + id).css({'width': '100%'});
                            swal({
                                title: "<?php echo __("Congratulations!"); ?>",
                                text: "<?php echo __("Your video download is complete, it is encoding now"); ?>",
                                type: "success"
                            });
                            $("#grid").bootgrid("reload");
                        }
                    }
                });
            }
            $(document).ready(function () {

                $('.datepicker').datetimepicker({
                    format: 'yyyy-mm-dd hh:ii',
                    autoclose:true
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
                var grid = $("#grid").bootgrid({
                    ajax: true,
                    url: "<?php echo $global['webSiteRootURL'] . "videos.json"; ?>",
                    formatters: {
                        "commands": function (column, row)
                        {
                            var originalBtn = '<a href="<?php echo $global['webSiteRootURL']; ?>/videos/original_' + row.filename + '" target="_blank" class="btn btn-xs btn-default" data-toggle="tooltip" data-placement="left" title="<?php echo __("Download Original"); ?>"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span></a>'
                            var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="<?php echo __("Edit"); ?>"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                            var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Delete"); ?>""><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
                            var reloadBtn = '<button type="button" class="btn btn-default btn-xs command-refresh"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Refresh"); ?>""><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button>';
                            var inactiveBtn = '<button style="color: #090" type="button" class="btn btn-default btn-xs command-inactive"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Inactivate"); ?>""><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></button>';
                            var activeBtn = '<button style="color: #A00" type="button" class="btn btn-default btn-xs command-active"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Activate"); ?>""><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span></button>';
                            var reencodeMP4Btn = '<button type="button" class="btn btn-default btn-xs command-reencode"  data-row-id="mp4"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Re-encode Video"); ?>""><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> MP4</button>';
                            var reencodeWEBMBtn = '<button type="button" class="btn btn-default btn-xs command-reencode"  data-row-id="webm"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Re-encode Video"); ?>""><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> WEBM</button>';
                            var reencodeImageBtn = '<button type="button" class="btn btn-default btn-xs command-reencode"  data-row-id="img"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Re-encode Image"); ?>""><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Img</button>';
                            var reencodeMp3 = '<button type="button" class="btn btn-default btn-xs command-reencode"  data-row-id="mp3"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Re-encode Audio"); ?>""><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> MP3</button>';
                            var reencodeOGG = '<button type="button" class="btn btn-default btn-xs command-reencode"  data-row-id="ogg"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Re-encode Audio"); ?>""><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> OGG</button>';
                            var reencodeAudio = reencodeMp3 + reencodeOGG;
                            var reencodeBtn = reencodeMP4Btn + reencodeWEBMBtn + reencodeImageBtn;
                            if (row.type == "audio") {
                                reencodeBtn = reencodeAudio ;
                            }
                            var status;

                            if (row.status == "i") {
                                status = activeBtn;
                            } else if (row.status == "a") {
                                status = inactiveBtn;
                            } else if (row.status == "x") {
                                return editBtn + deleteBtn + reloadBtn + reencodeBtn + originalBtn;
                            } else if (row.status == "d") {
                                return deleteBtn;
                            } else {
                                return editBtn + deleteBtn + reencodeBtn + originalBtn;
                            }
                            return editBtn + deleteBtn + reloadBtn + status + reencodeBtn + originalBtn;
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
                        "titleTag": function (column, row) {
                            var tags = "";
                            if (/^x.*$/gi.test(row.status) || row.status == 'e') {
                                setTimeout(function () {
                                    checkProgressVideo(row.filename, row.id, row.status == 'e');
                                }, 1000);

                                if (row.type == "audio") {
                                    tags += "<a href='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + "_progress_mp3.txt' target='_blank' class='label label-danger' id='encodingmp3" + row.id + "' >MP3: 0%</a> <a href='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + "_progress_ogg.txt' target='_blank' class='label label-danger' id='encodingogg" + row.id + "' >OGG: 0%</a>";
                                    tags += "<br><span class='label label-info'>Audio Spectrum</span>";
                                    tags += "<a href='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + "_progress_mp4.txt' target='_blank' class='label label-danger' id='encodingmp4" + row.id + "' >MP4: 0%</a> <a href='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + "_progress_webm.txt' target='_blank' class='label label-danger' id='encodingwebm" + row.id + "' >WEBM: 0%</a>";
                                } else {
                                    tags += "<a href='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + "_progress_mp4.txt' target='_blank' class='label label-danger' id='encodingmp4" + row.id + "' >MP4: 0%</a> <a href='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + "_progress_webm.txt' target='_blank' class='label label-danger' id='encodingwebm" + row.id + "' >WEBM: 0%</a>";
                                }

                            } else if (row.status == 'd') {
                                setTimeout(function () {
                                    checkProgressDownload(row.filename, row.id);
                                }, 1000);

                                tags += '<div class="progress progress-striped active"><div id="downloadProgress' + row.id + '" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0px"></div></div>';

                            }
                            var type;
                            if (row.type === "audio") {
                                type = "<span class='fa fa-headphones' style='font-size:14px;'></span> ";
                            } else {
                                type = "<span class='fa fa-film' style='font-size:14px;'></span> ";
                            }
                            return type + row.title + "<br>" + tags;
                        }


                    }
                }).on("loaded.rs.jquery.bootgrid", function () {
                    /* Executes after data is loaded and rendered */
                    grid.find(".command-edit").on("click", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);

                        $('#inputVideoId').val(row.id);
                        $('#inputTitle').val(row.title);
                        $('#inputCleanTitle').val(row.clean_title);
                        $('#inputDescription').val(row.description);
                        $('#inputCategory').val(row.categories_id);
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
                        $('#videoFormModal').modal();
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

                                    modal.showPleaseWait();
                                    $.ajax({
                                        url: 'deleteVideo',
                                        data: {"id": row.id},
                                        type: 'post',
                                        success: function (response) {
                                            if (response.status === "1") {
                                                $("#grid").bootgrid("reload");
                                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your video has been deleted!"); ?>", "success");
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
                    });
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
                    var isPublic = $('#public').is(':checked');
                    var selectedVideoGroups = [];
                    var isAd = $('#videoIsAd').is(':checked');
                    var adElements = {};
                    if(isAd){
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
                        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("You must make this video public or select a group to see your video!"); ?>", "error");
                        return false;
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
                            "clean_title": $('#inputCleanTitle').val(),
                            "description": $('#inputDescription').val(),
                            "categories_id": $('#inputCategory').val(),
                            "public": isPublic,
                            "videoGroups": selectedVideoGroups,
                            "isAd": isAd,
                            "adElements": adElements
                        },
                        type: 'post',
                        success: function (response) {
                            if (response.status === "1") {
                                $('#videoFormModal').modal('hide');
                                $("#grid").bootgrid("reload");
                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your video has been saved!"); ?>", "success");
                            } else {
                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your video has NOT been saved!"); ?>", "error");
                            }
                            modal.hidePleaseWait();
                        }
                    });
                    return false;
                });


            });

        </script>
    </body>
</html>
