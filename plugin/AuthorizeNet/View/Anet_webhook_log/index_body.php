<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('Admins only');
}
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fas fa-list"></i> <?php echo __("Webhook Log"); ?>
        </div>
        <div class="panel-body">
            <table id="Anet_webhook_logTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php echo __('Event Type'); ?></th>
                        <th><?php echo __('Trans ID'); ?></th>
                        <th><?php echo __('Amount'); ?></th>
                        <th><?php echo __('Auth Code'); ?></th>
                        <th><?php echo __('Processed'); ?></th>
                        <th><?php echo __('Error'); ?></th>
                        <th><?php echo __('Created'); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th><?php echo __('Event Type'); ?></th>
                        <th><?php echo __('Trans ID'); ?></th>
                        <th><?php echo __('Amount'); ?></th>
                        <th><?php echo __('Auth Code'); ?></th>
                        <th><?php echo __('Processed'); ?></th>
                        <th><?php echo __('Error'); ?></th>
                        <th><?php echo __('Created'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#Anet_webhook_logTable').DataTable({
            serverSide: true,
            ajax: webSiteRootURL + "plugin/AuthorizeNet/View/Anet_webhook_log/list.json.php",
            order: [[6, 'desc']],
            columns: [
                { data: 'event_type' },
                {
                    data: 'payload_json',
                    render: function (data) {
                        try { var p = JSON.parse(data); return p.id || ''; } catch(e) { return ''; }
                    }
                },
                {
                    data: 'payload_json',
                    render: function (data) {
                        try { var p = JSON.parse(data); return p.authAmount != null ? '$' + p.authAmount : ''; } catch(e) { return ''; }
                    }
                },
                {
                    data: 'payload_json',
                    render: function (data) {
                        try { var p = JSON.parse(data); return p.authCode || ''; } catch(e) { return ''; }
                    }
                },
                {
                    data: 'processed',
                    render: function (data) {
                        return data ? '<span class="label label-success"><?php echo __("Yes"); ?></span>'
                                    : '<span class="label label-default"><?php echo __("No"); ?></span>';
                    }
                },
                { data: 'error_text', defaultContent: '' },
                {
                    data: 'created_php_time',
                    render: function (data) { return data ? new Date(data * 1000).toLocaleString() : ''; }
                }
            ]
        });
    });
</script>
