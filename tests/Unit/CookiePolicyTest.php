<?php

namespace Tests\Unit;

use Tests\TestCase;

class CookiePolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unset(
            $_SERVER['HTTPS'],
            $_SERVER['REQUEST_SCHEME'],
            $_SERVER['HTTP_X_FORWARDED_PROTO'],
            $_SERVER['HTTP_X_FORWARDED_SSL'],
            $_SERVER['HTTP_FRONT_END_HTTPS'],
            $_SERVER['HTTP_CF_VISITOR'],
            $_SERVER['SERVER_PORT'],
            $_SERVER['HTTP_HOST']
        );

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
        $this->assertSame('Example.com', \_getCookieRequestDomain('www.Example.com:8443'));
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
    public function testGetCookieDeleteTargetsListEachEmittedScopeOnce()
    {
        if (!function_exists('getDomain')) {
            eval('function getDomain(){ return "www.example.com"; }');
        }

        // "www." and ".example.com" variants are normalized to the same Domain attribute by
        // _setcookieInternal(), so listing them only repeated identical Set-Cookie headers.
        $this->assertSame(['example.com'], \_getCookieTargetDomains());
        $this->assertSame([
            ['path' => '/', 'domain' => null],
            ['path' => '/', 'domain' => 'example.com'],
        ], \_getCookieDeleteTargets());
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

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSessionStartUsesBrowserSessionCookie()
    {
        if (!class_exists('AVideoConf')) {
            eval('class AVideoConf { public function getSession_timeout() { return 3600; } }');
        }
        if (!function_exists('getDomain')) {
            eval('function getDomain() { return "example.com"; }');
        }

        global $_session_start_preload, $global;
        unset($_session_start_preload);
        $global['systemRootPath'] = \APP_ROOT . '/';

        \session_start_preload();

        $params = session_get_cookie_params();
        $this->assertSame(0, $params['lifetime']);
        $this->assertSame('3600', ini_get('session.gc_maxlifetime'));
    }

    /**
     * @test
     */
    public function testRegeneratedSessionCookiesRemainBrowserScoped()
    {
        $source = file_get_contents(\APP_ROOT . '/objects/functionsPHP.php');

        $this->assertStringContainsString("foreach (array_unique(['PHPSESSID', session_name()]) as \$cookieName)", $source);
        $this->assertStringContainsString('_resetSessionCookie($cookieName, session_id())', $source);
    }
}
