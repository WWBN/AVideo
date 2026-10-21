<?php
namespace StripeSubscriptionRequestTests;

use PHPUnit\Framework\TestCase;
class Finished extends \RuntimeException {}
class User {
    public static $logged = true;
    public static function isLogged() { return self::$logged; }
    public static function isAdmin() { return false; }
    public static function getId() { return 7; }
}
class AVideoPlugin {
    public static $requests = [];
    public static function loadPluginIfEnabled($name) { return new self(); }
    public function getDataObject() { return new \stdClass(); }
    public function setUpSubscription($plan, $token) {
        self::$requests[] = [$plan, $token];
        return (object) ['id' => 'sub_test', 'status' => 'active'];
    }
}
class SubscriptionPlansTable { public function __construct($id) {} }
class SubscriptionTable {
    public static $access = false;
    public static function getCheckoutAccess($user, $plan) { return self::$access; }
    public static function getUserPlan($plan, $user) { return self::$access; }
}
class Subscription { public static function isTrial($plan) { return false; } }
class AVideoLog { public static $ERROR = 2; }
function __($text) { return $text; }
function header($value) {}
function session_status() { return PHP_SESSION_ACTIVE; }
function _error_log($message, $level = null) {}
function forbiddenPage($message) { throw new \RuntimeException($message, 403); }
function forbidIfInvalidToken() { if (($_POST['globalToken'] ?? '') !== 'test-token') { forbiddenPage('Invalid token'); } }

class StripeSubscriptionRequestTest extends TestCase {
    private function request($post) {
        $_POST = $_REQUEST = $post;
        $setUpSubscriptionErrorResponse = '';
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/YPTWallet/plugins/YPTWalletStripe/requestSubscription.json.php');
        $source = preg_replace('/require_once\s+[^;]+;/', '', $source);
        $source = str_replace(['exit;', 'die(json_encode($obj));'], ['throw new Finished();', 'echo json_encode($obj); throw new Finished();'], $source);
        $source = str_replace('catch (Throwable $th) {', 'catch (Throwable $th) { if ($th instanceof Finished) { throw $th; }', $source);
        ob_start();
        try {
            try { eval('namespace ' . __NAMESPACE__ . '; use \\Throwable; use \\stdClass; ?>' . $source); }
            catch (Finished $done) {}
            return json_decode(ob_get_contents(), true);
        } finally { ob_end_clean(); }
    }
    protected function setUp(): void {
        User::$logged = true;
        SubscriptionTable::$access = false;
        AVideoPlugin::$requests = [];
    }
    public function testExistingAccessRecoversWithoutCallingTheGateway(): void {
        SubscriptionTable::$access = ['good_until' => '2026-11-01 12:00:00'];
        $response = $this->request(['plans_id' => 3, 'stripeToken' => 'tok_test', 'globalToken' => 'test-token']);
        $this->assertFalse($response['error']);
        $this->assertTrue($response['already_active']);
        $this->assertSame([], AVideoPlugin::$requests);
    }
    public function testNewCheckoutUsesTheExistingGatewayOnce(): void {
        $response = $this->request(['plans_id' => 3, 'stripeToken' => 'tok_test', 'globalToken' => 'test-token']);
        $this->assertFalse($response['error']);
        $this->assertSame([[3, 'tok_test']], AVideoPlugin::$requests);
    }
    public function testLoginIsRequired(): void {
        User::$logged = false;
        $this->expectExceptionCode(403);
        $this->request(['plans_id' => 3, 'stripeToken' => 'tok_test', 'globalToken' => 'test-token']);
    }
    public function testCsrfIsRequired(): void {
        $this->expectExceptionCode(403);
        $this->request(['plans_id' => 3, 'stripeToken' => 'tok_test']);
    }
}
