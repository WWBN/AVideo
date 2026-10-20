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
class AVideoPlugin
{
    public static $credited = false;
    public static $calls = [];
    public static function loadPluginIfEnabled($name) { return new self(); }
    public function isStripePaymentCredited($intent, $user) {
        self::$calls[] = [$intent, $user];
        return self::$credited;
    }
}
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
    }

    private function status(array $post)
    {
        $previous = $_POST;
        $_POST = $post;
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/singlePaymentStatus.json.php');
        $source = preg_replace('/require_once\s+[^;]+;/', '', $source);
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

    public function testCsrfTokenIsRequired(): void
    {
        $this->expectExceptionCode(403);
        $this->status(['payment_intent_id' => 'pi_test']);
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
