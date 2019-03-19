<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\Plan;
use PayPal\Api\ShippingAddress;

//require_once $global['systemRootPath'] . 'plugin/PayPalYPT/vendor/paypal/rest-api-sdk-php/lib/PayPal/Api/Plan.php';

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

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->ClientID = "ASUkHFpWX0T8sr8EiGdLZ05m-RAb8l-hdRxoq-OXWmua2i7EUfqFkMZvSoGgH2LhK7zAqt29IiS2oRTn";
        $obj->ClientSecret = "ECxtMBsLr0cFwSCgI0uaDiVzEUbVlV3r_o_qaU-SOsQqCEOKPq4uGlr1C0mhdDmEyO30mw7-PF0bOnfo";
        $obj->disableSandbox = false;
        return $obj;
    }

    public function setUpPayment($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $description = "") {
        global $global;

        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
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
        $transaction->setDescription($description);

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

    private function executePayment() {
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

    private function createBillingPlan($redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement') {
        global $global;

        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        $notify_url = "{$global['webSiteRootURL']}plugin/PayPalYPT/ipn.php";
        // Create a new billing plan
        $plan = new Plan();
        $plan->setName($name)
                ->setDescription($name)
                ->setType('INFINITE');

        // Set billing plan definitions
        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
                ->setType('REGULAR')
                ->setFrequency($frequency)
                ->setFrequencyInterval($interval)
                ->setCycles('0')
                ->setAmount(new Currency(array('value' => $total, 'currency' => $currency)));

        // Set merchant preferences
        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences->setReturnUrl($redirect_url)
                ->setCancelUrl($cancel_url)
                //->setNotifyUrl($notify_url)
                ->setAutoBillAmount('yes')
                ->setInitialFailAmountAction('CONTINUE')
                ->setMaxFailAttempts('0')
                ->setSetupFee(new Currency(array('value' => $total, 'currency' => $currency)));

        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);

        //create plan
        try {
            $createdPlan = $plan->create($apiContext);

            try {
                $patch = new Patch();
                $value = new PayPalModel('{"state":"ACTIVE"}');
                $patch->setOp('replace')
                        ->setPath('/')
                        ->setValue($value);
                $patchRequest = new PatchRequest();
                $patchRequest->addPatch($patch);
                $createdPlan->update($patchRequest, $apiContext);
                
                $plan = Plan::get($createdPlan->getId(), $apiContext);
                error_log("createBillingPlan: " . json_encode(array($redirect_url, $cancel_url, $total, $currency, $frequency, $interval, $name)));
                // Output plan id
                return $plan;
            } catch (PayPal\Exception\PayPalConnectionException $ex) {
                error_log("PayPal Error createBillingPlan 1: " . $ex->getData());
            } catch (Exception $ex) {
                error_log("PayPal Error createBillingPlan 2: " . $ex->getData());
            }
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            error_log("PayPal Error createBillingPlan 3: " . $ex->getData());
        } catch (Exception $ex) {
            error_log("PayPal Error createBillingPlan 4: " . $ex->getData());
        }
        return false;
    }

    private function getPlanId() {
        global $global;
        if (!empty($_POST['plans_id'])) {
            $s = new SubscriptionPlansTable($_POST['plans_id']);
            $plan_id = $s->getPaypal_plan_id();
            require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
            try {
                $plan = Plan::get($plan_id, $apiContext);
                if (!empty($plan)) {
                    return $plan->getId();
                }
            } catch (Exception $ex) {
                return false;
            }
        }
        return false;
    }

    public function setUpSubscription($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement') {
        global $global;

        require_once $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';

        $notify_url = "{$global['webSiteRootURL']}plugin/PayPalYPT/ipn.php";

        $planId = $this->getPlanId();
        if (empty($planId)) {
            //createBillingPlan($redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement') 
            $plan = $this->createBillingPlan($redirect_url, $cancel_url, $total, $currency, $frequency, $interval, $name);

            if (empty($plan)) {
                error_log("PayPal Error setUpSubscription Plan ID is empty ");
                return false;
            }
            $planId = $plan->getId();
            // save the paypal plan ID for reuse
            if(!empty($_POST['plans_id'])){
                $s = new SubscriptionPlansTable($_POST['plans_id']);
                $s->setPaypal_plan_id($planId);
                $s->save();
            }
        }
        // Create new agreement
        // the setup fee will be the first payment and start date is the next payment
        $startDate = date("Y-m-d\TH:i:s.000\Z", strtotime("+{$interval} {$frequency}"));
        $agreement = new Agreement();
        $agreement->setName($name)
                ->setDescription($name)
                ->setStartDate($startDate);

        $plan = new Plan();
        $plan->setId($planId);
        $agreement->setPlan($plan);

        // Add payer type
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {
            // Create agreement
            $agreement = $agreement->create($apiContext);

            // Extract approval URL to redirect user
            return $agreement;
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            error_log("PayPal Error createBillingPlan:  startDate: {$startDate} " . $ex->getData());
        } catch (Exception $ex) {
            error_log("PayPal Error createBillingPlan: startDate: {$startDate} " . $ex->getData());
        }
        return false;
    }

    private function executeBillingAgreement() {
        global $global;
        require_once $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        $token = $_GET['token'];
        $agreement = new \PayPal\Api\Agreement();

        try {
            // Execute agreement
            error_log("PayPal Try to execute ");
            $agreement->execute($token, $apiContext);
            return $agreement;
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            error_log("PayPal Error executeBillingAgreement: " . $ex->getData());
        } catch (Exception $ex) {
            error_log("PayPal Error executeBillingAgreement: " . $ex);
        }
        return false;
    }

    function execute() {
        if (!empty($_GET['paymentId'])) {
            error_log("PayPal Execute payment ");
            return $this->executePayment();
        } else if (!empty($_GET['token'])) {
            error_log("PayPal Billing Agreement ");
            return $this->executeBillingAgreement();
        }
        error_log("PayPal no payment to execute ");
        return false;
    }

    static function getAmountFromPayment($payment) {
        if (!is_object($payment)) {
            return false;
        }
        if (get_class($payment) === 'PayPal\Api\Agreement') {
            $amount = new stdClass();
            //error_log("getAmountFromPayment: ".json_encode($payment));
            //error_log("getAmountFromPayment: ". print_r($payment, true));
            //error_log("getAmountFromPayment: ".($payment->getId()));
            //error_log("getAmountFromPayment: ".($payment->getPlan()));
            //error_log("getAmountFromPayment: ".($payment->getPlan()->payment_definitions->amount->value));
            //error_log("getAmountFromPayment: ".($payment->getPlan()->merchant_preferences->setup_fee->value));
            //$amount->total = $payment->agreement_details->last_payment_amount->value;
            if(!empty(@$payment->getPlan()->payment_definitions->amount->value)){
                $amount->total = $payment->getPlan()->payment_definitions->amount->value;
            }else if(!empty(@$payment->getPlan()->merchant_preferences->setup_fee->value)){
                $amount->total = $payment->getPlan()->merchant_preferences->setup_fee->value;
            }else{
                $amount->total = 0;
            }
            return $amount;
        } else {
            return $payment->getTransactions()[0]->amount;
        }
    }

    function sendToPayPal($invoiceNumber, $redirect_url, $cancel_url, $total, $currency) {
        $payment = $this->setUpPayment($invoiceNumber, $redirect_url, $cancel_url, $total, $currency);
        if (!empty($payment)) {
            header("Location: {$payment->getApprovalLink()}");
            exit;
        }
    }

}
