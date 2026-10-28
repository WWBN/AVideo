<?php

namespace Tests\Unit\AdminPageWarning;

use PHPUnit\Framework\TestCase;

class AVideoPlugin
{
    public static $installed = true;
    public static $enabled = true;

    public static function exists($name) { return self::$installed; }
    public static function isEnabledByName($name) { return self::$enabled; }
}

function __($text) { return $text; }

class AdminPageWarningTest extends TestCase
{
    private $dir;
    private $originalGlobal;

    protected function setUp(): void
    {
        $this->originalGlobal = $GLOBALS['global'] ?? null;
        $this->dir = sys_get_temp_dir() . '/avideo-admin-page-' . bin2hex(random_bytes(6)) . '/';
        mkdir($this->dir . 'plugin/Subscription/page', 0777, true);
        mkdir($this->dir . 'plugin/Subscription/browse_page', 0777, true);
        $GLOBALS['global'] = ['systemRootPath' => $this->dir, 'webSiteRootURL' => 'https://example.test/'];
        AVideoPlugin::$installed = AVideoPlugin::$enabled = true;
        if (!function_exists(__NAMESPACE__ . '\\getAdminPageWarningHTML')) {
            $source = file_get_contents(APP_ROOT . '/admin/functions.php');
            $start = strpos($source, 'function getAdminPageWarningHTML(');
            $end = strpos($source, 'function createTable(', $start);
            eval('namespace ' . __NAMESPACE__ . ';' . substr($source, $start, $end - $start));
        }
    }

    protected function tearDown(): void
    {
        foreach (['page', 'browse_page'] as $folder) {
            foreach (glob($this->dir . 'plugin/Subscription/' . $folder . '/*') as $file) {
                unlink($file);
            }
            rmdir($this->dir . 'plugin/Subscription/' . $folder);
        }
        rmdir($this->dir . 'plugin/Subscription');
        rmdir($this->dir . 'plugin');
        rmdir($this->dir);
        $GLOBALS['global'] = $this->originalGlobal;
    }

    private function routeSubscription()
    {
        global $global;
        $source = file_get_contents(APP_ROOT . '/admin/index.php');
        $start = strpos($source, '    case "monetize_subscription":');
        $end = strpos($source, '    case "monetize_vast":', $start);
        eval('switch ("monetize_subscription") {' . substr($source, $start, $end - $start) . '}');
        return [$includeHead, $includeBody];
    }

    public function testCurrentPluginEditorIsPreferredWhenBothLayoutsExist()
    {
        touch($this->dir . 'plugin/Subscription/page/editor_body.php');
        touch($this->dir . 'plugin/Subscription/browse_page/editor_body.php');
        [$head, $body] = $this->routeSubscription();
        $this->assertSame($this->dir . 'plugin/Subscription/page/editor_head.php', $head);
        $this->assertSame($this->dir . 'plugin/Subscription/page/editor_body.php', $body[0]);
        $this->assertSame($this->dir . 'admin/monetize_subscription.php', $body[1]);
        $this->assertSame('', getAdminPageWarningHTML($body[0]));
    }

    public function testLegacyPluginEditorRemainsUsable()
    {
        touch($this->dir . 'plugin/Subscription/browse_page/editor_body.php');
        [$head, $body] = $this->routeSubscription();
        $this->assertSame($this->dir . 'plugin/Subscription/browse_page/editor_head.php', $head);
        $this->assertSame('', getAdminPageWarningHTML($body[0]));
    }

    public function testAbsentPluginIsNamedAndOffersTheStore()
    {
        AVideoPlugin::$installed = false;
        AVideoPlugin::$enabled = false;
        [, $body] = $this->routeSubscription();
        $html = getAdminPageWarningHTML($body[0]);
        $this->assertStringContainsString('The Subscription plugin is not installed.', $html);
        $this->assertStringContainsString('https://streamphp.com/marketplace/', $html);
        $this->assertStringNotContainsString('installed but disabled', $html);
    }

    public function testDisabledPluginIsNamedWithoutSuggestingAPurchase()
    {
        touch($this->dir . 'plugin/Subscription/page/editor_body.php');
        AVideoPlugin::$enabled = false;
        [, $body] = $this->routeSubscription();
        $html = getAdminPageWarningHTML($body[0]);
        $this->assertStringContainsString('The Subscription plugin is installed but disabled.', $html);
        $this->assertStringContainsString('browse_page=plugins', $html);
        $this->assertStringNotContainsString('marketplace', $html);
        $this->assertStringNotContainsString('not installed', $html);
    }

    public function testMissingEditorIsAnUpdateProblemForAnInstalledPlugin()
    {
        [, $body] = $this->routeSubscription();
        $html = getAdminPageWarningHTML($body[0]);
        $this->assertStringContainsString('The Subscription plugin is installed, but its administration page is unavailable.', $html);
        $this->assertStringNotContainsString('marketplace', $html);
        $this->assertStringNotContainsString('not installed', $html);
    }

    public function testMissingCorePageDoesNotAdvertiseAPlugin()
    {
        $html = getAdminPageWarningHTML($this->dir . 'admin/missing.php');
        $this->assertStringContainsString('Update AVideo', $html);
        $this->assertStringNotContainsString('marketplace', $html);
    }
}
