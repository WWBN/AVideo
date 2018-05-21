<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage categories"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/category.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: <?php echo __("Category"); ?></title>

        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>css/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js" type="text/javascript"></script>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css" rel="stylesheet" type="text/css"/>
    </head>

    <body>
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>
        <div class="container">
        <?php include $global['systemRootPath'] . 'view/include/updateCheck.php'; ?>
            <button type="button" class="btn btn-default" id="addCategoryBtn">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo __("New Category"); ?>
            </button>

            <table id="grid" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-column-id="id" data-type="numeric" data-identifier="true"><?php echo __("ID"); ?></th>
                        <th data-column-id="iconHtml" data-sortable="false"><?php echo __("Icon"); ?></th>
                        <th data-column-id="name" data-order="desc"><?php echo __("Name"); ?></th>
                        <th data-column-id="clean_name"><?php echo __("Clean Name"); ?></th>
                        <th data-column-id="description"><?php echo __("Description"); ?></th>
                        <th data-column-id="nextVideoOrder" data-formatter="nextVideoOrder"><?php echo __("Next video order"); ?></th>
                        <th data-column-id="parentId" data-formatter="parentId" ><?php echo __("Parent ID"); ?></th>
                        <th data-column-id="type" data-formatter="type"><?php echo __("Type"); ?></th>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false"></th>
                    </tr>
                </thead>
            </table>

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
        <?php include $global['systemRootPath'] . 'view/include/footer.php'; ?>
        <script>
            var fullCatList;
            $(document).ready(function () {
                $('.iconCat').iconpicker({
                    //searchInFooter: true, // If true, the search will be added to the footer instead of the title
                    //inputSearch:true,
                    //showFooter:true
                });

                function refreshSubCategoryList(){
                    $.getJSON( "<?php echo $global['webSiteRootURL'] . "categories.json"; ?>", function( data ) {
  		                var tmpHtml = "<option value='0' ><?php echo __("None (Parent)"); ?></option>";
                        fullCatList = data;
                        $.each( data.rows, function( key, val ) {
                            console.log(val.id+" "+val.name)
                            tmpHtml += "<option id='subcat"+val.id+"' value='"+val.id+"' >"+val.name+"</option>";
                        });
                        $("#inputParentId").html(tmpHtml);
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
                    url: "<?php echo $global['webSiteRootURL'] . "categories.json"; ?>",
                    formatters: {
                        "nextVideoOrder": function(column, row){
                            if(row.nextVideoOrder==0){
                                return "<?php echo __("Random"); ?>";
                            } else {
                                return "<?php echo __("By name"); ?>";
                            }
                        },
                        "parentId": function(column, row){
                            //if(fullCatList==undefined){
                               // refreshSubCategoryList();    
                            // }
                            if(fullCatList!=undefined){
                                var returnValue;
                                $.each( fullCatList.rows, function( key, val ) {
                                    //console.log(val.id + " = "+row.id);
                                    if(val.id==row.parentId){
                                        console.log("found sub "+val.name);
                                        returnValue = val.name;
                                    } else if((row.parentId=="0")||(row.parentId=="-1")){
                                        console.log("found parent");
                                        returnValue = "<?php echo __("None (Parent)"); ?>";
                                    }
                                });
                                if(returnValue!=undefined){
                                    return returnValue;
                                }
                            }
                            return <?php __("Not loaded yet"); ?>;
                            
                        },
                        "type": function(column, row){
                            if(row.type=='3'){
                                return "<?php echo __("Auto"); ?>";
                            } else if(row.type=='0'){
                                return "<?php echo __("Both"); ?>";
                            } else if(row.type=='1'){
                                return "<?php echo __("Audio"); ?>";
                            } else if(row.type=='2'){
                                return "<?php echo __("Video"); ?>";
                            } else {
                                return "<?php echo __("Invalid"); ?>";
                            }
                        },
                        "commands": function (column, row)
                        {
                            var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="<?php echo __("Edit"); ?>"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                            var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '  data-toggle="tooltip" data-placement="left" title="<?php echo __("Delete"); ?>"><span class="glyphicon glyphicon-erase" aria-hidden="true"></span></button>';
                            return editBtn + deleteBtn;
                        }
                    }
                }).on("loaded.rs.jquery.bootgrid", function () {
                    grid.find(".command-edit").on("click", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        // console.log(row);
                        $("#subcat"+row.id).hide(); // hide own entry
                        $('#inputCategoryId').val(row.id);
                        $('#inputName').val(row.name);
                        $('#inputCleanName').val(row.clean_name);
                        $('#inputDescription').val(row.description);
                        $('#inputNextVideoOrder').val(row.nextVideoOrder);
                        $('#inputParentId').val(row.parentId);
                        $('#inputType').val(row.type);
                        $(".iconCat i").attr("class", row.iconClass);

                        $('#categoryFormModal').modal();
                    }).end().find(".command-delete").on("click", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);
                        swal({
                            title: "<?php echo __("Are you sure?"); ?>",
                            text: "<?php echo __("You will not be able to recover this category!"); ?>",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                            closeOnConfirm: false
                        },
                                function () {

                                    modal.showPleaseWait();
                                    $.ajax({
                                        url: 'deleteCategory',
                                        data: {"id": row.id},
                                        type: 'post',
                                        success: function (response) {
                                            if (response.status === "1") {
                                                $("#grid").bootgrid("reload");
                                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your category has been deleted!"); ?>", "success");
                                            } else {
                                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your category has NOT been deleted!"); ?>", "error");
                                            }
                                            modal.hidePleaseWait();
                                        }
                                    });
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
                        url: 'addNewCategory',
                        data: {"id": $('#inputCategoryId').val(), "name": $('#inputName').val(), "clean_name": $('#inputCleanName').val(),"description": $('#inputDescription').val(),"nextVideoOrder": $('#inputNextVideoOrder').val(),"parentId": $('#inputParentId').val(),"type": $('#inputType').val(), "iconClass": $(".iconCat i").attr("class")},
                        type: 'post',
                        success: function (response) {
                            if (response.status === "1") {
                                $('#categoryFormModal').modal('hide');
                                $("#grid").bootgrid("reload");
                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your category has been saved!"); ?>", "success");
                            } else {
                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your category has NOT been saved!"); ?>", "error");
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
