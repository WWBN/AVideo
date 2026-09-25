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
        return "2.1";
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();
        $obj->Publishablekey = "pk_test_aQT12wEjRLKhXgk77TX4ftfa";
        $obj->Restrictedkey = "rk_test_kjyL5JaoAQwyiptuRlSzYJMZ00kRqXkLux";
        //Before you can verify signatures, you need to retrieve your endpoint’s secret from your Dashboard’s Webhooks settings. Select an endpoint that you want to obtain the secret for, then select the Click to reveal button.
        $obj->SigningSecret = "whsec_54gqoVeSuoeXEiNPcFhMN0jkBZY0JJG3";
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
        self::getWebhook();
    }

    static function getWebhook()
    {
        global $global;
        $webhooks = \Stripe\WebhookEndpoint::all(["limit" => 20]);
        $notify_url = "{$global['webSiteRootURL']}plugin/StripeYPT/ipn.php";
        if (!empty($webhooks->data)) {
            foreach ($webhooks->data as $value) {
                if ($value->url === $notify_url) {
                    return $value;
                }
                //$endpoint = \Stripe\WebhookEndpoint::retrieve($value->id);
                //$endpoint->delete();
            }
        }

        return \Stripe\WebhookEndpoint::create([
            "url" => $notify_url,
            "enabled_events" => ["*"]
        ]);
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
            $intent = !empty($metadata['singlePayment']) && $users_id > 0
                ? $this->getSinglePaymentIntentLocked($parameters)
                : \Stripe\PaymentIntent::create($parameters);

            _error_log("StripeYPT::getIntent success " . json_encode($intent));
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
            if (!empty($state)) {
                if (empty($state['intent'])) {
                    // A timeout may have hidden a successful create. Replay its original key,
                    // never generate a new key after Stripe's retention window has elapsed.
                    if ($state['fingerprint'] !== $fingerprint || time() - $state['created'] >= 23 * 3600) {
                        throw new \RuntimeException('Unresolved Stripe payment creation; reconciliation required');
                    }
                    $intent = $this->createSinglePaymentIntent($parameters, $state, $statePath);
                    $state['intent'] = $intent->id;
                    $this->saveSinglePaymentState($statePath, $state);
                }
                $intent = \Stripe\PaymentIntent::retrieve($state['intent']);
                if ($intent->status === 'succeeded') {
                    // Start the guard when success is observed, not when the charge was
                    // created (3DS confirmation can complete much later).
                    if (empty($state['completed_at'])) {
                        $state['completed_at'] = time();
                        $this->saveSinglePaymentState($statePath, $state);
                    }
                    if (time() - $state['completed_at'] < 300) {
                        if ($state['fingerprint'] === $fingerprint) {
                            return $intent;
                        }
                        $getIntentErrorResponse = 'Your previous payment was processed. Please wait five minutes before making another payment. No new charge was made.';
                        throw new \RuntimeException('Stripe payment completion cooldown');
                    }
                } elseif ($intent->status !== 'canceled') {
                    if (in_array($intent->status, ['processing', 'requires_capture'], true)) {
                        $getIntentErrorResponse = 'A payment request is already being processed. Please wait and try again.';
                        throw new \RuntimeException('Stripe payment still processing');
                    }
                    if (time() - $state['created'] < 900) {
                        if ($state['fingerprint'] === $fingerprint) {
                            return $intent;
                        }
                        $getIntentErrorResponse = 'Another payment is pending. Please finish it or wait 15 minutes before starting a different payment.';
                        throw new \RuntimeException('Different Stripe payment already pending');
                    }
                    // Expiry alone is not sufficient: invalidate the old client secret first.
                    // If cancellation races with confirmation or fails, do not create another.
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

                _error_log("StripeYPT::setUpPayment charge " . json_encode($charge));
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
                    $setUpSubscriptionErrorResponse = 'Could not create a Stripe costumer';
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
        _error_log("setUpSubscription: result " . json_encode($Subscription));
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
        if (!is_object($payload) || empty($payload->data->object->customer)) {
            _error_log("processSubscriptionIPN: ERROR", AVideoLog::$ERROR);
            return false;
        }
        $pluginS = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
        //$plan = Subscription::getFromStripeCostumerId($payload->data->object->customer);
        $metadata = self::getMetadataOrFromSubscription($payload);
        if (empty($metadata)) {
            _error_log("processSubscriptionIPN: ERROR Metadata not found " . json_encode($payload), AVideoLog::$ERROR);
            return false;
        }
        $payment_amount = self::getAmountPaidFromPayload($payload);
        if (!AVideoPlugin::isEnabledByName('Subscription') || !class_exists('Subscription')) {
            if (!AVideoPlugin::isEnabledByName('DiskUploadQuota') || !class_exists('DiskUploadQuota')) {
                _error_log("processSubscriptionIPN: ERROR Subscription not found " . json_encode(array($payload, $metadata)), AVideoLog::$WARNING);
                if (!empty($metadata['users_id'])) {
                    $pluginS->addBalance($metadata['users_id'], $payment_amount, "Stripe recurrent (no plan detected): " . $payload->data->object->description, json_encode($payload));
                }
            } else {
                $ipnFIle = "{$global['systemRootPath']}plugin/DiskUploadQuota/Subscription/Stripe/ipn.php";
                require_once $ipnFIle;
                exit;
            }
        } else {
            $plan = Subscription::getOrCreateStripeSubscription($metadata['users_id'], $metadata['plans_id'], $payload->data->object->customer);
            if (!empty($plan)) {
                $users_id = @$plan['users_id'];
                $plans_id = @$plan['subscriptions_plans_id'];
                if (!empty($users_id)) {
                    $pluginS->addBalance($users_id, $payment_amount, "Stripe recurrent: " . $payload->data->object->description, json_encode($payload));
                    if (!empty($plans_id)) {
                        $obj = Subscription::renew($users_id, $plans_id);
                        if (!empty($obj->error)) {
                            _error_log("processSubscriptionIPN: ERROR Subscription::renew " . json_encode($obj), AVideoLog::$ERROR);
                        }
                    } else {
                        _error_log("processSubscriptionIPN: ERROR plans_id not found", AVideoLog::$ERROR);
                    }
                } else {
                    _error_log("processSubscriptionIPN: ERROR User not found", AVideoLog::$ERROR);
                }
            } else {
                _error_log("processSubscriptionIPN: ERROR Plan not found", AVideoLog::$ERROR);
            }
        }
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
                return array("users_id" => $value->users_id, "plans_id" => $value->plans_id);
            }
        }
        if (!empty($payload->users_id)) {
            return array("users_id" => $payload->users_id, "plans_id" => @$payload->plans_id);
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

    function processSinglePaymentIPN($payload)
    {
        if (!is_object($payload)) {
            _error_log("Stripe processSinglePaymentIPN  empty payload");
            return false;
        }
        $pluginS = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
        $payment_amount = StripeYPT::addDot($payload->data->object->amount);
        $users_id = $payload->data->object->metadata->users_id;
        if (!empty($users_id)) {
            _error_log("Stripe processSinglePaymentIPN users_id={$users_id} payment_amount={$payment_amount} {$payload->data->object->description}");
            $pluginS->addBalance($users_id, $payment_amount, "Stripe single payment: " . $payload->data->object->description, json_encode($payload));
        } else {
            _error_log("Stripe processSinglePaymentIPN users_id is empty payment_amount={$payment_amount}");
        }
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
