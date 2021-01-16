<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once ($global['systemRootPath'] . 'plugin/RazorPayYPT/razorpay-php/Razorpay.php');

use Razorpay\Api\Api;
class RazorPayYPT extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$MONETIZATION,
            PluginTags::$FREE,
        );
    }
    public function getDescription() {
        global $global;
        $obj = $this->getDataObject();
        $str = "Go to Razorpay dashboard Site <a href='https://dashboard.razorpay.com/#/app/keys'>here</a>  (you must have Razorpay account, of course)<br>";
        $str .= "For Subscriptions, you MUST go to your <a href='https://dashboard.razorpay.com/#/app/webhooks'>Webhooks dashboard</a> check all checkboxes and setup this: <br>Webhook URL: ({$global['webSiteRootURL']}plugin/RazorPayYPT/ipn.php) <br>Secret: {$obj->webhookSecret} <br>
            Check more details here <a href='https://razorpay.com/docs/subscriptions/api/webhooks/#setup-webhooks'>here</a> <br>";
        
        $p = AVideoPlugin::loadPlugin("Subscription");
        if(!empty($p) && version_compare($p->getPluginVersion(), "3.6")==-1){
            $str .= " <br><strong class='alert alert-danger'>The <a href='#plugin27570156-dc62-46e3-ace9-86c6e8f9c84b'>Subscription</a> with RazorPay Requires <a href='#plugin27570156-dc62-46e3-ace9-86c6e8f9c84b'>Subscription plugin</a> version 3.6 or greater.</strong>";
        }
        
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
        global $global;
        $obj = new stdClass();
        $obj->api_key = "rzp_test_VJCqZwCeMt4CMP";
        $obj->api_secret = "DVNniPwmDzRiYniMzJZKpqCf";
        $obj->webhookSecret = md5($global['systemRootPath'].$global['salt']);
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

    private function getOrCreatePlanId($plans_id) {
        $plan = new SubscriptionPlansTable($plans_id);
        $obj = AVideoPlugin::getObjectData('YPTWallet');
        $api = $this->start();
        try {
            $plans = $api->plan->all();
        } catch (Exception $exc) {
            _error_log($exc->getTraceAsString());
            die("Looks like you didn't opt for Subscriptions in our dashboard. To enable subscriptions, please go to <a href='https://dashboard.razorpay.com/#/app/subscriptions'>Dashboard > Subscriptions</a> and click on Get Started button and try again.");
        }

        $plans = $api->plan->all();
        $id = "";
        if (!empty($plans->items)) {
            foreach ($plans->items as $value) {
                if ($value->notes->plans_id == $plans_id) {
                    return $value->id;
                }
            }
        }
        if (empty($id)) {
            $plans = $api->plan->create(array(
                'period' => 'daily',
                'interval' => $plan->getHow_many_days(),
                'item' => array(
                    'name' => $plan->getName(),
                    'description' => $plan->getDescription(),
                    'amount' => $plan->getPrice() * 100,
                    'currency' => $obj->currency
                ),
                'notes' => array(
                    'plans_id' => $plans_id,
                    'plan' => $plan->getName()
                )
                    )
            );
        }
        if (!empty($plans->id)) {
            return $plans->id;
        }
        return false;
    }

    public function setUpSubscription($plans_id) {
        if (!User::isLogged()) {
            _error_log("setUpSubscription: User not logged");
            return false;
        }
        $plan = new SubscriptionPlansTable($plans_id);
        $obj = AVideoPlugin::getObjectData('YPTWallet');

        if (empty($plan)) {
            _error_log("setUpSubscription: Plan not found");
            return false;
        }


        $razorpay_plan_id = $this->getOrCreatePlanId($plans_id);

        $plan = new SubscriptionPlansTable($plans_id);

        $options = array(
            'plan_id' => $razorpay_plan_id,
            'customer_notify' => 1,
            'total_count' => intval(3600/$plan->getHow_many_days()),
            'notes' => array(
                'user' => User::getUserName(),
                'users_id' => User::getId(),
                'plans_id' => $plans_id,
                'plan' => $plan->getName()
            )
        );

        $trialDays = $plan->getHow_many_days_trial();

        if (!empty($trialDays)) {
            $options['start_at'] = strtotime("+ $trialDays days");
        }
        $api = $this->start();
        $subscription = $api->subscription->create($options);
        return $subscription;
    }

    function cancelSubscriptions($subscription_id) {
        if (!User::isAdmin()) {
            _error_log("cancelSubscriptions: User not admin");
            return false;
        }
        global $global;
        $api = $this->start();
        $options = array(
            'cancel_at_cycle_end' => 1
        );
        return $api->subscription->fetch($subscription_id)->cancel($options);
    }

    function getAllSubscriptions($status = 'active') {
        if (!User::isAdmin()) {
            _error_log("getAllSubscriptions: User not admin");
            return false;
        }
        global $global;
        $api = $this->start();
        return $api->subscription->all();
    }

    function updateBillingPlan($plans_id, $total = '1.00', $currency = "USD", $interval = 1, $name = 'Base Agreement') {
        global $global;
        if (empty($plan_id)) {
            return false;
        }

        $plan = new SubscriptionPlansTable($plans_id);

        if (empty($plan)) {
            _error_log("updateBillingPlan: Plan not found");
            return false;
        }
        $api = $this->start();
        return $api->plan->create(array(
                    'period' => 'daily',
                    'interval' => $interval,
                    'item' => array(
                        'name' => $name,
                        'amount' => $total * 100,
                        'currency' => $currency
                    ),
                    'notes' => array(
                        'plans_id' => $plans_id,
                        'plan' => $plan->getName()
                    )
                        )
        );
    }

}
