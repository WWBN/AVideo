<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage("You can not do this");
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
                                    <!--
                                    <th><?php echo __("Restreamer"); ?></th>
                                    <th><?php echo __("M3u8"); ?></th>
                                    -->
                                    <th><?php echo __("LogFile"); ?></th>
                                    <th><?php echo __("live_transmitions_history_id"); ?></th>
                                    <th><?php echo __("live_restreams_id"); ?></th>
                                    <!--
                                    <th><?php echo __("Json"); ?></th>
                                    -->
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <!--
                                    <th><?php echo __("Restreamer"); ?></th>
                                    <th><?php echo __("M3u8"); ?></th>
                                    -->
                                    <th><?php echo __("LogFile"); ?></th>
                                    <th><?php echo __("live_transmitions_history_id"); ?></th>
                                    <th><?php echo __("live_restreams_id"); ?></th>
                                    <!--
                                    <th><?php echo __("Json"); ?></th>
                                    -->
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
        <button href="" class="delete_Live_restreams_logs btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var Live_restreams_logstableVar = $('#Live_restreams_logsTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_restreams_logs/list.json.php",
            "columns": [
                {"data": "id"},
                //{"data": "restreamer"},
                //{"data": "m3u8"},
                {
                    "data": "logFile",
                    render: function (data, type, row) {
                        var url = webSiteRootURL+'plugin/Live/view/Live_restreams/getAction.json.php?action=log&live_restreams_logs_id='+row.id;
                        url = addQueryStringParameter(url, 'logFile', row.logFile);
                        return '<a href="' + url + '" target="_blank">' + data + '</a>';
                    }
                },
                {"data": "live_transmitions_history_id"},
                {"data": "live_restreams_id"},
                //{"data": "json"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Live_restreams_logsbtnModelLinks').html()
                }
            ],
            select: true,
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
    });
</script>
