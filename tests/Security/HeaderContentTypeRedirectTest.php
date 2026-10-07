<?php

namespace Tests\Security\HeaderContentTypeRedirect;

use PHPUnit\Framework\TestCase;

// Isolate the production helpers so requests can be observed without network access.
function isValidURL($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function isSSRFSafeURL($url)
{
    HeaderContentTypeRedirectTest::$validated[] = $url;
    return parse_url($url, PHP_URL_HOST) === 'public.example';
}

function get_headers($url, $associative, $context)
{
    HeaderContentTypeRedirectTest::$requested[] = $url;
    HeaderContentTypeRedirectTest::$options = stream_context_get_options($context);
    return HeaderContentTypeRedirectTest::$responses[$url] ?? false;
}

function _error_log($message, $level)
{
}

class AVideoLog
{
    public static $SECURITY = 'security';
}

class HeaderContentTypeRedirectTest extends TestCase
{
    public static $validated = [];
    public static $requested = [];
    public static $responses = [];
    public static $options = [];

    public static function setUpBeforeClass(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
        foreach (['getHeaderContentTypeFromURL', 'ssrfResolveRedirectURL', 'ssrfNormalizeRedirectPath'] as $name) {
            $start = strpos($source, 'function ' . $name . '(');
            $end = strpos($source, "\nfunction ", $start + 1);
            eval('namespace ' . __NAMESPACE__ . ';' . substr($source, $start, $end - $start));
        }
    }

    protected function setUp(): void
    {
        self::$validated = self::$requested = self::$responses = self::$options = [];
    }

    public function testSixSafeRedirectsStillReturnFinalContentType(): void
    {
        for ($hop = 0; $hop < 6; $hop++) {
            self::$responses['https://public.example/' . $hop] = [
                'HTTP/1.1 302 Found', 'Location: /' . ($hop + 1), 'Content-Type: text/plain',
            ];
        }
        self::$responses['https://public.example/6'] = ['HTTP/1.1 200 OK', 'content-type: text/html'];

        $this->assertSame('text/html', getHeaderContentTypeFromURL('https://public.example/0'));
        $this->assertCount(7, self::$requested);
        $this->assertSame(self::$requested, self::$validated);
        $this->assertSame(0, self::$options['http']['follow_location']);
    }

    public function testZeroRelativeLocationIsFollowed(): void
    {
        self::$responses['https://public.example/path/start'] = ['HTTP/1.1 302 Found', 'Location: 0'];
        self::$responses['https://public.example/path/0'] = ['HTTP/1.1 200 OK', 'Content-Type: text/html'];

        $this->assertSame('text/html', getHeaderContentTypeFromURL('https://public.example/path/start'));
        $this->assertSame(['https://public.example/path/start', 'https://public.example/path/0'], self::$requested);
    }

    public function testInformationalHeadersDoNotHideTheRedirect(): void
    {
        self::$responses['https://public.example/start'] = [
            'HTTP/1.1 103 Early Hints', 'Link: </style.css>; rel=preload',
            'HTTP/1.1 302 Found', 'Location: https://public.example/final',
        ];
        self::$responses['https://public.example/final'] = ['HTTP/1.1 200 OK', 'Content-Type: text/html'];

        $this->assertSame('text/html', getHeaderContentTypeFromURL('https://public.example/start'));
        $this->assertSame(['https://public.example/start', 'https://public.example/final'], self::$requested);
    }

    /** @dataProvider unsafeLocations */
    public function testUnsafeRedirectIsNeverRequested($location): void
    {
        self::$responses['https://public.example/start'] = ['HTTP/1.1 302 Found', 'Location: /next'];
        self::$responses['https://public.example/next'] = ['HTTP/1.1 302 Found', 'Location: ' . $location];

        $this->assertFalse(getHeaderContentTypeFromURL('https://public.example/start'));
        $this->assertSame(['https://public.example/start', 'https://public.example/next'], self::$requested);
    }

    public static function unsafeLocations(): array
    {
        return [
            ['http://127.0.0.1/internal'],
            ['http://169.254.169.254/latest/meta-data/'],
            ['//192.168.1.1/private'],
            ['file:///etc/passwd'],
        ];
    }

    public function testRedirectLoopStopsAtThePreviousDefaultRequestLimit(): void
    {
        self::$responses['https://public.example/loop'] = ['HTTP/1.1 302 Found', 'Location: /loop'];
        $this->assertFalse(getHeaderContentTypeFromURL('https://public.example/loop'));
        $this->assertCount(20, self::$requested);
    }

    public function testFailedRequestReturnsFalse(): void
    {
        $this->assertFalse(getHeaderContentTypeFromURL('https://public.example/missing'));
    }

    public function testConfiguredContextAndRequestLimitArePreserved(): void
    {
        $default = stream_context_get_default();
        $original = stream_context_get_options($default);
        stream_context_set_option($default, 'http', 'max_redirects', 3);
        stream_context_set_option($default, 'http', 'header', 'X-Fixture: preserved');
        self::$responses['https://public.example/loop'] = ['HTTP/1.1 302 Found', 'Location: /loop'];
        try {
            $this->assertFalse(getHeaderContentTypeFromURL('https://public.example/loop'));
            $this->assertCount(3, self::$requested);
            $this->assertSame('X-Fixture: preserved', self::$options['http']['header']);
            $this->assertSame($original['http']['follow_location'] ?? 1,
                stream_context_get_options($default)['http']['follow_location'] ?? 1);
        } finally {
            stream_context_set_option($default, 'http', 'max_redirects', $original['http']['max_redirects'] ?? 20);
            stream_context_set_option($default, 'http', 'header', $original['http']['header'] ?? '');
        }
    }
}
