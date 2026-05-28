<?php

namespace Tests\Unit;

use Tests\TestCase;

class CookiePolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER = [];

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

    /**
     * @test
     */
    public function testCookieRequestDomainNormalization()
    {
        $this->assertSame('example.com', \_getCookieRequestDomain('www.Example.com:8443'));
        $this->assertSame('sub.example.com', \_getCookieRequestDomain('.sub.example.com'));
    }

    /**
     * @test
     * @dataProvider invalidCookieDomainsProvider
     */
    public function testNormalizeCookieDomainRejectsInvalidValues($domain)
    {
        $this->assertNull(\_normalizeCookieDomain($domain));
    }

    /**
     * @return array
     */
    public function invalidCookieDomainsProvider()
    {
        return [
            'empty' => [''],
            'single label host' => ['internalhost'],
            'ipv4' => ['192.168.1.10'],
            'invalid char' => ['exa_mple.com'],
            'leading dash' => ['-bad.example.com'],
            'trailing dash' => ['bad-.example.com'],
        ];
    }

    /**
     * @test
     */
    public function testGetCookieDeleteTargetsIncludesBareAndDotDomains()
    {
        global $global;
        $global['webSiteRootURL'] = 'https://www.example.com/';

        $targets = \_getCookieDeleteTargets();
        $domains = array_column($targets, 'domain');

        $this->assertContains(null, $domains);
        $this->assertContains('example.com', $domains);
        $this->assertContains('.example.com', $domains);
        $this->assertContains('www.example.com', $domains);
        $this->assertContains('.www.example.com', $domains);
    }

    /**
     * @test
     */
    public function testIsCookieSecureDetectsForwardedHttpsHeader()
    {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http, https';
        $this->assertTrue(\_isCookieSecure());
    }

    /**
     * @test
     */
    public function testIsCookieSecureReturnsFalseWithoutSecureSignals()
    {
        $this->assertFalse(\_isCookieSecure());
    }

    /**
     * @test
     */
    public function testCookieOptionsArrayOnlyIncludesValidDomain()
    {
        $config = [
            'lifetime' => 123,
            'path' => '/',
            'domain' => 'www.example.com:443',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        $options = \_getCookieOptionsArray($config);

        $this->assertSame('example.com', $options['domain']);
        $this->assertSame(123, $options['expires']);

        $config['domain'] = 'localhost';
        $options = \_getCookieOptionsArray($config);
        $this->assertArrayNotHasKey('domain', $options);
    }

    /**
     * @test
     */
    public function testLegacySameSitePathIncludesSameSiteAttribute()
    {
        $config = [
            'path' => '/',
            'secure' => false,
            'samesite' => 'Lax',
        ];
        $this->assertSame('/; SameSite=Lax', \_getLegacySameSitePath($config));
    }
}
