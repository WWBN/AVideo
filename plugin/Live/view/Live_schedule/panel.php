<?php
if (!User::canStream()) {
    return false;
}
$objScheduleLive = AVideoPlugin::getObjectData("Live");
global $Schedulecount;
?>
<link href="<?php echo getURL('view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'); ?>" rel="stylesheet" type="text/css" />
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="far fa-calendar-alt"></i> <?php echo __("Schedule a Live"); ?>
    </div>
    <div class="panel-body">

        <ul class="nav nav-tabs">
            <li class="active">
                <a data-toggle="tab" href="#newSchedule"><i class="far fa-file"></i> <?php echo __('New Schedule'); ?></a>
            </li>
            <li>
                <a data-toggle="tab" href="#savedSchedule"><i class="far fa-save"></i> <?php echo __('Saved Schedule'); ?> <span class="badge badge-primary savedScheduleTotals">0</span></a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="newSchedule" class="tab-pane fade in active" style="padding: 5px;">
                <?php
                include $global['systemRootPath'] . 'plugin/Live/view/Live_schedule/panelForm.php';
                ?>
            </div>
            <div id="savedSchedule" class="tab-pane fade" style="padding: 5px; max-height: 300px; overflow-y: auto;">
                <div class="list-group" id="schedule_live_list">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="Live_schedulebtnModelList" style="display: none;">
    <a class="list-group-item clearfix">
        <div class="list_name" style="white-space: nowrap;
             overflow: hidden;
             text-overflow: ellipsis;"></div>
        <br>
        <div class="btn-group btn-group-justified" style="margin-top: 10px;">
            <button class="btn btn-default" onclick="copyToClipboard($(this).attr('serverURL'));" data-toggle="tooltip" title="<?php echo __('Server URL'); ?>">
                <i class="fa fa-server"></i> <span class=""><?php echo __('RTMP URL'); ?></span>
            </button>
            <button class="btn btn-default" onclick="copyToClipboard($(this).attr('key'));" data-toggle="tooltip" title="<?php echo __('Key'); ?>">
                <i class="fa fa-key"></i> <span class=""><?php echo __('Key'); ?></span>
            </button>
            <button class="btn btn-default" onclick="copyToClipboard($(this).attr('serverURL') + '/' + $(this).attr('key'));" data-toggle="tooltip" title="<?php echo __('Server URL'); ?> + <?php echo __('Key'); ?>">
                <i class="fa fa-server"></i> + <i class="fa fa-key"></i> <span class="hidden-xs"><?php echo __('RTMP URL'); ?> + <?php echo __('Key'); ?></span>
            </button>
        </div>
        <div class="btn-group btn-group-justified" style="margin-top: 10px;">
            <button class="btn btn-primary" onclick="uploadPosterCroppie($(this).attr('schedule_id'));" data-toggle="tooltip" title="<?php echo __('Upload Poster Image'); ?>">
                <i class="far fa-image"></i> <i class="fas fa-upload"></i> <span class=""><?php echo __('Upload Poster'); ?></span>
            </button>
            <button class="btn btn-danger " onclick="removePosterSchedule($(this).attr('schedule_id'));" data-toggle="tooltip" title="<?php echo __('Remove Poster') ?>">
                <i class="fa fa-trash"></i> <span class=""><?php echo __('Delete Poster'); ?></span>
            </button>
        </div>
        <div class="btn-group  btn-group-justified futureButtons" style="margin-top: 10px;">            
            <button class="btn btn-primary" onclick="editSchedule($(this).attr('schedule_id'));" data-toggle="tooltip" title="<?php echo __('Edit') ?>">
                <i class="fa fa-edit"></i> <span class=""><?php echo __('Edit'); ?></span>
            </button>
            <button class="btn btn-danger" onclick="deleteSchedule($(this).attr('schedule_id'));" data-toggle="tooltip" title="<?php echo __('Delete') ?>">
                <i class="fa fa-trash"></i> <span class=""><?php echo __('Delete'); ?></span>
            </button>
        </div>
    </a>
</div>
<script src="<?php echo getCDN(); ?>js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
    var Schedule_plans = {};

    function editSchedule(schedule_id) {
        console.log(Schedule_plans[schedule_id]);
        var schedule = Schedule_plans[schedule_id];
        $('.nav-tabs a[href="#newSchedule"]').tab('show');
        $("#Live_schedule_id").val(schedule_id);
        $("#Schedule_title").val(schedule.title);
        $("#Schedule_status").val(schedule.status);
        $("#scheduled_time").val(schedule.scheduled_time);
        $("#scheduled_password").val(schedule.scheduled_password);
        $("#Schedule_live_servers_id").val(schedule.live_servers_id ? schedule.live_servers_id : 0);
        $("#users_id_company").val(schedule.users_id_company).trigger('change');
        $("#Schedule_description").val(schedule.description);
        $('.userGroupsSchedule').prop('checked', false);
        $.each(schedule.json.usergoups, function(index, value) {
            $('#groupSchedule' + value).prop('checked', true);
        });
    }

    function deleteSchedule(schedule_id) {
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
                        url: webSiteRootURL + "plugin/Live/view/Live_schedule/delete.json.php?id=" + schedule_id
                    }).done(function(resposta) {
                        if (resposta.error) {
                            avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                        } else {
                            listScheduledLives();
                        }
                        modal.hidePleaseWait();
                    });
                } else {

                }
            });
    }

    function uploadPosterCroppie(live_schedule_id) {
        var url = webSiteRootURL + "plugin/Live/view/Live_schedule/uploadPoster.php";
        url = addQueryStringParameter(url, 'live_schedule_id', live_schedule_id);
        url = addQueryStringParameter(url, 'live_servers_id', <?php printJSString($_REQUEST['live_servers_id'] ?? ''); ?>);
        avideoModalIframe(url);
    }

    function removePosterSchedule(schedule_id) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + "plugin/Live/removePoster.php?live_servers_id=<?php echo $_REQUEST['live_servers_id']; ?>&live_schedule_id=" + schedule_id,
            success: function(response) {
                modal.hidePleaseWait();
                if (response.error) {
                    avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                } else {
                    $('#mainVideo video').attr('poster', webSiteRootURL + response.newPoster);
                    $('#mainVideo .vjs-poster').css('background-image', 'url("' + webSiteRootURL + response.newPoster + '")');
                    $('.kv-file-content img').attr('src', '<?php echo $global['webSiteRootURL']; ?>' + response.newPoster);
                }
            }
        });
    }
</script>