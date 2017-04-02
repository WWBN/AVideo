<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager videos"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/category.php';
require_once $global['systemRootPath'] . 'objects/video.php';
$categories = Category::getAllCategories();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>

        <title><?php echo $global['webSiteTitle']; ?> :: <?php echo __("Videos"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">

            <table id="grid" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-column-id="id" data-type="numeric" data-identifier="true" data-width="50px"><?php echo __("ID"); ?></th>
                        <th data-column-id="title" ><?php echo __("Title"); ?></th>
                        <th data-column-id="status" data-formatter="status" ><?php echo __("Status"); ?></th>
                        <th data-column-id="category" ><?php echo __("Category"); ?></th>
                        <th data-column-id="duration" ><?php echo __("Duration"); ?></th>
                        <th data-column-id="created" data-order="desc"><?php echo __("Created"); ?></th>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false"  data-width="250px"></th>
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
                                <input type="text" id="inputDescription" class="form-control" placeholder="<?php echo __("Description"); ?>" required>
                                <label for="inputCategory" class="sr-only"><?php echo __("Category"); ?></label>
                                <select class="form-control last" id="inputCategory" required>
                                    <?php
                                    foreach ($categories as $value) {
                                        echo "<option value='{$value['id']}'>{$value['name']}</option>";
                                    }
                                    ?>
                                </select>
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
            include 'include/footer.php';
            ?>

        </div><!--/.container-->

        <script>
            function checkProgressVideo(filename, id, refresh) {
                $.ajax({
                    url: 'uploadStatus?filename=' + filename,
                    success: function (response) {
                        console.log(response);
                        if (response) {
                            if (response.mp4 && response.mp4.progress) {
                                txt = "MP4: " + response.mp4.progress + "%";
                                $('#encodingmp4' + id).html(txt);
                            }
                            if (response.webm && response.webm.progress) {
                                txt = "WEBM: " + response.webm.progress + "%";
                                $('#encodingwebm' + id).html(txt);
                            }
                            $('#encoding' + id).html(txt);
                        }
                        if ((!response || response.mp4.progress < 100 || response.webm.progress < 100) && refresh) {
                            if (response.mp4.progress > 0) {
                                $('#encodingmp4' + id).removeClass('label-danger');
                                $('#encodingmp4' + id).addClass('label-warning');
                            }
                            if (response.webm.progress > 0) {
                                $('#encodingwebm' + id).removeClass('label-danger');
                                $('#encodingwebm' + id).addClass('label-warning');
                            }

                            setTimeout(function () {
                                checkProgressVideo(filename, id, true);
                            }, 2000);

                        } else  if (response && response.mp4.progress === 100 && response.webm.progress === 100) {
                            $("#grid").bootgrid("reload");
                        }
                        if (response && response.mp4.progress === 100) {
                            $('#encodingmp4' + id).removeClass('label-warning');
                            $('#encodingmp4' + id).removeClass('label-danger');
                            $('#encodingmp4' + id).addClass('label-success');
                            $('#encodingmp4' + id).html('MP4: 100%');
                        } 
                        if (response && response.webm.progress === 100) {
                            $('#encodingwebm' + id).removeClass('label-warning');
                            $('#encodingwebm' + id).removeClass('label-danger');
                            $('#encodingwebm' + id).addClass('label-success');
                            $('#encodingwebm' + id).html('WEBM: 100%');
                        }
                    }
                });
            }
            $(document).ready(function () {
                $('[data-toggle="tooltip"]').tooltip();
                var grid = $("#grid").bootgrid({
                    ajax: true,
                    url: "<?php echo $global['webSiteRootURL'] . "videos.json"; ?>",
                    formatters: {
                        "commands": function (column, row)
                        {
                            var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="<?php echo __("Edit"); ?>"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                            var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Delete"); ?>""><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>';
                            var reloadBtn = '<button type="button" class="btn btn-default btn-xs command-refresh"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Refresh"); ?>""><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button>';
                            var inactiveBtn = '<button style="color: #090" type="button" class="btn btn-default btn-xs command-inactive"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Inactivate"); ?>""><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></button>';
                            var activeBtn = '<button style="color: #A00" type="button" class="btn btn-default btn-xs command-active"  data-row-id="' + row.id + '"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Activate"); ?>""><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span></button>';
                            var reencodeMP4Btn = '<button type="button" class="btn btn-default btn-xs command-reencode"  data-row-id="mp3"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Re-encode Video"); ?>""><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> MP4</button>';
                            var reencodeWEBMBtn = '<button type="button" class="btn btn-default btn-xs command-reencode"  data-row-id="webm"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Re-encode Video"); ?>""><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> WEBM</button>';
                            var reencodeImageBtn = '<button type="button" class="btn btn-default btn-xs command-reencode"  data-row-id="img"  data-toggle="tooltip" data-placement="left" title="<?php echo __("Re-encode Image"); ?>""><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Img</button>';
                            var reencodeBtn = reencodeMP4Btn+reencodeWEBMBtn+reencodeImageBtn;
                            var status;
                            if (row.status == "i") {
                                status = activeBtn;
                            } else if (row.status == "a") {
                                status = inactiveBtn;
                            } else if (row.status == "x") {
                                return editBtn + deleteBtn + reloadBtn + reencodeBtn;
                            } else {
                                return editBtn + reencodeBtn;
                            }
                            return editBtn + deleteBtn + reloadBtn + status + reencodeBtn;
                        },
                        "status": function (column, row)
                        {
                            if (row.status == 'e') {
                                setTimeout(function () {
                                    checkProgressVideo(row.filename, row.id, true);
                                }, 1000);
                                return "<span class='label label-danger' id='encodingmp4" + row.id + "'>MP4: 0%</span><span class='label label-danger' id='encodingwebm" + row.id + "'>WEBM: 0%</span>";
                            } else if (row.status == 'a') {
                                return "<span class='label label-success'><?php echo __("Active"); ?></span>";
                            } else if (row.status == 'i') {
                                return "<span class='label label-danger'><?php echo __("Inactive"); ?></span>";
                            } else if (row.status == 'x') {
                                setTimeout(function () {
                                    checkProgressVideo(row.filename, row.id, false);
                                }, 1000);
                                return "<a href='<?php echo $global['webSiteRootURL'];?>videos/"+row.filename+"_progress_mp4.txt' target='_blank' class='label label-danger' id='encodingmp4" + row.id + "'>MP4: 0%</a><a href='<?php echo $global['webSiteRootURL'];?>videos/"+row.filename+"_progress_mp4.txt' target='_blank' class='label label-danger' id='encodingwebm" + row.id + "'>WEBM: 0%</a>";
                            }

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
                    modal.showPleaseWait();
                    $.ajax({
                        url: 'addNewVideo',
                        data: {"id": $('#inputVideoId').val(), "title": $('#inputTitle').val(), "clean_title": $('#inputCleanTitle').val(), "description": $('#inputDescription').val(), "categories_id": $('#inputCategory').val()},
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
