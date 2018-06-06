<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage users"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/userGroups.php';
$userGroups = UserGroups::getAllUsersGroups();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Users"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
                    <?php
        include $global['systemRootPath'] . 'view/include/updateCheck.php';
        ?>
            <div class="btn-group" >
                <button type="button" class="btn btn-default" id="addUserBtn">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo __("New User"); ?>
                </button>
                <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups" class="btn btn-warning">
                    <span class="fa fa-users"></span> <?php echo __("User Groups"); ?>
                </a>
                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success">
                    <span class="fa fa-film"></span> <?php echo __("Videos"); ?>
                </a>
            </div>
            <table id="grid" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-column-id="user" data-formatter="user"><?php echo __("User"); ?></th>
                        <th data-column-id="name" data-order="desc"><?php echo __("Name"); ?></th>
                        <th data-column-id="email" ><?php echo __("E-mail"); ?></th>
                        <th data-column-id="created" ><?php echo __("Created"); ?></th>
                        <th data-column-id="modified" ><?php echo __("Modified"); ?></th>
                        <th data-column-id="tags" data-formatter="tags"  data-sortable="false" ><?php echo __("Tags"); ?></th>
                        <th data-column-id="commands" data-formatter="commands" data-sortable="false" data-width="50px"></th>
                    </tr>
                </thead>
            </table>

            <div id="userFormModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><?php echo __("User Form"); ?></h4>
                        </div>
                        <div class="modal-body">
                            <form class="form-compact"  id="updateUserForm" onsubmit="">
                                <input type="hidden" id="inputUserId"  >
                                <label for="inputUser" class="sr-only"><?php echo __("User"); ?></label>
                                <input type="text" id="inputUser" class="form-control first" placeholder="<?php echo __("User"); ?>" autofocus required="required">
                                <label for="inputPassword" class="sr-only"><?php echo __("Password"); ?></label>
                                <input type="password" id="inputPassword" class="form-control" placeholder="<?php echo __("Password"); ?>" required="required">
                                <label for="inputEmail" class="sr-only"><?php echo __("E-mail"); ?></label>
                                <input type="email" id="inputEmail" class="form-control" placeholder="<?php echo __("E-mail"); ?>" >
                                <label for="inputName" class="sr-only"><?php echo __("Name"); ?></label>
                                <input type="text" id="inputName" class="form-control " placeholder="<?php echo __("Name"); ?>" >
                                <label for="inputChannelName" class="sr-only"><?php echo __("Channel Name"); ?></label>
                                <input type="text" id="inputChannelName" class="form-control last" placeholder="<?php echo __("Channel Name"); ?>" >
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <?php echo __("is Admin"); ?>
                                        <div class="material-switch pull-right">
                                            <input type="checkbox" value="isAdmin" id="isAdmin"/>
                                            <label for="isAdmin" class="label-success"></label>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <?php echo __("Can Stream Videos"); ?>
                                        <div class="material-switch pull-right">
                                            <input type="checkbox" value="canStream" id="canStream"/>
                                            <label for="canStream" class="label-success"></label>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <?php echo __("Can Upload Videos"); ?>
                                        <div class="material-switch pull-right">
                                            <input type="checkbox" value="canUpload" id="canUpload"/>
                                            <label for="canUpload" class="label-success"></label>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <?php echo __("is Active"); ?>
                                        <div class="material-switch pull-right">
                                            <input type="checkbox" value="status" id="status"/>
                                            <label for="status" class="label-success"></label>
                                        </div>
                                    </li>
                                </ul>
                                <ul class="list-group">
                                    <li class="list-group-item active">
                                        <?php echo __("User Groups"); ?>
                                        <a href="#" class="btn btn-info btn-xs pull-right" data-toggle="popover" title="<?php echo __("What is User Groups"); ?>" data-placement="bottom"  data-content="<?php echo __("By associating groups with this user, they will be able to see all the videos that are related to this group"); ?>"><span class="fa fa-question-circle" aria-hidden="true"></span> <?php echo __("Help"); ?></a>
                                    </li>
                                    <?php
                                    foreach ($userGroups as $value) {
                                        ?>
                                        <li class="list-group-item">
                                            <span class="fa fa-unlock"></span>
                                            <?php echo $value['group_name']; ?>
                                            <span class="label label-info"><?php echo $value['total_videos']; ?> <?php echo __("Videos linked"); ?></span>
                                            <div class="material-switch pull-right">
                                                <input id="userGroup<?php echo $value['id']; ?>" type="checkbox" value="<?php echo $value['id']; ?>" class="userGroups"/>
                                                <label for="userGroup<?php echo $value['id']; ?>" class="label-warning"></label>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                            <button type="button" class="btn btn-primary" id="saveUserBtn"><?php echo __("Save changes"); ?></button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

        </div><!--/.container-->

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

        <script>
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
                    url: "<?php echo $global['webSiteRootURL'] . "objects/users.json.php"; ?>",
                    formatters: {
                        "commands": function (column, row) {
                            var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                            //var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '  data-toggle="tooltip" data-placement="left" title="Delete""><span class="glyphicon glyphicon-erase" aria-hidden="true"></span></button>';
                            //return editBtn + deleteBtn;
                            return editBtn;
                        },
                        "tags": function (column, row) {
                            var tags = "";
                            for (var i in row.tags) {
                                if (typeof row.tags[i].type == "undefined") {
                                    continue;
                                }
                                tags += "<span class=\"label label-" + row.tags[i].type + " fix-width\">" + row.tags[i].text + "</span><br>";
                            }
                            return tags;
                        },
                        "user": function (column, row) {
                            var photo = "";
                            if (row.photoURL) {
                                photo = "<br><img src='" + row.photoURL + "' class='img img-responsive img-rounded img-thumbnail' style='max-width:50px;'/>";
                            }
                            return row.user + photo;
                        }
                    }
                }).on("loaded.rs.jquery.bootgrid", function ()
                {
                    /* Executes after data is loaded and rendered */
                    grid.find(".command-edit").on("click", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);

                        $('#inputUserId').val(row.id);
                        $('#inputUser').val(row.user);
                        $('#inputPassword').val('');
                        $('#inputEmail').val(row.email);
                        $('#inputName').val(row.name);
                        $('#inputChannelName').val(row.channelName);

                        $('.userGroups').prop('checked', false);
                        for (var index in row.groups) {
                            $('#userGroup' + row.groups[index].id).prop('checked', true);
                        }
                        $('#isAdmin').prop('checked', (row.isAdmin == "1" ? true : false));
                        $('#canStream').prop('checked', (row.canStream == "1" ? true : false));
                        $('#canUpload').prop('checked', (row.canUpload == "1" ? true : false));
                        $('#status').prop('checked', (row.status === "a" ? true : false));

                        $('#userFormModal').modal();
                    }).end().find(".command-delete").on("click", function (e) {
                        /*
                         var row_index = $(this).closest('tr').index();
                         var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                         console.log(row);
                         swal({
                         title: "<?php echo __("Are you sure?"); ?>",
                         text: "<?php echo __("You will not be able to recover this user!"); ?>",
                         type: "warning",
                         showCancelButton: true,
                         confirmButtonColor: "#DD6B55",
                         confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                         closeOnConfirm: false
                         },
                         function () {

                         modal.showPleaseWait();
                         $.ajax({
                         url: '<?php echo $global['webSiteRootURL']; ?>objects/userDelete.json.php',
                         data: {"id": row.id},
                         type: 'post',
                         success: function (response) {
                         if (response.status === "1") {
                         $("#grid").bootgrid("reload");
                         swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your user has been deleted!"); ?>", "success");
                         } else {
                         swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been deleted!"); ?>", "error");
                         }
                         modal.hidePleaseWait();
                         }
                         });
                         });
                         */
                    });
                });



                $('#addUserBtn').click(function (evt) {
                    $('#inputUserId').val('');
                    $('#inputUser').val('');
                    $('#inputPassword').val('');
                    $('#inputEmail').val('');
                    $('#inputName').val('');
                    $('#inputChannelName').val('');
                    $('#isAdmin').prop('checked', false);
                    $('#canStream').prop('checked', false);
                    $('#canUpload').prop('checked', false);
                    $('.userGroups').prop('checked', false);
                    $('#status').prop('checked', true);

                    $('#userFormModal').modal();
                });

                $('#saveUserBtn').click(function (evt) {
                    $('#updateUserForm').submit();
                });

                $('#updateUserForm').submit(function (evt) {
                    evt.preventDefault();
                    modal.showPleaseWait();
                    var selectedUserGroups = [];
                    $('.userGroups:checked').each(function () {
                        selectedUserGroups.push($(this).val());
                    });

                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/userAddNew.json.php',
                        data: {
                            "id": $('#inputUserId').val(),
                            "user": $('#inputUser').val(),
                            "pass": $('#inputPassword').val(),
                            "email": $('#inputEmail').val(),
                            "name": $('#inputName').val(),
                            "channelName": $('#inputChannelName').val(),
                            "isAdmin": $('#isAdmin').is(':checked'),
                            "canStream": $('#canStream').is(':checked'),
                            "canUpload": $('#canUpload').is(':checked'),
                            "status": $('#status').is(':checked') ? 'a' : 'i',
                            "userGroups": selectedUserGroups
                        },
                        type: 'post',
                        success: function (response) {
                            if (response.status > "0") {
                                $('#userFormModal').modal('hide');
                                $("#grid").bootgrid("reload");
                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your user has been saved!"); ?>", "success");
                            } else if(response.error){
                                swal("<?php echo __("Sorry!"); ?>", response.error, "error");
                            } else {
                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been updated!"); ?>", "error");                                
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
