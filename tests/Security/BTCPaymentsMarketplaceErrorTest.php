<?php

namespace Tests\Security\BTCPaymentsMarketplaceError;

use PHPUnit\Framework\TestCase;

const BTC_MARKETPLACE_URL = 'https://marketplace.example/BTC/';
const MARKETPLACE_KEY = 'marketplace-key-secret';

class ForbiddenPage extends \RuntimeException
{
}

class Fixture
{
    public static $response = '';
    public static $requestedUrl = '';
    public static $logs = [];
}

class AVideoLog
{
    public static $DEBUG = 'DEBUG';
    public static $ERROR = 'ERROR';
}

class AVideoPlugin
{
    public static function getObjectData($name)
    {
        return (object) ['currency' => 'USD'];
    }

    public static function getDataObject($name)
    {
        return (object) ['useTestNet' => true, 'siteWallet' => '', 'siteWalletTest' => 'tb1qfixturewallet', 'BTCMarketPlaceKey' => MARKETPLACE_KEY];
    }
}

function addQueryStringParameter($url, $name, $value)
{
    return $url . (strpos($url, '?') === false ? '?' : '&') . $name . '=' . rawurlencode($value);
}

function _uniqid()
{
    return 'fixture';
}

function getVideosDir()
{
    return '/nonexistent-btc-fixture/';
}

function make_path($path)
{
    return true;
}

// The invoice request file is not needed here; nothing is written to disk.
function file_put_contents($file, $data)
{
    return strlen($data);
}

function shouldVerifySSLCertificates($fixedPublicHost = false)
{
    return true;
}

function url_get_contents($url)
{
    Fixture::$requestedUrl = $url;
    return Fixture::$response;
}

function _error_log($message, $level = '')
{
    Fixture::$logs[] = $message;
}

function forbiddenPage($message)
{
    throw new ForbiddenPage($message);
}

$btcSource = \file_get_contents(dirname(__DIR__, 2) . '/plugin/BTCPayments/BTCPayments.php');
$btcStart = strpos($btcSource, '    static function getMarketplaceURL(');
$btcEnd = strpos($btcSource, '    static public function setUpPayment(', $btcStart);
eval('namespace ' . __NAMESPACE__ . '; class BTCPayments {' . substr($btcSource, $btcStart, $btcEnd - $btcStart) . '}');
unset($btcSource, $btcStart, $btcEnd);

class BTCPaymentsMarketplaceErrorTest extends TestCase
{
    private $previousGlobal;

    protected function setUp(): void
    {
        $this->previousGlobal = $GLOBALS['global'] ?? null;
        $GLOBALS['global'] = ['webSiteRootURL' => 'https://site.example/'];
        Fixture::$response = '';
        Fixture::$requestedUrl = '';
        Fixture::$logs = [];
    }

    protected function tearDown(): void
    {
        $GLOBALS['global'] = $this->previousGlobal;
    }

    public function testUnreadableMarketplaceResponseDoesNotShowTheKeyToTheBuyer()
    {
        // Issue #10655: PHP notices printed before the JSON made the marketplace response unreadable.
        Fixture::$response = "<br />\nDeprecated: Function curl_close() is deprecated since 8.5 in BTCPay.php on line 56<br />\n"
            . '{"error":false,"invoice":{"id":"inv_1","checkoutLink":"https://btcpay.example/i/inv_1"}}';
        $message = $this->forbiddenMessage();

        $this->assertStringContainsString(MARKETPLACE_KEY, Fixture::$requestedUrl);
        $this->assertStringNotContainsString(MARKETPLACE_KEY, $message);
        $this->assertStringNotContainsString('marketplace.example', $message);
        $this->assertStringNotContainsString('Deprecated', $message);
        $this->assertStringNotContainsString('inv_1', $message);
        $this->assertLogsDoNotContainTheKey();
        // The administrator still finds the raw response in the log.
        $this->assertNotEmpty(array_filter(Fixture::$logs, function ($log) {
            return strpos($log, 'Deprecated: Function curl_close()') !== false;
        }));
    }

    public function testEmptyMarketplaceResponseDoesNotShowTheKeyToTheBuyer()
    {
        $message = $this->forbiddenMessage();

        $this->assertStringNotContainsString(MARKETPLACE_KEY, $message);
        $this->assertStringNotContainsString('marketplace.example', $message);
        $this->assertLogsDoNotContainTheKey();
    }

    public function testValidMarketplaceResponseIsReturnedUnchanged()
    {
        Fixture::$response = '{"error":false,"invoice":{"id":"inv_2","checkoutLink":"https://btcpay.example/i/inv_2"}}';

        $this->assertSame(json_decode(Fixture::$response, true), BTCPayments::createBTCPayInvoice(5, 7));
    }

    private function forbiddenMessage()
    {
        try {
            BTCPayments::createBTCPayInvoice(5, 7);
        } catch (ForbiddenPage $e) {
            return $e->getMessage();
        }
        $this->fail('The marketplace error did not stop the payment');
    }

    private function assertLogsDoNotContainTheKey()
    {
        $this->assertNotEmpty(Fixture::$logs);
        foreach (Fixture::$logs as $log) {
            $this->assertStringNotContainsString(MARKETPLACE_KEY, $log);
        }
    }
}
