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
                        <form id="panelScheduler_commandsForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Scheduler_commandsid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandscallbackURL"><?php echo __("CallbackURL"); ?>:</label>
                                    <input type="text" id="Scheduler_commandscallbackURL" name="callbackURL" class="form-control input-sm" placeholder="<?php echo __("CallbackURL"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandsparameters"><?php echo __("Parameters"); ?>:</label>
                                    <textarea id="Scheduler_commandsparameters" name="parameters" class="form-control input-sm" placeholder="<?php echo __("Parameters"); ?>" required="true"></textarea>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandsdate_to_execute"><?php echo __("Date To Execute"); ?>:</label>
                                    <input type="text" id="Scheduler_commandsdate_to_execute" name="date_to_execute" class="form-control input-sm" placeholder="<?php echo __("Date To Execute"); ?>" required="true" autocomplete="off">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandsexecuted_in"><?php echo __("Executed In"); ?>:</label>
                                    <input type="text" id="Scheduler_commandsexecuted_in" name="executed_in" class="form-control input-sm" placeholder="<?php echo __("Executed In"); ?>" required="true" autocomplete="off">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="Scheduler_commandsstatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                        <option value="c"><?php echo __("Canceled"); ?></option>
                                        <option value="e"><?php echo __("Executed"); ?></option>
                                        <option value="r"><?php echo __("Repeat"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandscallbackResponse"><?php echo __("CallbackResponse"); ?>:</label>
                                    <textarea id="Scheduler_commandscallbackResponse" name="callbackResponse" class="form-control input-sm" placeholder="<?php echo __("CallbackResponse"); ?>" required="true"></textarea>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandstimezone"><?php echo __("Timezone"); ?>:</label>
                                    <input type="text" id="Scheduler_commandstimezone" name="timezone" class="form-control input-sm" placeholder="<?php echo __("Timezone"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandsrepeat_minute"><?php echo __("Repeat Minute"); ?>:</label>
                                    <input type="number" step="1" id="Scheduler_commandsrepeat_minute" name="repeat_minute" class="form-control input-sm" placeholder="<?php echo __("Repeat Minute"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandsrepeat_hour"><?php echo __("Repeat Hour"); ?>:</label>
                                    <input type="number" step="1" id="Scheduler_commandsrepeat_hour" name="repeat_hour" class="form-control input-sm" placeholder="<?php echo __("Repeat Hour"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandsrepeat_day_of_month"><?php echo __("Repeat Day Of Month"); ?>:</label>
                                    <input type="number" step="1" id="Scheduler_commandsrepeat_day_of_month" name="repeat_day_of_month" class="form-control input-sm" placeholder="<?php echo __("Repeat Day Of Month"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandsrepeat_month"><?php echo __("Repeat Month"); ?>:</label>
                                    <input type="number" step="1" id="Scheduler_commandsrepeat_month" name="repeat_month" class="form-control input-sm" placeholder="<?php echo __("Repeat Month"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandsrepeat_day_of_week"><?php echo __("Repeat Day Of Week"); ?>:</label>
                                    <input type="number" step="1" id="Scheduler_commandsrepeat_day_of_week" name="repeat_day_of_week" class="form-control input-sm" placeholder="<?php echo __("Repeat Day Of Week"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Scheduler_commandstype"><?php echo __("Type"); ?>:</label>
                                    <input type="text" id="Scheduler_commandstype" name="type" class="form-control input-sm" placeholder="<?php echo __("Type"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newScheduler_commandsLink" onclick="clearScheduler_commandsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Scheduler_commandsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Parameters"); ?></th>
                                    <th><?php echo __("Date To Execute"); ?></th>
                                    <th><?php echo __("Executed In"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Repeat Minute"); ?></th>
                                    <th><?php echo __("Repeat Hour"); ?></th>
                                    <th><?php echo __("Repeat Day Of Month"); ?></th>
                                    <th><?php echo __("Repeat Month"); ?></th>
                                    <th><?php echo __("Repeat Day Of Week"); ?></th>
                                    <th><?php echo __("Type"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Parameters"); ?></th>
                                    <th><?php echo __("Date To Execute"); ?></th>
                                    <th><?php echo __("Executed In"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Repeat Minute"); ?></th>
                                    <th><?php echo __("Repeat Hour"); ?></th>
                                    <th><?php echo __("Repeat Day Of Month"); ?></th>
                                    <th><?php echo __("Repeat Month"); ?></th>
                                    <th><?php echo __("Repeat Day Of Week"); ?></th>
                                    <th><?php echo __("Type"); ?></th>
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
<div id="Scheduler_commandsbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Scheduler_commands btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Scheduler_commands btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearScheduler_commandsForm() {
        $('#Scheduler_commandsid').val('');
        $('#Scheduler_commandscallbackURL').val('');
        $('#Scheduler_commandsparameters').val('');
        $('#Scheduler_commandsdate_to_execute').val('');
        $('#Scheduler_commandsexecuted_in').val('');
        $('#Scheduler_commandsstatus').val('');
        $('#Scheduler_commandscallbackResponse').val('');
        $('#Scheduler_commandstimezone').val('');
        $('#Scheduler_commandsrepeat_minute').val('');
        $('#Scheduler_commandsrepeat_hour').val('');
        $('#Scheduler_commandsrepeat_day_of_month').val('');
        $('#Scheduler_commandsrepeat_month').val('');
        $('#Scheduler_commandsrepeat_day_of_week').val('');
        $('#Scheduler_commandstype').val('');
    }
    $(document).ready(function () {
        $('#addScheduler_commandsBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Scheduler/View/addScheduler_commandsVideo.php',
                data: $('#panelScheduler_commandsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelScheduler_commandsForm").trigger("reset");
                    }
                    clearScheduler_commandsForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Scheduler_commandstableVar = $('#Scheduler_commandsTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Scheduler/View/Scheduler_commands/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "parameters"},
                {"data": "date_to_execute"},
                {"data": "executed_in"},
                {"data": "status"},
                {"data": "repeat_minute"},
                {"data": "repeat_hour"},
                {"data": "repeat_day_of_month"},
                {"data": "repeat_month"},
                {"data": "repeat_day_of_week"},
                {"data": "type"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Scheduler_commandsbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newScheduler_commands').on('click', function (e) {
            e.preventDefault();
            $('#panelScheduler_commandsForm').trigger("reset");
            $('#Scheduler_commandsid').val('');
        });
        $('#panelScheduler_commandsForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Scheduler/View/Scheduler_commands/add.json.php',
                data: $('#panelScheduler_commandsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelScheduler_commandsForm").trigger("reset");
                    }
                    Scheduler_commandstableVar.ajax.reload();
                    $('#Scheduler_commandsid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Scheduler_commandsTable').on('click', 'button.delete_Scheduler_commands', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Scheduler_commandstableVar.row(tr).data();
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
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/Scheduler/View/Scheduler_commands/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    avideoAlertError(resposta.msg);
                                }
                                Scheduler_commandstableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Scheduler_commandsTable').on('click', 'button.edit_Scheduler_commands', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Scheduler_commandstableVar.row(tr).data();
            $('#Scheduler_commandsid').val(data.id);
            $('#Scheduler_commandscallbackURL').val(data.callbackURL);
            $('#Scheduler_commandsparameters').val(data.parameters);
            $('#Scheduler_commandsdate_to_execute').val(data.date_to_execute);
            $('#Scheduler_commandsexecuted_in').val(data.executed_in);
            $('#Scheduler_commandsstatus').val(data.status);
            $('#Scheduler_commandscallbackResponse').val(data.callbackResponse);
            $('#Scheduler_commandstimezone').val(data.timezone);
            $('#Scheduler_commandsrepeat_minute').val(data.repeat_minute);
            $('#Scheduler_commandsrepeat_hour').val(data.repeat_hour);
            $('#Scheduler_commandsrepeat_day_of_month').val(data.repeat_day_of_month);
            $('#Scheduler_commandsrepeat_month').val(data.repeat_month);
            $('#Scheduler_commandsrepeat_day_of_week').val(data.repeat_day_of_week);
            $('#Scheduler_commandstype').val(data.type);
        });
    });
</script>
<script> $(document).ready(function () {
        $('#Scheduler_commandsdate_to_execute').datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true});
    });</script>
<script> $(document).ready(function () {
        $('#Scheduler_commandsexecuted_in').datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true});
    });</script>