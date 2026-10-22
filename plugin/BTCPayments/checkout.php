<?php
require_once '../../videos/configuration.php';
if (!User::isLogged()) {
    forbiddenPage('Login required');
}
$plugin = AVideoPlugin::loadPluginIfEnabled('BTCPayments');
$invoiceId = is_string($_GET['invoice'] ?? null) ? $_GET['invoice'] : '';
$row = (!empty($plugin) && $invoiceId !== '') ? Btc_invoices::getFromInvoice_identification($invoiceId) : false;
if (empty($row) || intval($row['users_id']) !== intval(User::getId())) {
    forbiddenPage(__('Checkout not found'));
}
$status = BTCPayments::getPublicInvoiceStatus($row);
$_page = new Page([__('Bitcoin payment')]);
$_page->setExtraStyles(['plugin/YPTWallet/plugins/YPTWalletStripe/checkout.css', 'plugin/BTCPayments/checkout.css']);
$_page->setExtraScripts(['view/js/jquery.qrcode.min.js']);
?>
<div class="container walletCheckout btcCheckout" id="btcCheckout" data-invoice="<?php echo xss_esc($status['invoice']); ?>">
    <div class="btcCheckout-heading">
        <div class="btcCheckout-headingTitle">
            <span class="btcCheckout-brand text-primary"><i class="fab fa-bitcoin" aria-hidden="true"></i></span>
            <div><h1><?php echo __('Pay with Bitcoin'); ?></h1><p class="text-muted"><?php echo __('One-time payment. No automatic renewal.'); ?></p></div>
        </div>
        <?php echo getTourHelpButton('plugin/BTCPayments/checkout.help.json', 'btn btn-default btcCheckout-help', 'How to pay'); ?>
    </div>
    <div class="panel panel-default btcCheckout-card">
        <nav class="btcCheckout-progress" aria-label="<?php echo __('Checkout progress'); ?>">
            <ol class="stripeCheckout-steps btcCheckout-steps">
                <li data-checkout-step="1"><span class="btcCheckout-stepNumber"><b class="btcCheckout-stepIndex">1</b><i class="fas fa-check btcCheckout-stepCheck" aria-hidden="true"></i></span><span class="btcCheckout-stepLabel"><?php echo __('Send Bitcoin'); ?></span></li>
                <li data-checkout-step="2"><span class="btcCheckout-stepNumber"><b class="btcCheckout-stepIndex">2</b><i class="fas fa-check btcCheckout-stepCheck" aria-hidden="true"></i></span><span class="btcCheckout-stepLabel"><?php echo __('Confirm payment'); ?></span></li>
                <li data-checkout-step="3"><span class="btcCheckout-stepNumber"><b class="btcCheckout-stepIndex">3</b><i class="fas fa-check btcCheckout-stepCheck" aria-hidden="true"></i></span><span class="btcCheckout-stepLabel"><?php echo __('Ready'); ?></span></li>
            </ol>
        </nav>
        <aside class="panel-heading btcCheckout-summary" id="btcCheckoutSummary" aria-label="<?php echo __('Order summary'); ?>">
            <p class="btcCheckout-eyebrow text-muted"><?php echo __('Order summary'); ?></p>
            <p class="lead btcCheckout-description"><?php echo xss_esc($status['description']); ?></p>
            <dl class="btcCheckout-totals">
                <?php if (!empty($status['price']) && !empty($status['balance_applied'])) { ?>
                    <dt><?php echo __('Price'); ?></dt><dd><?php echo $status['price']; ?></dd>
                    <dt><?php echo __('Wallet balance applied'); ?></dt><dd><?php echo $status['balance_applied']; ?></dd>
                <?php } ?>
                <dt class="text-muted"><?php echo __('Amount to pay'); ?></dt>
                <dd class="walletCheckout-balance"><?php echo $status['amount']; ?></dd>
                <dt class="hidden" data-btc-amount-row><?php echo __('Bitcoin to send'); ?></dt>
                <dd class="btcCheckout-btcTotal hidden" data-btc-amount-row id="btcCheckoutBTCTotal"></dd>
            </dl>
            <div class="btcCheckout-facts">
                <p><i class="fas fa-check-circle text-success" aria-hidden="true"></i> <?php echo __('No automatic renewal'); ?></p>
                <p><i class="fas fa-network-wired" aria-hidden="true"></i> <?php echo $status['test'] ? __('Bitcoin testnet') : __('Bitcoin network'); ?></p>
            </div>
            <?php if ($status['test']) { ?>
                <div class="alert alert-warning stripeCheckout-test btcCheckout-test"><strong><?php echo __('Test mode'); ?></strong><p><?php echo __('No real money is used. Pay with a testnet wallet.'); ?></p></div>
            <?php } ?>
        </aside>
        <section class="panel-body btcCheckout-main" aria-label="<?php echo __('Payment'); ?>">
            <div class="alert alert-info stripeCheckout-status btcCheckout-status" id="btcCheckoutStatus" role="status" aria-live="polite" aria-busy="true" tabindex="-1">
                <span class="btcCheckout-statusIcon" aria-hidden="true"><i class="fas fa-hourglass-half" id="btcCheckoutStatusIcon"></i></span>
                <div><h2 id="btcCheckoutTitle"><?php echo __('Checking your payment'); ?></h2><p id="btcCheckoutMessage"><?php echo __('Please wait. No new payment will be made.'); ?></p><p id="btcCheckoutAccess" class="btcCheckout-access hidden"></p></div>
            </div>
            <div class="btcCheckout-pay hidden" id="btcCheckoutPay">
                <div class="btcCheckout-paymentGrid">
                    <div class="btcCheckout-scan">
                        <a class="btcCheckout-qr" id="btcCheckoutQRLink" href="#" aria-label="<?php echo __('Open in my Bitcoin wallet'); ?>"><span id="btcCheckoutQR"></span></a>
                        <p class="text-muted"><?php echo __('Scan with your Bitcoin wallet'); ?></p>
                    </div>
                    <div class="btcCheckout-fields">
                        <div class="form-group btcCheckout-amount">
                            <label for="btcCheckoutAmount"><?php echo __('Amount (BTC)'); ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control btcCheckout-copyField" id="btcCheckoutAmount" readonly spellcheck="false">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" data-btc-copy="#btcCheckoutAmount" aria-label="<?php echo __('Copy amount'); ?>"><i class="far fa-copy" aria-hidden="true"></i> <?php echo __('Copy'); ?></button>
                                </span>
                            </div>
                        </div>
                        <div class="form-group btcCheckout-address">
                            <label for="btcCheckoutAddress"><?php echo __('Bitcoin address'); ?></label>
                            <div class="input-group">
                                <textarea class="form-control btcCheckout-copyField" id="btcCheckoutAddress" rows="4" readonly spellcheck="false"></textarea>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" data-btc-copy="#btcCheckoutAddress" aria-label="<?php echo __('Copy Bitcoin address'); ?>"><i class="far fa-copy" aria-hidden="true"></i> <?php echo __('Copy'); ?></button>
                                </span>
                            </div>
                        </div>
                        <p class="btcCheckout-paymentNote text-muted"><i class="fas fa-info-circle" aria-hidden="true"></i> <?php echo __('Send the exact amount. Your wallet adds the network fee separately.'); ?></p>
                    </div>
                </div>
                <div class="btcCheckout-expiry">
                    <p class="text-muted"><i class="far fa-clock" aria-hidden="true"></i> <span id="btcCheckoutTimerText"></span></p>
                    <div class="progress btcCheckout-timer" aria-hidden="true"><div class="progress-bar" id="btcCheckoutTimerBar"></div></div>
                </div>
                <a class="btn btn-primary btn-block btn-lg btcCheckout-wallet" id="btcCheckoutWallet" href="#"><i class="fas fa-wallet" aria-hidden="true"></i> <?php echo __('Open in my Bitcoin wallet'); ?></a>
                <p class="btcCheckout-autoUpdate text-muted"><i class="fas fa-sync-alt" aria-hidden="true"></i> <?php echo __('Payment status updates automatically'); ?></p>
            </div>
            <div class="stripeCheckout btcCheckout-actions">
                <a class="btn btn-success btn-block btn-lg hidden" id="btcCheckoutContinue" target="_top"></a>
                <a class="btn btn-default btn-block hidden" id="btcCheckoutExternal" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt" aria-hidden="true"></i> <?php echo __('Open the payment page in a new tab'); ?></a>
                <a class="btn btn-default btn-block hidden" id="btcCheckoutReceipt" target="_blank" rel="noopener noreferrer"><i class="fas fa-receipt" aria-hidden="true"></i> <?php echo __('View receipt'); ?></a>
                <button type="button" class="btn btn-default btn-block hidden" id="btcCheckoutCheck"><i class="fas fa-sync" aria-hidden="true"></i> <?php echo __('Check payment status'); ?></button>
                <a class="btn btn-link btn-block hidden" id="btcCheckoutRetry" target="_top"></a>
            </div>
            <details class="btcCheckout-explainer">
                <summary><?php echo __('What happens after I pay?'); ?> <i class="fas fa-chevron-down" aria-hidden="true"></i></summary>
                <p class="text-muted"><?php echo __('We detect your payment automatically, then wait for the Bitcoin network to confirm it. You can leave this page and return using the same payment reference. If you already sent Bitcoin, do not pay again.'); ?></p>
            </details>
        </section>
        <footer class="btcCheckout-reference text-muted"><span><?php echo __('Payment reference'); ?></span><span class="btcCheckout-referenceValue"><?php echo xss_esc($status['invoice']); ?></span></footer>
    </div>
</div>
<script>
if (window.top !== window.self) {
    // BTCPay's "Return to store" inside an old modal, or any frame: show the status in the whole window.
    window.top.location.href = location.href;
}
$(function () {
    var texts = <?php echo json_encode([
        'waiting_title' => __('Waiting for your Bitcoin payment'),
        'waiting' => __('Scan the QR code or use the details below. This page updates automatically when your payment arrives.'),
        'partial_title' => __('Partial payment received'),
        'partial' => __('Part of the amount arrived. Send the remaining amount shown below before the time runs out.'),
        'preparing_title' => __('Preparing your Bitcoin payment'),
        'preparing' => __('Getting the payment address. If this takes too long, open the payment page in a new tab.'),
        'processing_title' => __('Payment received'),
        'processing' => __('Your Bitcoin payment arrived and is waiting for network confirmation. This usually takes a few minutes. Do not pay again; you can leave this page and come back later.'),
        'confirming_title' => __('Confirming your payment'),
        'confirming' => __('Your Bitcoin payment is confirmed. We are adding it to your account now. Do not pay again.'),
        'rented_title' => __('Your rental is ready'),
        'rented' => __('Payment confirmed. Your video is unlocked.'),
        'live_title' => __('Your event access is ready'),
        'live_started' => __('Payment confirmed. You can open the broadcast now.'),
        'live_future' => __('Payment confirmed. Your purchase is saved; return when the event starts.'),
        'credited_title' => __('Funds added'),
        'credited' => __('Payment confirmed and added to your wallet balance.'),
        'benefit_failed_title' => __('Payment received — purchase not completed'),
        'benefit_failed' => __('Your payment was added to your wallet, but the purchase could not be completed automatically. Use your wallet balance to complete it; do not pay again.'),
        'expired_title' => __('Payment time expired'),
        'expired' => __('No payment arrived before this Bitcoin invoice expired. You were not charged. You can start again.'),
        'late_title' => __('Payment arrived late'),
        'late' => __('Your payment arrived after this invoice expired, so it was not added automatically. Contact support with the reference on this page; do not pay again.'),
        'incomplete_title' => __('Incomplete payment'),
        'incomplete' => __('Less than the full amount arrived before this invoice expired, so nothing was added. Contact support with the reference on this page; do not pay this invoice again.'),
        'invalid_title' => __('Payment not accepted'),
        'invalid' => __('This Bitcoin invoice was marked invalid. If you sent a payment, contact support with the reference on this page.'),
        'error_title' => __('We could not check the payment now'),
        'error' => __('We could not refresh the payment status. Check again shortly. If you already sent Bitcoin, do not pay again.'),
        'slow' => __('This is taking longer than usual. Use Check payment status to check again; no new charge will be made.'),
        'time_left' => __('Time left to pay'),
        'time_up' => __('Time is up. Checking whether a payment arrived.'),
        'access_until' => __('Access until'),
        'starts' => __('Starts'),
        'try_again' => __('Try again'),
        'balance' => __('Wallet balance'),
    ]); ?>;
    var token = <?php echo json_encode(getToken()); ?>;
    var invoice = $('#btcCheckout').data('invoice');
    // script.js leaves the notifications of this invoice to this page.
    window.btcCheckoutInvoice = invoice;
    var startedAt = Date.now();
    var maxPollingMs = 60 * 60 * 1000;
    var busy = false;
    var timer = null;
    var countdown = null;
    var lastStage = '';
    var lastStatus = '';
    var shownURI = '';
    var announcedCredit = false;
    var isFinal = false;
    var paymentStep = 1;

    function show(id, visible) { $(id).toggleClass('hidden', !visible); }

    function render(stage, title, message, step) {
        // A failed refresh must not imply that Bitcoin was sent or confirmed.
        if (title === texts.error_title) { step = paymentStep; }
        else { paymentStep = step; }
        var box = $('#btcCheckoutStatus');
        box.removeClass('alert-info alert-success alert-warning alert-danger')
            .addClass(stage === 'ready' ? 'alert-success' : (stage === 'error' ? 'alert-danger' : (stage === 'warning' ? 'alert-warning' : 'alert-info')))
            .attr('aria-busy', stage === 'busy' ? 'true' : 'false');
        $('#btcCheckoutTitle').text(title);
        $('#btcCheckoutMessage').text(message);
        $('#btcCheckout').attr('data-payment-stage', stage);
        $('#btcCheckoutStatusIcon').attr('class', 'fas ' + (stage === 'ready' ? 'fa-check' : (stage === 'error' || stage === 'warning' ? 'fa-exclamation-triangle' : (stage === 'busy' ? 'fa-hourglass-half' : 'fa-wallet'))));
        $('[data-checkout-step]').removeClass('text-primary text-success is-complete').removeAttr('aria-current').each(function () {
            if (parseInt($(this).data('checkout-step'), 10) < step) { $(this).addClass('text-success is-complete'); }
        });
        $('[data-checkout-step="' + step + '"]').addClass(stage === 'ready' ? 'text-success is-complete' : 'text-primary').attr('aria-current', 'step');
        if (lastStage !== stage + title) {
            lastStage = stage + title;
            if (stage !== 'busy' && stage !== 'info') { box.trigger('focus'); }
        }
    }

    function formatTime(seconds) {
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function startCountdown(expiresIn, total) {
        clearInterval(countdown);
        var deadline = Date.now() + expiresIn * 1000;
        total = Math.max(total || 0, expiresIn, 1);
        var tick = function () {
            var left = Math.max(0, Math.round((deadline - Date.now()) / 1000));
            $('#btcCheckoutTimerBar').css('width', (left / total * 100) + '%')
                .toggleClass('progress-bar-warning', left <= 180 && left > 60)
                .toggleClass('progress-bar-danger', left <= 60);
            $('#btcCheckoutTimerText').text(left > 0 ? texts.time_left + ': ' + formatTime(left) : texts.time_up);
            if (left <= 0) {
                clearInterval(countdown);
                check(false);
            }
        };
        tick();
        countdown = setInterval(tick, 1000);
    }

    function showPayment(s) {
        var pay = s.payment;
        show('#btcCheckoutPay', true);
        $('#btcCheckoutAmount').val(pay.btc_amount);
        $('#btcCheckoutAddress').val(pay.address);
        $('#btcCheckoutWallet, #btcCheckoutQRLink').attr('href', pay.uri);
        $('#btcCheckoutBTCTotal').text(pay.btc_amount + ' BTC');
        show('[data-btc-amount-row]', true);
        if (shownURI !== pay.uri) {
            shownURI = pay.uri;
            $('#btcCheckoutQR').empty().qrcode({width: 200, height: 200, text: pay.uri});
        }
        startCountdown(s.expires_in, s.expires_total);
    }

    function apply(s) {
        show('#btcCheckoutContinue', false);
        show('#btcCheckoutRetry', false);
        show('#btcCheckoutAccess', false);
        show('#btcCheckoutReceipt', false);
        show('#btcCheckoutExternal', false);
        if (s.status !== 'new') {
            show('#btcCheckoutPay', false);
            clearInterval(countdown);
        }
        var access = '';
        if (s.access_until) { access = texts.access_until + ': ' + s.access_until; }
        if (s.live_starts && s.live_started === false) { access = texts.starts + ': ' + s.live_starts + (access ? ' · ' + access : ''); }
        if (s.balance && s.credited) { access += (access ? ' · ' : '') + texts.balance + ': ' + s.balance; }
        if (access) { $('#btcCheckoutAccess').text(access); show('#btcCheckoutAccess', true); }
        var cont = function () {
            if (s.continue_url) { $('#btcCheckoutContinue').attr('href', s.continue_url).text(s.continue_label); show('#btcCheckoutContinue', true); }
        };
        var retry = function () {
            if (s.retry_url) { $('#btcCheckoutRetry').attr('href', s.retry_url).text(texts.try_again); show('#btcCheckoutRetry', true); }
        };
        if (s.receipt_url) { $('#btcCheckoutReceipt').attr('href', s.receipt_url); show('#btcCheckoutReceipt', true); }
        lastStatus = s.status;
        if (s.credited) {
            var f = s.fulfillment;
            if (f === 'rented') { render('ready', texts.rented_title, texts.rented, 3); }
            else if (f === 'live_ready') { render('ready', texts.live_title, s.live_started ? texts.live_started : texts.live_future, 3); }
            else if (f === 'benefit_failed') { render('warning', texts.benefit_failed_title, texts.benefit_failed, 3); }
            else { render('ready', texts.credited_title, texts.credited, 3); }
            cont();
            if (!announcedCredit) {
                announcedCredit = true;
                if (typeof updateYPTWallet === 'function') { updateYPTWallet(); }
            }
            return false;
        }
        switch (s.status) {
            case 'new':
                if (s.payment) {
                    if (s.detail === 'PaidPartial') { render('warning', texts.partial_title, texts.partial, 1); }
                    else { render('info', texts.waiting_title, texts.waiting, 1); }
                    showPayment(s);
                } else {
                    render('busy', texts.preparing_title, texts.preparing, 1);
                }
                if (s.checkout_url) { $('#btcCheckoutExternal').attr('href', s.checkout_url); show('#btcCheckoutExternal', true); }
                return true;
            case 'processing':
                render('busy', texts.processing_title, texts.processing, 2);
                return true;
            case 'settled':
                render('busy', texts.confirming_title, texts.confirming, 2);
                return true;
            case 'expired':
                if (s.detail === 'PaidPartial') { render('warning', texts.incomplete_title, texts.incomplete, 1); }
                else if (s.detail === 'PaidLate') { render('warning', texts.late_title, texts.late, 2); }
                else { render('error', texts.expired_title, texts.expired, 1); retry(); }
                return false;
            case 'invalid':
                render('error', texts.invalid_title, texts.invalid, 1);
                retry();
                return false;
        }
        render('error', texts.error_title, texts.error, 2);
        return false;
    }

    function nextDelay() {
        var delay = lastStatus === 'settled' ? 3000 : (lastStatus === 'processing' ? 8000 : 5000);
        return document.hidden ? Math.max(delay, 15000) : delay;
    }

    function check(manual) {
        if (busy) { return; }
        busy = true;
        clearTimeout(timer);
        $('#btcCheckoutCheck').prop('disabled', true);
        $.ajax({url: webSiteRootURL + 'plugin/BTCPayments/checkoutStatus.json.php', method: 'POST', dataType: 'json', timeout: 30000,
            data: {invoice: invoice, globalToken: token}
        }).done(function (response) {
            if (response.error) {
                render('error', texts.error_title, response.msg || texts.error, 2);
                show('#btcCheckoutCheck', true);
                return;
            }
            isFinal = response.final === true;
            var keepPolling = apply(response) || response.retry === true;
            var tooLong = Date.now() - startedAt > maxPollingMs;
            show('#btcCheckoutCheck', !response.final && (!keepPolling || tooLong || manual));
            if (keepPolling && !tooLong) {
                timer = setTimeout(function () { check(false); }, nextDelay());
            } else if (keepPolling) {
                $('#btcCheckoutMessage').text(texts.slow);
            }
        }).fail(function () {
            render('error', texts.error_title, texts.error, 2);
            show('#btcCheckoutCheck', true);
            timer = setTimeout(function () { check(false); }, 15000);
        }).always(function () {
            busy = false;
            $('#btcCheckoutCheck').prop('disabled', false);
        });
    }

    $('#btcCheckoutCheck').on('click', function () { startedAt = Date.now(); check(true); });
    $('[data-btc-copy]').on('click', function (evt) {
        var value = $($(this).data('btc-copy')).val();
        if (value) { copyToClipboard(value, evt.pageY); }
    });
    $('.btcCheckout-copyField').on('focus click', function () { this.select(); });
    // A payment notification of this invoice (socket): check now instead of waiting for the next poll.
    document.addEventListener('BTCPayments', function (event) {
        if (event.detail && event.detail.id === invoice) { check(false); }
    });
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && !busy && !isFinal) { check(false); }
    });
    apply(<?php echo json_encode($status); ?>);
    check(false);
});
</script>
<?php
$_page->print();
?>
