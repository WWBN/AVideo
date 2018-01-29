<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;

class PayPalYPT extends PluginAbstract {

    public function getDescription() {
        return "Paypal module for several purposes<br>
            Go to Paypal developer Site here https://developer.paypal.com/developer/applications (you must have Paypal account, of course)
    <br>Click on Create App on right side of page
    <br>Choose name of your app and click Create App
    <br>Now you can see and manage everything include client ID and secret.";
    }

    public function getName() {
        return "PayPalYPT";
    }

    public function getUUID() {
        return "5f613a09-c0b6-4264-85cb-47ae076d949f";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->ClientID = "ASUkHFpWX0T8sr8EiGdLZ05m-RAb8l-hdRxoq-OXWmua2i7EUfqFkMZvSoGgH2LhK7zAqt29IiS2oRTn";
        $obj->ClientSecret = "ECxtMBsLr0cFwSCgI0uaDiVzEUbVlV3r_o_qaU-SOsQqCEOKPq4uGlr1C0mhdDmEyO30mw7-PF0bOnfo";
        $obj->currencyID = "USD";

        return $obj;
    }

    public function setUpPayment($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD") {
        global $global;

        require_once $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';

        $notify_url = "{$global['webSiteRootURL']}plugin/PayPalYPT/ipn.php";

        // After Step 2
        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new \PayPal\Api\Amount();
        $amount->setTotal($total);
        $amount->setCurrency($currency);

        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount);
        $transaction->setNotifyUrl($notify_url);
        $transaction->setInvoiceNumber($invoiceNumber);

        $redirectUrls = new \PayPal\Api\RedirectUrls();
        $redirectUrls->setReturnUrl($redirect_url)
                ->setCancelUrl($cancel_url);

        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions(array($transaction))
                ->setRedirectUrls($redirectUrls);

        // After Step 3
        try {
            $payment->create($apiContext);
            return $payment;
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            // This will print the detailed information on the exception.
            //REALLY HELPFUL FOR DEBUGGING
            error_log("PayPal Error: " . $ex->getData());
        }
        return false;
    }

    function executePayment() {
        global $global;
        require_once $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        // ### Approval Status
        // Determine if the user approved the payment or not
        // Get the payment Object by passing paymentId
        // payment id was previously stored in session in
        // CreatePaymentUsingPayPal.php
        $paymentId = $_GET['paymentId'];
        $payment = Payment::get($paymentId, $apiContext);
        $amount = self::getAmountFromPayment($payment);
        $total = $amount->total;
        $currency = $amount->currency;
        // ### Payment Execute
        // PaymentExecution object includes information necessary
        // to execute a PayPal account payment.
        // The payer_id is added to the request query parameters
        // when the user is redirected from paypal back to your site
        $execution = new PaymentExecution();
        $execution->setPayerId($_GET['PayerID']);
        // ### Optional Changes to Amount
        // If you wish to update the amount that you wish to charge the customer,
        // based on the shipping address or any other reason, you could
        // do that by passing the transaction object with just `amount` field in it.
        // Here is the example on how we changed the shipping to $1 more than before.
        $transaction = new Transaction();
        $amount = new Amount();
        //$details = new Details();
        $amount->setCurrency($currency);
        $amount->setTotal($total);
        //$amount->setDetails($details);
        $transaction->setAmount($amount);
        // Add the above transaction object inside our Execution object.
        $execution->addTransaction($transaction);
        try {
            // Execute the payment
            // (See bootstrap.php for more on `ApiContext`)
            $result = $payment->execute($execution, $apiContext);
            try {
                $payment = Payment::get($paymentId, $apiContext);
            } catch (Exception $ex) {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }
        return $payment;
    }

    static function getAmountFromPayment($payment) {
        if(!is_object($payment)){
            return false;
        }
        return $payment->getTransactions()[0]->amount;
    }

    function sendToPayPal($invoiceNumber, $redirect_url, $cancel_url, $total, $currency) {
        $payment = $this->setUpPayment($invoiceNumber, $redirect_url, $cancel_url, $total, $currency);
        if (!empty($payment)) {
            header("Location: {$payment->getApprovalLink()}");
            exit;
        }
    }

}
