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
                        <form id="panelUsers_login_historyForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Users_login_historyid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="Users_login_historyusers_id"><?php echo __("Users Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_id" id="Users_login_historyusers_id">
                                        <?php
                                        $options = Users_login_history::getAllUsers();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Users_login_historyuniqidV4"><?php echo __("UniqidV4"); ?>:</label>
                                    <input type="text" id="Users_login_historyuniqidV4" name="uniqidV4" class="form-control input-sm" placeholder="<?php echo __("UniqidV4"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Users_login_historyip"><?php echo __("Ip"); ?>:</label>
                                    <input type="text" id="Users_login_historyip" name="ip" class="form-control input-sm" placeholder="<?php echo __("Ip"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Users_login_historyuser_agent"><?php echo __("User Agent"); ?>:</label>
                                    <input type="text" id="Users_login_historyuser_agent" name="user_agent" class="form-control input-sm" placeholder="<?php echo __("User Agent"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Users_login_historyconfirmation_code"><?php echo __("Confirmation Code"); ?>:</label>
                                    <input type="text" id="Users_login_historyconfirmation_code" name="confirmation_code" class="form-control input-sm" placeholder="<?php echo __("Confirmation Code"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="Users_login_historystatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newUsers_login_historyLink" onclick="clearUsers_login_historyForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Users_login_historyTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("UniqidV4"); ?></th>
                                    <th><?php echo __("Ip"); ?></th>
                                    <th><?php echo __("User Agent"); ?></th>
                                    <th><?php echo __("Confirmation Code"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("UniqidV4"); ?></th>
                                    <th><?php echo __("Ip"); ?></th>
                                    <th><?php echo __("User Agent"); ?></th>
                                    <th><?php echo __("Confirmation Code"); ?></th>
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
<div id="Users_login_historybtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Users_login_history btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Users_login_history btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearUsers_login_historyForm() {
        $('#Users_login_historyid').val('');
        $('#Users_login_historyusers_id').val('');
        $('#Users_login_historyuniqidV4').val('');
        $('#Users_login_historyip').val('');
        $('#Users_login_historyuser_agent').val('');
        $('#Users_login_historyconfirmation_code').val('');
        $('#Users_login_historystatus').val('');
    }
    $(document).ready(function () {
        $('#addUsers_login_historyBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/LoginControl/View/addUsers_login_historyVideo.php',
                data: $('#panelUsers_login_historyForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelUsers_login_historyForm").trigger("reset");
                    }
                    clearUsers_login_historyForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Users_login_historytableVar = $('#Users_login_historyTable').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/LoginControl/View/Users_login_history/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "uniqidV4"},
                {"data": "ip"},
                {"data": "user_agent"},
                {"data": "confirmation_code"},
                {"data": "status"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Users_login_historybtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newUsers_login_history').on('click', function (e) {
            e.preventDefault();
            $('#panelUsers_login_historyForm').trigger("reset");
            $('#Users_login_historyid').val('');
        });
        $('#panelUsers_login_historyForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/LoginControl/View/Users_login_history/add.json.php',
                data: $('#panelUsers_login_historyForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelUsers_login_historyForm").trigger("reset");
                    }
                    Users_login_historytableVar.ajax.reload();
                    $('#Users_login_historyid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Users_login_historyTable').on('click', 'button.delete_Users_login_history', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Users_login_historytableVar.row(tr).data();
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
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/LoginControl/View/Users_login_history/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    swal("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                }
                                Users_login_historytableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Users_login_historyTable').on('click', 'button.edit_Users_login_history', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Users_login_historytableVar.row(tr).data();
            $('#Users_login_historyid').val(data.id);
            $('#Users_login_historyusers_id').val(data.users_id);
            $('#Users_login_historyuniqidV4').val(data.uniqidV4);
            $('#Users_login_historyip').val(data.ip);
            $('#Users_login_historyuser_agent').val(data.user_agent);
            $('#Users_login_historyconfirmation_code').val(data.confirmation_code);
            $('#Users_login_historystatus').val(data.status);
        });
    });
</script>
