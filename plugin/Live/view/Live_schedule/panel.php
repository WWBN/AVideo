<?php
if(!User::canStream()){
    return false;
}
$objScheduleLive = AVideoPlugin::getObjectData("Live");
global $Schedulecount;
?>
<script src="<?php echo getCDN(); ?>view/bootstrap/bootstrap-fileinput/js/fileinput.min.js" type="text/javascript"></script>
<link href="<?php echo getCDN(); ?>view/bootstrap/bootstrap-fileinput/css/fileinput.min.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo getCDN(); ?>view/bootstrap/bootstrap-fileinput/themes/fa/theme.min.js" type="text/javascript"></script>
<link href="<?php echo getCDN(); ?>view/bootstrap/bootstrap-fileinput/themes/explorer/theme.min.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo getCDN(); ?>view/bootstrap/bootstrap-fileinput/themes/explorer/theme.min.js" type="text/javascript"></script>
<link href="<?php echo getURL('view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'); ?>" rel="stylesheet" type="text/css"/>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="far fa-calendar-alt"></i>  <?php echo __("Schedule a Live"); ?>
    </div>
    <div class="panel-body">

        <ul class="nav nav-tabs">
            <li class="active">
                <a data-toggle="tab" href="#newSchedule"><i class="far fa-file"></i> <?php echo __('New Schedule'); ?></a>
            </li>
            <li>
                <a data-toggle="tab" href="#savedSchedule"><i class="far fa-save"></i> <?php echo __('Saved Schedule'); ?></a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="newSchedule" class="tab-pane fade in active" style="padding: 5px;">
                <form id="Schedule_form">
                    <input type="hidden" id="Live_schedule_id" name="id" >
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
                        <input type="text" id="scheduled_time" name="scheduled_time" class="form-control input-sm" placeholder="<?php echo __("Live Starts"); ?>" required="true" autocomplete="off">
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
                    <div class="form-group col-sm-12">
                        <label for="Schedule_description"><?php echo __("Description"); ?>:</label>
                        <textarea id="Schedule_description" name="description" class="form-control input-sm" placeholder="<?php echo __("Descriptions"); ?>" required="true" autocomplete="off"></textarea>
                    </div>
                </form>
            </div>
            <div id="savedSchedule" class="tab-pane fade" style="padding: 5px; max-height: 300px; overflow-y: auto;">
                <div class="list-group" id="schedule_live_list">
                </div>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <button class="btn btn-primary " onclick="resetSchedule()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></button>
        <button class="btn btn-success " id="saveScheduleLive"><i class="fas fa-save"></i> <?php echo __("Save Schedule"); ?></button>
    </div>
</div>
<div id="Live_schedulebtnModelList" style="display: none;">
    <a class="list-group-item clearfix" >
        <div class="list_name" style="white-space: nowrap;
             overflow: hidden;
             text-overflow: ellipsis;"></div>
             <br>
        <div class="btn-group pull-left">
            <button class="btn btn-default btn-xs" onclick="copyToClipboard($(this).attr('serverURL')); " data-toggle="tooltip" title="<?php echo __('Server URL'); ?>" >
                <i class="fa fa-server"></i> <span class="hidden-sm hidden-xs"><?php echo __('Server URL'); ?></span>
            </button>
            <button class="btn btn-default btn-xs" onclick="copyToClipboard($(this).attr('key'));" data-toggle="tooltip" title="<?php echo __('Key'); ?>" >
                <i class="fa fa-key"></i> <span class="hidden-sm hidden-xs"><?php echo __('Key'); ?></span>
            </button>
            <button class="btn btn-default btn-xs" onclick="copyToClipboard($(this).attr('serverURL')+'/'+$(this).attr('key'));" data-toggle="tooltip" title="<?php echo __('Server URL'); ?> + <?php echo __('Key'); ?>" >
                <i class="fa fa-server"></i> + <i class="fa fa-key"></i> <span class="hidden-sm hidden-xs"><?php echo __('Server URL'); ?> + <?php echo __('Key'); ?></span>
            </button>
        </div>
        <div class="btn-group pull-right">
            <button class="btn btn-primary btn-xs" onclick="uploadPoster($(this).attr('schedule_id'));" data-toggle="tooltip" title="<?php echo __('Upload Poster Image'); ?>" >
                <i class="far fa-image"></i> <i class="far fa-upload"></i> <span class="hidden-sm hidden-xs"><?php echo __('Upload Poster'); ?></span>
            </button>
            <button class="btn btn-danger btn-xs" onclick="removePosterSchedule($(this).attr('schedule_id'));" data-toggle="tooltip" title="<?php echo __('Remove Poster') ?>" >
                <i class="fa fa-trash"></i> <span class="hidden-sm hidden-xs"><?php echo __('Delete Poster'); ?></span>
            </button>
        </div>
        <hr>
        <div class="btn-group pull-right">
            <button class="btn btn-default faa-parent animated-hover " onclick="avideoModalIframeLarge(webSiteRootURL+'plugin/Live/webcamFullscreen.php?live_schedule_id='+$(this).attr('schedule_id'));" data-toggle="tooltip" title="<?php echo __('Go Live') ?>" >
                <i class="fas fa-circle faa-flash" style="color:red;"></i> <span class="hidden-sm hidden-xs"><?php echo __($objScheduleLive->button_title); ?></span>
            </button>
            <button class="btn btn-primary" onclick="editSchedule($(this).attr('schedule_id'));" data-toggle="tooltip" title="<?php echo __('Edit') ?>" >
                <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?php echo __('Edit'); ?></span>
            </button>
            <button class="btn btn-danger" onclick="deleteSchedule($(this).attr('schedule_id'));" data-toggle="tooltip" title="<?php echo __('Delete') ?>" >
                <i class="fa fa-trash"></i> <span class="hidden-sm hidden-xs"><?php echo __('Delete'); ?></span>
            </button>
        </div>
    </a>
</div>
<script src="<?php echo getCDN(); ?>js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
                var Schedule_plans = {};
                $(document).ready(function () {

                    $('#scheduled_time').datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true});
                    $('#Live_schedulestart_sell_in').datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true});
                    $('#saveScheduleLive').click(function () {
                        modal.showPleaseWait();
                        $.ajax({
                            type: "POST",
                            url: webSiteRootURL + "plugin/Live/view/Live_schedule/add.json.php",
                            data: $("#Schedule_form").serialize()
                        }).done(function (resposta) {
                            if (resposta.error) {
                                avideoAlertError(resposta.msg);
                            } else {
                                avideoAlertSuccess(resposta.msg);
                                listScheduledLives();
                                resetSchedule();
                            }
                            modal.hidePleaseWait();
                        });
                    });
                    listScheduledLives();
                });

                function resetSchedule() {
                    $("#Schedule_form")[0].reset();
                    $("#startsel1").trigger("change");
                    $("#Live_schedule_id").val('');
                }

                function listScheduledLives() {
                    Schedule_plans = {};
                    $.ajax({
                        url: webSiteRootURL + "plugin/Live/view/Live_schedule/list.json.php?users_id=<?php echo User::getId(); ?>"
                    }).done(
                            function (resposta) {
                                $("#schedule_live_list").empty();
                                for (x in resposta.data) {
                                    var schedule = resposta.data[x];
                                    if (typeof schedule != 'object') {
                                        continue;
                                    }
                                    Schedule_plans[schedule.id] = schedule;
                                    var $clone = $($('#Live_schedulebtnModelList').html());
                                    var id = 'scheduled_' + schedule.id;
                                    var text = '<div class="pull-left" style="margin-right:10px;" ><img class="img img-responsive" src="' + schedule.posterURL + '" style="max-height:70px;" id="schedule_poster_' + schedule.id + '" /></div>'
                                            + '<strong>' + schedule.title + '</strong>'
                                            + '<div style="font-size: 0.9em;"> '
                                            + schedule.secondsIntervalHuman
                                            + ' <span style="font-size: 0.8em;">[' + schedule.scheduled_time + ' ' + schedule.timezone + ']</span></div>';
                                    $('.list_name', $clone).html(text);
                                    $('.btn', $clone).attr('schedule_id', schedule.id).attr('serverURL', schedule.serverURL).attr('key', schedule.key);
                                    $($clone).attr('id', id);
                                    if (!schedule.future) {
                                        $('.btn-group', $clone).hide();
                                        $($clone).addClass('disabled');
                                    }
                                    $("#schedule_live_list").append($clone);
                                    //$('#'+id).tooltip();
                                }
                                console.log(resposta.data);
                            }
                    );
                }

                function editSchedule(schedule_id) {
                    console.log(Schedule_plans[schedule_id]);
                    var schedule = Schedule_plans[schedule_id];
                    $('.nav-tabs a[href="#newSchedule"]').tab('show');
                    $("#Live_schedule_id").val(schedule_id);
                    $("#Schedule_title").val(schedule.title);
                    $("#Schedule_status").val(schedule.status);
                    $("#scheduled_time").val(schedule.scheduled_time);
                    $("#Schedule_live_servers_id").val(schedule.live_servers_id);
                    $("#Schedule_description").val(schedule.description);
                }

                function deleteSchedule(schedule_id) {
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
                                        url: webSiteRootURL + "plugin/Live/view/Live_schedule/delete.json.php?id=" + schedule_id
                                    }).done(function (resposta) {
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

                function uploadPoster(schedule_id) {
                    console.log(Schedule_plans[schedule_id]);
                    var schedule = Schedule_plans[schedule_id];
                    avideoAlertHTMLText('<?php echo __('Upload Poster Image'); ?>', '<input id="input-jpg-Schedule" type="file" class="file-loading" accept="image/*">', '');
                    $("#input-jpg-Schedule").fileinput({
                        uploadUrl: webSiteRootURL + "plugin/Live/uploadPoster.php?live_servers_id=<?php echo $_REQUEST['live_servers_id']; ?>&live_schedule_id=" + schedule_id,
                        autoReplace: true,
                        overwriteInitial: true,
                        showUploadedThumbs: false,
                        showPreview: true,
                        maxFileCount: 1,
                        initialPreview: [
                            "<img class='img img-responsive' src='" + schedule.posterURL + "'>",
                        ],
                        initialCaption: 'ScheduledLiveBG.jpg',
                        initialPreviewShowDelete: false,
                        showRemove: false,
                        showClose: false,
                        layoutTemplates: {actionDelete: ''}, // disable thumbnail deletion
                        allowedFileExtensions: ["jpg", "jpeg", "png"],
                        //minImageWidth: 2048,
                        //minImageHeight: 1152,
                        //maxImageWidth: 2560,
                        //maxImageHeight: 1440
                    }).on('fileuploaded', function (event, previewId, index, fileId) {
                        listScheduledLives();
                    });
                }

                function removePosterSchedule(schedule_id) {
                    modal.showPleaseWait();
                    $.ajax({
                        url: webSiteRootURL + "plugin/Live/removePoster.php?live_servers_id=<?php echo $_REQUEST['live_servers_id']; ?>&live_schedule_id=" + schedule_id,
                        success: function (response) {
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