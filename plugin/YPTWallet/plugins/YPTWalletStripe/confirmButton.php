<?php
$obj = AVideoPlugin::getObjectData('StripeYPT');
$uid = uniqid();
$isPpvCheckout = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/PayPerView/page/buy.php') !== false;
$isLiveCheckout = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/PayPerViewLive/View/buy.php') !== false;
$stripeCheckoutActivationLabel = $isLiveCheckout ? __('Activate event access') : ($isPpvCheckout ? __('Activate rental') : __('Account credit'));
?>
<button type="submit" class="btn btn-primary" id="YPTWalletStripeButton<?php echo $uid; ?>"><i class="fas fa-credit-card"></i> <?php echo __($obj->paymentButtonLabel); ?></button>
<script src="https://js.stripe.com/v3/"></script>

<form action="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletStripe/requestPayment.json.php" method="post" id="payment-form<?php echo $uid; ?>" class="stripeCheckout hidden">
    <hr>
    <div class="panel panel-default">
        <div class="panel-heading"><strong><i class="fas fa-lock" aria-hidden="true"></i> <?php echo __('Secure card payment'); ?></strong></div>
        <div class="panel-body">
            <?php include __DIR__ . '/checkoutStatus.php'; ?>
            <div id="card-element<?php echo $uid; ?>">
                <!-- A Stripe Element will be inserted here. -->
            </div>
            <!-- Used to display form errors. -->
            <div id="card-errors<?php echo $uid; ?>" class="text-danger" role="alert"></div>
        </div>
        <div class="panel-footer">

            <button class="btn btn-primary btn-block"><?php echo __('Submit Payment'); ?></button>
        </div>
    </div>
</form>
<script>
    $(document).ready(function () {
        $('#YPTWalletStripeButton<?php echo $uid; ?>').click(function (evt) {
            evt.preventDefault();
            $('#payment-form<?php echo $uid; ?>').toggleClass('hidden');
        });
    });
    // Create a Stripe client.
    var stripe<?php echo $uid; ?> = Stripe('<?php echo $obj->Publishablekey; ?>');

    // Create an instance of Elements.
    var elements<?php echo $uid; ?> = stripe<?php echo $uid; ?>.elements();

    // Custom styling can be passed to options when creating an Element.
    // (Note that this demo uses a wider set of styles than the guide below.)
    var style<?php echo $uid; ?> = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };

    // Create an instance of the card Element.
    var card<?php echo $uid; ?> = elements<?php echo $uid; ?>.create('card', {style: style<?php echo $uid; ?>});

    // Add an instance of the card Element into the `card-element` <div>.
    card<?php echo $uid; ?>.mount('#card-element<?php echo $uid; ?>');

    // Handle real-time validation errors from the card Element.
    card<?php echo $uid; ?>.addEventListener('change', function (event) {
        var displayError = document.getElementById('card-errors<?php echo $uid; ?>');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Handle form submission.
    var form<?php echo $uid; ?> = document.getElementById('payment-form<?php echo $uid; ?>');
    var paymentPending<?php echo $uid; ?> = false;
    var paymentStatusSecret<?php echo $uid; ?> = '';
    var paymentIntentId<?php echo $uid; ?> = '';
    var walletCreditPending<?php echo $uid; ?> = false;
    var rentalCheckoutURL<?php echo $uid; ?> = '';
    var rentalVideoId<?php echo $uid; ?> = 0;
    var rentalReady<?php echo $uid; ?> = false;
    var liveScheduleId<?php echo $uid; ?> = 0;
    var checkoutContinueLabel<?php echo $uid; ?> = '';
    <?php include __DIR__ . '/checkoutStatusScript.php'; ?>
    function paymentMessage<?php echo $uid; ?>(message) {
        checkoutStatus<?php echo $uid; ?>(message, walletCreditPending<?php echo $uid; ?> ? 'activating' : 'processing',
            walletCreditPending<?php echo $uid; ?>
                ? <?php echo json_encode(__('Payment approved')); ?> : <?php echo json_encode(__('Confirming your payment')); ?>);
    }
    function paymentError<?php echo $uid; ?>(message) {
        paymentPending<?php echo $uid; ?> = false;
        $(form<?php echo $uid; ?>).find('button').prop('disabled', false);
        $(form<?php echo $uid; ?>).find('button').text(paymentStatusSecret<?php echo $uid; ?> || walletCreditPending<?php echo $uid; ?>
            ? <?php echo json_encode(__('Check payment status')); ?>
            : <?php echo json_encode(__('Submit Payment')); ?>);
        modal.hidePleaseWait();
        checkoutStatus<?php echo $uid; ?>(message, walletCreditPending<?php echo $uid; ?> ? 'pending' : 'error',
            walletCreditPending<?php echo $uid; ?> ? <?php echo json_encode(__('Payment approved — access is pending')); ?> : <?php echo json_encode(__('Payment needs your attention')); ?>);
    }
    function paymentComplete<?php echo $uid; ?>(message) {
        paymentStatusSecret<?php echo $uid; ?> = '';
        walletCreditPending<?php echo $uid; ?> = false;
        // Keep the button disabled until navigation, including in paymentsTest mode.
        modal.hidePleaseWait();
        checkoutStatus<?php echo $uid; ?>(message, 'ready', <?php echo json_encode(__('Payment confirmed')); ?>);
        var continueURL = rentalCheckoutURL<?php echo $uid; ?> || <?php echo json_encode(YPTWallet::getAddFundsSuccessRedirectURL()); ?>;
        if (continueURL) {
            $('#checkout-continue<?php echo $uid; ?>').attr('href', continueURL).removeClass('hidden')
                .text(checkoutContinueLabel<?php echo $uid; ?> || (rentalReady<?php echo $uid; ?> ? <?php echo json_encode(__('Watch Now')); ?> : <?php echo json_encode(__('Continue')); ?>));
        }
        avideoToastSuccess(message);
        updateYPTWallet();
        setTimeout(function() {
            <?php
            if (empty($global['paymentsTest'])) {
                $url = YPTWallet::getAddFundsSuccessRedirectURL();
                echo 'if (rentalCheckoutURL' . $uid . ') { window.top.location.href = rentalCheckoutURL' . $uid . '; } else { ';
                echo empty($url) ? 'location.reload();' : 'window.top.location.href=' . json_encode($url) . ';';
                echo ' }';
            }
            ?>
        }, 3000);
    }
    function checkWalletCredit<?php echo $uid; ?>(attempt) {
        walletCreditPending<?php echo $uid; ?> = true;
        modal.hidePleaseWait();
        paymentMessage<?php echo $uid; ?>(liveScheduleId<?php echo $uid; ?>
            ? <?php echo json_encode(__('Your card payment was approved. Activating your event access. Do not pay again.')); ?> : rentalCheckoutURL<?php echo $uid; ?>
            ? <?php echo json_encode(__('Your card payment was approved. Finalizing your rental. Do not pay again.')); ?>
            : <?php echo json_encode(__('Your card payment was approved. Waiting for your account credit. Do not pay again.')); ?>);
        function checkAgain() {
            if (attempt < 40) {
                setTimeout(function() { checkWalletCredit<?php echo $uid; ?>(attempt + 1); }, 3000);
            } else {
                paymentError<?php echo $uid; ?>(liveScheduleId<?php echo $uid; ?>
                    ? <?php echo json_encode(__('Your card payment was approved, but your event access is still processing. Use Check payment status; do not pay again.')); ?> : rentalCheckoutURL<?php echo $uid; ?>
                    ? <?php echo json_encode(__('Your card payment was approved, but your rental is still processing. Use Check payment status; do not pay again.')); ?>
                    : <?php echo json_encode(__('Your card payment was approved, but your account credit is still pending. Use Check payment status; do not pay again.')); ?>);
            }
        }
        $.ajax({
            url: webSiteRootURL + 'plugin/StripeYPT/singlePaymentStatus.json.php',
            type: 'POST',
            dataType: 'json',
            timeout: 15000,
            data: {payment_intent_id: paymentIntentId<?php echo $uid; ?>, videos_id: rentalVideoId<?php echo $uid; ?>,
                ppvlive_schedule_id: liveScheduleId<?php echo $uid; ?>, globalToken: <?php echo json_encode(getToken()); ?>},
            success: function(response) {
                if (!response.error && response.credited) {
                    if (liveScheduleId<?php echo $uid; ?>) {
                        if (response.live_ready === true && response.watch_url) {
                            rentalReady<?php echo $uid; ?> = true;
                            rentalCheckoutURL<?php echo $uid; ?> = response.watch_url;
                            checkoutContinueLabel<?php echo $uid; ?> = response.live_started === false ? <?php echo json_encode(__('View event details')); ?> : '';
                            paymentComplete<?php echo $uid; ?>(response.live_started === false
                                ? <?php echo json_encode(__('Your purchase is saved. Opening your event details. Return when the event starts.')); ?>
                                : <?php echo json_encode(__('Your live event access is ready. Opening the broadcast.')); ?>);
                        } else {
                            paymentComplete<?php echo $uid; ?>(<?php echo json_encode(__('Your payment is in your wallet. Complete the event purchase on the next page using this balance; do not pay by card again.')); ?>);
                        }
                        return;
                    }
                    if (response.rental_ready === true) {
                        rentalReady<?php echo $uid; ?> = true;
                        rentalCheckoutURL<?php echo $uid; ?> = webSiteRootURL + 'video/' + rentalVideoId<?php echo $uid; ?> + '?autoplay=1';
                        paymentComplete<?php echo $uid; ?>(<?php echo json_encode(__('Your rental is ready. Opening your video.')); ?>);
                        return;
                    }
                    if (response.rental_ready === false && rentalCheckoutURL<?php echo $uid; ?>) {
                        paymentComplete<?php echo $uid; ?>(<?php echo json_encode(__('Your payment is in your wallet. Complete the rental on the next page using this balance; do not pay by card again.')); ?>);
                        return;
                    }
                    // Credited does not prove rented (a failed rental keeps the credit): the next page shows access or Pay Now.
                    paymentComplete<?php echo $uid; ?>(rentalCheckoutURL<?php echo $uid; ?>
                        ? <?php echo json_encode(__('Payment confirmed. Opening your rental.')); ?>
                        : <?php echo json_encode($isPpvCheckout
                        ? __('Funds added. Complete your rental on the next page.') : __('Funds added successfully.')); ?>);
                } else {
                    checkAgain();
                }
            },
            error: checkAgain
        });
    }
    function checkPaymentStatus<?php echo $uid; ?>(attempt) {
        modal.hidePleaseWait();
        paymentMessage<?php echo $uid; ?>(<?php echo json_encode(__('Payment is still processing. Checking its status; no new charge will be made.')); ?>);
        function checkAgain() {
            if (attempt < 40) {
                setTimeout(function() {
                    checkPaymentStatus<?php echo $uid; ?>(attempt + 1);
                }, 3000);
            } else {
                // Keep the same secret: the next click only checks this payment.
                paymentError<?php echo $uid; ?>(<?php echo json_encode(__('Payment confirmation is taking longer than expected. Use Check payment status to check again. No new charge will be made.')); ?>);
            }
        }
        stripe<?php echo $uid; ?>.retrievePaymentIntent(paymentStatusSecret<?php echo $uid; ?>).then(function(result) {
            if (result.error || !result.paymentIntent) {
                checkAgain();
                return;
            }
            var intent = result.paymentIntent;
            if (intent.status === 'succeeded') {
                paymentIntentId<?php echo $uid; ?> = intent.id || paymentIntentId<?php echo $uid; ?>;
                checkWalletCredit<?php echo $uid; ?>(0);
            } else if (['canceled', 'requires_payment_method', 'requires_action', 'requires_confirmation'].indexOf(intent.status) !== -1) {
                paymentStatusSecret<?php echo $uid; ?> = '';
                paymentError<?php echo $uid; ?>(<?php echo json_encode(__('Payment was not completed. Please check your card details and try again.')); ?>);
            } else {
                checkAgain();
            }
        }).catch(checkAgain);
    }
    form<?php echo $uid; ?>.addEventListener('submit', function (event) {
        event.preventDefault();
        if (paymentPending<?php echo $uid; ?>) {
            return;
        }
        paymentPending<?php echo $uid; ?> = true;
        document.getElementById('card-errors<?php echo $uid; ?>').textContent = '';
        $(form<?php echo $uid; ?>).find('button').prop('disabled', true);
        if (walletCreditPending<?php echo $uid; ?>) {
            checkWalletCredit<?php echo $uid; ?>(0);
            return;
        }
        if (paymentStatusSecret<?php echo $uid; ?>) {
            checkPaymentStatus<?php echo $uid; ?>(0);
            return;
        }
        paymentMessage<?php echo $uid; ?>(<?php echo json_encode(__('Processing payment. Please wait and do not submit again.')); ?>);
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/StripeYPT/getIntent.json.php',
            data: {
                "value": $('#value<?php echo @$_GET['plans_id']; ?>').val(),
                "description": $('#description<?php echo @$_GET['plans_id']; ?>').val(),
                "plans_id": "<?php echo @$_GET['plans_id']; ?>",
                "plugin": <?php echo json_encode($isLiveCheckout ? 'PayPerViewLive' : ($isPpvCheckout ? 'PayPerView' : (string)($_REQUEST['plugin'] ?? ''))); ?>,
                "ppvlive_schedule_id": <?php echo $isLiveCheckout ? intval($_GET['ppvlive_schedule_id'] ?? 0) : 0; ?>,
                "videos_id": <?php echo $isPpvCheckout ? "$('#videos_id_" . intval($_GET['plans_id'] ?? 0) . "').val()" : '0'; ?>,
                "singlePayment": 1
            },
            type: 'post',
            dataType: 'json',
            timeout: 60000,
            success: function (response) {
                paymentIntentId<?php echo $uid; ?> = response.intent ? response.intent.id : '';
                var rental = response.intent && response.intent.metadata;
                if (rental && rental.ppvlive_auto_buy === '1') {
                    liveScheduleId<?php echo $uid; ?> = parseInt(rental.ppvlive_schedule_id, 10) || 0;
                    rentalCheckoutURL<?php echo $uid; ?> = webSiteRootURL + 'plugin/PayPerViewLive/View/buy.php?users_id='
                        + <?php echo intval($_GET['users_id'] ?? 0); ?> + '&ppvlive_schedule_id=' + encodeURIComponent(liveScheduleId<?php echo $uid; ?>);
                }
                if (rental && rental.ppv_auto_rent === '1') {
                    rentalVideoId<?php echo $uid; ?> = parseInt(rental.videos_id, 10) || 0;
                    // Bind the return page to this payment, not another tab's shared session redirect.
                    rentalCheckoutURL<?php echo $uid; ?> = webSiteRootURL + 'plugin/PayPerView/subscribePlanPerUser.php?plans_id='
                        + encodeURIComponent(rental.ppv_plans_id) + '&videos_id=' + encodeURIComponent(rental.videos_id) + '&purchased=1';
                }
                if (response.already_paid) {
                    checkWalletCredit<?php echo $uid; ?>(0);
                    return;
                }
                if (!response.error) {
                    // Allow Stripe's authentication dialog; the submit guard stays active.
                    modal.hidePleaseWait();
                    paymentMessage<?php echo $uid; ?>(<?php echo json_encode(__('Confirming your card. If your bank opens a confirmation window, complete it to continue. Do not submit another payment.')); ?>);
                    stripe<?php echo $uid; ?>.confirmCardPayment(
                            response.client_secret,{
                                payment_method: {card: card<?php echo $uid; ?>}
                            }
                    ).then(function (result) {
                        if (result.error) {
                            paymentError<?php echo $uid; ?>(result.error.message);
                        } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                            paymentIntentId<?php echo $uid; ?> = result.paymentIntent.id || paymentIntentId<?php echo $uid; ?>;
                            checkWalletCredit<?php echo $uid; ?>(0);
                        } else {
                            paymentStatusSecret<?php echo $uid; ?> = response.client_secret;
                            checkPaymentStatus<?php echo $uid; ?>(0);
                        }
                    }).catch(function() {
                        paymentStatusSecret<?php echo $uid; ?> = response.client_secret;
                        checkPaymentStatus<?php echo $uid; ?>(0);
                    });
                } else {
                    paymentError<?php echo $uid; ?>(response.msg);
                }
            },
            error: function() {
                paymentError<?php echo $uid; ?>(<?php echo json_encode(__('Could not confirm the payment status. Please try again to check the same payment.')); ?>);
            }
        });

    });

</script>
