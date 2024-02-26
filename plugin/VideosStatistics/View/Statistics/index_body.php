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
                        <form id="panelStatisticsForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Statisticsid" value="" >
<div class="form-group col-sm-12">
                                    <label for="Statisticsusers_id"><?php echo __("Users Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_id" id="Statisticsusers_id">
                                        <?php
                                        $options = Statistics::getAllUsers();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
<div class="form-group col-sm-12">
                                        <label for="Statisticstotal_videos"><?php echo __("Total Videos"); ?>:</label>
                                        <input type="number" step="1" id="Statisticstotal_videos" name="total_videos" class="form-control input-sm" placeholder="<?php echo __("Total Videos"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Statisticstotal_video_views"><?php echo __("Total Video Views"); ?>:</label>
                                        <input type="number" step="1" id="Statisticstotal_video_views" name="total_video_views" class="form-control input-sm" placeholder="<?php echo __("Total Video Views"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Statisticstotal_subscriptions"><?php echo __("Total Subscriptions"); ?>:</label>
                                        <input type="number" step="1" id="Statisticstotal_subscriptions" name="total_subscriptions" class="form-control input-sm" placeholder="<?php echo __("Total Subscriptions"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Statisticstotal_comments"><?php echo __("Total Comments"); ?>:</label>
                                        <input type="number" step="1" id="Statisticstotal_comments" name="total_comments" class="form-control input-sm" placeholder="<?php echo __("Total Comments"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Statisticstotal_likes"><?php echo __("Total Likes"); ?>:</label>
                                        <input type="number" step="1" id="Statisticstotal_likes" name="total_likes" class="form-control input-sm" placeholder="<?php echo __("Total Likes"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Statisticstotal_dislikes"><?php echo __("Total Dislikes"); ?>:</label>
                                        <input type="number" step="1" id="Statisticstotal_dislikes" name="total_dislikes" class="form-control input-sm" placeholder="<?php echo __("Total Dislikes"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Statisticstotal_duration_seconds"><?php echo __("Total Duration Seconds"); ?>:</label>
                                        <input type="number" step="1" id="Statisticstotal_duration_seconds" name="total_duration_seconds" class="form-control input-sm" placeholder="<?php echo __("Total Duration Seconds"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Statisticscollected_date"><?php echo __("Collected Date"); ?>:</label>
                                        <input type="text" id="Statisticscollected_date" name="collected_date" class="form-control input-sm" placeholder="<?php echo __("Collected Date"); ?>" required="true">
                                    </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newStatisticsLink" onclick="clearStatisticsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="StatisticsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
<th><?php echo __("Total Videos"); ?></th>
<th><?php echo __("Total Video Views"); ?></th>
<th><?php echo __("Total Subscriptions"); ?></th>
<th><?php echo __("Total Comments"); ?></th>
<th><?php echo __("Total Likes"); ?></th>
<th><?php echo __("Total Dislikes"); ?></th>
<th><?php echo __("Total Duration Seconds"); ?></th>
<th><?php echo __("Collected Date"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
<th><?php echo __("Total Videos"); ?></th>
<th><?php echo __("Total Video Views"); ?></th>
<th><?php echo __("Total Subscriptions"); ?></th>
<th><?php echo __("Total Comments"); ?></th>
<th><?php echo __("Total Likes"); ?></th>
<th><?php echo __("Total Dislikes"); ?></th>
<th><?php echo __("Total Duration Seconds"); ?></th>
<th><?php echo __("Collected Date"); ?></th>
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
<div id="StatisticsbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Statistics btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Statistics btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearStatisticsForm() {
    $('#Statisticsid').val('');
$('#Statisticsusers_id').val('');
$('#Statisticstotal_videos').val('');
$('#Statisticstotal_video_views').val('');
$('#Statisticstotal_subscriptions').val('');
$('#Statisticstotal_comments').val('');
$('#Statisticstotal_likes').val('');
$('#Statisticstotal_dislikes').val('');
$('#Statisticstotal_duration_seconds').val('');
$('#Statisticscollected_date').val('');
    }
    $(document).ready(function () {
    $('#addStatisticsBtn').click(function () {
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/VideosStatistics/View/addStatisticsVideo.php',
            data: $('#panelStatisticsForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelStatisticsForm").trigger("reset");
                }
                clearStatisticsForm();
                tableVideos.ajax.reload();
                modal.hidePleaseWait();
            }
        });
    });
    var StatisticstableVar = $('#StatisticsTable').DataTable({
        serverSide: true,
        "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/VideosStatistics/View/Statistics/list.json.php",
        "columns": [
        {"data": "id"},
{"data": "total_videos"},
{"data": "total_video_views"},
{"data": "total_subscriptions"},
{"data": "total_comments"},
{"data": "total_likes"},
{"data": "total_dislikes"},
{"data": "total_duration_seconds"},
{"data": "collected_date"},
        {
        sortable: false,
                data: null,
                defaultContent: $('#StatisticsbtnModelLinks').html()
        }
        ],
        select: true,
    });
    $('#newStatistics').on('click', function (e) {
    e.preventDefault();
    $('#panelStatisticsForm').trigger("reset");
    $('#Statisticsid').val('');
    });
    $('#panelStatisticsForm').on('submit', function (e) {
        e.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/VideosStatistics/View/Statistics/add.json.php',
            data: $('#panelStatisticsForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelStatisticsForm").trigger("reset");
                }
                StatisticstableVar.ajax.reload();
                $('#Statisticsid').val('');
                modal.hidePleaseWait();
            }
        });
    });
    $('#StatisticsTable').on('click', 'button.delete_Statistics', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = StatisticstableVar.row(tr).data();
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
                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/VideosStatistics/View/Statistics/delete.json.php",
                    data: data

            }).done(function (resposta) {
            if (resposta.error) {
                avideoAlertError(resposta.msg);
            }
            StatisticstableVar.ajax.reload();
            modal.hidePleaseWait();
            });
            } else {

            }
            });
    });
    $('#StatisticsTable').on('click', 'button.edit_Statistics', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = StatisticstableVar.row(tr).data();
    $('#Statisticsid').val(data.id);
$('#Statisticsusers_id').val(data.users_id);
$('#Statisticstotal_videos').val(data.total_videos);
$('#Statisticstotal_video_views').val(data.total_video_views);
$('#Statisticstotal_subscriptions').val(data.total_subscriptions);
$('#Statisticstotal_comments').val(data.total_comments);
$('#Statisticstotal_likes').val(data.total_likes);
$('#Statisticstotal_dislikes').val(data.total_dislikes);
$('#Statisticstotal_duration_seconds').val(data.total_duration_seconds);
$('#Statisticscollected_date').val(data.collected_date);
    });
    });
</script>
