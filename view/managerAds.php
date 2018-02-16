<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage ads"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/category.php';
require_once $global['systemRootPath'] . 'objects/video.php';
$categories = Category::getAllCategories();

require_once $global['systemRootPath'] . 'objects/userGroups.php';
$userGroups = UserGroups::getAllUsersGroups();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Ads"); ?></title>
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

            <div class="btn-group" >
                <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups" class="btn btn-warning">
                    <span class="fa fa-users"></span> <?php echo __("User Groups"); ?>
                </a>
                <a href="<?php echo $global['webSiteRootURL']; ?>users" class="btn btn-primary">
                    <span class="fa fa-user"></span> <?php echo __("Users"); ?>
                </a>

                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success">
                    <span class="fa fa-film"></span> <?php echo __("Videos"); ?>
                </a>
            </div>
            <table id="grid" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-column-id="title" data-formatter="titleTag" ><?php echo __("Video Title"); ?></th>
                        <th data-column-id="ad_title" ><?php echo __("Ad Title"); ?></th>
                        <th data-column-id="clicks" data-width="80px"><?php echo __("Clicks"); ?></th>
                        <th data-column-id="prints" data-width="80px"><?php echo __("Prints"); ?></th>
                        <th data-column-id="tags" data-formatter="tags" data-sortable="false" data-width="210px"><?php echo __("Tags"); ?></th>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false"  data-width="100px"></th>
                    </tr>
                </thead>
            </table>

            <div id="videoFormModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><?php echo __("Ads Form"); ?></h4>
                        </div>
                        <div class="modal-body">
                            <form class="form-compact"  id="updateCategoryForm" onsubmit="">
                                <input type="hidden" id="inputAdId"  >

                                <div class="form-group">
                                    <label for="inputAdTitle" ><?php echo __("Advertising Title"); ?></label>
                                    <input type="text" id="inputAdTitle" class="form-control " placeholder="<?php echo __("Advertising Title"); ?>" required autofocus>
                                </div>

                                <div class="form-group">
                                    <label for="inputAdUrlRedirect" ><?php echo __("URL"); ?></label>
                                    <input type="url" id="inputAdUrlRedirect" pattern="https?://.+" class="form-control " placeholder="<?php echo __("URL"); ?>" required >
                                </div>
                                <div class="form-group">
                                    <label for="inputAdStarts"><?php echo __("Starts on"); ?></label>
                                    <input type="text" id="inputAdStarts" class="form-control datepicker" placeholder="<?php echo __("Starts on"); ?>" required >
                                    <small>Leave Blank for Right Now</small>
                                </div>

                                <div class="form-group">
                                    <label for="inputAdFinish"><?php echo __("Finish on"); ?></label>
                                    <input type="text" id="inputAdFinish" class="form-control datepicker" placeholder="<?php echo __("Finish on"); ?>" required >
                                    <small>Leave Blank for Never</small>
                                </div>
                                <div class="form-group">
                                    <label for="inputAdSkip"><?php echo __("Skip Button appears after (X) seconds"); ?></label>
                                    <input type="number" id="inputAdSkip" class="form-control " placeholder="<?php echo __("Skip Button appears after (X) seconds"); ?>" required >
                                    <small>Leave blank for since begin or put a number of seconds bigger the the ad for never</small>
                                </div>

                                <div class="form-group">
                                    <label for="inputAdClick" ><?php echo __("Stop ad after (X) clicks"); ?></label>
                                    <input type="number" id="inputAdClick" class="form-control " placeholder="<?php echo __("Stop ad after (X) clicks"); ?>" required >
                                    <small>Leave Blank for Never</small>
                                </div>
                                <div class="form-group">
                                    <label for="inputAdPrints" ><?php echo __("Stop ad after (X) prints"); ?></label>
                                    <input type="number" id="inputAdPrints" class="form-control " placeholder="<?php echo __("Stop ad after (X) prints"); ?>" required >
                                    <small>Leave Blank for Never</small>
                                </div>
                                <div class="form-group">
                                    <label for="inputAdCategory" ><?php echo __("Category to display this Ad"); ?></label>
                                    <select class="form-control last" id="inputAdCategory" required>
                                        <?php
                                        foreach ($categories as $value) {
                                            echo "<option value='{$value['id']}'>{$value['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                            <button type="submit" class="btn btn-primary" id="saveCategoryBtn"><?php echo __("Save changes"); ?></button>
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
            $(document).ready(function () {

                $('.datepicker').datetimepicker({
                    format: 'yyyy-mm-dd hh:ii',
                    autoclose: true
                });

                $('[data-toggle="tooltip"]').tooltip();
                var grid = $("#grid").bootgrid({
                    ajax: true,
                    url: "<?php echo $global['webSiteRootURL'] . "ads.json"; ?>",
                    formatters: {
                        "commands": function (column, row)
                        {
                            var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="<?php echo __("Edit"); ?>"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                            var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Delete"); ?>""><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';

                            return editBtn + deleteBtn;
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
                                } else {
                                    tags += "<a href='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + "_progress_mp4.txt' target='_blank' class='label label-danger' id='encodingmp4" + row.id + "' >MP4: 0%</a> <a href='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + "_progress_webm.txt' target='_blank' class='label label-danger' id='encodingwebm" + row.id + "' >WEBM: 0%</a>";
                                }

                            } else if (row.status == 'd') {
                                setTimeout(function () {
                                    checkProgressDownload(row.filename, row.id);
                                }, 1000);

                                tags += '<div class="progress progress-striped active"><div id="downloadProgress' + row.id + '" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0px"></div></div>';

                            }
                            var type, img;
                            if (row.type === "audio") {
                                type = "<span class='fa fa-headphones' style='font-size:14px;'></span> ";
                                img = "<img class='img img-responsive img-thumbnail pull-left' src='<?php echo $global['webSiteRootURL']; ?>view/img/audio_wave.jpg' style='max-height:80px; margin-right: 5px;'> ";
                            } else {
                                type = "<span class='fa fa-film' style='font-size:14px;'></span> ";
                                img = "<img class='img img-responsive img-thumbnail pull-left' src='<?php echo $global['webSiteRootURL']; ?>videos/" + row.filename + ".jpg'  style='max-height:80px; margin-right: 5px;'> ";
                            }
                            return img + type + row.title + "<br>" + tags;
                        }


                    }
                }).on("loaded.rs.jquery.bootgrid", function () {
                    /* Executes after data is loaded and rendered */
                    grid.find(".command-edit").on("click", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);

                        $('#inputAdId').val(row.id);
                        $('#inputAdTitle').val(row.ad_title);
                        $('#inputAdStarts').val(row.starts);
                        $('#inputAdFinish').val(row.finish);
                        $('#inputAdSkip').val(row.skip_after_seconds);
                        $('#inputAdClick').val(row.finish_max_clicks);
                        $('#inputAdPrints').val(row.finish_max_prints);
                        $('#inputAdCategory').val(row.categories_id);
                        $('#inputAdUrlRedirect').val(row.redirect);

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
                                        url: 'deleteVideoAd',
                                        data: {"id": row.id},
                                        type: 'post',
                                        success: function (response) {
                                            if (response.status === "1") {
                                                $("#grid").bootgrid("reload");
                                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your ad has been deleted!"); ?>", "success");
                                            } else {
                                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your ad has NOT been deleted!"); ?>", "error");
                                            }
                                            modal.hidePleaseWait();
                                        }
                                    });
                                });
                    });
                });


                $('#saveCategoryBtn').click(function (evt) {
                    $('#updateCategoryForm').submit();
                });

                $('#updateCategoryForm').submit(function (evt) {
                    evt.preventDefault();
                    modal.showPleaseWait();
                    $.ajax({
                        url: 'addNewAd',
                        data: {
                            id: $('#inputAdId').val(),
                            title: $('#inputAdTitle').val(),
                            starts: $('#inputAdStarts').val(),
                            finish: $('#inputAdFinish').val(),
                            skipSeconds: $('#inputAdSkip').val(),
                            clicks: $('#inputAdClick').val(),
                            prints: $('#inputAdPrints').val(),
                            categories_id: $('#inputAdCategory').val(),
                            redirect: $('#inputAdUrlRedirect').val()
                        },
                        type: 'post',
                        success: function (response) {
                            if (response.status === "1") {
                                $('#videoFormModal').modal('hide');
                                $("#grid").bootgrid("reload");
                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your ad has been saved!"); ?>", "success");
                            } else {
                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your ad has NOT been saved!"); ?>", "error");
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
