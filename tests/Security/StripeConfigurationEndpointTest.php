<?php
namespace Tests\Security\StripeConfigurationEndpoint;

use PHPUnit\Framework\TestCase;

class User { public static $admin = true; public static function isAdmin() { return self::$admin; } }
class AVideoLog { public static $ERROR = 'error'; }
class AVideoPlugin
{
    public static $loads = 0, $editable = true;
    public static function loadPluginIfEnabled($name) { ++self::$loads; return new self(); }
    public static function isEnabledByName($name) { return true; }
    public function getUUID() { return 'stripe'; }
    public function canEditPlugin() { return self::$editable; }
    public function getEmptyDataObject() { return (object) []; }
    public function getDataObject() { return (object) ['enablePlugin_YPTWalletStripe' => true]; }
}
class Plugin {
    public static $clears = 0;
    public static function getPluginByUUID($uuid, $fresh) { return ['id' => 17, 'object_data' => '{}']; }
    public static function getTableName() { return 'plugins'; }
    public static function deletePluginCache($uuid) { ++self::$clears; }
}
class PluginAbstract { public static $dataObject = []; }
class sqlDAL {
    public static $writes = [], $affected = 1;
    public static function writeSql($sql, $formats, $values) {
        self::$writes[] = [$sql, $formats, $values];
        return true;
    }
    public static function readSql($sql, $formats, $values, $fresh) {
        return (object) ['object_data' => self::$affected === 1 ? self::$writes[count(self::$writes) - 1][2][0] : '{}'];
    }
    public static function fetchAssoc($result) { return (array) $result; }
    public static function close($result) {}
}
class StripeConfiguration {
    public static $checks = 0;
    public function __construct($settings, $url) {}
    public function check($features) { ++self::$checks; return ['checks' => [], 'webhooks' => []]; }
    public function configure($save) {
        return ['status' => $save((object) ['automaticWebhookSecret' => (object) ['type' => 'encrypted', 'value' => 'ciphertext']]) ? 'pass' : 'fail'];
    }
}
function header($value) {}
function __($value) { return $value; }
function _error_log($message, $level = null) {}
function forbiddenPage($message) { throw new \RuntimeException($message, 403); }
function forbidIfNotPost() { if ($_SERVER['REQUEST_METHOD'] !== 'POST') { throw new \RuntimeException('Method', 405); } }
function forbidIfInvalidToken() { if (($_POST['globalToken'] ?? '') !== 'test_token') { forbiddenPage('Token'); } }

class StripeConfigurationEndpointTest extends TestCase
{
    private $root, $previousGlobal;
    protected function setUp(): void {
        User::$admin = true;
        AVideoPlugin::$loads = 0;
        AVideoPlugin::$editable = true;
        StripeConfiguration::$checks = 0;
        Plugin::$clears = 0;
        sqlDAL::$writes = [];
        sqlDAL::$affected = 1;
        $this->previousGlobal = $GLOBALS['global'] ?? [];
        $this->root = sys_get_temp_dir() . '/stripe-configuration-' . bin2hex(random_bytes(8)) . '/';
        mkdir($this->root);
    }
    protected function tearDown(): void {
        foreach (glob($this->root . 'videos/stripe-payment-locks/*') ?: [] as $file) { unlink($file); }
        if (is_dir($this->root . 'videos/stripe-payment-locks')) {
            rmdir($this->root . 'videos/stripe-payment-locks');
            rmdir($this->root . 'videos');
        }
        rmdir($this->root);
        $GLOBALS['global'] = $this->previousGlobal;
    }
    private function request($post = [], $method = 'POST', $file = 'configuration.json.php') {
        $global = ['webSiteRootURL' => 'https://test.invalid/', 'systemRootPath' => $this->root];
        $previous = [$_POST, $_SERVER['REQUEST_METHOD'] ?? null];
        $_POST = $post;
        $_SERVER['REQUEST_METHOD'] = $method;
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/' . $file);
        $source = preg_replace('/require(?:_once)?\s+[^;]+;/', '', $source);
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; use Throwable; use RuntimeException; use InvalidArgumentException; ?>' . $source);
            return json_decode(ob_get_contents(), true);
        } finally {
            ob_end_clean();
            [$_POST, $_SERVER['REQUEST_METHOD']] = $previous;
        }
    }
    public function testAdministratorRequiredBeforeAnyStripeCalls(): void {
        User::$admin = false;
        try { $this->request(['globalToken' => 'test_token']); $this->fail('Must deny'); }
        catch (\RuntimeException $exception) { $this->assertSame(403, $exception->getCode()); }
        $this->assertSame(0, AVideoPlugin::$loads);
    }
    public function testCsrfRequiredEvenForSensitiveReadProbes(): void {
        try { $this->request(); $this->fail('Must deny'); }
        catch (\RuntimeException $exception) { $this->assertSame(403, $exception->getCode()); }
        $this->assertSame(0, AVideoPlugin::$loads);
    }
    public function testGetCannotMutateOrInspectConfiguration(): void {
        try { $this->request(['globalToken' => 'test_token', 'action' => 'configure'], 'GET'); $this->fail('Must deny'); }
        catch (\RuntimeException $exception) { $this->assertSame(405, $exception->getCode()); }
        $this->assertSame(0, AVideoPlugin::$loads);
    }
    public function testUnknownActionAndInputArraysDoNotReachStripe(): void {
        foreach (['delete', ['configure']] as $action) {
            $response = $this->request(['globalToken' => 'test_token', 'action' => $action]);
            $this->assertTrue($response['error']);
        }
        $this->assertSame(0, AVideoPlugin::$loads);
    }
    public function testDefaultRequestIsCheckOnly(): void {
        $response = $this->request(['globalToken' => 'test_token']);
        $this->assertFalse($response['error']);
        $this->assertSame(1, StripeConfiguration::$checks);
        $this->assertFalse($response['webhook']);
        $this->assertArrayNotHasKey('setup', $response);
    }
    public function testLegacyUrlUsesSameSecuredHandler(): void {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/createWebhook.json.php');
        $this->assertStringContainsString("require __DIR__ . '/configuration.json.php'", $source);
        $this->assertStringNotContainsString('WebhookEndpoint::create', $source);
    }
    public function testSetupSavesOnlyIfTheSettingsSnapshotStillMatches(): void {
        $response = $this->request(['globalToken' => 'test_token', 'action' => 'configure']);
        $this->assertFalse($response['error']);
        $this->assertSame('pass', $response['setup']['status']);
        $this->assertCount(1, sqlDAL::$writes);
        $this->assertSame('sis', sqlDAL::$writes[0][1]);
        $this->assertSame(17, sqlDAL::$writes[0][2][1]);
        $this->assertSame(hash('sha256', '{}'), sqlDAL::$writes[0][2][2]);
        $this->assertStringContainsString("SHA2(COALESCE(object_data, ''), 256) = ?", sqlDAL::$writes[0][0]);
        $this->assertSame(1, Plugin::$clears);
    }
    public function testConcurrentSettingsChangeIsNotReportedAsSaved(): void {
        sqlDAL::$affected = 0;
        $response = $this->request(['globalToken' => 'test_token', 'action' => 'configure']);
        $this->assertSame('fail', $response['setup']['status']);
        $this->assertSame(0, Plugin::$clears);
    }
    public function testLockedInstallationCannotSaveThroughTheNewEndpoint(): void {
        AVideoPlugin::$editable = false;
        $response = $this->request(['globalToken' => 'test_token', 'action' => 'configure']);
        $this->assertTrue($response['error']);
        $this->assertSame([], sqlDAL::$writes);
    }
    public function testExistingSetupLockPreventsConcurrentMutation(): void {
        mkdir($this->root . 'videos/stripe-payment-locks/', 0700, true);
        $lock = fopen($this->root . 'videos/stripe-payment-locks/configuration-' . hash('sha256', 'https://test.invalid/') . '.lock', 'c+');
        flock($lock, LOCK_EX);
        try {
            $response = $this->request(['globalToken' => 'test_token', 'action' => 'configure']);
            $this->assertTrue($response['error']);
            $this->assertSame([], sqlDAL::$writes);
        } finally { flock($lock, LOCK_UN); fclose($lock); }
    }
}
