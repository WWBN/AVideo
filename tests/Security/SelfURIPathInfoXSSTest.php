<?php

namespace Tests\SelfURIPathInfo;

use PHPUnit\Framework\TestCase;

function isAVideoMobileApp()
{
    return false;
}

function _session_start()
{
}

/**
 * GHSA-qmxq-pxm6-q5w8: getSelfURI() is built from $_SERVER['PHP_SELF'], which carries PATH_INFO already
 * decoded by the web server (/view/index.php/x%3C/script%3E...). Callers print it in a JS string
 * (view/include/navbarLang.php), an href (Layout::getLangsSelect()), a CSS comment (MobileYPT) and more,
 * so unsafe delimiters are percent-encoded while existing percent escapes stay the same. CSS comments
 * must not interpolate the URL: an asterisk followed by a slash would still terminate the comment.
 */
class SelfURIPathInfoXSSTest extends TestCase
{
    private $savedGlobals;

    public static function setUpBeforeClass(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
        foreach (['getSelfURI', 'fixTestURL', 'addQueryStringParameter', 'removeQueryStringParameter', 'fixURLQuery',
            'isFirstPage', 'isSameVideoAsSelfURI', 'URLsAreSameVideo', 'getVideoIDFromURL'] as $name) {
            preg_match('/function ' . $name . '\(.*?\n\}/s', $source, $matches);
            eval('namespace ' . __NAMESPACE__ . '; ' . $matches[0]);
        }
    }

    protected function setUp(): void
    {
        global $global;
        foreach (['_SERVER', '_REQUEST', '_GET', '_SESSION', 'global', 'isFirstPage'] as $name) {
            $this->savedGlobals[$name] = [array_key_exists($name, $GLOBALS), $GLOBALS[$name] ?? null];
        }
        $global['webSiteRootURL'] = 'https://example.test/';
        $_SERVER['HTTP_HOST'] = 'example.test';
        $_SERVER['QUERY_STRING'] = '';
        $_REQUEST = $_GET = $_SESSION = [];
        $GLOBALS['isFirstPage'] = false;
    }

    protected function tearDown(): void
    {
        foreach ($this->savedGlobals as $name => [$existed, $value]) {
            if ($existed) {
                $GLOBALS[$name] = $value;
            } else {
                unset($GLOBALS[$name]);
            }
        }
    }

    private static function selfURI($phpSelf, $queryString = '')
    {
        $_SERVER['PHP_SELF'] = $phpSelf;
        $_SERVER['QUERY_STRING'] = $queryString;
        return getSelfURI();
    }

    public function testDecodedPathInfoCannotBreakOutOfHtmlJsOrCss()
    {
        $paths = [
            '/view/index.php/x</script><svg onload=alert(1)>',
            '/view/about.php/x" autofocus onfocus=alert(1) x="',
            "/view/channel.php/x';alert(1);//",
            '/view/index.php/x</style><svg onload=alert(1)>',
            "/view/index.php/x\\\n`{}|^",
        ];
        foreach ($paths as $path) {
            $url = self::selfURI($path, 'a=1');
            $this->assertDoesNotMatchRegularExpression('/[\x00-\x20"\'<>\\\\^`{|}\x7F]/', $url, $path);
            $this->assertStringStartsWith('https://example.test/view/', $url);
            $this->assertSame(str_replace('index.php', '', $path), rawurldecode(substr($url, strlen('https://example.test'), -strlen('?a=1'))));
        }
        $this->assertSame('https://example.test/view//x%3C/script%3E%3Csvg%20onload=alert(1)%3E', self::selfURI($paths[0]));
    }

    public function testLegitimateURLsAreUnchanged()
    {
        $this->assertSame('https://example.test/view/', self::selfURI('/view/index.php'));
        $this->assertSame('https://example.test/view/channel.php?channelName=abc&current=2', self::selfURI('/view/channel.php', 'channelName=abc&current=2'));
        $this->assertSame('https://example.test/view/?search=caf%C3%A9+%22x%22&tags_id=3', self::selfURI('/view/index.php', 'search=caf%C3%A9+%22x%22&tags_id=3'));
        $this->assertSame('https://example.test/video/12', self::selfURI('/view/modeYoutube.php', 'v=12'));
        // IIS (web.config) routes missing files through index.php/{path}: that PATH_INFO is kept.
        $this->assertSame('https://example.test//channel/abc', self::selfURI('/index.php/channel/abc'));
        $this->assertSame('https://example.test/plugin/Live/view/x.php?&&b=2',self::selfURI('/plugin/Live/view/x.php', 'error=a&inMainIframe=1&b=2'));
    }

    public function testRawQueryStringIsEncodedToo()
    {
        $url = self::selfURI('/view/index.php', "x=\"';</script>");
        $this->assertSame('https://example.test/view/?x=%22%27;%3C/script%3E', $url);
    }

    public function testEachASCIIByteUsesTheIntendedEncoding()
    {
        $delimiters = ['"', "'", '<', '>', '\\', '^', '`', '{', '|', '}'];
        for ($byte = 0; $byte < 128; $byte++) {
            $character = chr($byte);
            $expected = ($byte <= 32 || $byte === 127 || in_array($character, $delimiters, true))
                ? rawurlencode($character) : $character;
            $this->assertSame('https://example.test/view/?x=' . $expected . 'z', self::selfURI('/view/index.php', 'x=' . $character . 'z'), 'ASCII byte ' . $byte);
        }
    }

    public function testMobileHeadCannotImportCSSFromDecodedPathInfo()
    {
        $_SERVER['PHP_SELF'] = '/view/index.php/*/@import/**/url(data:text/css,body%7Bcolor:red%7D);/*';
        $_SESSION['bgColor'] = '0000000000abcdef';
        $template = file_get_contents(dirname(__DIR__, 2) . '/plugin/MobileYPT/head.php');
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; ?>' . $template);
            $html = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        $cssWithoutComments = preg_replace('~/\*.*?\*/~s', '', $html);
        $this->assertStringNotContainsString('@import', $cssWithoutComments);
        $this->assertStringContainsString('background-color: #abcdef !important;', $html);
    }

    public function testRawQueryValuesSurviveLanguagePaginationAndNestedRedirects()
    {
        $url = self::selfURI('/view/channel.php', "search=O'Reilly media&token=a%2Bb%252F&current=2");
        $this->assertSame('https://example.test/view/channel.php?search=O%27Reilly%20media&token=a%2Bb%252F&current=2', $url);
        $languageURL = addQueryStringParameter($url, 'lang', 'pt_BR');
        $pageURL = addQueryStringParameter($languageURL, 'current', 3);
        parse_str(parse_url($pageURL, PHP_URL_QUERY), $query);
        $this->assertSame(['search' => "O'Reilly media", 'token' => 'a+b%2F', 'lang' => 'pt_BR', 'current' => '3'], $query);

        parse_str('redirectUri=' . urlencode($url), $redirect);
        $this->assertSame($url, $redirect['redirectUri']);
        $this->assertSame($url, json_decode(json_encode($url)));
        $this->assertSame($url, self::selfURI('/view/channel.php', 'search=O%27Reilly%20media&token=a%2Bb%252F&current=2'));
    }

    public function testPathDelimitersRemainUsableForVideoIdentityAndFirstPage()
    {
        self::selfURI('/view/index.php');
        $this->assertTrue(isFirstPage());
        $url = self::selfURI("/index.php/video/12/O'Reilly media");
        $this->assertSame('https://example.test//video/12/O%27Reilly%20media', $url);
        $this->assertSame(12, getVideoIDFromURL($url));
        $this->assertTrue(isSameVideoAsSelfURI('https://example.test/video/12/another-title'));
        $this->assertFalse(isFirstPage());
    }

    public function testMobileTestURLRewritePreservesEncodedPathAndPort()
    {
        global $global;
        $global['webSiteRootURL'] = 'https://vlu.me/';
        $_SERVER['HTTP_HOST'] = 'vlu.me:8443';
        $_REQUEST['isAVideoMobileApp'] = 1;
        $this->assertSame('http://192.168.0.2:8443/view//O%27Reilly?x=%2527', self::selfURI("/view/index.php/O'Reilly", 'x=%2527'));
    }
}
