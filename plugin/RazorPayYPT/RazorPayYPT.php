<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';


class RazorPayYPT extends PluginAbstract {

    public function getDescription() {
        $str = "Razorpay module for several purposes<br>
            Go to Razorpay dashboard Site <a href='https://dashboard.razorpay.com/#/app/keys'>here</a>  (you must have Razorpay account, of course)<br>";
        $str .= "Before you can verify signatures, you need to retrieve your endpoint’s secret from your Dashboard’s"
                . " <br><a href='https://dashboard.razorpay.com/#/app/webhooks'>Webhooks settings</a>."
                . " <br>Select an endpoint that you want to obtain the secret for, then select the Click to reveal button."
                . " <br><strong>The SigningSecret will be available after your first purchase attempt, Webhook will be created automatically.</strong>";
        return $str;
    }

    public function getName() {
        return "RazorPayYPT";
    }

    public function getUUID() {
        return "razor09-c0b6-4264-85cb-47ae076d949f";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->api_key = "rzp_test_VJCqZwCeMt4CMP";
        $obj->api_secret = "DVNniPwmDzRiYniMzJZKpqCf";
        //Before you can verify signatures, you need to retrieve your endpoint’s secret from your Dashboard’s Webhooks settings. Select an endpoint that you want to obtain the secret for, then select the Click to reveal button.
        //$obj->SigningSecret = "whsec_54gqoVeSuoeXEiNPcFhMN0jkBZY0JJG3";
        //$obj->disableSandbox = false;
        return $obj;
    }

    function start() {
        global $global;
        $obj = $this->getDataObject();
        return new Api($obj->api_key, $obj->api_secret);
    }

    public function setUpPayment($total = '1.00', $currency = "USD", $description = "") {
        global $global;
        $api = $this->start();
        $total = number_format(floatval($total), 2, "", "");
        _error_log("RazorpayYPT::setUpPayment $total , $currency, $description");
        if (!empty($_POST['razorpayToken'])) {
            $token = $_POST['razorpayToken'];
            try {
                $charge = \Razorpay\Charge::create([
                            'amount' => $total,
                            'currency' => $currency,
                            'description' => $description,
                            'source' => $token,
                ]);
                _error_log("RazorpayYPT::setUpPayment charge ".  json_encode($charge));
                return $charge;
            } catch (Exception $exc) {
                _error_log("RazorpayYPT::setUpPayment error ");
                _error_log($exc->getTraceAsString());
            }
        }else{
            _error_log("RazorpayYPT::setUpPayment stipeToken empty");
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
        _error_log("isPaymentOk: ".  json_encode($payment));
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

    public function createCostumer($users_id, $razorpayToken) {
        global $global;

        $user = new User($users_id);

        if (!empty($user)) {
            try {
                $this->start();
                return \Razorpay\Customer::create([
                            "description" => "Customer [$users_id] " . $user->getNameIdentification() . "(" . $user->getEmail() . ")",
                            "source" => $razorpayToken // obtained with Razorpay.js
                ]);
            } catch (Exception $exc) {
                _error_log($exc->getTraceAsString());
            }
        }
        return false;
    }

    public function getCostumerId($users_id, $razorpayToken) {

        $costumer = $this->createCostumer($users_id, $razorpayToken);

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
        _error_log("RazorpayYPT::isCostumerValid $id");
        try {
            $c = \Razorpay\Customer::retrieve($id);
            if ($c) {
                _error_log("RazorpayYPT::isCostumerValid IS VALID: " . json_encode($c));
                return true;
            } else {
                _error_log("RazorpayYPT::isCostumerValid NOT FOUND");
                return false;
            }
        } catch (Exception $exc) {
            _error_log("RazorpayYPT::isCostumerValid ERROR");
            return false;
        }
    }

    private function createBillingPlan($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement') {
        global $global;
        $this->start();
        return \Razorpay\Plan::create([
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
        return \Razorpay\Plan::update($plans_id, [
                    'currency' => $currency,
                    'interval_count' => $interval,
                    "product" => [
                        "name" => $name
                    ],
                    'nickname' => $name,
                    'amount' => self::removeDot($total),
        ]);
    }

    static function getSubscriptions($razorpay_costumer_id, $plans_id) {
        if (!User::isLogged()) {
            _error_log("getSubscription: User not logged");
            return false;
        }
        if (empty($razorpay_costumer_id)) {
            _error_log("costumer ID is empty");
            return false;
        }
        global $global;
        $users_id = User::getId();
        $obj = AVideoPlugin::getObjectData('RazorpayYPT');
        \Razorpay\Razorpay::setApiKey($obj->Restrictedkey);
        $costumer = \Razorpay\Customer::retrieve($razorpay_costumer_id);
        foreach ($costumer->subscriptions->data as $value) {
            $subscription = \Razorpay\Subscription::retrieve($value->id);
            if ($subscription->metadata->users_id == $users_id && $subscription->metadata->plans_id == $plans_id) {
                _error_log("RazorpayYPT::getSubscriptions $razorpay_costumer_id, $plans_id " . json_encode($subscription));
                return $subscription;
            }
        }
        _error_log("RazorpayYPT::getSubscriptions ERROR $razorpay_costumer_id, $plans_id " . json_encode($costumer));
        return false;
    }

    public function setUpSubscription($plans_id, $razorpayToken) {
        if (!User::isLogged()) {
            _error_log("setUpSubscription: User not logged");
            return false;
        }
        $subs = new SubscriptionPlansTable($plans_id);
        $obj = AVideoPlugin::getObjectData('YPTWallet');

        if (empty($subs)) {
            _error_log("setUpSubscription: Plan not found");
            return false;
        }
        // check costumer
        $sub = Subscription::getOrCreateRazorpaySubscription(User::getId(), $plans_id);

        if (!self::isCostumerValid($sub['razorpay_costumer_id'])) {
            $sub['razorpay_costumer_id'] = "";
        }

        if (empty($sub['razorpay_costumer_id'])) {
            $sub['razorpay_costumer_id'] = $this->getCostumerId(User::getId(), $razorpayToken);
            if (empty($sub['razorpay_costumer_id'])) {
                _error_log("setUpSubscription: Could not create a Razorpay costumer");
                return false;
            }
            Subscription::getOrCreateRazorpaySubscription(User::getId(), $plans_id, $sub['razorpay_costumer_id']);
        }

        // check plan
        $razorpay_plan_id = $subs->getRazorpay_plan_id();
        if (empty($razorpay_plan_id)) {
            $interval = $subs->getHow_many_days();
            $price = $subs->getPrice();
            $paymentName = $subs->getName();
            if (empty($paymentName)) {
                $paymentName = "Recurrent Payment";
            }

            $plan = $this->createBillingPlan($price, $obj->currency, "day", $interval, $paymentName);
            if (empty($plan)) {
                _error_log("setUpSubscription: could not create razorpay plan");
                return false;
            }
            $razorpay_plan_id = $plan->id;
        }

        _error_log("setUpSubscription: will start");
        $this->start();

        $metadata = new stdClass();
        $metadata->users_id = User::getId();
        $metadata->plans_id = $plans_id;
        $metadata->razorpay_costumer_id = $sub['razorpay_costumer_id'];

        $parameters = [
            "customer" => $sub['razorpay_costumer_id'],
            "items" => [
                [
                    "plan" => $razorpay_plan_id,
                ]
            ],
            "metadata" => [
                'users_id' => User::getId(),
                'plans_id' => $plans_id,
                'razorpay_costumer_id' => $sub['razorpay_costumer_id']
            ]
        ];

        $trialDays = $subs->getHow_many_days_trial();
        if (!empty($trialDays)) {
            $trial = strtotime("+{$trialDays} days");
            $parameters['trial_end'] = $trial;
        }

        $Subscription = \Razorpay\Subscription::create($parameters);
        _error_log("setUpSubscription: result " . json_encode($Subscription));
        return $Subscription;
    }

    function processSubscriptionIPN($payload) {
        if (!is_object($payload) || empty($payload->data->object->customer)) {
            return false;
        }
        $pluginS = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
        $plan = Subscription::getFromRazorpayCostumerId($payload->data->object->customer);
        $payment_amount = RazorpayYPT::addDot($payload->data->object->amount);
        $users_id = @$plan['users_id'];
        $plans_id = @$plan['subscriptions_plans_id'];
        if (!empty($users_id)) {
            $pluginS->addBalance($users_id, $payment_amount, "Razorpay recurrent: " . $payload->data->object->description, json_encode($payload));
            if (!empty($plans_id)) {
                Subscription::renew($users_id, $plans_id);
            }
        }
    }

    function getAllSubscriptions($status = 'active') {
        if (!User::isAdmin()) {
            _error_log("getAllSubscriptions: User not admin");
            return false;
        }
        global $global;
        $this->start();
        return \Razorpay\Subscription::all(['limit' => 1000, 'status' => $status]);
    }

    function cancelSubscriptions($id) {
        if (!User::isAdmin()) {
            _error_log("cancelSubscriptions: User not admin");
            return false;
        }
        global $global;
        try {
            $this->start();
            $sub = \Razorpay\Subscription::retrieve($id);
            $sub->cancel();
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/RazorpayYPT/pluginMenu.html';
        //return file_get_contents($filename);
    }

}
