<?php
$obj = AVideoPlugin::getObjectData('StripeYPT');
$uid = _uniqid();
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
<button type="submit" class="btn btn-primary" id="YPTWalletStripeButton<?php echo $uid; ?>"><i class="fas fa-credit-card"></i> <?php echo __($obj->paymentButtonLabel); ?></button>
<script src="https://js.stripe.com/v3/"></script>

<form action="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletStripe/requestPayment.json.php" method="post" id="payment-form<?php echo $uid; ?>" style="display:none;">
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
    $(document).ready(function () {
        $('#YPTWalletStripeButton<?php echo $uid; ?>').click(function (evt) {
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
    form<?php echo $uid; ?>.addEventListener('submit', function (event) {
        event.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/StripeYPT/getIntent.json.php',
            data: {
                "value": $('#value<?php echo @$_GET['plans_id']; ?>').val(),
                "description": $('#description<?php echo @$_GET['plans_id']; ?>').val(),
                "plans_id": "<?php echo @$_GET['plans_id']; ?>",
                "plugin": "<?php echo @$_REQUEST['plugin']; ?>",
                "user": "<?php echo User::getUserName() ?>",
                "pass": "<?php echo User::getUserPass(); ?>",
                "singlePayment": 1
            },
            type: 'post',
            success: function (response) {
                modal.hidePleaseWait();
                if (!response.error) {
                    console.log(response);
                    stripe<?php echo $uid; ?>.confirmCardPayment(
                            response.client_secret,{
                                payment_method: {card: card<?php echo $uid; ?>}
                            }
                    ).then(function (result) {
                        console.log(result);
                        if (result.error) {
                            // Inform the user if there was an error.
                            var errorElement = document.getElementById('card-errors<?php echo $uid; ?>');
                            errorElement.textContent = result.error.message;
                            avideoAlertError(result.error.message);
                        } else {
                            modal.showPleaseWait();
                            // Send the token to your server.
                            avideoToast("<?php echo __("Payment Success"); ?>");
                            updateYPTWallet();
                            setTimeout(function(){
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
                    });
                } else {
                    avideoAlertError(response.msg);
                }

            }
        });

    });

</script>
