<?php

namespace Tests\Security\StripeSinglePaymentStatus;

use PHPUnit\Framework\TestCase;

class User
{
    public static $logged = true;
    public static function isLogged() { return self::$logged; }
    public static function getId() { return 7; }
}
class AVideoLog { public static $ERROR = 'error'; }
class ObjectYPT {
    public static $clears = 0;
    public static function deleteAllSessionCache() { ++self::$clears; }
}
class AVideoPlugin
{
    public static $livePurchase = false;
    public static $credited = false;
    public static $calls = [];
    public static $videoAccess = 1;
    public static function loadPluginIfEnabled($name) { return new self(); }
    public function userCanWatchVideo($user, $video) { return self::$videoAccess; }
    public function getSubscriptionToPlan($user, $schedule) { return self::$livePurchase; }
    public function isStripePaymentCredited($intent, $user) {
        self::$calls[] = [$intent, $user];
        return self::$credited;
    }
}
class Live { public static function getLinkToLiveFromUsers_id($user) { return 'https://test.invalid/live/0/' . $user; } }
function __($text) { return $text; }
function header($value) {}
function _error_log($message, $level = null) {}
function forbiddenPage($message) { throw new \RuntimeException($message, 403); }
function forbidIfInvalidToken() {
    if (($_POST['globalToken'] ?? '') !== 'test_token') { forbiddenPage('Invalid token'); }
}

class StripeSinglePaymentStatusTest extends TestCase
{
    protected function setUp(): void
    {
        User::$logged = true;
        AVideoPlugin::$credited = false;
        AVideoPlugin::$calls = [];
        ObjectYPT::$clears = 0;
        AVideoPlugin::$livePurchase = false;
    }

    private function status(array $post, $simulateBootstrap = false)
    {
        $global = ['webSiteRootURL' => 'https://test.invalid/'];
        $previous = $_POST;
        $_POST = $post;
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/singlePaymentStatus.json.php');
        $source = preg_replace('/require_once\s+[^;]+;/', $simulateBootstrap ? '\$_POST["payment_intent_id"] = 0;' : '', $source);
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; use \\Throwable; use \\RuntimeException; use \\InvalidArgumentException; ?>' . $source);
            return json_decode(ob_get_contents(), true);
        } finally {
            ob_end_clean();
            $_POST = $previous;
        }
    }

    public function testOnlyCommittedCreditForLoggedInUserCanReportSuccess(): void
    {
        $post = ['payment_intent_id' => 'pi_test', 'users_id' => 8, 'globalToken' => 'test_token'];
        $this->assertSame(['error' => false, 'credited' => false, 'msg' => ''], $this->status($post));
        AVideoPlugin::$credited = true;
        $this->assertTrue($this->status($post)['credited']);
        $this->assertSame([['pi_test', 7], ['pi_test', 7]], AVideoPlugin::$calls);
    }

    public function testAuthenticationIsRequired(): void
    {
        User::$logged = false;
        $this->expectExceptionCode(403);
        $this->status(['payment_intent_id' => 'pi_test', 'globalToken' => 'test_token']);
    }

    public function testStripeIdentifierSurvivesBootstrapIntegerCoercion(): void
    {
        AVideoPlugin::$credited = true;
        $this->assertTrue($this->status(['payment_intent_id' => 'pi_test', 'globalToken' => 'test_token'], true)['credited']);
        $this->assertSame([['pi_test', 7]], AVideoPlugin::$calls);
    }

    public function testPlaybackReadinessRequiresBothCreditAndVideoAccess(): void
    {
        $post = ['payment_intent_id' => 'pi_test', 'videos_id' => 640, 'globalToken' => 'test_token'];
        $this->assertFalse($this->status($post)['rental_ready']);
        $this->assertSame(0, ObjectYPT::$clears);
        AVideoPlugin::$credited = true;
        AVideoPlugin::$videoAccess = -1;
        $this->assertFalse($this->status($post)['rental_ready']);
        $this->assertSame(0, ObjectYPT::$clears);
        AVideoPlugin::$videoAccess = 1;
        $this->assertTrue($this->status($post)['rental_ready']);
        $this->assertSame(1, ObjectYPT::$clears, 'Clear the browser denial only after credit and verified rental access');
    }

    public function testCsrfTokenIsRequired(): void
    {
        $this->expectExceptionCode(403);
        $this->status(['payment_intent_id' => 'pi_test']);
    }

    public function testLiveReadinessRequiresCommittedCreditAndActivePurchase(): void
    {
        $post = ['payment_intent_id' => 'pi_test', 'ppvlive_schedule_id' => 9, 'globalToken' => 'test_token'];
        $this->assertFalse($this->status($post)['live_ready']);
        AVideoPlugin::$credited = true;
        $this->assertFalse($this->status($post)['live_ready']);
        AVideoPlugin::$livePurchase = ['id' => 1, 'status' => 'i', 'schedule' => ['users_id' => 8]];
        $this->assertFalse($this->status($post)['live_ready']);
        $this->assertSame(0, ObjectYPT::$clears);
        AVideoPlugin::$livePurchase['status'] = 'a';
        $response = $this->status($post);
        $this->assertTrue($response['live_ready']);
        $this->assertSame('https://test.invalid/live/0/8', $response['watch_url']);
        $this->assertSame(1, ObjectYPT::$clears);
        AVideoPlugin::$livePurchase['schedule']['future'] = true;
        $response = $this->status($post);
        $this->assertFalse($response['live_started']);
        $this->assertSame('https://test.invalid/plugin/PayPerViewLive/View/buy.php?users_id=8&ppvlive_schedule_id=9', $response['watch_url']);
    }

    public function testInvalidIntentNeverQueriesTheLedger(): void
    {
        $response = $this->status(['payment_intent_id' => "pi_test' OR 1=1", 'globalToken' => 'test_token']);
        $this->assertTrue($response['error']);
        $this->assertFalse($response['credited']);
        $this->assertSame([], AVideoPlugin::$calls);
        $this->assertSame('An error occurred', $response['msg']);
    }
}
