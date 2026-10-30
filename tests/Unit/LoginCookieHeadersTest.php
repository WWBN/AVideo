<?php

namespace Tests\Unit\LoginCookieHeaders;

class HeaderState
{
    public static $headers = [];
    public static $callback;
    public static $removals = 0;
}

function header_register_callback($callback)
{
    HeaderState::$callback = $callback;
    return true;
}

function headers_list()
{
    return HeaderState::$headers;
}

function header_remove($name)
{
    HeaderState::$removals++;
    HeaderState::$headers = array_values(array_filter(HeaderState::$headers, function ($header) use ($name) {
        return stripos($header, $name . ':') !== 0;
    }));
}

function header($value, $replace = true)
{
    HeaderState::$headers[] = $value;
}

class LoginCookieHeadersTest extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        HeaderState::$headers = [];
        HeaderState::$callback = null;
        HeaderState::$removals = 0;
        $source = file_get_contents(\APP_ROOT . '/objects/login.json.php');
        $bootstrap = strpos($source, 'require_once __DIR__');
        $this->assertNotFalse($bootstrap);
        // Execute only the real response callback, without loading the application or database.
        eval('namespace ' . __NAMESPACE__ . ';' . substr($source, strlen('<?php'), $bootstrap - strlen('<?php')));
        $this->assertIsCallable(HeaderState::$callback);
    }

    public function testFinalCookiesPreserveScopesDeletionSecurityAndOrder()
    {
        HeaderState::$headers = [
            'Content-Type: application/json',
            'Set-Cookie: sid=old; Path=/; Secure; HttpOnly; SameSite=None',
            'Set-Cookie: sid=deleted; Path=/; Max-Age=0',
            'Set-Cookie: sid=final; Path=/; Secure; HttpOnly; SameSite=None',
            'Set-Cookie: sid=other-path; Path=/objects; Secure; HttpOnly',
            'Set-Cookie: sid=domain-old; Domain=.example.test; Path=/; Secure',
            'Set-Cookie: sid=domain-final; Domain=EXAMPLE.TEST; Path=/; Secure; HttpOnly',
            'Set-Cookie: sid=subdomain; Domain=sub.example.test; Path=/; Secure',
            'Set-Cookie: SID=uppercase; Path=/; Secure',
            'Set-Cookie: sid=partitioned; Path=/; Secure; Partitioned',
            'Set-Cookie: sid=default-path; Secure',
            'Set-Cookie: expired=old; Path=/',
            'Set-Cookie: expired=gone; Path=/; Max-Age=0; HttpOnly',
            'X-Test-Preserved: yes',
        ];
        call_user_func(HeaderState::$callback);
        $this->assertSame([
            'Content-Type: application/json',
            'X-Test-Preserved: yes',
            'Set-Cookie: sid=final; Path=/; Secure; HttpOnly; SameSite=None',
            'Set-Cookie: sid=other-path; Path=/objects; Secure; HttpOnly',
            'Set-Cookie: sid=domain-final; Domain=EXAMPLE.TEST; Path=/; Secure; HttpOnly',
            'Set-Cookie: sid=subdomain; Domain=sub.example.test; Path=/; Secure',
            'Set-Cookie: SID=uppercase; Path=/; Secure',
            'Set-Cookie: sid=partitioned; Path=/; Secure; Partitioned',
            'Set-Cookie: sid=default-path; Secure',
            'Set-Cookie: expired=gone; Path=/; Max-Age=0; HttpOnly',
        ], HeaderState::$headers);
        $this->assertSame(1, HeaderState::$removals);
    }

    public function testUniqueCookiesAndUnrelatedHeadersAreNotRewritten()
    {
        $headers = [
            'Content-Type: application/json',
            'Set-Cookie: credentials=synthetic; Path=/; Secure; HttpOnly; SameSite=None',
            'X-Test-Preserved: yes',
            'Set-Cookie: device=synthetic; Path=/; Secure; HttpOnly',
        ];
        HeaderState::$headers = $headers;
        call_user_func(HeaderState::$callback);
        $this->assertSame($headers, HeaderState::$headers);
        $this->assertSame(0, HeaderState::$removals);
    }

    public function testRepeatedSessionResetsKeepOnlyFinalSessionCookie()
    {
        for ($index = 0; $index < 120; $index++) {
            HeaderState::$headers[] = 'Set-Cookie: session=synthetic-' . $index . '; Path=/; Secure; HttpOnly; SameSite=None';
        }
        $this->assertGreaterThan(8192, strlen(implode("\r\n", HeaderState::$headers)));
        call_user_func(HeaderState::$callback);
        $this->assertSame([
            'Set-Cookie: session=synthetic-119; Path=/; Secure; HttpOnly; SameSite=None',
        ], HeaderState::$headers);
    }
}
