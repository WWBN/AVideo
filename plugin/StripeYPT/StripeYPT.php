<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/StripeYPT/init.php';

class StripeYPT extends PluginAbstract {

    private $Publishablekey, $Restrictedkey, $SigningSecret;

    public function getDescription() {
        $str = "Stripe module for several purposes<br>
            Go to Stripe dashboard Site <a href='https://dashboard.stripe.com/apikeys'>here</a>  (you must have Stripe account, of course)<br>";
        $str .= "Before you can verify signatures, you need to retrieve your endpoint’s secret from your Dashboard’s"
                . " <br><a href='https://dashboard.stripe.com/account/webhooks'>Webhooks settings</a>."
                . " <br>Select an endpoint that you want to obtain the secret for, then select the Click to reveal button."
                . " <br><strong>The SigningSecret will be available after your first purchase attempt, Webhook will be created automatically.</strong>";
        return $str;
    }

    public function getName() {
        return "StripeYPT";
    }

    public function getUUID() {
        return "stripe09-c0b6-4264-85cb-47ae076d949f";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->Publishablekey = "pk_test_aQT12wEjRLKhXgk77TX4ftfa";
        $obj->Restrictedkey = "rk_test_kjyL5JaoAQwyiptuRlSzYJMZ00kRqXkLux";
        //Before you can verify signatures, you need to retrieve your endpoint’s secret from your Dashboard’s Webhooks settings. Select an endpoint that you want to obtain the secret for, then select the Click to reveal button.
        $obj->SigningSecret = "whsec_54gqoVeSuoeXEiNPcFhMN0jkBZY0JJG3";
        //$obj->disableSandbox = false;
        return $obj;
    }

    public function getDataObject() {

        if (!empty($this->Publishablekey)) {
            $obj = new stdClass();
            $obj->Publishablekey = $this->Publishablekey;
            $obj->Restrictedkey = $this->Restrictedkey;
            $obj->SigningSecret = $this->SigningSecret;
            return $obj;
        }

        return parent::getDataObject();
    }

    public function setTempDataObject($Publishablekey, $Restrictedkey, $SigningSecret) {
        $this->Publishablekey = $Publishablekey;
        $this->Restrictedkey = $Restrictedkey;
        $this->SigningSecret = $SigningSecret;
    }

    function start() {
        global $global;
        $obj = $this->getDataObject();
        \Stripe\Stripe::setApiKey($obj->Restrictedkey);
        $this->getWebhook();
    }

    function getWebhook() {
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

    public function setUpPayment($total = '1.00', $currency = "USD", $description = "") {
        global $global;
        $this->start();
        $total = number_format(floatval($total), 2, "", "");
        error_log("StripeYPT::setUpPayment $total , $currency, $description");
        if (!empty($_POST['stripeToken'])) {
            $token = $_POST['stripeToken'];
            try {
                $charge = \Stripe\Charge::create([
                            'amount' => $total,
                            'currency' => $currency,
                            'description' => $description,
                            'source' => $token,
                ]);
                error_log("StripeYPT::setUpPayment charge ".  json_encode($charge));
                return $charge;
            } catch (Exception $exc) {
                error_log("StripeYPT::setUpPayment error ");
                error_log($exc->getTraceAsString());
            }
        }else{
            error_log("StripeYPT::setUpPayment stipeToken empty");
        }
        return false;
    }

    static function getAmountFromPayment($payment) {
        if (!is_object($payment)) {
            return false;
        }
        if (empty($payment->amount)) {
            return 0;
        }
        return self::addDot($payment->amount);
    }

    static function addDot($value) {
        $val = substr($value, 0, -2);
        $cents = substr($value, -2);
        return floatval("$val.$cents");
    }

    static function removeDot($value) {
        $value = floatval($value);
        return number_format($value, 2, "", "");
    }

    static function getCurrencyFromPayment($payment) {
        if (!is_object($payment)) {
            return false;
        }
        return $payment->currency;
    }

    static function isPaymentOk($payment, $value, $currency) {
        error_log("isPaymentOk: ".  json_encode($payment));
        error_log("isPaymentOk: $value, $currency");
        if (!is_object($payment)) {
            error_log("isPaymentOk: NOT object");
            return false;
        }

        if (empty($payment->paid)) {
            error_log("isPaymentOk: NOT paid");
            return false;
        }

        if (strcasecmp($currency, self::getCurrencyFromPayment($payment)) !== 0) {
            error_log("isPaymentOk: NOT same currency");
            return false;
        }

        if ($value > self::getAmountFromPayment($payment)) {
            error_log("isPaymentOk: NOT same amount");
            return false;
        }
        return true;
    }

    public function createCostumer($users_id, $stripeToken) {
        global $global;

        $user = new User($users_id);

        if (!empty($user)) {
            try {
                $this->start();
                return \Stripe\Customer::create([
                            "description" => "Customer [$users_id] " . $user->getNameIdentification() . "(" . $user->getEmail() . ")",
                            "source" => $stripeToken // obtained with Stripe.js
                ]);
            } catch (Exception $exc) {
                error_log($exc->getTraceAsString());
            }
        }
        return false;
    }

    public function getCostumerId($users_id, $stripeToken) {

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

    public static function isCostumerValid($id) {
        if ($id == 'canceled') {
            return false;
        }
        error_log("StripeYPT::isCostumerValid $id");
        try {
            $c = \Stripe\Customer::retrieve($id);
            if ($c) {
                error_log("StripeYPT::isCostumerValid IS VALID: " . json_encode($c));
                return true;
            } else {
                error_log("StripeYPT::isCostumerValid NOT FOUND");
                return false;
            }
        } catch (Exception $exc) {
            error_log("StripeYPT::isCostumerValid ERROR");
            return false;
        }
    }

    private function createBillingPlan($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement') {
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
        ]);
    }

    function updateBillingPlan($plans_id, $total = '1.00', $currency = "USD", $interval = 1, $name = 'Base Agreement') {
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

    static function getSubscriptions($stripe_costumer_id, $plans_id) {
        if (!User::isLogged()) {
            error_log("getSubscription: User not logged");
            return false;
        }
        if (empty($stripe_costumer_id)) {
            error_log("costumer ID is empty");
            return false;
        }
        global $global;
        $users_id = User::getId();
        $obj = YouPHPTubePlugin::getObjectData('StripeYPT');
        \Stripe\Stripe::setApiKey($obj->Restrictedkey);
        $costumer = \Stripe\Customer::retrieve($stripe_costumer_id);
        foreach ($costumer->subscriptions->data as $value) {
            $subscription = \Stripe\Subscription::retrieve($value->id);
            if ($subscription->metadata->users_id == $users_id && $subscription->metadata->plans_id == $plans_id) {
                error_log("StripeYPT::getSubscriptions $stripe_costumer_id, $plans_id " . json_encode($subscription));
                return $subscription;
            }
        }
        error_log("StripeYPT::getSubscriptions ERROR $stripe_costumer_id, $plans_id " . json_encode($costumer));
        return false;
    }

    public function setUpSubscription($plans_id, $stripeToken) {
        if (!User::isLogged()) {
            error_log("setUpSubscription: User not logged");
            return false;
        }
        $subs = new SubscriptionPlansTable($plans_id);
        $obj = YouPHPTubePlugin::getObjectData('YPTWallet');

        if (empty($subs)) {
            error_log("setUpSubscription: Plan not found");
            return false;
        }
        // check costumer
        $sub = Subscription::getOrCreateStripeSubscription(User::getId(), $plans_id);

        if (!self::isCostumerValid($sub['stripe_costumer_id'])) {
            $sub['stripe_costumer_id'] = "";
        }

        if (empty($sub['stripe_costumer_id'])) {
            $sub['stripe_costumer_id'] = $this->getCostumerId(User::getId(), $stripeToken);
            if (empty($sub['stripe_costumer_id'])) {
                error_log("setUpSubscription: Could not create a Stripe costumer");
                return false;
            }
            Subscription::getOrCreateStripeSubscription(User::getId(), $plans_id, $sub['stripe_costumer_id']);
        }

        // check plan
        $stripe_plan_id = $subs->getStripe_plan_id();
        if (empty($stripe_plan_id)) {
            $interval = $subs->getHow_many_days();
            $price = $subs->getPrice();
            $paymentName = $subs->getName();
            if (empty($paymentName)) {
                $paymentName = "Recurrent Payment";
            }

            $plan = $this->createBillingPlan($price, $obj->currency, "day", $interval, $paymentName);
            if (empty($plan)) {
                error_log("setUpSubscription: could not create stripe plan");
                return false;
            }
            $stripe_plan_id = $plan->id;
        }

        error_log("setUpSubscription: will start");
        $this->start();

        $metadata = new stdClass();
        $metadata->users_id = User::getId();
        $metadata->plans_id = $plans_id;
        $metadata->stripe_costumer_id = $sub['stripe_costumer_id'];

        $parameters = [
            "customer" => $sub['stripe_costumer_id'],
            "items" => [
                [
                    "plan" => $stripe_plan_id,
                ]
            ],
            "metadata" => [
                'users_id' => User::getId(),
                'plans_id' => $plans_id,
                'stripe_costumer_id' => $sub['stripe_costumer_id']
            ]
        ];

        $trialDays = $subs->getHow_many_days_trial();
        if (!empty($trialDays)) {
            $trial = strtotime("+{$trialDays} days");
            $parameters['trial_end'] = $trial;
        }

        $Subscription = \Stripe\Subscription::create($parameters);
        error_log("setUpSubscription: result " . json_encode($Subscription));
        return $Subscription;
    }

    function processSubscriptionIPN($payload) {
        if (!is_object($payload) || empty($payload->data->object->customer)) {
            return false;
        }
        $pluginS = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
        $plan = Subscription::getFromStripeCostumerId($payload->data->object->customer);
        $payment_amount = StripeYPT::addDot($payload->data->object->amount);
        $users_id = @$plan['users_id'];
        $plans_id = @$plan['subscriptions_plans_id'];
        if (!empty($users_id)) {
            $pluginS->addBalance($users_id, $payment_amount, "Stripe recurrent: " . $payload->data->object->description, json_encode($payload));
            if (!empty($plans_id)) {
                Subscription::renew($users_id, $plans_id);
            }
        }
    }

    function getAllSubscriptions($status = 'active') {
        if (!User::isAdmin()) {
            error_log("getAllSubscriptions: User not admin");
            return false;
        }
        global $global;
        $this->start();
        return \Stripe\Subscription::all(['limit' => 1000, 'status' => $status]);
    }

    function cancelSubscriptions($id) {
        if (!User::isAdmin()) {
            error_log("cancelSubscriptions: User not admin");
            return false;
        }
        global $global;
        try {
            $this->start();
            $sub = \Stripe\Subscription::retrieve($id);
            $sub->cancel();
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/StripeYPT/pluginMenu.html';
        return file_get_contents($filename);
    }

}
