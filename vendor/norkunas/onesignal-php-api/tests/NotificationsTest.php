<?php

declare(strict_types=1);

namespace OneSignal\Tests;

use OneSignal\Notifications;
use OneSignal\OneSignal;
use OneSignal\Resolver\ResolverFactory;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class NotificationsTest extends ApiTestCase
{
    public function testGetOne(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame(OneSignal::API_URL.'/notifications/481a2734-6b7d-11e4-a6ea-4b53294fa671?app_id=fakeApplicationId', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);

            return new MockResponse($this->loadFixture('notifications_get_one.json'), ['http_code' => 200]);
        });

        $notifications = new Notifications($client, new ResolverFactory($client->getConfig()));

        $responseData = $notifications->getOne('481a2734-6b7d-11e4-a6ea-4b53294fa671');

        self::assertSame([
            'id' => '481a2734-6b7d-11e4-a6ea-4b53294fa671',
            'successful' => 15,
            'failed' => 1,
            'converted' => 3,
            'remaining' => 0,
            'queued_at' => 1415914655,
            'send_after' => 1415914655,
            'completed_at' => 1415914656,
            'url' => 'https://yourWebsiteToOpen.com',
            'data' => ['foo' => 'bar', 'your' => 'custom metadata'],
            'canceled' => false,
            'headings' => [
                'en' => 'English and default language heading',
                'es' => 'Spanish language heading',
            ],
            'contents' => [
                'en' => 'English language content',
                'es' => 'Hola',
            ],
            'platform_delivery_stats' => [
                'ios' => [
                    'success' => 5,
                    'failed' => 1,
                    'errored' => 0,
                ],
                'android' => [
                    'success' => 10,
                    'failed' => 0,
                    'errored' => 0,
                ],
            ],
        ], $responseData);
    }

    public function testGetAll(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('GET', $method);
            $this->assertSame(OneSignal::API_URL.'/notifications?app_id=fakeApplicationId', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('notifications_get_all.json'), ['http_code' => 200]);
        });

        $notifications = new Notifications($client, new ResolverFactory($client->getConfig()));

        $responseData = $notifications->getAll();

        self::assertSame([
            'total_count' => 1,
            'offset' => 0,
            'limit' => 50,
            'notifications' => [
                [
                    'adm_big_picture' => '',
                    'adm_group' => '',
                    'adm_group_message' => ['en' => ''],
                    'adm_large_icon' => '',
                    'adm_small_icon' => '',
                    'adm_sound' => '',
                    'spoken_text' => [],
                    'alexa_ssml' => null,
                    'alexa_display_title' => null,
                    'amazon_background_data' => false,
                    'android_accent_color' => '',
                    'android_group' => '',
                    'android_group_message' => ['en' => ''],
                    'android_led_color' => '',
                    'android_sound' => '',
                    'android_visibility' => 1,
                    'app_id' => '3beb3078-e0f1-4629-af17-fde833b9f716',
                    'big_picture' => '',
                    'buttons' => [['id' => 'test1', 'text' => 'Download', 'icon' => '']],
                    'canceled' => false,
                    'chrome_big_picture' => '',
                    'chrome_icon' => '',
                    'chrome_web_icon' => 'https://img.onesignal.com/t/73b9b966-f19e-4410-8b5d-51ebdef4652e.png',
                    'chrome_web_image' => '',
                    'chrome_web_badge' => '',
                    'content_available' => false,
                    'contents' => [
                        'en' => 'Come by and check out our new Jordan\'s!!! (Shoes) 🎃🙊👻',
                    ],
                    'converted' => 1,
                    'data' => ['your_data_key' => 'your_data_value'],
                    'delayed_option' => 'immediate',
                    'delivery_time_of_day' => '1:15PM',
                    'errored' => 1,
                    'excluded_segments' => ['3 Days Inactive'],
                    'failed' => 0,
                    'firefox_icon' => '',
                    'headings' => [
                        'en' => 'Thomas\' Greatest Site in the World!! 😜😁',
                    ],
                    'id' => 'e664a747-324c-406a-bafb-ab51db71c960',
                    'include_player_ids' => null,
                    'include_external_user_ids' => null,
                    'channel_for_external_user_ids' => 'push',
                    'included_segments' => ['All'],
                    'thread_id' => null,
                    'ios_badgeCount' => 1,
                    'ios_badgeType' => 'None',
                    'ios_category' => '',
                    'ios_sound' => '',
                    'apns_alert' => null,
                    'isAdm' => false,
                    'isAndroid' => true,
                    'isChrome' => false,
                    'isChromeWeb' => true,
                    'isAlexa' => false,
                    'isFirefox' => true,
                    'isIos' => true,
                    'isSafari' => true,
                    'isWP' => false,
                    'isWP_WNS' => false,
                    'isEdge' => null,
                    'large_icon' => '',
                    'priority' => 5,
                    'queued_at' => 1557946677,
                    'remaining' => 0,
                    'send_after' => 1557946620,
                    'completed_at' => 1557946677,
                    'small_icon' => '',
                    'successful' => 386,
                    'received' => null,
                    'tags' => null,
                    'filters' => null,
                    'template_id' => null,
                    'ttl' => null,
                    'url' => 'https://mysite.com',
                    'web_url' => null,
                    'app_url' => null,
                    'web_buttons' => null,
                    'web_push_topic' => null,
                    'wp_sound' => '',
                    'wp_wns_sound' => '',
                    'platform_delivery_stats' => [
                        'chrome_web_push' => [
                            'successful' => 14,
                            'failed' => 0,
                            'errored' => 0,
                        ],
                        'android' => [
                            'errored' => 1,
                            'successful' => 368,
                            'failed' => 0,
                        ],
                        'safari_web_push' => [
                            'successful' => 2,
                            'failed' => 0,
                            'errored' => 0,
                        ],
                        'ios' => [
                            'successful' => 1,
                            'failed' => 0,
                            'errored' => 0,
                        ],
                        'firefox_web_push' => [
                            'successful' => 1,
                            'failed' => 0,
                            'errored' => 0,
                        ],
                    ],
                    'ios_attachments' => [
                        'https://img.onesignal.com/n/44843933-68d4-450c-af5c-5e5c1a9d946e.jpg',
                    ],
                ],
            ],
        ], $responseData);
    }

    public function testAdd(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/notifications', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertArrayHasKey('content-type', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Content-Type: application/json', $options['normalized_headers']['content-type'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('notifications_add.json'), ['http_code' => 200]);
        });

        $notifications = new Notifications($client, new ResolverFactory($client->getConfig()));

        $responseData = $notifications->add([
            'name' => 'My Notification Name',
            'contents' => [
                'en' => 'English Message',
            ],
            'included_segments' => ['All'],
            'data' => ['foo' => 'bar'],
            'web_buttons' => [
                [
                    'id' => 'like-button',
                    'text' => 'Like',
                    'icon' => 'http://i.imgur.com/N8SN8ZS.png',
                    'url' => 'https://yoursite.com',
                ],
                [
                    'id' => 'like-button-2',
                    'text' => 'Like2',
                    'icon' => 'http://i.imgur.com/N8SN8ZS.png',
                    'url' => 'https://yoursite.com',
                ],
            ],
        ]);

        self::assertSame([
            'id' => '458dcec4-cf53-11e3-add2-000c2940e62c',
            'recipients' => 3,
        ], $responseData);
    }

    public function testOpen(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('PUT', $method);
            $this->assertSame(OneSignal::API_URL.'/notifications/458dcec4-cf53-11e3-add2-000c2940e62c', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('notifications_open.json'), ['http_code' => 200]);
        });

        $notifications = new Notifications($client, new ResolverFactory($client->getConfig()));

        $responseData = $notifications->open('458dcec4-cf53-11e3-add2-000c2940e62c');

        self::assertSame([
            'success' => true,
        ], $responseData);
    }

    public function testCancel(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('DELETE', $method);
            $this->assertSame(OneSignal::API_URL.'/notifications/458dcec4-cf53-11e3-add2-000c2940e62c?app_id=fakeApplicationId', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('notifications_cancel.json'), ['http_code' => 200]);
        });

        $notifications = new Notifications($client, new ResolverFactory($client->getConfig()));

        $responseData = $notifications->cancel('458dcec4-cf53-11e3-add2-000c2940e62c');

        self::assertSame([
            'success' => true,
        ], $responseData);
    }

    public function testHistory(): void
    {
        $client = $this->createClientMock(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame(OneSignal::API_URL.'/notifications/458dcec4-cf53-11e3-add2-000c2940e62c/history', $url);
            $this->assertArrayHasKey('accept', $options['normalized_headers']);
            $this->assertArrayHasKey('authorization', $options['normalized_headers']);
            $this->assertSame('Accept: application/json', $options['normalized_headers']['accept'][0]);
            $this->assertSame('Authorization: Basic fakeApplicationAuthKey', $options['normalized_headers']['authorization'][0]);

            return new MockResponse($this->loadFixture('notifications_history.json'), ['http_code' => 200]);
        });

        $notifications = new Notifications($client, new ResolverFactory($client->getConfig()));

        $responseData = $notifications->history('458dcec4-cf53-11e3-add2-000c2940e62c', [
            'events' => 'clicked',
            'email' => 'your_email@email.com',
        ]);

        self::assertSame([
            'success' => true,
            'destination_url' => 'https://onesignal-aws-link.com',
        ], $responseData);
    }
}
