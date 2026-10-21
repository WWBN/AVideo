<?php

namespace Tests\StripeSinglePayment;

use PHPUnit\Framework\TestCase;

$source = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/StripeYPT.php');
$source = substr($source, strpos($source, 'class StripeYPT extends'));
$source = str_replace('\\Stripe\\', '\\Tests\\StripeSinglePayment\\Gateway\\', $source);
eval('namespace ' . __NAMESPACE__ . '; ' . $source);

class PluginAbstract {}
class AVideoPlugin
{
    public static $ppv, $wallet;
    public static function getDataObject($name) { return (object) ['Restrictedkey' => 'rk_test_runtime']; }
    public static function loadPluginIfEnabled($name) { return $name === 'YPTWallet' ? self::$wallet : self::$ppv; }
}
class AVideoLog { public static $WARNING = 'warning', $ERROR = 'error'; }
class TestWallet
{
    public $balance = 0;
    public function getBalance($user) { return $this->balance; }
    public $credited = [], $balances = [];
    public function isStripePaymentCredited($intentId, $users_id) { return in_array($intentId, $this->credited, true); }
    public function processPaymentOnce($provider, $ids, $users_id, callable $process) { return $process(); }
    public function addBalance($users_id, $value, $description = '', $json = '{}') { $this->balances[] = [$users_id, $value]; return true; }
}
class User
{
    public static $id = 42;
    public static function getId() { return self::$id; }
    public static function getEmail_() { return ''; }
}
function isValidEmail($email) { return false; }
function _error_log($message, $level = null) { $GLOBALS['checkoutLogs'][] = $message; }
function time() { return StripeSinglePaymentCheckoutTest::$now; }
class CheckoutStripe extends StripeYPT
{
    public function start() {}
    public function getDataObject() { return (object) ['Restrictedkey' => 'test-only']; }
}

class StripeSinglePaymentCheckoutTest extends TestCase
{
    public static $now;
    private $root;
    private $stripe;

    protected function setUp(): void
    {
        self::$now = 100000;
        $GLOBALS['checkoutLogs'] = [];
        User::$id = 42;
        AVideoPlugin::$ppv = null;
        AVideoPlugin::$wallet = null;
        Gateway\PaymentIntent::$objects = [];
        Gateway\PaymentIntent::$keys = [];
        Gateway\PaymentIntent::$requests = [];
        Gateway\PaymentIntent::$loseResponse = false;
        Gateway\PaymentIntent::$failRetrieve = false;
        Gateway\PaymentIntent::$failCancel = false;
        Gateway\PaymentIntent::$reject = false;
        Gateway\PaymentIntent::$onCreate = null;
        Gateway\Charge::$objects = [];
        Gateway\Charge::$failRetrieve = false;
        Gateway\Charge::$denyRead = false;
        $this->root = sys_get_temp_dir() . '/avideo-payment-test-' . bin2hex(random_bytes(8)) . '/';
        mkdir($this->root);
        $GLOBALS['global'] = ['systemRootPath' => $this->root, 'webSiteRootURL' => 'https://test.invalid/'];
        $this->stripe = new CheckoutStripe();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->root . 'videos/stripe-payment-locks/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->root . 'videos/stripe-payment-locks')) {
            rmdir($this->root . 'videos/stripe-payment-locks');
            rmdir($this->root . 'videos');
        }
        rmdir($this->root);
    }

    private function pay($amount = 1.99)
    {
        return $this->stripe->getIntent($amount, 'USD', 'Payment', ['singlePayment' => 1, 'users_id' => User::$id]);
    }

    public function testPaymentStartupDoesNotInspectOrCreateWebhooks(): void
    {
        // There is deliberately no WebhookEndpoint test double: runtime needs no management API.
        StripeYPT::_start();
        $this->assertSame('rk_test_runtime', Gateway\Stripe::$key);
        $this->assertSame('2022-11-15', Gateway\Stripe::$version);
    }

    public function testRepeatedRequestReusesPendingIntent(): void
    {
        $first = $this->pay();
        $this->assertSame($first, $this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }

    public function testIntentLogsContainIdentifiersWithoutClientSecret(): void
    {
        $intent = $this->pay();
        $logs = implode("\n", $GLOBALS['checkoutLogs']);
        $this->assertStringContainsString('success id=' . $intent->id, $logs);
        $this->assertStringNotContainsString($intent->client_secret, $logs);
    }

    public function testReceiptUsesCatalogDetailsAndDifferentMovieCannotReusePendingIntent(): void
    {
        AVideoPlugin::$ppv = new class {
            public function getPaymentDetails($plan, $video, $funding) {
                return ['description' => 'Wallet funds for PPV rental: Movie ' . $video . ' | 72 hours',
                    'metadata' => ['videos_id' => (string) $video, 'ppv_plans_id' => (string) $plan, 'rental_hours' => '72']];
            }
        };
        $metadata = ['singlePayment' => 1, 'plugin' => 'PayPerView', 'plans_id' => 2, 'videos_id' => 3143];
        $first = $this->stripe->getIntent(1.99, 'USD', 'Untrusted movie name', $metadata);
        $this->assertSame('Wallet funds for PPV rental: Movie 3143 | 72 hours', $first->description);
        $this->assertSame('3143', $first->metadata['videos_id']);
        $this->assertSame('2', $first->metadata['ppv_plans_id']);
        $this->assertSame($first, $this->stripe->getIntent(1.99, 'USD', 'Other submitted text', $metadata));
        $metadata['videos_id'] = 6106;
        $second = $this->stripe->getIntent(1.99, 'USD', 'Submitted text', $metadata);
        $this->assertNotSame($first->id, $second->id);
        $this->assertSame('canceled', $first->status);
        $this->assertSame('6106', $second->metadata['videos_id']);
    }

    public function testInvalidCatalogSelectionDoesNotCreatePaymentAndOlderPpvStillWorks(): void
    {
        AVideoPlugin::$ppv = new class { public function getPaymentDetails($plan, $video, $funding) { return false; } };
        $metadata = ['singlePayment' => 1, 'plugin' => 'PayPerView', 'plans_id' => 2, 'videos_id' => 3143];
        $this->assertFalse($this->stripe->getIntent(1.99, 'USD', 'Fallback', $metadata));
        $this->assertCount(0, Gateway\PaymentIntent::$objects);
        AVideoPlugin::$ppv = new \stdClass();
        $this->assertSame('Fallback', $this->stripe->getIntent(1.99, 'USD', 'Fallback', $metadata)->description);
    }

    public function testBusyLockRejectsConcurrentRequest(): void
    {
        Gateway\PaymentIntent::$onCreate = function () {
            $this->assertFalse($this->pay());
            $this->assertStringContainsString('already being processed', $GLOBALS['getIntentErrorResponse']);
        };
        $this->assertNotFalse($this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }

    public function testNewPpvCheckoutBindsAutomaticRentalAndCatalogPriceToIntent(): void
    {
        AVideoPlugin::$ppv = new class {
            public function completeWalletCheckout() {}
            public function getPaymentDetails($plan, $video, $funding) {
                return ['description' => $funding ? 'Wallet funds' : 'PPV rental: A Serbian Film', 'value' => 1.99,
                    'metadata' => ['videos_id' => (string) $video, 'ppv_plans_id' => (string) $plan, 'rental_hours' => '72']];
            }
        };
        $intent = $this->stripe->getIntent(0.99, 'USD', 'Untrusted title',
            ['singlePayment' => 1, 'plugin' => 'PayPerView', 'plans_id' => 2, 'videos_id' => 3419]);
        $this->assertSame('1', $intent->metadata['ppv_auto_rent']);
        $this->assertSame('42', $intent->metadata['users_id']);
        $this->assertSame('1.99', $intent->metadata['ppv_value']);
        $this->assertSame('3419', $intent->metadata['videos_id']);
        $this->assertSame('PPV rental: A Serbian Film', $intent->description);
        $this->assertSame(99, intval($intent->amount), 'Only the shortfall is charged to the card');
    }

    public function testDisabledPpvDoesNotCreateACardCharge(): void
    {
        $this->assertFalse($this->stripe->getIntent(1.99, 'USD', 'PPV rental',
            ['singlePayment' => 1, 'plugin' => 'PayPerView', 'plans_id' => 2, 'videos_id' => 3419]));
        $this->assertSame([], Gateway\PaymentIntent::$objects);
    }

    public function testLiveEventUsesServerPriceAndWalletShortfallInsteadOfSubmittedAmount(): void
    {
        AVideoPlugin::$wallet = new TestWallet();
        AVideoPlugin::$wallet->balance = 0.50;
        AVideoPlugin::$ppv = new class {
            public $owned = false;
            public function getSubscriptionToPlan($user, $schedule) { return $this->owned ? ['id'=>1,'status'=>'a'] : false; }
            public function completeWalletCheckout() {}
            public function getPaymentDetails($schedule) {
                return $schedule === 9 ? ['value' => 1.99, 'description' => 'PPV Live: Test event',
                    'metadata' => ['ppvlive_schedule_id' => '9', 'live_ends' => '2030-01-01 00:00:00']] : false;
            }
        };
        $metadata = ['singlePayment' => 1, 'plugin' => 'PayPerViewLive', 'ppvlive_schedule_id' => 9, 'users_id' => 999];
        $intent = $this->stripe->getIntent(0.01, 'USD', 'Submitted event', $metadata);
        $this->assertSame(149, intval($intent->amount));
        $this->assertSame('42', $intent->metadata['users_id']);
        $this->assertSame('1', $intent->metadata['ppvlive_auto_buy']);
        $this->assertSame('1.99', $intent->metadata['ppvlive_value']);
        $this->assertSame('PPV Live: Test event', $intent->description);
        $this->assertSame($intent, $this->stripe->getIntent(99, 'USD', 'Other text', $metadata));
        AVideoPlugin::$wallet->balance = 1.99;
        $this->assertFalse($this->stripe->getIntent(0.01, 'USD', 'Event', $metadata));
        $this->assertCount(1, Gateway\PaymentIntent::$objects, 'A sufficient wallet never creates another card payment');
        AVideoPlugin::$wallet->balance = 0;
        AVideoPlugin::$ppv->owned = true;
        $this->assertFalse($this->stripe->getIntent(1.99, 'USD', 'Event', $metadata));
        $this->assertCount(1, Gateway\PaymentIntent::$objects, 'A stale tab cannot charge an already-owned event');
        $metadata['ppvlive_schedule_id'] = 10;
        $this->assertFalse($this->stripe->getIntent(1.99, 'USD', 'Event', $metadata));
    }

    private function paid($intent, $chargedAt)
    {
        $intent->status = 'succeeded';
        $intent->latest_charge = 'ch_' . $intent->id;
        Gateway\Charge::$objects[$intent->latest_charge] = (object) ['created' => $chargedAt];
    }

    public function testSuccessfulPaymentIsReusedAndOtherPaymentsBlockedForFiveMinutes(): void
    {
        $first = $this->pay();
        $this->paid($first, self::$now);
        $this->assertSame($first, $this->pay());
        self::$now += 299;
        $this->assertSame($first, $this->pay());
        $this->assertFalse($this->pay(3.99));
        self::$now++;
        $this->assertNotSame($first->id, $this->pay()->id);
        $this->assertCount(2, Gateway\PaymentIntent::$objects);
    }

    public function testReturningCustomerIsNotBlockedByAnOldSuccessfulPayment(): void
    {
        // The browser confirms the card, so the server first sees the success days later.
        $first = $this->pay();
        $this->paid($first, self::$now + 30);
        self::$now += 7 * 86400;
        $second = $this->pay();
        $this->assertNotSame($first->id, $second->id);
        $this->assertSame('requires_payment_method', $second->status);
        $this->paid($second, self::$now);
        self::$now += 7 * 86400;
        $this->assertNotFalse($this->pay(25));
        $this->assertCount(3, Gateway\PaymentIntent::$objects);
    }

    public function testSlowConfirmationCooldownIsMeasuredFromTheCharge(): void
    {
        $first = $this->pay();
        self::$now += 3600;
        $this->paid($first, self::$now - 60);
        $this->assertSame($first, $this->pay());
        $this->assertFalse($this->pay(3.99));
        self::$now += 240;
        $this->assertNotSame($first->id, $this->pay()->id);
        $this->assertCount(2, Gateway\PaymentIntent::$objects);
    }

    public function testApprovedPaymentWaitingForItsCreditIsNeverChargedAgain(): void
    {
        AVideoPlugin::$wallet = new TestWallet();
        $first = $this->pay();
        $this->paid($first, self::$now);
        self::$now += 3600; // webhook still failing long after the five-minute cooldown
        $this->assertSame($first, $this->pay(), 'The same checkout only waits for the credit');
        $this->assertFalse($this->pay(3.99));
        $this->assertStringContainsString('still being added', $GLOBALS['getIntentErrorResponse']);
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
        AVideoPlugin::$wallet->credited[] = $first->id;
        $this->assertNotSame($first->id, $this->pay(3.99)->id);
    }

    public function testCreditWaitEndsWithStripeRetriesOrARefund(): void
    {
        AVideoPlugin::$wallet = new TestWallet();
        $first = $this->pay();
        $this->paid($first, self::$now);
        self::$now += 3 * 86400;
        $second = $this->pay(3.99);
        $this->assertNotSame($first->id, $second->id);
        $this->paid($second, self::$now);
        Gateway\Charge::$objects[$second->latest_charge]->refunded = true;
        self::$now += 600;
        $this->assertNotFalse($this->pay(4.99));
        $this->assertCount(3, Gateway\PaymentIntent::$objects);
    }

    private function chargeEvent(?array $current = null)
    {
        $payload = json_decode('{"id":"evt_1","type":"charge.succeeded","data":{"object":{"id":"ch_1","amount":199,'
            . '"amount_refunded":0,"status":"succeeded","payment_intent":"pi_1","metadata":{"users_id":"7","singlePayment":"1"}}}}');
        if ($current !== null) {
            Gateway\Charge::$objects['ch_1'] = (object) array_merge(['id' => 'ch_1', 'amount' => 199, 'status' => 'succeeded'], $current);
        }
        return $payload;
    }

    public function testLateEventOfARefundedChargeIsNotCredited(): void
    {
        AVideoPlugin::$wallet = new TestWallet();
        $this->assertFalse($this->stripe->processSinglePaymentIPN($this->chargeEvent(['amount_refunded' => 199, 'refunded' => true])));
        $this->assertSame([], AVideoPlugin::$wallet->balances);
    }

    public function testPartialRefundCreditsOnlyWhatIsStillPaid(): void
    {
        AVideoPlugin::$wallet = new TestWallet();
        $this->assertTrue($this->stripe->processSinglePaymentIPN($this->chargeEvent(['amount_refunded' => 194])));
        $this->assertSame([['7', 0.05]], AVideoPlugin::$wallet->balances);
    }

    public function testChargeCreditUsesTheEventWhenTheKeyCannotReadCharges(): void
    {
        AVideoPlugin::$wallet = new TestWallet();
        Gateway\Charge::$denyRead = true;
        $this->assertTrue($this->stripe->processSinglePaymentIPN($this->chargeEvent()));
        $this->assertSame([['7', 1.99]], AVideoPlugin::$wallet->balances);
    }

    public function testChargeReadFailureAsksStripeToRetryTheEvent(): void
    {
        AVideoPlugin::$wallet = new TestWallet();
        Gateway\Charge::$failRetrieve = true;
        $this->expectException(\RuntimeException::class);
        try {
            $this->stripe->processSinglePaymentIPN($this->chargeEvent());
        } finally {
            $this->assertSame([], AVideoPlugin::$wallet->balances);
        }
    }

    public function testDirectChargeLogContainsIdentifiersOnly(): void
    {
        $previous = $_POST;
        $_POST['stripeToken'] = 'tok_test';
        try {
            $charge = $this->stripe->setUpPayment(1.99, 'USD', 'PPV rental');
        } finally {
            $_POST = $previous;
        }
        $logs = implode("\n", $GLOBALS['checkoutLogs']);
        $this->assertStringContainsString('charge id=' . $charge->id, $logs);
        $this->assertStringNotContainsString('buyer@example.invalid', $logs);
        $this->assertStringNotContainsString('4242', $logs);
    }

    public function testChargeReadFailureDoesNotCreateAnotherPayment(): void
    {
        $first = $this->pay();
        $this->paid($first, self::$now);
        self::$now += 7 * 86400;
        Gateway\Charge::$failRetrieve = true;
        $this->assertFalse($this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }

    public function testMissingChargesPermissionDegradesToObservedCooldownInsteadOfLockingOut(): void
    {
        $first = $this->pay();
        $this->paid($first, self::$now);
        self::$now += 7 * 86400;
        Gateway\Charge::$denyRead = true;
        $this->assertSame($first, $this->pay());
        self::$now += 300;
        $this->assertNotSame($first->id, $this->pay()->id);
        $this->assertCount(2, Gateway\PaymentIntent::$objects);
    }

    public function testStateFromPreviousVersionWithCompletedAtIsUnblockedByChargeTime(): void
    {
        $first = $this->pay();
        $this->paid($first, self::$now);
        self::$now += 7 * 86400;
        // The previous version wrote completed_at when it first observed the success,
        // i.e. on this returning purchase, and then blocked it for five minutes.
        $files = glob($this->root . 'videos/stripe-payment-locks/*.php');
        $prefix = "<?php exit; ?>\n";
        $state = json_decode(substr(file_get_contents($files[0]), strlen($prefix)), true);
        $state['completed_at'] = self::$now;
        file_put_contents($files[0], $prefix . json_encode($state));
        self::$now += 60;
        $this->assertNotSame($first->id, $this->pay()->id);
        $this->assertCount(2, Gateway\PaymentIntent::$objects);
    }

    public function testSuccessWithoutChargeTimeFailsClosedUntilObservedCooldownEnds(): void
    {
        $first = $this->pay();
        $first->status = 'succeeded';
        self::$now += 7 * 86400;
        $this->assertSame($first, $this->pay());
        self::$now += 299;
        $this->assertFalse($this->pay(3.99));
        self::$now++;
        $this->assertNotSame($first->id, $this->pay()->id);
        $this->assertCount(2, Gateway\PaymentIntent::$objects);
    }

    public function testLostCreateResponseRetriesSameIdempotencyKey(): void
    {
        Gateway\PaymentIntent::$loseResponse = true;
        $this->assertFalse($this->pay());
        $this->assertNotFalse($this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
        $this->assertSame(Gateway\PaymentIntent::$requests[0], Gateway\PaymentIntent::$requests[1]);
    }

    public function testUncertainCreationStartsOverWhenTheKeyCanNoLongerBeReplayed(): void
    {
        // The hidden intent was never confirmed and its client secret never left the
        // server, so a fresh key is safe once Stripe's retention window has elapsed.
        Gateway\PaymentIntent::$loseResponse = true;
        $this->assertFalse($this->pay());
        self::$now += 23 * 3600;
        $this->assertNotFalse($this->pay());
        $this->assertCount(2, Gateway\PaymentIntent::$requests);
        $this->assertNotSame(Gateway\PaymentIntent::$requests[0], Gateway\PaymentIntent::$requests[1]);
    }

    public function testUncertainCreationStartsOverForADifferentAmount(): void
    {
        Gateway\PaymentIntent::$loseResponse = true;
        $this->assertFalse($this->pay());
        $this->assertNotFalse($this->pay(4.99));
        $this->assertCount(2, Gateway\PaymentIntent::$requests);
        $this->assertNotSame(Gateway\PaymentIntent::$requests[0], Gateway\PaymentIntent::$requests[1]);
    }

    public function testPendingDifferentAmountCancelsThePreviousIntentFirst(): void
    {
        $first = $this->pay();
        $second = $this->pay(4.99);
        $this->assertSame('canceled', $first->status);
        $this->assertNotSame($first->id, $second->id);
        $this->assertCount(2, Gateway\PaymentIntent::$objects);
    }

    public function testPendingDifferentAmountNeverCreatesAnotherWhenCancelFails(): void
    {
        $this->pay();
        Gateway\PaymentIntent::$failCancel = true;
        $this->assertFalse($this->pay(4.99));
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }

    public function testExpiredIntentIsCanceledBeforeReplacement(): void
    {
        $first = $this->pay();
        self::$now += 900;
        $second = $this->pay();
        $this->assertSame('canceled', $first->status);
        $this->assertNotSame($first->id, $second->id);
    }

    public function testCancellationRaceNeverCreatesAnotherPayment(): void
    {
        $this->pay();
        self::$now += 900;
        Gateway\PaymentIntent::$failCancel = true;
        $this->assertFalse($this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }

    public function testProcessingNeverExpiresIntoAnotherCharge(): void
    {
        $first = $this->pay();
        $first->status = 'processing';
        self::$now += 86400;
        $this->assertFalse($this->pay());
        $first->status = 'requires_capture';
        $this->assertFalse($this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }

    public function testStripeReadFailureDoesNotCreateAnotherPayment(): void
    {
        $this->pay();
        Gateway\PaymentIntent::$failRetrieve = true;
        $this->assertFalse($this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }

    public function testUsersHaveIndependentLocks(): void
    {
        $first = $this->pay();
        User::$id++;
        $this->assertNotSame($first->id, $this->pay()->id);
    }

    public function testCorruptStateFailsClosed(): void
    {
        $this->pay();
        $files = glob($this->root . 'videos/stripe-payment-locks/*.php');
        file_put_contents($files[0], 'corrupt');
        $this->assertFalse($this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }

    public function testEmptyStateFailsClosed(): void
    {
        $this->pay();
        $files = glob($this->root . 'videos/stripe-payment-locks/*.php');
        file_put_contents($files[0], '');
        $this->assertFalse($this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }

    public function testValidationFailureAllowsCorrectedAmount(): void
    {
        Gateway\PaymentIntent::$reject = true;
        $this->assertFalse($this->pay(0.01));
        Gateway\PaymentIntent::$reject = false;
        $this->assertNotFalse($this->pay(1.99));
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }
}

namespace Tests\StripeSinglePayment\Gateway;

class Stripe
{
    public static $key, $version;
    public static function setApiKey($key) { self::$key = $key; }
    public static function setApiVersion($version) { self::$version = $version; }
}

class PaymentIntent
{
    public static $objects = [], $keys = [], $requests = [];
    public static $loseResponse, $failRetrieve, $failCancel, $onCreate, $reject;
    public $id, $client_secret, $latest_charge, $amount, $currency, $description, $metadata, $status = 'requires_payment_method';

    public static function create($parameters, $options = [])
    {
        $key = $options['idempotency_key'] ?? uniqid();
        self::$requests[] = $key;
        if (self::$reject) { throw new Exception\InvalidRequestException('Below minimum amount'); }
        if (self::$onCreate) {
            $callback = self::$onCreate;
            self::$onCreate = null;
            $callback();
        }
        if (isset(self::$keys[$key])) { return self::$keys[$key]; }
        $intent = new self();
        $intent->id = 'pi_' . (count(self::$objects) + 1);
        $intent->amount = $parameters['amount'];
        $intent->currency = $parameters['currency'];
        $intent->description = $parameters['description'];
        $intent->metadata = $parameters['metadata'];
        $intent->client_secret = $intent->id . '_test_secret';
        self::$objects[$intent->id] = $intent;
        self::$keys[$key] = $intent;
        if (self::$loseResponse) {
            self::$loseResponse = false;
            throw new \RuntimeException('Response lost after Stripe created the intent');
        }
        return $intent;
    }

    public static function retrieve($id)
    {
        if (self::$failRetrieve) { throw new \RuntimeException('Stripe unavailable'); }
        return self::$objects[$id];
    }

    public function cancel()
    {
        if (self::$failCancel) { throw new \RuntimeException('Payment already confirmed'); }
        $this->status = 'canceled';
        return $this;
    }
}

class Charge
{
    public static $objects = [], $failRetrieve = false, $denyRead = false;

    public static function create($parameters)
    {
        return (object) ['id' => 'ch_direct', 'status' => 'succeeded', 'amount' => $parameters['amount'], 'currency' => $parameters['currency'],
            'billing_details' => (object) ['email' => 'buyer@example.invalid'], 'payment_method_details' => (object) ['card' => (object) ['last4' => '4242']]];
    }

    public static function retrieve($id)
    {
        if (self::$denyRead) { throw new Exception\PermissionException('This API key does not have the required permissions'); }
        if (self::$failRetrieve) { throw new \RuntimeException('Stripe unavailable'); }
        return self::$objects[$id];
    }
}

namespace Tests\StripeSinglePayment\Gateway\Exception;

class InvalidRequestException extends \RuntimeException
{
    public function getHttpStatus() { return 400; }
    public function getStripeCode() { return 'amount_too_small'; }
}

class PermissionException extends \RuntimeException {}
