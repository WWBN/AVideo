<?php

namespace Tests\Unit;

use Tests\TestCase;

class CookiePolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!function_exists('_normalizeCookieDomain')) {
            require_once \APP_ROOT . '/objects/functionsPHP.php';
        }
    }

    /**
     * @test
     */
    public function testCookieDomainIsOmittedForLocalAndIpHosts()
    {
        $this->assertNull(\_normalizeCookieDomain('localhost'));
        $this->assertNull(\_normalizeCookieDomain('127.0.0.1'));
        $this->assertNull(\_normalizeCookieDomain('[::1]'));
        $this->assertNull(\_normalizeCookieDomain('intranet'));
    }

    /**
     * @test
     */
    public function testCookieDomainIsNormalizedForRegularHosts()
    {
        $this->assertSame('example.com', \_normalizeCookieDomain('www.Example.com:443'));
        $this->assertSame('sub.example.com', \_normalizeCookieDomain('sub.example.com'));
    }

    /**
     * @test
     */
    public function testSessionCookieConfigOmitsInvalidDomainAttribute()
    {
        $ipConfig = \_getSessionCookieParamsConfig(3600, '127.0.0.1');
        $localConfig = \_getSessionCookieParamsConfig(3600, 'localhost');
        $domainConfig = \_getSessionCookieParamsConfig(3600, 'www.example.com');

        $this->assertNull($ipConfig['domain']);
        $this->assertNull($localConfig['domain']);
        $this->assertSame('example.com', $domainConfig['domain']);
    }
}
