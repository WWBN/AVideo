<?php

namespace Tests\Security\PaymentConnectionGuard;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

$paymentConnectionSource = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsMySQL.php');
foreach (['_mysql_connect', 'canCloseConnection', '_mysql_close'] as $paymentConnectionFunction) {
    $paymentConnectionStart = strpos($paymentConnectionSource, 'function ' . $paymentConnectionFunction . '(');
    $paymentConnectionEnd = strpos($paymentConnectionSource, '\nfunction ', $paymentConnectionStart + 1);
    // Normalize line endings before extracting up to the next function.
    $paymentConnectionBody = str_replace("\r\n", "\n", $paymentConnectionSource);
    $paymentConnectionStart = strpos($paymentConnectionBody, 'function ' . $paymentConnectionFunction . '(');
    $paymentConnectionEnd = strpos($paymentConnectionBody, "\nfunction ", $paymentConnectionStart + 1);
    eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use Throwable; ' . substr($paymentConnectionBody, $paymentConnectionStart, $paymentConnectionEnd - $paymentConnectionStart));
}
unset($paymentConnectionSource, $paymentConnectionFunction, $paymentConnectionStart, $paymentConnectionEnd, $paymentConnectionBody);

function _mysql_is_open() { return $GLOBALS['global']['mysqli']->open; }
function isCommandLineInterface() { return false; }
function getRealIpAddr() { return '203.0.113.10'; }
function _error_log($message, $level = '') {}
class AVideoLog { public static $WARNING = 'warning'; }
class mysqli {
    public static $connections = 0;
    public $open = true;
    public function __construct(...$args) { ++self::$connections; }
    public function close() { $this->open = false; }
    public function select_db($name) {}
    public function set_charset($name) { return true; }
}

class PaymentConnectionGuardTest extends TestCase
{
    private $savedGlobals;
    protected function setUp(): void {
        foreach (['global', 'mysqlHost', 'mysqlUser', 'mysqlPass', 'mysqlDatabase', 'mysqlPort', 'mysql_connect_is_persistent', 'mysql_connect_was_closed', 'isStandAlone', '_SERVER'] as $name) {
            $this->savedGlobals[$name] = $GLOBALS[$name] ?? null;
        }
        $db = new mysqli();
        $GLOBALS['global'] = ['mysqli' => $db, 'mysqlTransactionConnection' => $db];
        $GLOBALS['mysqlHost'] = 'disposable.test';
        $GLOBALS['mysqlUser'] = 'test';
        $GLOBALS['mysqlPass'] = 'test';
        $GLOBALS['mysqlDatabase'] = 'test';
        $GLOBALS['mysqlPort'] = 0;
        $GLOBALS['mysql_connect_is_persistent'] = false;
        $GLOBALS['isStandAlone'] = false;
        $_SERVER['SCRIPT_NAME'] = '/plugin/StripeYPT/ipn.php';
        mysqli::$connections = 0;
    }
    protected function tearDown(): void {
        foreach ($this->savedGlobals as $name => $value) { $GLOBALS[$name] = $value; }
    }
    public function testSocketStyleCloseKeepsPaymentConnectionAndLockAlive() {
        $this->assertFalse(canCloseConnection());
        _mysql_close();
        $this->assertTrue(_mysql_is_open());
    }
    public function testLostPaymentConnectionCannotReconnectInAutocommitMode() {
        $GLOBALS['global']['mysqli']->open = false;
        $this->expectException(RuntimeException::class);
        try { _mysql_connect(); }
        finally { $this->assertSame(0, mysqli::$connections); }
    }
    public function testOrdinaryRequestStillClosesItsConnection() {
        unset($GLOBALS['global']['mysqlTransactionConnection']);
        $this->assertTrue(canCloseConnection());
        _mysql_close();
        $this->assertFalse(_mysql_is_open());
    }
}
