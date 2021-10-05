<?php

declare(strict_types=1);

namespace OneSignal\Tests;

use OneSignal\Apps;
use OneSignal\Devices;
use OneSignal\OneSignal;
use OneSignal\Resolver\ResolverFactory;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AppsTest extends ApiTestCase
{
    public function testGetOne(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame(OneSignal::API_URL.'/apps/e4e87830-b954-11e3-811d-f3b376925f15', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeUserAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('apps_get_one.json'), ['http_code' => 200]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->getOne('e4e87830-b954-11e3-811d-f3b376925f15');

        self::assertSame([
            'id' => 'e4e87830-b954-11e3-811d-f3b376925f15',
            'name' => 'Your app 1',
            'players' => 0,
            'messageable_players' => 0,
            'updated_at' => '2014-04-01T04:20:02.003Z',
            'created_at' => '2014-04-01T04:20:02.003Z',
            'gcm_key' => 'a gcm push key',
            'chrome_web_origin' => 'Chrome Web Push Site URL',
            'chrome_web_default_notification_icon' => 'http://yoursite.com/chrome_notification_icon',
            'chrome_web_sub_domain' => 'your_site_name',
            'apns_env' => 'production',
            'apns_certificates' => 'Your apns certificate',
            'safari_apns_certificate' => 'Your Safari APNS certificate',
            'safari_site_origin' => 'The homename for your website for Safari Push, including http or https',
            'safari_push_id' => 'The certificate bundle ID for Safari Web Push',
            'safari_icon_16_16' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/16x16.png',
            'safari_icon_32_32' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/16x16@2.png',
            'safari_icon_64_64' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/32x32@2x.png',
            'safari_icon_128_128' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/128x128.png',
            'safari_icon_256_256' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/128x128@2x.png',
            'site_name' => 'The URL to your website for Web Push',
            'basic_auth_key' => 'NGEwMGZmMjItY2NkNy0xMWUzLTk5ZDUtMDAwYzI5NDBlNjJj',
        ], $responseData);
    }

    public function testGetOneNonExisting(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame(OneSignal::API_URL.'/apps/a', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeUserAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('apps_get_one_not_existing.json'), ['http_code' => 404]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->getOne('a');

        self::assertSame([
            'errors' => 'Couldn\'t find app with id = a',
        ], $responseData);
    }

    public function testGetAll(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame(OneSignal::API_URL.'/apps', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeUserAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('apps_get_all.json'), ['http_code' => 200]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->getAll();

        self::assertSame([
            [
                'id' => '92911750-242d-4260-9e00-9d9034f139ce',
                'name' => 'Your app 1',
                'players' => 150,
                'messageable_players' => 143,
                'updated_at' => '2014-04-01T04:20:02.003Z',
                'created_at' => '2014-04-01T04:20:02.003Z',
                'gcm_key' => 'a gcm push key',
                'chrome_key' => 'A Chrome Web Push GCM key',
                'chrome_web_origin' => 'Chrome Web Push Site URL',
                'chrome_web_gcm_sender_id' => 'Chrome Web Push GCM Sender ID',
                'chrome_web_default_notification_icon' => 'http://yoursite.com/chrome_notification_icon',
                'chrome_web_sub_domain' => 'your_site_name',
                'apns_env' => 'sandbox',
                'apns_certificates' => 'Your apns certificate',
                'safari_apns_certificate' => 'Your Safari APNS certificate',
                'safari_site_origin' => 'The homename for your website for Safari Push, including http or https',
                'safari_push_id' => 'The certificate bundle ID for Safari Web Push',
                'safari_icon_16_16' => 'http://onesignal.com/safari_packages/92911750-242d-4260-9e00-9d9034f139ce/16x16.png',
                'safari_icon_32_32' => 'http://onesignal.com/safari_packages/92911750-242d-4260-9e00-9d9034f139ce/16x16@2.png',
                'safari_icon_64_64' => 'http://onesignal.com/safari_packages/92911750-242d-4260-9e00-9d9034f139ce/32x32@2x.png',
                'safari_icon_128_128' => 'http://onesignal.com/safari_packages/92911750-242d-4260-9e00-9d9034f139ce/128x128.png',
                'safari_icon_256_256' => 'http://onesignal.com/safari_packages/92911750-242d-4260-9e00-9d9034f139ce/128x128@2x.png',
                'site_name' => 'The URL to your website for Web Push',
                'basic_auth_key' => 'NGEwMGZmMjItY2NkNy0xMWUzLTk5ZDUtMDAwYzI5NDBlNjJj',
            ],
            [
                'id' => 'e4e87830-b954-11e3-811d-f3b376925f15',
                'name' => 'Your app 2',
                'players' => 100,
                'messageable_players' => 80,
                'updated_at' => '2014-04-01T04:20:02.003Z',
                'created_at' => '2014-04-01T04:20:02.003Z',
                'gcm_key' => 'a gcm push key',
                'chrome_key' => 'A Chrome Web Push GCM key',
                'chrome_web_origin' => 'Chrome Web Push Site URL',
                'chrome_web_gcm_sender_id' => 'Chrome Web Push GCM Sender ID',
                'chrome_web_default_notification_icon' => 'http://yoursite.com/chrome_notification_icon',
                'chrome_web_sub_domain' => 'your_site_name',
                'apns_env' => 'sandbox',
                'apns_certificates' => 'Your apns certificate',
                'safari_apns_certificate' => 'Your Safari APNS certificate',
                'safari_site_origin' => 'The homename for your website for Safari Push, including http or https',
                'safari_push_id' => 'The certificate bundle ID for Safari Web Push',
                'safari_icon_16_16' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/16x16.png',
                'safari_icon_32_32' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/16x16@2.png',
                'safari_icon_64_64' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/32x32@2x.png',
                'safari_icon_128_128' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/128x128.png',
                'safari_icon_256_256' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/128x128@2x.png',
                'site_name' => 'The URL to your website for Web Push',
                'basic_auth_key' => 'NGEwMGZmMjItY2NkNy0xMWUzLTk5ZDUtMDAwYzI5NDBlNjJj',
            ],
        ], $responseData);
    }

    public function testGetWithWrongUserAuthKey(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame(OneSignal::API_URL.'/apps', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeUserAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('apps_get_with_wrong_user_auth_key.json'), ['http_code' => 400]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->getAll();

        self::assertSame([
            'errors' => [
                'Please include a case-sensitive header of Authorization: Basic <YOUR-USER-AUTH-KEY-HERE> with a valid User Auth key.',
            ],
            'reference' => [
                'https://documentation.onesignal.com/docs/accounts-and-keys#section-user-auth-key',
            ],
        ], $responseData);
    }

    public function testAdd(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/apps', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeUserAuthKey', $options['normalized_headers']['authorization'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('apps_add.json'), ['http_code' => 200]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->add([
            'name' => 'Your app 1',
            'apns_env' => 'production',
            'apns_p12' => 'asdsadcvawe223cwef...',
            'apns_p12_password' => 'FooBar',
            'organization_id' => 'your_organization_id',
            'gcm_key' => 'a gcm push key',
        ]);

        self::assertSame([
            'id' => 'e4e87830-b954-11e3-811d-f3b376925f15',
            'name' => 'Your app 1',
            'players' => 0,
            'messageable_players' => 0,
            'updated_at' => '2014-04-01T04:20:02.003Z',
            'created_at' => '2014-04-01T04:20:02.003Z',
            'gcm_key' => 'a gcm push key',
            'chrome_web_origin' => 'Chrome Web Push Site URL',
            'chrome_web_default_notification_icon' => 'http://yoursite.com/chrome_notification_icon',
            'chrome_web_sub_domain' => 'your_site_name',
            'apns_env' => 'production',
            'apns_certificates' => 'Your apns certificate',
            'safari_apns_certificate' => 'Your Safari APNS certificate',
            'safari_site_origin' => 'The homename for your website for Safari Push, including http or https',
            'safari_push_id' => 'The certificate bundle ID for Safari Web Push',
            'safari_icon_16_16' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/16x16.png',
            'safari_icon_32_32' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/16x16@2.png',
            'safari_icon_64_64' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/32x32@2x.png',
            'safari_icon_128_128' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/128x128.png',
            'safari_icon_256_256' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/128x128@2x.png',
            'site_name' => 'The URL to your website for Web Push',
            'basic_auth_key' => 'NGEwMGZmMjItY2NkNy0xMWUzLTk5ZDUtMDAwYzI5NDBlNjJj',
        ], $responseData);
    }

    public function testAddWithEmptyName(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/apps', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeUserAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('apps_add_with_empty_name.json'), ['http_code' => 400]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->add(['name' => '']);

        self::assertSame([
            'errors' => [
                'Name Enter an app name',
            ],
        ], $responseData);
    }

    public function testUpdate(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/apps', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeUserAuthKey', $options['normalized_headers']['authorization'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('apps_add.json'), ['http_code' => 200]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->add([
            'name' => 'Your app 1',
            'apns_env' => 'production',
            'apns_p12' => 'asdsadcvawe223cwef...',
            'apns_p12_password' => 'FooBar',
            'organization_id' => 'your_organization_id',
            'gcm_key' => 'a gcm push key',
        ]);

        self::assertSame([
            'id' => 'e4e87830-b954-11e3-811d-f3b376925f15',
            'name' => 'Your app 1',
            'players' => 0,
            'messageable_players' => 0,
            'updated_at' => '2014-04-01T04:20:02.003Z',
            'created_at' => '2014-04-01T04:20:02.003Z',
            'gcm_key' => 'a gcm push key',
            'chrome_web_origin' => 'Chrome Web Push Site URL',
            'chrome_web_default_notification_icon' => 'http://yoursite.com/chrome_notification_icon',
            'chrome_web_sub_domain' => 'your_site_name',
            'apns_env' => 'production',
            'apns_certificates' => 'Your apns certificate',
            'safari_apns_certificate' => 'Your Safari APNS certificate',
            'safari_site_origin' => 'The homename for your website for Safari Push, including http or https',
            'safari_push_id' => 'The certificate bundle ID for Safari Web Push',
            'safari_icon_16_16' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/16x16.png',
            'safari_icon_32_32' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/16x16@2.png',
            'safari_icon_64_64' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/32x32@2x.png',
            'safari_icon_128_128' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/128x128.png',
            'safari_icon_256_256' => 'http://onesignal.com/safari_packages/e4e87830-b954-11e3-811d-f3b376925f15/128x128@2x.png',
            'site_name' => 'The URL to your website for Web Push',
            'basic_auth_key' => 'NGEwMGZmMjItY2NkNy0xMWUzLTk5ZDUtMDAwYzI5NDBlNjJj',
        ], $responseData);
    }

    public function testUpdateNotExisting(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('PUT', $method);
            $this->assertSame(OneSignal::API_URL.'/apps/a', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeUserAuthKey', $options['normalized_headers']['authorization'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('apps_update_not_existing.json'), ['http_code' => 404]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->update('a', ['name' => 'Your app 1']);

        self::assertSame([
            'status' => 404,
            'error' => 'Not Found',
        ], $responseData);
    }

    public function testCreateSegment(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/apps/fakeApplicationId/segments', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('apps_create_segment.json'), ['http_code' => 200]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->createSegment('fakeApplicationId', [
            'name' => '1',
            'filters' => [
                [
                    'field' => 'session_count',
                    'relation' => '>',
                    'value' => 1,
                ],
                [
                    'operator' => 'AND',
                ],
                [
                    'field' => 'tag',
                    'relation' => '!=',
                    'key' => 'tag_key',
                    'value' => '1',
                ],
                [
                    'operator' => 'OR',
                ],
                [
                    'field' => 'last_session',
                    'relation' => '<',
                    'value' => '30,',
                ],
            ],
        ]);

        self::assertSame([
            'success' => true,
            'id' => '7ed2887d-bd24-4a81-8220-4b256a08ab19',
        ], $responseData);
    }

    public function testCreateSegmentWithExistingId(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/apps/fakeApplicationId/segments', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('apps_create_segment_conflict.json'), ['http_code' => 409]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->createSegment('fakeApplicationId', [
            'id' => '7ed2887d-bd24-4a81-8220-4b256a08ab19',
            'name' => '1',
            'filters' => [],
        ]);

        self::assertSame([
            'success' => false,
            'errors' => ['Segment with the given id already exists.'],
        ], $responseData);
    }

    public function testCreateSegmentWithEmptyName(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/apps/fakeApplicationId/segments', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);

            return new MockResponse($this->loadFixture('apps_create_segment_with_empty_name.json'), ['http_code' => 400]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->createSegment('fakeApplicationId', [
            'name' => '',
            'filters' => [],
        ]);

        self::assertSame([
            'success' => false,
            'errors' => ['name is required'],
        ], $responseData);
    }

    public function testOutcomes(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame(OneSignal::API_URL.'/apps/fakeApplicationId/outcomes?outcome_time_range=1h&outcome_attribution=direct&outcome_names%5B%5D=os__session_duration.count&outcome_names%5B%5D=os__click.count&outcome_names%5B%5D=Sales%2C+Purchase.sum&outcome_platforms=0%2C1', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('apps_outcomes.json'), ['http_code' => 200]);
        });

        $apps = new Apps($client, new ResolverFactory($client->getConfig()));

        $responseData = $apps->outcomes('fakeApplicationId', [
            'outcome_names' => [
                'os__session_duration.count',
                'os__click.count',
                'Sales, Purchase.sum',
            ],
            'outcome_time_range' => '1h',
            'outcome_platforms' => [Devices::IOS, Devices::ANDROID],
            'outcome_attribution' => Apps::OUTCOME_ATTRIBUTION_DIRECT,
        ]);

        self::assertSame([
            'outcomes' => [
                [
                    'id' => 'os__session_duration',
                    'value' => 100,
                    'aggregation' => 'sum',
                ],
                [
                    'id' => 'os__click',
                    'value' => 4,
                    'aggregation' => 'count',
                ],
                [
                    'id' => 'Sales, Purchase.count',
                    'value' => 348,
                    'aggregation' => 'sum',
                ],
            ],
        ], $responseData);
    }
}
