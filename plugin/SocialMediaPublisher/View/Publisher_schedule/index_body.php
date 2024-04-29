<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('Admins only');
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
                        <form id="panelPublisher_scheduleForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Publisher_scheduleid" value="" >
<div class="form-group col-sm-12">
                                        <label for="Publisher_schedulescheduled_timestamp"><?php echo __("Scheduled Timestamp"); ?>:</label>
                                        <input type="text" id="Publisher_schedulescheduled_timestamp" name="scheduled_timestamp" class="form-control input-sm" placeholder="<?php echo __("Scheduled Timestamp"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="status"><?php echo __("Status"); ?>:</label>
                                        <select class="form-control input-sm" name="status" id="Publisher_schedulestatus">
                                            <option value="a"><?php echo __("Active"); ?></option>
                                            <option value="i"><?php echo __("Inactive"); ?></option>
                                        </select>
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Publisher_scheduletimezone"><?php echo __("Timezone"); ?>:</label>
                                        <input type="text" id="Publisher_scheduletimezone" name="timezone" class="form-control input-sm" placeholder="<?php echo __("Timezone"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                    <label for="Publisher_schedulevideos_id"><?php echo __("Videos Id"); ?>:</label>
                                    <select class="form-control input-sm" name="videos_id" id="Publisher_schedulevideos_id">
                                        <?php
                                        $options = Publisher_schedule::getAllVideos();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
<div class="form-group col-sm-12">
                                    <label for="Publisher_scheduleusers_id"><?php echo __("Users Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_id" id="Publisher_scheduleusers_id">
                                        <?php
                                        $options = Publisher_schedule::getAllUsers();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
<div class="form-group col-sm-12">
                                    <label for="Publisher_schedulepublisher_social_medias_id"><?php echo __("Publisher Social Medias Id"); ?>:</label>
                                    <select class="form-control input-sm" name="publisher_social_medias_id" id="Publisher_schedulepublisher_social_medias_id">
                                        <?php
                                        $options = Publisher_schedule::getAllPublisher_social_medias();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newPublisher_scheduleLink" onclick="clearPublisher_scheduleForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Publisher_scheduleTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
<th><?php echo __("Scheduled Timestamp"); ?></th>
<th><?php echo __("Status"); ?></th>
<th><?php echo __("Timezone"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
<th><?php echo __("Scheduled Timestamp"); ?></th>
<th><?php echo __("Status"); ?></th>
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
<div id="Publisher_schedulebtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Publisher_schedule btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Publisher_schedule btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearPublisher_scheduleForm() {
    $('#Publisher_scheduleid').val('');
$('#Publisher_schedulescheduled_timestamp').val('');
$('#Publisher_schedulestatus').val('');
$('#Publisher_scheduletimezone').val('');
$('#Publisher_schedulevideos_id').val('');
$('#Publisher_scheduleusers_id').val('');
$('#Publisher_schedulepublisher_social_medias_id').val('');
    }
    $(document).ready(function () {
    $('#addPublisher_scheduleBtn').click(function () {
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/addPublisher_scheduleVideo.php',
            data: $('#panelPublisher_scheduleForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelPublisher_scheduleForm").trigger("reset");
                }
                clearPublisher_scheduleForm();
                tableVideos.ajax.reload();
                modal.hidePleaseWait();
            }
        });
    });
    var Publisher_scheduletableVar = $('#Publisher_scheduleTable').DataTable({
        serverSide: true,
        "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/Publisher_schedule/list.json.php",
        "columns": [
        {"data": "id"},
{"data": "scheduled_timestamp"},
{"data": "status"},
{"data": "timezone"},
        {
        sortable: false,
                data: null,
                defaultContent: $('#Publisher_schedulebtnModelLinks').html()
        }
        ],
        select: true,
    });
    $('#newPublisher_schedule').on('click', function (e) {
    e.preventDefault();
    $('#panelPublisher_scheduleForm').trigger("reset");
    $('#Publisher_scheduleid').val('');
    });
    $('#panelPublisher_scheduleForm').on('submit', function (e) {
        e.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/Publisher_schedule/add.json.php',
            data: $('#panelPublisher_scheduleForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelPublisher_scheduleForm").trigger("reset");
                }
                Publisher_scheduletableVar.ajax.reload();
                $('#Publisher_scheduleid').val('');
                modal.hidePleaseWait();
            }
        });
    });
    $('#Publisher_scheduleTable').on('click', 'button.delete_Publisher_schedule', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Publisher_scheduletableVar.row(tr).data();
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
                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/SocialMediaPublisher/View/Publisher_schedule/delete.json.php",
                    data: data

            }).done(function (resposta) {
            if (resposta.error) {
                avideoAlertError(resposta.msg);
            }
            Publisher_scheduletableVar.ajax.reload();
            modal.hidePleaseWait();
            });
            } else {

            }
            });
    });
    $('#Publisher_scheduleTable').on('click', 'button.edit_Publisher_schedule', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Publisher_scheduletableVar.row(tr).data();
    $('#Publisher_scheduleid').val(data.id);
$('#Publisher_schedulescheduled_timestamp').val(data.scheduled_timestamp);
$('#Publisher_schedulestatus').val(data.status);
$('#Publisher_scheduletimezone').val(data.timezone);
$('#Publisher_schedulevideos_id').val(data.videos_id);
$('#Publisher_scheduleusers_id').val(data.users_id);
$('#Publisher_schedulepublisher_social_medias_id').val(data.publisher_social_medias_id);
    });
    });
</script>
