<?php

namespace Tests\StripeSinglePayment;

use PHPUnit\Framework\TestCase;

$source = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/StripeYPT.php');
$source = substr($source, strpos($source, 'class StripeYPT extends'));
$source = str_replace('\\Stripe\\', '\\Tests\\StripeSinglePayment\\Gateway\\', $source);
eval('namespace ' . __NAMESPACE__ . '; ' . $source);

class PluginAbstract {}
class User
{
    public static $id = 42;
    public static function getId() { return self::$id; }
    public static function getEmail_() { return ''; }
}
function isValidEmail($email) { return false; }
function _error_log($message, $level = null) {}
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
        User::$id = 42;
        Gateway\PaymentIntent::$objects = [];
        Gateway\PaymentIntent::$keys = [];
        Gateway\PaymentIntent::$requests = [];
        Gateway\PaymentIntent::$loseResponse = false;
        Gateway\PaymentIntent::$failRetrieve = false;
        Gateway\PaymentIntent::$failCancel = false;
        Gateway\PaymentIntent::$reject = false;
        Gateway\PaymentIntent::$onCreate = null;
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

    public function testRepeatedRequestReusesPendingIntent(): void
    {
        $first = $this->pay();
        $this->assertSame($first, $this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
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

    public function testSuccessfulPaymentIsReusedAndOtherPaymentsBlockedForFiveMinutes(): void
    {
        $first = $this->pay();
        $first->status = 'succeeded';
        $this->assertSame($first, $this->pay());
        self::$now += 299;
        $this->assertSame($first, $this->pay());
        $this->assertFalse($this->pay(3.99));
        self::$now++;
        $this->assertNotSame($first->id, $this->pay()->id);
        $this->assertCount(2, Gateway\PaymentIntent::$objects);
    }

    public function testSlowConfirmationStartsCooldownWhenSuccessIsObserved(): void
    {
        $first = $this->pay();
        self::$now += 3600;
        $first->status = 'succeeded';
        $this->assertSame($first, $this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
    }

    public function testLostCreateResponseRetriesSameIdempotencyKey(): void
    {
        Gateway\PaymentIntent::$loseResponse = true;
        $this->assertFalse($this->pay());
        $this->assertNotFalse($this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$objects);
        $this->assertSame(Gateway\PaymentIntent::$requests[0], Gateway\PaymentIntent::$requests[1]);
    }

    public function testUncertainExpiredCreationDoesNotCreateAnotherPayment(): void
    {
        Gateway\PaymentIntent::$loseResponse = true;
        $this->assertFalse($this->pay());
        self::$now += 23 * 3600;
        $this->assertFalse($this->pay());
        $this->assertCount(1, Gateway\PaymentIntent::$requests);
    }

    public function testPendingDifferentAmountIsBlocked(): void
    {
        $this->pay();
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

class PaymentIntent
{
    public static $objects = [], $keys = [], $requests = [];
    public static $loseResponse, $failRetrieve, $failCancel, $onCreate, $reject;
    public $id, $client_secret, $status = 'requires_payment_method';

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

namespace Tests\StripeSinglePayment\Gateway\Exception;

class InvalidRequestException extends \RuntimeException
{
    public function getHttpStatus() { return 400; }
    public function getStripeCode() { return 'amount_too_small'; }
}
