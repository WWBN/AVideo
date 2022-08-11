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
                                    <th><?php echo __("users_id"); ?></th>
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
                                    <th><?php echo __("users_id"); ?></th>
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
    $(document).ready(function () {
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
                {"data": "users_id"}
            ],
            select: true,
        });
    });
</script>
