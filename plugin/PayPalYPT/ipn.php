<?php
require_once './objects/PayPalPayments.php';
require_once './objects/Account.php';
require_once './objects/PluginBuy.php';
// STEP 1: read POST data
// Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
// Instead, read raw POST data from the input stream.
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
$req = 'cmd=_notify-validate';
if (function_exists('get_magic_quotes_gpc')) {
    $get_magic_quotes_exists = true;
}
foreach ($myPost as $key => $value) {
    if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
        $value = urlencode(stripslashes($value));
    } else {
        $value = urlencode($value);
    }
    $req .= "&$key=$value";
}

// Step 2: POST IPN data back to PayPal to validate
$ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
//$ch = curl_init('https://ipnpb.sandbox.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
// In wamp-like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set
// the directory path of the certificate as shown below:
// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
if (!($res = curl_exec($ch))) {
    error_log("Got " . curl_error($ch) . " when processing IPN data");
    curl_close($ch);
    exit;
}
curl_close($ch);

// inspect IPN validation result and act accordingly
if (strcmp($res, "VERIFIED") == 0) {
    error_log(print_r($_POST, true));
    // The IPN is verified, process it:
    // check whether the payment_status is Completed  
    // check that txn_id has not been previously processed
    // check that receiver_email is your Primary PayPal email
    // check that payment_amount/payment_currency are correct
    // process the notification
    // assign posted variables to local variables
    // $_POST['custom'] is the Item ID field
    
    $plugin = 0;
    parse_str($_POST['item_number'], $output);
    if(!empty($output)){
        if(!empty($output['plugin'])){
            $plugin = $output['plugin'];
        }
    }
    
    $item_name = $_POST['item_name'];
    $item_number = $_POST['item_number'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $payer_email = $_POST['payer_email'];
    $planId = $_POST['custom'];
    $invoice = $_POST['invoice'];

    if ($payment_status == "Completed") {
        if (empty($invoice)) {
            $msg = "** invoice is empty: " . print_r($_POST, true);
            error_log($msg);
            die($msg);
        }
        
        error_log("Payment status Completed");
        $paypal = new PayPalPayments("");
        $paypal->setItem_name($item_name);
        $paypal->setItem_number($item_number);
        $paypal->setMc_currency($payment_currency);
        $paypal->getMc_gross($payment_amount);
        $paypal->setPayer_email($payer_email);
        $paypal->setPayment_status($payment_status);
        $paypal->setReceiver_email($receiver_email);
        $paypal->setTxn_id($txn_id);
        $paypal->setCustom($planId);
        $paypal->setInvoice($invoice);

        if(empty($plugin)){
            error_log("Easy Tube Plan");
            // update plan
            $account = new Account($invoice);
            $account->setPlans_id($planId);
            $expire = "+30 days";
            $sotrageLimit = 30;
            switch ($planId) {
                case 2:
                    $expire = "+1 month";
                    $sotrageLimit = 120;
                    break;
                case 3:
                    $expire = "+3 months";
                    $sotrageLimit = 480;
                    break;
                case 4:
                    $expire = "+12 months";
                    $sotrageLimit = 1200;
                    break;
            }
            $date = strtotime($expire);
            $dateStr = date('Y-m-d h:i:s ', $date);
            $account->setExpiration($dateStr);
            $account->save();      
            
            $paypal->setAccounts_id($invoice);
            
            require_once './objects/create.php';
            $name = $account->getAccountname();
            $c = new Create($name, "");
            $c->createConfig($sotrageLimit);
        }else{
            error_log("Buy Plugin, plugin_id = {$plugin} : user_id = {$invoice}");
            // plugin buy
            $pluginObj = new PluginBuy(0);
            $pluginObj->setPlugins_id($plugin);
            $pluginObj->setUsers_id($invoice);
            $plugin_buy_id = $pluginObj->save();
            
            $paypal->setPlugin_buy_id($plugin_buy_id);
        }             
        
        
        $paypal->save();
        
    }
    else{
        error_log("Error Payment status {$payment_status}");
    }

    // IPN message values depend upon the type of notification sent.
    // To loop through the &_POST array and print the NV pairs to the screen:
    foreach ($_POST as $key => $value) {
        echo $key . " = " . $value . "<br>";
    }
} else if (strcmp($res, "INVALID") == 0) {
    // IPN invalid, log for manual investigation
    echo "The response from PayPal IPN was: <b>" . $res . "</b>";
    error_log("The response from PayPal IPN was: <b>" . $res . "</b>");
}
?>