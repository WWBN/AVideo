<?php
namespace Tests\Security\PayPalAgreementCancelEndpoint;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/plugin/PayPalYPT/Objects/PayPalYPT_checkout.php';

class RequestStopped extends \RuntimeException {}
class User {
    public static $logged = true, $admin = false;
    public static function isLogged() { return self::$logged; }
    public static function isAdmin() { return self::$admin; }
    public static function getId() { return 7; }
}
class Subscription {
    public static $owner = true, $lookups = [];
    public static function isAgreementFromUser($id, $user) { self::$lookups[] = [$id, $user]; return self::$owner; }
    public static function getFromAgreement($id) { return ['id' => 9]; }
}
class SubscriptionTable {
    public static $row, $saves;
    public function __construct($id) {}
    public function setAgreement_id($id) { self::$row['agreement_id'] = $id; }
    public function save() { ++self::$saves; return true; }
}
class AVideoPlugin { public static function loadPluginIfEnabled($name) { return new PayPalYPT(); } }
class PayPalYPT {
    public static $canceled = [];
    public static function cancelAgreement($id) {
        self::$canceled[] = $id;
        return new class { public function getAgreementDetails() { return new \stdClass(); } };
    }
}
function header($value) {}
function _error_log($message) {}
function forbiddenPage($message) { throw new \RuntimeException($message, 403); }
function forbidIfNotPost() { if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { throw new \RuntimeException('Method', 405); } }
function forbidIfInvalidToken() { if (($_POST['globalToken'] ?? '') !== 'test-token') { forbiddenPage('Token'); } }

class PayPalAgreementCancelEndpointTest extends TestCase
{
    protected function setUp(): void {
        User::$logged = Subscription::$owner = true;
        User::$admin = false;
        PayPalYPT::$canceled = Subscription::$lookups = [];
        SubscriptionTable::$row = ['agreement_id' => 'I-AGREEMENT01', 'good_until' => '2026-10-28 12:00:00', 'status' => 'a'];
        SubscriptionTable::$saves = 0;
    }
    private function request($id = 'I-AGREEMENT01', $token = 'test-token', $method = 'POST') {
        $previous = [$_POST, $_GET, $_REQUEST, $_SERVER['REQUEST_METHOD'] ?? null];
        $_POST = ['agreement_id' => $id, 'globalToken' => $token];
        $_GET = $_REQUEST = [];
        $_SERVER['REQUEST_METHOD'] = $method;
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/PayPalAgreementCancel.json.php');
        $filters = file_get_contents(dirname(__DIR__, 2) . '/objects/security.php');
        $start = strpos($filters, '    // all variables with _id at the end');
        $end = strpos($filters, '    foreach ($securityFilterInt', $start);
        $filters = '$scanThis =& $_POST;' . substr($filters, $start, $end - $start);
        $source = str_replace("require_once '../../videos/configuration.php';", $filters, $source);
        $source = preg_replace('/^<\?php/', '', $source);
        $source = str_replace('die(', 'throw new RequestStopped(', $source);
        try {
            try { eval('namespace ' . __NAMESPACE__ . '; use stdClass; use \\PayPalYPT_checkout;' . $source); }
            catch (RequestStopped $stop) { return json_decode($stop->getMessage(), true); }
        } finally {
            [$_POST, $_GET, $_REQUEST, $_SERVER['REQUEST_METHOD']] = $previous;
        }
    }
    public function testExternalAgreementSurvivesTheRealIdFilterAndKeepsPaidAccess(): void {
        $result = $this->request();
        $this->assertFalse($result['error']);
        $this->assertSame([['I-AGREEMENT01', 7]], Subscription::$lookups);
        $this->assertSame(['I-AGREEMENT01'], PayPalYPT::$canceled);
        $this->assertSame(['agreement_id' => 'canceled', 'good_until' => '2026-10-28 12:00:00', 'status' => 'a'], SubscriptionTable::$row);
        $this->assertSame(1, SubscriptionTable::$saves);
    }
    public function testAnotherBuyersAgreementCannotBeCanceled(): void {
        Subscription::$owner = false;
        $this->assertTrue($this->request()['error']);
        $this->assertSame([], PayPalYPT::$canceled);
        $this->assertSame(0, SubscriptionTable::$saves);
    }
    public function testAdministratorCanCancelAValidAgreement(): void {
        User::$admin = true;
        Subscription::$owner = false;
        $this->assertFalse($this->request()['error']);
        $this->assertSame(['I-AGREEMENT01'], PayPalYPT::$canceled);
    }
    public function testInvalidProviderIdsNeverReachPayPal(): void {
        foreach ([['I-AGREEMENT01'], 'I-../../other', 'I', ''] as $id) {
            $this->assertTrue($this->request($id)['error']);
        }
        $this->assertSame([], PayPalYPT::$canceled);
    }
    public function testLoggedOutRequestCannotCancel(): void {
        User::$logged = false;
        $this->assertTrue($this->request()['error']);
        $this->assertSame([], PayPalYPT::$canceled);
    }
    public function testPostAndCsrfAreRequiredBeforeAnyProviderCall(): void {
        foreach ([['', 'POST', 403], ['test-token', 'GET', 405]] as [$token, $method, $status]) {
            try { $this->request('I-AGREEMENT01', $token, $method); $this->fail('Must deny'); }
            catch (\RuntimeException $error) { $this->assertSame($status, $error->getCode()); }
        }
        $this->assertSame([], PayPalYPT::$canceled);
    }
}
