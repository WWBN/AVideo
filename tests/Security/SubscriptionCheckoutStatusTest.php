<?php
namespace SubscriptionCheckoutTests;

use PHPUnit\Framework\TestCase;

class User {
    public static $logged = true;
    public static function isLogged() { return self::$logged; }
    public static function getId() { return 7; }
}
class AVideoPlugin {
    public static function loadPluginIfEnabled($name) { return new self(); }
    public function getCheckoutAccess($user, $plan) { return SubscriptionTable::getCheckoutAccess($user, $plan); }
}
class SubscriptionTable {
    public static $access = false;
    public static $calls = [];
    public static function getCheckoutAccess($user, $plan) {
        self::$calls[] = [$user, $plan];
        return self::$access;
    }
}
class AVideoLog { public static $ERROR = 2; }
class ObjectYPT {
    public static $clears = 0;
    public static function deleteAllSessionCache() { ++self::$clears; }
}
function __($text) { return $text; }
function header($value) {}
function _error_log($message, $level) {}
function forbiddenPage($message) { throw new \RuntimeException($message, 403); }
function forbidIfInvalidToken() {
    if (($_POST['globalToken'] ?? '') !== 'test-token') { forbiddenPage('Invalid token'); }
}

class SubscriptionCheckoutStatusTest extends TestCase {
    private function request($post) {
        $_POST = $post;
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/subscriptionStatus.json.php');
        $source = preg_replace('/require_once\s+[^;]+;/', '', $source);
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; use \\Throwable; use \\InvalidArgumentException; ?>' . $source);
            return json_decode(ob_get_contents(), true);
        } finally { ob_end_clean(); }
    }
    protected function setUp(): void {
        User::$logged = true;
        SubscriptionTable::$access = false;
        SubscriptionTable::$calls = [];
        ObjectYPT::$clears = 0;
    }
    public function testOnlySessionUserAndCommittedAccessDetermineReadiness(): void {
        $post = ['plans_id' => 2, 'users_id' => 99, 'globalToken' => 'test-token'];
        $this->assertFalse($this->request($post)['active']);
        $this->assertSame(0, ObjectYPT::$clears);
        SubscriptionTable::$access = ['good_until' => '2026-11-21 12:00:00'];
        $this->assertTrue($this->request($post)['active']);
        $this->assertSame(1, ObjectYPT::$clears, 'Evict the buyer session denial only after committed access');
        $this->assertSame([[7, 2], [7, 2]], SubscriptionTable::$calls);
    }
    public function testLoginIsRequired(): void {
        User::$logged = false;
        $this->expectExceptionCode(403);
        $this->request(['plans_id' => 2, 'globalToken' => 'test-token']);
    }
    public function testCsrfIsRequired(): void {
        $this->expectExceptionCode(403);
        $this->request(['plans_id' => 2]);
    }
    public function testInvalidPlanNeverReadsAccess(): void {
        $this->assertTrue($this->request(['plans_id' => 0, 'globalToken' => 'test-token'])['error']);
        $this->assertSame([], SubscriptionTable::$calls);
    }
}
