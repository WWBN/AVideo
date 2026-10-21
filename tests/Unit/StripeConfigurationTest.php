<?php
namespace Tests\StripeConfiguration;

use PHPUnit\Framework\TestCase;

$configurationSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/StripeYPT/Objects/StripeConfiguration.php');
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use Throwable; ?>' . $configurationSource);

class Plugin
{
    public static function encryptIfNeed($settings) {
        $settings->automaticWebhookSecret->value = 'encrypted:' . base64_encode($settings->automaticWebhookSecret->value);
        return $settings;
    }
    public static function decryptIfNeed($settings) {
        $settings->automaticWebhookSecret->value = base64_decode(substr($settings->automaticWebhookSecret->value, 10));
        return $settings;
    }
}
class ObjectYPT
{
    public static $cache = [];
    public static function getCacheGlobal($name, ...$args) { return self::$cache[$name] ?? false; }
    public static function setCacheGlobal($name, $value, ...$args) { self::$cache[$name] = $value; }
}
class Resource
{
    public $calls = [], $denied = false;
    public function all($parameters) {
        $this->calls[] = $parameters;
        if ($this->denied) { throw new \Stripe\Exception\PermissionException('NEVER EXPOSE sk_test_private whsec_private'); }
        return (object) ['data' => [], 'has_more' => false];
    }
}
class AccountResource
{
    public $enabled = true, $denied = false;
    public function retrieve() {
        if ($this->denied) { throw new \Stripe\Exception\PermissionException('private'); }
        return (object) ['id' => 'acct_fixture', 'charges_enabled' => $this->enabled, 'email' => 'never-return@example.invalid'];
    }
}
class Endpoints extends Resource
{
    public $objects = [], $creates = [], $updates = [], $pageSize = 100, $denyWrite = false;
    public function all($parameters) {
        parent::all($parameters);
        $start = 0;
        foreach ($this->objects as $index => $endpoint) {
            if ($endpoint->id === ($parameters['starting_after'] ?? '')) { $start = $index + 1; }
        }
        return (object) ['data' => array_slice($this->objects, $start, $this->pageSize), 'has_more' => count($this->objects) > $start + $this->pageSize];
    }
    public function create($parameters, $options) {
        if ($this->denyWrite) { throw new \Stripe\Exception\PermissionException('NEVER EXPOSE sk_test_private'); }
        $this->creates[] = [$parameters, $options];
        $endpoint = (object) array_merge($parameters, ['id' => 'we_new', 'secret' => 'whsec_createdSecret', 'livemode' => false, 'status' => 'enabled']);
        $this->objects[] = $endpoint;
        return $endpoint;
    }
    public function update($id, $parameters) {
        if ($this->denyWrite) { throw new \Stripe\Exception\PermissionException('NEVER EXPOSE sk_test_private'); }
        $this->updates[] = [$id, $parameters];
        foreach ($this->objects as $endpoint) {
            if ($endpoint->id !== $id) { continue; }
            if (isset($parameters['disabled'])) { $endpoint->status = $parameters['disabled'] ? 'disabled' : 'enabled'; }
            if (isset($parameters['enabled_events'])) { $endpoint->enabled_events = $parameters['enabled_events']; }
            return $endpoint;
        }
    }
}

class StripeConfigurationTest extends TestCase
{
    private $settings, $client, $checker;
    const URL = 'https://site.example/plugin/StripeYPT/ipn.php';
    protected function setUp(): void {
        ObjectYPT::$cache = [];
        $this->settings = (object) ['Publishablekey' => 'pk_test_public', 'Restrictedkey' => 'rk_test_private', 'SigningSecret' => 'whsec_manual'];
        $this->client = new \stdClass();
        foreach (['paymentIntents', 'charges', 'customers', 'paymentMethods', 'plans', 'products', 'subscriptions', 'invoices'] as $resource) {
            $this->client->$resource = new Resource();
        }
        $this->client->webhookEndpoints = new Endpoints();
        $this->client->accounts = new AccountResource();
        $this->checker = new StripeConfiguration($this->settings, self::URL, $this->client);
    }
    private function endpoint($id = 'we_existing', $changes = []) {
        return (object) array_merge(['id' => $id, 'url' => self::URL, 'status' => 'enabled', 'livemode' => false,
            'api_version' => '2022-11-15', 'enabled_events' => StripeConfiguration::EVENTS], $changes);
    }
    private function check($id, $report) {
        foreach ($report['checks'] as $check) { if ($check['id'] === $id) { return $check; } }
        $this->fail('Missing check ' . $id);
    }
    public function testReadOnlyChecksNeverCreateObjectsOrExposeSecrets(): void {
        $this->client->webhookEndpoints->objects[] = $this->endpoint('we_existing', ['secret' => 'whsec_neverReturn']);
        $report = $this->checker->check(['wallet' => true, 'wallet_stripe' => true, 'subscription' => true]);
        $this->assertFalse($report['has_failures']);
        $this->assertCount(8, $report['permissions']);
        $this->assertSame('warning', $report['permissions'][0]['status']);
        $this->assertSame('warning', $this->check('signing', $report)['status']);
        $this->assertStringNotContainsString('whsec_', json_encode($report));
        $this->assertStringNotContainsString('rk_test_', json_encode($report));
        $this->assertSame([], $this->client->webhookEndpoints->creates);
        $this->assertSame([], $this->client->webhookEndpoints->updates);
    }
    public function testMismatchedModesPreventApiCallsAndSetup(): void {
        $this->settings->Publishablekey = 'pk_live_other';
        $this->assertSame('fail', $this->check('keys', $this->checker->check())['status']);
        $this->assertSame('fail', $this->checker->configure(function () { $this->fail('Must not save'); })['status']);
        $this->assertSame([], $this->client->paymentIntents->calls);
        $this->assertSame([], $this->client->webhookEndpoints->calls);
    }
    public function testDeniedPermissionsAreActionableAndRedacted(): void {
        $this->client->invoices->denied = true;
        $report = $this->checker->check(['subscription' => true]);
        $this->assertTrue($report['has_failures']);
        $this->assertSame('fail', $report['permissions'][7]['status']);
        $this->assertStringContainsString('denied', $report['permissions'][7]['message']);
        $this->assertStringNotContainsString('NEVER EXPOSE', json_encode($report));
    }
    public function testPaginationFindsEndpointAndNoOpDoesNotNeedWrite(): void {
        $this->client->webhookEndpoints->pageSize = 20;
        for ($i = 0; $i < 25; ++$i) {
            $this->client->webhookEndpoints->objects[] = $this->endpoint('we_' . $i, ['url' => 'https://other.example/' . $i]);
        }
        $this->client->webhookEndpoints->objects[] = $this->endpoint();
        $this->assertSame('pass', $this->checker->configure(function () { $this->fail('Must not save'); })['status']);
        $this->assertCount(2, $this->client->webhookEndpoints->calls);
        $this->assertSame([], $this->client->webhookEndpoints->creates);
        $this->assertSame([], $this->client->webhookEndpoints->updates);
    }
    public function testRepairAddsEventsAndEnablesOnlyExactMatchingEndpoint(): void {
        $this->client->webhookEndpoints->objects = [$this->endpoint('we_other', ['url' => self::URL . '?other=1']),
            $this->endpoint('we_ours', ['status' => 'disabled', 'enabled_events' => ['charge.failed']])];
        $this->assertSame('pass', $this->checker->configure(function () { $this->fail('Must not save'); })['status']);
        $this->assertSame([['we_ours', ['disabled' => false, 'enabled_events' => ['charge.failed', 'charge.succeeded', 'invoice.payment_succeeded']]]], $this->client->webhookEndpoints->updates);
        $this->assertSame('pass', $this->check('webhook', $this->checker->check())['status']);
    }
    public function testWildcardEventsArePreserved(): void {
        $this->client->webhookEndpoints->objects[] = $this->endpoint('we_existing', ['enabled_events' => ['*']]);
        $this->checker->configure(function () {});
        $this->assertSame([], $this->client->webhookEndpoints->updates);
    }
    public function testNewEndpointPinsVersionAndSavesEncryptedSecretWithoutReplacingManualSecret(): void {
        $saved = null;
        $result = $this->checker->configure(function ($settings) use (&$saved) { $saved = $settings; return true; });
        $this->assertSame('pass', $result['status']);
        $this->assertSame(StripeConfiguration::EVENTS, $this->client->webhookEndpoints->creates[0][0]['enabled_events']);
        $this->assertSame('2022-11-15', $this->client->webhookEndpoints->creates[0][0]['api_version']);
        $this->assertSame('whsec_manual', $saved->SigningSecret);
        $this->assertStringNotContainsString('whsec_', $saved->automaticWebhookSecret->value);
        $this->assertSame('whsec_manual,whsec_createdSecret', StripeConfiguration::signingSecrets($saved, self::URL));
        $this->assertStringNotContainsString('whsec_', json_encode($this->checker->check()));
        $saved->Restrictedkey = 'rk_test_other';
        $this->assertSame('whsec_manual', StripeConfiguration::signingSecrets($saved, self::URL));
        $saved->Restrictedkey = 'rk_test_private';
        $this->assertSame('whsec_manual', StripeConfiguration::signingSecrets($saved, self::URL . '?other=1'));
    }
    /** @dataProvider unsafeExistingEndpoints */
    public function testExistingVersionModeOrDuplicatesRequireManualReview($changes, $duplicate): void {
        $this->client->webhookEndpoints->objects[] = $this->endpoint('we_existing', $changes);
        if ($duplicate) { $this->client->webhookEndpoints->objects[] = $this->endpoint('we_duplicate'); }
        $this->assertSame('fail', $this->checker->configure(function () { $this->fail('Must not save'); })['status']);
        $this->assertSame([], $this->client->webhookEndpoints->creates);
        $this->assertSame([], $this->client->webhookEndpoints->updates);
    }
    public function unsafeExistingEndpoints(): array {
        return [[['api_version' => null], false], [['api_version' => '2025-01-27.acacia'], false], [['livemode' => true], false], [[], true]];
    }
    public function testMissingSecretOnExistingEndpointIsNotFalselySuccessful(): void {
        $this->settings->SigningSecret = '';
        $this->client->webhookEndpoints->objects[] = $this->endpoint();
        $this->assertSame('warning', $this->checker->configure(function () { $this->fail('Must not save'); })['status']);
        $this->assertSame('fail', $this->check('signing', $this->checker->check())['status']);
    }
    public function testDeliveryEvidenceIsBoundToCurrentKeyUrlAndSecrets(): void {
        StripeConfiguration::recordDelivery($this->settings, self::URL, (object) ['type' => 'charge.succeeded', 'livemode' => false]);
        $this->assertSame('pass', $this->check('signing', $this->checker->check())['status']);
        $this->assertStringNotContainsString('whsec_', json_encode(ObjectYPT::$cache));
        $this->settings->SigningSecret = 'whsec_rotated';
        $this->assertSame('warning', $this->check('signing', $this->checker->check())['status']);
        $this->settings->SigningSecret = 'whsec_manual';
        $this->settings->Restrictedkey = 'rk_test_changed';
        $this->assertSame('warning', $this->check('signing', $this->checker->check())['status']);
        $this->settings->Restrictedkey = 'rk_test_private';
        $otherUrl = new StripeConfiguration($this->settings, self::URL . '?other', $this->client);
        $this->assertSame('warning', $this->check('signing', $otherUrl->check())['status']);
    }
    public function testWrongModeOrIgnoredEventCannotProduceDeliveryProof(): void {
        StripeConfiguration::recordDelivery($this->settings, self::URL, (object) ['type' => 'charge.succeeded', 'livemode' => true]);
        StripeConfiguration::recordDelivery($this->settings, self::URL, (object) ['type' => 'charge.failed', 'livemode' => false]);
        $this->assertSame([], ObjectYPT::$cache);
    }
    public function testAccountChargeCapabilityIsCheckedWithoutLeakingAccountDetails(): void {
        $this->client->accounts->enabled = false;
        $this->settings->Restrictedkey = 'rk_live_private';
        $this->settings->Publishablekey = 'pk_live_public';
        $report = $this->checker->check();
        $this->assertTrue($report['has_failures']);
        $this->assertSame('acct_fixture', $report['account_id']);
        $this->assertSame('fail', $this->check('account', $report)['status']);
        $this->assertStringNotContainsString('never-return', json_encode($report));
    }
    public function testSandboxChargeCapabilityDoesNotFalselyBlockTestPayments(): void {
        $this->client->accounts->enabled = false;
        $this->client->webhookEndpoints->objects[] = $this->endpoint();
        $report = $this->checker->check();
        $this->assertFalse($report['has_failures']);
        $this->assertSame('warning', $this->check('account', $report)['status']);
    }
    public function testOptionalAccountPermissionDoesNotBlockOtherChecks(): void {
        $this->client->accounts->denied = true;
        $this->client->webhookEndpoints->objects[] = $this->endpoint();
        $report = $this->checker->check();
        $this->assertFalse($report['has_failures']);
        $this->assertSame('warning', $this->check('account', $report)['status']);
        $this->assertSame('pass', $report['permissions'][1]['status']);
    }
    public function testSaveFailureIsReportedAndRetryDoesNotCreateAnotherEndpoint(): void {
        $this->settings->SigningSecret = '';
        $result = $this->checker->configure(function () { return false; });
        $this->assertSame('fail', $result['status']);
        $this->assertStringContainsString('could not be saved', $result['message']);
        $this->checker->configure(function () { $this->fail('Must not save'); });
        $this->assertCount(1, $this->client->webhookEndpoints->creates);
    }
    public function testDeniedWebhookAccessDoesNotTriggerBlindCreate(): void {
        $this->client->webhookEndpoints->denied = true;
        $report = $this->checker->check();
        $this->assertFalse($report['can_configure']);
        $this->assertSame('warning', $this->check('webhook', $report)['status']);
        $this->assertSame('fail', $this->checker->configure(function () {})['status']);
        $this->assertSame([], $this->client->webhookEndpoints->creates);
    }
    public function testReadPermissionDoesNotPretendWriteSetupSucceeded(): void {
        $this->client->webhookEndpoints->denyWrite = true;
        $result = $this->checker->configure(function () { $this->fail('Must not save'); });
        $this->assertSame('fail', $result['status']);
        $this->assertStringContainsString('Write', $result['message']);
        $this->assertStringNotContainsString('sk_test_', json_encode($result));
        $this->assertSame([], $this->client->webhookEndpoints->creates);
    }
    /** @dataProvider invalidUrls */
    public function testSetupRejectsNonPublicHttpsUrls($url): void {
        $checker = new StripeConfiguration($this->settings, $url, $this->client);
        $this->assertSame('fail', $checker->configure(function () {})['status']);
        $this->assertSame([], $this->client->webhookEndpoints->calls);
    }
    public function invalidUrls(): array { return [['http://site.example/ipn.php'], ['https://localhost/ipn.php'], ['https://127.0.0.1/ipn.php']]; }
}
