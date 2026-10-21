<?php
namespace StripeSubscriptionMetadataTests;

use PHPUnit\Framework\TestCase;
use RuntimeException;

// Load production payment code with isolated provider/wallet doubles, never the site DB.
$metadataTestSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/StripeYPT.php');
$metadataTestSource = substr($metadataTestSource, strpos($metadataTestSource, 'class StripeYPT extends'));
$metadataTestSource = str_replace('\\Stripe\\', '\\StripeSubscriptionMetadataTests\\Gateway\\', $metadataTestSource);
$metadataTestSource = str_replace("class_exists('Subscription')", 'class_exists(Subscription::class)', $metadataTestSource);
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use Exception; ' . $metadataTestSource);

class PluginAbstract {}
class AVideoLog { public static $ERROR = 1, $WARNING = 2; }
function _error_log($message, $level = null) {}
function _json_decode($value) { return is_string($value) ? json_decode($value) : $value; }
class AVideoPlugin {
    public static $wallet;
    public static function getDataObject($name) { return (object) ['Restrictedkey' => 'test_stub']; }
    public static function loadPluginIfEnabled($name) { return self::$wallet; }
    public static function isEnabledByName($name) { return $name === 'Subscription'; }
}
class Subscription {
    public static $renewals = 0, $plans = [];
    public static function getOrCreateStripeSubscription($user, $plan, $customer) {
        if (!$plan) { throw new RuntimeException('Null plan must not reach persistence'); }
        self::$plans[] = [$user, $plan];
        return ['users_id' => $user, 'subscriptions_plans_id' => $plan];
    }
    public static function renew($user, $plan) { ++self::$renewals; return (object) ['error' => false]; }
}
class Wallet {
    public $seen = [], $credits = [], $transactions = 0;
    public function processPaymentOnce($provider, $ids, $user, $callback) {
        if (array_intersect($ids, $this->seen)) { return true; }
        ++$this->transactions;
        $result = $callback();
        $this->seen = array_merge($this->seen, $ids);
        return $result;
    }
    public function addBalance($user, $amount, $description, $json) { $this->credits[] = [$user, $amount]; }
    public function runPaymentStep($callback, $description) { return $callback(); }
}

class StripeSubscriptionMetadataTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['global'] = ['webSiteRootURL' => 'https://example.test/'];
        AVideoPlugin::$wallet = new Wallet();
        Subscription::$renewals = 0;
        Subscription::$plans = [];
        Gateway\Invoice::$fail = false;
        Gateway\Invoice::$subscription = 'sub_test';
        Gateway\Invoice::$reads = [];
        Gateway\Charge::$reads = [];
        Gateway\Charge::$deny = false;
    }
    private function charge() {
        return json_decode('{"id":"evt_charge","type":"charge.succeeded","data":{"object":{"id":"ch_test","customer":"cus_test","invoice":"in_test","payment_intent":"pi_test","amount_captured":249,"metadata":{}}}}');
    }
    private function invoice() {
        return json_decode('{"id":"evt_invoice","type":"invoice.payment_succeeded","data":{"object":{"id":"in_test","customer":"cus_test","subscription":"sub_test","charge":"ch_test","payment_intent":"pi_test","amount_paid":249,"metadata":{}}}}');
    }
    public function testChargeFirstResolvesInvoicePlanAndInvoiceDoesNotRenewAgain(): void {
        $stripe = new StripeYPT();
        $this->assertTrue($stripe->processSubscriptionIPN($this->charge()));
        $this->assertTrue($stripe->processSubscriptionIPN($this->invoice()));
        $this->assertSame(['in_test'], Gateway\Invoice::$reads);
        $this->assertSame([[7, 3]], Subscription::$plans);
        $this->assertSame(1, Subscription::$renewals);
        $this->assertSame([[7, 2.49]], AVideoPlugin::$wallet->credits);
    }
    public function testInvoiceFirstAndExpandedInvoiceReferenceStillCreditOnce(): void {
        $stripe = new StripeYPT();
        $charge = $this->charge();
        $charge->data->object->invoice = (object) ['id' => 'in_test'];
        $this->assertTrue($stripe->processSubscriptionIPN($this->invoice()));
        $this->assertTrue($stripe->processSubscriptionIPN($charge));
        $this->assertSame(1, Subscription::$renewals);
        $this->assertCount(1, AVideoPlugin::$wallet->credits);
    }
    public function testInvoiceLookupFailureDoesNotCreditOrMarkPayment(): void {
        Gateway\Invoice::$fail = true;
        try { (new StripeYPT())->processSubscriptionIPN($this->charge()); $this->fail('Expected retryable failure'); }
        catch (RuntimeException $error) { $this->assertSame('Stripe unavailable', $error->getMessage()); }
        $this->assertSame([], AVideoPlugin::$wallet->credits);
        $this->assertSame([], AVideoPlugin::$wallet->seen);
        $this->assertSame(0, AVideoPlugin::$wallet->transactions);
    }
    public function testSnapshotWithoutInvoiceUsesCurrentChargeAtPinnedApiVersion(): void {
        $charge = $this->charge();
        unset($charge->data->object->invoice);
        $this->assertTrue((new StripeYPT())->processSubscriptionIPN($charge));
        $this->assertSame(['ch_test'], Gateway\Charge::$reads);
        $this->assertSame(['in_test'], Gateway\Invoice::$reads);
        $this->assertSame([[7, 3]], Subscription::$plans);
        $this->assertSame(1, Subscription::$renewals);
    }
    public function testRestrictedKeyCanFulfillViaInvoiceAndAcknowledgeChargeRetry(): void {
        Gateway\Charge::$deny = true;
        $charge = $this->charge();
        unset($charge->data->object->invoice);
        $stripe = new StripeYPT();
        try { $stripe->processSubscriptionIPN($charge); $this->fail('Unresolved plan must retry'); }
        catch (RuntimeException $error) { $this->assertStringContainsString('plan metadata', $error->getMessage()); }
        $this->assertSame([], AVideoPlugin::$wallet->credits);
        $this->assertTrue($stripe->processSubscriptionIPN($this->invoice()));
        $this->assertTrue($stripe->processSubscriptionIPN($charge));
        $this->assertSame(1, Subscription::$renewals);
        $this->assertCount(1, AVideoPlugin::$wallet->credits);
    }
    public function testUserOnlyCustomerMetadataNeverCreatesANullPlan(): void {
        Gateway\Invoice::$subscription = null;
        try { (new StripeYPT())->processSubscriptionIPN($this->charge()); $this->fail('Expected unresolved plan'); }
        catch (RuntimeException $error) { $this->assertStringContainsString('plan metadata', $error->getMessage()); }
        $this->assertSame([], Subscription::$plans);
        $this->assertSame([], AVideoPlugin::$wallet->credits);
        $this->assertSame([], AVideoPlugin::$wallet->seen);
    }
    public function testCompleteEventMetadataNeedsNoInvoiceLookup(): void {
        $charge = $this->charge();
        $charge->data->object->metadata = (object) ['users_id' => 7, 'plans_id' => 3];
        $this->assertTrue((new StripeYPT())->processSubscriptionIPN($charge));
        $this->assertSame([], Gateway\Invoice::$reads);
        $this->assertSame([[7, 3]], Subscription::$plans);
    }
    public function testUnrelatedCustomerlessChargeIsAcknowledgedWithoutCredit(): void {
        $charge = $this->charge();
        $charge->data->object->customer = null;
        $this->assertFalse((new StripeYPT())->processSubscriptionIPN($charge));
        $this->assertSame([], AVideoPlugin::$wallet->credits);
    }
}

namespace StripeSubscriptionMetadataTests\Gateway;
class Stripe {
    public static function setApiVersion($version) {}
    public static function setApiKey($key) {}
}
class WebhookEndpoint {
    public static function all($params) { return (object) ['data' => [(object) ['url' => 'https://example.test/plugin/StripeYPT/ipn.php']]]; }
}
class Invoice {
    public static $fail = false, $subscription = 'sub_test', $reads = [];
    public static function retrieve($id) {
        self::$reads[] = $id;
        if (self::$fail) { throw new \RuntimeException('Stripe unavailable'); }
        return (object) ['subscription' => self::$subscription];
    }
}
class Subscription {
    public static function retrieve($id) { return (object) ['metadata' => (object) ['users_id' => 7, 'plans_id' => 3]]; }
}
class Charge {
    public static $reads = [], $deny = false;
    public static function retrieve($id) {
        self::$reads[] = $id;
        if (self::$deny) { throw new Exception\PermissionException('No Charges: Read'); }
        return (object) ['invoice' => 'in_test'];
    }
}
class Customer {
    public static function retrieve($id) { return (object) ['metadata' => (object) ['users_id' => 7]]; }
}

namespace StripeSubscriptionMetadataTests\Gateway\Exception;
class PermissionException extends \Exception {}
