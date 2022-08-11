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
            <div class="col-sm-12">
                <div class="panel panel-default ">
                    <div class="panel-heading"><i class="fas fa-edit"></i> <?php echo __("Edit"); ?></div>
                    <div class="panel-body">
                        <table id="Live_restreams_logsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Restreamer"); ?></th>
                                    <th><?php echo __("M3u8"); ?></th>
                                    <th><?php echo __("Destinations"); ?></th>
                                    <th><?php echo __("LogFile"); ?></th>
                                    <th><?php echo __("Json"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Restreamer"); ?></th>
                                    <th><?php echo __("M3u8"); ?></th>
                                    <th><?php echo __("Destinations"); ?></th>
                                    <th><?php echo __("LogFile"); ?></th>
                                    <th><?php echo __("Json"); ?></th>
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
<div id="Live_restreams_logsbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Live_restreams_logs btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Live_restreams_logs btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearLive_restreams_logsForm() {
        $('#Live_restreams_logsid').val('');
        $('#Live_restreams_logsrestreamer').val('');
        $('#Live_restreams_logsm3u8').val('');
        $('#Live_restreams_logsdestinations').val('');
        $('#Live_restreams_logslogFile').val('');
        $('#Live_restreams_logsusers_id').val('');
        $('#Live_restreams_logsjson').val('');
    }
    $(document).ready(function () {
        $('#addLive_restreams_logsBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/addLive_restreams_logsVideo.php',
                data: $('#panelLive_restreams_logsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelLive_restreams_logsForm").trigger("reset");
                    }
                    clearLive_restreams_logsForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Live_restreams_logstableVar = $('#Live_restreams_logsTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_restreams_logs/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "restreamer"},
                {"data": "m3u8"},
                {"data": "destinations"},
                {"data": "logFile"},
                {"data": "json"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Live_restreams_logsbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newLive_restreams_logs').on('click', function (e) {
            e.preventDefault();
            $('#panelLive_restreams_logsForm').trigger("reset");
            $('#Live_restreams_logsid').val('');
        });
        $('#panelLive_restreams_logsForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_restreams_logs/add.json.php',
                data: $('#panelLive_restreams_logsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelLive_restreams_logsForm").trigger("reset");
                    }
                    Live_restreams_logstableVar.ajax.reload();
                    $('#Live_restreams_logsid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Live_restreams_logsTable').on('click', 'button.delete_Live_restreams_logs', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Live_restreams_logstableVar.row(tr).data();
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
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_restreams_logs/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    avideoAlertError(resposta.msg);
                                }
                                Live_restreams_logstableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Live_restreams_logsTable').on('click', 'button.edit_Live_restreams_logs', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Live_restreams_logstableVar.row(tr).data();
            $('#Live_restreams_logsid').val(data.id);
            $('#Live_restreams_logsrestreamer').val(data.restreamer);
            $('#Live_restreams_logsm3u8').val(data.m3u8);
            $('#Live_restreams_logsdestinations').val(data.destinations);
            $('#Live_restreams_logslogFile').val(data.logFile);
            $('#Live_restreams_logsusers_id').val(data.users_id);
            $('#Live_restreams_logsjson').val(data.json);
        });
    });
</script>
