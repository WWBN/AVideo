<?php

namespace Tests\Security\WWBNIndexAuthorizationFixtures;

class State
{
    public static $admin = false;
}
class User
{
    public static function isAdmin() { return State::$admin; }
}
class AVideoLog { public static $SECURITY = 'security'; }
class RequestDenied extends \RuntimeException {}
function _error_log($message, $level = null) {}
function getCDN() { return 'https://media.example.net/'; }
function forbiddenPage($message, $log = false) { throw new RequestDenied($message, 403); }

// Execute the production request classifier without loading configuration, a database or WWBN.
foreach ([
    'objects/functions.php' => ['getRemoteAddrFromServerArray', 'isLoopbackIP', 'getForwardedClientIpFromServerArray',
        'isRequestFromTrustedProxy', 'getRealIpAddr', 'isCommandLineInterface', 'getRefferOrOrigin', 'isSameDomain', 'get_domain'],
    'objects/functionsSecurity.php' => ['forbidIfIsUntrustedRequest', 'isUntrustedRequest'],
    'objects/functionsAVideo.php' => ['isFromCDN', 'isAVideoUserAgent', 'requestComesFromSameDomainAsMyAVideo'],
] as $file => $functions) {
    $source = file_get_contents(dirname(__DIR__, 2) . '/' . $file);
    foreach ($functions as $function) {
        if (!preg_match('/^function ' . $function . '\([^\n]*\)\s*\{.*?^\}/ms', $source, $match)) {
            throw new \RuntimeException('Missing production function: ' . $function);
        }
        eval('namespace ' . __NAMESPACE__ . ';' . $match[0]);
    }
}
unset($file, $functions, $source, $function, $match);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\WWBNIndexAuthorizationFixtures\RequestDenied;
use Tests\Security\WWBNIndexAuthorizationFixtures\State;

/**
 * plugin/WWBNIndex/ajax.php (un-index/re-index/key reset/cache purge) and submitIndex.php are web-reachable and
 * act on WWBN with the site's own identity; both must refuse non-admins before any work runs (GHSA-6vp4-97cm-mrr8).
 */
class WWBNIndexAjaxAuthorizationTest extends TestCase
{
    private function source($file)
    {
        return str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/plugin/WWBNIndex/' . $file));
    }

    public function testAjaxRefusesNonAdminsAndCrossSiteRequestsBeforeAnyAction()
    {
        $source = $this->source('ajax.php');

        $guard = strpos($source, "if (!User::isAdmin()) {\n    forbiddenPage(");
        $csrf = strpos($source, 'forbidIfIsUntrustedRequest(');
        $this->assertNotFalse($guard, 'ajax.php must call forbiddenPage() for non-admins');
        $this->assertNotFalse($csrf, 'ajax.php is not a *.json.php file and must check the request origin itself');
        $this->assertGreaterThan(strpos($source, 'plugin/LoginControl/pgp/functions.php'), $guard, 'guard runs after the includes');
        foreach (['new WWBNIndex()', 'check_site_availability(', '$r = $_POST', '"authAccount"', 'clearCache(true)'] as $sideEffect) {
            $this->assertLessThan(strpos($source, $sideEffect), $guard, "admin check must precede {$sideEffect}");
            $this->assertLessThan(strpos($source, $sideEffect), $csrf, "origin check must precede {$sideEffect}");
        }
        // changePluginStatus is called right after the admin disables the plugin, so it must not require it enabled.
        $this->assertStringNotContainsString("isEnabledByName('WWBNIndex')", $source);
    }

    public function testSubmitIndexRefusesNonAdminsBeforeCallingWWBN()
    {
        $source = $this->source('submitIndex.php');

        $guard = strpos($source, "if (!User::isAdmin()) {\n    forbiddenPage(");
        $csrf = strpos($source, 'forbidIfIsUntrustedRequest(');
        $this->assertNotFalse($guard, 'submitIndex.php must call forbiddenPage() for non-admins');
        $this->assertNotFalse($csrf, 'submitIndex.php is not a *.json.php file and must check the request origin itself');
        $this->assertGreaterThan(strpos($source, 'plugin/WWBNIndex/WWBNIndex.php'), $guard);
        $this->assertLessThan(strpos($source, 'new WWBNIndex()'), $csrf);
        $this->assertLessThan(strpos($source, 'check_site_availability('), $guard);
        $this->assertLessThan(strpos($source, 'postVariables('), $guard);
    }

    /** @dataProvider requestCases */
    public function testEndpointGuardsWithProductionOriginClassifier($file, $admin, $headers, $cli, $allowed)
    {
        $names = ['global', 'lastMatchedAVideoUserAgent', 'AVideoMobileAPP_UA', 'AVideoEncoder_UA',
            'AVideoEncoderNetwork_UA', 'AVideoStreamer_UA', 'AVideoStorage_UA', 'AVideoRestreamer_UA'];
        $previous = [];
        foreach ($names as $name) {
            $previous[$name] = [array_key_exists($name, $GLOBALS), $GLOBALS[$name] ?? null];
        }
        $request = [$_GET, $_POST, $_REQUEST, $_SERVER];
        $reporting = error_reporting();
        $display = [ini_get('display_errors'), ini_get('display_startup_errors')];
        try {
            State::$admin = $admin;
            $_GET = $cli ? [] : ['ignoreCommandLineInterface' => 1];
            $_POST = $_REQUEST = [];
            $_SERVER = array_merge(['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '203.0.113.20',
                'HTTP_USER_AGENT' => 'Mozilla/5.0'], $headers);
            $GLOBALS['global'] = ['systemRootPath' => '/fixture/', 'webSiteRootURL' => 'https://video.example.com/avideo/'];
            foreach (['AVideoMobileAPP', 'AVideoEncoder', 'AVideoEncoderNetwork', 'AVideoStreamer', 'AVideoStorage', 'AVideoRestreamer'] as $agent) {
                $GLOBALS[$agent . '_UA'] = str_replace('APP', 'App', $agent);
            }
            $source = $this->source($file);
            $marker = $file === 'ajax.php' ? '$configuration = new AVideoConf();' : '$wwbnIndex = new WWBNIndex();';
            $end = strpos($source, $marker);
            $this->assertNotFalse($end);
            $source = substr($source, 5, $end - 5);
            // Strip only the known bootstrap includes; fail closed if another include is introduced.
            $includes = $file === 'ajax.php' ? [
                'require_once $configFile;',
                'require_once $global[\'systemRootPath\']. "plugin/WWBNIndex/WWBNIndex.php";',
                'require_once $global[\'systemRootPath\']. "objects/Channel.php";',
                'require_once $global[\'systemRootPath\']. "plugin/LoginControl/pgp/functions.php";',
            ] : ['require_once ($systemRootPath . "plugin/WWBNIndex/WWBNIndex.php");'];
            foreach ($includes as $include) {
                $source = str_replace($include, '', $source, $count);
                $this->assertSame(1, $count, $include);
            }
            foreach (token_get_all('<?php ' . $source) as $token) {
                if (is_array($token)) {
                    $this->assertNotContains($token[0], [T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE]);
                }
            }
            $denied = false;
            try {
                eval('namespace Tests\\Security\\WWBNIndexAuthorizationFixtures; ' . $source);
            } catch (RequestDenied $exception) {
                $denied = true;
                $this->assertSame(403, $exception->getCode());
            }
            $this->assertSame(!$allowed, $denied);
        } finally {
            [$_GET, $_POST, $_REQUEST, $_SERVER] = $request;
            foreach ($previous as $name => [$exists, $value]) {
                if ($exists) {
                    $GLOBALS[$name] = $value;
                } else {
                    unset($GLOBALS[$name]);
                }
            }
            error_reporting($reporting);
            ini_set('display_errors', $display[0]);
            ini_set('display_startup_errors', $display[1]);
        }
    }

    public static function requestCases(): iterable
    {
        $cases = [
            'admin plugin manager' => [true, ['HTTP_REFERER' => 'https://video.example.com/avideo/view/managerPlugins.php'], false, true],
            'admin unified panel' => [true, ['HTTP_REFERER' => 'https://video.example.com/avideo/admin/index.php?page=plugins'], false, true],
            'admin origin only' => [true, ['HTTP_ORIGIN' => 'https://video.example.com'], false, true],
            'admin configured CDN' => [true, ['HTTP_ORIGIN' => 'https://media.example.net'], false, true],
            'admin AVideo CDN' => [true, ['HTTP_ORIGIN' => 'https://customer.cdn.ypt.me'], false, true],
            'admin cross-origin' => [true, ['HTTP_ORIGIN' => 'https://attacker.invalid'], false, false],
            'admin sibling subdomain' => [true, ['HTTP_REFERER' => 'https://evil.example.com/form'], false, false],
            'admin deceptive CDN' => [true, ['HTTP_ORIGIN' => 'https://cdn.ypt.me.attacker.invalid'], false, false],
            'admin local reverse proxy cross-origin' => [true, ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_ORIGIN' => 'https://attacker.invalid'], false, false],
            'admin no origin headers remotely' => [true, [], false, false],
            'admin local headerless' => [true, ['REMOTE_ADDR' => '127.0.0.1'], false, true],
            'admin trusted AVideo agent' => [true, ['HTTP_ORIGIN' => 'https://attacker.invalid', 'HTTP_USER_AGENT' => 'AVideoEncoder'], false, true],
            'admin CLI' => [true, ['HTTP_ORIGIN' => 'https://attacker.invalid'], true, true],
            'non-admin same origin' => [false, ['HTTP_ORIGIN' => 'https://video.example.com'], false, false],
            'non-admin forged AVideo agent' => [false, ['HTTP_USER_AGENT' => 'AVideoEncoder'], false, false],
            'non-admin CLI' => [false, [], true, false],
        ];
        foreach (['ajax.php', 'submitIndex.php'] as $file) {
            foreach ($cases as $name => $case) {
                yield $file . ': ' . $name => array_merge([$file], $case);
            }
        }
    }
}
