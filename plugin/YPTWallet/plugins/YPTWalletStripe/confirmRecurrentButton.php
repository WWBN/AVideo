<?php
$obj = YouPHPTubePlugin::getObjectData('StripeYPT');
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
<button type="submit" class="btn btn-primary" id="YPTWalletStripeButton<?php echo $uid; ?>"><i class="fas fa-credit-card"></i> Subscribe</button>
<script src="https://js.stripe.com/v3/"></script>

<form 
    action="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletStripe/requestSubscription.json.php" 
    method="post" id="payment-form<?php echo $uid; ?>" style="display: none;">
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

            <button class="btn btn-primary btn-block">Submit Payment</button>
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
    var card<?php echo $uid; ?> = elements<?php echo $uid; ?>.create('card', {style: style});

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

        stripe<?php echo $uid; ?>.createToken(card<?php echo $uid; ?>).then(function (result) {
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

        modal.showPleaseWait();

        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletStripe/requestSubscription.json.php',
            data: {
                "value": $('#value<?php echo @$_GET['plans_id']; ?>').val(),
                "stripeToken": token.id,
                "plans_id": "<?php echo @$_GET['plans_id']; ?>"
            },
            type: 'post',
            success: function (response) {
                if (!response.error) {
                    $(".walletBalance").text(response.walletBalance);
                    setTimeout(function () {
                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Payment complete!"); ?>", "success");
                    }, 2000);
                    setTimeout(function () {
                        location.reload(); 
                    }, 5000);
                } else {
                    swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Error!"); ?>", "error");
                    setTimeout(function () {
                        modal.hidePleaseWait();
                    }, 500);
                }

            }
        });

    }
</script>