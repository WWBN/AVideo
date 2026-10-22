<?php

namespace Tests\Unit\BTCPaymentsCheckout;

use PHPUnit\Framework\TestCase;

class BTCPaymentsCheckoutRefused extends \RuntimeException
{
}

class Fixture
{
    public static $rows = [];
    public static $payments = [];
    public static $created = [];
    public static $processed = [];
    public static $verify = [];
    public static $requests = [];
    public static $balance = 0.0;
    public static $canWatch = false;
    public static $returnURL = '';
    public static $marketplaceError = false;
    public static $ppvEnabled = true;

    public static function reset()
    {
        self::$rows = self::$payments = self::$created = self::$processed = self::$verify = self::$requests = [];
        self::$balance = 0.0;
        self::$canWatch = self::$marketplaceError = false;
        self::$returnURL = '';
        self::$ppvEnabled = true;
    }

    public static function invoice($id, $status, $metadata, $expiration = null)
    {
        return ['id' => $id, 'status' => $status, 'additionalStatus' => 'None', 'amount' => '5', 'currency' => 'USD',
            'checkoutLink' => "https://btcpay.example/i/{$id}", 'createdTime' => time() - 60,
            'expirationTime' => $expiration ?? time() + 840, 'storeId' => 'store_secret', 'totalBTCPaid' => 0,
            'metadata' => $metadata + ['users_id' => 7, 'BTCPAY_NETWORK' => 'Testnet', 'users_sites_id' => 11, 'tmpFileName' => 'tmp_secret.btc.json'],
            'payments' => [['paymentMethodId' => 'BTC-CHAIN', 'destination' => 'tb1qaddress0000000000000000', 'due' => '0.00006137',
                'amount' => '0.00006137', 'paymentLink' => 'bitcoin:tb1qaddress0000000000000000?amount=0.00006137',
                'additionalData' => ['keyPath' => '0/58']]]];
    }

    public static function addRow(array $invoice, $usersId = 7)
    {
        $id = count(self::$rows) + 1;
        self::$rows[$id] = ['id' => $id, 'invoice_identification' => $invoice['id'], 'users_id' => $usersId,
            'amount_currency' => $invoice['amount'], 'amount_btc' => 0, 'currency' => 'USD', 'status' => 'a',
            'created_php_time' => time() - 60, 'json' => json_encode($invoice)];
        return self::$rows[$id];
    }
}

class AVideoLog
{
    public static $DEBUG = 'debug';
    public static $ERROR = 'error';
    public static $WARNING = 'warning';
}

class WalletPlugin
{
    public function getDataObject()
    {
        return (object) ['currency' => 'USD'];
    }

    public function getBalance($usersId)
    {
        return Fixture::$balance;
    }
}

class PpvPlugin
{
    public function getPaymentDetails($plansId, $videosId)
    {
        return ['value' => 10, 'description' => 'PPV rental: Movie | Plan | 72 hours',
            'metadata' => ['rental_hours' => '72', 'video_title' => 'Movie', 'plan_name' => 'Plan']];
    }

    public function completeWalletCheckout()
    {
        return true;
    }
}

class AVideoPlugin
{
    public static function loadPluginIfEnabled($name)
    {
        if ($name === 'YPTWallet') {
            return new WalletPlugin();
        }
        if ($name === 'PayPerView') {
            return Fixture::$ppvEnabled ? new PpvPlugin() : false;
        }
        return false;
    }

    public static function getDataObject($name)
    {
        return (object) ['useTestNet' => true, 'BTCMarketPlaceKey' => 'marketplace-key'];
    }
}

class YPTWallet
{
    public static function formatCurrency($value)
    {
        return '$' . number_format(floatval($value), 2) . ' USD';
    }

    public static function getUserBalance($usersId)
    {
        return Fixture::$balance;
    }

    public static function getAddFundsSuccessRedirectURL()
    {
        return Fixture::$returnURL;
    }
}

class Btc_invoices
{
    private $data = ['id' => 0];

    public function __construct($id)
    {
    }

    public function __call($method, $arguments)
    {
        $this->data[lcfirst(substr($method, 3))] = $arguments[0];
    }

    public function save()
    {
        $id = count(Fixture::$rows) + 1;
        Fixture::$rows[$id] = $this->data + ['created_php_time' => time()];
        Fixture::$rows[$id]['id'] = $id;
        return $id;
    }

    public static function getRecentFromUser($usersId, $limit = 10)
    {
        $rows = array_values(array_filter(Fixture::$rows, function ($row) use ($usersId) {
            return intval($row['users_id']) === intval($usersId);
        }));
        return array_slice(array_reverse($rows), 0, $limit);
    }

    public static function getFromInvoice_identification($invoiceId)
    {
        foreach (Fixture::$rows as $row) {
            if ($row['invoice_identification'] === $invoiceId) {
                return $row;
            }
        }
        return false;
    }
}

class Btc_payments
{
    public static function getFromInvoice_identification($btcInvoicesId)
    {
        return Fixture::$payments[$btcInvoicesId] ?? false;
    }
}

class PayPerView
{
    public static function getBuyURL($videosId)
    {
        return 'https://site.example/buy/' . $videosId;
    }
}

class PPV_Plans_Users_Videos
{
    public static function userCanWatchVideo($usersId, $videosId)
    {
        return Fixture::$canWatch;
    }

    public static function getActivePlan($usersId, $videosId)
    {
        return ['expiration' => '2030-01-01 00:00:00'];
    }
}

class SiteConfig
{
    public function getWebSiteTitle()
    {
        return 'Site';
    }
}

function __($text)
{
    return $text;
}

function _error_log($message, $level = '')
{
}

function parseToFloat($value)
{
    return is_numeric($value) ? floatval($value) : 0;
}

function isSameDomainAsMyAVideo($url)
{
    return strpos((string) $url, 'https://site.example/') === 0;
}

function _uniqid()
{
    return 'abcdef123456';
}

function addQueryStringParameter($url, $name, $value)
{
    return $url . (strpos($url, '?') === false ? '?' : '&') . $name . '=' . rawurlencode($value);
}

function shouldVerifySSLCertificates($fixedPublicHost = false)
{
    return true;
}

function url_get_contents($url)
{
    Fixture::$requests[] = $url;
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
    $invoice = Fixture::$verify[$query['invoiceId'] ?? ''] ?? null;
    return $invoice === null ? '' : json_encode(['error' => false, 'invoice' => $invoice]);
}

$btcSource = \file_get_contents(dirname(__DIR__, 2) . '/plugin/BTCPayments/BTCPayments.php');
$btcStart = strpos($btcSource, '    public static function getCheckoutPageURL(');
$btcEnd = strpos($btcSource, '    public function getHeadCode(', $btcStart);
// class_exists('X') looks up a global name: point it at the stubs above without defining global classes,
// which would change other tests in the same PHPUnit process.
$btcCode = str_replace(["class_exists('PayPerView')", "class_exists('PPV_Plans_Users_Videos')"],
    ["class_exists(__NAMESPACE__ . '\\\\PayPerView')", "class_exists(__NAMESPACE__ . '\\\\PPV_Plans_Users_Videos')"],
    substr($btcSource, $btcStart, $btcEnd - $btcStart));
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use stdClass; class BTCPayments {' . $btcCode . '
    static function getMarketplaceURL($page) { return "https://marketplace.example/BTC/" . $page; }
    static function createBTCPayInvoice($amount, $users_id, $walletSplits = [], $metadata = [], $redirectUrl = null, $throwOnError = false) {
        Fixture::$created[] = compact("amount", "users_id", "metadata", "redirectUrl", "throwOnError");
        if (Fixture::$marketplaceError) { return ["error" => true, "msg" => "BTCPay API Error"]; }
        $id = "inv_" . count(Fixture::$created);
        return ["error" => false, "invoice" => Fixture::invoice($id, "New", $metadata + ["users_id" => $users_id]) + []];
    }
    public static function processVerifiedInvoice($invoice, $notifyUser = true) {
        Fixture::$processed[] = [$invoice->id, $notifyUser];
        foreach (Fixture::$rows as $key => $row) {
            if ($row["invoice_identification"] === $invoice->id) { Fixture::$rows[$key]["json"] = json_encode($invoice); }
        }
    }
}');
unset($btcSource, $btcStart, $btcEnd, $btcCode);

class BTCPaymentsCheckoutTest extends TestCase
{
    private $previousGlobal;
    private $previousConfig;

    protected function setUp(): void
    {
        $this->previousGlobal = $GLOBALS['global'] ?? null;
        $this->previousConfig = $GLOBALS['config'] ?? null;
        $GLOBALS['global'] = ['webSiteRootURL' => 'https://site.example/'];
        $GLOBALS['config'] = new SiteConfig();
        Fixture::reset();
    }

    protected function tearDown(): void
    {
        $GLOBALS['global'] = $this->previousGlobal;
        $GLOBALS['config'] = $this->previousConfig;
    }

    private function refusal(array $request)
    {
        try {
            BTCPayments::startCheckout(7, $request);
        } catch (BTCPaymentsCheckoutRefused $refused) {
            return $refused->getMessage();
        }
        $this->fail('The checkout was not refused');
    }

    public function testWalletTopUpCreatesOneInvoiceAndReusesItOnADoubleClick()
    {
        $first = BTCPayments::startCheckout(7, ['value' => '5']);
        $second = BTCPayments::startCheckout(7, ['value' => '5']);

        $this->assertSame('inv_1', $first['invoice']);
        $this->assertSame($first, $second);
        $this->assertCount(1, Fixture::$created);
        $created = Fixture::$created[0];
        $this->assertSame(5.0, $created['amount']);
        $this->assertTrue($created['throwOnError']);
        $this->assertSame('wallet', $created['metadata']['purpose']);
        $this->assertSame(40, strlen($created['metadata']['selectionHash']));
        $this->assertStringStartsWith('https://site.example/plugin/BTCPayments/payment-complete.php?order=7-', $created['redirectUrl']);
        $saved = json_decode(Fixture::$rows[1]['json'], true);
        $this->assertSame('New', $saved['status'], 'The invoice itself is saved, not the whole marketplace answer');
        $this->assertSame('https://site.example/plugin/BTCPayments/checkout.php?invoice=inv_1', BTCPayments::getCheckoutPageURL('inv_1'));
    }

    public function invalidAmounts()
    {
        return [['0'], ['-1'], ['abc'], ['100001'], ['']];
    }

    /** @dataProvider invalidAmounts */
    public function testInvalidAmountsAreRefused($value)
    {
        $this->assertSame('Invalid Value', $this->refusal(['value' => $value]));
        $this->assertSame([], Fixture::$created);
    }

    public function testAnExpiredInvoiceIsNotReused()
    {
        BTCPayments::startCheckout(7, ['value' => '5']);
        $invoice = json_decode(Fixture::$rows[1]['json'], true);
        $invoice['expirationTime'] = time() - 10;
        Fixture::$rows[1]['json'] = json_encode($invoice);
        $invoice['status'] = 'Expired';
        Fixture::$verify['inv_1'] = $invoice;

        $this->assertSame('inv_2', BTCPayments::startCheckout(7, ['value' => '5'])['invoice']);
        $this->assertSame([['inv_1', false]], Fixture::$processed, 'BTCPay is asked before giving up on the old invoice');
    }

    public function testAPaymentBeingConfirmedBlocksASecondInvoice()
    {
        BTCPayments::startCheckout(7, ['value' => '5']);
        $invoice = json_decode(Fixture::$rows[1]['json'], true);
        $invoice['expirationTime'] = time() - 10;
        Fixture::$rows[1]['json'] = json_encode($invoice);
        $invoice['status'] = 'Processing';
        Fixture::$verify['inv_1'] = $invoice;

        $result = BTCPayments::startCheckout(7, ['value' => '5']);
        $this->assertSame('inv_1', $result['invoice']);
        $this->assertSame('Your previous Bitcoin payment is still being confirmed. No new payment was created.', $result['message']);
        $this->assertCount(1, Fixture::$created);
    }

    public function testADifferentAmountIsANewPurchase()
    {
        BTCPayments::startCheckout(7, ['value' => '5']);
        $this->assertSame('inv_2', BTCPayments::startCheckout(7, ['value' => '10'])['invoice']);
    }

    public function testPpvRentalIsPricedOnTheServerAndTheBalancePaysFirst()
    {
        Fixture::$balance = 3.0;
        BTCPayments::startCheckout(7, ['plugin' => 'PayPerView', 'value' => '0.01', 'plans_id' => 2, 'videos_id' => 3419]);

        $created = Fixture::$created[0];
        $this->assertSame(7.0, $created['amount']);
        $this->assertSame('ppv', $created['metadata']['purpose']);
        $selection = $created['metadata']['selection'];
        $this->assertSame(['10.00', '3.00', '10.00', 3419, 2, 72], [$selection['price'], $selection['balance_applied'],
            $selection['ppv_value'], $selection['videos_id'], $selection['ppv_plans_id'], $selection['rental_hours']]);
    }

    public function testRefusalsMatchThePayPalCheckout()
    {
        Fixture::$canWatch = true;
        $this->assertSame('You already have access to this video. No payment is needed.',
            $this->refusal(['plugin' => 'PayPerView', 'plans_id' => 2, 'videos_id' => 3419]));
        Fixture::$canWatch = false;
        Fixture::$balance = 10.0;
        $this->assertStringStartsWith('Your wallet balance already covers this purchase.',
            $this->refusal(['plugin' => 'PayPerView', 'plans_id' => 2, 'videos_id' => 3419]));
        Fixture::$balance = 0.0;
        Fixture::$marketplaceError = true;
        $this->assertSame('Bitcoin payments are not available right now. You were not charged. Please try again.',
            $this->refusal(['value' => '5']));
        $this->assertSame([], Fixture::$rows, 'A refused invoice is not saved');
    }

    public function testDisabledPpvFundsTheWalletInstead()
    {
        Fixture::$ppvEnabled = false;
        Fixture::$returnURL = 'https://site.example/buy/3419';
        BTCPayments::startCheckout(7, ['plugin' => 'PayPerView', 'value' => '10', 'plans_id' => 2, 'videos_id' => 3419]);
        $this->assertSame('wallet', Fixture::$created[0]['metadata']['purpose']);
        $this->assertSame('https://site.example/buy/3419', Fixture::$created[0]['metadata']['selection']['return_url']);
    }

    public function testWaitingInvoiceShowsTheBitcoinPaymentWithoutInternals()
    {
        $row = Fixture::addRow(Fixture::invoice('inv_9', 'New', ['purpose' => 'wallet', 'selection' => ['description' => 'Site wallet credit']]));
        $status = BTCPayments::getPublicInvoiceStatus($row);

        $this->assertSame(['new', false, true], [$status['status'], $status['final'], $status['test']]);
        $this->assertSame(['address' => 'tb1qaddress0000000000000000', 'btc_amount' => '0.00006137',
            'uri' => 'bitcoin:tb1qaddress0000000000000000?amount=0.00006137'], $status['payment']);
        $this->assertSame('https://btcpay.example/i/inv_9', $status['checkout_url']);
        $this->assertGreaterThan(800, $status['expires_in']);
        $this->assertSame('Site wallet credit', $status['description']);
        $public = json_encode($status);
        foreach (['tmp_secret', 'store_secret', 'users_sites_id', 'selectionHash', 'keyPath', 'marketplace-key'] as $internal) {
            $this->assertStringNotContainsString($internal, $public);
        }
    }

    public function testOnlySafeLinksReachThePage()
    {
        $invoice = Fixture::invoice('inv_9', 'New', []);
        $invoice['payments'][0]['paymentLink'] = 'javascript:alert(1)';
        $invoice['checkoutLink'] = 'javascript:alert(2)';
        $status = BTCPayments::getPublicInvoiceStatus(Fixture::addRow($invoice));
        $this->assertSame('bitcoin:tb1qaddress0000000000000000?amount=0.00006137', $status['payment']['uri']);
        $this->assertSame('', $status['checkout_url']);
    }

    public function testExpiredAndLegacyInvoices()
    {
        $expired = BTCPayments::getPublicInvoiceStatus(Fixture::addRow(Fixture::invoice('inv_1', 'New', [], time() - 5)));
        $this->assertSame(['expired', true, null], [$expired['status'], $expired['final'], $expired['payment']]);
        $this->assertSame('https://site.example/plugin/YPTWallet/view/addFunds.php', $expired['retry_url']);

        // Rows saved before 2026-10-22 hold the whole marketplace answer.
        $legacy = Fixture::addRow(Fixture::invoice('inv_2', 'Settled', ['description' => 'Wallet add funds']));
        $legacy['json'] = json_encode(['error' => false, 'siteId' => 'site', 'invoice' => json_decode($legacy['json'], true)]);
        $status = BTCPayments::getPublicInvoiceStatus($legacy);
        $this->assertSame(['settled', false, 'Wallet add funds'], [$status['status'], $status['credited'], $status['description']]);
    }

    public function testCreditedRentalLeadsToTheVideo()
    {
        $row = Fixture::addRow(Fixture::invoice('inv_3', 'Settled', ['purpose' => 'ppv',
            'selection' => ['videos_id' => 3419, 'rental_hours' => 72, 'description' => 'PPV rental: Movie']]));
        Fixture::$payments[$row['id']] = ['id' => 1, 'json' => json_encode(['avideo_fulfillment' => 'rented'])];
        $status = BTCPayments::getPublicInvoiceStatus($row);

        $this->assertSame([true, 'rented', true], [$status['credited'], $status['fulfillment'], $status['final']]);
        $this->assertSame('https://site.example/video/3419?autoplay=1', $status['continue_url']);
        $this->assertSame('Watch Now', $status['continue_label']);
        $this->assertSame('2030-01-01 00:00:00', $status['access_until']);
        $this->assertSame('https://btcpay.example/i/inv_3/receipt', $status['receipt_url']);
    }

    public function testWalletCreditReturnsOnlyToThisSite()
    {
        $own = Fixture::addRow(Fixture::invoice('inv_4', 'New', ['selection' => ['return_url' => 'https://site.example/plans']]));
        $this->assertSame('https://site.example/plans', BTCPayments::getPublicInvoiceStatus($own)['continue_url']);
        $foreign = Fixture::addRow(Fixture::invoice('inv_5', 'New', ['selection' => ['return_url' => 'https://evil.example/']]));
        $this->assertSame('https://site.example/plugin/YPTWallet/view/addFunds.php', BTCPayments::getPublicInvoiceStatus($foreign)['continue_url']);
    }

    public function testVerificationRejectsBadIdsAndAnotherInvoice()
    {
        try {
            BTCPayments::fetchVerifiedInvoice('../status.json.php?x=');
            $this->fail('An invalid invoice ID was sent to the marketplace');
        } catch (\RuntimeException $error) {
            $this->assertSame([], Fixture::$requests);
        }
        Fixture::$verify['inv_6'] = Fixture::invoice('inv_7', 'Settled', []);
        $this->expectException(\RuntimeException::class);
        BTCPayments::fetchVerifiedInvoice('inv_6');
    }

    public function testReconcileAppliesOnlyTheOwnersInvoiceWithoutNotifying()
    {
        $row = Fixture::addRow(Fixture::invoice('inv_8', 'New', []));
        Fixture::$verify['inv_8'] = Fixture::invoice('inv_8', 'Processing', []);
        $fresh = BTCPayments::reconcileInvoice($row);
        $this->assertSame([['inv_8', false]], Fixture::$processed);
        $this->assertSame('processing', BTCPayments::getPublicInvoiceStatus($fresh)['status']);

        Fixture::$verify['inv_8']['metadata']['users_id'] = 99;
        $this->expectException(\RuntimeException::class);
        try {
            BTCPayments::reconcileInvoice($row);
        } finally {
            $this->assertCount(1, Fixture::$processed, 'Another user\'s invoice is never applied');
        }
    }
}
