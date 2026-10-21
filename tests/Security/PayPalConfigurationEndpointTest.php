<?php
namespace Tests\Security\PayPalConfigurationEndpoint;

use PHPUnit\Framework\TestCase;

class User { public static $admin = true; public static function isAdmin() { return self::$admin; } }
class AVideoLog { public static $ERROR = 'error'; }
class AVideoPlugin
{
    public static $loads = 0, $editable = true;
    public static function loadPluginIfEnabled($name) { ++self::$loads; return new self(); }
    public static function isEnabledByName($name) { return true; }
    public function getUUID() { return 'paypal'; }
    public function canEditPlugin() { return self::$editable; }
    public function getEmptyDataObject() { return (object) ['ClientID' => '', 'ClientSecret' => '', 'webhookId' => '']; }
    public function getDataObject() { return (object) ['enablePlugin_YPTWalletPayPal' => true, 'currency' => 'USD']; }
}
class Plugin {
    public static $clears = 0, $stored = '{"ClientID":"app","ClientSecret":"secret","paymentButtonLabel":"Pay"}';
    public static function getPluginByUUID($uuid, $fresh) { return ['id' => 21, 'object_data' => self::$stored]; }
    public static function getTableName() { return 'plugins'; }
    public static function deletePluginCache($uuid) { ++self::$clears; }
}
class PluginAbstract { public static $dataObject = []; }
class PayPalYPT_checkout { public static function isInstalled() { return true; } }
class PayPalYPT
{
    public static function getWebhookURL() { return 'https://test.invalid/plugin/PayPalYPT/webhook.php'; }
    public static function getIPNURL() { return 'https://test.invalid/plugin/PayPalYPT/ipn.php'; }
    public static function getRecordedDelivery($channel, $settings) { return []; }
}
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
class PayPalConfiguration {
    public static $checks = 0, $features = null, $settings = null;
    public function __construct($settings, $url) { self::$settings = $settings; }
    public function check($features) { ++self::$checks; self::$features = $features; return ['checks' => [], 'webhooks' => [], 'can_configure' => true]; }
    public function configure($save) { return ['status' => $save('WH-12345678ABC') ? 'pass' : 'fail']; }
}
function header($value) {}
function __($value) { return $value; }
function _error_log($message, $level = null) {}
function forbiddenPage($message) { throw new \RuntimeException($message, 403); }
function forbidIfNotPost() { if ($_SERVER['REQUEST_METHOD'] !== 'POST') { throw new \RuntimeException('Method', 405); } }
function forbidIfInvalidToken() { if (($_POST['globalToken'] ?? '') !== 'test_token') { forbiddenPage('Token'); } }

class PayPalConfigurationEndpointTest extends TestCase
{
    private $root, $previousGlobal;
    protected function setUp(): void {
        User::$admin = true;
        AVideoPlugin::$loads = 0;
        AVideoPlugin::$editable = true;
        PayPalConfiguration::$checks = 0;
        PayPalConfiguration::$features = PayPalConfiguration::$settings = null;
        Plugin::$clears = 0;
        sqlDAL::$writes = [];
        sqlDAL::$affected = 1;
        $this->previousGlobal = $GLOBALS['global'] ?? [];
        $this->root = sys_get_temp_dir() . '/paypal-configuration-' . bin2hex(random_bytes(8)) . '/';
        mkdir($this->root);
    }
    protected function tearDown(): void {
        foreach (glob($this->root . 'videos/paypal-locks/*') ?: [] as $file) { unlink($file); }
        if (is_dir($this->root . 'videos/paypal-locks')) {
            rmdir($this->root . 'videos/paypal-locks');
            rmdir($this->root . 'videos');
        }
        rmdir($this->root);
        $GLOBALS['global'] = $this->previousGlobal;
    }
    private function request($post = [], $method = 'POST') {
        $global = ['webSiteRootURL' => 'https://test.invalid/', 'systemRootPath' => $this->root];
        $previous = [$_POST, $_SERVER['REQUEST_METHOD'] ?? null];
        $_POST = $post;
        $_SERVER['REQUEST_METHOD'] = $method;
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/configuration.json.php');
        $source = preg_replace('/require(?:_once)?\s+[^;]+;/', '', $source);
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; use Throwable; use RuntimeException; use InvalidArgumentException; use stdClass; ?>' . $source);
            return json_decode(ob_get_contents(), true);
        } finally {
            ob_end_clean();
            [$_POST, $_SERVER['REQUEST_METHOD']] = $previous;
        }
    }
    public function testAdministratorRequiredBeforeAnyPayPalCalls(): void {
        User::$admin = false;
        try { $this->request(['globalToken' => 'test_token']); $this->fail('Must deny'); }
        catch (\RuntimeException $exception) { $this->assertSame(403, $exception->getCode()); }
        $this->assertSame(0, AVideoPlugin::$loads);
    }
    public function testCsrfRequiredEvenForReadOnlyChecks(): void {
        try { $this->request(); $this->fail('Must deny'); }
        catch (\RuntimeException $exception) { $this->assertSame(403, $exception->getCode()); }
        $this->assertSame(0, AVideoPlugin::$loads);
    }
    public function testGetCannotInspectOrMutate(): void {
        try { $this->request(['globalToken' => 'test_token', 'action' => 'configure'], 'GET'); $this->fail('Must deny'); }
        catch (\RuntimeException $exception) { $this->assertSame(405, $exception->getCode()); }
        $this->assertSame(0, AVideoPlugin::$loads);
    }
    public function testUnknownActionAndArraysDoNotReachPayPal(): void {
        foreach (['delete', 'deleteAll', ['configure']] as $action) {
            $response = $this->request(['globalToken' => 'test_token', 'action' => $action]);
            $this->assertTrue($response['error']);
        }
        $this->assertSame(0, AVideoPlugin::$loads);
        $this->assertSame(0, PayPalConfiguration::$checks);
    }
    public function testDefaultRequestIsCheckOnlyAndReportsEnabledFeatures(): void {
        $response = $this->request(['globalToken' => 'test_token']);
        $this->assertFalse($response['error']);
        $this->assertSame(1, PayPalConfiguration::$checks);
        $this->assertArrayNotHasKey('setup', $response);
        $this->assertSame([], sqlDAL::$writes);
        $this->assertTrue(PayPalConfiguration::$features['wallet_paypal']);
        $this->assertSame('USD', PayPalConfiguration::$features['currency']);
        $this->assertSame('app', PayPalConfiguration::$settings->ClientID, 'Stored settings are read fresh');
    }
    public function testSetupSavesOnlyTheWebhookIdIfTheSettingsSnapshotStillMatches(): void {
        $response = $this->request(['globalToken' => 'test_token', 'action' => 'configure']);
        $this->assertFalse($response['error']);
        $this->assertSame('pass', $response['setup']['status']);
        $this->assertCount(1, sqlDAL::$writes);
        [$sql, $formats, $values] = sqlDAL::$writes[0];
        $this->assertSame('sis', $formats);
        $this->assertSame(21, $values[1]);
        $this->assertSame(hash('sha256', Plugin::$stored), $values[2]);
        $this->assertStringContainsString("SHA2(COALESCE(object_data, ''), 256) = ?", $sql);
        $saved = json_decode(stripslashes($values[0]), true);
        $this->assertSame(['ClientID' => 'app', 'ClientSecret' => 'secret', 'paymentButtonLabel' => 'Pay', 'webhookId' => 'WH-12345678ABC'], $saved,
            'Other settings are preserved; only the webhook ID is added');
        $this->assertSame(1, Plugin::$clears);
    }
    public function testConcurrentSettingsChangeIsNotReportedAsSaved(): void {
        sqlDAL::$affected = 0;
        $response = $this->request(['globalToken' => 'test_token', 'action' => 'configure']);
        $this->assertSame('fail', $response['setup']['status']);
        $this->assertSame(0, Plugin::$clears);
    }
    public function testLockedInstallationCannotConfigure(): void {
        AVideoPlugin::$editable = false;
        $response = $this->request(['globalToken' => 'test_token', 'action' => 'configure']);
        $this->assertTrue($response['error']);
        $this->assertSame([], sqlDAL::$writes);
        $this->assertSame('Advanced configuration changes are disabled', $response['msg']);
    }
    public function testLockedInstallationStillRunsReadOnlyChecks(): void {
        AVideoPlugin::$editable = false;
        $response = $this->request(['globalToken' => 'test_token']);
        $this->assertFalse($response['error']);
        $this->assertFalse($response['report']['can_configure']);
    }
    public function testExistingSetupLockPreventsConcurrentMutation(): void {
        mkdir($this->root . 'videos/paypal-locks/', 0700, true);
        $lock = fopen($this->root . 'videos/paypal-locks/configuration-' . hash('sha256', 'https://test.invalid/') . '.lock', 'c+');
        flock($lock, LOCK_EX);
        try {
            $response = $this->request(['globalToken' => 'test_token', 'action' => 'configure']);
            $this->assertTrue($response['error']);
            $this->assertSame('Another PayPal setup is in progress', $response['msg']);
            $this->assertSame([], sqlDAL::$writes);
        } finally { flock($lock, LOCK_UN); fclose($lock); }
    }
    public function testWebhookValidationNeverCreatesOrDeletesWebhooks(): void {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/PayPalYPT.php');
        $start = strpos($source, 'static function validateWebhook()');
        $end = strpos($source, 'static function getWebhookURL()', $start);
        $validate = substr($source, $start, $end - $start);
        $this->assertStringNotContainsString('createWebhook', $validate);
        $this->assertStringNotContainsString('getOrCreateWebhook', $validate);
        $this->assertStringNotContainsString('var_dump', $source);
        $delete = substr($source, strpos($source, 'static function deleteAllWebhooks()'), 400);
        $this->assertStringNotContainsString('->delete(', $delete, 'Bulk webhook deletion stays disabled');
        $this->assertStringNotContainsString("'DEBUG'", file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/bootstrap.php'));
        foreach (['test.php', 'resend.php'] as $file) {
            $script = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/' . $file);
            $this->assertStringContainsString('User::isAdmin()', $script, "{$file} is administrator-only");
            $this->assertStringNotContainsString('var_dump', $script);
        }
    }
}
