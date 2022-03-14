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
                        <form id="panelLive_scheduleForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Live_scheduleid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="Live_scheduletitle"><?php echo __("Title"); ?>:</label>
                                    <input type="text" id="Live_scheduletitle" name="title" class="form-control input-sm" placeholder="<?php echo __("Title"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_scheduledescription"><?php echo __("Description"); ?>:</label>
                                    <input type="text" id="Live_scheduledescription" name="description" class="form-control input-sm" placeholder="<?php echo __("Description"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_schedulekey"><?php echo __("Key"); ?>:</label>
                                    <input type="text" id="Live_schedulekey" name="key" class="form-control input-sm" placeholder="<?php echo __("Key"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_scheduleusers_id"><?php echo __("Users Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_id" id="Live_scheduleusers_id">
                                        <?php
                                        $options = Live_schedule::getAllUsers();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_schedulelive_servers_id"><?php echo __("Live Servers Id"); ?>:</label>
                                    <select class="form-control input-sm" name="live_servers_id" id="Live_schedulelive_servers_id">
                                        <?php
                                        $options = Live_schedule::getAllLive_servers();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_schedulescheduled_time"><?php echo __("Scheduled Time"); ?>:</label>
                                    <input type="text" id="Live_schedulescheduled_time" name="scheduled_time" class="form-control input-sm" placeholder="<?php echo __("Scheduled Time"); ?>" required="true" autocomplete="off"  readonly="readonly" >
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_schedulescheduled_password"><?php echo __("Scheduled Password"); ?>:</label>
                                    <input type="password" id="Live_schedulescheduled_password" name="scheduled_password" class="form-control input-sm" placeholder="<?php echo __("Scheduled Password"); ?>" autocomplete="off"  >
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_scheduletimezone"><?php echo __("Timezone"); ?>:</label>
                                    <input type="text" id="Live_scheduletimezone" name="timezone" class="form-control input-sm" placeholder="<?php echo __("Timezone"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="Live_schedulestatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_scheduleposter"><?php echo __("Poster"); ?>:</label>
                                    <input type="text" id="Live_scheduleposter" name="poster" class="form-control input-sm" placeholder="<?php echo __("Poster"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_schedulepublic"><?php echo __("Public"); ?>:</label>
                                    <input type="text" id="Live_schedulepublic" name="public" class="form-control input-sm" placeholder="<?php echo __("Public"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_schedulesaveTransmition"><?php echo __("SaveTransmition"); ?>:</label>
                                    <input type="text" id="Live_schedulesaveTransmition" name="saveTransmition" class="form-control input-sm" placeholder="<?php echo __("SaveTransmition"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Live_scheduleshowOnTV"><?php echo __("ShowOnTV"); ?>:</label>
                                    <input type="text" id="Live_scheduleshowOnTV" name="showOnTV" class="form-control input-sm" placeholder="<?php echo __("ShowOnTV"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newLive_scheduleLink" onclick="clearLive_scheduleForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Live_scheduleTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Title"); ?></th>
                                    <th><?php echo __("Description"); ?></th>
                                    <th><?php echo __("Key"); ?></th>
                                    <th><?php echo __("Scheduled Time"); ?></th>
                                    <th><?php echo __("Timezone"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Poster"); ?></th>
                                    <th><?php echo __("Public"); ?></th>
                                    <th><?php echo __("SaveTransmition"); ?></th>
                                    <th><?php echo __("ShowOnTV"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Title"); ?></th>
                                    <th><?php echo __("Description"); ?></th>
                                    <th><?php echo __("Key"); ?></th>
                                    <th><?php echo __("Scheduled Time"); ?></th>
                                    <th><?php echo __("Timezone"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Poster"); ?></th>
                                    <th><?php echo __("Public"); ?></th>
                                    <th><?php echo __("SaveTransmition"); ?></th>
                                    <th><?php echo __("ShowOnTV"); ?></th>
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
<div id="Live_schedulebtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Live_schedule btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Live_schedule btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearLive_scheduleForm() {
        $('#Live_scheduleid').val('');
        $('#Live_scheduletitle').val('');
        $('#Live_scheduledescription').val('');
        $('#Live_schedulekey').val('');
        $('#Live_scheduleusers_id').val('');
        $('#Live_schedulelive_servers_id').val('');
        $('#Live_schedulescheduled_time').val('');
        $('#Live_schedulescheduled_password').val('');
        $('#Live_scheduletimezone').val('');
        $('#Live_schedulestatus').val('');
        $('#Live_scheduleposter').val('');
        $('#Live_schedulepublic').val('');
        $('#Live_schedulesaveTransmition').val('');
        $('#Live_scheduleshowOnTV').val('');
    }
    $(document).ready(function () {
        $('#addLive_scheduleBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/addLive_scheduleVideo.php',
                data: $('#panelLive_scheduleForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelLive_scheduleForm").trigger("reset");
                    }
                    clearLive_scheduleForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Live_scheduletableVar = $('#Live_scheduleTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_schedule/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "title"},
                {"data": "description"},
                {"data": "key"},
                {"data": "scheduled_time"},
                {"data": "timezone"},
                {"data": "status"},
                {"data": "poster"},
                {"data": "public"},
                {"data": "saveTransmition"},
                {"data": "showOnTV"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Live_schedulebtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newLive_schedule').on('click', function (e) {
            e.preventDefault();
            $('#panelLive_scheduleForm').trigger("reset");
            $('#Live_scheduleid').val('');
        });
        $('#panelLive_scheduleForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_schedule/add.json.php',
                data: $('#panelLive_scheduleForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelLive_scheduleForm").trigger("reset");
                    }
                    Live_scheduletableVar.ajax.reload();
                    $('#Live_scheduleid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Live_scheduleTable').on('click', 'button.delete_Live_schedule', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Live_scheduletableVar.row(tr).data();
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
                                type: "POST",
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_schedule/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    avideoAlertError(resposta.msg);
                                }
                                Live_scheduletableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Live_scheduleTable').on('click', 'button.edit_Live_schedule', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Live_scheduletableVar.row(tr).data();
            $('#Live_scheduleid').val(data.id);
            $('#Live_scheduletitle').val(data.title);
            $('#Live_scheduledescription').val(data.description);
            $('#Live_schedulekey').val(data.key);
            $('#Live_scheduleusers_id').val(data.users_id);
            $('#Live_schedulelive_servers_id').val(data.live_servers_id);
            $('#Live_schedulescheduled_time').val(data.scheduled_time);
            $('#Live_schedulescheduled_password').val(data.scheduled_password);
            $('#Live_scheduletimezone').val(data.timezone);
            $('#Live_schedulestatus').val(data.status);
            $('#Live_scheduleposter').val(data.poster);
            $('#Live_schedulepublic').val(data.public);
            $('#Live_schedulesaveTransmition').val(data.saveTransmition);
            $('#Live_scheduleshowOnTV').val(data.showOnTV);
        });
    });
</script>
<script> $(document).ready(function () {
        $('#Live_schedulescheduled_time').datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true, ignoreReadonly: true});
    });</script>