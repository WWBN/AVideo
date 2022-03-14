<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
?>


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
                        <form id="panelUsers_groups_permissionsForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Users_groups_permissionsid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="Users_groups_permissionsname"><?php echo __("Name"); ?>:</label>
                                    <input type="text" id="Users_groups_permissionsname" name="name" class="form-control input-sm" placeholder="<?php echo __("Name"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Users_groups_permissionsusers_groups_id"><?php echo __("Users Groups Id1"); ?>:</label>
                                    <select class="form-control input-sm" name="users_groups_id" id="Users_groups_permissionsusers_groups_id">
                                        <?php
                                        $options = Users_groups_permissions::getAllUsers_groups();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Users_groups_permissionsplugins_id"><?php echo __("Plugins Id"); ?>:</label>
                                    <select class="form-control input-sm" name="plugins_id" id="Users_groups_permissionsplugins_id">
                                        <?php
                                        $options = Users_groups_permissions::getAllPlugins();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Users_groups_permissionstype"><?php echo __("Type"); ?>:</label>
                                    <input type="number" step="1" id="Users_groups_permissionstype" name="type" class="form-control input-sm" placeholder="<?php echo __("Type"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="Users_groups_permissionsstatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newUsers_groups_permissionsLink" onclick="clearUsers_groups_permissionsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Users_groups_permissionsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Name"); ?></th>
                                    <th><?php echo __("Type"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Name"); ?></th>
                                    <th><?php echo __("Type"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
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
<div id="Users_groups_permissionsbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Users_groups_permissions btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Users_groups_permissions btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearUsers_groups_permissionsForm() {
        $('#Users_groups_permissionsid').val('');
        $('#Users_groups_permissionsname').val('');
        $('#Users_groups_permissionsusers_groups_id').val('');
        $('#Users_groups_permissionsplugins_id').val('');
        $('#Users_groups_permissionstype').val('');
        $('#Users_groups_permissionsstatus').val('');
    }
    $(document).ready(function () {
        $('#addUsers_groups_permissionsBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Permissions/View/addUsers_groups_permissionsVideo.php',
                data: $('#panelUsers_groups_permissionsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelUsers_groups_permissionsForm").trigger("reset");
                    }
                    clearUsers_groups_permissionsForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Users_groups_permissionstableVar = $('#Users_groups_permissionsTable').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Permissions/View/Users_groups_permissions/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "name"},
                {"data": "type"},
                {"data": "status"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Users_groups_permissionsbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newUsers_groups_permissions').on('click', function (e) {
            e.preventDefault();
            $('#panelUsers_groups_permissionsForm').trigger("reset");
            $('#Users_groups_permissionsid').val('');
        });
        $('#panelUsers_groups_permissionsForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Permissions/View/Users_groups_permissions/add.json.php',
                data: $('#panelUsers_groups_permissionsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelUsers_groups_permissionsForm").trigger("reset");
                    }
                    Users_groups_permissionstableVar.ajax.reload();
                    $('#Users_groups_permissionsid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Users_groups_permissionsTable').on('click', 'button.delete_Users_groups_permissions', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Users_groups_permissionstableVar.row(tr).data();
            swal({
                title: "<?php echo __("Are you sure?"); ?>",
                text: "<?php echo __("You will not be able to recover this action!"); ?>",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                    .then((willDelete) => {
                        if (willDelete) {
                            modal.showPleaseWait();
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/Permissions/View/Users_groups_permissions/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    swal("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                }
                                Users_groups_permissionstableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Users_groups_permissionsTable').on('click', 'button.edit_Users_groups_permissions', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Users_groups_permissionstableVar.row(tr).data();
            $('#Users_groups_permissionsid').val(data.id);
            $('#Users_groups_permissionsname').val(data.name);
            $('#Users_groups_permissionsusers_groups_id').val(data.users_groups_id);
            $('#Users_groups_permissionsplugins_id').val(data.plugins_id);
            $('#Users_groups_permissionstype').val(data.type);
            $('#Users_groups_permissionsstatus').val(data.status);
        });
    });
</script>
