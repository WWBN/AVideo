<?php
require_once '../../videos/configuration.php';
if (!User::isLogged()) {
    forbiddenPage('Login required');
}
$paypal = AVideoPlugin::loadPluginIfEnabled('PayPalYPT');
$invoice = is_string($_GET['invoice'] ?? null) ? $_GET['invoice'] : '';
$checkout = (!empty($paypal) && PayPalYPT_checkout::isInstalled()) ? PayPalYPT_checkout::getByInvoice($invoice) : false;
if (empty($checkout) || intval($checkout['users_id']) !== intval(User::getId())) {
    forbiddenPage(__('Checkout not found'));
}
$status = PayPalYPT::getPublicCheckoutStatus($checkout);
$isSubscription = $checkout['purpose'] === 'subscription';
$isSandbox = PayPalYPT::getEnvironment() === 'sandbox';
$_page = new Page([__('PayPal payment')]);
$_page->setExtraStyles(['plugin/YPTWallet/plugins/YPTWalletStripe/checkout.css']);
?>
<div class="container walletCheckout" id="paypalCheckout" data-invoice="<?php echo xss_esc($checkout['invoice_number']); ?>">
    <div class="text-center walletCheckout-heading">
        <h1><i class="fab fa-paypal" aria-hidden="true"></i> <?php echo $isSubscription ? __('PayPal subscription') : __('PayPal payment'); ?></h1>
        <p><?php echo $isSubscription ? __('Automatic recurring billing through PayPal. You can cancel it in your account.') : __('One-time payment. No automatic renewal.'); ?></p>
    </div>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="col-sm-6">
                    <p class="text-muted"><?php echo __('Purchase'); ?></p>
                    <p class="lead"><?php echo xss_esc($status['description']); ?></p>
                    <dl>
                        <?php if (!empty($status['price']) && !empty($status['balance_applied'])) { ?>
                            <dt><?php echo __('Price'); ?></dt><dd><?php echo $status['price']; ?></dd>
                            <dt><?php echo __('Wallet balance applied'); ?></dt><dd><?php echo $status['balance_applied']; ?></dd>
                        <?php } ?>
                        <dt><?php echo $isSubscription ? __('Charged by PayPal each period') : __('Charged by PayPal'); ?></dt>
                        <dd class="walletCheckout-balance"><?php echo $status['amount']; ?></dd>
                    </dl>
                    <?php if ($isSandbox) { ?>
                        <p class="alert alert-warning stripeCheckout-test"><strong><?php echo __('Test mode'); ?></strong> &mdash; <?php echo __('PayPal Sandbox. No real payment is made.'); ?></p>
                    <?php } ?>
                </div>
                <div class="col-sm-6">
                    <ol class="stripeCheckout-steps" aria-label="<?php echo __('Checkout progress'); ?>">
                        <li data-checkout-step="1"><span>1</span> <?php echo __('Approve on PayPal'); ?></li>
                        <li data-checkout-step="2"><span>2</span> <?php echo __('Confirm payment'); ?></li>
                        <li data-checkout-step="3"><span>3</span> <?php echo __('Ready'); ?></li>
                    </ol>
                    <div class="alert alert-info stripeCheckout-status" id="paypalCheckoutStatus" role="status" aria-live="polite" aria-busy="true" tabindex="-1">
                        <strong id="paypalCheckoutTitle"><?php echo __('Checking your payment'); ?></strong>
                        <p id="paypalCheckoutMessage"><?php echo __('Please wait. No new payment will be made.'); ?></p>
                        <p id="paypalCheckoutAccess" class="hidden"></p>
                    </div>
                    <div class="stripeCheckout">
                        <a class="btn btn-success btn-block btn-lg hidden" id="paypalCheckoutContinue" target="_top"></a>
                        <a class="btn btn-primary btn-block hidden" id="paypalCheckoutApprove" target="_top"><i class="fab fa-paypal" aria-hidden="true"></i> <?php echo __('Continue on PayPal'); ?></a>
                        <button type="button" class="btn btn-default btn-block hidden" id="paypalCheckoutCheck"><i class="fas fa-sync" aria-hidden="true"></i> <?php echo __('Check payment status'); ?></button>
                        <a class="btn btn-link btn-block hidden" id="paypalCheckoutRetry" target="_top"></a>
                    </div>
                    <p class="text-muted"><small><?php echo __('Reference'); ?>: <?php echo xss_esc($checkout['invoice_number']); ?></small></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(function () {
    var texts = <?php echo json_encode([
        'waiting_title' => __('Waiting for your PayPal approval'),
        'waiting' => __('Approve the payment on PayPal to continue. Nothing has been charged yet.'),
        'canceled_title' => __('Payment canceled'),
        'canceled' => __('You canceled on PayPal. You were not charged.'),
        'confirming_title' => __('Confirming your payment'),
        'confirming' => __('PayPal approved your payment. We are confirming it now. Do not pay again.'),
        'pending_title' => __('Payment pending at PayPal'),
        'pending' => __('PayPal has not completed this payment yet. Your purchase is activated automatically when PayPal completes it. Do not pay again; you can leave this page and come back later.'),
        'active_title' => __('Subscription approved'),
        'active' => __('Your PayPal subscription was approved. Waiting for PayPal to confirm the first payment. Do not subscribe again.'),
        'trial_title' => __('Free trial started'),
        'trial' => __('Your PayPal subscription was approved and your free trial is active. PayPal charges you automatically when the trial ends.'),
        'rented_title' => __('Your rental is ready'),
        'rented' => __('Payment confirmed. Your video is unlocked.'),
        'live_title' => __('Your event access is ready'),
        'live_started' => __('Payment confirmed. You can open the broadcast now.'),
        'live_future' => __('Payment confirmed. Your purchase is saved; return when the event starts.'),
        'subscribed_title' => __('Subscription active'),
        'subscribed' => __('Payment confirmed. Your subscription is active.'),
        'credited_title' => __('Funds added'),
        'credited' => __('Payment confirmed and added to your wallet balance.'),
        'benefit_failed_title' => __('Payment received — purchase not completed'),
        'benefit_failed' => __('Your payment was added to your wallet, but the purchase could not be completed automatically. Use your wallet balance to complete it; do not pay again.'),
        'failed_title' => __('Payment not completed'),
        'failed' => __('PayPal did not complete this payment and nothing was credited. You can try again.'),
        'denied_title' => __('Payment declined'),
        'denied' => __('PayPal declined this payment and nothing was credited. You can try again with another payment method.'),
        'expired_title' => __('Payment expired'),
        'expired' => __('This PayPal approval expired before it was completed. You were not charged. You can start again.'),
        'refunded_title' => __('Payment refunded or reversed'),
        'refunded' => __('PayPal reports this payment as refunded or reversed. Contact support if you need help.'),
        'error_title' => __('We could not check the payment now'),
        'error' => __('Your payment is safe. Use Check payment status in a moment; do not pay again.'),
        'slow' => __('This is taking longer than usual. Use Check payment status to check again; no new charge will be made.'),
        'access_until' => __('Access until'),
        'starts' => __('Starts'),
        'try_again' => __('Try again'),
        'balance' => __('Wallet balance'),
    ]); ?>;
    var token = <?php echo json_encode(getToken()); ?>;
    var invoice = $('#paypalCheckout').data('invoice');
    var attempts = 0;
    var busy = false;
    var timer = null;
    var lastStage = '';

    function show(id, visible) { $(id).toggleClass('hidden', !visible); }

    function render(stage, title, message, step) {
        var box = $('#paypalCheckoutStatus');
        box.removeClass('alert-info alert-success alert-warning alert-danger')
            .addClass(stage === 'ready' ? 'alert-success' : (stage === 'error' ? 'alert-danger' : (stage === 'warning' ? 'alert-warning' : 'alert-info')))
            .attr('aria-busy', stage === 'busy' ? 'true' : 'false');
        $('#paypalCheckoutTitle').text(title);
        $('#paypalCheckoutMessage').text(message);
        $('[data-checkout-step]').removeClass('text-primary text-success');
        $('[data-checkout-step="' + step + '"]').addClass(stage === 'ready' ? 'text-success' : 'text-primary');
        if (lastStage !== stage + title) {
            lastStage = stage + title;
            if (stage !== 'busy') { box.trigger('focus'); }
        }
    }

    function apply(s) {
        show('#paypalCheckoutApprove', false);
        show('#paypalCheckoutContinue', false);
        show('#paypalCheckoutRetry', false);
        show('#paypalCheckoutAccess', false);
        var access = '';
        if (s.access_until) { access = texts.access_until + ': ' + s.access_until; }
        if (s.live_starts && s.live_started === false) { access = texts.starts + ': ' + s.live_starts + (access ? ' · ' + access : ''); }
        if (s.balance) { access += (access ? ' · ' : '') + texts.balance + ': ' + s.balance; }
        if (access) { $('#paypalCheckoutAccess').text(access); show('#paypalCheckoutAccess', true); }
        var cont = function (label) {
            if (s.continue_url) { $('#paypalCheckoutContinue').attr('href', s.continue_url).text(label || s.continue_label); show('#paypalCheckoutContinue', true); }
        };
        var retry = function () {
            if (s.retry_url) { $('#paypalCheckoutRetry').attr('href', s.retry_url).text(texts.try_again); show('#paypalCheckoutRetry', true); }
        };
        switch (s.status) {
            case 'creating':
            case 'created':
                if (s.detail === 'buyer_canceled') {
                    render('warning', texts.canceled_title, texts.canceled, 1);
                    retry();
                } else {
                    render('info', texts.waiting_title, texts.waiting, 1);
                    if (s.approval_url) { $('#paypalCheckoutApprove').attr('href', s.approval_url); show('#paypalCheckoutApprove', true); }
                }
                return false;
            case 'approved':
                render('busy', texts.confirming_title, texts.confirming, 2);
                return true;
            case 'pending':
                render('warning', texts.pending_title, texts.pending, 2);
                return true;
            case 'active':
                render('busy', texts.active_title, texts.active, 2);
                return true;
            case 'trial':
                render('ready', texts.trial_title, texts.trial, 3);
                cont();
                return false;
            case 'completed':
                var f = s.fulfillment;
                if (f === 'rented') { render('ready', texts.rented_title, texts.rented, 3); }
                else if (f === 'live_ready') { render('ready', texts.live_title, s.live_started ? texts.live_started : texts.live_future, 3); }
                else if (f === 'subscribed') { render('ready', texts.subscribed_title, texts.subscribed, 3); }
                else if (f === 'benefit_failed') { render('warning', texts.benefit_failed_title, texts.benefit_failed, 3); }
                else { render('ready', texts.credited_title, texts.credited, 3); }
                cont();
                if (typeof updateYPTWallet === 'function') { updateYPTWallet(); }
                return false;
            case 'failed':
            case 'denied':
            case 'expired':
            case 'refunded':
                render(s.status === 'refunded' ? 'warning' : 'error', texts[s.status + '_title'], texts[s.status], 2);
                if (s.status !== 'refunded') { retry(); } else { cont(); }
                return false;
        }
        render('error', texts.error_title, texts.error, 2);
        return false;
    }

    function check(manual) {
        if (busy) { return; }
        busy = true;
        clearTimeout(timer);
        $('#paypalCheckoutCheck').prop('disabled', true);
        $.ajax({url: webSiteRootURL + 'plugin/PayPalYPT/checkoutStatus.json.php', method: 'POST', dataType: 'json', timeout: 30000,
            data: {invoice: invoice, globalToken: token}
        }).done(function (response) {
            if (response.error) {
                render('error', texts.error_title, response.msg || texts.error, 2);
                show('#paypalCheckoutCheck', true);
                return;
            }
            var keepPolling = apply(response) || response.retry === true;
            attempts++;
            show('#paypalCheckoutCheck', !response.final && (!keepPolling || attempts >= 40 || manual));
            if (keepPolling && attempts < 40) {
                timer = setTimeout(function () { check(false); }, attempts < 10 ? 3000 : 6000);
            } else if (keepPolling) {
                $('#paypalCheckoutMessage').text(texts.slow);
            }
        }).fail(function () {
            render('error', texts.error_title, texts.error, 2);
            show('#paypalCheckoutCheck', true);
        }).always(function () {
            busy = false;
            $('#paypalCheckoutCheck').prop('disabled', false);
        });
    }

    $('#paypalCheckoutCheck').on('click', function () { attempts = 0; check(true); });
    apply(<?php echo json_encode($status); ?>);
    check(false);
});
</script>
<?php
$_page->print();
?>
