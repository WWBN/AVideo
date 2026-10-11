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
    . 'public function getDataObject() { return (object)["virtual_currency_enable" => false]; }
       public function getOrCreateWallet($id) { return new Wallet($id); }}');
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

function _error_log($message, $level = '') { $GLOBALS['global']['mysqli']->errorLogs[] = $message; }
class AVideoLog {
    public static $ERROR = 'error';
    public static $WARNING = 'warning';
}
function mysqlBeginTransaction() { return $GLOBALS['global']['mysqli']->begin_transaction(); }
function mysqlCommit() { return $GLOBALS['global']['mysqli']->commit(); }
function mysqlRollback() { return $GLOBALS['global']['mysqli']->rollback(); }

class User {
    public static function isAdmin() { return true; }
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

class PaymentWebhookIdempotencyTest extends TestCase
{
    private $previousGlobal;
    private $previousDatabase;
    private $db;
    private $payments;

    protected function setUp(): void {
        $this->previousGlobal = $GLOBALS['global'] ?? null;
        $this->previousDatabase = $GLOBALS['mysqlDatabase'] ?? null;
        $this->db = new Database();
        $GLOBALS['global'] = ['mysqli' => $this->db, 'webSiteRootURL' => 'https://example.test/'];
        $GLOBALS['mysqlDatabase'] = 'test_payments';
        $this->payments = new Payments();
    }
    protected function tearDown(): void {
        $GLOBALS['global'] = $this->previousGlobal;
        $GLOBALS['mysqlDatabase'] = $this->previousDatabase;
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
    public function testConcurrentDeliveryMustRetryWithoutMutation() {
        $this->db->busy = true;
        $this->expectException(RuntimeException::class);
        try { $this->deliver(); }
        finally { $this->assertSame([], $this->db->logs); $this->assertSame(0, $this->db->renewals); }
    }
}
