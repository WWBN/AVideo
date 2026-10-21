<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/PayPalYPT/Objects/PayPalYPT_log.php';

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
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\Payer;
use PayPal\Api\Plan;
use PayPal\Api\ShippingAddress;
use PaypalPayoutsSDK\Payouts\PayoutsPostRequest;
use PaypalPayoutsSDK\Payouts\PayoutsGetRequest;
use PayPal\Api\WebhookEvent;

require_once $global['systemRootPath'] . 'plugin/PayPalYPT/PayPalClient.php';
require_once $global['systemRootPath'] . 'plugin/PayPalYPT/Objects/PayPalYPT_checkout.php';
require_once $global['systemRootPath'] . 'plugin/PayPalYPT/Objects/PayPalPaymentProcessor.php';

class PayPalYPT extends PluginAbstract
{
    // Public sandbox application shipped as the default: many installations share it, so its
    // webhooks belong to other sites. Never configure or delete webhooks on it.
    const DEMO_CLIENT_ID = 'ASUkHFpWX0T8sr8EiGdLZ05m-RAb8l-hdRxoq-OXWmua2i7EUfqFkMZvSoGgH2LhK7zAqt29IiS2oRTn';
    // Events with a handler in webhook.php. COMPLETED fulfills; the others only update the checkout
    // status or flag a refund/reversal for manual review.
    const WEBHOOK_EVENTS = ['PAYMENT.SALE.COMPLETED', 'PAYMENT.SALE.DENIED', 'PAYMENT.SALE.REFUNDED', 'PAYMENT.SALE.REVERSED'];

    public function getPluginDependencies() {
        return array(
            PluginDependency::create('YPTWallet', 'Credits confirmed PayPal payments to the user wallet balance'),
        );
    }

    public function getTags()
    {
        return [
            PluginTags::$MONETIZATION,
            PluginTags::$FREE,
        ];
    }

    public function getDescription()
    {
        $txt = "Paypal module for several purposes";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/PayPalYPT-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        return $txt . $help;
    }

    public function getName()
    {
        return "PayPalYPT";
    }

    public function getUUID()
    {
        return "5f613a09-c0b6-4264-85cb-47ae076d949f";
    }

    public function getPluginVersion()
    {
        return "3.0";
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();
        $obj->ClientID = self::DEMO_CLIENT_ID;
        $obj->ClientSecret = "ECxtMBsLr0cFwSCgI0uaDiVzEUbVlV3r_o_qaU-SOsQqCEOKPq4uGlr1C0mhdDmEyO30mw7-PF0bOnfo";
        $obj->subscriptionButtonLabel = "Subscribe With PayPal";
        $obj->paymentButtonLabel = "Pay With PayPal";
        $obj->disableSandbox = false;
        $obj->webhookId = "";
        self::addDataObjectHelper('ClientID', 'REST app Client ID', 'From your own app in developer.paypal.com (Sandbox or Live, matching disableSandbox). The default value is a shared public sandbox app: replace it.');
        self::addDataObjectHelper('ClientSecret', 'REST app secret', 'Secret of the same PayPal app. Never post it in issues.');
        self::addDataObjectHelper('disableSandbox', 'Live payments', 'Unchecked: PayPal Sandbox (test money). Checked: Live (real payments). Use credentials from the same environment.');
        self::addDataObjectHelper('webhookId', 'Webhook ID', 'Filled by Check configuration. Used to verify the signature of webhook deliveries; copy it from your PayPal app if you created the webhook manually.');
        return $obj;
    }

    public function updateScript()
    {
        // updateV3.0.sql creates the checkout table (CREATE TABLE IF NOT EXISTS).
        return parent::updateScript();
    }

    public static function getEnvironment($settings = null)
    {
        $settings = $settings ?: AVideoPlugin::getObjectData('PayPalYPT');
        return empty($settings->disableSandbox) ? 'sandbox' : 'live';
    }

    public static function getApiBaseURL($settings = null)
    {
        return self::getEnvironment($settings) === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    /** Binds saved state to the app and environment without storing the secret. */
    public static function getClientHash($settings = null)
    {
        $settings = $settings ?: AVideoPlugin::getObjectData('PayPalYPT');
        return hash('sha256', self::getEnvironment($settings) . '|' . trim((string) $settings->ClientID));
    }

    public static function isSharedDemoApp($settings = null)
    {
        $settings = $settings ?: AVideoPlugin::getObjectData('PayPalYPT');
        return trim((string) $settings->ClientID) === self::DEMO_CLIENT_ID;
    }

    public static function getSdkConfig($settings = null)
    {
        global $global;
        $settings = $settings ?: AVideoPlugin::getObjectData('PayPalYPT');
        // Even INFO writes approval tokens in request URLs, and ERROR writes response bodies.
        // AVideo logs only safe identifiers and describeApiError() instead.
        return [
            'mode' => self::getEnvironment($settings),
            'log.LogEnabled' => false,
            'log.FileName' => $global['systemRootPath'] . 'videos/PayPal.log',
            'log.LogLevel' => 'INFO',
            'http.ConnectionTimeOut' => 30,
        ];
    }

    public static function getApiContext($requestId = null, $settings = null)
    {
        $settings = $settings ?: AVideoPlugin::getObjectData('PayPalYPT');
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(trim((string) $settings->ClientID), trim((string) $settings->ClientSecret)),
            $requestId
        );
        $apiContext->setConfig(self::getSdkConfig($settings));
        return $apiContext;
    }

    /** REST call through the SDK transport (OAuth, endpoint, retries). $payload is sent verbatim. */
    public static function restCall($method, $path, $payload = '', $apiContext = null)
    {
        $apiContext = $apiContext ?: self::getApiContext();
        $restCall = new \PayPal\Transport\PayPalRestCall($apiContext);
        $json = $restCall->execute(['PayPal\Handler\RestHandler'], $path, $method, $payload, ['Content-Type' => 'application/json']);
        $data = json_decode((string) $json, true);
        return is_array($data) ? $data : [];
    }

    /** Safe log text for a PayPal API error: HTTP status and PayPal's error name/debug ID only. */
    public static function describeApiError($exception)
    {
        $text = get_class($exception);
        if ($exception instanceof \PayPal\Exception\PayPalConnectionException) {
            $data = json_decode((string) $exception->getData(), true);
            $text .= ' http=' . intval($exception->getCode());
            if (is_array($data)) {
                $text .= ' name=' . preg_replace('/[^A-Z0-9_]/i', '', (string) ($data['name'] ?? $data['error'] ?? ''))
                    . ' debug_id=' . preg_replace('/[^a-z0-9]/i', '', (string) ($data['debug_id'] ?? ''));
            }
        }
        return $text;
    }

    public function setUpPayment($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $description = "")
    {
        global $global;
        $total = parseToFloat($total);
        if ($total < 1) {
            $total = 1;
        }
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
            ->setTransactions([$transaction])
            ->setRedirectUrls($redirectUrls);

        // After Step 3
        try {
            $payment->create($apiContext);
            return $payment;
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            // This will print the detailed information on the exception.
            //REALLY HELPFUL FOR DEBUGGING
            _error_log("PayPal Error: " . $ex->getData());
        }
        return false;
    }

    public function getPlanDetails($plan_id)
    {
        global $global;

        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        try {
            $plan = Plan::get($plan_id, $apiContext);
        } catch (Exception $ex) {
            return $ex;
        }
        return $plan;
    }

    private function executePayment()
    {
        global $global;
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
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

    private function createBillingPlan($redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement', $plans_id = 0)
    {
        global $global;
        _error_log("createBillingPlan: start: " . json_encode([$redirect_url, $cancel_url, $total, $currency, $frequency, $interval, $name]));
        if ($total < 1) {
            $total = 1;
        }
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        $notify_url = "{$global['webSiteRootURL']}plugin/PayPalYPT/ipn.php";
        // Create a new billing plan
        $plan = new Plan();
        $plan->setName(substr(cleanString($name), 0, 126))
            ->setDescription(substr(cleanString($name), 0, 126))
            ->setType('INFINITE');

        $paymentDefinitionArray = [];

        if (!empty($plans_id)) {
            $subs = new SubscriptionPlansTable($plans_id);
            $trialDays = $subs->getHow_many_days_trial();
            if (!empty($trialDays)) {
                $trialPaymentDefinition = new PaymentDefinition();
                $trialPaymentDefinition->setName('Trial Payment')
                    ->setType('TRIAL')
                    ->setFrequency('Day')
                    ->setFrequencyInterval($trialDays)
                    ->setCycles("1")
                    ->setAmount(new Currency(['value' => 0, 'currency' => $currency]));
                $paymentDefinitionArray[] = $trialPaymentDefinition;
            }
        }

        // Set billing plan definitions
        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
            ->setType('REGULAR')
            ->setFrequency($frequency)
            ->setFrequencyInterval($interval)
            ->setCycles('0')
            ->setAmount(new Currency(['value' => $total, 'currency' => $currency]));
        $paymentDefinitionArray[] = $paymentDefinition;

        $plan->setPaymentDefinitions($paymentDefinitionArray);

        // Set merchant preferences
        $merchantPreferences = new MerchantPreferences();
        // if there is a trial do not charge a setup fee
        if (empty($trialDays)) {
            $merchantPreferences->setReturnUrl($redirect_url)
                ->setCancelUrl($cancel_url)
                //->setNotifyUrl($notify_url)
                ->setAutoBillAmount('yes')
                ->setInitialFailAmountAction('CONTINUE')
                ->setMaxFailAttempts('0')
                ->setSetupFee(new Currency(['value' => $total, 'currency' => $currency]));
        } else {
            $merchantPreferences->setReturnUrl($redirect_url)
                ->setCancelUrl($cancel_url)
                //->setNotifyUrl($notify_url)
                ->setAutoBillAmount('yes')
                ->setInitialFailAmountAction('CONTINUE')
                ->setMaxFailAttempts('0');
        }
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
                _error_log("createBillingPlan: " . json_encode([$redirect_url, $cancel_url, $total, $currency, $frequency, $interval, $name]));
                // Output plan id
                return $plan;
            } catch (PayPal\Exception\PayPalConnectionException $ex) {
                _error_log("PayPal Error createBillingPlan 1: " . $ex->getData());
            } catch (Exception $ex) {
                _error_log("PayPal Error createBillingPlan 2: " . $ex->getMessage());
            }
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            _error_log("PayPal Error createBillingPlan 3: " . $ex->getData());
        } catch (Exception $ex) {
            _error_log("PayPal Error createBillingPlan 4: " . $ex->getMessage());
        }
        return false;
    }

    private function getPlanId()
    {
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

    public function setUpSubscription($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement')
    {
        global $global;

        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        if ($total < 1) {
            $total = 1;
        }
        $notify_url = "{$global['webSiteRootURL']}plugin/PayPalYPT/ipn.php";

        $planId = $this->getPlanId();
        if (empty($planId)) {
            //createBillingPlan($redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement')
            $plan = $this->createBillingPlan($redirect_url, $cancel_url, $total, $currency, $frequency, $interval, $name, $_POST['plans_id']);

            if (empty($plan)) {
                _error_log("PayPal Error setUpSubscription Plan ID is empty ");
                return false;
            }
            $planId = $plan->getId();
            // save the paypal plan ID for reuse
            if (!empty($_POST['plans_id'])) {
                $s = new SubscriptionPlansTable($_POST['plans_id']);
                $s->setPaypal_plan_id($planId);
                $s->save();
            }
        }
        // Create new agreement
        // the setup fee will be the first payment and start date is the next payment

        $subs = new SubscriptionPlansTable($_POST['plans_id']);
        if (!empty($subs)) {
            $trialDays = $subs->getHow_many_days_trial();
        }
        if (!empty($trialDays)) {
            $startDate = date("Y-m-d\TH:i:s.000\Z", strtotime("+12 hour"));
        } else {
            $startDate = date("Y-m-d\TH:i:s.000\Z", strtotime("+{$interval} {$frequency}"));
        }
        $agreement = new Agreement();
        $agreement->setName(substr(cleanString($name), 0, 126))
            ->setDescription(substr(cleanString($name), 0, 126))
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
            _error_log("PayPal Error createBillingPlan 5: startDate: {$startDate} " . $ex->getData());
        } catch (Exception $ex) {
            _error_log("PayPal Error createBillingPlan 6: startDate: {$startDate} " . $ex->getMessage());
        }
        return false;
    }

    public function setUpSubscriptionV2($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = '', $json = '', $trialDays = 0)
    {
        global $global;

        if ($total < 1) {
            $total = 1;
        }
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        //createBillingPlanV2($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = '', $trialDays = 0)
        $plan = $this->createBillingPlanV2($total, $currency, $frequency, $interval, $name, $json, $trialDays);

        if (empty($plan)) {
            _error_log("setUpSubscriptionV2: PayPal Error setUpSubscription Plan ID is empty ");
            return false;
        }
        $planId = $plan->getId();

        // Create new agreement
        // the setup fee will be the first payment and start date is the next payment
        if (!empty($trialDays)) {
            $startDate = date("Y-m-d\TH:i:s.000\Z", strtotime("+12 hour"));
        } else {
            $startDate = date("Y-m-d\TH:i:s.000\Z", strtotime("+{$interval} {$frequency}"));
        }
        $agreement = new Agreement();
        $agreement->setName(substr(cleanString($name), 0, 126))
            ->setDescription(substr(cleanString($json), 0, 126))
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
            _error_log("setUpSubscriptionV2: PayPal Error createBillingPlan 5: startDate: {$startDate} " . $ex->getData());
        } catch (Exception $ex) {
            _error_log("setUpSubscriptionV2: PayPal Error createBillingPlan 6: startDate: {$startDate} " . $ex->getMessage());
        }
        return false;
    }

    private function createBillingPlanV2($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = '', $json = '', $trialDays = 0)
    {
        global $global;
        if ($total < 1) {
            $total = 1;
        }
        $currency = strtoupper($currency);
        _error_log("createBillingPlanV2: createBillingPlan: start: " . json_encode([$total, $currency, $frequency, $interval, $name, $trialDays]));

        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        $notify_url = "{$global['webSiteRootURL']}plugin/PayPalYPT/ipnV2.php";
        $notify_url = addQueryStringParameter($notify_url, 'json', $json);

        $cancel_url = addQueryStringParameter($notify_url, 'success', 0);
        $success_url = addQueryStringParameter($notify_url, 'success', 1);

        // Create a new billing plan
        $plan = new Plan();
        $plan->setName(substr(cleanString($name), 0, 126))
            ->setDescription(substr(json_encode(User::getId()), 0, 126))
            ->setType('INFINITE');

        $paymentDefinitionArray = [];

        if (!empty($trialDays)) {
            $trialPaymentDefinition = new PaymentDefinition();
            $trialPaymentDefinition->setName('Trial Payment')
                ->setType('TRIAL')
                ->setFrequency('Day')
                ->setFrequencyInterval($trialDays)
                ->setCycles("1")
                ->setAmount(new Currency(['value' => 0, 'currency' => $currency]));
            $paymentDefinitionArray[] = $trialPaymentDefinition;
        }

        // Set billing plan definitions
        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
            ->setType('REGULAR')
            ->setFrequency($frequency)
            ->setFrequencyInterval($interval)
            ->setCycles('0')
            ->setAmount(new Currency(['value' => $total, 'currency' => $currency]));
        $paymentDefinitionArray[] = $paymentDefinition;

        $plan->setPaymentDefinitions($paymentDefinitionArray);

        // Set merchant preferences
        $merchantPreferences = new MerchantPreferences();
        // if there is a trial do not charge a setup fee
        if (empty($trialDays)) {
            $merchantPreferences->setReturnUrl($success_url)
                ->setCancelUrl($cancel_url)
                ->setNotifyUrl($notify_url)
                ->setAutoBillAmount('YES')
                ->setInitialFailAmountAction('CONTINUE')
                ->setMaxFailAttempts('0')
                ->setSetupFee(new Currency(['value' => $total, 'currency' => $currency]));
        } else {
            $merchantPreferences->setReturnUrl($success_url)
                ->setCancelUrl($cancel_url)
                ->setNotifyUrl($notify_url)
                ->setAutoBillAmount('YES')
                ->setInitialFailAmountAction('CONTINUE')
                ->setMaxFailAttempts('0');
        }
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
                _error_log("createBillingPlanV2: createBillingPlan: " . json_encode([$total, $currency, $frequency, $interval, $name, $trialDays, $plan]));
                // Output plan id
                return $plan;
            } catch (PayPal\Exception\PayPalConnectionException $ex) {
                _error_log("createBillingPlanV2: PayPal Error createBillingPlan 1: " . $ex->getData());
            } catch (Exception $ex) {
                _error_log("createBillingPlanV2: PayPal Error createBillingPlan 2: " . $ex->getMessage());
            }
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            _error_log("createBillingPlanV2: PayPal Error createBillingPlan 3: " . $ex->getData());
        } catch (Exception $ex) {
            _error_log("createBillingPlanV2: PayPal Error createBillingPlan 4: " . $ex->getMessage());
        }
        return false;
    }

    private function executeBillingAgreement()
    {
        // Webhooks are no longer created here: configuration is an explicit administrator action.
        global $global;
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        $token = $_GET['token'];
        $agreement = new \PayPal\Api\Agreement();

        try {
            // Execute agreement
            _error_log("PayPal Try to execute ");
            $agreement->execute($token, $apiContext);
            return $agreement;
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            _error_log("PayPal Error executeBillingAgreement: " . $ex->getData());
        } catch (Exception $ex) {
            _error_log("PayPal Error executeBillingAgreement: " . $ex->getMessage());
        }
        return false;
    }

    public static function getBillingAgreement($agreement_id)
    {
        global $global;
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        return Agreement::get($agreement_id, $apiContext);
    }

    public static function cancelAgreement($agreement_id)
    {
        global $global;
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';

        //Create an Agreement State Descriptor, explaining the reason to suspend.
        $agreementStateDescriptor = new AgreementStateDescriptor();
        $agreementStateDescriptor->setNote("Canceled by user");

        $createdAgreement = self::getBillingAgreement($agreement_id);

        try {
            $createdAgreement->suspend($agreementStateDescriptor, $apiContext);
            // Lets get the updated Agreement Object
            return Agreement::get($createdAgreement->getId(), $apiContext);
        } catch (Exception $ex) {
            return false;
        }
    }

    public function execute()
    {
        if (!empty($_GET['paymentId'])) {
            _error_log("PayPal Execute payment ");
            return $this->executePayment();
        } elseif (!empty($_GET['token'])) {
            _error_log("PayPal Billing Agreement ");
            return $this->executeBillingAgreement();
        }
        _error_log("PayPal no payment to execute ");
        return false;
    }

    public static function getAmountFromPayment($payment)
    {
        if (!is_object($payment)) {
            return false;
        }
        if (get_class($payment) === 'PayPal\Api\Agreement') {
            $amount = new stdClass();
            //_error_log("getAmountFromPayment: ".json_encode($payment));
            //_error_log("getAmountFromPayment: ". print_r($payment, true));
            //_error_log("getAmountFromPayment: ".($payment->getId()));
            //_error_log("getAmountFromPayment: ".($payment->getPlan()));
            //_error_log("getAmountFromPayment: ".($payment->getPlan()->payment_definitions->amount->value));
            //_error_log("getAmountFromPayment: ".($payment->getPlan()->merchant_preferences->setup_fee->value));
            //$amount->total = $payment->agreement_details->last_payment_amount->value;
            if (!empty(@$payment->getPlan()->payment_definitions->amount->value)) {
                $amount->total = $payment->getPlan()->payment_definitions->amount->value;
            } elseif (!empty(@$payment->getPlan()->merchant_preferences->setup_fee->value)) {
                $amount->total = $payment->getPlan()->merchant_preferences->setup_fee->value;
            } else {
                $amount->total = 0;
            }
            $amount->total = floatval($amount->total);
            return $amount;
        } else {
            return $payment->getTransactions()[0]->amount;
        }
    }

    public function sendToPayPal($invoiceNumber, $redirect_url, $cancel_url, $total, $currency)
    {
        if ($total < 1) {
            $total = 1;
        }
        $payment = $this->setUpPayment($invoiceNumber, $redirect_url, $cancel_url, $total, $currency);
        if (!empty($payment)) {
            header("Location: {$payment->getApprovalLink()}");
            exit;
        }
    }

    public static function updateBillingPlan($plan_id, $total = '1.00', $currency = "USD", $interval = 1, $name = 'Base Agreement')
    {
        global $global;
        if (empty($plan_id)) {
            return false;
        }
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';

        $createdPlan = Plan::get($plan_id, $apiContext);

        try {
            $patch1 = new Patch();
            $patch1->setOp('replace')
                ->setPath('/')
                ->setValue(_json_decode('{"name": "' . $name . '"}'));

            $paymentDefinitions = $createdPlan->getPaymentDefinitions();
            $paymentDefinition = $paymentDefinitions[0];
            $paymentDefinitionId = $paymentDefinition->getId();

            $patch2 = new Patch();
            $patch2->setOp('replace')
                ->setPath('/payment-definitions/' . $paymentDefinitionId)
                ->setValue(_json_decode('{
                                                "amount": {
                                                    "currency": "' . $currency . '",
                                                    "value": "' . $total . '"
                                                },
                                                "frequency_interval": "' . $interval . '"
                                            }'));
            $patchRequest = new PatchRequest();
            $patchRequest->addPatch($patch1);
            $patchRequest->addPatch($patch2);

            $createdPlan->update($patchRequest, $apiContext);

            return Plan::get($createdPlan->getId(), $apiContext);
        } catch (Exception $ex) {
            _error_log("PayPal Error updateBillingPlan: " . $ex->getMessage());
        }
        return false;
    }

    /** IPN fields exactly as PayPal sent them, before the generic request filters (e.g. *_id to integer). */
    public static function getRawIPN()
    {
        $fields = [];
        foreach (explode('&', (string) file_get_contents('php://input')) as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                $fields[urldecode($keyval[0])] = urldecode($keyval[1]);
            }
        }
        return $fields;
    }

    public static function getIPNVerificationURL($settings = null)
    {
        return self::getEnvironment($settings) === 'live'
            ? 'https://ipnpb.paypal.com/cgi-bin/webscr' : 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
    }

    public static function IPNcheck()
    {
        $obj = AVideoPlugin::getDataObject('PayPalYPT');

        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = [];
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }
        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        foreach ($myPost as $key => $value) {
            $value = urlencode($value);
            $req .= "&$key=$value";
        }

        // Step 2: POST IPN data back to PayPal to validate
        $ipnURL = self::getIPNVerificationURL($obj);
        // The message holds payer names and e-mails: log only safe identifiers.
        _error_log("IPNcheck: URL {$ipnURL} txn_type=" . preg_replace('/[^a-z_]/i', '', (string) ($myPost['txn_type'] ?? ''))
            . ' ipn_track_id=' . preg_replace('/[^a-z0-9]/i', '', (string) ($myPost['ipn_track_id'] ?? '')));

        $ch = curl_init($ipnURL);
        //curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-IPN-VerificationScript');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: PHP-IPN-Verification-Script',
            'Connection: Close',
        ]);
        // In wamp-like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "https://curl.haxx.se/docs/caextract.html" and set
        // the directory path of the certificate as shown below:
        // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        if (!($res = curl_exec($ch))) {
            _error_log("IPNcheck: Got " . curl_error($ch) . " when processing IPN data", AVideoLog::$ERROR);
            curl_close($ch);
            // Not verified yet: a non-2xx answer makes PayPal send the same notification again.
            http_response_code(503);
            exit;
        }
        $httpStatus = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        curl_close($ch);
        // inspect IPN validation result and act accordingly
        if (strcmp($res, "VERIFIED") == 0) {
            _error_log("IPNcheck SUCCESS: The response from IPN was: " . $res . "");
            return true;
        } elseif (strcmp($res, "INVALID") == 0) {
            // IPN invalid, log for manual investigation
            _error_log("IPNcheck ERROR: The response from IPN was: " . $res . "", AVideoLog::$SECURITY);
            return false;
        }
        _error_log("IPNcheck ERROR: Unexpected verification response http={$httpStatus} length=" . strlen((string) $res), AVideoLog::$ERROR);
        http_response_code(503);
        exit;
    }

    public static function setUserReceiverEmail($users_id, $email)
    {
        $user = new User($users_id);
        $paramName = 'PayPalReceiverEmail';
        return $user->addExternalOptions($paramName, $email);
    }

    public static function getUserReceiverEmail($users_id)
    {
        $user = new User($users_id);
        $paramName = 'PayPalReceiverEmail';
        return $user->getExternalOption($paramName);
    }

    public function getMyAccount($users_id)
    {
        global $global;

        $obj = AVideoPlugin::getDataObjectIfEnabled('YPTWallet');
        if (empty($obj) || empty($obj->enableAutoWithdrawFundsPagePaypal)) {
            return '';
        }

        include $global['systemRootPath'] . 'plugin/PayPalYPT/payOutReceiverEmailForm.php';
    }

    public static function WalletPayout($users_id_to_be_paid, $value)
    {
        global $config, $global;
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->response = false;

        if (empty($value)) {
            $obj->msg = 'value is empty';
            return $obj;
        }

        $wallet = AVideoPlugin::getDataObjectIfEnabled('YPTWallet');
        if (empty($wallet)) {
            $obj->msg = 'YPTWallet plugin is disabled';
            return $obj;
        }

        // check if the user has a paypal email
        $receiver_email = self::getUserReceiverEmail($users_id_to_be_paid);
        if (empty($receiver_email)) {
            $obj->msg = "The user {$users_id_to_be_paid} does not have a paypal receiver email";
            return $obj;
        }
        mysqlBeginTransaction();
        // transfer money from wallet
        $description = "Paypal payout to {$receiver_email} [users_id=$users_id_to_be_paid]";
        $transfer = YPTWallet::transferBalanceToSiteOwner($users_id_to_be_paid, $value, $description, true);
        if ($transfer) {
            $email_subject = $note = "You received " . YPTWallet::formatCurrency($value) . " from " . $config->getWebSiteTitle() . " ";
            // payout using paypal
            $obj->response = self::Payout($receiver_email, $value, $wallet->currency, $note, $email_subject);
            if (empty($obj->response) || !empty($obj->response->error)) {
                $description = "Paypal refund";
                $obj->msg = 'PayPal Payout error: ' . $obj->response->msg;
                $transfer = YPTWallet::transferBalanceFromSiteOwner($users_id_to_be_paid, $value, $description, true);
                mysqlRollback();
                return $obj;
            } else {
                $payout_batch_id = $obj->response->response->result->batch_header->payout_batch_id;
                $paymentLink = "<br><button class='btn btn-xs btn-default' onclick='avideoModalIframeSmall(\"{$global['webSiteRootURL']}plugin/PayPalYPT/payout.php?payout_batch={$payout_batch_id}\");'>PayPal Info</button>";
                $description .= $paymentLink;
                YPTWallet::setLogInfo($transfer, $obj->response);
                YPTWallet::setLogDescription($transfer, $description);
            }

            $obj->error = false;
        }

        mysqlCommit();
        return $obj;
    }

    public static function Payout($receiver_email, $value, $currency = 'USD', $note = '', $email_subject = '')
    {
        $obj = new stdClass();
        $obj->msg = '';
        $obj->error = true;
        $obj->response = false;

        if (empty($value)) {
            $obj->msg = 'PayPal::Payout value is empty';
            _error_log($obj->msg);
            return $obj;
        }

        if (empty($receiver_email)) {
            $obj->msg = "PayPal::Payout The user does not have a paypal receiver email";
            _error_log($obj->msg);
            return $obj;
        }

        $wallet = AVideoPlugin::getDataObjectIfEnabled('YPTWallet');
        if (empty($wallet) || empty($wallet->enableAutoWithdrawFundsPagePaypal)) {
            $obj->msg = 'PayPal::Payout Wallet enableAutoWithdrawFundsPagePaypal is disabled';
            _error_log($obj->msg);
            return $obj;
        }
        try {
            $request = new PaypalPayoutsSDK\Payouts\PayoutsPostRequest();
            $request->body = new stdClass();
            $request->body->sender_batch_header = new stdClass();
            $request->body->sender_batch_header->email_subject = $email_subject;
            $item = new stdClass();
            $item->recipient_type = 'EMAIL';
            $item->receiver = $receiver_email;
            $item->note = $note;
            $item->amount = new stdClass();
            $item->amount->currency = $currency;
            $item->amount->value = $value;
            $request->body->items = [$item];

            $request->body = object_to_array($request->body);

            $client = PayPalClient::client();
            $obj->response = $client->execute($request);
            // The response holds the receiver's e-mail: log only the batch identifiers.
            $batch = $obj->response->result->batch_header ?? null;
            $obj->msg = 'PayPal::Payout status=' . intval($obj->response->statusCode ?? 0)
                . ' batch=' . preg_replace('/[^A-Z0-9]/i', '', (string) ($batch->payout_batch_id ?? ''))
                . ' batch_status=' . preg_replace('/[^A-Z_]/i', '', (string) ($batch->batch_status ?? ''));
            _error_log($obj->msg);
            if (is_object($obj->response) && $obj->response->statusCode == 201) {
                $obj->error = false;
            }
            return $obj;
        } catch (\PayPalHttp\HttpException $e) {
            $msg = '';
            //Parse failure response
            $msg .= $e->getMessage() . PHP_EOL;
            $error = json_decode($e->getMessage());
            $msg .= $error->message . PHP_EOL;
            $msg .= $error->name . PHP_EOL;
            $msg .= $error->debug_id . PHP_EOL;
            $obj->msg = 'PayPal::Payout ' . $msg;
            _error_log($obj->msg);
        }
        return $obj;
    }

    public static function getPayoutInfo($payout_batch_id)
    {
        try {
            $request = new PaypalPayoutsSDK\Payouts\PayoutsGetRequest($payout_batch_id);
            $client = PayPalClient::client();
            return $client->execute($request);
        } catch (\PayPalHttp\HttpException $e) {
            $msg = '';
            //Parse failure response
            $msg .= $e->getMessage() . PHP_EOL;
            $error = json_decode($e->getMessage());
            $msg .= $error->message . PHP_EOL;
            $msg .= $error->name . PHP_EOL;
            $msg .= $error->debug_id . PHP_EOL;
            $obj = new stdClass();
            $obj->msg = 'PayPal::PayoutInfo ' . $msg;
            _error_log($obj->msg);
        }
        return $obj;
    }

    public function getWalletConfigurationHTML($users_id, $wallet, $walletDataObject)
    {
        global $global;
        $obj = AVideoPlugin::getDataObjectIfEnabled('YPTWallet');
        if (empty($obj->enableAutoWithdrawFundsPagePaypal)) {
            if (User::isAdmin()) {
                YPTWallet::showAdminMessage();
                echo '<div class="alert alert-warning" role="alert">
                    <i class="fa fa-info-circle"></i>
                    <strong>Admin Notice:</strong> PayPal YPT configuration will only appear if <strong>enableAutoWithdrawFundsPagePaypal</strong> is checked in the plugin parameters.
                </div>';
            }
            return '';
        }
        include_once $global['systemRootPath'] . 'plugin/PayPalYPT/getWalletConfigurationHTML.php';
    }

    public function getPluginMenu()
    {
        global $global;
        return '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/PayPalYPT/View/editor.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>'
            . '<button onclick="avideoModalIframe(webSiteRootURL+\'plugin/PayPalYPT/configuration.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fas fa-check-circle"></i> Check configuration</button>';
    }

    public static function isTokenUsed($token)
    {
        $row = PayPalYPT_log::getFromToken($token);
        return !empty($row);
    }

    public static function isRecurringPaymentIdUsed($recurring_payment_id)
    {
        $row = PayPalYPT_log::getFromRecurringPaymentId($recurring_payment_id);
        return !empty($row);
    }

    public static function getAllLogsFromUser($users_id)
    {
        $rows = PayPalYPT_log::getAllFromUser($users_id);
        return $rows;
    }

    /**
     * Read-only lookup kept for older callers. It never creates, changes or deletes webhooks:
     * an administrator configures them explicitly in PayPalYPT > Check configuration.
     */
    public static function getOrCreateWebhook()
    {
        try {
            $webhook = self::getWebhook();
            return empty($webhook) ? false : new \PayPal\Api\Webhook($webhook);
        } catch (Exception $ex) {
            _error_log('PayPalYPT::getOrCreateWebhook lookup failed: ' . self::describeApiError($ex), AVideoLog::$WARNING);
            return false;
        }
    }

    /** This installation's webhook (exact URL) in the configured app, or false. Throws on API errors. */
    public static function getWebhook($apiContext = null)
    {
        $url = self::getWebhookURL();
        // The list API returns every webhook of the app at once (an app has at most 10); there is no paging.
        $data = self::restCall('GET', '/v1/notifications/webhooks', '', $apiContext);
        foreach ($data['webhooks'] ?? [] as $webhook) {
            if (($webhook['url'] ?? '') === $url) {
                return $webhook;
            }
        }
        return false;
    }

    /** @deprecated Use PayPalYPT > Check configuration, an explicit administrator action. */
    public static function createWebhook()
    {
        _error_log('PayPalYPT::createWebhook is disabled: configure the webhook in PayPalYPT > Check configuration', AVideoLog::$WARNING);
        return false;
    }

    /** @deprecated Removing every webhook of an app also removed other sites' webhooks. Kept as a no-op. */
    static function deleteAllWebhooks()
    {
        _error_log('PayPalYPT::deleteAllWebhooks refused: AVideo never deletes PayPal webhooks', AVideoLog::$WARNING);
        return false;
    }

    /** The saved webhook ID, else a read-only lookup of the exact URL. Never creates a webhook. */
    public static function getWebhookIdForValidation()
    {
        $settings = AVideoPlugin::getObjectData('PayPalYPT');
        $saved = trim((string) ($settings->webhookId ?? ''));
        if (PayPalYPT_checkout::isValidProviderId($saved)) {
            return $saved;
        }
        $webhook = self::getWebhook();
        return empty($webhook['id']) ? '' : (string) $webhook['id'];
    }

    private static function getSignatureHeaders()
    {
        $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
        $headers = array_change_key_case($headers, CASE_UPPER);
        foreach (['PAYPAL-AUTH-ALGO', 'PAYPAL-CERT-URL', 'PAYPAL-TRANSMISSION-ID', 'PAYPAL-TRANSMISSION-SIG', 'PAYPAL-TRANSMISSION-TIME'] as $name) {
            if (empty($headers[$name])) {
                $headers[$name] = $_SERVER['HTTP_' . str_replace('-', '_', $name)] ?? '';
            }
        }
        return $headers;
    }

    /**
     * Verifies a webhook delivery with PayPal's verify-webhook-signature API and the webhook ID of this
     * installation. Returns (object) ['webhook_event' => event] when PayPal confirms it, false when the
     * delivery is not authentic, and throws when it cannot be checked now (PayPal will retry).
     */
    static function validateWebhook()
    {
        $requestBody = file_get_contents('php://input');
        $headers = self::getSignatureHeaders();
        foreach (['PAYPAL-AUTH-ALGO', 'PAYPAL-CERT-URL', 'PAYPAL-TRANSMISSION-ID', 'PAYPAL-TRANSMISSION-SIG', 'PAYPAL-TRANSMISSION-TIME'] as $name) {
            if (empty($headers[$name]) || !is_string($headers[$name]) || strlen($headers[$name]) > 2048) {
                _error_log('PayPal webhook: rejected, signature header missing', AVideoLog::$SECURITY);
                return false;
            }
        }
        $event = json_decode((string) $requestBody);
        if (!is_object($event) || empty($event->id) || empty($event->event_type)) {
            _error_log('PayPal webhook: rejected, the body is not a PayPal event', AVideoLog::$SECURITY);
            return false;
        }
        // PayPal itself downloads the certificate during verification; AVideo never fetches this URL.
        // Reject an obviously foreign certificate URL without spending an API call on it.
        $certURL = $headers['PAYPAL-CERT-URL'];
        $certHost = strtolower((string) parse_url($certURL, PHP_URL_HOST));
        if (parse_url($certURL, PHP_URL_SCHEME) !== 'https' || !preg_match('/(^|\.)paypal\.com$/D', $certHost)) {
            _error_log('PayPal webhook: rejected, certificate URL is not a PayPal URL event=' . preg_replace('/[^A-Z0-9-]/i', '', (string) $event->id), AVideoLog::$SECURITY);
            return false;
        }
        $webhookId = self::getWebhookIdForValidation();
        if ($webhookId === '') {
            throw new RuntimeException('PayPal webhook ID is not configured; run PayPalYPT > Check configuration');
        }
        // webhook_event must be the original bytes: re-encoding the decoded event changes the signed content.
        $payload = '{"auth_algo":' . json_encode($headers['PAYPAL-AUTH-ALGO'])
            . ',"cert_url":' . json_encode($certURL)
            . ',"transmission_id":' . json_encode($headers['PAYPAL-TRANSMISSION-ID'])
            . ',"transmission_sig":' . json_encode($headers['PAYPAL-TRANSMISSION-SIG'])
            . ',"transmission_time":' . json_encode($headers['PAYPAL-TRANSMISSION-TIME'])
            . ',"webhook_id":' . json_encode($webhookId)
            . ',"webhook_event":' . trim($requestBody) . '}';
        $result = self::restCall('POST', '/v1/notifications/verify-webhook-signature', $payload);
        if (($result['verification_status'] ?? '') !== 'SUCCESS') {
            _error_log('PayPal webhook: signature verification failed event=' . preg_replace('/[^A-Z0-9-]/i', '', (string) $event->id)
                . ' type=' . preg_replace('/[^A-Z0-9._-]/i', '', (string) $event->event_type), AVideoLog::$SECURITY);
            return false;
        }
        return (object) ['webhook_event' => $event, 'webhook_id' => $webhookId];
    }

    static function getWebhookURL()
    {
        global $global;
        $url = "{$global['webSiteRootURL']}plugin/PayPalYPT/webhook.php";
        $url = str_replace('http://', 'https://', $url);
        return $url;
    }

    public static function resendWebhook($event_id)
    {
        global $global;
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        $webhookEvent = new WebhookEvent();
        $webhookEvent->setId($event_id);
        try {
            $output = $webhookEvent->resend($apiContext);
            _error_log('PayPal::resendWebhook event=' . preg_replace('/[^A-Z0-9-]/i', '', (string) $event_id));
            return json_decode($output->toJSON());
        } catch (Exception $ex) {
            _error_log('PayPal::resendWebhook failed: ' . self::describeApiError($ex), AVideoLog::$WARNING);
        }
        return false;
    }

    /** Evidence that this installation handled an authentic notification (no payload, no personal data). */
    public static function recordDelivery($channel, $eventType)
    {
        global $forceDeviceType;
        if (!in_array($channel, ['webhook', 'ipn'], true)) {
            return;
        }
        $settings = AVideoPlugin::getObjectData('PayPalYPT');
        $previousDevice = $forceDeviceType;
        try {
            // The callback and the administrator have different country/device metadata: use one bucket.
            $forceDeviceType = 'web';
            ObjectYPT::setCacheGlobal(self::deliveryCacheName($settings, $channel),
                ['received_at' => time(), 'type' => preg_replace('/[^A-Z0-9._-]/i', '', (string) $eventType)], true, true);
        } catch (Throwable $th) {
            // Diagnostic evidence must never affect the payment response.
            _error_log('PayPal: could not record delivery evidence ' . get_class($th), AVideoLog::$WARNING);
        } finally {
            $forceDeviceType = $previousDevice;
        }
    }

    public static function getRecordedDelivery($channel, $settings = null)
    {
        global $forceDeviceType;
        $settings = $settings ?: AVideoPlugin::getObjectData('PayPalYPT');
        $previousDevice = $forceDeviceType;
        try {
            $forceDeviceType = 'web';
            return (array) ObjectYPT::getCacheGlobal(self::deliveryCacheName($settings, $channel), 30 * 86400, true, true, true);
        } finally {
            $forceDeviceType = $previousDevice;
        }
    }

    private static function deliveryCacheName($settings, $channel)
    {
        $url = $channel === 'webhook' ? self::getWebhookURL() : self::getIPNURL();
        return 'paypal-configuration-delivery/' . $channel . '-' . hash('sha256', self::getClientHash($settings) . '|' . $url
            . '|' . trim((string) ($settings->webhookId ?? '')));
    }

    public static function getIPNURL()
    {
        global $global;
        return "{$global['webSiteRootURL']}plugin/PayPalYPT/ipn.php";
    }

    /* ---------------------------------------------------------------------------------------------
     * One-time checkout (v1 Payments API, intent=sale): wallet top-up, PPV rental, PPV Live event.
     * ------------------------------------------------------------------------------------------- */

    /** Creates the PayPal payment of a saved checkout. Returns ['id' => PAYID-..., 'approval_url' => ...]. */
    public static function createCheckoutPayment(array $checkout, $returnUrl, $cancelUrl)
    {
        $selection = $checkout['selection'];
        $total = PayPalYPT_checkout::formatAmount($checkout['amount']);
        $name = self::cleanReceiptText($selection['item_name'] ?? '', 127);
        $description = self::cleanReceiptText($selection['description'] ?? $name, 127);
        $payload = [
            'intent' => 'sale',
            'payer' => ['payment_method' => 'paypal'],
            'transactions' => [[
                'amount' => ['total' => $total, 'currency' => $checkout['currency']],
                'description' => $description,
                'invoice_number' => $checkout['invoice_number'],
                'custom' => $checkout['invoice_number'],
                'notify_url' => self::getIPNURL(),
                'item_list' => ['items' => [[
                    'name' => $name, 'quantity' => '1', 'price' => $total, 'currency' => $checkout['currency'],
                    'sku' => self::cleanReceiptText($selection['sku'] ?? $checkout['purpose'], 127),
                ]]],
            ]],
            'redirect_urls' => ['return_url' => $returnUrl, 'cancel_url' => $cancelUrl],
        ];
        // PayPal-Request-Id: retrying a lost create response returns the same payment, not a new one.
        $data = self::restCall('POST', '/v1/payments/payment', json_encode($payload),
            self::getApiContext('avideo-create-' . $checkout['invoice_number']));
        $approval = '';
        foreach ($data['links'] ?? [] as $link) {
            if (($link['rel'] ?? '') === 'approval_url') {
                $approval = (string) $link['href'];
            }
        }
        if (!PayPalYPT_checkout::isValidProviderId($data['id'] ?? '') || !self::isPayPalApprovalURL($approval)) {
            throw new RuntimeException('PayPal did not return a payment approval link');
        }
        return ['id' => (string) $data['id'], 'approval_url' => $approval];
    }

    public static function isPayPalApprovalURL($url)
    {
        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));
        return parse_url((string) $url, PHP_URL_SCHEME) === 'https' && preg_match('/(^|\.)paypal\.com$/D', $host) === 1;
    }

    public static function cleanReceiptText($text, $length)
    {
        $text = trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', strip_tags(html_entity_decode((string) $text, ENT_QUOTES, 'UTF-8'))));
        return mb_substr($text, 0, $length, 'UTF-8');
    }

    /** The facts AVideo needs from a v1 payment resource. */
    public static function normalizePayment(array $data)
    {
        $transaction = $data['transactions'][0] ?? [];
        $sale = [];
        foreach ($transaction['related_resources'] ?? [] as $resource) {
            if (!empty($resource['sale'])) {
                $sale = $resource['sale'];
            }
        }
        return [
            'id' => (string) ($data['id'] ?? ''),
            'intent' => (string) ($data['intent'] ?? ''),
            'state' => (string) ($data['state'] ?? ''),
            'invoice' => (string) ($transaction['invoice_number'] ?? ''),
            'total' => (string) ($transaction['amount']['total'] ?? ''),
            'currency' => (string) ($transaction['amount']['currency'] ?? ''),
            'payer_id' => (string) ($data['payer']['payer_info']['payer_id'] ?? ''),
            'sale' => empty($sale) ? [] : [
                'id' => (string) ($sale['id'] ?? ''),
                'state' => (string) ($sale['state'] ?? ''),
                'total' => (string) ($sale['amount']['total'] ?? ''),
                'currency' => (string) ($sale['amount']['currency'] ?? ''),
                'reason_code' => preg_replace('/[^A-Z0-9_]/i', '', (string) ($sale['reason_code'] ?? '')),
            ],
        ];
    }

    private static function assertPaymentMatchesCheckout(array $payment, array $checkout)
    {
        if ($payment['id'] !== $checkout['payment_id'] || $payment['intent'] !== 'sale'
            || $payment['invoice'] !== $checkout['invoice_number']
            || PayPalPaymentProcessor::cents($payment['total']) !== PayPalPaymentProcessor::cents($checkout['amount'])
            || strcasecmp($payment['currency'], $checkout['currency']) !== 0) {
            throw new PayPalPaymentMismatchException('PayPal payment does not match the saved checkout');
        }
    }

    private static function assertSameApp(array $checkout)
    {
        if (!hash_equals((string) $checkout['client_hash'], self::getClientHash())) {
            throw new RuntimeException('The PayPal app or environment changed after this checkout started');
        }
    }

    private static function errorName($exception)
    {
        if (!$exception instanceof \PayPal\Exception\PayPalConnectionException) {
            return '';
        }
        $data = json_decode((string) $exception->getData(), true);
        return is_array($data) ? (string) ($data['name'] ?? '') : '';
    }

    /**
     * Brings a checkout up to date with PayPal and completes it when PayPal reports a completed sale.
     * Safe to call from the return URL, webhook, IPN and status check at the same time.
     */
    public static function reconcileCheckout(array $checkout, $source)
    {
        if ($checkout['purpose'] === 'subscription') {
            return self::reconcileSubscriptionCheckout($checkout, $source);
        }
        return PayPalYPT_checkout::withLock(PayPalYPT_checkout::lockName($checkout), function () use ($checkout, $source) {
            $checkout = PayPalYPT_checkout::getById($checkout['id']);
            if (in_array($checkout['status'], PayPalYPT_checkout::FINAL_STATES, true)) {
                return $checkout;
            }
            $age = time() - strtotime($checkout['created']);
            if (empty($checkout['payment_id'])) {
                // The create request never returned a payment; nothing can have been approved.
                return $age > PayPalYPT_checkout::APPROVAL_TTL ? PayPalYPT_checkout::update($checkout['id'], ['status' => 'expired']) : $checkout;
            }
            self::assertSameApp($checkout);
            try {
                $payment = self::normalizePayment(self::restCall('GET', '/v1/payments/payment/' . rawurlencode($checkout['payment_id'])));
                self::assertPaymentMatchesCheckout($payment, $checkout);
                if ($payment['state'] === 'created') {
                    if ($payment['payer_id'] === '') {
                        return $age > PayPalYPT_checkout::APPROVAL_TTL ? PayPalYPT_checkout::update($checkout['id'], ['status' => 'expired']) : $checkout;
                    }
                    // Approved by the buyer: execute it here, so a closed tab does not lose the payment.
                    $checkout = PayPalYPT_checkout::update($checkout['id'], ['status' => 'approved']);
                    try {
                        // Same PayPal-Request-Id on every retry: a lost response cannot execute it twice.
                        self::restCall('POST', '/v1/payments/payment/' . rawurlencode($checkout['payment_id']) . '/execute',
                            json_encode(['payer_id' => $payment['payer_id']]), self::getApiContext('avideo-execute-' . $checkout['invoice_number']));
                    } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                        $name = self::errorName($ex);
                        _error_log("PayPal checkout execute failed invoice={$checkout['invoice_number']} " . self::describeApiError($ex), AVideoLog::$WARNING);
                        if ($name === 'PAYMENT_ALREADY_DONE') {
                            // Executed by an earlier request whose response was lost: read it below.
                        } elseif (in_array($name, ['INSTRUMENT_DECLINED', 'PAYMENT_EXPIRED', 'PAYMENT_STATE_INVALID', 'PAYMENT_NOT_APPROVED_FOR_EXECUTION',
                            'TRANSACTION_REFUSED', 'PAYEE_ACCOUNT_RESTRICTED', 'CANNOT_PAY_SELF'], true)) {
                            return PayPalYPT_checkout::update($checkout['id'], ['status' => 'failed', 'status_detail' => substr($name, 0, 64)]);
                        } else {
                            throw $ex;
                        }
                    }
                    // Read the executed payment back: the credited facts always come from a fresh GET.
                    $payment = self::normalizePayment(self::restCall('GET', '/v1/payments/payment/' . rawurlencode($checkout['payment_id'])));
                    self::assertPaymentMatchesCheckout($payment, $checkout);
                }
            } catch (PayPalPaymentMismatchException $ex) {
                _error_log("PayPal checkout: payment does not match the saved checkout; not credited invoice={$checkout['invoice_number']} payment={$checkout['payment_id']}", AVideoLog::$SECURITY);
                return PayPalYPT_checkout::update($checkout['id'], ['status' => 'failed', 'status_detail' => 'mismatch']);
            }
            if ($payment['state'] === 'failed') {
                return PayPalYPT_checkout::update($checkout['id'], ['status' => 'failed', 'status_detail' => 'payment_failed']);
            }
            $sale = $payment['sale'];
            if (empty($sale['id'])) {
                return PayPalYPT_checkout::update($checkout['id'], ['status' => 'pending', 'status_detail' => 'no_sale']);
            }
            switch ($sale['state']) {
                case 'completed':
                    $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
                    if (empty($wallet)) {
                        throw new RuntimeException('YPTWallet is disabled; the PayPal payment cannot be credited yet');
                    }
                    try {
                        PayPalPaymentProcessor::fulfillCheckoutSale($wallet, $checkout, $sale, $source);
                    } catch (PayPalPaymentMismatchException $ex) {
                        _error_log("PayPal checkout: sale does not match the checkout; not credited invoice={$checkout['invoice_number']} sale={$sale['id']}", AVideoLog::$SECURITY);
                        return PayPalYPT_checkout::update($checkout['id'], ['status' => 'failed', 'status_detail' => 'mismatch', 'sale_id' => $sale['id']]);
                    }
                    return PayPalYPT_checkout::getById($checkout['id']);
                case 'pending':
                    // Credited only when PayPal completes it (webhook, IPN or a later status check).
                    return PayPalYPT_checkout::update($checkout['id'], ['status' => 'pending', 'sale_id' => $sale['id'],
                        'status_detail' => substr($sale['reason_code'] ?: 'pending', 0, 64)]);
                case 'denied':
                case 'failed':
                    return PayPalYPT_checkout::update($checkout['id'], ['status' => 'denied', 'sale_id' => $sale['id'], 'status_detail' => $sale['state']]);
                default:
                    // refunded, partially_refunded or reversed before AVideo credited it: never credited automatically.
                    _error_log("PayPal checkout: sale is {$sale['state']} before it was credited; review it manually invoice={$checkout['invoice_number']} sale={$sale['id']}", AVideoLog::$WARNING);
                    return PayPalYPT_checkout::update($checkout['id'], ['status' => 'refunded', 'sale_id' => $sale['id'],
                        'status_detail' => substr(preg_replace('/[^a-z_]/i', '', $sale['state']) . '_before_credit', 0, 64)]);
            }
        });
    }

    /* ---------------------------------------------------------------------------------------------
     * Recurring checkout (v1 Billing Agreements, kept for existing plans and agreements).
     * ------------------------------------------------------------------------------------------- */

    public static function getApprovalToken($approvalURL)
    {
        parse_str((string) parse_url((string) $approvalURL, PHP_URL_QUERY), $query);
        return PayPalYPT_checkout::isValidProviderId($query['token'] ?? '') ? (string) $query['token'] : '';
    }

    /** Completed payments of an agreement since the checkout started, from PayPal's transaction search. */
    public static function getAgreementPayments($agreementId, $since)
    {
        $start = gmdate('Y-m-d', strtotime($since) - 86400);
        $end = gmdate('Y-m-d', time() + 86400);
        $data = self::restCall('GET', '/v1/payments/billing-agreements/' . rawurlencode($agreementId)
            . '/transactions?start_date=' . $start . '&end_date=' . $end);
        $payments = [];
        foreach ($data['agreement_transaction_list'] ?? [] as $transaction) {
            $id = (string) ($transaction['transaction_id'] ?? '');
            if ($id === $agreementId || !PayPalYPT_checkout::isValidProviderId($id)) {
                continue;
            }
            $payments[] = ['id' => $id, 'status' => (string) ($transaction['status'] ?? ''),
                'total' => (string) ($transaction['amount']['value'] ?? ''), 'currency' => (string) ($transaction['amount']['currency'] ?? '')];
        }
        return $payments;
    }

    public static function reconcileSubscriptionCheckout(array $checkout, $source)
    {
        return PayPalYPT_checkout::withLock(PayPalYPT_checkout::lockName($checkout), function () use ($checkout, $source) {
            $checkout = PayPalYPT_checkout::getById($checkout['id']);
            if (in_array($checkout['status'], PayPalYPT_checkout::FINAL_STATES, true) || empty($checkout['payment_id'])) {
                return $checkout;
            }
            self::assertSameApp($checkout);
            $plansId = intval($checkout['selection']['plans_id'] ?? 0);
            $usersId = intval($checkout['users_id']);
            if ($checkout['status'] === 'created') {
                try {
                    // The approval token alone executes the agreement, so this also works when the buyer closed the tab.
                    $agreement = self::restCall('POST', '/v1/payments/billing-agreements/' . rawurlencode($checkout['payment_id']) . '/agreement-execute',
                        '{}', self::getApiContext('avideo-agreement-' . $checkout['invoice_number']));
                } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                    if (intval($ex->getCode()) >= 400 && intval($ex->getCode()) < 500) {
                        // Not approved (yet), canceled or expired: the buyer was not charged.
                        if (time() - strtotime($checkout['created']) > PayPalYPT_checkout::APPROVAL_TTL) {
                            return PayPalYPT_checkout::update($checkout['id'], ['status' => 'expired']);
                        }
                        return $checkout;
                    }
                    throw $ex;
                }
                $agreementId = (string) ($agreement['id'] ?? '');
                if (PayPalYPT_checkout::isValidProviderId($agreementId) && empty($agreement['state'])) {
                    $agreement = self::restCall('GET', '/v1/payments/billing-agreements/' . rawurlencode($agreementId));
                }
                if (!PayPalYPT_checkout::isValidProviderId($agreementId) || strcasecmp((string) ($agreement['state'] ?? ''), 'Active') !== 0) {
                    _error_log("PayPal subscription checkout: agreement not active invoice={$checkout['invoice_number']} state=" . preg_replace('/[^a-z]/i', '', (string) ($agreement['state'] ?? '')), AVideoLog::$WARNING);
                    return PayPalYPT_checkout::update($checkout['id'], ['status' => 'failed', 'status_detail' => 'agreement_not_active']);
                }
                $trial = !empty($checkout['selection']['trial_days']);
                $checkout = PayPalYPT_checkout::update($checkout['id'], ['status' => $trial ? 'trial' : 'active', 'agreement_id' => $agreementId,
                    'fulfillment' => $trial ? 'trial' : null]);
                if (AVideoPlugin::isEnabledByName('Subscription')) {
                    // Links the agreement to the buyer; access is granted only by a completed payment (or the plan's trial).
                    PayPalPaymentProcessor::withoutBrowserOrigin(function () use ($usersId, $plansId, $agreementId, $trial) {
                        Subscription::getOrCreateSubscription($usersId, $plansId, $agreementId);
                        if ($trial) {
                            Subscription::onTrial($usersId, $plansId);
                        }
                    });
                }
                if ($trial) {
                    return $checkout;
                }
            }
            if ($checkout['status'] !== 'active' || empty($checkout['agreement_id'])) {
                return $checkout;
            }
            $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
            if (empty($wallet) || !AVideoPlugin::isEnabledByName('Subscription')) {
                throw new RuntimeException('YPTWallet and Subscription must be enabled to complete a PayPal subscription');
            }
            foreach (self::getAgreementPayments($checkout['agreement_id'], $checkout['created']) as $sale) {
                if (strcasecmp($sale['status'], 'Completed') !== 0) {
                    continue;
                }
                if (PayPalPaymentProcessor::cents($sale['total']) <= 0 || strcasecmp($sale['currency'], $checkout['currency']) !== 0) {
                    _error_log("PayPal subscription checkout: first payment currency or amount is invalid; not credited invoice={$checkout['invoice_number']} sale={$sale['id']}", AVideoLog::$SECURITY);
                    return PayPalYPT_checkout::update($checkout['id'], ['status' => 'failed', 'status_detail' => 'mismatch', 'sale_id' => $sale['id']]);
                }
                if (PayPalPaymentProcessor::cents($sale['total']) !== PayPalPaymentProcessor::cents($checkout['amount'])) {
                    // A reused PayPal plan keeps its own price; credit what PayPal charged, as the webhook and IPN do.
                    _error_log("PayPal subscription checkout: first payment {$sale['total']} differs from the plan price {$checkout['amount']} invoice={$checkout['invoice_number']}", AVideoLog::$WARNING);
                }
                $plan = new SubscriptionPlansTable($plansId);
                // Same ledger key as the webhook (resource.id) and the IPN (txn_id): one credit and one renewal.
                PayPalPaymentProcessor::processSubscriptionSale($wallet, $usersId, $plansId, $checkout['agreement_id'], [$sale['id']],
                    floatval($sale['total']), 'Paypal recurrent ' . $plan->getName(),
                    ['source' => preg_replace('/[^a-z_-]/i', '', (string) $source), 'sale_id' => $sale['id'], 'agreement_id' => $checkout['agreement_id']],
                    [], function () use ($sale) {
                        return self::isRecurringPaymentIdUsed($sale['id']);
                    });
                return PayPalYPT_checkout::getById($checkout['id']);
            }
            return $checkout;
        });
    }

    /**
     * Starts (or resumes) a one-time checkout for the logged-in buyer. The amount and the selection come
     * from the catalog and the wallet on the server; the browser only names what it wants to buy.
     * Returns ['approval_url' => ...] to send the buyer to PayPal, or ['invoice' => ..., 'message' => ...]
     * when an earlier payment must be followed instead of charging again. Throws PayPalCheckoutRefused
     * with a buyer-facing message for expected refusals.
     */
    public static function startOneTimeCheckout($usersId, array $request)
    {
        global $config;
        $usersId = intval($usersId);
        $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
        if ($usersId < 1 || empty($wallet)) {
            throw new PayPalCheckoutRefused(__('Please sign in to pay.'));
        }
        if (!PayPalYPT_checkout::isInstalled()) {
            _error_log('PayPal checkout: table PayPalYPT_checkout is missing; update the PayPalYPT plugin', AVideoLog::$ERROR);
            throw new PayPalCheckoutRefused(__('PayPal payments are being updated. Please try again later.'));
        }
        $currency = strtoupper((string) $wallet->getDataObject()->currency);
        $balance = round(floatval($wallet->getBalance($usersId)), 2);
        $plugin = (string) ($request['plugin'] ?? '');
        $siteTitle = self::cleanReceiptText($config->getWebSiteTitle(), 60);
        $ppv = $plugin === 'PayPerView' ? AVideoPlugin::loadPluginIfEnabled('PayPerView') : false;
        $live = $plugin === 'PayPerViewLive' ? AVideoPlugin::loadPluginIfEnabled('PayPerViewLive') : false;
        $videosId = intval($request['videos_id'] ?? 0);
        // Only one movie can be rented automatically; whole-plan purchases and older independently deployed
        // PPV/PPV Live versions keep the previous behavior: fund the wallet, then buy with the balance.
        $autoRental = !empty($ppv) && method_exists($ppv, 'getPaymentDetails') && method_exists($ppv, 'completeWalletCheckout') && $videosId > 0;
        $autoLive = !empty($live) && method_exists($live, 'getPaymentDetails') && method_exists($live, 'completeWalletCheckout');
        if ($autoRental) {
            $plansId = intval($request['plans_id'] ?? 0);
            $details = $ppv->getPaymentDetails($plansId, $videosId);
            if (empty($details) || floatval($details['value']) <= 0) {
                throw new PayPalCheckoutRefused(__('This rental is not available for PayPal checkout.'));
            }
            if (class_exists('PPV_Plans_Users_Videos') && PPV_Plans_Users_Videos::userCanWatchVideo($usersId, $videosId)) {
                throw new PayPalCheckoutRefused(__('You already have access to this video. No payment is needed.'));
            }
            $price = round(floatval($details['value']), 2);
            $purpose = 'ppv';
            $title = $details['metadata']['video_title'] ?? '';
            $selection = ['ppv_plans_id' => $plansId, 'videos_id' => $videosId,
                'rental_hours' => intval($details['metadata']['rental_hours']), 'ppv_value' => number_format($price, 2, '.', ''),
                'video_title' => $title, 'plan_name' => $details['metadata']['plan_name'] ?? '',
                'description' => $details['description'], 'item_name' => 'PPV rental: ' . $title, 'sku' => 'ppv-' . $videosId];
        } elseif ($autoLive) {
            $scheduleId = intval($request['ppvlive_schedule_id'] ?? 0);
            $details = $live->getPaymentDetails($scheduleId);
            if (empty($details)) {
                throw new PayPalCheckoutRefused(__('This event is not available for purchase.'));
            }
            $purchase = $live->getSubscriptionToPlan($usersId, $scheduleId);
            if (!empty($purchase['id']) && $purchase['status'] === 'a') {
                throw new PayPalCheckoutRefused(__('You already have access to this event. No payment is needed.'));
            }
            $price = round(floatval($details['value']), 2);
            $purpose = 'ppvlive';
            $selection = ['ppvlive_schedule_id' => $scheduleId, 'ppvlive_value' => number_format($price, 2, '.', ''),
                'live_starts' => $details['metadata']['live_starts'], 'live_ends' => $details['metadata']['live_ends'],
                'event_name' => $details['metadata']['event_name'], 'live_owner' => intval($details['owner']),
                'description' => $details['description'], 'item_name' => 'PPV Live: ' . $details['metadata']['event_name'],
                'sku' => 'ppvlive-' . $scheduleId];
        } else {
            $price = round(floatval(parseToFloat($request['value'] ?? 0)), 2);
            if ($price <= 0 || $price > 100000) {
                throw new PayPalCheckoutRefused(__('Invalid Value'));
            }
            $purpose = 'wallet';
            $selection = ['description' => $siteTitle . ' wallet credit', 'item_name' => $siteTitle . ' wallet credit', 'sku' => 'wallet'];
            $balance = 0;
            // Back to the page that asked for the funds (PPV plan, event, plans page), never to another site.
            $returnURL = (string) YPTWallet::getAddFundsSuccessRedirectURL();
            if ($plugin !== '' && $returnURL !== '' && isSameDomainAsMyAVideo($returnURL)) {
                $selection['return_url'] = $returnURL;
            }
        }
        // The wallet balance pays first; PayPal charges only the rest.
        $amount = round(max(0, $price - $balance), 2);
        if ($amount <= 0) {
            throw new PayPalCheckoutRefused(__('Your wallet balance already covers this purchase. Reload the page and pay with your balance; no PayPal payment is needed.'));
        }
        $selection['price'] = number_format($price, 2, '.', '');
        $selection['balance_applied'] = number_format($price - $amount, 2, '.', '');
        $settings = AVideoPlugin::getObjectData('PayPalYPT');
        $environment = self::getEnvironment($settings);
        $clientHash = self::getClientHash($settings);
        $hash = PayPalYPT_checkout::selectionHash($usersId, $purpose, $selection, $amount, $currency, $environment, $clientHash);
        try {
            return PayPalYPT_checkout::withLock('avideo_paypal_user_' . sha1($GLOBALS['mysqlDatabase'] . '|' . $usersId),
                function () use ($usersId, $purpose, $selection, $amount, $currency, $environment, $clientHash, $hash) {
                    $paid = PayPalYPT_checkout::getUnfinishedPaid($usersId, $hash);
                    if (!empty($paid)) {
                        // An approved or pending PayPal payment of this buyer is still being confirmed: never charge again.
                        return ['invoice' => $paid['invoice_number'], 'message' => __('Your previous PayPal payment is still being confirmed. No new payment was created.')];
                    }
                    $checkout = PayPalYPT_checkout::getReusable($usersId, $hash);
                    if (!empty($checkout) && $checkout['status'] === 'created' && empty($checkout['status_detail'])
                        && self::isPayPalApprovalURL($checkout['approval_url'])) {
                        // Double click, refresh or a second tab: reuse the same PayPal payment.
                        return ['approval_url' => $checkout['approval_url'], 'invoice' => $checkout['invoice_number']];
                    }
                    if (empty($checkout) || $checkout['status'] !== 'creating') {
                        $checkout = PayPalYPT_checkout::create($usersId, $purpose, $selection, $amount, $currency, $environment, $clientHash);
                    }
                    $base = self::getCheckoutReturnBase();
                    try {
                        $payment = self::createCheckoutPayment($checkout,
                            $base . 'redirect_url.php?invoice=' . $checkout['invoice_number'], $base . 'cancel_url.php?invoice=' . $checkout['invoice_number']);
                    } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                        _error_log("PayPal checkout create failed invoice={$checkout['invoice_number']} " . self::describeApiError($ex), AVideoLog::$ERROR);
                        if (intval($ex->getCode()) >= 400 && intval($ex->getCode()) < 500) {
                            // A definite rejection created nothing; a new click starts a new checkout.
                            PayPalYPT_checkout::update($checkout['id'], ['status' => 'failed', 'status_detail' => substr(self::errorName($ex) ?: 'create_rejected', 0, 64)]);
                        }
                        // Otherwise the row stays 'creating': the next click retries with the same PayPal-Request-Id.
                        throw new PayPalCheckoutRefused(__('PayPal is not available right now. You were not charged. Please try again.'));
                    }
                    PayPalYPT_checkout::update($checkout['id'], ['payment_id' => $payment['id'], 'approval_url' => $payment['approval_url'], 'status' => 'created']);
                    _error_log("PayPal checkout created invoice={$checkout['invoice_number']} payment={$payment['id']} users_id={$usersId} purpose={$purpose} amount={$amount} {$currency}");
                    return ['approval_url' => $payment['approval_url'], 'invoice' => $checkout['invoice_number']];
                }, 3);
        } catch (PayPalCheckoutBusyException $ex) {
            throw new PayPalCheckoutRefused(__('A payment request is already being processed. Please wait and try again.'));
        }
    }

    /** Starts a recurring checkout (billing agreement) for a Subscription plan, priced on the server. */
    public function startSubscriptionCheckout($usersId, $plansId)
    {
        $usersId = intval($usersId);
        $plansId = intval($plansId);
        $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
        if ($usersId < 1 || empty($wallet) || $plansId < 1 || !AVideoPlugin::isEnabledByName('Subscription')) {
            throw new PayPalCheckoutRefused(__('This plan is not available for PayPal checkout.'));
        }
        if (!PayPalYPT_checkout::isInstalled()) {
            _error_log('PayPal checkout: table PayPalYPT_checkout is missing; update the PayPalYPT plugin', AVideoLog::$ERROR);
            throw new PayPalCheckoutRefused(__('PayPal payments are being updated. Please try again later.'));
        }
        $plan = new SubscriptionPlansTable($plansId);
        if (empty($plan->getId()) || floatval($plan->getPrice()) <= 0) {
            throw new PayPalCheckoutRefused(__('This plan is not available for PayPal checkout.'));
        }
        $currency = strtoupper((string) $wallet->getDataObject()->currency);
        $bi = YPTWallet::getBillingInterval($plan->getHow_many_days());
        $name = $plan->getName() ?: 'Recurrent Payment';
        // setUpSubscription() charges at least 1.00 per cycle.
        $amount = max(1, round(floatval($plan->getPrice()), 2));
        $trialDays = intval($plan->getHow_many_days_trial());
        $selection = ['plans_id' => $plansId, 'plan_name' => self::cleanReceiptText($name, 120), 'trial_days' => $trialDays,
            'how_many_days' => intval($plan->getHow_many_days()), 'description' => self::cleanReceiptText($name, 120),
            'return_url' => Subscription::getBuyURL()];
        $settings = AVideoPlugin::getObjectData('PayPalYPT');
        $environment = self::getEnvironment($settings);
        $clientHash = self::getClientHash($settings);
        $hash = PayPalYPT_checkout::selectionHash($usersId, 'subscription', ['plans_id' => $plansId], $amount, $currency, $environment, $clientHash);
        try {
            return PayPalYPT_checkout::withLock('avideo_paypal_user_' . sha1($GLOBALS['mysqlDatabase'] . '|' . $usersId),
                function () use ($usersId, $plansId, $selection, $amount, $currency, $environment, $clientHash, $hash, $bi, $name) {
                    $checkout = PayPalYPT_checkout::getReusable($usersId, $hash);
                    if (!empty($checkout) && in_array($checkout['status'], ['active', 'approved', 'pending'], true)) {
                        return ['invoice' => $checkout['invoice_number'], 'message' => __('Your PayPal subscription is already being confirmed. No new payment was created.')];
                    }
                    if (!empty($checkout) && $checkout['status'] === 'created' && empty($checkout['status_detail'])
                        && self::isPayPalApprovalURL($checkout['approval_url'])) {
                        return ['approval_url' => $checkout['approval_url'], 'invoice' => $checkout['invoice_number']];
                    }
                    // Keyed by the plan only: the return URL in the selection may differ between pages.
                    $checkout = PayPalYPT_checkout::create($usersId, 'subscription', $selection, $amount, $currency, $environment, $clientHash, $hash);
                    $base = self::getCheckoutReturnBase();
                    $agreement = $this->setUpSubscription($checkout['invoice_number'], $base . 'redirect_url.php?invoice=' . $checkout['invoice_number'],
                        $base . 'cancel_url.php?invoice=' . $checkout['invoice_number'], $amount, $currency, $bi->paypalFrequency, $bi->paypalInterval, $name);
                    $approval = empty($agreement) ? '' : (string) $agreement->getApprovalLink();
                    $token = self::getApprovalToken($approval);
                    if ($token === '' || !self::isPayPalApprovalURL($approval)) {
                        PayPalYPT_checkout::update($checkout['id'], ['status' => 'failed', 'status_detail' => 'create_failed']);
                        throw new PayPalCheckoutRefused(__('PayPal is not available right now. You were not charged. Please try again.'));
                    }
                    PayPalYPT_checkout::update($checkout['id'], ['payment_id' => $token, 'approval_url' => $approval, 'status' => 'created']);
                    _error_log("PayPal subscription checkout created invoice={$checkout['invoice_number']} users_id={$usersId} plans_id={$plansId} amount={$amount} {$currency}");
                    return ['approval_url' => $approval, 'invoice' => $checkout['invoice_number']];
                }, 3);
        } catch (PayPalCheckoutBusyException $ex) {
            throw new PayPalCheckoutRefused(__('A payment request is already being processed. Please wait and try again.'));
        }
    }

    public static function getCheckoutReturnBase()
    {
        global $global;
        return $global['webSiteRootURL'] . 'plugin/YPTWallet/plugins/YPTWalletPayPal/';
    }

    public static function getCheckoutPageURL($invoice)
    {
        global $global;
        return $global['webSiteRootURL'] . 'plugin/PayPalYPT/checkout.php?invoice=' . rawurlencode($invoice);
    }

    /** The buyer's view of a checkout: no PayPal objects, payer data or internal errors. */
    public static function getPublicCheckoutStatus(array $checkout)
    {
        global $global;
        $selection = $checkout['selection'];
        $status = [
            'invoice' => $checkout['invoice_number'],
            'purpose' => $checkout['purpose'],
            'status' => $checkout['status'],
            'detail' => (string) $checkout['status_detail'],
            'fulfillment' => (string) $checkout['fulfillment'],
            'final' => in_array($checkout['status'], PayPalYPT_checkout::FINAL_STATES, true),
            'amount' => YPTWallet::formatCurrency($checkout['amount']),
            'description' => (string) ($selection['description'] ?? ''),
            'access_until' => '',
            'continue_url' => '',
            'continue_label' => '',
            'retry_url' => '',
        ];
        if ($checkout['status'] === 'created' && self::isPayPalApprovalURL($checkout['approval_url'])) {
            // Only the buyer reads this status; the link lets them finish the same approval.
            $status['approval_url'] = $checkout['approval_url'];
        }
        $status['price'] = isset($selection['price']) ? YPTWallet::formatCurrency($selection['price']) : '';
        $status['balance_applied'] = floatval($selection['balance_applied'] ?? 0) > 0 ? YPTWallet::formatCurrency($selection['balance_applied']) : '';
        $usersId = intval($checkout['users_id']);
        $videosId = intval($selection['videos_id'] ?? 0);
        $done = $checkout['status'] === 'completed';
        if ($checkout['purpose'] === 'ppv') {
            $status['retry_url'] = class_exists('PayPerView') ? PayPerView::getBuyURL($videosId) : $global['webSiteRootURL'];
            if ($done && $checkout['fulfillment'] === 'rented') {
                $status['continue_url'] = $global['webSiteRootURL'] . 'video/' . $videosId . '?autoplay=1';
                $status['continue_label'] = __('Watch Now');
                $rental = class_exists('PPV_Plans_Users_Videos') ? PPV_Plans_Users_Videos::getActivePlan($usersId, $videosId) : false;
                $status['access_until'] = !empty($rental['expiration']) ? (string) $rental['expiration']
                    : sprintf(__('Valid for %d hours'), intval($selection['rental_hours'] ?? 0));
            } else {
                $status['continue_url'] = $status['retry_url'];
                $status['continue_label'] = __('Complete your rental with your wallet balance');
            }
        } elseif ($checkout['purpose'] === 'ppvlive') {
            $eventURL = $global['webSiteRootURL'] . 'plugin/PayPerViewLive/View/buy.php?users_id=' . intval($selection['live_owner'] ?? 0)
                . '&ppvlive_schedule_id=' . intval($selection['ppvlive_schedule_id'] ?? 0);
            $status['retry_url'] = $eventURL;
            $status['continue_url'] = $eventURL;
            if ($done && $checkout['fulfillment'] === 'live_ready') {
                $started = strtotime(($selection['live_starts'] ?? '') . ' UTC') <= time();
                $status['access_until'] = (string) ($selection['live_ends'] ?? '') . ' UTC';
                $status['live_starts'] = (string) ($selection['live_starts'] ?? '') . ' UTC';
                $status['live_started'] = $started;
                if ($started && class_exists('Live')) {
                    $status['continue_url'] = Live::getLinkToLiveFromUsers_id(intval($selection['live_owner'] ?? 0));
                    $status['continue_label'] = __('Watch Now');
                } else {
                    $status['continue_label'] = __('View event details');
                }
            } else {
                $status['continue_label'] = __('Complete your event purchase with your wallet balance');
            }
        } elseif ($checkout['purpose'] === 'subscription') {
            $status['retry_url'] = !empty($selection['return_url']) ? $selection['return_url'] : $global['webSiteRootURL'];
            $status['continue_url'] = $status['retry_url'];
            $status['continue_label'] = __('Start watching');
            if (($done || $checkout['status'] === 'trial') && class_exists('SubscriptionTable')) {
                $row = SubscriptionTable::getSubscription($usersId, intval($selection['plans_id'] ?? 0), false, false);
                $status['access_until'] = (string) ($row['good_until'] ?? '');
            }
        } elseif (!empty($selection['return_url'])) {
            $status['retry_url'] = $selection['return_url'];
            $status['continue_url'] = $selection['return_url'];
            $status['continue_label'] = __('Continue with your purchase');
        } else {
            $status['retry_url'] = $global['webSiteRootURL'] . 'plugin/YPTWallet/view/addFunds.php';
            $status['continue_url'] = $status['retry_url'];
            $status['continue_label'] = __('View my wallet');
        }
        if (!empty($global['webSiteRootURL']) && class_exists('YPTWallet')) {
            $status['balance'] = YPTWallet::formatCurrency(YPTWallet::getUserBalance($usersId));
        }
        return $status;
    }
}
