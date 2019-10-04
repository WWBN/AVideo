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
        <a href="<?php echo $global['webSiteRootURL']; ?>objects/getAllEmails.csv.php" class="btn btn-primary">
            <i class="fas fa-file-csv"></i> <?php echo __("CSV File"); ?>
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
                <th data-column-id="commands" data-formatter="commands" data-sortable="false" data-width="100px"></th>
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
                        <input type="text" id="inputChannelName" class="form-control" placeholder="<?php echo __("Channel Name"); ?>" >
                        <label for="inputAnalyticsCode" class="sr-only"><?php echo __("Analytics Code"); ?></label>
                        <input type="text" id="inputAnalyticsCode" class="form-control last" placeholder="Google Analytics Code: UA-123456789-1" >
                        <small>Do not paste the full javascript code, paste only the gtag id</small>
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
                                <?php echo __("Can view chart"); ?>
                                <div class="material-switch pull-right">
                                    <input type="checkbox" value="canViewChart" id="canViewChart"/>
                                    <label for="canViewChart" class="label-success"></label>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <?php echo __("E-mail Verified"); ?>
                                <div class="material-switch pull-right">
                                    <input type="checkbox" value="isEmailVerified" id="isEmailVerified"/>
                                    <label for="isEmailVerified" class="label-success"></label>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <?php echo __("is Active"); ?>
                                <div class="material-switch pull-right">
                                    <input type="checkbox" value="status" id="status"/>
                                    <label for="status" class="label-success"></label>
                                </div>
                            </li>
                            <?php
                            print YouPHPTubePlugin::getUserOptions();
                            ?>
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


    <div id="userInfoModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo __("User Info"); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label class="col-md-4 control-label"><?php echo __("First Name"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input  id="first_name" class="form-control"  type="text" readonly >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-md-4 control-label"><?php echo __("Last Name"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input  id="last_name" class="form-control" readonly >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-md-4 control-label"><?php echo __("Address"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input  id="address" class="form-control"  readonly >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-md-4 control-label"><?php echo __("Zip Code"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input  id="zip_code" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-md-4 control-label"><?php echo __("Country"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input  id="country" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-md-4 control-label"><?php echo __("Region"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input  id="region" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-md-4 control-label"><?php echo __("City"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input  id="city" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <label class="col-md-4 control-label"><?php echo __("Document"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <img src="" class="img img-responsive img-thumbnail" id="documentImage"/>
                            </div>
                        </div>
                    </div>

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

</div><!--/.container-->

<script>
    function isAnalytics() {
        str = $('#inputAnalyticsCode').val();
        return true;
        //return str === '' || (/^ua-\d{4,9}-\d{1,4}$/i).test(str.toString());
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
            url: "<?php echo $global['webSiteRootURL'] . "objects/users.json.php"; ?>",
            formatters: {
                "commands": function (column, row) {
                    var editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button>'
                    var infoBtn = '<button type="button" class="btn btn-xs btn-default command-info" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Info"><i class="fas fa-info-circle"></i></button>'
                    //var deleteBtn = '<button type="button" class="btn btn-default btn-xs command-delete"  data-row-id="' + row.id + '  data-toggle="tooltip" data-placement="left" title="Delete""><span class="glyphicon glyphicon-erase" aria-hidden="true"></span></button>';
                    var pluginsButtons = '<br><?php echo YouPHPTubePlugin::getUsersManagerListButton(); ?>';
                    return editBtn + infoBtn+pluginsButtons;
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
                        photo = "<br><img src='" + row.photo + "' class='img img-responsive img-rounded img-thumbnail' style='max-width:50px;'/>";
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
                $('#inputAnalyticsCode').val(row.analyticsCode);

                $('.userGroups').prop('checked', false);
                for (var index in row.groups) {
                    $('#userGroup' + row.groups[index].id).prop('checked', true);
                }
                $('#isAdmin').prop('checked', (row.isAdmin == "1" ? true : false));
                $('#canStream').prop('checked', (row.canStream == "1" ? true : false));
                $('#canUpload').prop('checked', (row.canUpload == "1" ? true : false));
                $('#canViewChart').prop('checked', (row.canViewChart == "1" ? true : false));
                $('#status').prop('checked', (row.status === "a" ? true : false));
                $('#isEmailVerified').prop('checked', (row.isEmailVerified == "1" ? true : false));
<?php
print YouPHPTubePlugin::loadUsersFormJS();
?>

                $('#userFormModal').modal();
            }).end().find(".command-info").on("click", function (e) {

                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                console.log(row);
                modal.showPleaseWait();
                $('#first_name').val(row.first_name);
                $('#last_name').val(row.last_name);
                $('#address').val(row.address);
                $('#zip_code').val(row.zip_code);
                $('#country').val(row.country);
                $('#region').val(row.region);
                $('#city').val(row.city);
                $('#documentImage').attr('src', '<?php echo $global['webSiteRootURL']; ?>objects/userDocument.png.php?users_id=' + row.id);
                $('#userInfoModal').modal();
                modal.hidePleaseWait();

            });
        });



        $('#addUserBtn').click(function (evt) {
            $('#inputUserId').val('');
            $('#inputUser').val('');
            $('#inputPassword').val('');
            $('#inputEmail').val('');
            $('#inputName').val('');
            $('#inputChannelName').val('');
            $('#inputAnalyticsCode').val('');
            $('#isAdmin').prop('checked', false);
            $('#canStream').prop('checked', false);
            $('#canUpload').prop('checked', false);
            $('#canViewChart').prop('checked', false);
            $('.userGroups').prop('checked', false);
            $('#status').prop('checked', true);
            $('#isEmailVerified').prop('checked', false);
<?php
print YouPHPTubePlugin::addUserBtnJS();
?>
            $('#userFormModal').modal();
        });

        $('#saveUserBtn').click(function (evt) {
            $('#updateUserForm').submit();
        });

        $('#updateUserForm').submit(function (evt) {
        evt.preventDefault();
                if (!isAnalytics()){
        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your analytics code is wrong"); ?>", "error");
                $('#inputAnalyticsCode').focus();
                return false;
        }

        modal.showPleaseWait();
                var selectedUserGroups = [];
                $('.userGroups:checked').each(function () {
        selectedUserGroups.push($(this).val());
        });
                $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/userAddNew.json.php',
                        data: {
<?php
print YouPHPTubePlugin::updateUserFormJS();
?>
                        "id": $('#inputUserId').val(),
                                "user": $('#inputUser').val(),
                                "pass": $('#inputPassword').val(),
                                "email": $('#inputEmail').val(),
                                "name": $('#inputName').val(),
                                "channelName": $('#inputChannelName').val(),
                                "analyticsCode": $('#inputAnalyticsCode').val(),
                                "isAdmin": $('#isAdmin').is(':checked'),
                                "canStream": $('#canStream').is(':checked'),
                                "canUpload": $('#canUpload').is(':checked'),
                                "canViewChart": $('#canViewChart').is(':checked'),
                                "status": $('#status').is(':checked') ? 'a' : 'i',
                                "isEmailVerified": $('#isEmailVerified').is(':checked'),
                                "userGroups": selectedUserGroups
                        },
                        type: 'post',
                        success: function (response) {
                        if (response.status > "0") {
                        $('#userFormModal').modal('hide');
                                $("#grid").bootgrid("reload");
                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your user has been saved!"); ?>", "success");
                        } else if (response.error){
                        swal("<?php echo __("Sorry!"); ?>", response.error, "error");
                        } else {
                        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been updated!"); ?>", "error");
                        }
                        modal.hidePleaseWait();
                        }
                });
                return false;
        }
        );
    });

</script>