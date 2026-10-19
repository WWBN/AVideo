<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::canUpload()) {
    forbiddenPage('You cannot upload');
}
include_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/texts.php';
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fas fa-history"></i> <?php echo __("Publishing history"); ?>
    </div>
    <div class="panel-body">
        <table id="Publisher_video_publisher_logsTable" class="display table table-bordered table-striped table-hover table-condensed" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th><?php echo __("Date"); ?></th>
                    <th></th>
                    <th><?php echo __("Status"); ?></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script type="text/javascript">
    var Publisher_video_publisher_logstableVarVID;
    $(document).ready(function() {
        Publisher_video_publisher_logstableVarVID = $('#Publisher_video_publisher_logsTable').DataTable({
            serverSide: true,
            searching: false,
            "order": [
                [0, 'desc']
            ],
            "ajax": webSiteRootURL + "plugin/SocialMediaPublisher/View/Publisher_video_publisher_logs/list.json.php?videos_id=<?php echo intval(getVideos_id()); ?>",
            "language": {
                "emptyTable": socialPublisherText.noHistory
            },
            "columns": [{
                    "data": "publish_datetimestamp",
                    width: '90px',
                    render: function(data, type, row) {
                        return $('<span>').text(row.publish).html();
                    }
                },
                {
                    data: 'name',
                    orderable: false,
                    width: '40px',
                    render: function(data, type, row) {
                        return row.provider ? '<div class="largeSocialIcon">' + row.provider.ico + '</div>' : '';
                    }
                },
                {
                    "data": "details",
                    orderable: false,
                    render: function(data, type, row) {
                        if (row.job) {
                            return socialPublisherRenderJob(row);
                        }
                        // other networks: message built by Publisher_video_publisher_logs::getInfo()
                        return row.msg;
                    }
                }
            ],
            "drawCallback": function() {
                socialPublisherScheduleHistoryReload(this.api().rows().data().toArray());
            }
        });

        $('#Publisher_video_publisher_logsTable').on('click', 'button.socialPublisherJobAction', function(e) {
            e.preventDefault();
            var data = Publisher_video_publisher_logstableVarVID.row($(this).closest('tr')[0]).data();
            socialPublisherJobAction(data.id, $(this).attr('data-action'), $(this));
        });
        $('#Publisher_video_publisher_logsTable').on('click', 'button.socialPublisherJobReconnect', function(e) {
            e.preventDefault();
            openYPT('youtube');
        });
    });
</script>
