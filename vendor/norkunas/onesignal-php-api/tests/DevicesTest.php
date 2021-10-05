<?php

declare(strict_types=1);

namespace OneSignal\Tests;

use OneSignal\Devices;
use OneSignal\OneSignal;
use OneSignal\Resolver\ResolverFactory;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DevicesTest extends ApiTestCase
{
    public function testGetOne(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame(OneSignal::API_URL.'/players/e4e87830-b954-11e3-811d-f3b376925f15?app_id=fakeApplicationId', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);

            return new MockResponse($this->loadFixture('devices_get_one.json'), ['http_code' => 200]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->getOne('e4e87830-b954-11e3-811d-f3b376925f15');

        self::assertSame([
            'identifier' => 'ce777617da7f548fe7a9ab6febb56cf39fba6d382000c0395666288d961ee566',
            'session_count' => 1,
            'language' => 'en',
            'timezone' => -28800,
            'game_version' => '1.0',
            'device_os' => '7.0.4',
            'device_type' => 0,
            'device_model' => 'iPhone',
            'ad_id' => null,
            'tags' => ['a' => '1', 'foo' => 'bar'],
            'last_active' => 1395096859,
            'amount_spent' => 0.0,
            'created_at' => 1395096859,
            'invalid_identifier' => false,
            'badge_count' => 0,
            'external_user_id' => null,
        ], $responseData);
    }

    public function testGetOneNonExisting(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame(OneSignal::API_URL.'/players/a?app_id=fakeApplicationId', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);

            return new MockResponse($this->loadFixture('devices_get_one_not_existing.json'), ['http_code' => 400]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->getOne('a');

        self::assertSame([
            'errors' => ['No user with this id found'],
        ], $responseData);
    }

    public function testGetAll(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame(OneSignal::API_URL.'/players?app_id=fakeApplicationId', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('devices_get_all.json'), ['http_code' => 200]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->getAll();

        self::assertSame([
            'total_count' => 1,
            'offset' => 0,
            'limit' => 300,
            'players' => [
                [
                    'identifier' => 'ce777617da7f548fe7a9ab6febb56cf39fba6d382000c0395666288d961ee566',
                    'session_count' => 1,
                    'language' => 'en',
                    'timezone' => -28800,
                    'game_version' => '1.0',
                    'device_os' => '7.0.4',
                    'device_type' => 0,
                    'device_model' => 'iPhone',
                    'ad_id' => null,
                    'tags' => ['a' => '1', 'foo' => 'bar'],
                    'last_active' => 1395096859,
                    'amount_spent' => 0.0,
                    'created_at' => 1395096859,
                    'invalid_identifier' => false,
                    'badge_count' => 0,
                    'external_user_id' => null,
                ],
            ],
        ], $responseData);
    }

    public function testAdd(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/players', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('devices_add.json'), ['http_code' => 200]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->add([
            'identifier' => 'ce777617da7f548fe7a9ab6febb56cf39fba6d382000c0395666288d961ee566',
            'language' => 'en',
            'timezone' => -28800,
            'game_version' => '1.0',
            'device_os' => '7.0.4',
            'device_type' => 0,
            'device_model' => 'iPhone 8,2',
            'tags' => ['a' => '1', 'foo' => 'bar'],
        ]);

        self::assertSame([
            'success' => true,
            'id' => 'ffffb794-ba37-11e3-8077-031d62f86ebf',
        ], $responseData);
    }

    public function testUpdate(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('PUT', $method);
            $this->assertSame(OneSignal::API_URL.'/players/e4e87830-b954-11e3-811d-f3b376925f15', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('devices_update.json'), ['http_code' => 200]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->update('e4e87830-b954-11e3-811d-f3b376925f15', [
            'language' => 'es',
            'timezone' => -28800,
            'game_version' => '1.0',
            'device_os' => '7.0.4',
            'device_model' => 'iPhone',
            'ip' => '127.0.0.1',
            'tags' => ['a' => '1', 'foo' => ''],
        ]);

        self::assertSame([
            'success' => true,
        ], $responseData);
    }

    public function testDelete(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('DELETE', $method);
            $this->assertSame(OneSignal::API_URL.'/players/e4e87830-b954-11e3-811d-f3b376925f15?app_id=fakeApplicationId', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('devices_delete.json'), ['http_code' => 200]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->delete('e4e87830-b954-11e3-811d-f3b376925f15');

        self::assertSame([
            'success' => true,
        ], $responseData);
    }

    public function testOnSession(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/players/e4e87830-b954-11e3-811d-f3b376925f15/on_session', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('devices_on_session.json'), ['http_code' => 200]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->onSession('e4e87830-b954-11e3-811d-f3b376925f15', [
            'language' => 'es',
            'timezone' => -28800,
            'game_version' => '1.0',
            'device_os' => '7.0.4',
        ]);

        self::assertSame([
            'success' => true,
        ], $responseData);
    }

    public function testOnPurchase(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/players/e4e87830-b954-11e3-811d-f3b376925f15/on_purchase', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('devices_on_purchase.json'), ['http_code' => 200]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->onPurchase('e4e87830-b954-11e3-811d-f3b376925f15', [
            'purchases' => [
                [
                    'sku' => 'SKU123',
                    'iso' => 'USD',
                    'amount' => 0.99,
                ],
            ],
        ]);

        self::assertSame([
            'success' => true,
        ], $responseData);
    }

    public function testOnFocus(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/players/e4e87830-b954-11e3-811d-f3b376925f15/on_focus', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('devices_on_focus.json'), ['http_code' => 200]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->onFocus('e4e87830-b954-11e3-811d-f3b376925f15', [
            'state' => 'ping',
            'active_time' => 60,
        ]);

        self::assertSame([
            'success' => true,
        ], $responseData);
    }

    public function testCsvExport(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/players/csv_export?app_id=fakeApplicationId', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('devices_csv_export.json'), ['http_code' => 200]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->csvExport(['country', 'notification_types', 'external_user_id', 'location', 'rooted', 'ip', 'country', 'web_auth', 'web_p256'], 'Active Users', 1469392779);

        self::assertSame([
            'csv_file_url' => 'https://onesignal.com/csv_exports/b2f7f966-d8cc-11e4-bed1-df8f05be55ba/users_184948440ec0e334728e87228011ff41_2015-11-10.csv.gz',
        ], $responseData);
    }

    public function testEditTags(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('PUT', $method);
            $this->assertSame(OneSignal::API_URL.'/apps/fakeApplicationId/users/12345', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('devices_edit_tags.json'), ['http_code' => 200]);
        });

        $devices = new Devices($client, new ResolverFactory($client->getConfig()));

        $responseData = $devices->editTags('12345', [
            'tags' => ['a' => '1', 'foo' => ''],
        ]);

        self::assertSame([
            'success' => true,
        ], $responseData);
    }
}
