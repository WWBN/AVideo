<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/StripeYPT/init.php';

class StripeYPT extends PluginAbstract {

    public function getDescription() {
        return "Stripe module for several purposes<br>
            Go to Stripe dashboard Site here https://dashboard.stripe.com/test/apikeys (you must have Stripe account, of course)";
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
        $obj->disableSandbox = false;
        return $obj;
    }

    private function start() {
        global $global;
        $obj = $this->getDataObject();
        $notify_url = "{$global['webSiteRootURL']}plugin/StripeYPT/ipn.php";

        \Stripe\Stripe::setApiKey($obj->Restrictedkey);
        $this->setWebhook();
    }
    
    private function setWebhook(){
        global $global;
        $webhooks = \Stripe\WebhookEndpoint::all(["limit" => 20]);
        $notify_url = "{$global['webSiteRootURL']}plugin/StripeYPT/ipn.php";
        if(!empty($webhooks->data)){
            foreach ($webhooks->data as $value) {
                if($value->url === $notify_url){
                    $notify_url = "";
                    break;
                }
            }
        }
        
        if(!empty($notify_url)){
            \Stripe\WebhookEndpoint::create([
                "url" => $notify_url,
                "enabled_events" => ["*"]
            ]);
        }
        
    }

    public function setUpPayment($total = '1.00', $currency = "USD", $description = "") {
        global $global;
        $this->start();
        $total = number_format(floatval($total), 2, "", "");
        if (!empty($_POST['stripeToken'])) {
            $token = $_POST['stripeToken'];
            $charge = \Stripe\Charge::create([
                        'amount' => $total,
                        'currency' => $currency,
                        'description' => $description,
                        'source' => $token,
            ]);
            return $charge;
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

    private static function addDot($value) {
        $val = substr($payment->amount, 0, -2);
        $cents = substr($payment->amount, -2);
        return floatval("$val.$cents");
    }

    private static function removeDot($value) {
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

        if (!is_object($payment)) {
            return false;
        }

        if (empty($payment->paid)) {
            return false;
        }

        if (strcasecmp($currency, self::getCurrencyFromPayment($payment)) !== 0) {
            return false;
        }

        if ($value > self::getAmountFromPayment($payment)) {
            return false;
        }
        return true;
    }

    public function createCostumer($users_id, $stripeToken) {
        global $global;

        $user = new User($users_id);

        if (!empty($user)) {
            $this->start();
            return \Stripe\Customer::create([
                        "description" => "Customer [$users_id] " . $user->getNameIdentification(),
                        "source" => $stripeToken // obtained with Stripe.js
            ]);
        }
        return false;
    }

    public function getCostumerId($users_id, $stripeToken) {
        $costumer = $this->createCostumer($users_id, $stripeToken);

        if (!empty($costumer)) {
            return $costumer->id;
        }

        return false;
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

    static function updateBillingPlan($plans_id, $total = '1.00', $currency = "USD", $interval = 1, $name = 'Base Agreement') {
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
        
        $Subscription = \Stripe\Subscription::create([
                    "customer" => $sub['stripe_costumer_id'],
                    "items" => [
                        [
                            "plan" => $stripe_plan_id,
                        ]
                    ],
                    "metadata" => $metadata
        ]);
        error_log("setUpSubscription: result ".  json_encode($Subscription));
        return $Subscription;
    }

}
