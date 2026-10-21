<?php
global $planTitle;
$obj = AVideoPlugin::getObjectData('StripeYPT');
$uid = uniqid();
$checkoutPlansId = intval($_GET['plans_id'] ?? 0);
$checkoutVideoId = intval($_GET['videos_id'] ?? 0);
$stripeCheckoutActivationLabel = __('Activate subscription');
$checkoutContinueURL = $checkoutVideoId > 0
    ? $global['webSiteRootURL'] . 'video/' . $checkoutVideoId . '?autoplay=1'
    : $global['webSiteRootURL'];
?>
<button type="button" class="btn btn-primary" id="YPTWalletStripeButton<?php echo $uid; ?>"><i class="fas fa-credit-card"></i> <?php echo __($obj->subscriptionButtonLabel); ?></button>
<script src="https://js.stripe.com/v3/"></script>
<form id="payment-form<?php echo $uid; ?>" class="stripeCheckout hidden">
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong><i class="fas fa-lock" aria-hidden="true"></i> <?php echo __('Secure subscription checkout'); ?></strong>
            <?php if (!empty($planTitle)) { ?><p><?php echo htmlspecialchars($planTitle, ENT_QUOTES, 'UTF-8'); ?></p><?php } ?>
        </div>
        <div class="panel-body">
            <?php include __DIR__ . '/checkoutStatus.php'; ?>
            <p class="text-muted"><?php echo __('Your subscription renews automatically. You can cancel from your account.'); ?></p>
            <div id="card-element<?php echo $uid; ?>"></div>
            <div id="card-errors<?php echo $uid; ?>" class="text-danger" role="alert"></div>
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-primary btn-block"><?php echo __('Subscribe and unlock access'); ?></button>
        </div>
    </div>
</form>
<script>
(function () {
    var form<?php echo $uid; ?> = document.getElementById('payment-form<?php echo $uid; ?>');
    var stripe = Stripe(<?php echo json_encode($obj->Publishablekey); ?>);
    var card = stripe.elements().create('card', {style: {base: {color: '#32325d', fontSize: '16px'}}});
    card.mount('#card-element<?php echo $uid; ?>');
    var busy = false, approved = false, completed = false, paymentSecret = '', pollTimer = null;
    var alreadyActive = false;
    var continueURL = <?php echo json_encode($checkoutContinueURL); ?>;
    <?php include __DIR__ . '/checkoutStatusScript.php'; ?>
    function message(text, stage, title) { checkoutStatus<?php echo $uid; ?>(text, stage, title); }
    function unlock(text) {
        busy = false;
        modal.hidePleaseWait();
        $(form<?php echo $uid; ?>).find('button').prop('disabled', false).text(approved || paymentSecret
            ? <?php echo json_encode(__('Check payment status')); ?> : <?php echo json_encode(__('Subscribe and unlock access')); ?>);
        message(text, approved ? 'pending' : 'error', approved
            ? <?php echo json_encode(__('Payment approved — access is pending')); ?> : <?php echo json_encode(__('Payment needs your attention')); ?>);
    }
    function complete() {
        if (completed) { return; }
        completed = true;
        busy = true;
        modal.hidePleaseWait();
        message(<?php echo json_encode(__('Your subscription is active. You can start watching now.')); ?>, 'ready', <?php echo json_encode(__('You are subscribed')); ?>);
        $('#checkout-continue<?php echo $uid; ?>').attr('href', continueURL).removeClass('hidden')
            .text(<?php echo json_encode($checkoutVideoId > 0 ? __('Watch Now') : __('Start watching')); ?>);
        $(form<?php echo $uid; ?>).find('button').prop('disabled', true);
        avideoToastSuccess(<?php echo json_encode(__('Subscription activated')); ?>);
        updateYPTWallet();
        <?php if (empty($global['paymentsTest'])) { ?>
        setTimeout(function () { window.top.location.href = continueURL; }, 2500);
        <?php } ?>
    }
    function checkAccess(attempt) {
        modal.hidePleaseWait();
        message(alreadyActive ? <?php echo json_encode(__('Checking your existing access. No new charge was made.')); ?>
            : <?php echo json_encode(__('Payment approved. We are activating your subscription. Do not pay again.')); ?>,
            'activating', alreadyActive ? <?php echo json_encode(__('Subscription found')); ?> : <?php echo json_encode(__('Payment approved')); ?>);
        function again() {
            if (attempt < 40) { pollTimer = setTimeout(function () { checkAccess(attempt + 1); }, 3000); }
            else { unlock(<?php echo json_encode(__('Your payment was approved, but activation is taking longer than expected. Check payment status to continue; no new charge will be made.')); ?>); }
        }
        $.ajax({
            url: webSiteRootURL + 'plugin/StripeYPT/subscriptionStatus.json.php',
            type: 'POST', dataType: 'json', timeout: 15000,
            data: {plans_id: <?php echo $checkoutPlansId; ?>, globalToken: <?php echo json_encode(getToken()); ?>},
            success: function (response) {
                if (!response.error && response.active) { complete(); } else { again(); }
            }, error: again
        });
    }
    function paymentResult(result) {
        modal.hidePleaseWait();
        if (result.error) {
            unlock(result.error.message);
        } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
            approved = true;
            checkAccess(0);
        } else { checkPayment(0); }
    }
    function checkPayment(attempt) {
        modal.hidePleaseWait();
        message(<?php echo json_encode(__('Checking your payment. No new charge will be made.')); ?>, 'processing', <?php echo json_encode(__('Confirming your payment')); ?>);
        function again() {
            if (attempt < 40) { pollTimer = setTimeout(function () { checkPayment(attempt + 1); }, 3000); }
            else { unlock(<?php echo json_encode(__('We could not confirm the payment yet. Check payment status before trying another payment.')); ?>); }
        }
        stripe.retrievePaymentIntent(paymentSecret).then(function (result) {
            if (result.error || !result.paymentIntent) { again(); return; }
            var status = result.paymentIntent.status;
            if (status === 'succeeded') { approved = true; checkAccess(0); }
            else if (['requires_action', 'requires_payment_method', 'requires_confirmation'].indexOf(status) !== -1) {
                message(<?php echo json_encode(__('Complete the bank confirmation to finish your subscription.')); ?>, 'processing', <?php echo json_encode(__('Confirm your payment')); ?>);
                stripe.confirmCardPayment(paymentSecret, {payment_method: {card: card}}).then(paymentResult).catch(again);
            } else if (status === 'canceled') {
                paymentSecret = '';
                unlock(<?php echo json_encode(__('This payment was canceled. Please try again.')); ?>);
            } else { again(); }
        }).catch(again);
    }
    $('#YPTWalletStripeButton<?php echo $uid; ?>').click(function () {
        $(form<?php echo $uid; ?>).toggleClass('hidden');
    });
    card.addEventListener('change', function (event) {
        document.getElementById('card-errors<?php echo $uid; ?>').textContent = event.error ? event.error.message : '';
    });
    form<?php echo $uid; ?>.addEventListener('submit', function (event) {
        event.preventDefault();
        if (busy || completed) { return; }
        busy = true;
        clearTimeout(pollTimer);
        $(form<?php echo $uid; ?>).find('button').prop('disabled', true);
        document.getElementById('card-errors<?php echo $uid; ?>').textContent = '';
        if (approved) { checkAccess(0); return; }
        if (paymentSecret) { checkPayment(0); return; }
        message(<?php echo json_encode(__('Preparing your subscription. Please wait and do not submit again.')); ?>, 'processing', <?php echo json_encode(__('Confirming your payment')); ?>);
        modal.showPleaseWait();
        stripe.createToken(card).then(function (result) {
            if (result.error) { unlock(result.error.message); return; }
            $.ajax({
                url: webSiteRootURL + 'plugin/YPTWallet/plugins/YPTWalletStripe/requestSubscription.json.php',
                type: 'POST', dataType: 'json', timeout: 60000,
                data: {stripeToken: result.token.id, plans_id: <?php echo $checkoutPlansId; ?>, globalToken: <?php echo json_encode(getToken()); ?>},
                success: function (response) {
                    modal.hidePleaseWait();
                    if (!response.error) { alreadyActive = !!response.already_active; approved = true; checkAccess(0); return; }
                    var intent = response.payment && response.payment.latest_invoice && response.payment.latest_invoice.payment_intent;
                    if (response.confirmCardPayment && intent && intent.client_secret) {
                        paymentSecret = intent.client_secret;
                        checkPayment(0);
                    } else { unlock(response.msg || <?php echo json_encode(__('Could not start the subscription. Please try again.')); ?>); }
                },
                error: function () {
                    unlock(<?php echo json_encode(__('The connection was interrupted. Check your account before retrying this subscription.')); ?>);
                }
            });
        }).catch(function () { unlock(<?php echo json_encode(__('Could not confirm the payment. Please try again.')); ?>); });
    });
})();
</script>
