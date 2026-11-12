<?php

namespace Tests\Security\WalletConfigurationCsrf;

use PHPUnit\Framework\TestCase;

class State
{
    public static $writes = [], $loads = 0, $cdn = 'https://media.example.net/';
}
class User
{
    public static function isLogged() { return true; }
    public static function getId() { return 7; }
}
class AVideoPlugin
{
    public static function loadPluginIfEnabled($name) { ++State::$loads; return new self(); }
    public function getBalanceFormated($id) { return '$0.00'; }
}
class Wallet
{
    public function __construct($id) {}
    public function setUsers_id($id) {}
    public function setCrypto_wallet_address($address) { State::$writes[] = ['address', $address]; }
    public function save() { State::$writes[] = ['save']; return true; }
}
class YPTWallet
{
    public static function setDonationNotificationURL($id, $url) { State::$writes[] = ['url', $url]; return true; }
    public static function regenerateDonationNotificationSecret($id) { State::$writes[] = ['regenerate']; return true; }
    public static function getDonationNotificationSecret($id) { return 'fixture-secret'; }
}
class AVideoLog { public static $SECURITY = 'security'; }
class RequestDenied extends \RuntimeException {}
function _error_log($message, $level = null) {}
function header($value) {}
function getCDN() { return State::$cdn; }
// Replace terminating responses only; execute the real request classification below.
function forbiddenPage($message, $log = false) { throw new RequestDenied($message, 403); }
function forbidIfNotPost()
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        throw new RequestDenied('Method not allowed', 405);
    }
}

$root = dirname(__DIR__, 2);
foreach ([
    'objects/functions.php' => ['getRemoteAddrFromServerArray', 'isLoopbackIP', 'getForwardedClientIpFromServerArray',
        'isRequestFromTrustedProxy', 'getRealIpAddr', 'isCommandLineInterface', 'getRefferOrOrigin', 'isSameDomain', 'get_domain'],
    'objects/functionsSecurity.php' => ['forbidIfIsUntrustedRequest', 'isUntrustedRequest'],
    'objects/functionsAVideo.php' => ['isCDN', 'isFromCDN', 'isAVideoUserAgent', 'requestComesFromSameDomainAsMyAVideo'],
] as $file => $functions) {
    $source = file_get_contents($root . '/' . $file);
    foreach ($functions as $function) {
        if (!preg_match('/^function ' . $function . '\([^\n]*\)\s*\{.*?^\}/ms', $source, $match)) {
            throw new \RuntimeException('Missing production function: ' . $function);
        }
        eval('namespace ' . __NAMESPACE__ . ';' . $match[0]);
    }
}
unset($root, $file, $functions, $source, $function, $match);

class WalletConfigurationCsrfTest extends TestCase
{
    private $previous;
    private $previousAgents;
    private $previousMatchedAgent;

    protected function setUp(): void
    {
        $this->previous = [$_GET, $_POST, $_REQUEST, $_SERVER, $GLOBALS['global'] ?? null];
        $this->previousMatchedAgent = $GLOBALS['lastMatchedAVideoUserAgent'] ?? null;
        State::$writes = [];
        State::$loads = 0;
        $_GET = ['ignoreCommandLineInterface' => 1];
        $_POST = ['CryptoWallet' => 'fixture-address', 'donation_notification_url' => 'https://hooks.example.net/donation',
            'regenerate_webhook_secret' => 1];
        $_REQUEST = $_POST;
        $_SERVER = ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '203.0.113.20', 'HTTP_USER_AGENT' => 'Mozilla/5.0'];
        $GLOBALS['global'] = ['webSiteRootURL' => 'https://wallet.example.com/avideo/'];
        foreach (['AVideoMobileAPP', 'AVideoEncoder', 'AVideoEncoderNetwork', 'AVideoStreamer', 'AVideoStorage', 'AVideoRestreamer'] as $agent) {
            $this->previousAgents[$agent . '_UA'] = $GLOBALS[$agent . '_UA'] ?? null;
            $GLOBALS[$agent . '_UA'] = str_replace('APP', 'App', $agent);
        }
    }

    protected function tearDown(): void
    {
        [$_GET, $_POST, $_REQUEST, $_SERVER, $GLOBALS['global']] = $this->previous;
        if ($this->previousMatchedAgent === null) {
            unset($GLOBALS['lastMatchedAVideoUserAgent']);
        } else {
            $GLOBALS['lastMatchedAVideoUserAgent'] = $this->previousMatchedAgent;
        }
        foreach ($this->previousAgents as $agent => $value) {
            if ($value === null) {
                unset($GLOBALS[$agent]);
            } else {
                $GLOBALS[$agent] = $value;
            }
        }
    }

    private function request()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/YPTWallet/view/saveConfiguration.php');
        // No site configuration, database or real wallet is loaded by this fixture.
        $source = preg_replace('/require_once\s+[^;]+;/', '', $source);
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; use stdClass; ?>' . $source);
            return json_decode(ob_get_contents(), true);
        } finally {
            ob_end_clean();
        }
    }

    /** @dataProvider untrustedOrigins */
    public function testWalletConfigurationRejectsUntrustedOriginsBeforeAnyWalletAccess($url)
    {
        $_SERVER['HTTP_ORIGIN'] = $url;
        $_SERVER['HTTP_REFERER'] = $url . '/form';
        try {
            $this->request();
            $this->fail('Untrusted request reached the wallet');
        } catch (RequestDenied $exception) {
            $this->assertSame(403, $exception->getCode());
        }
        $this->assertSame([], State::$writes);
        $this->assertSame(0, State::$loads);
    }

    public static function untrustedOrigins(): array
    {
        return [
            ['https://attacker.invalid'],
            ['https://cdn.ypt.me.attacker.invalid'],
            ['https://cdnayptbme.attacker.invalid'],
            ['https://attacker.invalid/cdn.ypt.me'],
            ['https://attacker.invalid/?cdn.ypt.me'],
            ['https://cdn.ypt.me@attacker.invalid'],
            ['null'],
        ];
    }

    public function testWalletConfigurationRejectsHeaderlessGetBehindLocalProxy()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'POST';
        // A no-referrer cross-site link/image sends no Origin on GET.
        $_POST = [];
        $_GET += ['donation_notification_url' => 'https://attacker.invalid/hook', 'regenerate_webhook_secret' => 1];
        $_REQUEST = $_GET;
        try {
            $this->request();
            $this->fail('GET must not reach wallet configuration writes');
        } catch (RequestDenied $exception) {
            $this->assertSame(405, $exception->getCode());
        }
        $this->assertSame([], State::$writes);
        $this->assertSame(0, State::$loads);
    }

    /** @dataProvider trustedOrigins */
    public function testWalletConfigurationAcceptsTrustedAjaxOrigins($headers)
    {
        $_SERVER = array_merge($_SERVER, $headers);
        $response = $this->request();
        $this->assertFalse($response['error']);
        $this->assertSame([['address', 'fixture-address'], ['save'],
            ['url', 'https://hooks.example.net/donation'], ['regenerate']], State::$writes);
    }

    public static function trustedOrigins(): array
    {
        return [
            [['HTTP_REFERER' => 'https://wallet.example.com/avideo/plugin/YPTWallet/']],
            [['HTTP_ORIGIN' => 'https://wallet.example.com']],
            [['HTTP_ORIGIN' => 'https://wallet.example.com:8443']],
            [['HTTP_ORIGIN' => 'http://wallet.example.com']],
            [['HTTP_REFERER' => 'https://media.example.net/avideo/']],
            [['HTTP_ORIGIN' => 'https://customer.cdn.ypt.me']],
            [['HTTP_ORIGIN' => 'https://cdn.ypt.me']],
            [['HTTP_ORIGIN' => 'https://attacker.invalid', 'HTTP_USER_AGENT' => 'AVideoMobileApp']],
            [['REMOTE_ADDR' => '127.0.0.1']],
            [['REMOTE_ADDR' => '127.0.0.1', 'HTTP_ORIGIN' => 'https://wallet.example.com']],
        ];
    }

    public function testWalletConfigurationLocalProxyDoesNotTrustCrossOriginPost()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $this->testWalletConfigurationRejectsUntrustedOriginsBeforeAnyWalletAccess('https://attacker.invalid');
    }

    public function testWalletConfigurationWithoutOriginOrRefererFailsClosedForRemoteBrowsers()
    {
        try {
            $this->request();
            $this->fail('Remote browser without origin information must be refused');
        } catch (RequestDenied $exception) {
            $this->assertSame(403, $exception->getCode());
        }
        $this->assertSame([], State::$writes);
    }

    public function testWalletConfigurationPreservesCliException()
    {
        $_GET = [];
        $_SERVER['HTTP_ORIGIN'] = 'https://attacker.invalid';
        $this->assertFalse($this->request()['error']);
    }

    public function testWalletConfigurationPreservesBareCdnHostHeader()
    {
        foreach (['cdn.ypt.me', 'Customer.CDN.YPT.ME', 'https://customer.cdn.ypt.me/'] as $host) {
            $_SERVER['HTTP_CDN_HOST'] = $host;
            $this->assertTrue(isCDN(), $host);
        }
        foreach (['cdn.ypt.me.attacker.invalid', 'attacker.invalid/cdn.ypt.me', 'cdnayptbme.attacker.invalid'] as $host) {
            $_SERVER['HTTP_CDN_HOST'] = $host;
            $this->assertFalse(isCDN(), $host);
        }
    }
}
