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
                        <form id="panellogincontrol_historyForm">
                            <div class="row">
                                <input type="hidden" name="id" id="logincontrol_historyid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="logincontrol_historyusers_id"><?php echo __("Users Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_id" id="logincontrol_historyusers_id">
                                        <?php
                                        $options = logincontrol_history::getAllUsers();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">[' . $value['id'] . ']' . $value['user'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="logincontrol_historyuniqidV4"><?php echo __("UniqidV4"); ?>:</label>
                                    <input type="text" id="logincontrol_historyuniqidV4" name="uniqidV4" class="form-control input-sm" placeholder="<?php echo __("UniqidV4"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="logincontrol_historyip"><?php echo __("Ip"); ?>:</label>
                                    <input type="text" id="logincontrol_historyip" name="ip" class="form-control input-sm" placeholder="<?php echo __("Ip"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="logincontrol_historyuser_agent"><?php echo __("User Agent"); ?>:</label>
                                    <input type="text" id="logincontrol_historyuser_agent" name="user_agent" class="form-control input-sm" placeholder="<?php echo __("User Agent"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="logincontrol_historyconfirmation_code"><?php echo __("Confirmation Code"); ?>:</label>
                                    <input type="text" id="logincontrol_historyconfirmation_code" name="confirmation_code" class="form-control input-sm" placeholder="<?php echo __("Confirmation Code"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="logincontrol_historystatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newlogincontrol_historyLink" onclick="clearlogincontrol_historyForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="logincontrol_historyTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
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
<div id="logincontrol_historybtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_logincontrol_history btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_logincontrol_history btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearlogincontrol_historyForm() {
        $('#logincontrol_historyid').val('');
        $('#logincontrol_historyusers_id').val('');
        $('#logincontrol_historyuniqidV4').val('');
        $('#logincontrol_historyip').val('');
        $('#logincontrol_historyuser_agent').val('');
        $('#logincontrol_historyconfirmation_code').val('');
        $('#logincontrol_historystatus').val('');
    }
    $(document).ready(function () {
        $('#addlogincontrol_historyBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/LoginControl/View/addlogincontrol_historyVideo.php',
                data: $('#panellogincontrol_historyForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panellogincontrol_historyForm").trigger("reset");
                    }
                    clearlogincontrol_historyForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var logincontrol_historytableVar = $('#logincontrol_historyTable').DataTable({
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
                    defaultContent: $('#logincontrol_historybtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newlogincontrol_history').on('click', function (e) {
            e.preventDefault();
            $('#panellogincontrol_historyForm').trigger("reset");
            $('#logincontrol_historyid').val('');
        });
        $('#panellogincontrol_historyForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/LoginControl/View/Users_login_history/add.json.php',
                data: $('#panellogincontrol_historyForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panellogincontrol_historyForm").trigger("reset");
                    }
                    logincontrol_historytableVar.ajax.reload();
                    $('#logincontrol_historyid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#logincontrol_historyTable').on('click', 'button.delete_logincontrol_history', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = logincontrol_historytableVar.row(tr).data();
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
                                logincontrol_historytableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#logincontrol_historyTable').on('click', 'button.edit_logincontrol_history', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = logincontrol_historytableVar.row(tr).data();
            $('#logincontrol_historyid').val(data.id);
            $('#logincontrol_historyusers_id').val(data.users_id);
            $('#logincontrol_historyuniqidV4').val(data.uniqidV4);
            $('#logincontrol_historyip').val(data.ip);
            $('#logincontrol_historyuser_agent').val(data.user_agent);
            $('#logincontrol_historyconfirmation_code').val(data.confirmation_code);
            $('#logincontrol_historystatus').val(data.status);
        });
    });
</script>
