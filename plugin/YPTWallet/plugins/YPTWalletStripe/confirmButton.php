<?php
$obj = YouPHPTubePlugin::getObjectData('StripeYPT');
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
<button type="submit" class="btn btn-primary" id="YPTWalletStripeButton"><i class="fas fa-credit-card"></i> Pay Now</button>
<script src="https://js.stripe.com/v3/"></script>

<form action="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletStripe/requestPayment.json.php" method="post" id="payment-form" style="display: none;">
    <hr>
    <div class="panel panel-default">
        <div class="panel-heading"><strong>Credit or debit card</strong></div>
        <div class="panel-body">
            <div id="card-element">
                <!-- A Stripe Element will be inserted here. -->
            </div>
            <!-- Used to display form errors. -->
            <div id="card-errors" role="alert"></div>
        </div>
        <div class="panel-footer">

            <button class="btn btn-primary btn-block">Submit Payment</button>
        </div>
    </div>
</form>
<script>
    $(document).ready(function () {
        $('#YPTWalletStripeButton').click(function (evt) {
            evt.preventDefault();
            $('#payment-form').slideToggle();
        });
    });
    // Create a Stripe client.
    var stripe = Stripe('<?php echo $obj->Publishablekey; ?>');

    // Create an instance of Elements.
    var elements = stripe.elements();

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
    var card = elements.create('card', {style: style});

    // Add an instance of the card Element into the `card-element` <div>.
    card.mount('#card-element');

    // Handle real-time validation errors from the card Element.
    card.addEventListener('change', function (event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Handle form submission.
    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        stripe.createToken(card).then(function (result) {
            console.log(result);
            if (result.error) {
                // Inform the user if there was an error.
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // Send the token to your server.
                stripeTokenHandler(result.token);
            }
        });
    });

    // Submit the form with the token ID.
    function stripeTokenHandler(token) {

        modal.showPleaseWait();

        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletStripe/requestPayment.json.php',
            data: {
                "value": $('#value<?php echo @$_GET['plans_id']; ?>').val(),
                "stripeToken": token.id
            },
            type: 'post',
            success: function (response) {
                if (!response.error) {
                    $(".walletBalance").text(response.walletBalance);
                    swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Payment complete!"); ?>", "success");
                } else {
                    swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Error!"); ?>", "error");
                }
                setTimeout(function () {
                    modal.hidePleaseWait();
                }, 500);

            }
        });

    }
</script>