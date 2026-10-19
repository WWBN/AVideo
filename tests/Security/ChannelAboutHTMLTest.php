<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * Any channel owner writes the channel "About" HTML. The TinyMCE image/media buttons insert
 * <img> and player iframes; only iframes of known video players and of this site's own embed
 * pages may survive purifyChannelAboutHTML().
 */
class ChannelAboutHTMLTest extends TestCase
{
    private const SITE = 'https://example.com/';

    protected function setUp(): void
    {
        require_once \APP_ROOT . '/objects/channelAboutHTML.php';
    }

    private function purify($html)
    {
        return purifyChannelAboutHTML($html, self::SITE, sys_get_temp_dir());
    }

    public function testPlayerIframesFromTheMediaButtonAreKept()
    {
        $sources = [
            'https://www.youtube.com/embed/abc123',
            'https://www.youtube-nocookie.com/embed/abc123',
            'https://player.vimeo.com/video/123?title=0&byline=0',
            'https://www.dailymotion.com/embed/video/x7xyz',
            'https://example.com/videoEmbed/12/my-video',
            'https://example.com/vEmbed/.abc_1',
            'https://example.com/channel/MyChannel/videoEmbed/12/my-video',
            'https://example.com/channel/My%20Channel/audioEmbed/.abc_1/my-audio',
            'https://example.com/channel/MyChannel/articleEmbed/12/my-article',
            // cleanURLName() keeps dots, so titles such as "Wait for it..." end up in the embed path
            'https://example.com/videoEmbed/12/wait-for-it...',
            'https://example.com/channel/MyChannel/videoEmbed/.abc_1/version-2..0-released',
        ];
        foreach ($sources as $src) {
            $html = $this->purify('<p><iframe src="' . htmlspecialchars($src) . '" width="560" height="314" allowfullscreen="allowfullscreen"></iframe></p>');
            $this->assertStringContainsString('<iframe allowfullscreen="allowfullscreen" src="', $html, $src);
            $this->assertStringContainsString('width="560"', $html, $src);
        }
    }

    public function testImagesByURLAreKept()
    {
        $html = $this->purify('<p><img src="https://cdn.example.org/banner.png" alt="Banner" width="300" height="100"></p>');
        $this->assertStringContainsString('<img src="https://cdn.example.org/banner.png" alt="Banner" width="300" height="100"', $html);
    }

    public function testOtherIframesAreRemoved()
    {
        $sources = [
            'https://evil.example.net/embed/abc',
            'https://www.youtube.com.evil.example.net/embed/abc',
            'https://evil.example.net/?https://www.youtube.com/embed/abc',
            'javascript:alert(1)',
            'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==',
            'https://example.com/objects/logoff.php',
            'https://example.com/videoEmbed/../logoff',
            'https://example.com/videoEmbed/%2e%2e/logoff',
            'https://example.com/vEmbed/..',
            'https://example.com/channel/MyChannel/video/12/my-video',
            'https://example.com/channel/MyChannel/objects/logoff.php',
            'https://example.com/channel/../videoEmbed/12',
            'https://example.com/channel/%2e%2e/videoEmbed/12',
            'https://example.com/channel/MyChannel/videoEmbed/../../../../objects/logoff.php',
            'https://example.com/channel/MyChannel/videoEmbed/%2e%2e/%2e%2e/objects/logoff.php',
            'https://example.com/channel/MyChannel/videoEmbed/12\\..\\objects/logoff.php',
            'https://example.com/channel/MyChannel/videoEmbed/12/%5c..%5c..%5cobjects/logoff.php',
            'https://example.com/channel/MyChannel/videoEmbed/12/%2f..%2f..%2fobjects/logoff.php',
            'https://example.com/videoEmbed/12/.%2e/.%2E/objects/logoff.php',
            'https://example.com/channel/./videoEmbed/12',
            'https://example.com/videoEmbed/.../logoff',
            'https://example.com/videoEmbed/12/..?x=1',
            'https://example.com.evil.example.net/videoEmbed/1',
            'https://www.youtube.com/watch?v=abc',
        ];
        foreach ($sources as $src) {
            $html = $this->purify('<p>a</p><iframe src="' . htmlspecialchars($src) . '"></iframe>');
            $this->assertStringNotContainsString('<iframe', $html, $src);
        }
    }

    public function testTabsAndNewlinesCannotFormADotSegment()
    {
        // Browsers drop tabs and newlines from URLs, so ".\t." must never reach the page as-is
        $html = $this->purify("<iframe src=\"https://example.com/videoEmbed/12/.\t./.\n./objects/logoff.php\"></iframe>");
        if (strpos($html, '<iframe') !== false) {
            $this->assertDoesNotMatchRegularExpression('~[\t\n\r]|/\.\./~', $html);
        }
        $this->assertTrue(true);
    }

    public function testScriptsAndEventHandlersAreStillRemoved()
    {
        $html = $this->purify('<p onclick="alert(1)">x</p><script>alert(1)</script><img src="x" onerror="alert(1)"><iframe src="https://www.youtube.com/embed/a" onload="alert(1)"></iframe>');
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('onload', $html);
    }

    public function testIframeTextIsNotTurnedIntoATag()
    {
        $html = $this->purify('<p>&lt;iframe src="https://evil.example.net"&gt;</p>');
        $this->assertStringNotContainsString('<iframe', $html);
    }

    public function testEmptySiteURLStillAllowsOnlyThePlayers()
    {
        $html = purifyChannelAboutHTML('<iframe src="https://example.com/videoEmbed/1"></iframe><iframe src="https://www.youtube.com/embed/a"></iframe>', '', sys_get_temp_dir());
        $this->assertSame(1, substr_count($html, '<iframe'));
        $this->assertStringContainsString('youtube.com/embed/a', $html);
    }

    public function testChannelEmbedsWorkInASubdirectory()
    {
        $site = 'https://example.com/avideo/';
        foreach (['https://example.com', '//example.com'] as $origin) {
            $src = $origin . '/avideo/channel/MyChannel/videoEmbed/.abc_1/my-video';
            $html = purifyChannelAboutHTML('<iframe src="' . $src . '"></iframe>', $site, sys_get_temp_dir());
            $this->assertStringContainsString('<iframe', $html, $src);
            $this->assertStringContainsString($src, $html, $src);
        }
        $html = purifyChannelAboutHTML('<iframe src="https://example.com/channel/MyChannel/videoEmbed/12"></iframe>', $site, sys_get_temp_dir());
        $this->assertStringNotContainsString('<iframe', $html);
    }
}
