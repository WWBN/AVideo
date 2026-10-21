<?php
namespace Tests\Unit\PayPalCheckout;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/*
 * Runs the real PayPalYPT checkout code (start, reconcile, webhook signature check) against a scripted
 * PayPal API and an in-memory checkout store. The store's SQL is exercised separately on MySQL.
 */

class PluginAbstract { public function updateScript() { return true; } public static function addDataObjectHelper() {} }
class AVideoLog { public static $ERROR = 'error'; public static $WARNING = 'warning'; public static $SECURITY = 'security'; }
function __($text) { return $text; }
function _error_log($message, $level = '') { FakeRest::$logs[] = [$level, $message]; }
function parseToFloat($value) { return floatval($value); }
function http_response_code($code = null) { return 200; }

class FakeRest
{
    public static $responses = [], $calls = [], $input = '', $headers = [], $logs = [];
    /** Queue answers by "METHOD path-prefix": an array is returned, an exception is thrown. */
    public static function on($key, $answer) { self::$responses[$key][] = $answer; }
    public static function call($method, $path, $payload, $requestId) {
        self::$calls[] = [$method, $path, $payload, $requestId];
        foreach (self::$responses as $key => $queue) {
            [$verb, $prefix] = explode(' ', $key, 2);
            if ($verb === $method && strpos($path, $prefix) === 0 && $queue) {
                $answer = count($queue) > 1 ? array_shift(self::$responses[$key]) : $queue[0];
                if ($answer instanceof \Throwable) { throw $answer; }
                return $answer;
            }
        }
        throw new RuntimeException("Unexpected PayPal call {$method} {$path}");
    }
    public static function paypalError($status, $name) {
        $exception = new \PayPal\Exception\PayPalConnectionException('https://api.example', 'HTTP ' . $status, $status);
        $exception->setData(json_encode(['name' => $name, 'debug_id' => 'abc123']));
        return $exception;
    }
}

class Settings { public static $paypal; }
class Wallet
{
    public static $balance = 0.0;
    public function getDataObject() { return (object) ['currency' => 'USD']; }
    public function getBalance($users_id) { return self::$balance; }
}
class AVideoPlugin
{
    public static $ppv, $live, $subscription = true;
    public static function getObjectData($name) { return Settings::$paypal; }
    public static function loadPluginIfEnabled($name) {
        if ($name === 'YPTWallet') { return new Wallet(); }
        if ($name === 'PayPerView') { return self::$ppv; }
        if ($name === 'PayPerViewLive') { return self::$live; }
        return false;
    }
    public static function isEnabledByName($name) { return $name === 'Subscription' ? self::$subscription : false; }
}
class Config { public function getWebSiteTitle() { return 'Test <b>Site</b>'; } }
class YPTWallet
{
    public static $returnURL = '';
    public static function formatCurrency($v) { return '$' . number_format((float) $v, 2); }
    public static function getUserBalance($id) { return Wallet::$balance; }
    public static function getAddFundsSuccessRedirectURL() { return self::$returnURL; }
    public static function getBillingInterval($days) { return (object) ['paypalFrequency' => 'Month', 'paypalInterval' => 1]; }
}
function isSameDomainAsMyAVideo($url) { return strpos($url, 'https://site.example/') === 0; }
class OldPpvWithoutWalletCheckout {}
class PpvCatalog
{
    public $price = 4.5;
    public function getPaymentDetails($plan, $video, $walletFunding = false) {
        return $plan == 3 && $video == 77 ? ['value' => $this->price, 'description' => 'PPV rental: Movie A | 48h plan | 48 hours',
            'metadata' => ['ppv_plans_id' => '3', 'videos_id' => '77', 'rental_hours' => '48', 'plan_name' => '48h plan', 'video_title' => 'Movie A']] : false;
    }
    public function completeWalletCheckout() { return true; }
}
class PPV_Plans_Users_Videos
{
    public static $owned = false;
    public static function userCanWatchVideo($u, $v) { return self::$owned; }
    public static function getActivePlan($u, $v) { return ['expiration' => '2026-10-23 10:00:00']; }
}
// Production checks class_exists('PPV_Plans_Users_Videos') (an optional plugin class) by its global name.
if (!class_exists('PPV_Plans_Users_Videos', false)) {
    class_alias(PPV_Plans_Users_Videos::class, 'PPV_Plans_Users_Videos');
}
class Subscription
{
    public static $linked = [], $trials = 0;
    public static function getOrCreateSubscription($u, $p, $a) { self::$linked[] = [$u, $p, $a]; }
    public static function onTrial($u, $p) { self::$trials++; }
    public static function getBuyURL() { return 'https://site.example/plans'; }
}
class SubscriptionPlansTable
{
    public static $price = 9.99, $trial = 0;
    public function __construct($id) {}
    public function getId() { return 5; }
    public function getPrice() { return self::$price; }
    public function getName() { return 'Gold'; }
    public function getHow_many_days() { return 30; }
    public function getHow_many_days_trial() { return self::$trial; }
}

class PayPalPaymentProcessor
{
    public static $fulfilled = [], $subscriptionSales = [];
    public static function cents($v) { return intval(round(floatval($v) * 100)); }
    public static function withoutBrowserOrigin(callable $work) { return $work(); }
    public static function fulfillCheckoutSale($wallet, array $checkout, array $sale, $source) {
        self::$fulfilled[] = [$checkout['invoice_number'], $sale, $source];
        PayPalYPT_checkout::update($checkout['id'], ['status' => 'completed', 'sale_id' => $sale['id'], 'fulfillment' => 'credited']);
        return 'credited';
    }
    public static function processSubscriptionSale($wallet, $users, $plans, $agreement, array $ids, $amount, $description, $json, $log = [], $legacy = null) {
        self::$subscriptionSales[] = compact('users', 'plans', 'agreement', 'ids', 'amount');
        return true;
    }
}
class PayPalPaymentMismatchException extends RuntimeException {}
class PayPalCheckoutBusyException extends RuntimeException {}
class PayPalCheckoutRefused extends RuntimeException {}

/** In-memory version of PayPalYPT_checkout with the same public behavior. */
class PayPalYPT_checkout
{
    const APPROVAL_TTL = 10800;
    const FINAL_STATES = ['completed', 'trial', 'canceled', 'expired', 'failed', 'denied', 'refunded'];
    public static $rows = [], $locked = false, $installed = true;
    public static function isInstalled() { return self::$installed; }
    public static function isValidInvoiceNumber($i) { return is_string($i) && preg_match('/^AVPP[0-9A-F]{24}$/D', $i) === 1; }
    public static function isValidProviderId($id) { return is_string($id) && preg_match('/^[A-Za-z0-9_-]{6,64}$/D', $id) === 1; }
    public static function formatAmount($a) { return number_format(round(floatval($a), 2), 2, '.', ''); }
    public static function selectionHash($u, $p, $s, $a, $c, $e, $h) { return hash('sha256', json_encode([$u, $p, $s, self::formatAmount($a), $c, $e, $h])); }
    public static function create($u, $purpose, array $selection, $amount, $currency, $env, $client, $hash = null) {
        $id = count(self::$rows) + 1;
        self::$rows[$id] = ['id' => $id, 'users_id' => $u, 'invoice_number' => 'AVPP' . str_pad(strtoupper(dechex($id)), 24, '0', STR_PAD_LEFT),
            'purpose' => $purpose, 'selection_hash' => $hash ?: self::selectionHash($u, $purpose, $selection, $amount, $currency, $env, $client),
            'selection' => $selection, 'amount' => self::formatAmount($amount), 'currency' => $currency, 'environment' => $env,
            'client_hash' => $client, 'payment_id' => null, 'agreement_id' => null, 'sale_id' => null, 'approval_url' => null,
            'status' => 'creating', 'status_detail' => null, 'fulfillment' => null, 'created' => date('Y-m-d H:i:s')];
        return self::$rows[$id];
    }
    public static function update($id, array $fields) { self::$rows[$id] = array_merge(self::$rows[$id], $fields); return self::$rows[$id]; }
    public static function getById($id) { return self::$rows[$id] ?? false; }
    private static function find(callable $match) { foreach (array_reverse(self::$rows) as $row) { if ($match($row)) { return $row; } } return false; }
    public static function getByInvoice($i) { return self::find(function ($r) use ($i) { return $r['invoice_number'] === $i; }); }
    public static function getByPaymentId($p) { return self::find(function ($r) use ($p) { return $r['payment_id'] === $p; }); }
    public static function getByAgreementId($a) { return self::find(function ($r) use ($a) { return $r['agreement_id'] === $a; }); }
    public static function getReusable($u, $h) { return self::find(function ($r) use ($u, $h) { return $r['users_id'] == $u && $r['selection_hash'] === $h && in_array($r['status'], ['creating', 'created', 'approved', 'pending', 'active'], true); }); }
    public static function getUnfinishedPaid($u, $h) { return self::find(function ($r) use ($u, $h) { return $r['users_id'] == $u && $r['selection_hash'] === $h && in_array($r['status'], ['approved', 'pending'], true); }); }
    public static function lockName($row) { return 'lock-' . $row['invoice_number']; }
    public static function withLock($name, callable $work, $timeout = 10) {
        if (self::$locked) { throw new PayPalCheckoutBusyException('busy'); }
        return $work();
    }
}

$source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/PayPalYPT.php');
$source = substr($source, strpos($source, 'class PayPalYPT extends PluginAbstract'));
$source = str_replace("    public static function restCall(\$method, \$path, \$payload = '', \$apiContext = null)\n",
    "    public static function restCall(\$method, \$path, \$payload = '', \$apiContext = null) { return FakeRest::call(\$method, \$path, \$payload, \$apiContext); }\n"
    . "    public static function restCallReal(\$method, \$path, \$payload = '', \$apiContext = null)\n", $source, $restReplaced);
$source = str_replace("    public static function getApiContext(\$requestId = null, \$settings = null)\n",
    "    public static function getApiContext(\$requestId = null, \$settings = null) { return \$requestId; }\n"
    . "    public static function getApiContextReal(\$requestId = null, \$settings = null)\n", $source, $contextReplaced);
$source = str_replace("file_get_contents('php://input')", 'FakeRest::$input', $source, $inputReplaced);
$source = str_replace("function_exists('getallheaders') ? (getallheaders() ?: []) : []", 'FakeRest::$headers', $source, $headersReplaced);
if ($restReplaced !== 1 || $contextReplaced !== 1 || $inputReplaced < 1 || $headersReplaced !== 1) {
    throw new RuntimeException('PayPalYPT.php changed: update the test fixture');
}
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use Exception; use Throwable; use InvalidArgumentException; use stdClass;' . $source);

class SubscriptionApproval extends PayPalYPT
{
    public function setUpSubscription($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = 'USD', $frequency = 'Month', $interval = 1, $name = 'Base Agreement') {
        return new class {
            public function getApprovalLink() { return 'https://www.sandbox.paypal.com/checkoutnow?token=EC-PRIVATE-APPROVAL'; }
        };
    }
}

class PayPalCheckoutTest extends TestCase
{
    protected function setUp(): void {
        global $global, $config, $mysqlDatabase;
        $global = ['webSiteRootURL' => 'https://site.example/', 'systemRootPath' => '/tmp/'];
        $config = new Config();
        $mysqlDatabase = 'test';
        Settings::$paypal = (object) ['ClientID' => 'OWN-app', 'ClientSecret' => 's', 'disableSandbox' => false, 'webhookId' => 'WH-SAVED00001'];
        FakeRest::$responses = FakeRest::$calls = FakeRest::$logs = [];
        FakeRest::$input = '';
        FakeRest::$headers = [];
        PayPalYPT_checkout::$rows = [];
        PayPalYPT_checkout::$locked = false;
        PayPalYPT_checkout::$installed = true;
        PayPalPaymentProcessor::$fulfilled = PayPalPaymentProcessor::$subscriptionSales = [];
        Subscription::$linked = [];
        Subscription::$trials = 0;
        SubscriptionPlansTable::$trial = 0;
        Wallet::$balance = 0.0;
        AVideoPlugin::$ppv = new PpvCatalog();
        AVideoPlugin::$subscription = true;
        YPTWallet::$returnURL = '';
        PPV_Plans_Users_Videos::$owned = false;
    }

    private function checkout($purpose = 'wallet', $amount = 10, $selection = ['description' => 'Wallet credit']) {
        $row = PayPalYPT_checkout::create(7, $purpose, $selection, $amount, 'USD', 'sandbox', PayPalYPT::getClientHash());
        return PayPalYPT_checkout::update($row['id'], ['payment_id' => 'PAYID-TEST123', 'status' => 'created', 'approval_url' => 'https://www.sandbox.paypal.com/checkoutnow?token=EC-1']);
    }
    private function payment($row, $state, $payerId = '', $sale = null, $total = null) {
        $data = ['id' => 'PAYID-TEST123', 'intent' => 'sale', 'state' => $state, 'payer' => ['payer_info' => ['payer_id' => $payerId]],
            'transactions' => [['invoice_number' => $row['invoice_number'], 'amount' => ['total' => $total ?? $row['amount'], 'currency' => 'USD']]]];
        if ($sale) {
            $data['transactions'][0]['related_resources'] = [['sale' => ['id' => 'SALE-0001', 'state' => $sale, 'reason_code' => $sale === 'pending' ? 'ECHECK' : null,
                'amount' => ['total' => $total ?? $row['amount'], 'currency' => 'USD']]]];
        }
        return $data;
    }

    public function testApprovedPaymentIsExecutedOnceWithAStableRequestIdAndCredited(): void {
        $row = $this->checkout();
        FakeRest::on('GET /v1/payments/payment/PAYID-TEST123', $this->payment($row, 'created', 'PAYER1'));
        FakeRest::on('GET /v1/payments/payment/PAYID-TEST123', $this->payment($row, 'approved', 'PAYER1', 'completed'));
        // The execute answer is not trusted: an incomplete body must not mark a charged payment as a mismatch.
        FakeRest::on('POST /v1/payments/payment/PAYID-TEST123/execute', ['id' => 'PAYID-TEST123', 'state' => 'approved']);
        $result = PayPalYPT::reconcileCheckout($row, 'return');
        $this->assertSame('completed', $result['status']);
        $execute = array_values(array_filter(FakeRest::$calls, function ($c) { return $c[0] === 'POST'; }));
        $this->assertCount(1, $execute);
        $this->assertSame('{"payer_id":"PAYER1"}', $execute[0][2]);
        $this->assertSame('avideo-execute-' . $row['invoice_number'], $execute[0][3]);
        $this->assertCount(1, PayPalPaymentProcessor::$fulfilled);
        $this->assertSame('SALE-0001', PayPalPaymentProcessor::$fulfilled[0][1]['id']);
        // Webhook, IPN and status checks after completion do not call PayPal again.
        FakeRest::$calls = [];
        PayPalYPT::reconcileCheckout($row, 'webhook');
        $this->assertSame([], FakeRest::$calls);
    }
    public function testNotApprovedYetChangesNothingAndExpiresAfterTheApprovalWindow(): void {
        $row = $this->checkout();
        FakeRest::on('GET /v1/payments/payment/', $this->payment($row, 'created'));
        $this->assertSame('created', PayPalYPT::reconcileCheckout($row, 'status')['status']);
        PayPalYPT_checkout::$rows[$row['id']]['created'] = date('Y-m-d H:i:s', time() - 4 * 3600);
        $this->assertSame('expired', PayPalYPT::reconcileCheckout($row, 'status')['status']);
        $this->assertSame([], PayPalPaymentProcessor::$fulfilled);
    }
    public function testPendingSaleIsNeverCreditedUntilPayPalCompletesIt(): void {
        $row = $this->checkout();
        FakeRest::on('GET /v1/payments/payment/', $this->payment($row, 'approved', 'PAYER1', 'pending'));
        $result = PayPalYPT::reconcileCheckout($row, 'return');
        $this->assertSame('pending', $result['status']);
        $this->assertSame('ECHECK', $result['status_detail']);
        $this->assertSame([], PayPalPaymentProcessor::$fulfilled);
        FakeRest::$responses = [];
        FakeRest::on('GET /v1/payments/payment/', $this->payment($row, 'approved', 'PAYER1', 'completed'));
        $this->assertSame('completed', PayPalYPT::reconcileCheckout($row, 'webhook')['status']);
        $this->assertCount(1, PayPalPaymentProcessor::$fulfilled);
    }
    public function testDeniedRefundedAndReversedSalesAreNeverCredited(): void {
        foreach (['denied' => 'denied', 'refunded' => 'refunded', 'partially_refunded' => 'refunded', 'reversed' => 'refunded'] as $sale => $status) {
            PayPalYPT_checkout::$rows = [];
            FakeRest::$responses = [];
            $row = $this->checkout();
            FakeRest::on('GET /v1/payments/payment/', $this->payment($row, 'approved', 'PAYER1', $sale));
            $this->assertSame($status, PayPalYPT::reconcileCheckout($row, 'webhook')['status'], $sale);
        }
        $this->assertSame([], PayPalPaymentProcessor::$fulfilled);
    }
    public function testAmountInvoiceOrCurrencyMismatchIsRefusedAndLogged(): void {
        $row = $this->checkout();
        FakeRest::on('GET /v1/payments/payment/', $this->payment($row, 'approved', 'PAYER1', 'completed', '1.00'));
        $result = PayPalYPT::reconcileCheckout($row, 'return');
        $this->assertSame('failed', $result['status']);
        $this->assertSame('mismatch', $result['status_detail']);
        $this->assertSame([], PayPalPaymentProcessor::$fulfilled);
        $this->assertContains('security', array_column(FakeRest::$logs, 0));
    }
    public function testAlreadyExecutedPaymentIsReadInsteadOfFailing(): void {
        $row = $this->checkout();
        FakeRest::on('GET /v1/payments/payment/PAYID-TEST123', $this->payment($row, 'created', 'PAYER1'));
        FakeRest::on('GET /v1/payments/payment/PAYID-TEST123', $this->payment($row, 'approved', 'PAYER1', 'completed'));
        FakeRest::on('POST /v1/payments/payment/PAYID-TEST123/execute', FakeRest::paypalError(400, 'PAYMENT_ALREADY_DONE'));
        $this->assertSame('completed', PayPalYPT::reconcileCheckout($row, 'status')['status']);
        $this->assertCount(1, PayPalPaymentProcessor::$fulfilled);
    }
    public function testDeclinedFundingFailsWithoutCreditAndTransientErrorsRetry(): void {
        $row = $this->checkout();
        FakeRest::on('GET /v1/payments/payment/', $this->payment($row, 'created', 'PAYER1'));
        FakeRest::on('POST /v1/payments/payment/PAYID-TEST123/execute', FakeRest::paypalError(400, 'INSTRUMENT_DECLINED'));
        $result = PayPalYPT::reconcileCheckout($row, 'return');
        $this->assertSame('failed', $result['status']);
        $this->assertSame('INSTRUMENT_DECLINED', $result['status_detail']);

        PayPalYPT_checkout::$rows = [];
        FakeRest::$responses = [];
        $row = $this->checkout();
        FakeRest::on('GET /v1/payments/payment/', $this->payment($row, 'created', 'PAYER1'));
        FakeRest::on('POST /v1/payments/payment/PAYID-TEST123/execute', FakeRest::paypalError(503, 'INTERNAL_SERVICE_ERROR'));
        try {
            PayPalYPT::reconcileCheckout($row, 'return');
            $this->fail('A transient failure must be retried');
        } catch (\PayPal\Exception\PayPalConnectionException $exception) {
            $this->assertSame('approved', PayPalYPT_checkout::getById($row['id'])['status'], 'Approved: never offered a second payment');
        }
    }
    public function testWholePlanAndOlderContentPluginsStillFundTheWallet(): void {
        YPTWallet::$returnURL = 'https://site.example/plugin/PayPerView/page/buy.php?plans_id=3';
        FakeRest::on('POST /v1/payments/payment', ['id' => 'PAYID-NEW0001', 'links' => [['rel' => 'approval_url', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=EC-9']]]);
        $result = PayPalYPT::startOneTimeCheckout(7, ['plugin' => 'PayPerView', 'plans_id' => 3, 'videos_id' => 0, 'value' => '4.50']);
        $row = PayPalYPT_checkout::getByInvoice($result['invoice']);
        $this->assertSame('wallet', $row['purpose'], 'No movie: the wallet is funded and the buyer completes the plan purchase');
        $this->assertSame('4.50', $row['amount']);
        $status = PayPalYPT::getPublicCheckoutStatus(PayPalYPT_checkout::update($row['id'], ['status' => 'completed', 'fulfillment' => 'credited']));
        $this->assertSame(YPTWallet::$returnURL, $status['continue_url'], 'Back to the purchase page');

        $this->setUp();
        AVideoPlugin::$ppv = new OldPpvWithoutWalletCheckout();
        YPTWallet::$returnURL = 'https://attacker.example/steal';
        FakeRest::on('POST /v1/payments/payment', ['id' => 'PAYID-NEW0001', 'links' => [['rel' => 'approval_url', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=EC-9']]]);
        $result = PayPalYPT::startOneTimeCheckout(7, ['plugin' => 'PayPerView', 'plans_id' => 3, 'videos_id' => 77, 'value' => '4.50']);
        $row = PayPalYPT_checkout::getByInvoice($result['invoice']);
        $this->assertSame('wallet', $row['purpose']);
        $this->assertArrayNotHasKey('return_url', $row['selection'], 'A foreign return URL is ignored');
    }
    public function testChangedAppOrEnvironmentCannotConfirmAnOlderCheckout(): void {
        $row = $this->checkout();
        Settings::$paypal->disableSandbox = true;
        $this->expectException(RuntimeException::class);
        PayPalYPT::reconcileCheckout($row, 'status');
    }

    public function testPpvCheckoutIsPricedFromTheCatalogAndTheWalletPaysFirst(): void {
        Wallet::$balance = 1.25;
        FakeRest::on('POST /v1/payments/payment', ['id' => 'PAYID-NEW0001', 'links' => [['rel' => 'approval_url', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=EC-9']]]);
        $result = PayPalYPT::startOneTimeCheckout(7, ['plugin' => 'PayPerView', 'plans_id' => 3, 'videos_id' => 77, 'value' => '0.01']);
        $this->assertSame('https://www.sandbox.paypal.com/checkoutnow?token=EC-9', $result['approval_url']);
        $row = PayPalYPT_checkout::getByInvoice($result['invoice']);
        $this->assertSame('3.25', $row['amount'], 'Catalog price 4.50 minus 1.25 balance, not the browser value');
        $this->assertSame('ppv', $row['purpose']);
        $this->assertSame(77, $row['selection']['videos_id']);
        $this->assertSame('4.50', $row['selection']['ppv_value']);
        $payload = json_decode(FakeRest::$calls[0][2], true);
        $this->assertSame('avideo-create-' . $result['invoice'], FakeRest::$calls[0][3]);
        $this->assertSame($result['invoice'], $payload['transactions'][0]['invoice_number']);
        $this->assertSame('3.25', $payload['transactions'][0]['amount']['total']);
        $this->assertSame('PPV rental: Movie A', $payload['transactions'][0]['item_list']['items'][0]['name']);
        $this->assertStringContainsString('redirect_url.php?invoice=' . $result['invoice'], $payload['redirect_urls']['return_url']);
    }
    public function testDoubleClickReusesTheSamePayPalPayment(): void {
        FakeRest::on('POST /v1/payments/payment', ['id' => 'PAYID-NEW0001', 'links' => [['rel' => 'approval_url', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=EC-9']]]);
        $first = PayPalYPT::startOneTimeCheckout(7, ['value' => '10']);
        $second = PayPalYPT::startOneTimeCheckout(7, ['value' => '10']);
        $this->assertSame($first, $second);
        $this->assertCount(1, FakeRest::$calls);
        $this->assertCount(1, PayPalYPT_checkout::$rows);
    }
    public function testAPaidButUnfinishedPurchaseIsNeverChargedAgain(): void {
        FakeRest::on('POST /v1/payments/payment', ['id' => 'PAYID-NEW0001', 'links' => [['rel' => 'approval_url', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=EC-9']]]);
        $first = PayPalYPT::startOneTimeCheckout(7, ['plugin' => 'PayPerView', 'plans_id' => 3, 'videos_id' => 77]);
        PayPalYPT_checkout::update(1, ['status' => 'pending']);
        $second = PayPalYPT::startOneTimeCheckout(7, ['plugin' => 'PayPerView', 'plans_id' => 3, 'videos_id' => 77]);
        $this->assertArrayNotHasKey('approval_url', $second);
        $this->assertSame($first['invoice'], $second['invoice']);
        $this->assertCount(1, FakeRest::$calls);
    }
    public function testRefusalsNeedNoPayPalCall(): void {
        $cases = [
            [['plugin' => 'PayPerView', 'plans_id' => 3, 'videos_id' => 77], function () { Wallet::$balance = 10; }],
            [['plugin' => 'PayPerView', 'plans_id' => 3, 'videos_id' => 77], function () { PPV_Plans_Users_Videos::$owned = true; }],
            [['plugin' => 'PayPerView', 'plans_id' => 9, 'videos_id' => 77], function () {}],
            [['value' => '-5'], function () {}],
            [['value' => '10'], function () { PayPalYPT_checkout::$installed = false; }],
        ];
        foreach ($cases as $i => [$request, $prepare]) {
            $this->setUp();
            $prepare();
            try {
                PayPalYPT::startOneTimeCheckout(7, $request);
                $this->fail("case {$i} must be refused");
            } catch (PayPalCheckoutRefused $refused) {
                $this->assertSame([], FakeRest::$calls, "case {$i}");
            } catch (RuntimeException $unexpected) {
                $this->fail("case {$i}: " . $unexpected->getMessage());
            }
        }
    }
    public function testCreateFailureNeverLosesTheIdempotencyKey(): void {
        FakeRest::on('POST /v1/payments/payment', FakeRest::paypalError(503, 'INTERNAL_SERVICE_ERROR'));
        try { PayPalYPT::startOneTimeCheckout(7, ['value' => '10']); $this->fail('refused'); } catch (PayPalCheckoutRefused $refused) {}
        $this->assertSame('creating', PayPalYPT_checkout::getById(1)['status']);
        FakeRest::$responses = [];
        FakeRest::on('POST /v1/payments/payment', ['id' => 'PAYID-NEW0001', 'links' => [['rel' => 'approval_url', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=EC-9']]]);
        PayPalYPT::startOneTimeCheckout(7, ['value' => '10']);
        $this->assertCount(1, PayPalYPT_checkout::$rows);
        $this->assertSame(FakeRest::$calls[0][3], FakeRest::$calls[1][3], 'The retry reuses the PayPal-Request-Id');

        $this->setUp();
        FakeRest::on('POST /v1/payments/payment', FakeRest::paypalError(400, 'VALIDATION_ERROR'));
        try { PayPalYPT::startOneTimeCheckout(7, ['value' => '10']); } catch (PayPalCheckoutRefused $refused) {}
        $this->assertSame('failed', PayPalYPT_checkout::getById(1)['status']);
    }
    public function testBusyBuyerLockIsReportedWithoutCreatingAPayment(): void {
        PayPalYPT_checkout::$locked = true;
        $this->expectException(PayPalCheckoutRefused::class);
        PayPalYPT::startOneTimeCheckout(7, ['value' => '10']);
    }

    private function subscriptionCheckout() {
        $hash = 'subscription-hash';
        $row = PayPalYPT_checkout::create(7, 'subscription', ['plans_id' => 5, 'trial_days' => SubscriptionPlansTable::$trial], 9.99, 'USD', 'sandbox', PayPalYPT::getClientHash(), $hash);
        return PayPalYPT_checkout::update($row['id'], ['payment_id' => 'EC-TOKEN0001', 'status' => 'created']);
    }
    public function testSubscriptionApprovalTokenIsSavedForRecoveryButNeverLogged(): void {
        $result = (new SubscriptionApproval())->startSubscriptionCheckout(7, 5);
        $row = PayPalYPT_checkout::getByInvoice($result['invoice']);
        $this->assertSame('EC-PRIVATE-APPROVAL', $row['payment_id']);
        $logs = json_encode(FakeRest::$logs);
        $this->assertStringContainsString($result['invoice'], $logs);
        $this->assertStringNotContainsString('EC-PRIVATE-APPROVAL', $logs);
    }
    public function testSdkLoggingCannotWriteCredentialBearingUrlsOrErrorBodies(): void {
        $config = PayPalYPT::getSdkConfig();
        $manager = \PayPal\Core\PayPalConfigManager::getInstance();
        $previous = $manager->getConfigHashmap();
        $logFile = tempnam(sys_get_temp_dir(), 'paypal-log-');
        try {
            $config['log.FileName'] = $logFile;
            $manager->addConfigs($config);
            $logger = \PayPal\Core\PayPalLoggingManager::getInstance();
            $logger->info('POST https://api.example/EC-PRIVATE-APPROVAL/agreement-execute');
            $logger->error('{"payer":"private@example.invalid"}');
            $this->assertSame('', file_get_contents($logFile));
        } finally {
            $manager->addConfigs($previous);
            unlink($logFile);
        }
        $error = new \PayPal\Exception\PayPalConnectionException('https://api.example/EC-PRIVATE-APPROVAL/agreement-execute', 'Failure at EC-PRIVATE-APPROVAL', 500);
        $error->setData('{"name":"INTERNAL_ERROR","debug_id":"abc123","payer":"private@example.invalid"}');
        $description = PayPalYPT::describeApiError($error);
        $this->assertStringContainsString('http=500 name=INTERNAL_ERROR debug_id=abc123', $description);
        $this->assertStringNotContainsString('EC-PRIVATE-APPROVAL', $description);
        $this->assertStringNotContainsString('private@example.invalid', $description);
    }
    public function testSubscriptionIsLinkedAtApprovalButFulfilledOnlyByACompletedSale(): void {
        $row = $this->subscriptionCheckout();
        FakeRest::on('POST /v1/payments/billing-agreements/EC-TOKEN0001/agreement-execute', ['id' => 'I-AGREEMENT01', 'state' => 'Active']);
        FakeRest::on('GET /v1/payments/billing-agreements/I-AGREEMENT01/transactions', ['agreement_transaction_list' => [
            ['transaction_id' => 'I-AGREEMENT01', 'status' => 'Created'],
        ]]);
        $result = PayPalYPT::reconcileCheckout($row, 'return');
        $this->assertSame('active', $result['status']);
        $this->assertSame([[7, 5, 'I-AGREEMENT01']], Subscription::$linked);
        $this->assertSame([], PayPalPaymentProcessor::$subscriptionSales, 'An active agreement alone grants nothing');
        FakeRest::$responses = [];
        FakeRest::on('GET /v1/payments/billing-agreements/I-AGREEMENT01/transactions', ['agreement_transaction_list' => [
            ['transaction_id' => 'I-AGREEMENT01', 'status' => 'Created'],
            ['transaction_id' => 'SALE-SETUP01', 'status' => 'Completed', 'amount' => ['value' => '9.99', 'currency' => 'USD']],
        ]]);
        PayPalYPT::reconcileCheckout($result, 'status');
        $this->assertCount(1, PayPalPaymentProcessor::$subscriptionSales);
        $this->assertSame(['SALE-SETUP01'], PayPalPaymentProcessor::$subscriptionSales[0]['ids'], 'Same ledger key as webhook resource.id and IPN txn_id');
        $this->assertSame('I-AGREEMENT01', PayPalPaymentProcessor::$subscriptionSales[0]['agreement']);
    }
    public function testSubscriptionNotApprovedYetIsLeftForALaterCheck(): void {
        $row = $this->subscriptionCheckout();
        FakeRest::on('POST /v1/payments/billing-agreements/', FakeRest::paypalError(400, 'INVALID_TOKEN'));
        $this->assertSame('created', PayPalYPT::reconcileCheckout($row, 'status')['status']);
        $this->assertSame([], Subscription::$linked);
    }
    public function testTrialStartsWithoutAPaymentOrCredit(): void {
        SubscriptionPlansTable::$trial = 7;
        $row = $this->subscriptionCheckout();
        FakeRest::on('POST /v1/payments/billing-agreements/', ['id' => 'I-AGREEMENT02', 'state' => 'Active']);
        $result = PayPalYPT::reconcileCheckout($row, 'return');
        $this->assertSame('trial', $result['status']);
        $this->assertSame(1, Subscription::$trials);
        $this->assertSame([], PayPalPaymentProcessor::$subscriptionSales);
    }
    public function testInactiveAgreementIsNotLinked(): void {
        $row = $this->subscriptionCheckout();
        FakeRest::on('POST /v1/payments/billing-agreements/', ['id' => 'I-AGREEMENT03', 'state' => 'Pending']);
        $this->assertSame('failed', PayPalYPT::reconcileCheckout($row, 'return')['status']);
        $this->assertSame([], Subscription::$linked);
    }

    private function signedDelivery($body) {
        FakeRest::$input = $body;
        FakeRest::$headers = ['Paypal-Auth-Algo' => 'SHA256withRSA', 'Paypal-Cert-Url' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-1',
            'Paypal-Transmission-Id' => 'tid-1', 'Paypal-Transmission-Sig' => 'sig==', 'Paypal-Transmission-Time' => '2026-10-21T10:00:00Z'];
    }
    public function testWebhookSignatureIsVerifiedWithTheOriginalBodyAndSavedWebhookId(): void {
        $body = '{"id":"WH-1AB","event_type":"PAYMENT.SALE.COMPLETED","resource":{"id":"S1","amount":{"total":"10.00"}}}';
        $this->signedDelivery($body);
        FakeRest::on('POST /v1/notifications/verify-webhook-signature', ['verification_status' => 'SUCCESS']);
        $result = PayPalYPT::validateWebhook();
        $this->assertSame('WH-1AB', $result->webhook_event->id);
        $sent = FakeRest::$calls[0][2];
        $this->assertStringEndsWith(',"webhook_event":' . $body . '}', $sent, 'The signed bytes are sent unchanged');
        $this->assertSame('WH-SAVED00001', json_decode($sent, true)['webhook_id']);
        $this->assertCount(1, FakeRest::$calls, 'No webhook listing or creation during a delivery');
    }
    public function testUnsignedForgedOrForeignCertificateDeliveriesAreRejectedWithoutAnApiCall(): void {
        $body = '{"id":"WH-1AB","event_type":"PAYMENT.SALE.COMPLETED"}';
        $this->signedDelivery($body);
        unset(FakeRest::$headers['Paypal-Transmission-Sig']);
        $this->assertFalse(PayPalYPT::validateWebhook());
        $this->signedDelivery($body);
        FakeRest::$headers['Paypal-Cert-Url'] = 'https://attacker.example/cert.pem';
        $this->assertFalse(PayPalYPT::validateWebhook());
        $this->signedDelivery('not json');
        $this->assertFalse(PayPalYPT::validateWebhook());
        $this->assertSame([], FakeRest::$calls);
        $this->signedDelivery($body);
        FakeRest::on('POST /v1/notifications/verify-webhook-signature', ['verification_status' => 'FAILURE']);
        $this->assertFalse(PayPalYPT::validateWebhook());
    }
    public function testWebhookWithoutSavedIdUsesAReadOnlyLookupAndNeverCreatesOne(): void {
        Settings::$paypal->webhookId = '';
        $this->signedDelivery('{"id":"WH-1AB","event_type":"PAYMENT.SALE.COMPLETED"}');
        FakeRest::on('GET /v1/notifications/webhooks', ['webhooks' => [['id' => 'WH-OTHER0001', 'url' => 'https://other.example/x'],
            ['id' => 'WH-FOUND0001', 'url' => 'https://site.example/plugin/PayPalYPT/webhook.php']]]);
        FakeRest::on('POST /v1/notifications/verify-webhook-signature', ['verification_status' => 'SUCCESS']);
        PayPalYPT::validateWebhook();
        $this->assertSame('WH-FOUND0001', json_decode(FakeRest::$calls[1][2], true)['webhook_id']);
        $this->assertSame(['GET', 'POST'], array_column(FakeRest::$calls, 0));
        FakeRest::$responses = [];
        FakeRest::$calls = [];
        FakeRest::on('GET /v1/notifications/webhooks', ['webhooks' => []]);
        $this->expectException(RuntimeException::class);
        PayPalYPT::validateWebhook();
    }
    public function testPublicStatusNeverExposesPayPalOrPayerData(): void {
        $row = $this->checkout('ppv', 3.25, ['videos_id' => 77, 'description' => 'PPV rental: Movie A', 'price' => '4.50', 'balance_applied' => '1.25', 'rental_hours' => 48]);
        $row = PayPalYPT_checkout::update($row['id'], ['status' => 'completed', 'fulfillment' => 'rented', 'sale_id' => 'SALE-0001']);
        $status = PayPalYPT::getPublicCheckoutStatus($row);
        $this->assertSame('https://site.example/video/77?autoplay=1', $status['continue_url']);
        $this->assertSame('$1.25', $status['balance_applied']);
        $this->assertSame('2026-10-23 10:00:00', $status['access_until'], 'The rental expiration is shown');
        $json = json_encode($status);
        foreach (['SALE-0001', 'PAYID-TEST123', 'client_hash', 'payer'] as $hidden) {
            $this->assertStringNotContainsString($hidden, $json);
        }
    }
}
