<?php
require_once '../../videos/configuration.php';
if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}
$_page = new Page([__('PayPal configuration')]);
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2><i class="fab fa-paypal"></i> <?php echo __('PayPal configuration'); ?></h2>
            <p><?php echo __('Check your PayPal setup before accepting payments. These checks only authenticate and read: no payment, plan, agreement, payout or refund is created, and no webhook is deleted.'); ?></p>
        </div>
        <div class="panel-body">
            <form id="paypal-configuration-form">
                <input type="hidden" name="globalToken" value="<?php echo xss_esc(getToken()); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync"></i> <?php echo __('Run checks'); ?></button>
                <button type="button" id="paypal-configure" class="btn btn-default" disabled><i class="fas fa-wrench"></i> <?php echo __('Configure webhook automatically'); ?></button>
                <a class="btn btn-link" href="https://github.com/WWBN/AVideo/wiki/PayPalYPT-Plugin" target="_blank" rel="noopener noreferrer"><?php echo __('Setup guide'); ?> <i class="fas fa-external-link-alt"></i></a>
            </form>
            <p class="help-block"><?php echo __('Automatic setup creates the webhook for this site URL only, or adds the missing events to it and keeps any other events. It saves the Webhook ID used to verify signatures. It never deletes webhooks, never touches other URLs and never changes the app permissions.'); ?></p>
            <div id="paypal-configuration-message" role="status" aria-live="polite" class="alert alert-info"><?php echo __('Run the checks to see the current configuration.'); ?></div>
            <div id="paypal-configuration-results" class="hidden">
                <div class="well well-sm">
                    <p><strong><?php echo __('Environment'); ?>:</strong> <span id="paypal-configuration-environment"></span> &nbsp; <strong><?php echo __('API'); ?>:</strong> <span id="paypal-configuration-api"></span> &nbsp; <strong><?php echo __('App'); ?>:</strong> <span id="paypal-configuration-app"></span></p>
                    <p><strong><?php echo __('Webhook URL'); ?>:</strong> <span id="paypal-configuration-webhook-url"></span></p>
                    <p><strong><?php echo __('IPN URL'); ?>:</strong> <span id="paypal-configuration-ipn-url"></span> &nbsp; <strong><?php echo __('IPN verification'); ?>:</strong> <span id="paypal-configuration-ipn-verify"></span></p>
                    <p><strong><?php echo __('Webhook events'); ?>:</strong> <span id="paypal-configuration-events"></span></p>
                </div>
                <div class="clearfix">
                    <div class="col-md-4">
                        <h3 class="h4 text-danger"><i class="fas fa-exclamation-circle"></i> <?php echo __('Action required'); ?></h3>
                        <div id="paypal-configuration-fail"></div>
                    </div>
                    <div class="col-md-4">
                        <h3 class="h4 text-warning"><i class="fas fa-question-circle"></i> <?php echo __('Not proven / review'); ?></h3>
                        <div id="paypal-configuration-review"></div>
                    </div>
                    <div class="col-md-4">
                        <h3 class="h4 text-success"><i class="fas fa-check-circle"></i> <?php echo __('Verified'); ?></h3>
                        <div id="paypal-configuration-pass"></div>
                    </div>
                </div>
                <h3 class="h4"><?php echo __('Scopes returned by PayPal for this app'); ?></h3>
                <p class="help-block"><?php echo __('PayPal lists scopes in the OAuth answer. A scope does not prove that the account is approved for a product, or that a write will succeed.'); ?></p>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th><?php echo __('Feature'); ?></th><th><?php echo __('REST API'); ?></th><th><?php echo __('Scope'); ?></th><th><?php echo __('Need'); ?></th><th><?php echo __('Verification'); ?></th></tr></thead>
                        <tbody id="paypal-configuration-permissions"></tbody>
                    </table>
                </div>
                <h3 class="h4"><?php echo __('Read probes'); ?></h3>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th><?php echo __('Resource'); ?></th><th><?php echo __('Verification'); ?></th></tr></thead>
                        <tbody id="paypal-configuration-probes"></tbody>
                    </table>
                </div>
                <h3 class="h4"><?php echo __('Webhooks for this URL'); ?></h3>
                <div id="paypal-configuration-webhooks"></div>
                <div class="alert alert-info"><?php echo __('Final test: complete a sandbox purchase with a Personal sandbox buyer, confirm an HTTP 200 webhook delivery in developer.paypal.com, then check the wallet, rental, subscription and video playback. Never retry a paid purchase just because access is still pending.'); ?></div>
            </div>
        </div>
    </div>
</div>
<script>
$(function () {
    var form = $('#paypal-configuration-form');
    var message = $('#paypal-configuration-message');
    var configureButton = $('#paypal-configure');
    var labels = <?php echo json_encode(['pass' => __('Verified'), 'warning' => __('Review needed'), 'fail' => __('Action required'), 'unknown' => __('Not proven')]); ?>;
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
        message.attr('class', 'alert alert-info').text(<?php echo json_encode(__('Checking PayPal configuration. Please wait.')); ?>);
        $.ajax({url: webSiteRootURL + 'plugin/PayPalYPT/configuration.json.php', method: 'POST', dataType: 'json', timeout: 90000,
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
            $('#paypal-configuration-environment').text(report.environment === 'live' ? <?php echo json_encode(__('LIVE — real payments')); ?> : <?php echo json_encode(__('SANDBOX — test payments')); ?>);
            $('#paypal-configuration-api').text(report.api_base);
            $('#paypal-configuration-app').text(report.app_id || labels.unknown);
            $('#paypal-configuration-webhook-url').text(report.webhook_url);
            $('#paypal-configuration-ipn-url').text(report.ipn_url);
            $('#paypal-configuration-ipn-verify').text(report.ipn_verification_url);
            $('#paypal-configuration-events').text(report.events.join(', '));
            var groups = {fail: $('#paypal-configuration-fail').empty(), review: $('#paypal-configuration-review').empty(), pass: $('#paypal-configuration-pass').empty()};
            report.checks.forEach(function (check) {
                var target = check.status === 'pass' ? groups.pass : (check.status === 'fail' ? groups.fail : groups.review);
                $('<div>').addClass('alert alert-' + (colors[check.status] || 'info'))
                    .append($('<strong>').text(check.label + ': ')).append(badge(check.status))
                    .append($('<p>').text(check.message)).appendTo(target);
            });
            $.each(groups, function (name, target) {
                if (!target.children().length) { $('<p>').addClass('text-muted').text('—').appendTo(target); }
            });
            var permissions = $('#paypal-configuration-permissions').empty();
            report.permissions.forEach(function (permission) {
                $('<tr>').append($('<td>').text(permission.resource)).append($('<td>').text(permission.api))
                    .append($('<td>').append($('<code>').text(permission.scope))).append($('<td>').text(permission.required))
                    .append($('<td>').append(badge(permission.status)).append($('<p>').text(permission.message))).appendTo(permissions);
            });
            if (!report.permissions.length) { $('<tr>').append($('<td colspan="5">').text(<?php echo json_encode(__('Authenticate successfully to read the scopes.')); ?>)).appendTo(permissions); }
            var probes = $('#paypal-configuration-probes').empty();
            report.probes.forEach(function (probe) {
                $('<tr>').append($('<td>').text(probe.resource))
                    .append($('<td>').append(badge(probe.status)).append($('<p>').text(probe.message))).appendTo(probes);
            });
            if (!report.probes.length) { $('<tr>').append($('<td colspan="2">').text(<?php echo json_encode(__('Authenticate successfully to run the probes.')); ?>)).appendTo(probes); }
            var webhooks = $('#paypal-configuration-webhooks').empty();
            if (!report.webhooks.length) { $('<p>').text(<?php echo json_encode(__('No webhook for this URL could be confirmed.')); ?>).appendTo(webhooks); }
            report.webhooks.forEach(function (webhook) {
                $('<div>').addClass('well well-sm').append($('<strong>').text(webhook.id))
                    .append($('<p>').text(webhook.events.join(', '))).appendTo(webhooks);
            });
            if (typeof report.other_webhooks === 'number') {
                $('<p>').addClass('help-block').text(<?php echo json_encode(__('Other webhooks in this app (other URLs, not changed by AVideo):')); ?> + ' ' + report.other_webhooks).appendTo(webhooks);
            }
            $('#paypal-configuration-results').removeClass('hidden');
            var failed = report.has_failures || (response.setup && response.setup.status === 'fail');
            message.attr('class', 'alert alert-' + (failed ? 'warning' : 'info'))
                .text(response.setup ? response.setup.message : (failed
                    ? <?php echo json_encode(__('Checks completed. Some items need action before accepting payments.')); ?>
                    : <?php echo json_encode(__('Checks completed. Review the items that still need a sandbox test.')); ?>));
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
