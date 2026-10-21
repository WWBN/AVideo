<?php
namespace Tests\Unit\PayPalConfiguration;

use PHPUnit\Framework\TestCase;

class PayPalYPT
{
    const DEMO_CLIENT_ID = 'DEMO-shared-app';
    const WEBHOOK_EVENTS = ['PAYMENT.SALE.COMPLETED', 'PAYMENT.SALE.DENIED', 'PAYMENT.SALE.REFUNDED', 'PAYMENT.SALE.REVERSED'];
    public static function getIPNVerificationURL($settings) {
        return empty($settings->disableSandbox) ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';
    }
}
class PayPalYPT_checkout
{
    public static function isValidProviderId($id) { return is_string($id) && preg_match('/^[A-Za-z0-9_-]{6,64}$/D', $id) === 1; }
}
$source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PayPalYPT/Objects/PayPalConfiguration.php');
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use Throwable;' . preg_replace('/^<\?php/', '', $source));

/** Fake PayPal API: records each request and answers from a script. */
class FakePayPal
{
    public $calls = [];
    public $oauth = ['status' => 200, 'body' => '{"access_token":"A21AAsecret-token","app_id":"APP-123","expires_in":32400,"scope":"https://api.paypal.com/v1/payments/.* https://uri.paypal.com/services/applications/webhooks https://uri.paypal.com/services/billing-agreements"}'];
    public $webhooks = [];
    public $createStatus = 201;
    public $createThrows = false;
    public $patchStatus = 200;
    public $probeStatus = 200;
    public function __invoke($method, $url, array $headers, $body) {
        $path = parse_url($url, PHP_URL_PATH);
        $this->calls[] = [$method, $path, $body, $headers];
        if ($path === '/v1/oauth2/token') {
            return $this->oauth;
        }
        if ($method === 'GET' && $path === '/v1/notifications/webhooks') {
            return ['status' => 200, 'body' => json_encode(['webhooks' => $this->webhooks])];
        }
        if ($method === 'POST' && $path === '/v1/notifications/webhooks') {
            $data = json_decode($body, true);
            if ($this->createThrows) {
                // The response is lost after PayPal created it.
                $this->webhooks[] = ['id' => 'WH-LOSTRESPONSE1', 'url' => $data['url'], 'event_types' => $data['event_types']];
                throw new \RuntimeException('network');
            }
            if ($this->createStatus !== 201) {
                return ['status' => $this->createStatus, 'body' => '{"name":"WEBHOOK_NUMBER_LIMIT_EXCEEDED"}'];
            }
            $webhook = ['id' => 'WH-NEW1234567', 'url' => $data['url'], 'event_types' => $data['event_types']];
            $this->webhooks[] = $webhook;
            return ['status' => 201, 'body' => json_encode($webhook)];
        }
        if ($method === 'PATCH') {
            return ['status' => $this->patchStatus, 'body' => '{}'];
        }
        return ['status' => $this->probeStatus, 'body' => $this->probeStatus === 204 ? '' : '{}'];
    }
    public function methods() { return array_map(function ($call) { return $call[0] . ' ' . $call[1]; }, $this->calls); }
}

class PayPalConfigurationTest extends TestCase
{
    const URL = 'https://site.example/sub/plugin/PayPalYPT/webhook.php';

    private function settings($overrides = []) {
        return (object) array_merge(['ClientID' => 'OWN-app-client', 'ClientSecret' => 'own-secret-value', 'disableSandbox' => false, 'webhookId' => ''], $overrides);
    }
    private function webhook($id, $url, $events) {
        return ['id' => $id, 'url' => $url, 'event_types' => array_map(function ($name) { return ['name' => $name]; }, $events)];
    }
    private function status($report, $id) {
        foreach ($report['checks'] as $check) { if ($check['id'] === $id) { return $check['status']; } }
        return null;
    }

    public function testSharedDemoAppIsFlaggedAndNeverConfigured(): void {
        $api = new FakePayPal();
        $checker = new PayPalConfiguration($this->settings(['ClientID' => 'DEMO-shared-app']), self::URL, $api);
        $report = $checker->check([]);
        $this->assertSame('fail', $this->status($report, 'credentials'));
        $this->assertNotContains('GET /v1/notifications/webhooks', $api->methods(), 'Other sites\' webhooks are not read');
        $this->assertFalse($report['can_configure']);
        $api->calls = [];
        $result = $checker->configure(function () { return true; });
        $this->assertSame('fail', $result['status']);
        $this->assertSame([], $api->calls, 'No PayPal request at all for the shared app');
    }
    public function testSecretsAndTokenNeverReachTheReport(): void {
        $api = new FakePayPal();
        $api->webhooks = [$this->webhook('WH-OTHER00001', 'https://other.example/hook', ['*'])];
        $report = (new PayPalConfiguration($this->settings(), self::URL, $api))->check(['subscription' => true]);
        $json = json_encode($report);
        foreach (['A21AAsecret-token', 'own-secret-value', 'Authorization', 'other.example'] as $secret) {
            $this->assertStringNotContainsString($secret, $json);
        }
        $this->assertSame('APP-123', $report['app_id']);
        $this->assertSame(1, $report['other_webhooks'], 'Only a count of other sites\' webhooks is shown');
        $this->assertSame('Basic ' . base64_encode('OWN-app-client:own-secret-value'), substr($api->calls[0][3][2], strlen('Authorization: ')));
    }
    public function testEnvironmentComesFromTheSettingNotTheClientId(): void {
        $api = new FakePayPal();
        $report = (new PayPalConfiguration($this->settings(['disableSandbox' => true]), self::URL, $api))->check([]);
        $this->assertSame('live', $report['environment']);
        $this->assertSame('https://api-m.paypal.com/v1/oauth2/token', 'https://api-m.paypal.com' . $api->calls[0][1]);
        $this->assertSame('https://ipnpb.paypal.com/cgi-bin/webscr', $report['ipn_verification_url']);
    }
    public function testInvalidCredentialsAreAnActionableFailureWithoutFurtherCalls(): void {
        $api = new FakePayPal();
        $api->oauth = ['status' => 401, 'body' => '{"error":"invalid_client","error_description":"Client Authentication failed"}'];
        $report = (new PayPalConfiguration($this->settings(), self::URL, $api))->check([]);
        $this->assertSame('fail', $this->status($report, 'oauth'));
        $this->assertCount(1, $api->calls);
        $this->assertStringNotContainsString('Client Authentication failed', json_encode($report), 'PayPal error text is not echoed');
    }
    public function testMissingRequiredScopeFailsButOptionalScopeOnlyWarns(): void {
        $api = new FakePayPal();
        $api->oauth = ['status' => 200, 'body' => '{"access_token":"t","app_id":"APP-1","expires_in":100,"scope":"https://api.paypal.com/v1/payments/.*"}'];
        $report = (new PayPalConfiguration($this->settings(), self::URL, $api))->check(['subscription' => true, 'payouts' => false]);
        $byFeature = array_column($report['permissions'], 'status', 'resource');
        $this->assertSame('pass', $byFeature['One-time payments (wallet, PPV, PPV Live)']);
        $this->assertSame('fail', $byFeature['Recurring subscriptions']);
        $this->assertSame('warning', $byFeature['Webhook inspection and setup']);
        $this->assertSame('unknown', $byFeature['Payouts (wallet withdrawals)']);
    }
    public function testProbe404IsNotAnnouncedAsAccess(): void {
        $api = new FakePayPal();
        $api->probeStatus = 404;
        $report = (new PayPalConfiguration($this->settings(), self::URL, $api))->check([]);
        $this->assertSame('warning', $report['probes'][0]['status']);
        $api->probeStatus = 403;
        $report = (new PayPalConfiguration($this->settings(), self::URL, $api))->check([]);
        $this->assertSame('fail', $report['probes'][0]['status']);
    }
    public function testEmptySuccessfulReadDoesNotRequireConfigurationChanges(): void {
        $api = new FakePayPal();
        $api->probeStatus = 204;
        $api->webhooks = [$this->webhook('WH-EXISTING01', self::URL, PayPalYPT::WEBHOOK_EVENTS)];
        $report = (new PayPalConfiguration($this->settings(['webhookId' => 'WH-EXISTING01']), self::URL, $api))->check(['subscription' => true]);
        $this->assertSame(['pass', 'pass'], array_column($report['probes'], 'status'));
        $this->assertFalse($report['has_failures']);
        foreach ($report['probes'] as $probe) {
            $this->assertStringContainsString('Write access is not proven', $probe['message']);
        }
        $this->assertSame('unknown', $this->status($report, 'checkout'));
    }
    public function testMissingWebhookIsCreatedWithHandledEventsAndIdSaved(): void {
        $api = new FakePayPal();
        $saved = [];
        $result = (new PayPalConfiguration($this->settings(), self::URL, $api))->configure(function ($id) use (&$saved) { $saved[] = $id; return true; });
        $this->assertSame('pass', $result['status']);
        $this->assertSame(['WH-NEW1234567'], $saved);
        $create = array_values(array_filter($api->calls, function ($call) { return $call[0] === 'POST' && $call[1] === '/v1/notifications/webhooks'; }));
        $this->assertCount(1, $create);
        $payload = json_decode($create[0][2], true);
        $this->assertSame(self::URL, $payload['url']);
        $this->assertSame(PayPalYPT::WEBHOOK_EVENTS, array_column($payload['event_types'], 'name'));
    }
    public function testExistingWebhookKeepsItsOtherEventsWhenMissingOnesAreAdded(): void {
        $api = new FakePayPal();
        $api->webhooks = [$this->webhook('WH-EXISTING01', self::URL, ['PAYMENT.SALE.COMPLETED', 'CHECKOUT.ORDER.APPROVED'])];
        $result = (new PayPalConfiguration($this->settings(), self::URL, $api))->configure(function () { return true; });
        $this->assertSame('pass', $result['status']);
        $patch = array_values(array_filter($api->calls, function ($call) { return $call[0] === 'PATCH'; }));
        $this->assertCount(1, $patch);
        $this->assertSame('/v1/notifications/webhooks/WH-EXISTING01', $patch[0][1]);
        $names = array_column(json_decode($patch[0][2], true)[0]['value'], 'name');
        $this->assertContains('CHECKOUT.ORDER.APPROVED', $names, 'An event added by someone else is preserved');
        foreach (PayPalYPT::WEBHOOK_EVENTS as $event) { $this->assertContains($event, $names); }
        $this->assertNotContains('POST /v1/notifications/webhooks', $api->methods(), 'No second webhook');
    }
    public function testCorrectWebhookOnlySavesTheId(): void {
        $api = new FakePayPal();
        $api->webhooks = [$this->webhook('WH-EXISTING01', self::URL, PayPalYPT::WEBHOOK_EVENTS)];
        $saved = [];
        (new PayPalConfiguration($this->settings(), self::URL, $api))->configure(function ($id) use (&$saved) { $saved[] = $id; return true; });
        $this->assertSame(['WH-EXISTING01'], $saved);
        $this->assertSame(['POST /v1/oauth2/token', 'GET /v1/notifications/webhooks'], $api->methods());
    }
    public function testDuplicatesAreRefusedWithoutChanges(): void {
        $api = new FakePayPal();
        $api->webhooks = [$this->webhook('WH-DUP0000001', self::URL, ['*']), $this->webhook('WH-DUP0000002', self::URL, ['*'])];
        $report = (new PayPalConfiguration($this->settings(), self::URL, $api))->check([]);
        $this->assertSame('fail', $this->status($report, 'webhook'));
        $this->assertFalse($report['can_configure']);
        $result = (new PayPalConfiguration($this->settings(), self::URL, $api))->configure(function () { return true; });
        $this->assertSame('fail', $result['status']);
        $this->assertSame([], array_filter($api->methods(), function ($method) { return strpos($method, 'GET') !== 0 && strpos($method, 'POST /v1/oauth2') !== 0; }));
    }
    public function testWebhookQuotaIsReportedAndNothingIsEverDeleted(): void {
        $api = new FakePayPal();
        for ($i = 0; $i < 10; $i++) { $api->webhooks[] = $this->webhook('WH-OTHER0000' . $i, "https://site{$i}.example/hook", ['*']); }
        $report = (new PayPalConfiguration($this->settings(), self::URL, $api))->check([]);
        $this->assertFalse($report['can_configure']);
        $result = (new PayPalConfiguration($this->settings(), self::URL, $api))->configure(function () { return true; });
        $this->assertSame('fail', $result['status']);
        $api->webhooks = [];
        $api->createStatus = 400;
        $result = (new PayPalConfiguration($this->settings(), self::URL, $api))->configure(function () { return true; });
        $this->assertStringContainsString('never deletes', $result['message']);
        foreach ($api->methods() as $method) { $this->assertStringStartsNotWith('DELETE', $method); }
    }
    public function testLostCreateResponseIsReconciledInsteadOfCreatingAgain(): void {
        $api = new FakePayPal();
        $api->createThrows = true;
        $saved = [];
        $result = (new PayPalConfiguration($this->settings(), self::URL, $api))->configure(function ($id) use (&$saved) { $saved[] = $id; return true; });
        $this->assertSame('pass', $result['status']);
        $this->assertSame(['WH-LOSTRESPONSE1'], $saved);
        $this->assertCount(1, $api->webhooks);
    }
    public function testFailedSaveIsNeverReportedAsSuccess(): void {
        $api = new FakePayPal();
        $result = (new PayPalConfiguration($this->settings(), self::URL, $api))->configure(function () { return false; });
        $this->assertSame('fail', $result['status']);
        $this->assertStringContainsString('WH-NEW1234567', $result['message'], 'The admin can save the created ID manually');
    }
    public function testPrivateOrPlainHttpUrlsCannotBeConfigured(): void {
        foreach (['http://site.example/plugin/PayPalYPT/webhook.php', 'https://localhost/plugin/PayPalYPT/webhook.php', 'https://192.168.0.2/plugin/PayPalYPT/webhook.php'] as $url) {
            $api = new FakePayPal();
            $result = (new PayPalConfiguration($this->settings(), $url, $api))->configure(function () { return true; });
            $this->assertSame('fail', $result['status'], $url);
            $this->assertSame([], $api->calls, $url);
        }
    }
    public function testDeliveryEvidenceIsHistoricalAndNeverClaimsAPurchase(): void {
        $api = new FakePayPal();
        $report = (new PayPalConfiguration($this->settings(), self::URL, $api))->check(['webhook_delivery' => ['received_at' => 1760000000, 'type' => 'PAYMENT.SALE.COMPLETED']]);
        $this->assertSame('pass', $this->status($report, 'webhook_delivery'));
        $this->assertSame('unknown', $this->status($report, 'checkout'), 'Never a single green light for the whole purchase');
        $report = (new PayPalConfiguration($this->settings(), self::URL, $api))->check([]);
        $this->assertSame('unknown', $this->status($report, 'webhook_delivery'));
    }
    public function testWalletCurrencySupport(): void {
        $api = new FakePayPal();
        $this->assertSame('fail', $this->status((new PayPalConfiguration($this->settings(), self::URL, $api))->check(['currency' => 'ZAR']), 'currency'));
        $this->assertSame('warning', $this->status((new PayPalConfiguration($this->settings(), self::URL, $api))->check(['currency' => 'JPY']), 'currency'));
        $this->assertSame('pass', $this->status((new PayPalConfiguration($this->settings(), self::URL, $api))->check(['currency' => 'BRL']), 'currency'));
    }
}
