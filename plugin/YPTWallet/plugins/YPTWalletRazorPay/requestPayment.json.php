<?php
if (empty($_POST['value'])) {
    die("The value is empty");
}

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

require_once ($global['systemRootPath'] . 'plugin/RazorPayYPT/razorpay-php/Razorpay.php');

// Create the Razorpay Order

use Razorpay\Api\Api;

$razorPay = AVideoPlugin::loadPlugin('RazorPayYPT');
$pluginS = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$objS = $pluginS->getDataObject();
$obj = $razorPay->getDataObject();

$api = new Api($obj->api_key, $obj->api_secret);

//
// We create an razorpay order using orders api
// Docs: https://docs.razorpay.com/docs/orders
//
$invoiceNumber = uniqid();

$displayCurrency = $objS->currency;

$orderData = [
    'receipt' => $invoiceNumber,
    'amount' => $_POST['value'] * 100, // 2000 rupees in paise
    'currency' => $displayCurrency,
    'payment_capture' => 1, // auto capture
    'notes' => array(
        'user' => User::getUserName(),
        'users_id' => User::getId()
    )
];

$data = [];
try {
    $razorpayOrder = $api->order->create($orderData);
} catch (Exception $exc) {
    if ($exc->getMessage() === "Currency is not supported") {
        echo "<a href='https://razorpay.com/docs/international-payments/#enable-or-disable-international-payments-from-the-dashboard'>Enable or Disable International Payments</a><br>";
    }
    error_log("Razorpay requestPayment.json.php: [" . $exc->getCode() . "] " . $exc->getMessage());
    error_log("Razorpay requestPayment.json.php: " . $exc->getTraceAsString());
    die("Fail to connect on RazorPay");
}


$razorpayOrderId = $razorpayOrder['id'];

$_SESSION['razorpay_order_id'] = $razorpayOrderId;

$displayAmount = $amount = $orderData['amount'];

if ($displayCurrency !== 'INR') {
    $url = "https://api.fixer.io/latest?symbols=$displayCurrency&base=INR";
    $exchange = _json_decode(file_get_contents($url), true);

    $displayAmount = $exchange['rates'][$displayCurrency] * $amount / 100;
}

$checkout = 'automatic';

if (isset($_GET['checkout']) and in_array($_GET['checkout'], ['automatic', 'manual'], true)) {
    $checkout = $_GET['checkout'];
}

$data = [
    "key" => $obj->api_key,
    "amount" => $amount,
    "name" => $config->getWebSiteTitle() . " Payment",
    "image" => $config->getLogo(),
    "prefill" => [
        "name" => User::getName(),
        "email" => User::getEmail_()
    ],
    "notes" => [
        "users_id" => User::getId(),
    ],
    "order_id" => $razorpayOrderId,
    "callback_url" => "{$global['webSiteRootURL']}plugin/YPTWallet/plugins/YPTWalletRazorPay/redirect_url.php",
    "redirect" => true
];

if ($displayCurrency !== 'INR') {
    $data['display_currency'] = $displayCurrency;
    $data['display_amount'] = $displayAmount;
}


$json = json_encode($data);

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

