<?php

namespace Tests\Security\PaymentWebhookIdempotency;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

// Exercise the production transaction and transfer methods without a live site's database.
$root = dirname(__DIR__, 2);
$source = file_get_contents($root . '/plugin/YPTWallet/YPTWallet.php');
$start = strpos($source, '    private static $paymentTransactionActive');
$end = strpos($source, '    public function saveBalance(', $start);
$transferStart = strpos($source, '    public static function transferBalance($');
$transferEnd = strpos($source, '    public static function transferAndSplitBalance', $transferStart);
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use Throwable; class Payments {'
    . substr($source, $start, $end - $start)
    . substr($source, $transferStart, $transferEnd - $transferStart)
    . 'public function getDataObject() { return (object)["virtual_currency_enable" => false, "currency" => "USD"]; }
       public function getOrCreateWallet($id) { return new Wallet($id); }
       public static function getAddFundsSuccessRedirectURL() { return "https://example.test/paid"; }}');
class YPTWallet extends Payments {}
$source = file_get_contents($root . '/plugin/YPTWallet/Objects/Wallet_log.php');
$start = strpos($source, '    static function hasProcessedPayment(');
$end = strpos($source, '    static function addLog(', $start);
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; class WalletLog {' . substr($source, $start, $end - $start)
    . 'static function addLog($wallet, $value, $balance, $description="", $json="{}", $status="success", $type="", $information="") {
        $db = $GLOBALS["global"]["mysqli"];
        if ($type === "paymentProcessed" && $db->failMarker) { return false; }
        $db->logs[] = compact("wallet", "value", "description", "json", "status", "type", "information");
        return count($db->logs);
    }}');
$source = file_get_contents($root . '/plugin/StripeYPT/StripeYPT.php');
$start = strpos($source, '    public static function getPaymentIds(');
$end = strpos($source, '    /**', $start);
eval('namespace ' . __NAMESPACE__ . '; class StripeIds {' . substr($source, $start, $end - $start) . '}');
class PluginAbstract {}
$stripePpvSource = file_get_contents($root . '/plugin/StripeYPT/StripeYPT.php');
$stripePpvSource = substr($stripePpvSource, strpos($stripePpvSource, 'class StripeYPT extends'));
$stripePpvSource = str_replace(['\\Stripe\\Charge', '\\Stripe\\Exception\\PermissionException'], ['StripeCharge', 'ChargePermissionException'], $stripePpvSource);
eval('namespace ' . __NAMESPACE__ . '; ' . $stripePpvSource);
class ChargePermissionException extends RuntimeException {}
class StripeCharge
{
    public static $amountRefunded = 0, $denyRead = false;
    public static function retrieve($id) {
        if (self::$denyRead) { throw new ChargePermissionException('No Charges: Read'); }
        return (object) ['amount' => 199, 'amount_refunded' => self::$amountRefunded, 'status' => 'succeeded'];
    }
}
class PpvCheckout
{
    public static $fail = false;
    public function completeWalletCheckout($user, $plan, $video, $value, $hours) {
        if (!Payments::transferBalance($user, 8, $value, 'PPV rental', true)) { return false; }
        ++$GLOBALS['global']['mysqli']->renewals;
        return !self::$fail;
    }
}
class LiveCheckout
{
    public function completeWalletCheckout($user, $schedule, $value, $ends) {
        return (new PpvCheckout())->completeWalletCheckout($user, 0, 0, $value, 0);
    }
}

function _error_log($message, $level = '') { $GLOBALS['global']['mysqli']->errorLogs[] = is_string($message) ? $message : json_encode($message); }
class AVideoLog {
    public static $ERROR = 'error';
    public static $WARNING = 'warning';
    public static $SECURITY = 'security';
}
function mysqlBeginTransaction() { return $GLOBALS['global']['mysqli']->begin_transaction(); }
function mysqlCommit() { return $GLOBALS['global']['mysqli']->commit(); }
function mysqlRollback() { return $GLOBALS['global']['mysqli']->rollback(); }

class User {
    public static function isAdmin() { return true; }
    public static function isLogged() { return false; }
    public static function idExists($id) { return true; }
    public static function getNameIdentificationById($id) { return 'User'; }
}
class ObjectYPT {
    public static function clearSessionCache() {}
}
$walletSource = file_get_contents($root . '/plugin/YPTWallet/Objects/Wallet.php');
$start = strpos($walletSource, '    static function getFromUserForUpdate');
$end = strpos($walletSource, '    static function getOrCreateFromUser', $start);
eval('namespace ' . __NAMESPACE__ . '; trait WalletRowLock {' . substr($walletSource, $start, $end - $start) . '}');
class Wallet {
    use WalletRowLock;
    public static function getTableName() { return 'wallet'; }
    private $id;
    private $balance;
    public function __construct($id) {
        $this->id = $id;
        $this->balance = $GLOBALS['global']['mysqli']->balances[$id] ?? 0;
    }
    public function getId() { return $this->id; }
    public function getBalance() { return $this->balance; }
    public function setBalance($value) { $this->balance = (float) $value; }
    public function save() {
        $db = $GLOBALS['global']['mysqli'];
        if ($db->failCredit) { return false; }
        $db->balances[$this->id] = $this->balance;
        return $this->id;
    }

}
class sqlDAL {
    public static function readSql($sql, $formats, $values, $refresh) {
        if (!$refresh || strpos($sql, 'FOR UPDATE') === false) {
            throw new RuntimeException('Payment reads must lock fresh wallet rows');
        }
        return ['id' => $values[0], 'balance' => $GLOBALS['global']['mysqli']->balances[$values[0]] ?? 0];
    }
    public static function fetchAssoc($result) { return $result; }
    public static function close($result) {}
}
class Database {
    public $balances = [7 => 0, 8 => 0];
    public $logs = [];
    public $renewals = 0;
    public $commits = 0;
    public $released = 0;
    public $busy = false;
    public $failMarker = false;
    public $failCredit = false;
    public $failCommit = false;
    public $failBtcInvoice = false;
    public $failBtcPayment = false;
    public $errorLogs = [];
    public $error = '';
    private $snapshot;
    private $savepoint;
    public function prepare($sql) { return new Statement($this, $sql); }
    public function begin_transaction() {
        $this->snapshot = [$this->balances, $this->logs, $this->renewals];
        return true;
    }
    public function rollback() {
        [$this->balances, $this->logs, $this->renewals] = $this->snapshot;
        return true;
    }
    public function query($sql) {
        if ($sql === 'SAVEPOINT avideo_payment_step') {
            $this->savepoint = [$this->balances, $this->logs, $this->renewals];
        } elseif ($sql === 'ROLLBACK TO SAVEPOINT avideo_payment_step') {
            [$this->balances, $this->logs, $this->renewals] = $this->savepoint;
        } elseif ($sql !== 'RELEASE SAVEPOINT avideo_payment_step') {
            throw new RuntimeException("Unexpected SQL: {$sql}");
        }
        return true;
    }
    public function commit() {
        if ($this->failCommit) { return false; }
        ++$this->commits;
        return true;
    }
}
class Statement {
    private $db;
    private $sql;
    private $values;
    private $boundResult;
    public function __construct($db, $sql) { $this->db = $db; $this->sql = $sql; }
    public function bind_param($types, &...$values) { $this->values = $values; }
    public function execute() {
        if (strpos($this->sql, 'RELEASE_LOCK') !== false) { ++$this->db->released; }
        return true;
    }
    public function bind_result(&$value) { $this->boundResult =& $value; }
    public function fetch() {
        $row = $this->fetch_assoc();
        if ($row === null) { return null; }
        $this->boundResult = reset($row);
        return true;
    }
    public function close() {}
    public function get_result() { return $this; }
    public function fetch_assoc() {
        if (strpos($this->sql, 'GET_LOCK') !== false) { return ['acquired' => !$this->db->busy]; }
        if (strpos($this->sql, 'RELEASE_LOCK') !== false) { ++$this->db->released; return null; }
        foreach ($this->db->logs as $id => $log) {
            if (strpos($this->sql, "type = 'paymentProcessed'") !== false) {
                if ($log['type'] === 'paymentProcessed' && $log['information'] === $this->values[0]) {
                    return ['id' => $id + 1];
                }
            } elseif ($log['wallet'] === $this->values[0] && $log['type'] === 'addBalance'
                && $log['status'] === 'success'
                && strpos($log['description'], rtrim($this->values[1], '%')) === 0
                && strpos($log['json'], $this->values[2]) !== false) {
                return ['id' => $id + 1];
            }
        }
        return null;
    }
}

// Run the PayPal handler with the real payment helper and origin guard, without site configuration.
$paypalGuardSource = file_get_contents($root . '/objects/functionsSecurity.php');
$paypalGuardStart = strpos($paypalGuardSource, 'function isUntrustedRequest(');
$paypalGuardEnd = strpos($paypalGuardSource, 'function forbidIfItIsNotMyUsersId(', $paypalGuardStart);
eval('namespace ' . __NAMESPACE__ . ';' . substr($paypalGuardSource, $paypalGuardStart, $paypalGuardEnd - $paypalGuardStart));
function isCommandLineInterface() { return false; }
function getRemoteAddrFromServerArray($server) { return $server['REMOTE_ADDR'] ?? ''; }
function getRealIpAddr() { return '203.0.113.10'; }
function isLoopbackIP($ip) { return false; }
function requestComesFromSameDomainAsMyAVideo() { return false; }
function isAVideoUserAgent() { return false; }
function header($value) {}
function http_response_code($code) { PayPalDelivery::$status = $code; }
function _json_decode($value) { return json_decode($value); }
function xss_esc($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
class PayPalDelivery {
    public static $status = 200;
    public static $verified = true;
    public static $agreement = ['users_id' => 7, 'subscriptions_plans_id' => 3];
    public static $failRenewal = false;
    public static $ignoreLegacy = false;
    public static $filterRequest = false;
    public static $lastAgreement;
    public static $webhook;
}
class PayPalDeliveryStopped extends RuntimeException {}
class AVideoPlugin {
    public static $ppv;
    public static $walletEnabled = true;
    public static $subscriptionEnabled = true;
    public static function loadPluginIfEnabled($name) {
        if ($name === 'YPTWallet') { return self::$walletEnabled ? new Payments() : false; }
        if ($name === 'PayPerView') { return self::$ppv; }
        if ($name === 'PayPerViewLive') { return new LiveCheckout(); }
        return $name === 'Subscription' && !self::$subscriptionEnabled ? false : new self();
    }
    public static function getObjectData($name) { return (object)['currency' => 'USD']; }
}
class PayPalYPT {
    public static $deliveries = [];
    public static function IPNcheck() { return PayPalDelivery::$verified; }
    public static function validateWebhook() { return PayPalDelivery::$verified ? PayPalDelivery::$webhook : false; }
    public static function getWebhookURL() { return 'https://example.test/paypal-webhook'; }
    public static function recordDelivery($channel, $type) { self::$deliveries[] = [$channel, $type]; }
    public static function describeApiError($error) { return get_class($error); }
    public static $reconciled = [], $reconcileFails = false;
    public static function reconcileCheckout($checkout, $source) {
        if (self::$reconcileFails) { throw new RuntimeException('PayPal unavailable'); }
        self::$reconciled[] = [$checkout['invoice_number'], $source];
        return $checkout;
    }
    public static function isTokenUsed($id) {
        foreach ($GLOBALS['global']['mysqli']->logs as $log) {
            if ($log['type'] === 'paypalToken' && $log['information'] === $id) { return true; }
        }
        return false;
    }
    public static function isRecurringPaymentIdUsed($id) {
        if (!PayPalDelivery::$ignoreLegacy) {
            foreach ($GLOBALS['global']['mysqli']->logs as $log) {
                if ($log['type'] === 'paypalIPN' && (string) $log['information'] === (string) $id) { return true; }
            }
        }
        return false;
    }
}
class PayPalYPT_log {
    private $user, $paymentId, $value, $json, $token;
    public function __construct($id) {}
    public function setUsers_id($id) { $this->user = $id; }
    public function setRecurring_payment_id($id) { $this->paymentId = $id; }
    public function setValue($value) { $this->value = $value; }
    public function setJson($json) { $this->json = json_encode($json); }
    public function setAgreement_id($id) {}
    public function setToken($token) { $this->token = $token; }
    public function save() {
        // ObjectYPT::save() applies this guard to PayPalYPT_log (it is not in ignoreTableSecurityCheck()).
        if (isUntrustedRequest('SAVE PayPalYPT_log')) { return false; }
        return WalletLog::addLog($this->user, $this->value, 0, 'PayPal log', $this->json,
            'success', $this->token ? 'paypalToken' : 'paypalIPN', $this->token ?: $this->paymentId);
    }
}
// In-memory checkout rows; the reconcile state machine itself is covered by PayPalCheckoutTest.
class PayPalYPT_checkout {
    public static $rows = [];
    public static function isInstalled() { return !empty(self::$rows); }
    public static function getById($id) { return self::$rows[$id] ?? false; }
    public static function getByInvoice($invoice) { foreach (self::$rows as $row) { if ($row['invoice_number'] === $invoice) { return $row; } } return false; }
    public static function getByPaymentId($id) { foreach (self::$rows as $row) { if ($row['payment_id'] === $id) { return $row; } } return false; }
    public static function getByAgreementId($id) { foreach (self::$rows as $row) { if ($row['agreement_id'] === $id) { return $row; } } return false; }
    public static function update($id, array $fields) {
        self::$rows[$id] = array_merge(self::$rows[$id], $fields);
        return self::$rows[$id];
    }
}
// The real processor shared by webhook.php, ipn.php and the checkout return.
$paypalProcessorSource = file_get_contents($root . '/plugin/PayPalYPT/Objects/PayPalPaymentProcessor.php');
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException;' . preg_replace('/^<\?php/', '', $paypalProcessorSource));
class Subscription {
    public static function getFromAgreement($id) {
        if (!AVideoPlugin::$subscriptionEnabled) { throw new \Error('Subscription is not loaded'); }
        PayPalDelivery::$lastAgreement = $id;
        return PayPalDelivery::$agreement;
    }
    public static function renew($user, $plan) {
        Payments::transferBalance($user, 8, 10, 'Renew', true);
        ++$GLOBALS['global']['mysqli']->renewals;
        return (object)['error' => PayPalDelivery::$failRenewal];
    }
}
class SubscriptionPlansTable {
    public function __construct($id) {}
    public function getName() { return 'Fixture plan'; }
}

class BtcModel {
    protected $data = [];
    public function __construct($id) { $this->data['id'] = $id; }
    public function __call($method, $arguments) { $this->data[lcfirst(substr($method, 3))] = $arguments[0]; }
    public static function getIdOr0($id) {
        $field = static::TYPE === 'btcInvoice' ? 'invoice_identification' : 'btc_invoices_id';
        foreach ($GLOBALS['global']['mysqli']->logs as $row) {
            if ($row['type'] === static::TYPE && $row['data'][$field] === $id) { return $row['data']['id']; }
        }
        return 0;
    }
    public function save($notifySocket = false) {
        $db = $GLOBALS['global']['mysqli'];
        if (static::TYPE === 'btcInvoice' ? $db->failBtcInvoice : $db->failBtcPayment) { return false; }
        $id = $this->data['id'] ?: count($db->logs) + 1;
        $this->data['id'] = $id;
        $db->logs[$id - 1] = ['type' => static::TYPE, 'wallet' => 0, 'data' => $this->data];
        return $id;
    }
}
class Btc_invoices extends BtcModel { const TYPE = 'btcInvoice'; }
class Btc_payments extends BtcModel { const TYPE = 'btcPayment'; }
$btcFixtureSource = file_get_contents($root . '/plugin/BTCPayments/BTCPayments.php');
$btcFixtureStart = strpos($btcFixtureSource, '    public static function processVerifiedInvoice(');
$btcFixtureEnd = strpos($btcFixtureSource, '    public static function profileTabName(', $btcFixtureStart);
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use stdClass; class BTCPayments {'
    . substr($btcFixtureSource, $btcFixtureStart, $btcFixtureEnd - $btcFixtureStart) . '}');

class PaymentWebhookIdempotencyTest extends TestCase
{
    private $previousGlobal;
    private $previousDatabase;
    private $db;
    private $payments;
    private $previousRequest;

    protected function setUp(): void {
        $this->previousGlobal = $GLOBALS['global'] ?? null;
        $this->previousDatabase = $GLOBALS['mysqlDatabase'] ?? null;
        $this->db = new Database();
        $GLOBALS['global'] = ['mysqli' => $this->db, 'webSiteRootURL' => 'https://example.test/'];
        $GLOBALS['mysqlDatabase'] = 'test_payments';
        $this->payments = new Payments();
        foreach (['_POST', '_GET', '_REQUEST', '_SERVER'] as $name) { $this->previousRequest[$name] = $GLOBALS[$name] ?? null; }
        $_POST = ['txn_type' => 'recurring_payment', 'payment_status' => 'Completed', 'recurring_payment_id' => 'agreement_1', 'txn_id' => 'txn_1', 'mc_gross' => '10.00', 'mc_currency' => 'USD'];
        $_GET = [];
        $_REQUEST = [];
        $_SERVER = ['REMOTE_ADDR' => '203.0.113.10', 'HTTP_USER_AGENT' => 'PayPal IPN'];
        PayPalDelivery::$status = 200;
        PayPalDelivery::$verified = true;
        PayPalDelivery::$agreement = ['users_id' => 7, 'subscriptions_plans_id' => 3];
        PayPalDelivery::$failRenewal = PayPalDelivery::$ignoreLegacy = false;
        PayPalDelivery::$filterRequest = false;
        PayPalDelivery::$lastAgreement = null;
        AVideoPlugin::$walletEnabled = true;
        AVideoPlugin::$subscriptionEnabled = true;
        AVideoPlugin::$ppv = new PpvCheckout();
        PpvCheckout::$fail = StripeCharge::$denyRead = false;
        StripeCharge::$amountRefunded = 0;
        PayPalDelivery::$webhook = json_decode('{"webhook_event":{"id":"WH-event_1","event_type":"PAYMENT.SALE.COMPLETED","resource":{"id":"txn_1","billing_agreement_id":"agreement_1","amount":{"total":"10.00","currency":"USD"}}}}');
        PayPalYPT_checkout::$rows = [];
        PayPalYPT::$deliveries = [];
        PayPalYPT::$reconciled = [];
        PayPalYPT::$reconcileFails = false;
    }
    protected function tearDown(): void {
        $GLOBALS['global'] = $this->previousGlobal;
        $GLOBALS['mysqlDatabase'] = $this->previousDatabase;
        foreach ($this->previousRequest as $name => $value) { $GLOBALS[$name] = $value; }
    }
    private function payPalHandlerSource($file) {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/' . $file);
        if (PayPalDelivery::$filterRequest) {
            $filters = file_get_contents(dirname(__DIR__, 2) . '/objects/security.php');
            $filters = preg_replace('/^<\?php|^require_once .*;\r?$/m', '', $filters);
            // security.php is normally included at global scope; eval runs inside this method.
            $filters = str_replace('$scanThis = &$$value;', '$scanThis = &$GLOBALS[$value];', $filters);
            $source = str_replace("require_once \$global['systemRootPath'] . 'videos/configuration.php';", $filters, $source);
        }
        $source = preg_replace('/^require_once .*;\r?$/m', '', $source);
        return preg_replace('/^<\?php/', '', $source);
    }
    private function deliverPayPalIpn() {
        $global =& $GLOBALS['global'];
        $source = $this->payPalHandlerSource('ipn.php');
        $source = str_replace('die(', 'throw new PayPalDeliveryStopped(', $source);
        try { eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use stdClass;' . $source); }
        catch (PayPalDeliveryStopped $stop) { /* Simulate the handler ending its HTTP request. */ }
    }
    public function payPalNotificationsWithoutMoney() {
        return [
            'profile created' => [['txn_type' => 'recurring_payment_profile_created', 'amount' => '10.00', 'currency_code' => 'USD']],
            'profile cancelled' => [['txn_type' => 'recurring_payment_profile_cancel', 'amount' => '10.00', 'currency_code' => 'USD']],
            'payment failed' => [['txn_type' => 'recurring_payment_failed', 'amount' => '10.00', 'currency_code' => 'USD']],
            'payment skipped' => [['txn_type' => 'recurring_payment_skipped', 'amount' => '10.00', 'currency_code' => 'USD']],
            'payment pending' => [['payment_status' => 'Pending']],
            'payment refunded' => [['payment_status' => 'Refunded', 'mc_gross' => '-10.00']],
        ];
    }
    /** @dataProvider payPalNotificationsWithoutMoney */
    public function testOnlyACompletedRecurringPaymentCreditsAndRenews($fields) {
        // ipn.php's recurring branch was unreachable while the _id filter turned recurring_payment_id into 0;
        // once restored it must not pay for the agreement's other notification types.
        $_POST = array_replace($_POST, $fields);
        if (isset($fields['txn_type']) && $fields['txn_type'] !== 'recurring_payment') {
            unset($_POST['txn_id'], $_POST['mc_gross'], $_POST['mc_currency'], $_POST['payment_status']);
            $_POST['verify_sign'] = 'sig_' . $fields['txn_type'];
        }
        $this->deliverPayPalIpn();
        $this->assertSame([7 => 0, 8 => 0], $this->db->balances);
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame([], $this->db->logs);
        $this->assertSame(200, PayPalDelivery::$status, 'Acknowledged, so PayPal does not resend it');

        // the completed payment of the same agreement is still paid
        $_POST = ['txn_type' => 'recurring_payment', 'payment_status' => 'Completed', 'recurring_payment_id' => 'agreement_1', 'txn_id' => 'txn_1', 'mc_gross' => '10.00', 'mc_currency' => 'USD'];
        $this->deliverPayPalIpn();
        $this->assertSame(1, $this->db->renewals);
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
    }
    private function deliverPayPalWebhook() {
        $global =& $GLOBALS['global'];
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/webhook.php');
        $source = preg_replace('/^require_once .*;\r?$/m', '', $source);
        $source = preg_replace('/^<\?php/', '', $source);
        eval('namespace ' . __NAMESPACE__ . '; use RuntimeException;' . $source);
    }
    private function deliverPayPalV2() {
        $global =& $GLOBALS['global'];
        $_GET = ['json' => '{"users_id":7}'];
        $_REQUEST = ['success' => 1];
        $source = $this->payPalHandlerSource('ipnV2.php');
        $source = str_replace('die(', 'throw new PayPalDeliveryStopped(', $source);
        try { eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use stdClass;' . $source); }
        catch (PayPalDeliveryStopped $stop) { /* Simulate the handler ending its HTTP request. */ }
    }
    public function testPayPalV2CannotCreditAnIpnAlreadyProcessedByEitherHandler() {
        $_POST['verify_sign'] = str_repeat('s', 56);
        $this->deliverPayPalWebhook();
        $this->deliverPayPalV2();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame([7 => 0.0, 8 => 10.0], $this->db->balances);
        $this->assertSame(1, $this->db->renewals);
    }
    public function testPayPalV2AndIpnUseTheSameTransactionClaim() {
        $_POST['verify_sign'] = str_repeat('s', 56);
        $this->deliverPayPalV2();
        $this->deliverPayPalIpn();
        $this->deliverPayPalV2();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame([7 => 10.0, 8 => 0], $this->db->balances);
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame(1, $this->db->commits);
    }
    /** @dataProvider payPalNotificationsWithoutMoney */
    public function testPayPalV2CannotClaimNonPaymentNotifications($fields) {
        $_POST = array_replace($_POST, $fields);
        $_POST['verify_sign'] = str_repeat('s', 56);
        $this->deliverPayPalV2();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame([], $this->db->logs);
        $_POST['txn_type'] = 'recurring_payment';
        $_POST['payment_status'] = 'Completed';
        $_POST['mc_gross'] = '10.00';
        $this->deliverPayPalV2();
        $this->assertSame(10.0, $this->db->balances[7]);
    }
    public function testPayPalV2RestoresTheVerifiedTransactionIdAfterFrameworkFiltering() {
        PayPalDelivery::$filterRequest = true;
        $_POST['txn_id'] = '34Y69196BK064583G';
        $_POST['verify_sign'] = str_repeat('s', 56);
        $originalPost = $_POST;
        $this->deliverPayPalV2();
        $this->assertSame(200, PayPalDelivery::$status);
        $_POST = $originalPost;
        $this->deliverPayPalIpn();
        $this->assertSame([7 => 10.0, 8 => 0], $this->db->balances);
        $this->assertSame(1, $this->db->commits);
    }
    public function testPayPalV2MarkerFailureUndoesItsLogAndCreditBeforeRetrying() {
        $GLOBALS['global']['bypassSameDomainCheck'] = 0;
        $_POST['verify_sign'] = str_repeat('s', 56);
        $this->db->failMarker = true;
        $this->deliverPayPalV2();
        $this->assertSame(500, PayPalDelivery::$status);
        $this->assertSame([], $this->db->logs);
        $this->assertSame([7 => 0, 8 => 0], $this->db->balances);
        $this->assertSame(0, $GLOBALS['global']['bypassSameDomainCheck']);
        $this->db->failMarker = false;
        PayPalDelivery::$status = 200;
        $this->deliverPayPalV2();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(10.0, $this->db->balances[7]);
    }
    public function testPayPalIpnRecognizesTheFullSignatureInAPreMarkerV2Credit() {
        $_POST['txn_id'] = '34Y69196BK064583G';
        $_POST['verify_sign'] = str_repeat('s', 56);
        $legacyPost = array_replace($_POST, ['txn_id' => 34]);
        WalletLog::addLog(7, 10, 0, 'PayPal Subscription from token', json_encode($legacyPost), 'success', 'addBalance');
        $this->deliverPayPalIpn();
        $this->assertSame(0, $this->db->commits);
        $this->deliverPayPalV2();
        $this->assertSame(0, $this->db->commits);
        $this->assertSame(200, PayPalDelivery::$status);
    }
    private function deliver($ids = ['pay_1'], $provider = 'razorpay', $renew = true) {
        return $this->payments->processPaymentOnce($provider, $ids, 7, function () use ($renew) {
            $this->payments->addBalance(7, 10, 'Payment');
            if ($renew) {
                $this->assertNotFalse(Payments::transferBalance(7, 8, 10, 'Renew', true));
                ++$this->db->renewals;
            }
            return true;
        });
    }
    public function gatewayProviders() { return [['paypal'], ['btcpay'], ['another_gateway']]; }
    /** @dataProvider gatewayProviders */
    public function testAnyGatewayUsesTheWalletLedger($provider) {
        $this->deliver(['gateway_payment_1'], $provider, false);
        $this->deliver(['gateway_payment_1'], $provider, false);
        $this->assertSame(10.0, $this->db->balances[7]);
        $this->assertCount(1, array_filter($this->db->logs, function ($log) { return $log['type'] === 'paymentProcessed'; }));
    }
    public function testSameIdInDifferentGatewaysIsNotADuplicate() {
        $this->deliver(['shared_id'], 'paypal', false);
        $this->deliver(['shared_id'], 'btcpay', false);
        $this->deliver(['shared_id'], 'another_gateway', false);
        $this->assertSame(30.0, $this->db->balances[7]);
    }
    public function testNewGatewayCanSupplyItsOwnLegacyCreditPatterns() {
        WalletLog::addLog(7, 10, 0, 'NewGateway payment', '{"id":"legacy_1"}', 'success', 'addBalance');
        $this->assertTrue($this->payments->processPaymentOnce('new_gateway', ['legacy_1'], 7,
            function () { $this->fail('A historical credit must not be paid again'); },
            ['legacyDescriptions' => ['NewGateway %']]));
        $this->assertSame(0, $this->db->commits);
    }
    public function testGatewayLegacyLookupRunsInsideThePaymentTransaction() {
        $this->assertTrue($this->payments->processPaymentOnce('another_gateway', ['legacy_1'], 7,
            function () { $this->fail('A historical payment must not be paid again'); },
            ['legacyCheck' => function () {
                $this->assertSame($this->db, $GLOBALS['global']['mysqlTransactionConnection']);
                return true;
            }]));
        $this->assertSame([], $this->db->logs);
        $this->assertSame(0, $this->db->commits);
    }
    public function testPayPalIpnAndRestWebhookShareTheSamePaymentClaim() {
        $this->deliverPayPalIpn();
        $this->deliverPayPalWebhook();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(1, $this->db->renewals);
        $this->assertSame(10.0, $this->db->balances[8]);
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
    }
    public function testPayPalRestWebhookThenIpnCreditsAndRenewsOnce() {
        $this->deliverPayPalWebhook();
        $this->deliverPayPalIpn();
        $this->assertSame(1, $this->db->renewals);
        $this->assertSame(10.0, $this->db->balances[8]);
        $this->assertCount(1, array_filter($this->db->logs, function ($log) { return $log['type'] === 'paypalToken'; }));
    }
    public function testPayPalRestWebhookRecognizesPreMarkerTokenLog() {
        WalletLog::addLog(7, 10, 0, 'PayPal log', '{}', 'success', 'paypalToken', 'WH-event_1');
        $this->deliverPayPalWebhook();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame(0, $this->db->commits);
    }
    public function testPayPalHistoricalWebhookDoesNotRetryWhenSubscriptionIsDisabled() {
        WalletLog::addLog(7, 10, 0, 'PayPal log', '{}', 'success', 'paypalToken', 'WH-event_1');
        AVideoPlugin::$subscriptionEnabled = false;
        $this->deliverPayPalWebhook();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(0, $this->db->commits);
        $this->assertCount(1, $this->db->logs);
    }
    public function testPayPalIpnRecognizesPreMarkerWebhookCredit() {
        WalletLog::addLog(7, 10, 0, 'Paypal recurrent WH Fixture plan', json_encode(PayPalDelivery::$webhook), 'success', 'addBalance');
        WalletLog::addLog(7, 10, 0, 'PayPal log', json_encode(PayPalDelivery::$webhook), 'success', 'paypalToken', 'WH-event_1');
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame(0, $this->db->commits);
    }
    public function testPayPalWebhookRecognizesPreMarkerCreditWithoutGatewayLog() {
        // The old webhook wrote the wallet credit before saving its own gateway log.
        WalletLog::addLog(7, 10, 0, 'Paypal recurrent WH Fixture plan', json_encode(PayPalDelivery::$webhook), 'success', 'addBalance');
        $this->deliverPayPalWebhook();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame(0, $this->db->commits);
    }
    public function payPalWebhookEventsWithoutMoney() {
        return [['PAYMENT.SALE.PENDING'], ['PAYMENT.SALE.DENIED'], ['PAYMENT.SALE.REFUNDED'],
            ['PAYMENT.SALE.REVERSED'], ['BILLING.SUBSCRIPTION.PAYMENT.FAILED']];
    }
    /** @dataProvider payPalWebhookEventsWithoutMoney */
    public function testPayPalWebhookOnlyClaimsCompletedSales($eventType) {
        PayPalDelivery::$webhook->webhook_event->event_type = $eventType;
        $this->deliverPayPalWebhook();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame([], $this->db->logs);
        $this->assertSame([7 => 0, 8 => 0], $this->db->balances);
        $this->assertSame(0, $this->db->renewals);
        PayPalDelivery::$webhook->webhook_event->event_type = 'PAYMENT.SALE.COMPLETED';
        $this->deliverPayPalWebhook();
        $this->assertSame(1, $this->db->renewals, 'An earlier non-payment event must not claim the sale ID');
    }
    public function testPayPalRestWebhookFailureRollsBackItsOwnLogAndCanRetry() {
        $this->db->failMarker = true;
        $this->deliverPayPalWebhook();
        $this->assertSame(500, PayPalDelivery::$status);
        $this->assertSame([], $this->db->logs);
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame([7 => 0, 8 => 0], $this->db->balances);
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
        $this->db->failMarker = false;
        PayPalDelivery::$status = 200;
        $this->deliverPayPalWebhook();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(1, $this->db->renewals);
    }
    public function testUnverifiedPayPalRestWebhookCannotClaimAPayment() {
        PayPalDelivery::$verified = false;
        $this->deliverPayPalWebhook();
        $this->assertSame([], $this->db->logs);
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
    }
    public function testPayPalRestRenewalFailureKeepsTheCreditAndRestoresOriginFlag() {
        $GLOBALS['global']['bypassSameDomainCheck'] = 0;
        PayPalDelivery::$failRenewal = true;
        $this->deliverPayPalWebhook();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame([7 => 10.0, 8 => 0], $this->db->balances);
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame(0, $GLOBALS['global']['bypassSameDomainCheck']);
        $this->deliverPayPalWebhook();
        $this->assertSame(10.0, $this->db->balances[7]);
    }
    private function btcInvoice($id = 'btc_invoice_1', $status = 'Settled', $currency = 'USD') {
        return (object)['id' => $id, 'status' => $status, 'amount' => 10, 'currency' => $currency,
            'totalBTCPaid' => 0.0001, 'storeId' => 'store_1', 'checkoutLink' => 'https://example.test/invoice',
            'metadata' => (object)['users_id' => 7, 'BTCPAY_NETWORK' => 'Mainnet']];
    }
    public function testBtcPayUsesTheWalletLedgerAlongsideItsOwnPaymentLog() {
        BTCPayments::processVerifiedInvoice($this->btcInvoice());
        $response = BTCPayments::processVerifiedInvoice($this->btcInvoice());
        $this->assertSame('Payment already processed', $response->msg);
        $this->assertSame(10.0, $this->db->balances[7]);
        foreach (['btcInvoice', 'btcPayment', 'paymentProcessed'] as $type) {
            $this->assertCount(1, array_filter($this->db->logs, function ($log) use ($type) { return $log['type'] === $type; }));
        }
        BTCPayments::processVerifiedInvoice($this->btcInvoice('btc_invoice_2'));
        $this->assertSame(20.0, $this->db->balances[7]);
    }
    public function btcFailures() { return [['failBtcInvoice'], ['failBtcPayment'], ['failCredit'], ['failMarker']]; }
    /** @dataProvider btcFailures */
    public function testBtcPayInvoicePaymentAndCreditRollBackTogether($failure) {
        $this->db->$failure = true;
        try { BTCPayments::processVerifiedInvoice($this->btcInvoice()); $this->fail('Failed BTC payment was accepted'); }
        catch (RuntimeException $error) { $this->assertNotEmpty($error->getMessage()); }
        $this->assertSame([], $this->db->logs);
        $this->assertSame(0, $this->db->balances[7]);
        $this->db->$failure = false;
        BTCPayments::processVerifiedInvoice($this->btcInvoice());
        $this->assertSame(10.0, $this->db->balances[7]);
    }
    public function testBtcPayPreMarkerPaymentIsStillRecognized() {
        BTCPayments::processVerifiedInvoice($this->btcInvoice());
        $this->db->logs = array_values(array_filter($this->db->logs, function ($log) { return $log['type'] !== 'paymentProcessed'; }));
        $response = BTCPayments::processVerifiedInvoice($this->btcInvoice());
        $this->assertSame('Payment already processed', $response->msg);
        $this->assertSame(10.0, $this->db->balances[7]);
        $this->assertSame(1, $this->db->commits);
    }
    public function testBtcPayHistoricalDuplicateIsAcknowledgedWhileWalletIsDisabled() {
        BTCPayments::processVerifiedInvoice($this->btcInvoice());
        $this->db->logs = array_values(array_filter($this->db->logs, function ($log) { return $log['type'] !== 'paymentProcessed'; }));
        $before = $this->db->logs;
        AVideoPlugin::$walletEnabled = false;
        $response = BTCPayments::processVerifiedInvoice($this->btcInvoice());
        $this->assertSame('Payment already processed', $response->msg);
        $this->assertSame($before, $this->db->logs);
        $this->assertSame(10.0, $this->db->balances[7]);
        $this->assertSame(1, $this->db->commits);
    }
    public function testBtcPayPendingAndWrongCurrencyDoNotCredit() {
        BTCPayments::processVerifiedInvoice($this->btcInvoice('pending', 'New'));
        $this->assertSame(0, $this->db->balances[7]);
        BTCPayments::processVerifiedInvoice($this->btcInvoice('wrong_currency', 'Settled', 'EUR'));
        $this->assertSame(0, $this->db->balances[7]);
        $this->assertCount(0, array_filter($this->db->logs, function ($log) { return $log['type'] === 'btcPayment'; }));
    }
    public function walletAvailability() { return [[true], [false]]; }
    /** @dataProvider walletAvailability */
    public function testBtcPayCurrencyMismatchIsAcknowledgedWithoutWrites($enabled) {
        AVideoPlugin::$walletEnabled = $enabled;
        $response = BTCPayments::processVerifiedInvoice($this->btcInvoice('wrong_currency', 'Settled', 'EUR'));
        $this->assertSame('Ignored: currency mismatch', $response->msg);
        $this->assertSame([], $this->db->logs);
        $this->assertSame(0, $this->db->commits);
    }
    public function testBtcPayInvoiceWithoutAVideoUserIsAcknowledgedWithoutWrites() {
        $invoice = $this->btcInvoice();
        $invoice->metadata = new \stdClass();
        $response = BTCPayments::processVerifiedInvoice($invoice);
        $this->assertSame('Ignored: invoice without AVideo users_id', $response->msg);
        $this->assertSame([], $this->db->logs);
        $this->assertSame(0, $this->db->commits);
    }
    public function testBusyBtcPayPaymentDoesNotWriteItsOwnHistory() {
        $this->db->busy = true;
        $this->expectException(RuntimeException::class);
        try { BTCPayments::processVerifiedInvoice($this->btcInvoice()); }
        finally { $this->assertSame([], $this->db->logs); }
    }
    public function testRetryDoesNotCreditOrRenewAgain() {
        $this->assertTrue($this->deliver());
        $this->assertTrue($this->deliver());
        $this->assertSame([7 => 0.0, 8 => 10.0], $this->db->balances);
        $this->assertSame(1, $this->db->renewals);
        $this->assertSame(1, $this->db->commits, 'Nested renewal transfer must not commit the outer transaction');
    }
    public function testChargeAndInvoiceAliasesAreDeduplicatedButNextInvoiceIsPaid() {
        $invoice = json_decode('{"id":"evt_1","data":{"object":{"id":"in_1","charge":"ch_1"}}}');
        $charge = json_decode('{"id":"evt_2","data":{"object":{"id":"ch_1","invoice":"in_1"}}}');
        $this->deliver(StripeIds::getPaymentIds($invoice), 'stripe');
        $this->deliver(StripeIds::getPaymentIds($charge), 'stripe');
        $this->deliver(['in_2', 'ch_2', 'evt_3'], 'stripe');
        $this->assertSame(2, $this->db->renewals);
        $this->assertSame(20.0, $this->db->balances[8]);
    }
    public function testSinglePaymentDuplicateDoesNotCreditAgain() {
        $this->deliver(['ch_1', 'evt_1'], 'stripe', false);
        $this->deliver(['ch_1', 'evt_2'], 'stripe', false);
        $this->assertSame(10.0, $this->db->balances[7]);
    }
    private function ppvEvent() {
        return json_decode('{"id":"evt_ppv","type":"charge.succeeded","data":{"object":{"id":"ch_ppv","payment_intent":"pi_ppv","amount":199,"currency":"usd","metadata":{"users_id":"7","singlePayment":"1","plugin":"PayPerView","ppv_auto_rent":"1","ppv_plans_id":"2","videos_id":"3419","ppv_value":"1.99","rental_hours":"72"}}}}');
    }
    public function testCardWebhookCreditsAndCompletesRentalExactlyOnceWithoutBrowser() {
        $stripe = new StripeYPT();
        $this->assertTrue($stripe->processSinglePaymentIPN($this->ppvEvent()));
        $this->assertTrue($stripe->processSinglePaymentIPN($this->ppvEvent()));
        $this->assertSame(0.0, $this->db->balances[7]);
        $this->assertSame(1.99, $this->db->balances[8]);
        $this->assertSame(1, $this->db->renewals);
        $this->assertSame(1, $this->db->commits);
    }
    public function testLiveWebhookCompletesAccessOnceAndFailedActivationKeepsCredit() {
        $event = $this->ppvEvent();
        $metadata = $event->data->object->metadata;
        $metadata->plugin = 'PayPerViewLive';
        unset($metadata->ppv_auto_rent);
        $metadata->ppvlive_auto_buy = '1';
        $metadata->ppvlive_schedule_id = '9';
        $metadata->ppvlive_value = '1.99';
        $metadata->live_ends = '2030-01-01 00:00:00';
        $stripe = new StripeYPT();
        $this->assertTrue($stripe->processSinglePaymentIPN($event));
        $this->assertTrue($stripe->processSinglePaymentIPN($event));
        $this->assertSame(1, $this->db->renewals);
        $this->assertSame(0.0, $this->db->balances[7]);
        $this->assertSame(1.99, $this->db->balances[8]);
        $event->id = 'evt_live2';
        $event->data->object->id = 'ch_live2';
        $event->data->object->payment_intent = 'pi_live2';
        PpvCheckout::$fail = true;
        $this->assertTrue($stripe->processSinglePaymentIPN($event));
        $this->assertSame(1.99, $this->db->balances[7]);
        $this->assertSame(1.99, $this->db->balances[8], 'The failed transfer is rolled back');
        $this->assertSame(1, $this->db->renewals);
        $this->assertStringContainsString('PPV Live access not completed', implode('\n', $this->db->errorLogs));
    }
    private function assertCreditedWithoutRental($credit) {
        $this->assertSame([7 => $credit, 8 => 0], $this->db->balances, 'The card payment stays in the wallet; debit and split are undone');
        $this->assertSame(0, $this->db->renewals, 'No rental is saved');
    }
    public function testFailedRentalKeepsTheCreditUndoesOnlyTheRentalAndIsNotRetriedForever() {
        $stripe = new StripeYPT();
        PpvCheckout::$fail = true;
        $this->assertTrue($stripe->processSinglePaymentIPN($this->ppvEvent()), 'A failed rental must not make Stripe retry until it gives up');
        $this->assertCreditedWithoutRental(1.99);
        $this->assertStringContainsString('PPV rental not completed', implode("\n", $this->db->errorLogs));
        PpvCheckout::$fail = false;
        $this->assertTrue($stripe->processSinglePaymentIPN($this->ppvEvent()), 'A redelivery is a duplicate');
        $this->assertCreditedWithoutRental(1.99);
    }
    public function testShortfallSpentElsewhereIsCreditedInsteadOfLost() {
        // The card paid the 1.99 shortfall of a 3.98 rental, but the old 1.99 was spent in another tab.
        $event = $this->ppvEvent();
        $event->data->object->metadata->ppv_value = '3.98';
        for ($delivery = 0; $delivery < 3; $delivery++) {
            $this->assertTrue((new StripeYPT())->processSinglePaymentIPN($event));
        }
        $this->assertCreditedWithoutRental(1.99);
    }
    public function testAutoRentalWithoutCompatibleEnabledPpvKeepsTheCredit() {
        AVideoPlugin::$ppv = false;
        $this->assertTrue((new StripeYPT())->processSinglePaymentIPN($this->ppvEvent()));
        $this->assertCreditedWithoutRental(1.99);
    }
    public function testWrongCurrencyCannotCompleteAnAutomaticRental() {
        $event = $this->ppvEvent(); $event->data->object->currency = 'eur';
        $this->assertTrue((new StripeYPT())->processSinglePaymentIPN($event));
        $this->assertCreditedWithoutRental(1.99);
    }
    public function testRefundedAutoRentalCreditsOnlyWhatIsStillPaidAndNeverRents() {
        StripeCharge::$amountRefunded = 199;
        $this->assertFalse((new StripeYPT())->processSinglePaymentIPN($this->ppvEvent()));
        $this->assertSame([], $this->db->logs);
        StripeCharge::$amountRefunded = 99;
        $this->assertTrue((new StripeYPT())->processSinglePaymentIPN($this->ppvEvent()));
        $this->assertCreditedWithoutRental(1.0);
        $this->assertStringContainsString('partially refunded', implode("\n", $this->db->errorLogs));
    }
    public function testUnverifiedChargeIsCreditedButNeverRentedWithoutChargesRead() {
        StripeCharge::$denyRead = true;
        $this->assertTrue((new StripeYPT())->processSinglePaymentIPN($this->ppvEvent()));
        $this->assertCreditedWithoutRental(1.99);
        $this->assertStringContainsString('cannot read charges', implode("\n", $this->db->errorLogs));
    }
    public function testOldWalletCreditSuppressesRetryAfterUpgrade() {
        WalletLog::addLog(7, 10, 0, 'Stripe single payment: test', '{"id":"evt_old","data":{"object":{"id":"ch_old"}}}', 'success', 'addBalance');
        $this->deliver(['ch_old', 'evt_new'], 'stripe', false);
        $this->assertSame(0, $this->db->balances[7]);
        $this->assertSame(0, $this->db->commits);
    }
    public function failures() { return [['failCredit'], ['failMarker'], ['failCommit']]; }
    /** @dataProvider failures */
    public function testFailedCreditMarkerOrCommitRollsBackAndCanBeRetried($failure) {
        $this->db->$failure = true;
        try { $this->deliver(); $this->fail('Failed payment was acknowledged'); }
        catch (RuntimeException $error) { $this->assertNotEmpty($error->getMessage()); }
        $this->assertSame([7 => 0, 8 => 0], $this->db->balances);
        $this->assertSame([], $this->db->logs);
        $this->assertSame(0, $this->db->renewals);
        $failureLog = implode("\n", $this->db->errorLogs);
        $this->assertStringContainsString('FAILED and rolled back', $failureLog);
        $this->assertStringContainsString('provider=razorpay users_id=7 payment_ids=pay_1', $failureLog);
        $this->db->$failure = false;
        $this->assertTrue($this->deliver());
        $this->assertSame(1, $this->db->renewals);
    }
    private function deliverWithRenewal(callable $renewal, $ids = ['in_fail'], $credit = 10) {
        return $this->payments->processPaymentOnce('stripe', $ids, 7, function () use ($renewal, $credit) {
            $this->payments->addBalance(7, $credit);
            $renewed = $this->payments->runPaymentStep($renewal, 'test renewal');
            $this->assertFalse($renewed);
            return true;
        });
    }
    public function testFailedRenewalKeepsCreditAndUndoesOnlyTheRenewal() {
        $this->assertTrue($this->deliverWithRenewal(function () {
            $this->assertNotFalse(Payments::transferBalance(7, 8, 10, 'Renew', true));
            ++$this->db->renewals;
            return false;
        }));
        $this->assertSame([7 => 10.0, 8 => 0], $this->db->balances, 'Paid credit stays; the renewal transfer is undone');
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame(1, $this->db->commits);
        $this->assertTrue($this->deliverWithRenewal(function () { return true; }));
        $this->assertSame(10.0, $this->db->balances[7], 'A retry must not credit the same payment again');
    }
    public function testInsufficientBalanceRenewalReturnsFalseWithoutAbortingPayment() {
        $this->assertTrue($this->deliverWithRenewal(function () {
            return Payments::transferBalance(7, 8, 10, 'Renew', true) !== false;
        }, ['in_partial'], 4));
        $this->assertSame([7 => 4.0, 8 => 0], $this->db->balances);
    }
    public function testRenewalExceptionIsLoggedAndContained() {
        $this->assertTrue($this->deliverWithRenewal(function () {
            Payments::transferBalance(7, 8, 10, 'Renew', true);
            throw new RuntimeException('Renewal database error');
        }));
        $this->assertSame([7 => 10.0, 8 => 0], $this->db->balances);
        $this->assertStringContainsString('Renewal database error', implode("\n", $this->db->errorLogs));
    }
    public function testStripeIpnAcknowledgesUnrelatedChargesButRetriesFailures() {
        // setUpPayment() charges carry no customer/metadata, so processSubscriptionIPN() returns
        // false for their charge.succeeded events; only exceptions may become an HTTP 500.
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/ipn.php');
        $this->assertDoesNotMatchRegularExpression('/if\s*\(\s*!\s*\$stripe->process(Subscription|SinglePayment)IPN/', $source);
        $this->assertMatchesRegularExpression('/catch \(\\\\Throwable \$e\) \{[^}]*http_response_code\(500\)/', $source);
    }
    public function testPayPalRecurringNotificationIsCreditedOnce() {
        $this->assertTrue($this->deliver(['txn_1'], 'paypal'));
        $this->assertTrue($this->deliver(['txn_1'], 'paypal'));
        $this->assertSame([7 => 0.0, 8 => 10.0], $this->db->balances);
        $this->assertSame(1, $this->db->renewals);
        $this->deliver(['txn_2'], 'paypal');
        $this->assertSame(2, $this->db->renewals, 'The next recurring payment is still paid');
    }
    public function testPayPalDoesNotMatchAnotherProvidersLegacyCredit() {
        WalletLog::addLog(7, 10, 0, 'RazorPay recurrent payment: ', '{"notes":{"reference":"txn_1"}}', 'success', 'addBalance');
        $this->deliver(['txn_1'], 'paypal');
        $this->assertSame(1, $this->db->renewals, 'A Razorpay JSON reference must not suppress a PayPal payment');
    }
    public function testRazorpayLegacyCreditIsStillRecognized() {
        WalletLog::addLog(7, 10, 0, 'RazorPay recurrent payment: ', '{"id":"pay_old"}', 'success', 'addBalance');
        $this->deliver(['pay_old'], 'razorpay');
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame(0, $this->db->commits);
    }
    public function testVerifiedPayPalIpnCanSaveWithoutBrowserOriginAndRestoresGuard() {
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(1, $this->db->renewals);
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
        PayPalDelivery::$ignoreLegacy = true; // Both concurrent requests can pass the old, non-atomic check.
        $this->deliverPayPalIpn();
        $this->assertSame(1, $this->db->renewals);
        $this->assertCount(1, array_filter($this->db->logs, function ($log) { return $log['type'] === 'paypalIPN'; }));
    }
    public function testPayPalNumericPaymentIdIsAcceptedByTheStringIdHelper() {
        $GLOBALS['global']['bypassSameDomainCheck'] = 1;
        $_POST['txn_id'] = 61; // objects/security.php coerces fields ending in _id to integers.
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(1, $this->db->renewals);
        $this->assertSame(1, $GLOBALS['global']['bypassSameDomainCheck']);
    }
    public function testVerifiedPayPalIdsSurviveTheFrameworkIntegerFilters() {
        PayPalDelivery::$filterRequest = true;
        $_POST['recurring_payment_id'] = 'I-R52C41AGNEAP';
        $_POST['txn_id'] = '34Y69196BK064583G';
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame('I-R52C41AGNEAP', PayPalDelivery::$lastAgreement);
        $this->assertSame(1, $this->db->renewals);
        $log = array_values(array_filter($this->db->logs, function ($log) { return $log['type'] === 'paypalIPN'; }))[0];
        $this->assertSame('34Y69196BK064583G', $log['information']);
        $this->deliverPayPalIpn();
        $this->assertSame(1, $this->db->renewals);
    }
    public function testPayPalLossyIntegerIdCannotSuppressAnUnrelatedPayment() {
        PayPalDelivery::$filterRequest = true;
        $_POST['recurring_payment_id'] = 'I-R52C41AGNEAP';
        $_POST['txn_id'] = '34Y69196BK064583G';
        WalletLog::addLog(7, 10, 0, 'PayPal log', '{}', 'success', 'paypalIPN', '34');
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(1, $this->db->commits);
        $this->assertSame(1, $this->db->renewals);
        $this->deliverPayPalIpn();
        $this->assertSame(1, $this->db->renewals, 'The complete transaction ID still deduplicates retries');
    }
    public function testPayPalFailedRenewalKeepsCreditAndRestoresExistingGuard() {
        $GLOBALS['global']['bypassSameDomainCheck'] = 0;
        PayPalDelivery::$failRenewal = true;
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame([7 => 10.0, 8 => 0], $this->db->balances);
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame(0, $GLOBALS['global']['bypassSameDomainCheck']);
        $this->deliverPayPalIpn();
        $this->assertSame(10.0, $this->db->balances[7]);
    }
    public function testPayPalCreditFailureRollsBackLogAndRestoresGuardForRetry() {
        $this->db->failCredit = true;
        $this->deliverPayPalIpn();
        $this->assertSame(500, PayPalDelivery::$status);
        $this->assertSame([], $this->db->logs);
        $this->assertSame([7 => 0, 8 => 0], $this->db->balances);
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
        $this->db->failCredit = false;
        PayPalDelivery::$status = 200;
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(1, $this->db->renewals);
    }
    public function testPayPalLegacyLogWithoutMarkerIsStillIgnored() {
        WalletLog::addLog(7, 10, 0, 'PayPal log', '{}', 'success', 'paypalIPN', 'txn_1');
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(0, $this->db->commits);
        $this->assertSame(0, $this->db->renewals);
    }
    public function testPayPalSignatureFallbackAndBusyRetryRestoreGuard() {
        unset($_POST['txn_id']);
        $_POST['verify_sign'] = 'signature_fixture';
        $this->db->busy = true;
        $this->deliverPayPalIpn();
        $this->assertSame(500, PayPalDelivery::$status);
        $this->assertSame([], $this->db->logs);
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
        $this->db->busy = false;
        PayPalDelivery::$status = 200;
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame(1, $this->db->renewals);
        $this->deliverPayPalIpn();
        $this->assertSame(1, $this->db->renewals);
    }
    public function testUnrelatedPayPalAgreementAndCurrencyMismatchAreAcknowledged() {
        PayPalDelivery::$agreement['users_id'] = 0;
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        PayPalDelivery::$agreement['users_id'] = 7;
        $_POST['mc_currency'] = 'EUR';
        $this->deliverPayPalIpn();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame([], $this->db->logs);
        $this->assertSame(0, $this->db->commits);
    }
    public function testUnverifiedPayPalIpnNeverBypassesOriginGuard() {
        PayPalDelivery::$verified = false;
        PayPalDelivery::$filterRequest = true;
        $_POST['recurring_payment_id'] = 'I-R52C41AGNEAP';
        $this->deliverPayPalIpn();
        $this->assertSame([], $this->db->logs);
        $this->assertSame(0, $_POST['recurring_payment_id'], 'An unverified request cannot restore raw provider IDs');
        $this->assertArrayNotHasKey('bypassSameDomainCheck', $GLOBALS['global']);
    }
    public function testPayPalIpnClaimsThePaymentBeforeLoggingCreditingOrRenewing() {
        // ipn.php: the replay check and the PayPalYPT_log insert were separate steps, so two concurrent
        // copies of one notification could both credit and renew (check-to-insert race).
        // The IPN, the webhook and the checkout return now share PayPalPaymentProcessor::processSubscriptionSale().
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/Objects/PayPalPaymentProcessor.php');
        $method = strpos($source, 'public static function processSubscriptionSale(');
        $claim = strpos($source, '$wallet->processPaymentOnce(self::PROVIDER, $paymentIds, $users_id,', $method);
        $this->assertNotFalse($claim);
        foreach (['new PayPalYPT_log(0)', '$pp->save()', '$wallet->addBalance($users_id, $amount', 'Subscription::renew('] as $step) {
            $position = strpos($source, $step, $method);
            $this->assertNotFalse($position, $step);
            $this->assertGreaterThan($claim, $position, "{$step} must run inside processPaymentOnce()");
        }
        $this->assertSame(1, substr_count($source, 'Subscription::renew('));
        $this->assertMatchesRegularExpression('/runPaymentStep\(function \(\) use/', $source);
        $ipn = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/ipn.php');
        $call = strpos($ipn, 'PayPalPaymentProcessor::processSubscriptionSale($plugin, $users_id,');
        $this->assertNotFalse($call);
        $this->assertSame(0, substr_count($ipn, 'Subscription::renew('), 'Only the shared processor renews');
        // a failed or busy delivery must not be acknowledged, so PayPal sends it again
        $catch = strpos($ipn, 'catch (\Throwable $error)', $call);
        $this->assertNotFalse($catch);
        $this->assertNotFalse(strpos($ipn, 'http_response_code(500)', $catch));
    }
    private function checkoutRow($purpose = 'wallet', $selection = []) {
        PayPalYPT_checkout::$rows[1] = ['id' => 1, 'users_id' => 7, 'invoice_number' => 'AVPP000000000000000000000001', 'purpose' => $purpose,
            'selection' => $selection + ['description' => 'Fixture purchase'], 'amount' => '10.00', 'currency' => 'USD', 'payment_id' => 'PAYID-1',
            'agreement_id' => null, 'sale_id' => null, 'status' => 'approved', 'status_detail' => null, 'fulfillment' => null];
        return PayPalYPT_checkout::$rows[1];
    }
    private function sale($state = 'completed', $total = '10.00', $currency = 'USD') {
        return ['id' => 'SALE-1', 'state' => $state, 'total' => $total, 'currency' => $currency];
    }
    public function testPayPalCheckoutSaleCreditsOnceWhateverPathDeliversIt() {
        $this->checkoutRow();
        foreach (['return', 'webhook', 'ipn', 'status'] as $source) {
            PayPalPaymentProcessor::fulfillCheckoutSale($this->payments, PayPalYPT_checkout::getById(1), $this->sale(), $source);
        }
        $this->assertSame(10.0, $this->db->balances[7]);
        $this->assertSame(1, $this->db->commits);
        $this->assertSame('completed', PayPalYPT_checkout::getById(1)['status']);
        $this->assertSame('credited', PayPalYPT_checkout::getById(1)['fulfillment']);
        $this->assertCount(1, array_filter($this->db->logs, function ($log) { return $log['type'] === 'paymentProcessed'; }));
    }
    public function testPayPalPpvCheckoutRentsTheSelectedMovieInTheSameTransaction() {
        $this->checkoutRow('ppv', ['ppv_plans_id' => 3, 'videos_id' => 77, 'ppv_value' => '10.00', 'rental_hours' => 48]);
        $fulfillment = PayPalPaymentProcessor::fulfillCheckoutSale($this->payments, PayPalYPT_checkout::getById(1), $this->sale(), 'return');
        $this->assertSame('rented', $fulfillment);
        $this->assertSame([7 => 0.0, 8 => 10.0], $this->db->balances, 'Credited, then paid to the seller');
        $this->assertSame(1, $this->db->renewals);
        PayPalPaymentProcessor::fulfillCheckoutSale($this->payments, PayPalYPT_checkout::getById(1), $this->sale(), 'webhook');
        $this->assertSame(1, $this->db->renewals, 'One rental per sale');
    }
    public function testPayPalFailedRentalKeepsTheCreditForTheSamePurchase() {
        $this->checkoutRow('ppv', ['ppv_plans_id' => 3, 'videos_id' => 77, 'ppv_value' => '10.00', 'rental_hours' => 48]);
        PpvCheckout::$fail = true;
        $fulfillment = PayPalPaymentProcessor::fulfillCheckoutSale($this->payments, PayPalYPT_checkout::getById(1), $this->sale(), 'return');
        $this->assertSame('benefit_failed', $fulfillment);
        $this->assertSame([7 => 10.0, 8 => 0], $this->db->balances, 'No partial debit or seller split remains');
        $this->assertSame(0, $this->db->renewals);
        $this->assertSame(1, $this->db->commits);
    }
    public function testPayPalLiveCheckoutActivatesTheEvent() {
        $this->checkoutRow('ppvlive', ['ppvlive_schedule_id' => 4, 'ppvlive_value' => '10.00', 'live_ends' => '2026-10-30 10:00:00']);
        $this->assertSame('live_ready', PayPalPaymentProcessor::fulfillCheckoutSale($this->payments, PayPalYPT_checkout::getById(1), $this->sale(), 'webhook'));
        $this->assertSame([7 => 0.0, 8 => 10.0], $this->db->balances);
    }
    public function testPayPalPendingOrMismatchedSaleIsNeverClaimed() {
        $this->checkoutRow();
        foreach ([$this->sale('pending'), $this->sale('completed', '1.00'), $this->sale('completed', '10.00', 'EUR')] as $sale) {
            try {
                PayPalPaymentProcessor::fulfillCheckoutSale($this->payments, PayPalYPT_checkout::getById(1), $sale, 'webhook');
                $this->fail('Must not be credited');
            } catch (RuntimeException $refused) {
                $this->assertSame([], $this->db->logs);
            }
        }
        $this->assertSame('approved', PayPalYPT_checkout::getById(1)['status'], 'A pending sale does not claim the checkout');
        PayPalPaymentProcessor::fulfillCheckoutSale($this->payments, PayPalYPT_checkout::getById(1), $this->sale(), 'webhook');
        $this->assertSame(10.0, $this->db->balances[7], 'The completed event still credits after a pending one');
    }
    public function testPayPalCheckoutFailedMarkerRollsBackAndCanRetry() {
        $this->checkoutRow();
        $this->db->failMarker = true;
        try {
            PayPalPaymentProcessor::fulfillCheckoutSale($this->payments, PayPalYPT_checkout::getById(1), $this->sale(), 'webhook');
            $this->fail('A failed ledger marker must abort');
        } catch (RuntimeException $retry) {
            $this->assertSame([], $this->db->logs);
            $this->assertSame(0, $this->db->balances[7]);
        }
        $this->db->failMarker = false;
        PayPalPaymentProcessor::fulfillCheckoutSale($this->payments, PayPalYPT_checkout::getById(1), $this->sale(), 'webhook');
        $this->assertSame(10.0, $this->db->balances[7]);
    }
    public function testPayPalFirstSubscriptionPaymentSharesTheKeyWithWebhookAndIpn() {
        // Checkout return: the agreement's first sale is credited and renews once.
        PayPalPaymentProcessor::processSubscriptionSale($this->payments, 7, 3, 'agreement_1', ['txn_1'], 10, 'Paypal recurrent Fixture plan', ['sale_id' => 'txn_1']);
        $this->assertSame(1, $this->db->renewals);
        $this->deliverPayPalWebhook();
        $this->deliverPayPalIpn();
        $this->assertSame(1, $this->db->renewals, 'Webhook and IPN of the same sale do not renew again');
        $this->assertSame([7 => 0.0, 8 => 10.0], $this->db->balances);
        // The next cycle is a different sale and renews.
        $_POST['txn_id'] = 'txn_2';
        $this->deliverPayPalIpn();
        $this->assertSame(2, $this->db->renewals);
    }
    public function testPayPalIpnCreditsTheFirstPaymentReportedWithTheNewAgreement() {
        $_POST = ['txn_type' => 'recurring_payment_profile_created', 'recurring_payment_id' => 'agreement_1', 'initial_payment_status' => 'Completed',
            'initial_payment_txn_id' => 'txn_setup', 'initial_payment_amount' => '10.00', 'currency_code' => 'USD', 'amount' => '10.00'];
        $this->deliverPayPalIpn();
        $this->deliverPayPalIpn();
        $this->assertSame(1, $this->db->renewals);
        PayPalDelivery::$webhook->webhook_event->resource->id = 'txn_setup';
        $this->deliverPayPalWebhook();
        $this->assertSame(1, $this->db->renewals, 'Same setup-fee sale through the webhook');
        $_POST['initial_payment_status'] = 'Pending';
        $_POST['initial_payment_txn_id'] = 'txn_pending';
        $this->deliverPayPalIpn();
        $this->assertSame(1, $this->db->renewals, 'A pending first payment is not paid');
    }
    public function testPayPalNewAgreementOfAReturningSubscriberIsFoundThroughItsCheckout() {
        PayPalDelivery::$agreement = ['users_id' => 0, 'subscriptions_plans_id' => 0];
        $this->checkoutRow('subscription', ['plans_id' => 3]);
        PayPalYPT_checkout::$rows[1]['agreement_id'] = 'agreement_1';
        PayPalYPT_checkout::$rows[1]['status'] = 'active';
        $this->deliverPayPalIpn();
        $this->assertSame(1, $this->db->renewals, implode(' | ', $this->db->errorLogs));
        $this->assertSame('completed', PayPalYPT_checkout::getById(1)['status']);
        $this->assertSame('subscribed', PayPalYPT_checkout::getById(1)['fulfillment']);
    }
    public function testPayPalOneTimeWebhookOnlyTriggersAFreshReadOfTheCheckout() {
        $this->checkoutRow();
        PayPalDelivery::$webhook = json_decode('{"webhook_event":{"id":"WH-event_2","event_type":"PAYMENT.SALE.COMPLETED","resource":{"id":"SALE-1","parent_payment":"PAYID-1","amount":{"total":"999.00","currency":"USD"}}}}');
        $this->deliverPayPalWebhook();
        $this->assertSame([['AVPP000000000000000000000001', 'webhook']], PayPalYPT::$reconciled);
        $this->assertSame([], $this->db->logs, 'The event amount is never credited directly');
        $this->assertSame([['webhook', 'PAYMENT.SALE.COMPLETED']], PayPalYPT::$deliveries);
        PayPalYPT::$reconcileFails = true;
        PayPalYPT::$deliveries = [];
        $this->deliverPayPalWebhook();
        $this->assertSame(500, PayPalDelivery::$status, 'PayPal retries when PayPal cannot be read now');
        $this->assertSame([], PayPalYPT::$deliveries, 'A failed delivery is not recorded as evidence');
    }
    public function testPayPalRefundAfterCreditIsFlaggedButNeverCreditedOrDebited() {
        $this->checkoutRow();
        PayPalYPT_checkout::$rows[1]['status'] = 'completed';
        PayPalDelivery::$webhook = json_decode('{"webhook_event":{"id":"WH-event_3","event_type":"PAYMENT.SALE.REFUNDED","resource":{"id":"REFUND-1","sale_id":"SALE-1","parent_payment":"PAYID-1","amount":{"total":"10.00","currency":"USD"}}}}');
        $this->deliverPayPalWebhook();
        $this->assertSame(200, PayPalDelivery::$status);
        $this->assertSame('refunded', PayPalYPT_checkout::getById(1)['status']);
        $this->assertSame('refunded_after_credit', PayPalYPT_checkout::getById(1)['status_detail']);
        $this->assertSame([], $this->db->logs);
        $this->assertSame([7 => 0, 8 => 0], $this->db->balances);
    }
    public function testUnauthenticPayPalWebhookIsAnsweredWith400() {
        PayPalDelivery::$verified = false;
        $this->deliverPayPalWebhook();
        $this->assertSame(400, PayPalDelivery::$status);
        $this->assertSame([], PayPalYPT::$deliveries);
    }
    public function testPayPalOneTimeIpnOnlyTriggersAFreshReadOfTheCheckout() {
        $this->checkoutRow();
        $_POST = ['txn_type' => 'express_checkout', 'payment_status' => 'Completed', 'invoice' => 'AVPP000000000000000000000001',
            'txn_id' => 'SALE-1', 'mc_gross' => '999.00', 'mc_currency' => 'USD'];
        $this->deliverPayPalIpn();
        $this->assertSame([['AVPP000000000000000000000001', 'ipn']], PayPalYPT::$reconciled);
        $this->assertSame([], $this->db->logs);
        PayPalYPT::$reconcileFails = true;
        $this->deliverPayPalIpn();
        $this->assertSame(500, PayPalDelivery::$status);
    }
    public function testConcurrentDeliveryMustRetryWithoutMutation() {
        $this->db->busy = true;
        $this->expectException(RuntimeException::class);
        try { $this->deliver(); }
        finally { $this->assertSame([], $this->db->logs); $this->assertSame(0, $this->db->renewals); }
    }
}
