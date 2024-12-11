<?php
$objLive = AVideoPlugin::getDataObject('Live');
?>
<form id="Schedule_form">
    <div class="row">
        <input type="hidden" id="Live_schedule_id" name="id">
        <div class="form-group col-sm-6">
            <label for="Schedule_title"> <?php echo __("Title"); ?></label>
            <input type="text" id="Schedule_title" name="title" class="form-control input-sm" placeholder="<?php echo __("Live Title"); ?>" required="true">
        </div>
        <div class="form-group col-sm-6">
            <label for="Schedule_status"><?php echo __("Status"); ?>:</label>
            <select class="form-control input-sm" name="status" id="Schedule_status">
                <option value="a"><?php echo __("Active"); ?></option>
                <option value="i"><?php echo __("Inactive"); ?></option>
            </select>
        </div>
        <div class="form-group col-sm-6" style="padding-right: 1px">
            <label for="scheduled_time"><?php echo __("Live Starts"); ?>:</label>
            <input type="text" id="scheduled_time" name="scheduled_time" class="form-control input-sm" placeholder="<?php echo __("Live Starts"); ?>" required="true" autocomplete="off" readonly="readonly">
        </div>
        <div class="form-group col-sm-6">
            <label for="scheduled_password"><?php echo __("Scheduled Password"); ?>:</label>
            <?php
            echo getInputPassword('scheduled_password', 'class="form-control input-sm" ', __("Scheduled Password"));
            ?>
        </div>
        <?php
        $options = Live_servers::getAllActive();
        if (!empty($options)) {
        ?>
            <div class="form-group col-sm-6">
                <label for="Schedule_live_servers_id"><?php echo __("Live Servers Id"); ?>:</label>
                <select class="form-control input-sm" name="live_servers_id" id="Schedule_live_servers_id">
                    <option value="0"><?php echo __('Undefined'); ?></option>
                    <?php
                    foreach ($options as $value) {
                        echo '<option value="' . $value['id'] . '">[' . $value['id'] . '] ' . $value['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>
        <?php
        }
        ?>
        <?php
        $myAffiliation = CustomizeUser::getAffiliateCompanies(User::getId());
        if (!empty($myAffiliation)) {
        ?>
            <div class="form-group col-sm-6">
                <?php
                $users_id_list = array();
                foreach ($myAffiliation as $value) {
                    $users_id_list[] = $value['users_id_company'];
                }
                echo '<label for="users_id_company" >' . __("Company") . '</label>';
                echo Layout::getUserSelect('users_id_company', $users_id_list, "", 'users_id_company', '');
                ?>
            </div>
        <?php
        }
        ?>
        <div class="form-group col-sm-12">
            <label for="Schedule_description"><?php echo __("Description"); ?>:</label>
            <textarea id="Schedule_description" name="description" class="form-control input-sm" placeholder="<?php echo __("Descriptions"); ?>" required="true" autocomplete="off"></textarea>
        </div>
    </div>
    <?php
    if (empty($objLive->hideUserGroups)) {
    ?>
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __("Groups That Can See This Stream"); ?><br><small><?php echo __("Uncheck all to make it public"); ?></small></div>
            <div class="panel-body">
                <?php
                $ug = UserGroups::getAllUsersGroups();
                foreach ($ug as $value) {
                ?>
                    <div class="form-group">
                        <span class="fa fa-users"></span> <?php echo $value['group_name']; ?>
                        <div class="material-switch pull-right">
                            <input id="groupSchedule<?php echo $value['id']; ?>" type="checkbox" value="<?php echo $value['id']; ?>" class="userGroupsSchedule" name="userGroupsSchedule[]" />
                            <label for="groupSchedule<?php echo $value['id']; ?>" class="label-success"></label>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
    }
    ?>
</form>

<div class="btn-group btn-group-justified" role="group">
    <button class="btn btn-primary " onclick="resetLiveSchedule();"><i class="fas fa-plus"></i> <?php echo __("New"); ?></button>
    <button class="btn btn-success " id="saveScheduleLive" onclick="saveSchedule(false);"><i class="fas fa-save"></i> <?php echo __("Save Schedule"); ?></button>
</div>
<script>
    function saveSchedule(close) {
        if ($('#Schedule_title').val() == '') {
            avideoToastError('Empty title');
            return false;
        }
        if ($('#scheduled_time').val() == '') {
            avideoToastError('Empty date');
            return false;
        }
        modal.showPleaseWait();
        var data = $("#Schedule_form").serialize();
        data += '&users_id_company=' + $('#users_id_company').val();
        //console.log('saveSchedule', data);
        $.ajax({
            type: "POST",
            url: webSiteRootURL + "plugin/Live/view/Live_schedule/add.json.php",
            data: data
        }).done(function(resposta) {
            if (resposta.error) {
                avideoAlertError(resposta.msg);
                modal.hidePleaseWait();
            } else {
                if (close) {
                    avideoModalIframeCloseToastSuccess(resposta.msg);
                } else {
                    modal.hidePleaseWait();
                    avideoToastSuccess(resposta.msg);
                    listScheduledLives();
                    resetLiveSchedule();
                }
            }
        });
    }

    function resetLiveSchedule() {
        $("#Schedule_form")[0].reset();
        $("#startsel1").trigger("change");
        $("#Live_schedule_id").val('');
        $("#users_id_company").val(0).trigger('change');
        $('.userGroupsSchedule').prop('checked', false);
    }

    function listScheduledLives() {
        Schedule_plans = {};
        $.ajax({
            url: webSiteRootURL + "plugin/Live/view/Live_schedule/list.json.php?users_id=<?php echo User::getId(); ?>"
        }).done(
            function(resposta) {
                $('.savedScheduleTotals').text(resposta.data.length);
                $("#schedule_live_list").empty();
                for (x in resposta.data) {
                    var schedule = resposta.data[x];
                    if (typeof schedule != 'object') {
                        continue;
                    }
                    Schedule_plans[schedule.id] = schedule;
                    var $clone = $($('#Live_schedulebtnModelList').html());
                    var id = 'scheduled_' + schedule.id;
                    var text = '<div class="pull-left" style="margin-right:10px;" ><img class="img img-responsive" src="' + schedule.posterURL + '" style="max-height:70px;" id="schedule_poster_' + schedule.id + '_0" /></div>' +
                        '<strong>' + schedule.title + '</strong>' +
                        '<div style="font-size: 0.9em;"> ' +
                        schedule.secondsIntervalHuman +
                        ' <span style="font-size: 0.8em;">[' + schedule.scheduled_time + ' ' + schedule.timezone + ']</span></div>';
                    $('.list_name', $clone).html(text);
                    $('.btn', $clone).attr('schedule_id', schedule.id).attr('serverURL', schedule.serverURL).attr('key', schedule.key);
                    $($clone).attr('id', id);
                    if (!schedule.future) {
                        $('.btn-group.futureButtons', $clone).hide();
                        $($clone).addClass('disabled');
                    }
                    $("#schedule_live_list").append($clone);
                    //$('#'+id).tooltip();
                }
                console.log(resposta.data);
            }
        );
    }


    $(document).ready(function() {
        $('#scheduled_time').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            autoclose: true,
            ignoreReadonly: true
        });
        $('#Live_schedulestart_sell_in').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            autoclose: true,
            ignoreReadonly: true
        });
        listScheduledLives();
    });
</script>