<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
require_once $global['systemRootPath'] . 'vendor/stripe/stripe-php/init.php';

class StripeYPT extends PluginAbstract
{

    private $Publishablekey, $Restrictedkey, $SigningSecret;

    public function getTags()
    {
        return array(
            PluginTags::$MONETIZATION,
            PluginTags::$FREE,
        );
    }

    public function getDescription()
    {
        $txt = "Stripe module for several purposes";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/StripeYPT-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        return $txt . $help;
    }

    public function getName()
    {
        return "StripeYPT";
    }

    public function getUUID()
    {
        return "stripe09-c0b6-4264-85cb-47ae076d949f";
    }

    public function getPluginVersion()
    {
        return "2.5";
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();
        $obj->Publishablekey = "";
        $obj->Restrictedkey = "";
        //Before you can verify signatures, you need to retrieve your endpoint’s secret from your Dashboard’s Webhooks settings. Select an endpoint that you want to obtain the secret for, then select the Click to reveal button.
        $obj->SigningSecret = "";
        self::addDataObjectHelper('Publishablekey', 'Publishable key', 'Use a pk_test_ or pk_live_ key from the same Stripe account and mode as Restrictedkey.');
        self::addDataObjectHelper('Restrictedkey', 'Server API key', 'Prefer a restricted key. Open Check configuration for the required permissions and safe webhook setup.');
        self::addDataObjectHelper('SigningSecret', 'Webhook signing secret', 'For an existing endpoint, copy its whsec_ secret from Stripe. Automatic setup securely saves the secret of a newly created endpoint separately.');
        $obj->subscriptionButtonLabel = "Subscribe With Credit Card";
        $obj->paymentButtonLabel = "Pay With Credit Card";
        //$obj->disableSandbox = false;
        return $obj;
    }

    public function getDataObject()
    {

        if (!empty($this->Publishablekey)) {
            $obj = new stdClass();
            $obj->Publishablekey = $this->Publishablekey;
            $obj->Restrictedkey = $this->Restrictedkey;
            $obj->SigningSecret = $this->SigningSecret;
            return $obj;
        }

        return parent::getDataObject();
    }

    public function setTempDataObject($Publishablekey, $Restrictedkey, $SigningSecret)
    {
        $this->Publishablekey = $Publishablekey;
        $this->Restrictedkey = $Restrictedkey;
        $this->SigningSecret = $SigningSecret;
    }

    function start()
    {
        self::_start();
    }

    static function _start()
    {
        global $global;
        $obj = AVideoPlugin::getDataObject('StripeYPT');
        \Stripe\Stripe::setApiVersion('2022-11-15');
        \Stripe\Stripe::setApiKey($obj->Restrictedkey);
        // Payments must not require webhook-management permissions or mutate configuration.
    }

    static function getWebhook()
    {
        global $global;
        $notify_url = "{$global['webSiteRootURL']}plugin/StripeYPT/ipn.php";
        $parameters = ['limit' => 100];
        do {
            $webhooks = \Stripe\WebhookEndpoint::all($parameters);
            foreach ($webhooks->data as $value) {
                if ($value->url === $notify_url) {
                    return $value;
                }
                $parameters['starting_after'] = $value->id;
            }
        } while (!empty($webhooks->has_more) && !empty($webhooks->data));
        // Kept for callers of the old helper. Setup is now an explicit admin POST.
        return false;
    }

    public static function getSigningSecrets($settings)
    {
        global $global;
        require_once __DIR__ . '/Objects/StripeConfiguration.php';
        return StripeConfiguration::signingSecrets($settings, $global['webSiteRootURL'] . 'plugin/StripeYPT/ipn.php');
    }

    public function getIntent($total = '1.00', $currency = "USD", $description = "", $metadata = array(), $customer = "", $future_usage = "")
    {
        global $global, $config, $getIntentErrorResponse;
        $getIntentErrorResponse = '';
        $this->start();
        $total = number_format(floatval($total), 2, "", "");
        $users_id = User::getId();
        if (empty($description)) {
            $description = $config->getWebSiteTitle() . " Payment";
        }
        $parameters = [
            'amount' => $total,
            'currency' => $currency,
            'description' => $description,
            'metadata' => $metadata,
            //'confirm'=> true
        ];
        $email = User::getEmail_();
        if (isValidEmail($email)) {
            $parameters['receipt_email'] = $email;
        }
        if (!empty($customer)) {
            $parameters['customer'] = $customer;
        }
        if (!empty($customer)) {
            $parameters['setup_future_usage'] = $future_usage;
        }
        _error_log("StripeYPT::getIntent $total , $currency, $description");
        try {
            if (!empty($metadata['singlePayment']) && ($metadata['plugin'] ?? '') === 'PayPerView') {
                $ppv = AVideoPlugin::loadPluginIfEnabled('PayPerView');
                if (empty($ppv)) {
                    throw new \InvalidArgumentException('PayPerView is disabled');
                }
                // Older independently deployed PPV versions keep their existing description.
                if (method_exists($ppv, 'getPaymentDetails')) {
                    $autoRental = intval($metadata['videos_id'] ?? 0) > 0 && method_exists($ppv, 'completeWalletCheckout');
                    $details = $ppv->getPaymentDetails($metadata['plans_id'] ?? 0, $metadata['videos_id'] ?? 0, !$autoRental);
                    if (empty($details)) {
                        throw new \InvalidArgumentException('Invalid PayPerView payment selection');
                    }
                    $parameters['description'] = $details['description'];
                    $parameters['metadata'] = array_merge($metadata, $details['metadata']);
                    if ($autoRental) {
                        if (empty($details['value']) || $details['value'] <= 0 || $users_id < 1) {
                            throw new \InvalidArgumentException('Invalid PayPerView rental value');
                        }
                        $parameters['metadata']['ppv_auto_rent'] = '1';
                        $parameters['metadata']['users_id'] = (string) $users_id;
                        $parameters['metadata']['ppv_value'] = number_format($details['value'], 2, '.', '');
                    }
                }
            }
            if (!empty($metadata['singlePayment']) && ($metadata['plugin'] ?? '') === 'PayPerViewLive') {
                $live = AVideoPlugin::loadPluginIfEnabled('PayPerViewLive');
                if (empty($live) || !method_exists($live, 'getPaymentDetails') || !method_exists($live, 'completeWalletCheckout')) {
                    throw new \InvalidArgumentException('PayPerViewLive checkout is unavailable');
                }
                $details = $live->getPaymentDetails($metadata['ppvlive_schedule_id'] ?? 0);
                if (empty($details) || $users_id < 1) {
                    throw new \InvalidArgumentException('Invalid live event selection');
                }
                $purchase = $live->getSubscriptionToPlan($users_id, $metadata['ppvlive_schedule_id'] ?? 0);
                if (!empty($purchase['id']) && $purchase['status'] === 'a') {
                    $getIntentErrorResponse = 'You already have access to this event. Reload the page to open it; no card payment is needed.';
                    throw new \InvalidArgumentException('Live event already purchased');
                }
                $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
                $balance = $wallet->getBalance($users_id);
                $amount = max(0, round(($details['value'] - $balance) * 100));
                if ($amount < 1) {
                    $getIntentErrorResponse = 'Your balance covers this event. Reload the page and use your wallet; no card payment is needed.';
                    throw new \InvalidArgumentException('Live event already covered by the wallet');
                }
                $parameters['amount'] = $amount;
                $parameters['description'] = $details['description'];
                $parameters['metadata'] = array_merge($metadata, $details['metadata'], [
                    'users_id' => (string) $users_id, 'ppvlive_auto_buy' => '1',
                    'ppvlive_value' => number_format($details['value'], 2, '.', '')]);
            }
            $intent = !empty($metadata['singlePayment']) && $users_id > 0
                ? $this->getSinglePaymentIntentLocked($parameters)
                : \Stripe\PaymentIntent::create($parameters);

            _error_log('StripeYPT::getIntent success id=' . $intent->id . ' status=' . $intent->status
                . ' users_id=' . intval($users_id) . ' amount=' . intval($intent->amount) . ' currency=' . $intent->currency);
            return $intent;
        } catch (\Throwable $exc) {
            _error_log("StripeYPT::getIntent error " . $exc->getMessage());
            _error_log($exc->getTraceAsString());
        }
        return false;
    }

    private function getSinglePaymentIntentLocked($parameters)
    {
        global $global, $getIntentErrorResponse;
        // Unlike cache files, this state must survive cache cleanup and PHP requests.
        // No existing payment-attempt store exists in the file helpers or StripeYPT.
        $directory = $global['systemRootPath'] . 'videos/stripe-payment-locks/';
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new \RuntimeException('Cannot create Stripe payment lock directory');
        }
        $settings = $this->getDataObject();
        $scope = hash('sha256', $global['webSiteRootURL'] . '|' . $settings->Restrictedkey . '|' . User::getId());
        $statePath = $directory . $scope . '.php';
        $file = @fopen($directory . $scope . '.lock', 'c+');
        if (!$file) {
            throw new \RuntimeException('Cannot open Stripe payment lock');
        }
        $locked = false;
        try {
            $locked = flock($file, LOCK_EX | LOCK_NB);
            if (!$locked) {
                $getIntentErrorResponse = 'A payment request is already being processed. Please wait and try again.';
                throw new \RuntimeException('Stripe payment lock busy');
            }
            $hasState = is_file($statePath);
            $contents = $hasState ? file_get_contents($statePath) : '';
            if ($contents === false) {
                throw new \RuntimeException('Cannot read Stripe payment lock state');
            }
            $prefix = "<?php exit; ?>\n";
            $state = $contents === '' ? [] : json_decode(substr($contents, strlen($prefix)), true);
            if ($hasState && (strpos($contents, $prefix) !== 0 || !is_array($state) || empty($state['key']) || empty($state['created']) || empty($state['fingerprint']))) {
                throw new \RuntimeException('Invalid Stripe payment lock state; reconciliation required');
            }
            $fingerprint = hash('sha256', json_encode($parameters));
            if (!empty($state) && empty($state['intent'])) {
                // A timeout may have hidden a successful create. Replay its original key while
                // Stripe still honours it (24h); otherwise start over. That hidden intent was never
                // confirmed and its client secret never left the server, so it cannot charge anyone.
                if ($state['fingerprint'] === $fingerprint && time() - $state['created'] < 23 * 3600) {
                    $intent = $this->createSinglePaymentIntent($parameters, $state, $statePath);
                    $state['intent'] = $intent->id;
                    $this->saveSinglePaymentState($statePath, $state);
                } else {
                    $state = [];
                }
            }
            if (!empty($state)) {
                $intent = \Stripe\PaymentIntent::retrieve($state['intent']);
                if ($intent->status === 'succeeded') {
                    // Measure the guard from when the charge happened. The browser confirms the
                    // card, so this server usually first observes the success on the user's next
                    // purchase, possibly days later; starting the guard then would block it.
                    // Fetch the charge separately: expanding it would put card and billing
                    // details into the intent that is logged and returned to the browser.
                    $charge = $intent->latest_charge;
                    if (is_string($charge) && $charge !== '') {
                        try {
                            $charge = \Stripe\Charge::retrieve($charge);
                        } catch (\Stripe\Exception\PermissionException $exception) {
                            // Restricted key without "Charges: Read". Degrade to the observation
                            // guard below instead of locking the customer out, and tell the admin.
                            _error_log('StripeYPT::getIntent the Stripe restricted key cannot read Charges; grant it "Charges: Read", otherwise repeat purchases wait 5 minutes: ' . $exception->getMessage());
                            $charge = null;
                        }
                    }
                    if (is_object($charge) && !empty($charge->created)) {
                        $paidAt = (int) $charge->created;
                    } else {
                        // Fail closed if Stripe did not tell when it was paid.
                        if (empty($state['completed_at'])) {
                            $state['completed_at'] = time();
                            $this->saveSinglePaymentState($statePath, $state);
                        }
                        $paidAt = (int) $state['completed_at'];
                    }
                    if (time() - $paidAt < 300) {
                        if ($state['fingerprint'] === $fingerprint) {
                            return $intent;
                        }
                        $getIntentErrorResponse = 'Your previous payment was processed. Please wait five minutes before making another payment. No new charge was made.';
                        throw new \RuntimeException('Stripe payment completion cooldown');
                    }
                    // A delayed or failing webhook must not lead the customer into paying twice.
                    if ($this->isSinglePaymentCreditPending($intent, $charge, $paidAt)) {
                        if ($state['fingerprint'] === $fingerprint) {
                            return $intent;
                        }
                        $getIntentErrorResponse = 'Your previous card payment was approved and is still being added to your account. No new charge was made. Please wait and check your balance.';
                        throw new \RuntimeException('Stripe payment credit pending');
                    }
                } elseif ($intent->status !== 'canceled') {
                    if (in_array($intent->status, ['processing', 'requires_capture'], true)) {
                        $getIntentErrorResponse = 'A payment request is already being processed. Please wait and try again.';
                        throw new \RuntimeException('Stripe payment still processing');
                    }
                    if ($state['fingerprint'] === $fingerprint && time() - $state['created'] < 900) {
                        return $intent;
                    }
                    // A different amount or an expired attempt: invalidate the old client secret
                    // first. If cancellation races with confirmation or fails, do not create another.
                    $intent = $intent->cancel();
                    if ($intent->status !== 'canceled') {
                        throw new \RuntimeException('Previous Stripe payment could not be canceled');
                    }
                }
            }
            $state = ['key' => 'avideo_payment_' . bin2hex(random_bytes(24)), 'created' => time(), 'fingerprint' => $fingerprint];
            $this->saveSinglePaymentState($statePath, $state);
            $intent = $this->createSinglePaymentIntent($parameters, $state, $statePath);
            $state['intent'] = $intent->id;
            $this->saveSinglePaymentState($statePath, $state);
            return $intent;
        } finally {
            if ($locked) {
                flock($file, LOCK_UN);
            }
            fclose($file);
            // Never unlink a flock file: another worker may already have it open.
        }
    }

    /**
     * True while a paid single payment is not yet in the payer's wallet, within Stripe's 3-day webhook
     * retry window. A refunded charge will never be credited, and older wallets cannot be asked.
     */
    private function isSinglePaymentCreditPending($intent, $charge, $paidAt)
    {
        if (time() - $paidAt >= 3 * 86400 || (is_object($charge) && !empty($charge->refunded))) {
            return false;
        }
        $wallet = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
        if (empty($wallet) || !method_exists($wallet, 'isStripePaymentCredited')) {
            return false;
        }
        return !$wallet->isStripePaymentCredited($intent->id, User::getId());
    }

    private function createSinglePaymentIntent($parameters, $state, $statePath)
    {
        try {
            return \Stripe\PaymentIntent::create($parameters, ['idempotency_key' => $state['key']]);
        } catch (\Stripe\Exception\InvalidRequestException $exception) {
            // A definite validation rejection (e.g. below Stripe's minimum amount)
            // can be corrected. Ambiguous/network/idempotency failures keep the key.
            if ($exception->getHttpStatus() === 400 && $exception->getStripeCode() !== 'idempotency_key_in_use') {
                if (!unlink($statePath)) {
                    throw new \RuntimeException('Cannot clear rejected Stripe payment attempt', 0, $exception);
                }
            }
            throw $exception;
        }
    }

    private function saveSinglePaymentState($statePath, $state)
    {
        // Replace state atomically; a crash must not erase the saved idempotency key.
        $contents = "<?php exit; ?>\n" . json_encode($state);
        $temporary = $statePath . '.' . bin2hex(random_bytes(8)) . '.php';
        try {
            if (file_put_contents($temporary, $contents) !== strlen($contents) || !rename($temporary, $statePath)) {
                throw new \RuntimeException('Cannot persist Stripe payment attempt');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    public function setUpPayment($total = '1.00', $currency = "USD", $description = "")
    {
        global $global;
        $this->start();
        $total = number_format(floatval($total), 2, "", "");
        _error_log("StripeYPT::setUpPayment $total , $currency, $description");
        if (!empty($_POST['stripeToken'])) {
            $token = $_POST['stripeToken'];
            try {
                $charge = \Stripe\Charge::create([
                    'amount' => $total,
                    'currency' => $currency,
                    'description' => $description,
                    'source' => $token,
                ]);

                // The full charge holds billing name, e-mail, address and card details: log identifiers only.
                _error_log('StripeYPT::setUpPayment charge id=' . $charge->id . ' status=' . $charge->status
                    . ' amount=' . intval($charge->amount) . ' currency=' . $charge->currency);
                return $charge;
            } catch (Exception $exc) {
                _error_log("StripeYPT::setUpPayment error " . $exc->getMessage());
                _error_log($exc->getTraceAsString());
            }
        } else {
            _error_log("StripeYPT::setUpPayment stipeToken empty");
        }
        return false;
    }

    static function getAmountFromPayment($payment)
    {
        if (!is_object($payment)) {
            return false;
        }
        if (empty($payment->amount)) {
            return 0;
        }
        return self::addDot($payment->amount);
    }

    static function addDot($value)
    {
        $val = substr($value, 0, -2);
        $cents = substr($value, -2);
        return floatval("$val.$cents");
    }

    static function removeDot($value)
    {
        $value = floatval($value);
        return number_format($value, 2, "", "");
    }

    static function getCurrencyFromPayment($payment)
    {
        if (!is_object($payment)) {
            return false;
        }
        return $payment->currency;
    }

    static function isPaymentOk($payment, $value, $currency)
    {
        _error_log("isPaymentOk: " . json_encode($payment));
        _error_log("isPaymentOk: $value, $currency");
        if (!is_object($payment)) {
            _error_log("isPaymentOk: NOT object");
            return false;
        }

        if (empty($payment->paid)) {
            _error_log("isPaymentOk: NOT paid");
            return false;
        }

        if (strcasecmp($currency, self::getCurrencyFromPayment($payment)) !== 0) {
            _error_log("isPaymentOk: NOT same currency");
            return false;
        }

        if ($value > self::getAmountFromPayment($payment)) {
            _error_log("isPaymentOk: NOT same amount");
            return false;
        }
        return true;
    }

    public function createCostumer($users_id, $stripeToken)
    {
        global $global;

        $user = new User($users_id);

        if (!empty($user)) {
            try {
                $this->start();
                return \Stripe\Customer::create([
                    "description" => "Customer [$users_id] " . $user->getNameIdentificationBd() . "(" . $user->getEmail() . ")",
                    "source" => $stripeToken, // obtained with Stripe.js
                    'metadata' => [
                        'users_id' => $users_id,
                    ],
                ], ['idempotency_key' => 'avideo_customer_' . hash('sha256', $global['webSiteRootURL'] . '|' . $users_id . '|' . $stripeToken)]);
            } catch (Exception $exc) {
                _error_log($exc->getTraceAsString());
            }
        }
        return false;
    }

    public function getCostumerId($users_id, $stripeToken)
    {

        $costumer = $this->createCostumer($users_id, $stripeToken);

        if (!empty($costumer)) {
            if (self::isCostumerValid($costumer->id)) {
                return $costumer->id;
            } else {
                return false;
            }
        }

        return false;
    }

    public static function isCostumerValid($id)
    {
        if ($id == 'canceled') {
            return false;
        }
        //_error_log("StripeYPT::isCostumerValid $id");
        try {
            $c = \Stripe\Customer::retrieve($id);
            if ($c) {
                //_error_log("StripeYPT::isCostumerValid IS VALID: " . json_encode($c));
                return true;
            } else {
                _error_log("StripeYPT::isCostumerValid NOT FOUND {$id}");
                return false;
            }
        } catch (Exception $exc) {
            _error_log("StripeYPT::isCostumerValid ERROR {$id}");
            return false;
        }
    }

    private function createBillingPlan($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement', $requestOptions = [])
    {
        global $global;
        $this->start();
        return \Stripe\Plan::create([
            'currency' => $currency,
            'interval' => $frequency,
            'interval_count' => $interval,
            "product" => [
                "name" => $name,
                "type" => "service"
            ],
            'nickname' => $name,
            'amount' => self::removeDot($total),
            'metadata' => array('users_id' => User::getId(), 'recurrent' => 1)
        ], $requestOptions);
    }

    function updateBillingPlan($plans_id, $total = '1.00', $currency = "USD", $interval = 1, $name = 'Base Agreement')
    {
        global $global;
        if (empty($plan_id)) {
            return false;
        }
        $this->start();
        return \Stripe\Plan::update($plans_id, [
            'currency' => $currency,
            'interval_count' => $interval,
            "product" => [
                "name" => $name
            ],
            'nickname' => $name,
            'amount' => self::removeDot($total),
        ]);
    }

    static function getSubscriptions($stripe_costumer_id, $plans_id)
    {
        if (!User::isLogged()) {
            _error_log("getSubscription: User not logged");
            return false;
        }
        if (empty($stripe_costumer_id)) {
            _error_log("getSubscription: costumer ID is empty");
            return false;
        }
        global $global;
        $users_id = User::getId();
        $obj = AVideoPlugin::getObjectData('StripeYPT');
        \Stripe\Stripe::setApiKey($obj->Restrictedkey);

        /*
          $costumer = \Stripe\Customer::retrieve($stripe_costumer_id);
          if(!empty($costumer->subscriptions)){
          foreach ($costumer->subscriptions->data as $value) {
          $subscription = \Stripe\Subscription::retrieve($value->id);
          if ($subscription->metadata->users_id == $users_id && $subscription->metadata->plans_id == $plans_id) {
          //_error_log("StripeYPT::getSubscriptions $stripe_costumer_id, $plans_id " . json_encode($subscription));
          return $subscription;
          }
          }
          }

          _error_log("StripeYPT::getSubscriptions We could not find the subscription trying to expand $stripe_costumer_id, $plans_id " . json_encode($costumer));

          $costumer = \Stripe\Customer::retrieve($stripe_costumer_id,['expand' => ['subscriptions']]);
          if(!empty($costumer->subscriptions)){
          foreach ($costumer->subscriptions->data as $value) {
          $subscription = \Stripe\Subscription::retrieve($value->id);
          if ($subscription->metadata->users_id == $users_id && $subscription->metadata->plans_id == $plans_id) {
          //_error_log("StripeYPT::getSubscriptions $stripe_costumer_id, $plans_id " . json_encode($subscription));
          return $subscription;
          }
          }
          }

          _error_log("StripeYPT::getSubscriptions We could not find the subscription trying to list from subscription $stripe_costumer_id, $plans_id " . json_encode($costumer));

         */
        // I guess only this is enought
        $subscriptions = \Stripe\Subscription::all(['customer' => $stripe_costumer_id, 'status' => 'active']);
        if (!empty($subscriptions)) {
            foreach ($subscriptions->data as $value) {
                $subscription = \Stripe\Subscription::retrieve($value->id);
                if ($subscription->metadata->users_id == $users_id && $subscription->metadata->plans_id == $plans_id) {
                    //_error_log("StripeYPT::getSubscriptions $stripe_costumer_id, $plans_id " . json_encode($subscription));
                    return $subscription;
                }
            }
        }

        _error_log("StripeYPT::getSubscriptions ERROR $stripe_costumer_id, $plans_id " . json_encode($subscriptions));
        return false;
    }

    function userHasActiveSubscriptionOnPlan($plans_id)
    {
        $users_id = User::getId();
        if (empty($users_id)) {
            _error_log("StripeYPT::userHasActiveSubscriptionOnPlan($plans_id) users id is empty");
            return false;
        }
        $subscriptions = $this->getAllSubscriptionsSearch($users_id, $plans_id);
        $total = count($subscriptions->data);
        _error_log("StripeYPT::userHasActiveSubscriptionOnPlan($plans_id) total={$total}");
        if (empty($total)) {
            _error_log("StripeYPT::userHasActiveSubscriptionOnPlan($plans_id) empty total " . json_encode($subscriptions->data));
        }
        foreach ($subscriptions->data as $subscription) {
            _error_log("StripeYPT::userHasActiveSubscriptionOnPlan metadata " . json_encode($subscription->metadata));
            if ($users_id == $subscription->metadata->users_id) {
                if ($plans_id == $subscription->metadata->plans_id) {
                    return $subscription;
                }
            }
        }
        return false;
    }

    public function setUpSubscription($plans_id, $stripeToken)
    {
        global $global, $setUpSubscriptionErrorResponse;
        $setUpSubscriptionErrorResponse = '';
        if (!User::isLogged()) {
            $setUpSubscriptionErrorResponse = 'User not logged';
            _error_log("setUpSubscription: User not logged");
            return false;
        }
        if (empty($plans_id)) {
            $setUpSubscriptionErrorResponse = 'plans_id is empty';
            _error_log("setUpSubscription: plans_id is empty");
            return false;
        }
        // Same advisory-lock pattern as AuthorizeNet; serialize across PHP sessions/workers.
        $lockName = 'stripe_subscription_' . sha1($global['webSiteRootURL'] . '|' . User::getId() . '|' . intval($plans_id));
        $locked = false;
        try {
            $res = sqlDAL::readSql('SELECT GET_LOCK(?, ?) AS locked', 'si', [$lockName, 0], true);
            $row = $res ? sqlDAL::fetchAssoc($res) : false;
            sqlDAL::close($res);
            $locked = !empty($row['locked']);
            if (!$locked) {
                $setUpSubscriptionErrorResponse = 'A subscription request is already being processed. Please wait and try again.';
                return false;
            }
            return $this->setUpSubscriptionLocked($plans_id, $stripeToken);
        } catch (\Throwable $th) {
            _error_log('StripeYPT::setUpSubscription: ' . $th->getMessage(), AVideoLog::$ERROR);
            $setUpSubscriptionErrorResponse = 'An error occurred';
            return false;
        } finally {
            if ($locked) {
                $res = sqlDAL::readSql('SELECT RELEASE_LOCK(?) AS released', 's', [$lockName], true);
                sqlDAL::close($res);
            }
        }
    }

    /**
     * Local Stripe customer links for a user/plan, read bypassing the per-request sqlDAL cache
     * (writes do not invalidate it, and a stale read here could charge a second customer).
     */
    private static function getStripeCustomerLinks($users_id, $plans_id)
    {
        $sql = 'SELECT id, users_id, subscriptions_plans_id, stripe_costumer_id FROM ' . SubscriptionTable::getTableName()
            . ' WHERE users_id = ? AND subscriptions_plans_id = ?';
        $res = sqlDAL::readSql($sql, 'ii', [intval($users_id), intval($plans_id)], true);
        $rows = $res ? sqlDAL::fetchAllAssoc($res) : [];
        sqlDAL::close($res);
        return $rows;
    }

    private function setUpSubscriptionLocked($plans_id, $stripeToken)
    {
        global $global, $setUpSubscriptionErrorResponse;
        $this->start();
        $requestOptions = [];
        if ($plans_id > 0 || !User::isAdmin()) {
            $subs = new SubscriptionPlansTable($plans_id);
            $obj = AVideoPlugin::getObjectData('YPTWallet');

            if (empty($subs)) {
                $setUpSubscriptionErrorResponse = 'Plan not found"';
                _error_log("setUpSubscription: Plan not found");
                return false;
            }
            $subscription = $this->userHasActiveSubscriptionOnPlan($plans_id);
            if (!empty($subscription)) {
                $setUpSubscriptionErrorResponse = 'the user already have an active subscription for this plan';
                _error_log("setUpSubscription: the user already have an active subscription for this plan " . json_encode($subscription));
                return false;
            } else {
                _error_log("setUpSubscription: the user does not have any active subscription for this plan [{$plans_id}]");
            }

            // Search is eventually consistent. Check every locally linked customer directly,
            // including legacy duplicate rows, before creating anything that can charge.
            $sub = ['stripe_costumer_id' => ''];
            $customers = [];
            $endedSubscriptions = [];
            $incompleteSubscription = null;
            $planRows = self::getStripeCustomerLinks(User::getId(), $plans_id);
            foreach ($planRows as $row) {
                if (!empty($row['stripe_costumer_id']) && $row['stripe_costumer_id'] !== 'canceled') {
                    $customers[$row['stripe_costumer_id']] = true;
                }
            }
            $usableCustomers = [];
            foreach (array_keys($customers) as $customerId) {
                try {
                    $customer = \Stripe\Customer::retrieve($customerId);
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    // Only a missing customer (e.g. Stripe account/mode changed) is skipped; other errors abort.
                    if ($e->getStripeCode() !== 'resource_missing') {
                        throw $e;
                    }
                    continue;
                }
                if (!empty($customer->deleted)) {
                    continue;
                }
                $usableCustomers[$customerId] = true;
                $sub['stripe_costumer_id'] = $customerId;
                $subscriptions = \Stripe\Subscription::all(['customer' => $customerId, 'status' => 'all', 'limit' => 100]);
                foreach ($subscriptions->autoPagingIterator() as $existing) {
                    if ($existing->metadata->users_id != User::getId() || $existing->metadata->plans_id != $plans_id) {
                        continue;
                    }
                    if (!in_array($existing->status, ['canceled', 'incomplete_expired'], true)) {
                        if ($existing->status === 'incomplete') {
                            $incompleteSubscription = $existing;
                            continue;
                        }
                        $setUpSubscriptionErrorResponse = 'the user already have an active subscription for this plan';
                        _error_log('setUpSubscription: prevented duplicate subscription ' . $existing->id);
                        return false;
                    }
                    $endedSubscriptions[] = $existing->id;
                }
            }
            if (!empty($incompleteSubscription)) {
                return \Stripe\Subscription::retrieve(['id' => $incompleteSubscription->id, 'expand' => ['latest_invoice.payment_intent']]);
            }

            // Local rows whose customer is 'canceled', deleted or missing.
            $staleRows = [];
            foreach ($planRows as $row) {
                if (!empty($row['stripe_costumer_id']) && empty($usableCustomers[$row['stripe_costumer_id']])) {
                    $staleRows[] = $row;
                }
            }
            if (empty($sub['stripe_costumer_id'])) {
                $sub['stripe_costumer_id'] = $this->getCostumerId(User::getId(), $stripeToken);
                if (empty($sub['stripe_costumer_id'])) {
                    _error_log("setUpSubscription: Could not create a Stripe costumer");
                    $setUpSubscriptionErrorResponse = 'Your card could not be verified. Check your card details or try another card.';
                    return false;
                }
                if (!empty($staleRows)) {
                    // getOrCreateStripeSubscription() never replaces a non-empty customer id,
                    // so relink the stale row to the new customer.
                    if (!preg_match('/^cus_[A-Za-z0-9]+$/', $sub['stripe_costumer_id'])) {
                        throw new \RuntimeException('Unexpected Stripe customer id');
                    }
                    SubscriptionTable::updateStripeCostumerId($staleRows[0]['id'], $sub['stripe_costumer_id']);
                } else {
                    // Call once, with the customer already known. Calling first with an empty
                    // customer can cache a missing row and make the second call insert again.
                    Subscription::getOrCreateStripeSubscription(User::getId(), $plans_id, $sub['stripe_costumer_id']);
                }
                // Never charge a customer that retries would not find again.
                $saved = false;
                foreach (self::getStripeCustomerLinks(User::getId(), $plans_id) as $row) {
                    if ($row['stripe_costumer_id'] === $sub['stripe_costumer_id']) {
                        $saved = true;
                    }
                }
                if (!$saved) {
                    throw new \RuntimeException('Could not persist the Stripe customer for this subscription');
                }
            }

            // The customer is persisted and read back fresh under the lock, so every retry and
            // double click charges the same customer with the same key. A cancellation adds an
            // ended subscription or a new customer, which renews the key.
            sort($endedSubscriptions);
            $requestOptions = ['idempotency_key' => 'avideo_subscription_' . hash('sha256', $global['webSiteRootURL'] . '|' . User::getId() . '|' . $plans_id . '|' . $sub['stripe_costumer_id'] . '|' . implode(',', $endedSubscriptions))];

            // check plan
            $stripe_plan_id = $subs->getStripe_plan_id();
            if (empty($stripe_plan_id)) {
                $bi = YPTWallet::getBillingInterval($subs->getHow_many_days());
                $price = $subs->getPrice();
                $paymentName = $subs->getName();
                if (empty($paymentName)) {
                    $paymentName = "Recurrent Payment";
                }

                $plan = $this->createBillingPlan($price, $obj->currency, $bi->stripeFrequency, $bi->stripeInterval, $paymentName, ['idempotency_key' => $requestOptions['idempotency_key'] . '_plan']);
                if (empty($plan)) {
                    _error_log("setUpSubscription: could not create stripe plan");
                    return false;
                }
                $stripe_plan_id = $plan->id;
            }
        } else {
            $sub = array();
            $sub['stripe_costumer_id'] = $this->getCostumerId(User::getId(), $stripeToken);
            if (empty($sub['stripe_costumer_id'])) {
                _error_log("Could not create a customer");
                return false;
            }
            $plan = $this->createBillingPlan($_REQUEST['value'], 'USD', "day", 1, 'Test plan');
            if (empty($plan)) {
                _error_log("setUpSubscription: could not create stripe plan");
                return false;
            }
            $stripe_plan_id = $plan->id;
        }



        _error_log("setUpSubscription: will start");
        $this->start();

        $metadata = new stdClass();
        $metadata->users_id = User::getId();
        $metadata->plans_id = $plans_id;
        $metadata->stripe_costumer_id = $sub['stripe_costumer_id'];
        StripeYPT::updateCustomerMetadata($metadata->stripe_costumer_id, ['users_id' => $metadata->users_id]);

        $subMetadata = [
            'users_id' => User::getId(),
            'plans_id' => $plans_id,
            'stripe_costumer_id' => $sub['stripe_costumer_id']
        ];
        $parameters = [
            "customer" => $sub['stripe_costumer_id'],
            "items" => [
                [
                    "plan" => $stripe_plan_id,
                ]
            ],
            "metadata" => $subMetadata,
            "expand" => ['latest_invoice.payment_intent']
        ];
        if (!empty($subs) && is_object($subs)) {
            $trialDays = $subs->getHow_many_days_trial();
            if (!empty($trialDays)) {
                $parameters['trial_period_days'] = intval($trialDays);
            }
        }

        _error_log("setUpSubscription: parameters " . json_encode($parameters));
        $Subscription = \Stripe\Subscription::create($parameters, $requestOptions);

        StripeYPT::updateSubscriptionMetadata($Subscription->id, $subMetadata);
        _error_log('setUpSubscription: id=' . $Subscription->id . ' status=' . $Subscription->status);
        return $Subscription;
    }

    static function updateCustomerMetadata($stripe_costumer_id, $metadata = [])
    {
        if (empty($stripe_costumer_id)) {
            return false;
        }
        if (empty($metadata)) {
            return false;
        }
        try {
            _error_log('updateCustomerMetadata: ' . json_encode($metadata));
            self::_start();
            return \Stripe\Customer::update(
                $stripe_costumer_id,
                ['metadata' => $metadata]
            );
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            _error_log('updateCustomerMetadata: Error updating customer: ' . $e->getMessage());
        } catch (\Stripe\Exception\ApiErrorException $e) {
            _error_log('updateCustomerMetadata: Stripe API error: ' . $e->getMessage());
        } catch (Exception $e) {
            _error_log('updateCustomerMetadata: General error: ' . $e->getMessage());
        }
        return false;
    }

    static function updateSubscriptionMetadata($stripe_subscription_id, $metadata = [])
    {
        if (empty($stripe_costumer_id)) {
            return false;
        }
        if (empty($metadata)) {
            return false;
        }
        try {
            _error_log('updateSubscriptionMetadata: ' . json_encode($metadata));
            self::_start();
            return \Stripe\Subscription::update(
                $stripe_subscription_id,
                ['metadata' => $metadata]
            );
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            _error_log('updateSubscriptionMetadata: Error updating customer: ' . $e->getMessage());
        } catch (\Stripe\Exception\ApiErrorException $e) {
            _error_log('updateSubscriptionMetadata: Stripe API error: ' . $e->getMessage());
        } catch (Exception $e) {
            _error_log('updateSubscriptionMetadata: General error: ' . $e->getMessage());
        }
        return false;
    }

    static function getAmountPaidFromPayload($payload)
    {
        $amount = "000";
        if (!empty($payload->data->object->amount_paid)) {
            $amount = $payload->data->object->amount_paid;
        } else if (!empty($payload->data->object->amount_captured)) {
            $amount = $payload->data->object->amount_captured;
        }
        if (empty($amount)) {
            _error_log("Stripe:getAmountPaidFromPayload is empty " . json_encode($payload), AVideoLog::$ERROR);
        } else {
            _error_log("Stripe:getAmountPaidFromPayload {$amount} ");
        }
        return self::addDot($amount);
    }

    function processSubscriptionIPN($payload)
    {
        global $global;
        $logContext = self::getPaymentLogContext($payload);
        if (!is_object($payload) || empty($payload->data->object->customer)) {
            // e.g. setUpPayment() charges: not an AVideo subscription, acknowledged without changes.
            _error_log("processSubscriptionIPN: ignored, the payment has no customer {$logContext}", AVideoLog::$WARNING);
            return false;
        }
        $pluginS = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
        $metadata = self::getMetadataOrFromSubscription($payload);
        if (empty($metadata['users_id'])) {
            _error_log("processSubscriptionIPN: ignored, users_id metadata not found {$logContext}", AVideoLog::$WARNING);
            return false;
        }
        $payment_amount = self::getAmountPaidFromPayload($payload);
        $logContext .= " users_id={$metadata['users_id']} plans_id=" . intval($metadata['plans_id'] ?? 0) . " amount={$payment_amount}";
        $hasSubscription = AVideoPlugin::isEnabledByName('Subscription') && class_exists('Subscription');
        if (!$hasSubscription && AVideoPlugin::isEnabledByName('DiskUploadQuota') && class_exists('DiskUploadQuota')) {
            // This separate integration owns its payment processing; preserve its dispatch contract.
            _error_log("processSubscriptionIPN: handled by DiskUploadQuota {$logContext}");
            $ipnFIle = "{$global['systemRootPath']}plugin/DiskUploadQuota/Subscription/Stripe/ipn.php";
            require_once $ipnFIle;
            exit;
        }
        _error_log("processSubscriptionIPN: processing {$logContext}");
        return $pluginS->processPaymentOnce('stripe', self::getPaymentIds($payload), $metadata['users_id'],
            function () use ($pluginS, $payload, $metadata, $payment_amount, $hasSubscription, $logContext) {
                if (!$hasSubscription) {
                    $pluginS->addBalance($metadata['users_id'], $payment_amount,
                        'Stripe recurrent (no plan detected): ' . ($payload->data->object->description ?? ''), json_encode($payload));
                    return true;
                }
                if (empty($metadata['plans_id'])) {
                    // A processed invoice may already cover this charge. Otherwise retry before
                    // creating a null-plan row or marking a payment whose plan is still unknown.
                    throw new RuntimeException('Stripe subscription plan metadata is not available yet');
                }
                $plan = Subscription::getOrCreateStripeSubscription($metadata['users_id'], $metadata['plans_id'], $payload->data->object->customer);
                if (empty($plan['users_id']) || empty($plan['subscriptions_plans_id'])) {
                    // Keep the money: credit the payer so the subscription can be renewed manually.
                    _error_log("processSubscriptionIPN: subscription not found, only the wallet was credited {$logContext}", AVideoLog::$ERROR);
                    $pluginS->addBalance($metadata['users_id'], $payment_amount,
                        'Stripe recurrent (subscription not found): ' . ($payload->data->object->description ?? ''), json_encode($payload));
                    return true;
                }
                $pluginS->addBalance($plan['users_id'], $payment_amount,
                    'Stripe recurrent: ' . ($payload->data->object->description ?? ''), json_encode($payload));
                $renewed = $pluginS->runPaymentStep(function () use ($plan, $logContext) {
                    $renew = Subscription::renew($plan['users_id'], $plan['subscriptions_plans_id']);
                    if (!isset($renew->error) || $renew->error) {
                        _error_log('processSubscriptionIPN: Subscription::renew failed: ' . ($renew->msg ?? '') . " {$logContext}", AVideoLog::$ERROR);
                        return false;
                    }
                    return true;
                }, 'Stripe subscription renewal');
                if (!$renewed) {
                    _error_log("processSubscriptionIPN: payment credited to the wallet but the renewal was undone; renew it manually {$logContext}", AVideoLog::$ERROR);
                }
                return true;
            });
    }

    /** Invoice and charge notifications can describe the same payment. */
    public static function getPaymentIds($payload)
    {
        $object = $payload->data->object;
        $ids = [$object->id ?? null, $payload->id ?? null];
        foreach (['invoice', 'charge', 'payment_intent'] as $field) {
            $value = $object->$field ?? null;
            $ids[] = is_object($value) ? ($value->id ?? null) : $value;
        }
        return array_values(array_unique(array_filter($ids, function ($id) {
            return is_string($id) && $id !== '';
        })));
    }

    /**
     * Non-secret identifiers of a Stripe event, to find it in the logs and in the Stripe dashboard.
     */
    public static function getPaymentLogContext($payload)
    {
        $object = $payload->data->object ?? null;
        $customer = $object->customer ?? '';
        return 'event=' . ($payload->id ?? '') . ' type=' . ($payload->type ?? '') . ' object=' . ($object->id ?? '')
            . ' customer=' . (is_string($customer) ? $customer : ($customer->id ?? ''));
    }

    /**
     * Return plans an users id
     * @param array $payload
     */
    static function getMetadata($payload)
    {
        $payload = _json_decode($payload);
        foreach ($payload as $value) {
            if (empty($value->users_id) && empty($value->plans_id)) {
                if (is_object($value) || is_array($value)) {
                    $obj = self::getMetadata($value);
                    if (!empty($obj)) {
                        return $obj;
                    }
                }
            } else {
                return array("users_id" => $value->users_id ?? null, "plans_id" => $value->plans_id ?? null);
            }
        }
        if (!empty($payload->users_id)) {
            return array("users_id" => $payload->users_id, "plans_id" => $payload->plans_id ?? null);
        }
        return false;
    }

    static function getSubscriptionId($payload)
    {
        foreach ($payload as $value) {
            if (empty($value->subscription) && (is_object($value) || is_array($value))) {
                $obj = self::getSubscriptionId($value);
                if (!empty($obj)) {
                    return $obj;
                }
            } else if (!empty($value->subscription)) {
                if (preg_match('/^sub_/', $value->subscription)) {
                    return $value->subscription;
                }
            }
        }
        return false;
    }

    static function getCustomerId($payload)
    {
        return $payload->data->object->customer;
    }

    static function getMetadataOrFromSubscription($payload)
    {
        $obj = self::getMetadata($payload);
        if (empty($obj) || empty($obj['plans_id'])) {
            _error_log('getMetadataOrFromSubscription not found, try from subscription ID');
            $subscription_id = self::getSubscriptionId($payload);
            if (empty($subscription_id) && ($payload->type ?? '') === 'charge.succeeded') {
                // A subscription charge references the invoice, not the subscription itself.
                // Customer metadata identifies the user only; never guess their plan from it.
                $invoiceId = $payload->data->object->invoice ?? '';
                if (is_object($invoiceId)) {
                    $invoiceId = $invoiceId->id ?? '';
                }
                try {
                    // A newer event snapshot can omit invoice while the pinned API still exposes it.
                    if (empty($invoiceId) && !empty($payload->data->object->id)) {
                        $charge = \Stripe\Charge::retrieve($payload->data->object->id);
                        $invoiceId = $charge->invoice ?? '';
                    }
                    if (is_string($invoiceId) && preg_match('/^in_[a-zA-Z0-9]+$/', $invoiceId)) {
                        $invoice = \Stripe\Invoice::retrieve($invoiceId);
                        $subscription_id = self::getSubscriptionId((object) ['invoice' => $invoice]);
                    }
                } catch (\Stripe\Exception\PermissionException $exception) {
                    // Legacy restricted keys can still fulfill through invoice.payment_succeeded.
                    _error_log('Stripe subscription: Charges/Invoices Read permission is needed to resolve this charge; waiting for its invoice notification', AVideoLog::$WARNING);
                }
            }
            if (!empty($subscription_id)) {
                _error_log('getMetadataOrFromSubscription subscription_id found ' . $subscription_id);
                $subscription = self::retrieveSubscriptions($subscription_id);
                if (!empty($subscription)) {
                    $obj = self::getMetadata($subscription->metadata);
                }
            } else {
                _error_log('getMetadataOrFromSubscription subscription_id NOT found ');
            }
        }
        if (empty($obj)) {
            _error_log('getMetadataOrFromSubscription not found, try from customer ID');
            $customer_id = self::getCustomerId($payload);

            $c = \Stripe\Customer::retrieve($customer_id);

            if (!empty($c)) {
                _error_log("getMetadataOrFromSubscription Customer::retrieve [$customer_id] => " . json_encode($c->metadata));
                $obj = self::getMetadata($c->metadata);
                _error_log("getMetadataOrFromSubscription Customer::retrieve done " . json_encode($obj));
                if (empty($obj) && !empty($c->email)) {
                    _error_log("getMetadataOrFromSubscription try from email " . json_encode($c->email));
                    $user = User::getUserFromEmail($c->email);
                    $obj = array('users_id' => $user['id']);
                }
            }
        }
        return $obj;
    }

    static function isSinglePayment($payload)
    {
        return $payload->type == "charge.succeeded" && !empty($payload->data->object->metadata->singlePayment);
    }

    static function isSubscriptionPayment($payload)
    {
        return ($payload->type == "invoice.payment_succeeded" && !empty($payload->data->object->customer)) || $payload->type == "charge.succeeded" && empty($payload->data->object->metadata->singlePayment);
    }

    static function isSubscriptionCanceled($payload)
    {
        return ($payload->type == "customer.subscription.deleted" && !empty($payload->data->object->customer));
    }

    /**
     * An event is a snapshot taken when the charge succeeded, and Stripe retries failed deliveries for
     * days: read the charge as it is now so a refund made in the meantime is not credited (in cents).
     */
    private static function getSinglePaymentAmountStillPaid($payload, $logContext, &$chargeVerified = null)
    {
        $chargeVerified = false;
        $charge = $payload->data->object;
        try {
            $charge = \Stripe\Charge::retrieve($charge->id);
            $chargeVerified = true;
        } catch (\Stripe\Exception\PermissionException $exception) {
            // Restricted key without "Charges: Read": keep crediting the delivered snapshot.
            _error_log("Stripe processSinglePaymentIPN: grant the restricted key \"Charges: Read\" so refunds are detected; using the event snapshot {$logContext}", AVideoLog::$WARNING);
        }
        if (($charge->status ?? 'succeeded') !== 'succeeded') {
            _error_log("Stripe processSinglePaymentIPN: ignored, the charge is now {$charge->status} {$logContext}", AVideoLog::$WARNING);
            return 0;
        }
        $amount = intval($charge->amount) - intval($charge->amount_refunded ?? 0);
        if ($amount <= 0) {
            _error_log("Stripe processSinglePaymentIPN: ignored, the charge was refunded before it was credited {$logContext}", AVideoLog::$WARNING);
        } elseif (!empty($charge->amount_refunded)) {
            _error_log("Stripe processSinglePaymentIPN: crediting only the part not refunded ({$amount}) {$logContext}", AVideoLog::$WARNING);
        }
        return $amount;
    }

    function processSinglePaymentIPN($payload)
    {
        $logContext = self::getPaymentLogContext($payload);
        if (!is_object($payload) || empty($payload->data->object->metadata->users_id)
            || empty($payload->data->object->id)) {
            _error_log("Stripe processSinglePaymentIPN: ignored, users_id metadata not found {$logContext}", AVideoLog::$WARNING);
            return false;
        }
        $amount = self::getSinglePaymentAmountStillPaid($payload, $logContext, $chargeVerified);
        if ($amount <= 0) {
            return false;
        }
        $metadata = $payload->data->object->metadata;
        $autoLive = ($metadata->ppvlive_auto_buy ?? '') === '1';
        $autoRental = ($metadata->ppv_auto_rent ?? '') === '1';
        if ($autoRental && (!$chargeVerified || $amount !== intval($payload->data->object->amount))) {
            // Never rent on an unverified or partially refunded charge. Retrying cannot change either
            // state, so credit what was paid and let the customer finish the rental from the wallet.
            _error_log('Stripe processSinglePaymentIPN: PPV rental left to the customer, '
                . ($chargeVerified ? 'the charge was partially refunded' : 'the Stripe key cannot read charges')
                . "; the payment is credited to the wallet {$logContext}", AVideoLog::$ERROR);
            $autoRental = false;
        }
        $autoLive = $autoLive && $chargeVerified && $amount === intval($payload->data->object->amount);
        $pluginS = AVideoPlugin::loadPluginIfEnabled('YPTWallet');
        // addDot() needs at least three digits: 5 cents must not become 0.5.
        $payment_amount = StripeYPT::addDot(str_pad((string) $amount, 3, '0', STR_PAD_LEFT));
        $users_id = $payload->data->object->metadata->users_id;
        _error_log("Stripe processSinglePaymentIPN: processing {$logContext} users_id={$users_id} amount={$payment_amount}");
        return $pluginS->processPaymentOnce('stripe', self::getPaymentIds($payload), $users_id,
            function () use ($pluginS, $users_id, $payment_amount, $payload, $metadata, $autoRental, $autoLive, $logContext) {
                $pluginS->addBalance($users_id, $payment_amount,
                    'Stripe single payment: ' . ($payload->data->object->description ?? ''), json_encode($payload));
                // A savepoint undoes only the rental (debit, seller/site split, rental row) when it fails:
                // the money must stay credited, since a deterministic failure (balance spent elsewhere,
                // changed price, disabled plugin) would otherwise retry until Stripe gives up.
                // Database errors are rethrown by runPaymentStep() and roll back the whole delivery.
                if ($autoRental && !$pluginS->runPaymentStep(function () use ($pluginS, $users_id, $payload, $metadata) {
                    $ppv = AVideoPlugin::loadPluginIfEnabled('PayPerView');
                    return ($metadata->plugin ?? '') === 'PayPerView' && !empty($ppv) && method_exists($ppv, 'completeWalletCheckout')
                        && strcasecmp((string) $payload->data->object->currency, (string) $pluginS->getDataObject()->currency) === 0
                        && $ppv->completeWalletCheckout($users_id, $metadata->ppv_plans_id ?? 0, $metadata->videos_id ?? 0,
                            $metadata->ppv_value ?? '', $metadata->rental_hours ?? 0) === true;
                }, 'PPV rental')) {
                    _error_log("Stripe processSinglePaymentIPN: PPV rental not completed; the payment stays in the customer's wallet to finish it {$logContext}", AVideoLog::$ERROR);
                }
                if ($autoLive && !$pluginS->runPaymentStep(function () use ($pluginS, $users_id, $payload, $metadata) {
                    $live = AVideoPlugin::loadPluginIfEnabled('PayPerViewLive');
                    return ($metadata->plugin ?? '') === 'PayPerViewLive' && !empty($live)
                        && method_exists($live, 'completeWalletCheckout')
                        && strcasecmp((string) $payload->data->object->currency, (string) $pluginS->getDataObject()->currency) === 0
                        && $live->completeWalletCheckout($users_id, $metadata->ppvlive_schedule_id ?? 0,
                            $metadata->ppvlive_value ?? '', $metadata->live_ends ?? '') === true;
                }, 'PPV Live access')) {
                    _error_log("Stripe processSinglePaymentIPN: PPV Live access not completed; the payment stays in the customer's wallet {$logContext}", AVideoLog::$ERROR);
                }
                return true;
            });
    }

    function getAllSubscriptions($status = 'active')
    {
        if (!User::isAdmin()) {
            _error_log("getAllSubscriptions: User not admin");
            return false;
        }
        global $global;
        $this->start();
        $limit = 1000;
        if (!empty($_REQUEST['debug'])) {
            $limit = 8;
        }

        $subscriptions = \Stripe\Subscription::all(['limit' => $limit, 'status' => $status]);

        while (empty($_REQUEST['debug']) && $subscriptions->has_more) {
            _error_log('getAllSubscriptions: has more, total now=' . count($subscriptions->data));
            $new_subscriptions = \Stripe\Subscription::all(['limit' => $limit, 'status' => $status, 'starting_after' => end($subscriptions->data)->id]);
            $subscriptions->has_more = $new_subscriptions->has_more;
            $subscriptions->data = array_merge($subscriptions->data, $new_subscriptions->data);
        }

        return $subscriptions;
    }

    function getAllSubscriptionsSearch($users_id, $plans_id)
    {
        if (!User::isLogged()) {
            _error_log("getAllSubscriptions: User not logged");
            return false;
        }
        global $global;
        $this->start();
        $limit = 100;
        if (!empty($_REQUEST['debug'])) {
            $limit = 8;
        }

        $metadataquery = '';
        if (!empty($users_id)) {
            $metadataquery .= " AND metadata['users_id']:'{$users_id}'";
        }
        if (!empty($plans_id)) {
            $metadataquery .= " AND metadata['plans_id']:'{$plans_id}'";
        }

        $subscriptions = \Stripe\Subscription::search(['limit' => $limit, 'query' => "status:'active' {$metadataquery}"]);

        return $subscriptions;
    }

    function getAllCreditCards($plans_id)
    {

        $s = new SubscriptionTable($plans_id);
        $customer_id = $s->getStripe_costumer_id();

        $this->start();

        // Retrieve the stored Stripe customer ID from the database
        //$customer_id = getCustomerIdFromDB($users_id);
        if (empty($customer_id)) {
            _error_log("getAllCreditCards: No Stripe customer ID found for plan {$plans_id}");
            return false;
        }

        try {
            // Fetch all payment methods (cards) for the customer
            $cards = \Stripe\Customer::allPaymentMethods($customer_id, ['type' => 'card']);

            return $cards->data;
        } catch (Exception $e) {
            _error_log("getAllCreditCards: Error fetching cards - " . $e->getMessage());
            return false;
        }
    }

    function addCard($customer_id, $paymentMethodId)
    {
        global $addCardErrorMessage;
        $addCardErrorMessage = '';

        try {
            $this->start();


            // Retrieve the Payment Method object
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);

            // Attach the payment method to the customer
            $paymentMethod->attach(['customer' => $customer_id]);

            // Optionally, set this as the default payment method for future payments
            \Stripe\Customer::update(
                $customer_id,
                [
                    'invoice_settings' => ['default_payment_method' => $paymentMethodId]
                ]
            );

            return $paymentMethod;
        } catch (\Stripe\Exception\ApiErrorException $exc) {
            _error_log("addCard: Error: " . $exc->getMessage());
            $addCardErrorMessage = $exc->getMessage();
            return false;
        }
    }

    function addCardToSubscription($subscription, $paymentMethod)
    {
        try {
            // Attach the payment method to the customer
            $paymentMethod->attach(['customer' => $subscription->customer]);

            // Set this payment method as the default for future invoices
            \Stripe\Customer::update(
                $subscription->customer,
                ['invoice_settings' => ['default_payment_method' => $paymentMethod->id]]
            );

            // Update the subscription to use the new payment method
            \Stripe\Subscription::update(
                $subscription->id,
                ['default_payment_method' => $paymentMethod->id]
            );

            return true;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            _error_log("addCardToSubscription: Error: " . $e->getMessage());
            return false;
        }
    }


    function deleteCard($customerId, $paymentMethodId)
    {
        try {
            $this->start();

            var_dump(array($customerId, $paymentMethodId));
            // Retrieve the Payment Method object
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            // Detach the payment method from the customer
            $response = $paymentMethod->detach();

            return $response;
        } catch (\Stripe\Exception\ApiErrorException $exc) {
            _error_log("deleteCard: Error: " . $exc->getMessage());
            return false;
        }
    }


    function cancelSubscriptions($id)
    {
        if (!User::isLogged()) {
            _error_log("cancelSubscriptions: must login");
            return false;
        }
        global $global;
        try {
            $this->start();
            $sub = \Stripe\Subscription::retrieve($id);
            $response = $sub->cancel();
            return $response;
        } catch (Exception $exc) {
            _error_log("cancelSubscriptions: Error: " . $exc->getMessage());
            return false;
        }
    }

    static function retrieveSubscriptions($id)
    {
        try {
            self::_start();
            $sub = \Stripe\Subscription::retrieve($id);
            return $sub;
        } catch (Exception $exc) {
            _error_log("retrieveSubscription: Error: " . $exc->getMessage());
            return false;
        }
    }

    public function getPluginMenu()
    {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/StripeYPT/pluginMenu.html';
        return file_get_contents($filename);
    }
}
