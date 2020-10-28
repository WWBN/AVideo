<div class="panel panel-default">
    <div class="panel-heading">
        <div class="btn-group" >
            <button type="button" class="btn btn-default" id="addUserGroupsBtn">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo __("New User Groups"); ?>
            </button>
            <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success">
                <span class="fa fa-film" aria-hidden="true"></span> <?php echo __("Videos"); ?>
            </a>
            <a href="<?php echo $global['webSiteRootURL']; ?>users" class="btn btn-primary">
                <span class="fa fa-user" aria-hidden="true"></span> <?php echo __("Users"); ?>
            </a>
            <a href="#" class="btn btn-info pull-right" data-toggle="popover" title="<?php echo __("What is User Groups"); ?>" data-placement="bottom"  data-content="<?php echo __("This is where you can create groups and associate them with your videos and users. This will make your videos private. Only users who are in the same group as the videos can view them"); ?>"><span class="fa fa-question-circle" aria-hidden="true"></span> <?php echo __("What is User Groups"); ?></a>
        </div>
    </div>
    <div class="panel-body">
        <table id="grid" class="table table-condensed table-hover table-striped">
            <thead>
                <tr>
                    <th data-column-id="group_name" data-order="asc"><?php echo __("Name"); ?></th>
                    <th data-column-id="created" data-width="150px"  ><?php echo __("Created"); ?></th>
                    <th data-column-id="modified" data-width="150px"  ><?php echo __("Modified"); ?></th>
                    <th data-column-id="commands" data-formatter="commands" data-sortable="false" data-width="100px"  ></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div id="groupFormModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __("User Groups Form"); ?></h4>
            </div>
            <div class="modal-body">
                <form class="form-compact"  id="updateUserGroupsForm" onsubmit="">
                    <input type="hidden" id="inputUserGroupsId" name="id"  >
                    <label for="inputName" class="sr-only"><?php echo __("Name"); ?></label>
                    <input type="text" id="inputName" name="group_name" class="form-control" placeholder="<?php echo __("Name"); ?>" required autofocus>

                    <?php
                    if(User::isAdmin()){
                    ?>
                    <hr>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php echo __("Group Permissions"); ?>
                        </div>
                        <div class="panel-body">
                            <?php
                            echo Permissions::getForm();
                            ?>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                <button type="button" class="btn btn-primary" id="saveUserGroupsBtn"><?php echo __("Save changes"); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</div><!--/.container-->
<div id="pluginsPermissionModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div id="pluginsPermissionModalContent">

        </div>
    </div>
</div>
<script>
    
    function pluginPermissionsBtn(plugins_id) {
        modal.showPleaseWait();
        $('#groupFormModal').modal('hide');
        $("#pluginsPermissionModalContent").html('');
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/Permissions/getPermissionsFromPlugin.html.php?plugins_id=' + plugins_id,
            success: function (response) {
                modal.hidePleaseWait();
                $("#pluginsPermissionModalContent").html(response);
                $('#pluginsPermissionModal').modal();
            }
        });
    }
    $(document).ready(function () {
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
            url: "<?php echo $global['webSiteRootURL'] . "objects/usersGroups.json.php"; ?>",
            formatters: {
                "commands": function (column, row)
                {
                    var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="<?php echo __('Edit'); ?>"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                    var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '  data-toggle="tooltip" data-placement="left" title="<?php echo __('Delete'); ?>""><i class="fa fa-trash"></i></button>';
                    return editBtn + deleteBtn;
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function () {
            /* Executes after data is loaded and rendered */
            grid.find(".command-edit").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                console.log(row);

                $('#inputUserGroupsId').val(row.id);
                $('#inputName').val(row.group_name);


                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/Permissions/getPermissions.json.php?users_groups_id=' + row.id,
                    success: function (response) {
                        console.log(response);
                        $(".permissions").prop("checked", false);
                        for (var key in response) {
                            if(typeof key !== 'string'){
                                continue;
                            }
                            for (var subkey in response[key]) {
                                if(typeof subkey !== 'string' || isNaN(subkey)){
                                    continue;
                                }
                                var selector = "."+key+"[value=\""+response[key][subkey]+"\"]";
                                console.log(selector, $(selector));
                                $(selector).prop("checked", true);
                            }
                        }
                        $('#groupFormModal').modal();
                        modal.hidePleaseWait();
                    }
                });


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
                                    url: '<?php echo $global['webSiteRootURL']; ?>objects/userGroupsDelete.json.php',
                                    data: {"id": row.id},
                                    type: 'post',
                                    success: function (response) {
                                        if (response.status === "1") {
                                            $("#grid").bootgrid("reload");
                                            avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your group has been deleted!"); ?>", "success");
                                        } else {
                                            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your group has NOT been deleted!"); ?>", "error");
                                        }
                                        modal.hidePleaseWait();
                                    }
                                });
                            }
                        });
            });
        });

        $('#addUserGroupsBtn').click(function (evt) {
            $('#inputUserGroupsId').val('');
            $('#inputName').val('');
            $('#inputCleanName').val('');
            $("#updateUserGroupsForm").trigger("reset");
            $(".permissions").prop("checked", false);
            $('#groupFormModal').modal();

        });

        $('#saveUserGroupsBtn').click(function (evt) {
            $('#updateUserGroupsForm').submit();
        });

        $('#updateUserGroupsForm').submit(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL'] . "objects/userGroupsAddNew.json.php"; ?>',
                data: $(this).serialize(),
                type: 'post',
                success: function (response) {
                    if (response.status) {
                        $('#groupFormModal').modal('hide');
                        $("#grid").bootgrid("reload");
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your group has been saved!"); ?>", "success");
                    } else {
                        avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your group has NOT been saved!"); ?>", "error");
                    }
                    modal.hidePleaseWait();
                }
            });
            return false;
        });
        $('[data-toggle="tooltip"]').tooltip();
    });

</script>
