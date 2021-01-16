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
                        <form id="panelMeet_scheduleForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Meet_scheduleid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="Meet_scheduleusers_id"><?php echo __("Users Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_id" id="Meet_scheduleusers_id">
                                        <?php
                                        $options = Meet_schedule::getAllUsers();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['user'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="Meet_schedulestatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_schedulepublic"><?php echo __("Public"); ?>:</label>
                                    <input type="text" id="Meet_schedulepublic" name="public" class="form-control input-sm" placeholder="<?php echo __("Public"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_schedulelive_stream"><?php echo __("Live Stream"); ?>:</label>
                                    <input type="text" id="Meet_schedulelive_stream" name="live_stream" class="form-control input-sm" placeholder="<?php echo __("Live Stream"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_schedulepassword"><?php echo __("Password"); ?>:</label>
                                    <input type="text" id="Meet_schedulepassword" name="password" class="form-control input-sm" placeholder="<?php echo __("Password"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_scheduletopic"><?php echo __("Topic"); ?>:</label>
                                    <input type="text" id="Meet_scheduletopic" name="topic" class="form-control input-sm" placeholder="<?php echo __("Topic"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_schedulestarts"><?php echo __("Starts"); ?>:</label>
                                    <input type="text" id="Meet_schedulestarts" name="starts" class="form-control input-sm" placeholder="<?php echo __("Starts"); ?>" required="true" autocomplete="off">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_schedulefinish"><?php echo __("Finish"); ?>:</label>
                                    <input type="text" id="Meet_schedulefinish" name="finish" class="form-control input-sm" placeholder="<?php echo __("Finish"); ?>" required="true" autocomplete="off">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_schedulename"><?php echo __("Name"); ?>:</label>
                                    <input type="text" id="Meet_schedulename" name="name" class="form-control input-sm" placeholder="<?php echo __("Name"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Meet_schedulemeet_code"><?php echo __("Meet Code"); ?>:</label>
                                    <input type="text" id="Meet_schedulemeet_code" name="meet_code" class="form-control input-sm" placeholder="<?php echo __("Meet Code"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newMeet_scheduleLink" onclick="clearMeet_scheduleForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Meet_scheduleTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Public"); ?></th>
                                    <th><?php echo __("Live Stream"); ?></th>
                                    <th><?php echo __("Password"); ?></th>
                                    <th><?php echo __("Topic"); ?></th>
                                    <th><?php echo __("Starts"); ?></th>
                                    <th><?php echo __("Finish"); ?></th>
                                    <th><?php echo __("Name"); ?></th>
                                    <th><?php echo __("Meet Code"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Public"); ?></th>
                                    <th><?php echo __("Live Stream"); ?></th>
                                    <th><?php echo __("Password"); ?></th>
                                    <th><?php echo __("Topic"); ?></th>
                                    <th><?php echo __("Starts"); ?></th>
                                    <th><?php echo __("Finish"); ?></th>
                                    <th><?php echo __("Name"); ?></th>
                                    <th><?php echo __("Meet Code"); ?></th>
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
<div id="Meet_schedulebtnModelLinks" style="display:none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Meet_schedule btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Meet_schedule btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearMeet_scheduleForm() {
        $('#Meet_scheduleid').val('');
        $('#Meet_scheduleusers_id').val('');
        $('#Meet_schedulestatus').val('');
        $('#Meet_schedulepublic').val('');
        $('#Meet_schedulelive_stream').val('');
        $('#Meet_schedulepassword').val('');
        $('#Meet_scheduletopic').val('');
        $('#Meet_schedulestarts').val('');
        $('#Meet_schedulefinish').val('');
        $('#Meet_schedulename').val('');
        $('#Meet_schedulemeet_code').val('');
    }
    $(document).ready(function () {
        $('#addMeet_scheduleBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/addMeet_scheduleVideo.php',
                data: $('#panelMeet_scheduleForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelMeet_scheduleForm").trigger("reset");
                    }
                    clearMeet_scheduleForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Meet_scheduletableVar = $('#Meet_scheduleTable').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_schedule/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "status"},
                {"data": "public"},
                {"data": "live_stream"},
                {"data": "password"},
                {"data": "topic"},
                {"data": "starts"},
                {"data": "finish"},
                {"data": "name"},
                {"data": "meet_code"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Meet_schedulebtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newMeet_schedule').on('click', function (e) {
            e.preventDefault();
            $('#panelMeet_scheduleForm').trigger("reset");
            $('#Meet_scheduleid').val('');
        });
        $('#panelMeet_scheduleForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_schedule/add.json.php',
                data: $('#panelMeet_scheduleForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelMeet_scheduleForm").trigger("reset");
                    }
                    Meet_scheduletableVar.ajax.reload();
                    $('#Meet_scheduleid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Meet_scheduleTable').on('click', 'button.delete_Meet_schedule', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Meet_scheduletableVar.row(tr).data();
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
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_schedule/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                }
                                Meet_scheduletableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Meet_scheduleTable').on('click', 'button.edit_Meet_schedule', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Meet_scheduletableVar.row(tr).data();
            $('#Meet_scheduleid').val(data.id);
            $('#Meet_scheduleusers_id').val(data.users_id);
            $('#Meet_schedulestatus').val(data.status);
            $('#Meet_schedulepublic').val(data.public);
            $('#Meet_schedulelive_stream').val(data.live_stream);
            $('#Meet_schedulepassword').val(data.password);
            $('#Meet_scheduletopic').val(data.topic);
            $('#Meet_schedulestarts').val(data.starts);
            $('#Meet_schedulefinish').val(data.finish);
            $('#Meet_schedulename').val(data.name);
            $('#Meet_schedulemeet_code').val(data.meet_code);
        });
    });
</script>
<script> $(document).ready(function () {
        $('#Meet_schedulestarts').datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true});
    });</script>
<script> $(document).ready(function () {
        $('#Meet_schedulefinish').datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true});
    });</script>