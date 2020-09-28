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
                        <form id="panelMeet_join_logForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Meet_join_logid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="Meet_join_logmeet_schedule_id"><?php echo __("Meet Schedule Id"); ?>:</label>
                                    <select class="form-control input-sm" name="meet_schedule_id" id="Meet_join_logmeet_schedule_id">
                                        <?php
                                        $options = Meet_join_log::getAllMeet_schedule();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_join_logusers_id"><?php echo __("Users Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_id" id="Meet_join_logusers_id">
                                        <?php
                                        $options = Meet_join_log::getAllUsers();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_join_logip"><?php echo __("Ip"); ?>:</label>
                                    <input type="text" id="Meet_join_logip" name="ip" class="form-control input-sm" placeholder="<?php echo __("Ip"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_join_loguser_agent"><?php echo __("User Agent"); ?>:</label>
                                    <input type="text" id="Meet_join_loguser_agent" name="user_agent" class="form-control input-sm" placeholder="<?php echo __("User Agent"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newMeet_join_logLink" onclick="clearMeet_join_logForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Meet_join_logTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Ip"); ?></th>
                                    <th><?php echo __("User Agent"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Ip"); ?></th>
                                    <th><?php echo __("User Agent"); ?></th>
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
<div id="Meet_join_logbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Meet_join_log btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Meet_join_log btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearMeet_join_logForm() {
        $('#Meet_join_logid').val('');
        $('#Meet_join_logmeet_schedule_id').val('');
        $('#Meet_join_logusers_id').val('');
        $('#Meet_join_logip').val('');
        $('#Meet_join_loguser_agent').val('');
    }
    $(document).ready(function () {
        $('#addMeet_join_logBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/addMeet_join_logVideo.php',
                data: $('#panelMeet_join_logForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelMeet_join_logForm").trigger("reset");
                    }
                    clearMeet_join_logForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Meet_join_logtableVar = $('#Meet_join_logTable').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_join_log/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "ip"},
                {"data": "user_agent"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Meet_join_logbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newMeet_join_log').on('click', function (e) {
            e.preventDefault();
            $('#panelMeet_join_logForm').trigger("reset");
            $('#Meet_join_logid').val('');
        });
        $('#panelMeet_join_logForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_join_log/add.json.php',
                data: $('#panelMeet_join_logForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelMeet_join_logForm").trigger("reset");
                    }
                    Meet_join_logtableVar.ajax.reload();
                    $('#Meet_join_logid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Meet_join_logTable').on('click', 'button.delete_Meet_join_log', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Meet_join_logtableVar.row(tr).data();
            swal({
                title: "<?php echo __("Are you sure?"); ?>",
                text: "<?php echo __("You will not be able to recover this action!"); ?>",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                    .then(function(willDelete) {
                        if (willDelete) {
                            modal.showPleaseWait();
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_join_log/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                }
                                Meet_join_logtableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Meet_join_logTable').on('click', 'button.edit_Meet_join_log', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Meet_join_logtableVar.row(tr).data();
            $('#Meet_join_logid').val(data.id);
            $('#Meet_join_logmeet_schedule_id').val(data.meet_schedule_id);
            $('#Meet_join_logusers_id').val(data.users_id);
            $('#Meet_join_logip').val(data.ip);
            $('#Meet_join_loguser_agent').val(data.user_agent);
        });
    });
</script>
