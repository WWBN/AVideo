<?php
require_once '../../videos/configuration.php';

if (!User::isLogged()) {
    gotToLoginAndComeBackHere('Please login first');
}

$subscription_id = intval($_REQUEST['subscription_id']);

if (empty($subscription_id)) {
    forbiddenPage('subscription_id plugin not found');
}

$users_id = User::getId();

$stripe = AVideoPlugin::loadPlugin("StripeYPT");

//$subscriptions = $stripe->getAllSubscriptionsSearch($users_id, 0);
//var_dump($subscriptions->data);exit;

$subs = AVideoPlugin::loadPluginIfEnabled("Subscription");

if (empty($subs)) {
    forbiddenPage('Subscription plugin not found');
}

$s = new SubscriptionTable($subscription_id);

if (!User::isAdmin() && $users_id != $s->getUsers_id()) {
    forbiddenPage('This plan does not belong to you');
}

$cards = $stripe->getAllCreditCards($subscription_id);

global $planTitle;
$obj = AVideoPlugin::getObjectData('StripeYPT');
$_page = new Page(array("Credit cards"));
?>

<div class="container-fluid">
    <div id="card-list" class="panel panel-default">
        <div class="panel-heading">
            <h2><i class="fa fa-credit-card"></i> Manage Your Credit Cards</h2>
        </div>
        <div class="panel-body">
            <ul class="list-group" id="saved-cards">
                <li class="list-group-item text-center">Loading cards...</li>
            </ul>
        </div>
        <div class="panel-footer">
            <!-- Add New Card -->
            <form action="#" method="post" id="payment-form">
                <hr>
                <div class="panel panel-default">
                    <div class="panel-heading"><strong><i class="fa fa-plus"></i> Add a New Card</strong></div>
                    <div class="panel-body">
                        <div id="card-element"></div>
                        <div id="card-errors" role="alert"></div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary btn-block" type="submit">Add Card</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    $(document).ready(function () {
        loadCreditCards(); // Load cards when page loads
    });

    function loadCreditCards() {
        let subscriptionId = <?php echo $subscription_id; ?>;
        $.ajax({
            url: webSiteRootURL+'plugin/StripeYPT/listCreditCards.json.php',
            type: 'GET',
            data: { subscription_id: subscriptionId },
            dataType: 'json',
            success: function (response) {
                let cardList = $("#saved-cards");
                cardList.empty(); // Clear previous list

                if (response.error || !response.cards.length) {
                    cardList.append('<li class="list-group-item text-center">No saved cards.</li>');
                    return;
                }

                // Loop through the cards and append to the list
                response.cards.forEach(function (card) {
                    let cardItem = `
                        <li class="list-group-item">
                            <i class="fa fa-credit-card"></i>
                            <strong>${card.card.brand.toUpperCase()}</strong> - **** **** **** ${card.card.last4}
                            <span class="label label-info">Exp: ${card.card.exp_month}/${card.card.exp_year}</span>
                            <button class="btn btn-danger btn-xs pull-right remove-card" onclick="deleteCardFromStripe('${card.id}')">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </li>
                    `;
                    cardList.append(cardItem);
                });
            },
            error: function () {
                $("#saved-cards").html('<li class="list-group-item text-center">Failed to load cards.</li>');
            }
        });
    }


    // Create a Stripe client.
    var stripe = Stripe('<?php echo $obj->Publishablekey; ?>');
    var elements = stripe.elements();

    // Card Styling
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
    var card = elements.create('card', {
        style: style
    });
    card.mount('#card-element');

    // Handle real-time validation errors from the card Element.
    card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        displayError.textContent = event.error ? event.error.message : '';
    });

    // Handle form submission.
    $('#payment-form').on('submit', function(event) {
        event.preventDefault();
        stripe.createPaymentMethod({
                type: "card",
                card: card
            })
            .then(function(result) {
                if (result.error) {
                    $('#card-errors').text(result.error.message);
                } else {
                    addCardToStripe(result.paymentMethod.id);
                }
            });
    });

    function addCardToStripe(paymentMethodId) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/StripeYPT/addCard.json.php',
            data: {
                paymentMethodId: paymentMethodId,
                subscription_id: '<?php echo $subscription_id; ?>'
            },
            type: 'post',
            complete: function(resp) {
                avideoResponse(resp);
                modal.hidePleaseWait();
                loadCreditCards();
            }
        });
    }

    function deleteCardFromStripe(paymentMethodId) {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/StripeYPT/deleteCard.json.php',
            data: {
                paymentMethodId: paymentMethodId,
                subscription_id: '<?php echo $subscription_id; ?>'
            },
            type: 'post',
            complete: function(resp) {
                avideoResponse(resp);
                modal.hidePleaseWait();
                loadCreditCards();
            }
        });
    }
</script>

<?php
$_page->print();
?>
