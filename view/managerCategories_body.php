<div class="container-fluid">
    <br>
    <div class="panel panel-default">
        <div class="panel-heading">

            <button type="button" class="btn btn-default" id="addCategoryBtn">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo __("New Category"); ?>
            </button>
        </div>
        <div class="panel-body">
            <table id="grid" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-column-id="id" data-type="numeric" data-identifier="true" data-width="5%"><?php echo __("ID"); ?></th>
                        <th data-column-id="iconHtml" data-sortable="false" data-width="5%"><?php echo __("Icon"); ?></th>
                        <th data-column-id="name" data-order="desc"  data-formatter="name"  data-width="40%"><?php echo __("Name"); ?></th>
                        <th data-column-id="private" data-formatter="private"><?php echo __("Private"); ?></th>
                        <th data-column-id="owner"><?php echo __("Owner"); ?></th>
                        <th data-column-id="fullTotal_videos" data-sortable="false"><?php echo __("Videos"); ?></th>
                        <th data-column-id="fullTotal_lives" data-sortable="false"><?php echo __("Lives"); ?></th>
                        <th data-column-id="fullTotal_livelinks" data-sortable="false"><?php echo __("Live Links"); ?></th>
                        <th data-column-id="allow_download" data-formatter="download" ><?php echo __("Download"); ?></th>
                        <th data-column-id="suggested" data-formatter="suggested" ><?php echo __("Suggested"); ?></th>
                        <th data-column-id="order" ><?php echo __("Order"); ?></th>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false" data-width="130px"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div id="categoryFormModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fas fa-list"></i> <?php echo __("Category Form"); ?></h4>
                </div>
                <div class="modal-body">
                    <form class="form-compact"  id="updateCategoryForm" onsubmit="">
                        <ul class="nav nav-tabs">
                            <li class="active"><a data-toggle="tab" href="#images"><?php echo __("Images"); ?></a></li>
                            <li><a data-toggle="tab" href="#metaData"><?php echo __("Meta Data"); ?></a></li>
                        </ul>

                        <div class="tab-content">
                            <div id="images" class="tab-pane fade in active" style="padding: 5px;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label><?php echo __("Image"); ?></label>                        
                                            <?php
                                            $croppie1 = getCroppie(__("Upload Image"), "setImage1", 144, 192);
                                            echo $croppie1['html'];
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label><?php echo __("Background"); ?></label>                        
                                            <?php
                                            $croppie2 = getCroppie(__("Upload Image"), "setImage2", 1280, 180, 400);
                                            echo $croppie2['html'];
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="metaData" class="tab-pane fade" style="padding: 5px;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="hidden" id="inputCategoryId"  >
                                            <label for="inputName"><?php echo __("Name"); ?></label>
                                            <input type="text" id="inputName" class="form-control" placeholder="<?php echo __("Name"); ?>" required autofocus>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="inputCleanName"><?php echo __("Clean Name"); ?></label>
                                            <input type="text" id="inputCleanName" class="form-control" placeholder="<?php echo __("Clean Name"); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label  for="inputDescription"><?php echo __("Description"); ?></label>
                                            <textarea class="form-control" rows="5" id="inputDescription" placeholder="<?php echo __("Description"); ?>"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo __("Order"); ?></label>                        
                                            <input type="number" id="order" class="form-control" placeholder="<?php echo __("Order"); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo __("Privacy"); ?> <i class="fas fa-question-circle" data-toggle="tooltip" title="<?php echo htmlentities(__('This option will not make your videos private, this option is for other users not to be able to include their videos in this category. to make your videos private use the user groups feature')); ?>" ></i></label>                        
                                            <select class="form-control" id="inputPrivate">
                                                <option value="0"><?php echo __("Public"); ?></option>
                                                <option value="1"><?php echo __("Private"); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo __("Allow Download"); ?></label>                        
                                            <select class="form-control" id="allow_download">
                                                <option value="1"><?php echo __("Yes"); ?></option>
                                                <option value="0"><?php echo __("No"); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo __("Suggested"); ?></label>                        
                                            <select class="form-control" id="inputSuggested">
                                                <option value="0"><?php echo __("No"); ?></option>
                                                <option value="1"><?php echo __("Yes"); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo __("Parent-Category"); ?></label>
                                            <select class="form-control" id="inputParentId">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo __("Category Icon"); ?></label>                        
                                            <?php
                                            echo Layout::getIconsSelect("iconCat");
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
<script>
    var fullCatList;
    var image1;
    var image2;
    function setImage1(resp) {
        image1 = resp;
<?php
echo $croppie2['getCroppieFunction'];
?>
    }
    function setImage2(resp) {
        image2 = resp;
        $('.nav-tabs a[href="#images"]').tab('show');
        setTimeout(function(){saveCategory(image1, image2);},500);
    }


    function getCategoryPhotoPath($categories_id) {
        return getCategoryAssetPath("photo.png", $categories_id);
    }

    function getCategoryBackgroundPath($categories_id) {
        return getCategoryAssetPath("background.png", $categories_id);
    }

    function getCategoryAssetPath($name, $categories_id) {

        $dir = "videos/categories/assets/";
        $dir += $categories_id + "/" + $name;

        return webSiteRootURL + $dir;
    }


    $(document).ready(function () {

        function refreshSubCategoryList() {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL'] . "objects/categories.json.php"; ?>',
                success: function (data) {
                    var tmpHtml = "<option value='0' ><?php echo __("None (Parent)"); ?></option>";
                    fullCatList = data;
                    $.each(data.rows, function (key, val) {
                        console.log(val.id + " " + val.hierarchyAndName);
                        tmpHtml += "<option id='subcat" + val.id + "' value='" + val.id + "' >" + val.hierarchyAndName + "</option>";
                    });
                    $("#inputParentId").html(tmpHtml);
                }
            });
        }

        $('#categoryFormModal').on('hidden.bs.modal', function () {
            // when modal is closed in any way, get the new list - show old entry again (hidden by edit) + if a name was changed, it's corrected with this reload. 
            refreshSubCategoryList();
        })

        refreshSubCategoryList();

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
            url: "<?php echo $global['webSiteRootURL'] . "objects/categories.json.php"; ?>",
            formatters: {
                "download": function (column, row) {
                    if (row.allow_download == 1) {
                        return '<i class="far fa-check-square"></i>';
                    } else {
                        return '<i class="far fa-square"></i>';
                    }
                },
                "suggested": function (column, row) {
                    if (row.suggested == 1) {
                        return '<i class="far fa-check-square"></i>';
                    } else {
                        return '<i class="far fa-square"></i>';
                    }
                },
                "name": function (column, row) {
                    return row.hierarchyAndName
                },
                "type": function (column, row) {
                    if (row.type == '3') {
                        return "<?php echo __("Auto"); ?>";
                    } else if (row.type == '0') {
                        return "<?php echo __("Both"); ?>";
                    } else if (row.type == '1') {
                        return "<?php echo __("Audio"); ?>";
                    } else if (row.type == '2') {
                        return "<?php echo __("Video"); ?>";
                    } else {
                        return "<?php echo __("Invalid"); ?>";
                    }
                },
                "private": function (column, row) {
                    if (row.private == '1') {
                        return "<?php echo __("Private"); ?>";
                    } else {
                        return "<?php echo __("Public"); ?>";
                    }
                },
                "commands": function (column, row)
                {
                    var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" title="<?php echo __("Edit"); ?>"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                    var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete" data-row-id="' + row.id + '" data-toggle="tooltip" title="<?php echo __("Delete"); ?>"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                    var rssBtn = '<br><a class="btn btn-info btn-xs" data-toggle="tooltip" title="<?php echo __("RSS Feed"); ?>" target="_blank" href="<?php echo $global['webSiteRootURL']; ?>feed/?catName=' + row.clean_name + '" ><i class="fas fa-rss-square"></i></a>';
                    rssBtn += '<a class="btn btn-info btn-xs" data-toggle="tooltip" title="<?php echo __("MRSS Feed"); ?>" target="_blank" href="<?php echo $global['webSiteRootURL']; ?>mrss/?catName=' + row.clean_name + '" >MRSS</a>';
                    rssBtn += '<a class="btn btn-info btn-xs" data-toggle="tooltip" title="<?php echo __("Roku Json"); ?>" target="_blank" href="<?php echo $global['webSiteRootURL']; ?>roku.json?catName=' + row.clean_name + '" >ROKU</a>';

                    if (!row.canEdit) {
                        editBtn = "";
                        deleteBtn = "";
                    }

                    return editBtn + deleteBtn + rssBtn;
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function () {
            grid.find(".command-edit").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];

                // console.log(row);
                $("#subcat" + row.id).hide(); // hide own entry
                $('#inputCategoryId').val(row.id);
                $('#inputName').val(row.name);
                $('#inputCleanName').val(row.clean_name);
                $('#inputDescription').val(row.description);
                $('#inputSuggested').val(row.suggested);
                $('#inputPrivate').val(row.private);
                $('#allow_download').val(row.allow_download);
                $('#order').val(row.order);
                $('#inputParentId').val(row.parentId);
                //$('#inputType').val(row.type);
                $("select[name='iconCat']").val(row.iconClass);
                $("select[name='iconCat']").trigger('change');

                $('#categoryFormModal').modal();
                console.log("restartCroppie");
<?php
echo $croppie1['restartCroppie'] . "(getCategoryPhotoPath(row.id));";
echo $croppie2['restartCroppie'] . "(getCategoryBackgroundPath(row.id));";
?>

                console.log("restartCroppie done");
            }).end().find(".command-delete").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                swal({
                    title: "<?php echo __("Are you sure?"); ?>",
                    text: "<?php echo __("You will not be able to recover this action!"); ?>",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                        .then(function (willDelete) {
                            if (willDelete) {


                                modal.showPleaseWait();
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL'] . "objects/categoryDelete.json.php"; ?>',
                                    data: {"id": row.id},
                                    type: 'post',
                                    success: function (response) {
                                        if (response.status === "1") {
                                            $("#grid").bootgrid("reload");
                                            avideoToast("<?php echo __("Your category has been deleted!"); ?>");
                                        } else {
                                            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your category has NOT been deleted!"); ?>", "error");
                                        }
                                        modal.hidePleaseWait();
                                    }
                                });
                            }
                        });
            });
        });



        $('#inputCleanName').keyup(function (evt) {
            $('#inputCleanName').val(clean_name($('#inputCleanName').val()));
        });

        $('#inputName').keyup(function (evt) {
            $('#inputCleanName').val(clean_name($('#inputName').val()));
        });

        $('#addCategoryBtn').click(function (evt) {
            $('#inputCategoryId').val('');
            $('#inputName').val('');
            $('#inputCleanName').val('');
            $('#inputDescription').val('');
            $('#inputParentId').val('0');
            //$('#inputType').val('3');

<?php
echo $croppie1['restartCroppie'] . "(getCategoryPhotoPath(0));";
echo $croppie2['restartCroppie'] . "(getCategoryBackgroundPath(0));";
?>

            $('#categoryFormModal').modal();
        });

        $('#saveCategoryBtn').click(function (evt) {
            $('#updateCategoryForm').submit();
        });

        $('#updateCategoryForm').submit(function (evt) {
            //$('#updateCategoryForm a[href="#images"]').trigger("click");
            
            evt.preventDefault();
            setTimeout(function(){
                <?php
echo $croppie1['getCroppieFunction'];
?>
                
            },500);

            return false;
        });
    });

    function saveCategory(image1, image2) {
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL'] . "objects/categoryAddNew.json.php"; ?>',
            data: {
                "id": $('#inputCategoryId').val(),
                "name": $('#inputName').val(),
                "clean_name": $('#inputCleanName').val(),
                "description": $('#inputDescription').val(),
                "suggested": $('#inputSuggested').val(),
                "private": $('#inputPrivate').val(),
                "allow_download": $('#allow_download').val(),
                "order": $('#order').val(),
                "parentId": $('#inputParentId').val(),
                "iconClass": $("select[name='iconCat']").val(),
                "image1": image1,
                "image2": image2
            },
            type: 'post',
            success: function (response) {
                if (!response.error) {
                    $('#categoryFormModal').modal('hide');
                    $("#grid").bootgrid("reload");
                    avideoToast("<?php echo __("Your category has been saved!"); ?>");
                } else {
                    avideoAlertError(response.msg);
                }
                modal.hidePleaseWait();
            }
        });
    }

</script>