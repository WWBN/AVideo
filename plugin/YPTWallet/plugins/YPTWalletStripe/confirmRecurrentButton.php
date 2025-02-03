<?php
global $planTitle;
$obj = AVideoPlugin::getObjectData('StripeYPT');
$uid = uniqid();
?>
<style>
    /**
 * The CSS shown here will not be introduced in the Quickstart guide, but shows
 * how you can use CSS to style your Element's container.
 */
    .StripeElement {
        box-sizing: border-box;

        height: 40px;

        padding: 10px 12px;

        border: 1px solid transparent;
        border-radius: 4px;
        background-color: white;

        box-shadow: 0 1px 3px 0 #e6ebf1;
        -webkit-transition: box-shadow 150ms ease;
        transition: box-shadow 150ms ease;
    }

    .StripeElement--focus {
        box-shadow: 0 1px 3px 0 #cfd7df;
    }

    .StripeElement--invalid {
        border-color: #fa755a;
    }

    .StripeElement--webkit-autofill {
        background-color: #fefde5 !important;
    }
</style>
<button type="submit" class="btn btn-primary" id="YPTWalletStripeButton<?php echo $uid; ?>"><i class="fas fa-credit-card"></i> <?php echo __($obj->subscriptionButtonLabel); ?></button>
<script src="https://js.stripe.com/v3/"></script>

<form action="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletStripe/requestSubscription.json.php" method="post" id="payment-form<?php echo $uid; ?>" style="display:none;">
    <hr>
    <div class="panel panel-default">
        <div class="panel-heading"><strong>Credit or debit card</strong></div>
        <div class="panel-body">
            <div id="card-element<?php echo $uid; ?>">
                <!-- A Stripe Element will be inserted here. -->
            </div>
            <!-- Used to display form errors. -->
            <div id="card-errors<?php echo $uid; ?>" role="alert"></div>
        </div>
        <div class="panel-footer">

            <button class="btn btn-primary btn-block"><?php echo __('Submit Payment'); ?></button>
        </div>
    </div>
</form>
<script>
    $(document).ready(function() {
        $('#YPTWalletStripeButton<?php echo $uid; ?>').click(function(evt) {
            evt.preventDefault();
            $('#payment-form<?php echo $uid; ?>').slideToggle();
        });
    });
    // Create a Stripe client.
    var stripe<?php echo $uid; ?> = Stripe('<?php echo $obj->Publishablekey; ?>');

    // Create an instance of Elements.
    var elements<?php echo $uid; ?> = stripe<?php echo $uid; ?>.elements();

    // Custom styling can be passed to options when creating an Element.
    // (Note that this demo uses a wider set of styles than the guide below.)
    var style = {
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
    var card<?php echo $uid; ?> = elements<?php echo $uid; ?>.create('card', {
        style: style
    });

    // Add an instance of the card Element into the `card-element` <div>.
    card<?php echo $uid; ?>.mount('#card-element<?php echo $uid; ?>');

    // Handle real-time validation errors from the card Element.
    card<?php echo $uid; ?>.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors<?php echo $uid; ?>');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Handle form submission.
    var form<?php echo $uid; ?> = document.getElementById('payment-form<?php echo $uid; ?>');
    form<?php echo $uid; ?>.addEventListener('submit', function(event) {
        event.preventDefault();
        stripe<?php echo $uid; ?>.createToken(card<?php echo $uid; ?>).then(function(result) {
            console.log(result);
            if (result.error) {
                // Inform the user if there was an error.
                var errorElement = document.getElementById('card-errors<?php echo $uid; ?>');
                errorElement.textContent = result.error.message;
            } else {
                // Send the token to your server.
                stripeTokenHandler<?php echo $uid; ?>(result.token);
            }
        });
    });

    // Submit the form with the token ID.
    function stripeTokenHandler<?php echo $uid; ?>(token) {
        console.log('stripeTokenHandler', token);

        function handlePaymentConfirmation(result) {
            avideoToastInfo('Finishig ...');
            console.log('stripeTokenHandler: handlePaymentConfirmation', result);
            if (result.error) {
                // Inform the user if there was an error.
                var errorElement = document.getElementById('card-errors<?php echo $uid; ?>');
                errorElement.textContent = result.error.message;
                avideoAlertError(result.error.message);
            } else {
                // Send the token to your server.
                avideoToast("<?php echo __("Payment Success"); ?>");
                updateYPTWallet();
                setTimeout(function() {
                    <?php
                    if (empty($global['paymentsTest'])) {
                        $url = YPTWallet::getAddFundsSuccessRedirectURL();
                        echo empty($url) ? 'location.reload();' : "window.top.location.href='{$url}'";
                    }else{
                        echo 'modal.hidePleaseWait();';
                    }
                    ?>
                }, 3000);
            }
        }

        function confirmSubscriptionPayment(response) {
            avideoToastInfo('Getting confirmation ...');
            console.log('stripeTokenHandler: confirmSubscriptionPayment', response);
            if (response.latest_invoice.payment_intent) {
                stripe<?php echo $uid; ?>.confirmCardPayment(
                    response.latest_invoice.payment_intent.client_secret, {
                        payment_method: {
                            card: card<?php echo $uid; ?>,
                        }
                    }
                ).then(handlePaymentConfirmation);
            } else {
                avideoAlertError(response.msg);
            }
        }


        function checkPayment(response) {
            avideoToastInfo('Checking ...');
            console.log('stripeTokenHandler: checkPayment', response);
            if (!response.error) {
                if(response.walletBalance){
                    $(".walletBalance").text(response.walletBalance);
                }
                setTimeout(function() {
                    avideoAlertSuccess("<?php echo __("Payment complete!"); ?>");
                }, 2000);
                <?php
                if (empty($global['paymentsTest'])) {
                    echo 'setTimeout(function() {
                            location.reload();
                        }, 5000);';
                }
                ?>
            } else if (response.confirmCardPayment) {
                avideoToastInfo(response.msg);
                confirmSubscriptionPayment(response.payment);
            } else {
                avideoResponse(response);
                setTimeout(function() {
                    modal.hidePleaseWait();
                }, 500);
            }

        }

        modal.showPleaseWait();
        console.log('stripeTokenHandler: requestSubscription');
        avideoToastInfo('Requesting ...');
        $.ajax({
            url: webSiteRootURL + 'plugin/YPTWallet/plugins/YPTWalletStripe/requestSubscription.json.php',
            data: {
                "value": $('#value<?php echo @$_GET['plans_id']; ?>').val(),
                "stripeToken": token.id,
                "plans_id": "<?php echo @$_GET['plans_id']; ?>"
            },
            type: 'post',
            success: checkPayment
        });
    }
</script>
