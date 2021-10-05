<?php

declare(strict_types=1);

namespace OneSignal\Tests\Resolver;

use OneSignal\Resolver\AppResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class AppResolverTest extends TestCase
{
    /**
     * @var AppResolver
     */
    private $appResolver;

    protected function setUp(): void
    {
        $this->appResolver = new AppResolver();
    }

    public function testResolveWithValidValues(): void
    {
        $expectedData = [
            'name' => 'value',
            'apns_env' => 'sandbox',
            'apns_p12' => 'value',
            'apns_p12_password' => 'value',
            'gcm_key' => 'value',
            'android_gcm_sender_id' => 'value',
            'chrome_key' => 'value',
            'safari_apns_p12' => 'value',
            'chrome_web_key' => 'value',
            'safari_apns_p12_password' => 'value',
            'site_name' => 'value',
            'safari_site_origin' => 'value',
            'safari_icon_16_16' => 'value',
            'safari_icon_32_32' => 'value',
            'safari_icon_64_64' => 'value',
            'safari_icon_128_128' => 'value',
            'safari_icon_256_256' => 'value',
            'chrome_web_origin' => 'value',
            'chrome_web_gcm_sender_id' => 'value',
            'chrome_web_default_notification_icon' => 'value',
            'chrome_web_sub_domain' => 'value',
            'organization_id' => 'value',
        ];

        self::assertEquals($expectedData, $this->appResolver->resolve($expectedData));
    }

    public function testResolveWithMissingRequiredValue(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->appResolver->resolve([]);
    }

    public function wrongValueTypesProvider(): iterable
    {
        yield [['name' => 666]];
        yield [['apns_env' => 666]];
        yield [['apns_p12' => 666]];
        yield [['apns_p12_password' => 666]];
        yield [['gcm_key' => 666]];
        yield [['android_gcm_sender_id' => 666]];
        yield [['chrome_key' => 666]];
        yield [['safari_apns_p12' => 666]];
        yield [['chrome_web_key' => 666]];
        yield [['safari_apns_p12_password' => 666]];
        yield [['site_name' => 666]];
        yield [['safari_site_origin' => 666]];
        yield [['safari_icon_16_16' => 666]];
        yield [['safari_icon_32_32' => 666]];
        yield [['safari_icon_64_64' => 666]];
        yield [['safari_icon_128_128' => 666]];
        yield [['safari_icon_256_256' => 666]];
        yield [['chrome_web_origin' => 666]];
        yield [['chrome_web_gcm_sender_id' => 666]];
        yield [['chrome_web_default_notification_icon' => 666]];
        yield [['chrome_web_sub_domain' => 666]];
        yield [['organization_id' => 666]];
    }

    /**
     * @dataProvider wrongValueTypesProvider
     */
    public function testResolveWithWrongValueTypes(array $wrongOption): void
    {
        $this->expectException(InvalidOptionsException::class);

        $requiredOptions = [
            'name' => 'fakeName',
        ];

        $this->appResolver->resolve(array_merge($requiredOptions, $wrongOption));
    }

    public function testResolveWithWrongOption(): void
    {
        $this->expectException(UndefinedOptionsException::class);

        $this->appResolver->resolve(['wrongOption' => 'wrongValue']);
    }
}
