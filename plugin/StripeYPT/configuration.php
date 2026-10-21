<?php
require_once '../../videos/configuration.php';
if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}
$_page = new Page([__('Stripe configuration')]);
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2><i class="fab fa-stripe"></i> <?php echo __('Stripe configuration'); ?></h2>
            <p><?php echo __('Check your payment setup before accepting payments. No charges or subscriptions are created by these checks.'); ?></p>
        </div>
        <div class="panel-body">
            <form id="stripe-configuration-form">
                <input type="hidden" name="globalToken" value="<?php echo xss_esc(getToken()); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync"></i> <?php echo __('Run checks'); ?></button>
                <button type="button" id="stripe-configure" class="btn btn-default" disabled><i class="fas fa-wrench"></i> <?php echo __('Configure webhook automatically'); ?></button>
                <a class="btn btn-link" href="https://github.com/WWBN/AVideo/wiki/StripeYPT-Plugin" target="_blank" rel="noopener noreferrer"><?php echo __('Setup guide'); ?> <i class="fas fa-external-link-alt"></i></a>
            </form>
            <p class="help-block"><?php echo __('Automatic setup needs Webhook Endpoints: Write. It creates the webhook and securely saves its new signing secret, or enables the existing endpoint and adds missing events. It preserves existing events and does not replace endpoints or change API key permissions.'); ?></p>
            <div id="stripe-configuration-message" role="status" aria-live="polite" class="alert alert-info"><?php echo __('Run the checks to see the current configuration.'); ?></div>
            <div id="stripe-configuration-results" class="hidden">
                <div class="well well-sm">
                    <strong><?php echo __('Mode'); ?>:</strong> <span id="stripe-configuration-mode"></span>
                    &nbsp; <strong><?php echo __('API version'); ?>:</strong> <span id="stripe-configuration-version"></span>
                    <p><strong><?php echo __('Webhook URL'); ?>:</strong> <span id="stripe-configuration-url"></span></p>
                </div>
                <h3><?php echo __('Configuration checks'); ?></h3>
                <div id="stripe-configuration-checks"></div>
                <h3><?php echo __('API permissions'); ?></h3>
                <p class="help-block"><?php echo __('Read probes confirm API access without creating payment objects. Write permissions require a Dashboard review or a sandbox checkout. A successful check does not guarantee payment approval or playback.'); ?></p>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th><?php echo __('Resource'); ?></th><th><?php echo __('Required access'); ?></th><th><?php echo __('Verification'); ?></th></tr></thead>
                        <tbody id="stripe-configuration-permissions"></tbody>
                    </table>
                </div>
                <h3><?php echo __('Matching webhooks'); ?></h3>
                <div id="stripe-configuration-webhooks"></div>
                <div class="alert alert-info"><?php echo __('Final test: complete a sandbox purchase, confirm an HTTP 200 delivery in Stripe, then check the wallet or rental, subscription access and video playback. Also test a declined card and 3D Secure. Never retry a charged payment just because access is still pending.'); ?></div>
            </div>
        </div>
    </div>
</div>
<script>
$(function () {
    var form = $('#stripe-configuration-form');
    var message = $('#stripe-configuration-message');
    var configureButton = $('#stripe-configure');
    var labels = <?php echo json_encode(['pass' => __('Verified'), 'warning' => __('Review needed'), 'fail' => __('Action required'), 'unknown' => __('Not verified')]); ?>;
    var colors = {pass: 'success', warning: 'warning', fail: 'danger', unknown: 'info'};
    var allowed = false;
    var busy = false;
    function badge(status) {
        return $('<span>').addClass('label label-' + (colors[status] || 'info')).text(labels[status] || labels.unknown);
    }
    function run(action) {
        if (busy) { return; }
        busy = true;
        form.find('button').prop('disabled', true);
        modal.showPleaseWait();
        message.attr('class', 'alert alert-info').text(<?php echo json_encode(__('Checking Stripe configuration. Please wait.')); ?>);
        $.ajax({url: webSiteRootURL + 'plugin/StripeYPT/configuration.json.php', method: 'POST', dataType: 'json', timeout: 90000,
            data: form.serialize() + '&action=' + action
        }).done(function (response) {
            if (response.error || !response.report) {
                allowed = false;
                message.attr('class', 'alert alert-danger').text(response.msg || <?php echo json_encode(__('An error occurred')); ?>);
                avideoToastError(message.text());
                return;
            }
            var report = response.report;
            allowed = report.can_configure;
            $('#stripe-configuration-mode').text(report.mode === 'live' ? <?php echo json_encode(__('LIVE — real payments')); ?> : (report.mode === 'test' ? <?php echo json_encode(__('SANDBOX — test payments')); ?> : labels.unknown));
            $('#stripe-configuration-version').text(report.api_version);
            $('#stripe-configuration-url').text(report.webhook_url);
            var checks = $('#stripe-configuration-checks').empty();
            report.checks.forEach(function (check) {
                $('<div>').addClass('alert alert-' + (colors[check.status] || 'info'))
                    .append($('<strong>').text(check.label + ': ')).append(badge(check.status))
                    .append($('<p>').text(check.message)).appendTo(checks);
            });
            var permissions = $('#stripe-configuration-permissions').empty();
            report.permissions.forEach(function (permission) {
                $('<tr>').append($('<td>').text(permission.resource)).append($('<td>').text(permission.required))
                    .append($('<td>').append(badge(permission.status)).append($('<p>').text(permission.message))).appendTo(permissions);
            });
            var webhooks = $('#stripe-configuration-webhooks').empty();
            if (!report.webhooks.length) { $('<p>').text(<?php echo json_encode(__('No matching endpoint could be confirmed.')); ?>).appendTo(webhooks); }
            report.webhooks.forEach(function (webhook) {
                $('<div>').addClass('well well-sm').append($('<strong>').text(webhook.id))
                    .append($('<p>').text(webhook.status + ' · ' + (webhook.api_version || <?php echo json_encode(__('Account default version')); ?>)))
                    .append($('<p>').text(webhook.enabled_events.join(', '))).appendTo(webhooks);
            });
            $('#stripe-configuration-results').removeClass('hidden');
            var failed = report.has_failures || (response.setup && response.setup.status === 'fail');
            message.attr('class', 'alert alert-' + (failed ? 'warning' : 'info'))
                .text(response.setup ? response.setup.message : <?php echo json_encode(__('Checks completed. Review the details below, including items that still need a sandbox test.')); ?>);
            if (response.setup) {
                if (response.setup.status === 'pass') { avideoToastSuccess(response.setup.message); }
                else { avideoToastError(response.setup.message); }
            }
        }).fail(function () {
            allowed = false;
            message.attr('class', 'alert alert-danger').text(<?php echo json_encode(__('The check could not finish. Reload the page and try again.')); ?>);
            avideoToastError(message.text());
        }).always(function () {
            busy = false;
            form.find('button[type="submit"]').prop('disabled', false);
            configureButton.prop('disabled', !allowed);
            modal.hidePleaseWait();
        });
    }
    form.on('submit', function (event) { event.preventDefault(); run('check'); });
    configureButton.on('click', function () { run('configure'); });
});
</script>
<?php $_page->print(); ?>
