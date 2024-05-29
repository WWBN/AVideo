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
    <div class="panel-body">
        <table id="Publisher_video_publisher_logsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>#</th>
                    <th></th>
                    <th><?php echo __("Publish"); ?></th>
                    <th><?php echo __("Details"); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th></th>
                    <th><?php echo __("Publish"); ?></th>
                    <th><?php echo __("Details"); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script type="text/javascript">
    var Publisher_video_publisher_logstableVarVID;
    $(document).ready(function() {
        Publisher_video_publisher_logstableVarVID = $('#Publisher_video_publisher_logsTable').DataTable({
            serverSide: true,
            "ajax": webSiteRootURL + "plugin/SocialMediaPublisher/View/Publisher_video_publisher_logs/list.json.php?videos_id=<?php echo getVideos_id(); ?>",
            "columns": [{
                    "data": "id"
                },
                {
                    data: 'name',
                    render: function(data, type, row) {
                        return '<div class="largeSocialIcon">'+row.provider.ico+'</div>';
                    }
                },
                {
                    "data": "publish_datetimestamp",
                    render: function(data, type, row) {
                        return row.publish;
                    }
                },
                {
                    "data": "details",
                    render: function(data, type, row) {
                        return row.msg;
                    }
                }
            ],
            select: true,
            "order": [
                [0, 'desc']
            ]
        });
    });
</script>