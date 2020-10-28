<div class="container-fluid">
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
                        <th data-column-id="fullTotal" data-sortable="false"><?php echo __("Videos"); ?></th>
                        <th data-column-id="allow_download" ><?php echo __("Download"); ?></th>
                        <th data-column-id="order" ><?php echo __("Order"); ?></th>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false"></th>
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
                    <h4 class="modal-title"><?php echo __("Category Form"); ?></h4>
                </div>
                <div class="modal-body">
                    <form class="form-compact"  id="updateCategoryForm" onsubmit="">
                        <input type="hidden" id="inputCategoryId"  >
                        <label for="inputName" class="sr-only"><?php echo __("Name"); ?></label>
                        <input type="text" id="inputName" class="form-control first" placeholder="<?php echo __("Name"); ?>" required autofocus>
                        <label for="inputCleanName" class="sr-only"><?php echo __("Clean Name"); ?></label>
                        <input type="text" id="inputCleanName" class="form-control last" placeholder="<?php echo __("Clean Name"); ?>" required>
                        <label class="sr-only" for="inputDescription"><?php echo __("Description"); ?></label>
                        <textarea class="form-control" rows="5" id="inputDescription" placeholder="<?php echo __("Description"); ?>"></textarea>
                        <label><?php echo __("Order"); ?></label>                        
                        <input type="number" id="order" class="form-control" placeholder="<?php echo __("Order"); ?>">
                        <label><?php echo __("Privacy"); ?></label>                        
                        <select class="form-control" id="inputPrivate">
                            <option value="0"><?php echo __("Public"); ?></option>
                            <option value="1"><?php echo __("Private"); ?></option>
                        </select>
                        <label><?php echo __("Allow Download"); ?></label>                        
                        <select class="form-control" id="allow_download">
                            <option value="1"><?php echo __("Yes"); ?></option>
                            <option value="0"><?php echo __("No"); ?></option>
                        </select>
                        <label><?php echo __("Autoplay next-video-order"); ?></label>                        
                        <select class="form-control" id="inputNextVideoOrder">
                            <option value="0"><?php echo __("Random"); ?></option>
                            <option value="1"><?php echo __("By name"); ?></option>
                        </select>
                        <div><label><?php echo __("Parent-Category"); ?></label>
                            <select class="form-control" id="inputParentId">
                            </select>
                        </div>
                        <div><label for="inputType"><?php echo __("Type"); ?></label>
                            <select class="form-control" id="inputType">
                                <option value="3"><?php echo __("Auto"); ?></option>
                                <option value="0"><?php echo __("Both"); ?></option>
                                <option value="1"><?php echo __("Audio"); ?></option>
                                <option value="2"><?php echo __("Video"); ?></option>
                            </select>
                        </div>
                        <div class="btn-group">
                            <button data-selected="graduation-cap" type="button" class="icp iconCat btn btn-default dropdown-toggle iconpicker-component" data-toggle="dropdown">
                                <?php echo __("Select an icon for the category"); ?>  <i class="fa fa-fw"></i>
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu"></div>
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
    $(document).ready(function () {
        $('.iconCat').iconpicker({
            //searchInFooter: true, // If true, the search will be added to the footer instead of the title
            //inputSearch:true,
            //showFooter:true
        });

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
                "nextVideoOrder": function (column, row) {
                    if (row.nextVideoOrder == 0) {
                        return "<?php echo __("Random"); ?>";
                    } else {
                        return "<?php echo __("By name"); ?>";
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
                    var rssBtn = '<a class="btn btn-info btn-xs" data-toggle="tooltip" title="<?php echo __("RSS Feed"); ?>" target="_blank" href="<?php echo $global['webSiteRootURL']; ?>feed/?catName=' + row.clean_name + '" ><i class="fas fa-rss-square"></i></a>';

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
                $('#inputNextVideoOrder').val(row.nextVideoOrder);
                $('#inputPrivate').val(row.private);
                $('#allow_download').val(row.allow_download);
                $('#order').val(row.order);
                $('#inputParentId').val(row.parentId);
                $('#inputType').val(row.type);
                $(".iconCat i").attr("class", row.iconClass);

                $('#categoryFormModal').modal();
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
                                            avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your category has been deleted!"); ?>", "success");
                                        } else {
                                            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your category has NOT been deleted!"); ?>", "error");
                                        }
                                        modal.hidePleaseWait();
                                    }
                                });
                            }
                        });
            });
            setTimeout(function(){$('[data-toggle="tooltip"]').tooltip();},1000);
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
            $('#inputType').val('3');
            $('#categoryFormModal').modal();
        });

        $('#saveCategoryBtn').click(function (evt) {
            $('#updateCategoryForm').submit();
        });

        $('#updateCategoryForm').submit(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL'] . "objects/categoryAddNew.json.php"; ?>',
                data: {
                    "id": $('#inputCategoryId').val(), "name": $('#inputName').val(), "clean_name": $('#inputCleanName').val(), "description": $('#inputDescription').val(), "nextVideoOrder": $('#inputNextVideoOrder').val(), "private": $('#inputPrivate').val(),
                    "allow_download": $('#allow_download').val(), "order": $('#order').val(), "parentId": $('#inputParentId').val(), "type": $('#inputType').val(), "iconClass": $(".iconCat i").attr("class")},
                type: 'post',
                success: function (response) {
                    if (response.status) {
                        $('#categoryFormModal').modal('hide');
                        $("#grid").bootgrid("reload");
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your category has been saved!"); ?>", "success");
                    } else {
                        avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your category has NOT been saved!"); ?>", "error");
                    }
                    modal.hidePleaseWait();
                }
            });
            return false;
        });
    });

</script>