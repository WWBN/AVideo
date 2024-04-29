<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::canUpload()) {
    forbiddenPage('You cannot upload');
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
                        <form id="panelpublisher_video_publisher_logsForm">
                            <div class="row">
                                <input type="hidden" name="id" id="publisher_video_publisher_logsid" value="">
                                <div class="form-group col-sm-12">
                                    <label for="publisher_video_publisher_logspublish_datetimestamp"><?php echo __("Publish Datetimestamp"); ?>:</label>
                                    <input type="text" id="publisher_video_publisher_logspublish_datetimestamp" name="publish_datetimestamp" class="form-control input-sm" placeholder="<?php echo __("Publish Datetimestamp"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="publisher_video_publisher_logsstatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="publisher_video_publisher_logsdetails"><?php echo __("Details"); ?>:</label>
                                    <textarea id="publisher_video_publisher_logsdetails" name="details" class="form-control input-sm" placeholder="<?php echo __("Details"); ?>" required="true"></textarea>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="publisher_video_publisher_logsvideos_id"><?php echo __("Videos Id"); ?>:</label>
                                    <select class="form-control input-sm" name="videos_id" id="publisher_video_publisher_logsvideos_id">
                                        <?php
                                        $options = publisher_video_publisher_logs::getAllVideos();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="publisher_video_publisher_logsusers_id"><?php echo __("Users Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_id" id="publisher_video_publisher_logsusers_id">
                                        <?php
                                        $options = publisher_video_publisher_logs::getAllUsers();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="publisher_video_publisher_logspublisher_social_medias_id"><?php echo __("Publisher Social Medias Id"); ?>:</label>
                                    <select class="form-control input-sm" name="publisher_social_medias_id" id="publisher_video_publisher_logspublisher_social_medias_id">
                                        <?php
                                        $options = publisher_video_publisher_logs::getAllPublisher_social_medias();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="publisher_video_publisher_logstimezone"><?php echo __("Timezone"); ?>:</label>
                                    <input type="text" id="publisher_video_publisher_logstimezone" name="timezone" class="form-control input-sm" placeholder="<?php echo __("Timezone"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newpublisher_video_publisher_logsLink" onclick="clearpublisher_video_publisher_logsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="publisher_video_publisher_logsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Publish Datetimestamp"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Details"); ?></th>
                                    <th><?php echo __("Timezone"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Publish Datetimestamp"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Details"); ?></th>
                                    <th><?php echo __("Timezone"); ?></th>
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
<div id="publisher_video_publisher_logsbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_publisher_video_publisher_logs btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_publisher_video_publisher_logs btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearpublisher_video_publisher_logsForm() {
        $('#publisher_video_publisher_logsid').val('');
        $('#publisher_video_publisher_logspublish_datetimestamp').val('');
        $('#publisher_video_publisher_logsstatus').val('');
        $('#publisher_video_publisher_logsdetails').val('');
        $('#publisher_video_publisher_logsvideos_id').val('');
        $('#publisher_video_publisher_logsusers_id').val('');
        $('#publisher_video_publisher_logspublisher_social_medias_id').val('');
        $('#publisher_video_publisher_logstimezone').val('');
    }
    $(document).ready(function() {
        $('#addpublisher_video_publisher_logsBtn').click(function() {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/addpublisher_video_publisher_logsVideo.php',
                data: $('#panelpublisher_video_publisher_logsForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelpublisher_video_publisher_logsForm").trigger("reset");
                    }
                    clearpublisher_video_publisher_logsForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var publisher_video_publisher_logstableVar = $('#publisher_video_publisher_logsTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/publisher_video_publisher_logs/list.json.php",
            "columns": [{
                    "data": "id"
                },
                {
                    "data": "publish_datetimestamp"
                },
                {
                    "data": "status"
                },
                {
                    "data": "details"
                },
                {
                    "data": "timezone"
                },
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#publisher_video_publisher_logsbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newpublisher_video_publisher_logs').on('click', function(e) {
            e.preventDefault();
            $('#panelpublisher_video_publisher_logsForm').trigger("reset");
            $('#publisher_video_publisher_logsid').val('');
        });
        $('#panelpublisher_video_publisher_logsForm').on('submit', function(e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/publisher_video_publisher_logs/add.json.php',
                data: $('#panelpublisher_video_publisher_logsForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelpublisher_video_publisher_logsForm").trigger("reset");
                    }
                    publisher_video_publisher_logstableVar.ajax.reload();
                    $('#publisher_video_publisher_logsid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#publisher_video_publisher_logsTable').on('click', 'button.delete_publisher_video_publisher_logs', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = publisher_video_publisher_logstableVar.row(tr).data();
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
                            url: "<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/publisher_video_publisher_logs/delete.json.php",
                            data: data

                        }).done(function(resposta) {
                            if (resposta.error) {
                                avideoAlertError(resposta.msg);
                            }
                            publisher_video_publisher_logstableVar.ajax.reload();
                            modal.hidePleaseWait();
                        });
                    } else {

                    }
                });
        });
        $('#publisher_video_publisher_logsTable').on('click', 'button.edit_publisher_video_publisher_logs', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = publisher_video_publisher_logstableVar.row(tr).data();
            $('#publisher_video_publisher_logsid').val(data.id);
            $('#publisher_video_publisher_logspublish_datetimestamp').val(data.publish_datetimestamp);
            $('#publisher_video_publisher_logsstatus').val(data.status);
            $('#publisher_video_publisher_logsdetails').val(data.details);
            $('#publisher_video_publisher_logsvideos_id').val(data.videos_id);
            $('#publisher_video_publisher_logsusers_id').val(data.users_id);
            $('#publisher_video_publisher_logspublisher_social_medias_id').val(data.publisher_social_medias_id);
            $('#publisher_video_publisher_logstimezone').val(data.timezone);
        });
    });
</script>