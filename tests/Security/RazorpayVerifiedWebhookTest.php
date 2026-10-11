<?php

namespace Tests\Security\RazorpayVerifiedWebhook;

use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once dirname(__DIR__, 2) . '/plugin/RazorPayYPT/razorpay-php/src/Utility.php';
require_once dirname(__DIR__, 2) . '/plugin/RazorPayYPT/razorpay-php/src/Errors/SignatureVerificationError.php';

class Fixture {
    const SECRET = 'fixture-only-razorpay-secret';
    public static $body;
    public static $status;
    public static $logs;
    public static $credits;
    public static $renewals;
    public static $failRenewal;
}
function header($value) {}
function http_response_code($code) { Fixture::$status = $code; }
function _error_log($message, $level = '') { Fixture::$logs[] = $message; }
class AVideoLog {
    public static $ERROR = 'error';
    public static $WARNING = 'warning';
}
function _json_decode($json) { return json_decode($json); }
function file_get_contents($path) { return Fixture::$body; }
class AVideoPlugin {
    public static function loadPluginIfEnabled($name) { return $name === 'YPTWallet' ? new Wallet() : new Provider(); }
    public static function getObjectData($name) { return (object)['webhookSecret' => Fixture::SECRET]; }
}
class Provider {
    public function start() { return (object)['utility' => new \Razorpay\Api\Utility()]; }
}
class Wallet {
    public function processPaymentOnce($provider, $ids, $user, $process) {
        if ($process() !== true) {
            Fixture::$credits = 0;
            throw new RuntimeException('Failed renewal');
        }
        return true;
    }
    public function addBalance($user, $value, $description, $json) { Fixture::$credits += $value; }
    public function runPaymentStep($step, $label) { return $step() === true; }
}
class Subscription {
    public static function renew($user, $plan) {
        if (empty($GLOBALS['global']['bypassSameDomainCheck'])) {
            throw new RuntimeException('Verified provider renewal requires scoped origin bypass');
        }
        if (Fixture::$failRenewal) { return (object)['error' => true]; }
        ++Fixture::$renewals;
        return (object)['error' => false];
    }
}

class RazorpayVerifiedWebhookTest extends TestCase
{
    private $saved;
    protected function setUp(): void {
        foreach (['global', '_SERVER', '_GET', '_POST'] as $name) { $this->saved[$name] = $GLOBALS[$name] ?? null; }
        $GLOBALS['global'] = ['systemRootPath' => '/fixture/'];
        $_GET = $_POST = [];
        $_SERVER = [];
        Fixture::$body = '{"event":"subscription.charged","payload":{"subscription":{"entity":{"notes":{"users_id":7,"plans_id":3}}},"payment":{"entity":{"id":"pay_fixture","amount":1000}}}}';
        Fixture::$status = 200;
        Fixture::$logs = [];
        Fixture::$credits = Fixture::$renewals = 0;
        Fixture::$failRenewal = false;
    }
    protected function tearDown(): void {
        foreach ($this->saved as $name => $value) { $GLOBALS[$name] = $value; }
    }
    private function deliver($validSignature = true) {
        $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] = $validSignature
            ? hash_hmac('sha256', Fixture::$body, Fixture::SECRET) : 'invalid-fixture-signature';
        $global =& $GLOBALS['global'];
        $source = \file_get_contents(dirname(__DIR__, 2) . '/plugin/RazorPayYPT/ipn.php');
        $source = preg_replace('/^require_once .*;\r?$/m', '', $source);
        $source = preg_replace('/^<\?php|\?>\s*$/', '', $source);
        eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use stdClass;' . $source);
    }
    public function testRealProviderSignatureAllowsOriginlessRenewalAndRestoresGuard() {
        $this->deliver();
        $this->assertSame(200, Fixture::$status);
        $this->assertEquals(10, Fixture::$credits);
        $this->assertSame(1, Fixture::$renewals);
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
        $this->assertStringNotContainsString(Fixture::SECRET, implode('\n', Fixture::$logs));
    }
    public function testInvalidSignatureCannotCreditRenewOrBypassOriginGuard() {
        $this->deliver(false);
        $this->assertSame(400, Fixture::$status);
        $this->assertSame(0, Fixture::$credits);
        $this->assertSame(0, Fixture::$renewals);
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
        $this->assertStringNotContainsString(Fixture::SECRET, implode('\n', Fixture::$logs));
    }
    public function testFailedRenewalKeepsCreditLogsAndRestoresExistingOriginFlag() {
        $GLOBALS['global']['bypassSameDomainCheck'] = 0;
        Fixture::$failRenewal = true;
        $this->deliver();
        $this->assertSame(200, Fixture::$status, 'A paid delivery must not be retried forever because the renewal failed');
        $this->assertEquals(10, Fixture::$credits);
        $this->assertSame(0, Fixture::$renewals);
        $this->assertStringContainsString('renew it manually payment=pay_fixture', implode("\n", Fixture::$logs));
        $this->assertSame(0, $GLOBALS['global']['bypassSameDomainCheck']);
    }
    public function testSubscriptionNotCreatedByAVideoIsAcknowledgedWithoutChanges() {
        Fixture::$body = '{"event":"subscription.charged","payload":{"subscription":{"entity":{"id":"sub_other","notes":{}}},"payment":{"entity":{"id":"pay_other","amount":1000}}}}';
        $this->deliver();
        $this->assertSame(200, Fixture::$status);
        $this->assertSame(0, Fixture::$credits);
        $this->assertSame(0, Fixture::$renewals);
        $this->assertStringContainsString('ignored', implode("\n", Fixture::$logs));
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
    }
}
