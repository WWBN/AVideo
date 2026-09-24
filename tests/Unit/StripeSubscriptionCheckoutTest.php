<?php

namespace Tests\StripeCheckout;

use PHPUnit\Framework\TestCase;
use stdClass;
use Exception;

// Load the actual checkout implementation with isolated DB/Stripe doubles.
// Bootstrapping the application here would connect to the configured live database.
$source = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/StripeYPT.php');
$source = substr($source, strpos($source, 'class StripeYPT extends'));
$source = str_replace('\\Stripe\\', '\\Tests\\StripeCheckout\\Gateway\\', $source);
eval('namespace ' . __NAMESPACE__ . '; use stdClass; use Exception; ' . $source);

class PluginAbstract {}
class AVideoLog { public static $ERROR = 1; }
function _error_log($message, $level = null) {}
class User
{
    public static function getId() { return 42; }
    public static function isLogged() { return true; }
    public static function isAdmin() { return false; }
    public function getNameIdentificationBd() { return 'Test'; }
    public function getEmail() { return 'test@example.invalid'; }
}
class AVideoPlugin
{
    public static function getObjectData($name) { return (object) ['currency' => 'USD']; }
    public static function getDataObject($name) { return (object) ['Restrictedkey' => 'test_key']; }
}
class SubscriptionPlansTable
{
    public static $trial = 0;
    public function getStripe_plan_id() { return ''; }
    public function getPrice() { return 6.99; }
    public function getName() { return 'Monthly'; }
    public function getHow_many_days() { return 30; }
    public function getHow_many_days_trial() { return self::$trial; }
}
class YPTWallet
{
    public static function getBillingInterval($days) { return (object) ['stripeFrequency' => 'month', 'stripeInterval' => 1]; }
}
class sqlDAL
{
    public static $locked = false;
    public static $unavailable = false;
    public static $releases = 0;
    public static $cache = [];
    public static function readSql($sql, $types, $values, $refresh)
    {
        if (strpos($sql, 'FROM subscriptions') !== false) {
            // Emulates the real per-request cache: writes never invalidate it.
            $key = $sql . json_encode($values);
            if ($refresh || !isset(self::$cache[$key])) {
                self::$cache[$key] = ['rows' => array_values(array_filter(Subscription::$rows, function ($row) use ($values) {
                    return $row['users_id'] == $values[0] && $row['subscriptions_plans_id'] == $values[1];
                }))];
            }
            return self::$cache[$key];
        }
        if (!$refresh) { throw new \RuntimeException('Lock query must bypass cache'); }
        if (strpos($sql, 'RELEASE_LOCK') !== false) {
            self::$locked = false;
            self::$releases++;
            return ['released' => 1];
        }
        if (self::$unavailable || self::$locked) { return ['locked' => 0]; }
        self::$locked = true;
        return ['locked' => 1];
    }
    public static function fetchAssoc($row) { return $row; }
    public static function fetchAllAssoc($res) { return $res['rows']; }
    public static function close($result) {}
}
class Subscription
{
    public static $rows = [];
    public static $persist = true;
    public static $saveCalls = 0;
    // Mirrors SubscriptionTable: inserts when missing, fills an empty customer, never replaces a non-empty one.
    public static function getOrCreateStripeSubscription($user, $plan, $customer = '')
    {
        if (!$customer) { throw new \RuntimeException('Empty customer primes stale missing-row cache'); }
        self::$saveCalls++;
        if (!self::$persist) { return null; }
        foreach (self::$rows as $key => $row) {
            if ($row['users_id'] == $user && $row['subscriptions_plans_id'] == $plan) {
                if (empty($row['stripe_costumer_id'])) { self::$rows[$key]['stripe_costumer_id'] = $customer; }
                return null; // Reproduce the legacy stale read after save.
            }
        }
        self::$rows[] = ['id' => count(self::$rows) + 100, 'users_id' => $user, 'subscriptions_plans_id' => $plan, 'stripe_costumer_id' => $customer, 'modified' => '2026-01-01 00:00:00'];
        return null;
    }
}
class SubscriptionTable
{
    public static function getTableName() { return 'subscriptions'; }
    public static function updateStripeCostumerId($id, $customer)
    {
        if (!Subscription::$persist) { return false; }
        foreach (Subscription::$rows as $key => $row) {
            if ($row['id'] == $id) { Subscription::$rows[$key]['stripe_costumer_id'] = $customer; }
        }
        return true;
    }
}
class Checkout extends StripeYPT
{
    public function start() {}
    public function userHasActiveSubscriptionOnPlan($plan) { return false; } // Stripe Search propagation delay.
}

class StripeSubscriptionCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['global'] = ['webSiteRootURL' => 'https://test.example.invalid/'];
        sqlDAL::$locked = sqlDAL::$unavailable = false;
        sqlDAL::$releases = 0;
        sqlDAL::$cache = [];
        Subscription::$rows = [];
        Subscription::$persist = true;
        Subscription::$saveCalls = 0;
        SubscriptionPlansTable::$trial = 0;
        Gateway\Customer::$creates = 0;
        Gateway\Customer::$errors = [];
        Gateway\Customer::$deleted = [];
        Gateway\Subscription::$items = [];
        Gateway\Subscription::$creates = [];
        Gateway\Subscription::$onCreate = null;
        Gateway\Subscription::$failList = false;
        Gateway\Subscription::$loseResponse = false;
        Gateway\Subscription::$hideFromList = false;
    }

    public function testTwoSubmissionsWithDifferentTokensAndStaleSearchChargeOnce()
    {
        $checkout = new Checkout();
        $this->assertSame('active', $checkout->setUpSubscription(13, 'token_one')->status);
        $this->assertFalse($checkout->setUpSubscription(13, 'token_two'));
        $this->assertCount(1, Gateway\Subscription::$creates);
        $this->assertSame(1, Gateway\Customer::$creates);
        $this->assertSame(1, Subscription::$saveCalls);
        $this->assertSame(2, sqlDAL::$releases);
    }

    public function testConcurrentRequestCannotCreateWhileLockIsHeld()
    {
        Gateway\Subscription::$onCreate = function () {
            $this->assertFalse((new Checkout())->setUpSubscription(13, 'concurrent_token'));
        };
        $this->assertNotFalse((new Checkout())->setUpSubscription(13, 'first_token'));
        $this->assertCount(1, Gateway\Subscription::$creates);
        $this->assertFalse(sqlDAL::$locked);
    }

    public function testMissingLockOrFailedCustomerPersistenceNeverCharges()
    {
        sqlDAL::$unavailable = true;
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'token'));
        $this->assertSame(0, Gateway\Customer::$creates);
        sqlDAL::$unavailable = false;
        Subscription::$persist = false;
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'token'));
        $this->assertCount(0, Gateway\Subscription::$creates);
        $this->assertFalse(sqlDAL::$locked);
    }

    /** @dataProvider existingStatuses */
    public function testExistingNonTerminalSubscriptionPreventsAnotherCharge($status)
    {
        Subscription::$rows = [['id' => 7, 'users_id' => 42, 'subscriptions_plans_id' => 13, 'stripe_costumer_id' => 'cus_old', 'modified' => 't1']];
        Gateway\Subscription::$items = [Gateway\Subscription::item('sub_old', 'cus_old', $status)];
        $result = (new Checkout())->setUpSubscription(13, 'token');
        if ($status === 'incomplete') { $this->assertSame('sub_old', $result->id); }
        else { $this->assertFalse($result); }
        $this->assertCount(0, Gateway\Subscription::$creates);
    }

    public function existingStatuses()
    {
        return array_map(function ($status) { return [$status]; }, ['active', 'trialing', 'incomplete', 'past_due', 'unpaid', 'paused']);
    }

    public function testLostResponseAndListFailureDoNotCreateAnotherSubscription()
    {
        Gateway\Subscription::$loseResponse = true;
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'token'));
        Gateway\Subscription::$failList = true;
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'retry_token'));
        Gateway\Subscription::$failList = false;
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'retry_token'));
        $this->assertCount(1, Gateway\Subscription::$creates);
        $this->assertFalse(sqlDAL::$locked);
    }

    public function testAllLegacyCustomersAreChecked()
    {
        Subscription::$rows = [
            ['id' => 7, 'users_id' => 42, 'subscriptions_plans_id' => 13, 'stripe_costumer_id' => 'cus_empty', 'modified' => 't1'],
            ['id' => 8, 'users_id' => 42, 'subscriptions_plans_id' => 13, 'stripe_costumer_id' => 'cus_active', 'modified' => 't1'],
        ];
        Gateway\Subscription::$items = [Gateway\Subscription::item('sub_old', 'cus_active', 'active')];
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'token'));
        $this->assertCount(0, Gateway\Subscription::$creates);
    }

    public function testCancellationAllowsNewSubscriptionWithNewKeyAndStableTrialParameters()
    {
        SubscriptionPlansTable::$trial = 7;
        $checkout = new Checkout();
        $first = $checkout->setUpSubscription(13, 'token_one');
        $first->status = 'canceled';
        $this->assertNotFalse($checkout->setUpSubscription(13, 'token_two'));
        $calls = Gateway\Subscription::$creates;
        $this->assertCount(2, $calls);
        $this->assertNotSame($calls[0][1]['idempotency_key'], $calls[1][1]['idempotency_key']);
        $this->assertSame(7, $calls[0][0]['trial_period_days']);
        $this->assertArrayNotHasKey('trial_end', $calls[0][0]);
    }

    public function testActiveLegacySubscriptionTakesPriorityOverIncompleteOne()
    {
        Subscription::$rows = [['id' => 7, 'users_id' => 42, 'subscriptions_plans_id' => 13, 'stripe_costumer_id' => 'cus_old', 'modified' => 't1']];
        Gateway\Subscription::$items = [Gateway\Subscription::item('sub_pending', 'cus_old', 'incomplete'), Gateway\Subscription::item('sub_paid', 'cus_old', 'active')];
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'token'));
        $this->assertCount(0, Gateway\Subscription::$creates);
    }

    public function testResubscribeAfterCancelRelinksRowAndStillBlocksDoubleClick()
    {
        Subscription::$rows = [['id' => 7, 'users_id' => 42, 'subscriptions_plans_id' => 13, 'stripe_costumer_id' => 'canceled', 'modified' => 't1']];
        $this->assertSame('active', (new Checkout())->setUpSubscription(13, 'token_one')->status);
        $this->assertSame('cus_1', Subscription::$rows[0]['stripe_costumer_id']);
        $this->assertCount(1, Subscription::$rows);
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'token_two'));
        $this->assertCount(1, Gateway\Subscription::$creates);
    }

    public function testCancelThenResubscribeWithinIdempotencyWindowUsesNewKey()
    {
        $first = (new Checkout())->setUpSubscription(13, 'token_one');
        $first->status = 'canceled';
        // subscriptionCancel.json.php marks the row and bumps `modified`.
        Subscription::$rows[0]['stripe_costumer_id'] = 'canceled';
        Subscription::$rows[0]['modified'] = '2026-01-01 00:05:00';
        $this->assertSame('active', (new Checkout())->setUpSubscription(13, 'token_two')->status);
        $calls = Gateway\Subscription::$creates;
        $this->assertCount(2, $calls);
        $this->assertNotSame($calls[0][1]['idempotency_key'], $calls[1][1]['idempotency_key']);
    }

    /** @dataProvider unusableCustomers */
    public function testDeletedOrMissingCustomerIsReplaced($deleted, $error)
    {
        Subscription::$rows = [['id' => 7, 'users_id' => 42, 'subscriptions_plans_id' => 13, 'stripe_costumer_id' => 'cus_gone', 'modified' => 't1']];
        Gateway\Customer::$deleted = $deleted;
        Gateway\Customer::$errors = $error;
        $this->assertSame('active', (new Checkout())->setUpSubscription(13, 'token')->status);
        $this->assertSame('cus_1', Subscription::$rows[0]['stripe_costumer_id']);
        $this->assertCount(1, Gateway\Subscription::$creates);
    }

    public function unusableCustomers()
    {
        return [
            'deleted' => [['cus_gone'], []],
            'missing' => [[], ['cus_gone' => 'resource_missing']],
        ];
    }

    public function testTransientCustomerLookupErrorNeverCharges()
    {
        Subscription::$rows = [['id' => 7, 'users_id' => 42, 'subscriptions_plans_id' => 13, 'stripe_costumer_id' => 'cus_old', 'modified' => 't1']];
        Gateway\Customer::$errors = ['cus_old' => 'rate_limit'];
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'token'));
        $this->assertSame(0, Gateway\Customer::$creates);
        $this->assertCount(0, Gateway\Subscription::$creates);
        $this->assertFalse(sqlDAL::$locked);
    }

    public function testStaleRequestCacheCannotCreateSecondCustomer()
    {
        // Prime the per-request cache with "no rows", as an earlier read in the request would.
        sqlDAL::readSql('SELECT id, users_id, subscriptions_plans_id, stripe_costumer_id FROM subscriptions WHERE users_id = ? AND subscriptions_plans_id = ?', 'ii', [42, 13], false);
        $this->assertNotFalse((new Checkout())->setUpSubscription(13, 'token_one'));
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'token_two'));
        $this->assertSame(1, Gateway\Customer::$creates);
        $this->assertCount(1, Gateway\Subscription::$creates);
    }

    public function testRetryAfterRelinkReplaysSameIdempotencyKey()
    {
        Subscription::$rows = [['id' => 7, 'users_id' => 42, 'subscriptions_plans_id' => 13, 'stripe_costumer_id' => 'canceled', 'modified' => 't1']];
        Gateway\Subscription::$loseResponse = true;
        $this->assertFalse((new Checkout())->setUpSubscription(13, 'token_one'));
        $this->assertSame('cus_1', Subscription::$rows[0]['stripe_costumer_id']);
        // Even if the list missed the created subscription, the retry must replay, not charge again.
        Gateway\Subscription::$loseResponse = false;
        Gateway\Subscription::$hideFromList = true;
        $this->assertSame('sub_1', (new Checkout())->setUpSubscription(13, 'token_two')->id);
        $this->assertCount(1, Gateway\Subscription::$creates);
        $this->assertSame(1, Gateway\Customer::$creates);
    }
}

namespace Tests\StripeCheckout\Gateway;

class Stripe
{
    public static function setApiVersion($version) {}
    public static function setApiKey($key) {}
}
class WebhookEndpoint
{
    public static function all($params) { return (object) ['data' => [(object) ['url' => 'https://test.example.invalid/plugin/StripeYPT/ipn.php']]]; }
}
class Customer
{
    public static $creates = 0, $errors = [], $deleted = [];
    public static function create($params, $options) { return (object) ['id' => 'cus_' . ++self::$creates]; }
    public static function retrieve($id)
    {
        if (isset(self::$errors[$id])) { throw new Exception\InvalidRequestException(self::$errors[$id]); }
        return (object) ['id' => $id, 'deleted' => in_array($id, self::$deleted, true)];
    }
    public static function update($id, $params) { return (object) ['id' => $id]; }
}
class Plan
{
    public static function create($params, $options) { return (object) ['id' => 'plan_' . $options['idempotency_key']]; }
}
class Subscription
{
    public static $items = [], $creates = [], $onCreate, $failList = false, $loseResponse = false, $hideFromList = false;
    public static function item($id, $customer, $status)
    {
        return (object) ['id' => $id, 'customer' => $customer, 'status' => $status, 'metadata' => (object) ['users_id' => 42, 'plans_id' => 13]];
    }
    public static function all($params)
    {
        if (self::$failList) { throw new \RuntimeException('Stripe unavailable'); }
        if (self::$hideFromList) { return new Collection([]); }
        return new Collection(array_filter(self::$items, function ($item) use ($params) { return $item->customer === $params['customer']; }));
    }
    public static function retrieve($params)
    {
        foreach (self::$items as $item) { if ($item->id === $params['id']) { return $item; } }
        throw new \RuntimeException('Missing subscription');
    }
    public static function create($params, $options)
    {
        foreach (self::$creates as $call) {
            if ($call[1]['idempotency_key'] === $options['idempotency_key']) {
                if ($call[0] !== $params) { throw new \RuntimeException('Idempotency parameter mismatch'); }
                return $call[2]; // Stripe replays the original response.
            }
        }
        if (self::$onCreate) { call_user_func(self::$onCreate); }
        $item = self::item('sub_' . (count(self::$creates) + 1), $params['customer'], 'active');
        self::$creates[] = [$params, $options, $item];
        self::$items[] = $item;
        if (self::$loseResponse) { throw new \RuntimeException('Response lost after charge'); }
        return $item;
    }
}
class Collection
{
    private $items;
    public function __construct($items) { $this->items = $items; }
    public function autoPagingIterator() { return new \ArrayIterator($this->items); }
}

namespace Tests\StripeCheckout\Gateway\Exception;

class InvalidRequestException extends \Exception
{
    private $stripeCode;
    public function __construct($stripeCode) { parent::__construct($stripeCode); $this->stripeCode = $stripeCode; }
    public function getStripeCode() { return $this->stripeCode; }
}
