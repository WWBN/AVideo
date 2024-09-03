<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('Admins only');
}
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fas fa-cog"></i> <?php echo __("Configurations"); ?>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="panel panel-default ">
                        <div class="panel-heading"><i class="far fa-plus-square"></i> <?php echo __("Create"); ?></div>
                        <div class="panel-body">
                            <form id="panelUsers_connectionsForm">
                                <div class="row">
                                    <input type="hidden" name="id" id="Users_connectionsid" value="">
                                    <div class="form-group col-sm-12">
                                        <label for="Users_connectionsusers_id1"><?php echo __("Users Id1"); ?>:</label>
                                        <select class="form-control input-sm" name="users_id1" id="Users_connectionsusers_id1">
                                            <?php
                                            $options = Users_connections::getAllUsers();
                                            foreach ($options as $value) {
                                                echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="Users_connectionsusers_id2"><?php echo __("Users Id2"); ?>:</label>
                                        <select class="form-control input-sm" name="users_id2" id="Users_connectionsusers_id2">
                                            <?php
                                            $options = Users_connections::getAllUsers();
                                            foreach ($options as $value) {
                                                echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="Users_connectionsuser1_status"><?php echo __("User1 Status"); ?>:</label>
                                        <input type="text" id="Users_connectionsuser1_status" name="user1_status" class="form-control input-sm" placeholder="<?php echo __("User1 Status"); ?>" required="true">
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="Users_connectionsuser2_status"><?php echo __("User2 Status"); ?>:</label>
                                        <input type="text" id="Users_connectionsuser2_status" name="user2_status" class="form-control input-sm" placeholder="<?php echo __("User2 Status"); ?>" required="true">
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="Users_connectionsuser1_mute"><?php echo __("User1 Mute"); ?>:</label>
                                        <input type="number" step="1" id="Users_connectionsuser1_mute" name="user1_mute" class="form-control input-sm" placeholder="<?php echo __("User1 Mute"); ?>" required="true">
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="Users_connectionsuser2_mute"><?php echo __("User2 Mute"); ?>:</label>
                                        <input type="number" step="1" id="Users_connectionsuser2_mute" name="user2_mute" class="form-control input-sm" placeholder="<?php echo __("User2 Mute"); ?>" required="true">
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="Users_connectionscreated_php_time"><?php echo __("Created Php Time"); ?>:</label>
                                        <input type="text" id="Users_connectionscreated_php_time" name="created_php_time" class="form-control input-sm" placeholder="<?php echo __("Created Php Time"); ?>" required="true">
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="Users_connectionsmodified_php_time"><?php echo __("Modified Php Time"); ?>:</label>
                                        <input type="text" id="Users_connectionsmodified_php_time" name="modified_php_time" class="form-control input-sm" placeholder="<?php echo __("Modified Php Time"); ?>" required="true">
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="Users_connectionsjson"><?php echo __("Json"); ?>:</label>
                                        <textarea id="Users_connectionsjson" name="json" class="form-control input-sm" placeholder="<?php echo __("Json"); ?>" required="true"></textarea>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <div class="btn-group pull-right">
                                            <span class="btn btn-success" id="newUsers_connectionsLink" onclick="clearUsers_connectionsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
                                            <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="panel panel-default ">
                        <div class="panel-heading"><i class="fas fa-edit"></i> <?php echo __("Edit"); ?></div>
                        <div class="panel-body">
                            <table id="Users_connectionsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo __("User1 Status"); ?></th>
                                        <th><?php echo __("User2 Status"); ?></th>
                                        <th><?php echo __("User1 Mute"); ?></th>
                                        <th><?php echo __("User2 Mute"); ?></th>
                                        <th><?php echo __("Created Php Time"); ?></th>
                                        <th><?php echo __("Modified Php Time"); ?></th>
                                        <th><?php echo __("Json"); ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo __("User1 Status"); ?></th>
                                        <th><?php echo __("User2 Status"); ?></th>
                                        <th><?php echo __("User1 Mute"); ?></th>
                                        <th><?php echo __("User2 Mute"); ?></th>
                                        <th><?php echo __("Created Php Time"); ?></th>
                                        <th><?php echo __("Modified Php Time"); ?></th>
                                        <th><?php echo __("Json"); ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="Users_connectionsbtnModelLinks" style="display: none;">
        <div class="btn-group pull-right">
            <button href="" class="edit_Users_connections btn btn-default btn-xs">
                <i class="fa fa-edit"></i>
            </button>
            <button href="" class="delete_Users_connections btn btn-danger btn-xs">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>
</div>
<script type="text/javascript">
    function clearUsers_connectionsForm() {
        $('#Users_connectionsid').val('');
        $('#Users_connectionsusers_id1').val('');
        $('#Users_connectionsusers_id2').val('');
        $('#Users_connectionsuser1_status').val('');
        $('#Users_connectionsuser2_status').val('');
        $('#Users_connectionsuser1_mute').val('');
        $('#Users_connectionsuser2_mute').val('');
        $('#Users_connectionscreated_php_time').val('');
        $('#Users_connectionsmodified_php_time').val('');
        $('#Users_connectionsjson').val('');
    }
    $(document).ready(function() {
        $('#addUsers_connectionsBtn').click(function() {
            $.ajax({
                url: webSiteRootURL + 'plugin/UserConnections/View/addUsers_connectionsVideo.php',
                data: $('#panelUsers_connectionsForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelUsers_connectionsForm").trigger("reset");
                    }
                    clearUsers_connectionsForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Users_connectionstableVar = $('#Users_connectionsTable').DataTable({
            serverSide: true,
            "ajax": webSiteRootURL + "plugin/UserConnections/View/Users_connections/list.json.php",
            "columns": [{
                    "data": "id"
                },
                {
                    "data": "user1_status"
                },
                {
                    "data": "user2_status"
                },
                {
                    "data": "user1_mute"
                },
                {
                    "data": "user2_mute"
                },
                {
                    "data": "created_php_time"
                },
                {
                    "data": "modified_php_time"
                },
                {
                    "data": "json"
                },
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Users_connectionsbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newUsers_connections').on('click', function(e) {
            e.preventDefault();
            $('#panelUsers_connectionsForm').trigger("reset");
            $('#Users_connectionsid').val('');
        });
        $('#panelUsers_connectionsForm').on('submit', function(e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/UserConnections/View/Users_connections/add.json.php',
                data: $('#panelUsers_connectionsForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast(__("Your register has been saved!"));
                        $("#panelUsers_connectionsForm").trigger("reset");
                    }
                    Users_connectionstableVar.ajax.reload();
                    $('#Users_connectionsid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Users_connectionsTable').on('click', 'button.delete_Users_connections', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Users_connectionstableVar.row(tr).data();
            swal({
                    title: __("Are you sure?"),
                    text: __("You will not be able to recover this action!"),
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then(function(willDelete) {
                    if (willDelete) {
                        modal.showPleaseWait();
                        $.ajax({
                            type: "POST",
                            url: webSiteRootURL + "plugin/UserConnections/View/Users_connections/delete.json.php",
                            data: data

                        }).done(function(resposta) {
                            if (resposta.error) {
                                avideoAlertError(resposta.msg);
                            }
                            Users_connectionstableVar.ajax.reload();
                            modal.hidePleaseWait();
                        });
                    } else {

                    }
                });
        });
        $('#Users_connectionsTable').on('click', 'button.edit_Users_connections', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Users_connectionstableVar.row(tr).data();
            $('#Users_connectionsid').val(data.id);
            $('#Users_connectionsusers_id1').val(data.users_id1);
            $('#Users_connectionsusers_id2').val(data.users_id2);
            $('#Users_connectionsuser1_status').val(data.user1_status);
            $('#Users_connectionsuser2_status').val(data.user2_status);
            $('#Users_connectionsuser1_mute').val(data.user1_mute);
            $('#Users_connectionsuser2_mute').val(data.user2_mute);
            $('#Users_connectionscreated_php_time').val(data.created_php_time);
            $('#Users_connectionsmodified_php_time').val(data.modified_php_time);
            $('#Users_connectionsjson').val(data.json);
        });
    });
</script>