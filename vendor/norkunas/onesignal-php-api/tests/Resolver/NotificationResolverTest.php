<?php

declare(strict_types=1);

namespace OneSignal\Tests\Resolver;

use DateTime;
use OneSignal\Resolver\NotificationResolver;
use OneSignal\Tests\OneSignalTestCase;
use OneSignal\Tests\PrivateAccessorTrait;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationResolverTest extends OneSignalTestCase
{
    use PrivateAccessorTrait;

    /**
     * @var NotificationResolver
     */
    private $notificationResolver;

    protected function setUp(): void
    {
        $this->notificationResolver = new NotificationResolver($this->createConfig());
    }

    public function testResolveWithValidValues(): void
    {
        $inputData = [
            'name' => 'value',
            'contents' => ['value'],
            'headings' => ['value'],
            'subtitle' => ['value'],
            'isIos' => false,
            'isAndroid' => false,
            'isWP' => false,
            'isWP_WNS' => false,
            'isAdm' => false,
            'isChrome' => false,
            'isChromeWeb' => false,
            'isFirefox' => false,
            'isSafari' => false,
            'isAnyWeb' => false,
            'included_segments' => ['value'],
            'excluded_segments' => ['value'],
            'include_player_ids' => ['value'],
            'include_ios_tokens' => ['value'],
            'include_android_reg_ids' => ['value'],
            'include_external_user_ids' => ['value'],
            'channel_for_external_user_ids' => 'push',
            'include_email_tokens' => ['value'],
            'include_wp_uris' => ['value'],
            'include_wp_wns_uris' => ['value'],
            'include_amazon_reg_ids' => ['value'],
            'include_chrome_reg_ids' => ['value'],
            'include_chrome_web_reg_ids' => ['value'],
            'app_ids' => ['value'],
            'filters' => [],
            'ios_badgeType' => 'SetTo',
            'ios_badgeCount' => 23,
            'ios_sound' => 'value',
            'android_sound' => 'value',
            'adm_sound' => 'value',
            'wp_sound' => 'value',
            'wp_wns_sound' => 'value',
            'data' => ['value'],
            'buttons' => [],
            'android_channel_id' => '09228c02-6188-4307-b139-402600213d0e',
            'existing_android_channel_id' => '09228c02-6188-4307-b139-402600213d0e',
            'android_background_layout' => ['image' => 'https://example.com/image/png', 'headings_color' => 'FF0000FF', 'contents_color' => 'FFFF0000'],
            'small_icon' => 'value',
            'large_icon' => 'value',
            'ios_attachments' => ['key' => 'value'],
            'big_picture' => 'value',
            'adm_small_icon' => 'value',
            'adm_large_icon' => 'value',
            'adm_big_picture' => 'value',
            'web_buttons' => [
                [
                    'id' => 'value',
                    'text' => 'value',
                    'icon' => 'value',
                    'url' => 'value',
                ],
            ],
            'ios_category' => 'value',
            'chrome_icon' => 'value',
            'chrome_big_picture' => 'value',
            'chrome_web_icon' => 'value',
            'chrome_web_image' => 'value',
            'firefox_icon' => 'value',
            'url' => 'http://url.com',
            'web_url' => 'http://url.com',
            'app_url' => 'myapp://path',
            'send_after' => new DateTime(),
            'delayed_option' => 'timezone',
            'delivery_time_of_day' => new DateTime(),
            'android_led_color' => 'value',
            'android_accent_color' => 'value',
            'android_visibility' => -1,
            'collapse_id' => 'value',
            'content_available' => true,
            'mutable_content' => true,
            'android_background_data' => true,
            'amazon_background_data' => true,
            'template_id' => 'value',
            'android_group' => 'value',
            'android_group_message' => ['value'],
            'adm_group' => 'value',
            'adm_group_message' => ['value'],
            'thread_id' => 'value',
            'summary_arg' => 'value',
            'summary_arg_count' => 10,
            'ttl' => 23,
            'priority' => 10,
            'app_id' => 'value',
            'email_subject' => 'value',
            'email_body' => 'value',
            'email_from_name' => 'value',
            'email_from_address' => 'value',
            'external_id' => 'value',
            'web_push_topic' => 'value',
            'apns_push_type_override' => 'voip',
            'sms_from' => 'value',
            'sms_media_urls' => ['value'],
        ];

        $expectedData = $inputData;
        $expectedData['send_after'] = $expectedData['send_after']->format('Y-m-d H:i:sO');
        $expectedData['delivery_time_of_day'] = $expectedData['delivery_time_of_day']->format('g:iA');

        self::assertEquals($expectedData, $this->notificationResolver->resolve($inputData));
    }

    public function wrongValueTypesProvider(): iterable
    {
        yield [['name' => 666]];
        yield [['contents' => 666]];
        yield [['headings' => 666]];
        yield [['subtitle' => 666]];
        yield [['isIos' => 666]];
        yield [['isAndroid' => 666]];
        yield [['isWP' => 666]];
        yield [['isWP_WNS' => 666]];
        yield [['isAdm' => 666]];
        yield [['isChrome' => 666]];
        yield [['isChromeWeb' => 666]];
        yield [['isFirefox' => 666]];
        yield [['isSafari' => 666]];
        yield [['isAnyWeb' => 666]];
        yield [['included_segments' => 'wrongType']];
        yield [['excluded_segments' => 'wrongType']];
        yield [['include_player_ids' => 'wrongType']];
        yield [['include_ios_tokens' => 'wrongType']];
        yield [['include_android_reg_ids' => 666]];
        yield [['include_external_user_ids' => 666]];
        yield [['channel_for_external_user_ids' => 'wrongChannel']];
        yield [['include_email_tokens' => 666]];
        yield [['include_wp_uris' => 666]];
        yield [['include_wp_wns_uris' => 666]];
        yield [['include_amazon_reg_ids' => 666]];
        yield [['include_chrome_reg_ids' => 666]];
        yield [['include_chrome_web_reg_ids' => 666]];
        yield [['app_ids' => 666]];
        yield [['filters' => 666]];
        yield [['ios_badgeType' => 'wrongType']];
        yield [['ios_badgeCount' => 'wrongType']];
        yield [['ios_sound' => 666]];
        yield [['android_sound' => 666]];
        yield [['adm_sound' => 666]];
        yield [['wp_sound' => 666]];
        yield [['wp_wns_sound' => 666]];
        yield [['data' => 666]];
        yield [['buttons' => 666]];
        yield [['android_channel_id' => 666]];
        yield [['existing_android_channel_id' => 666]];
        yield [['android_background_layout' => ['wrongKey' => 'value']]];
        yield [['small_icon' => 666]];
        yield [['large_icon' => 666]];
        yield [['ios_attachments' => 666]];
        yield [['big_picture' => 666]];
        yield [['adm_small_icon' => 666]];
        yield [['adm_large_icon' => 666]];
        yield [['adm_big_picture' => 666]];
        yield [['web_buttons' => 666]];
        yield [['ios_category' => 666]];
        yield [['chrome_icon' => 666]];
        yield [['chrome_big_picture' => 666]];
        yield [['chrome_web_icon' => 666]];
        yield [['chrome_web_image' => 666]];
        yield [['firefox_icon' => 666]];
        yield [['url' => 666]];
        yield [['web_url' => 666]];
        yield [['app_url' => 666]];
        yield [['send_after' => 666]];
        yield [['delayed_option' => 666]];
        yield [['delivery_time_of_day' => 666]];
        yield [['android_led_color' => 666]];
        yield [['android_accent_color' => 666]];
        yield [['android_visibility' => 'wrongType']];
        yield [['collapse_id' => 666]];
        yield [['content_available' => 666]];
        yield [['mutable_content' => 666]];
        yield [['android_background_data' => 666]];
        yield [['amazon_background_data' => 666]];
        yield [['template_id' => 666]];
        yield [['android_group' => 666]];
        yield [['android_group_message' => 666]];
        yield [['adm_group' => 666]];
        yield [['adm_group_message' => 666]];
        yield [['ttl' => 'wrongType']];
        yield [['priority' => 'wrongType']];
        yield [['app_id' => 666]];
        yield [['email_subject' => 666]];
        yield [['email_body' => 666]];
        yield [['email_from_name' => 666]];
        yield [['email_from_address' => 666]];
        yield [['external_id' => 666]];
        yield [['web_push_topic' => 666]];
        yield [['apns_push_type_override' => 666]];
        yield [['sms_from' => 666]];
        yield [['sms_media_urls' => 666]];
    }

    /**
     * @dataProvider wrongValueTypesProvider
     */
    public function testResolveWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->notificationResolver->resolve($wrongOption);
    }

    public function testResolveDefaultValues(): void
    {
        $expectedData = [
            'app_id' => 'fakeApplicationId',
        ];

        self::assertEquals($expectedData, $this->notificationResolver->resolve([]));
    }

    public function testResolveWithWrongOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->notificationResolver->resolve(['wrongOption' => 'wrongValue']);
    }

    /****** Private functions testing ******/

    public function testNormalizeFilters(): void
    {
        $method = $this->getPrivateMethod(NotificationResolver::class, 'normalizeFilters');

        $inputData = [
            new OptionsResolver(),
            [
                ['field' => 'myField'],
                ['wrongField' => 'wrongValue'],
                ['operator' => 'OR'],
                ['operator' => 'AND'],
            ],
        ];

        $expectedData =
            [
                ['field' => 'myField'],
                ['operator' => 'OR'],
                ['operator' => 'OR'],
            ];

        self::assertEquals($expectedData, $method->invokeArgs($this->notificationResolver, $inputData));
    }

    public function testFilterUrl(): void
    {
        $method = $this->getPrivateMethod(NotificationResolver::class, 'filterUrl');

        self::assertEquals(true, $method->invokeArgs($this->notificationResolver, ['http://fakeUrl.com']));
        self::assertEquals(false, $method->invokeArgs($this->notificationResolver, ['wrongUrl']));
    }

    public function testNormalizeButtons(): void
    {
        $method = $this->getPrivateMethod(NotificationResolver::class, 'normalizeButtons');

        $inputData = [
            ['wrongOption' => 'wrongValue'],
            ['text' => 'value', 'id' => 2],
            ['text' => 'value', 'id' => 8, 'icon' => 'iconValue'],
        ];

        $expectedData = [
            ['text' => 'value', 'id' => 2, 'icon' => null],
            ['text' => 'value', 'id' => 8, 'icon' => 'iconValue'],
        ];

        self::assertEquals($expectedData, $method->invokeArgs($this->notificationResolver, [$inputData]));
    }

    public function testFilterAndroidBackgroundLayout(): void
    {
        $method = $this->getPrivateMethod(NotificationResolver::class, 'filterAndroidBackgroundLayout');

        self::assertEquals(false, $method->invokeArgs($this->notificationResolver, [[]]));

        $requiredData = [
            'image' => 'value',
            'headings_color' => 'value',
            'contents_color' => 'value',
        ];

        self::assertEquals(true, $method->invokeArgs($this->notificationResolver, [$requiredData]));

        $inputData = array_merge($requiredData, ['image' => 45]);

        self::assertEquals(false, $method->invokeArgs($this->notificationResolver, [$inputData]));

        $inputData = array_merge($requiredData, ['wrongOption' => 'wrongValue']);

        self::assertEquals(false, $method->invokeArgs($this->notificationResolver, [$inputData]));
    }

    public function testFilterIosAttachments(): void
    {
        $method = $this->getPrivateMethod(NotificationResolver::class, 'filterIosAttachments');

        self::assertEquals(false, $method->invokeArgs($this->notificationResolver, [['option' => 666]]));
        self::assertEquals(false, $method->invokeArgs($this->notificationResolver, [[666 => 666]]));
        self::assertEquals(false, $method->invokeArgs($this->notificationResolver, [[666 => 'value']]));
        self::assertEquals(true, $method->invokeArgs($this->notificationResolver, [['option' => 'value']]));
    }

    public function testFilterWebButtons(): void
    {
        $method = $this->getPrivateMethod(NotificationResolver::class, 'filterWebButtons');

        $inputData = [
            [
                'id' => 'value',
                'text' => 'value',
                'icon' => 'value',
                'url' => 'value',
            ],
        ];

        self::assertEquals(true, $method->invokeArgs($this->notificationResolver, [$inputData]));

        self::assertEquals(false, $method->invokeArgs($this->notificationResolver, [array_merge(['wrongOption' => 'wrongValue'], $inputData)]));

        unset($inputData[0]['url']);

        self::assertEquals(false, $method->invokeArgs($this->notificationResolver, [$inputData]));
    }

    public function testDateTime(): void
    {
        $method = $this->getPrivateMethod(NotificationResolver::class, 'normalizeDateTime');

        $inputData = new DateTime();
        $expectedData = $inputData->format(NotificationResolver::SEND_AFTER_FORMAT);

        self::assertEquals($expectedData, $method->invokeArgs($this->notificationResolver, [new OptionsResolver(), $inputData, NotificationResolver::SEND_AFTER_FORMAT]));
    }
}
