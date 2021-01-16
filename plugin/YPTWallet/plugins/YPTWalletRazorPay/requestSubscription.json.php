<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/Subscription/Subscription.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("RazorPayYPT");
$pluginS = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$objS = $pluginS->getDataObject();
$objR = $plugin->getDataObject();

$obj = new stdClass();
$obj->error = true;

$displayCurrency = $objS->currency;


$invoiceNumber = uniqid();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['recurrentSubscription']['plans_id']);
if (!empty($_POST['plans_id'])) {
    $_SESSION['recurrentSubscription']['plans_id'] = $_POST['plans_id'];
}

$subs = new SubscriptionPlansTable($_POST['plans_id']);

if (empty($subs)) {
    die("Plan Not found");
}

if (!User::isLogged()) {
    die("User not logged");
}

$users_id = User::getId();

//setUpSubscription($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement')
_error_log("Request subscription setUpSubscription: " . json_encode($_POST));
$payment = $plugin->setUpSubscription($_POST['plans_id']);
_error_log("Request subscription setUpSubscription Done ");
if (!empty($payment) && !empty($payment->status) && ($payment->status == "active" || $payment->status == "created")) {

    $data = [
        "key" => $objR->api_key,
        "subscription_id" => $payment->id,
        "subscription_card_change" => 0,
        "name" => $config->getWebSiteTitle() . " Payment",
        "image" => $config->getLogo(),
        "prefill" => [
            "name" => User::getName(),
            "email" => User::getEmail_()
        ],
        "notes" => [
            "users_id" => User::getId(),
            "plans_id" => $_POST['plans_id']
        ],
        "callback_url" => "{$global['webSiteRootURL']}plugin/YPTWallet/plugins/YPTWalletRazorPay/redirect_url.php",
        "redirect" => true
    ];

    if ($displayCurrency !== 'INR') {
        $data['display_currency'] = $displayCurrency;
        $data['display_amount'] = $displayAmount;
    }
    $json = json_encode($data);
    $obj->error = false;
} else {
    _error_log("Request subscription Stripe error: " . json_encode($payment));
}
if ($obj->error) {
    die("Error on Subscription request");
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title>Add Funds</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <style>
            .razorpay-payment-button{
                display: none;
            }
        </style>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="row">
                <div class="panel panel-default">
                    <div class="panel-heading"><?php echo __("Process Payment"); ?></div>
                    <div class="panel-body">
                        <h1><?php echo $subs->getName(); ?></h1>
                        <h3>
                            <?php
                            echo YPTWallet::formatCurrency($subs->getPrice());
                            ?>
                        </h3>
                        <div>
                            <?php
                            echo nl2br($subs->getDescription());
                            ?>
                        </div>
                        <form name='razorpayform' action="<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/plugins/YPTWalletRazorPay/redirect_url.php" method="POST">
                            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                            <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
                            <input type="hidden" name="users_id" value="<?php echo User::getId(); ?>">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {
                var options = <?php echo $json ?>;

                /**
                 * The entire list of Checkout fields is available at
                 * https://docs.razorpay.com/docs/checkout-form#checkout-fields
                 */
                options.handler = function (response) {
                    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                    document.getElementById('razorpay_signature').value = response.razorpay_signature;
                    document.razorpayform.submit();
                };

                options.modal = {
                    ondismiss: function () {
                        document.location = "<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/addFunds.php";
                        console.log("This code runs when the popup is closed");
                    },
                    // Boolean indicating whether pressing escape key
                    // should close the checkout form. (default: true)
                    escape: true,
                    // Boolean indicating whether clicking translucent blank
                    // space outside checkout form should close the form. (default: false)
                    backdropclose: false
                };

                var rzp = new Razorpay(options);

                rzp.open();
            });
        </script>
    </body>
</html>