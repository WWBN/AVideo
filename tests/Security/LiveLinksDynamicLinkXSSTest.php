<?php

namespace Tests\LiveLinksDynamicLink;

use PHPUnit\Framework\TestCase;

class AVideoPlugin { public static function loadPlugin($name) {} }
class LiveLinks
{
    public static function getSourceLink($id) { return false; }
    public static function getImage($id) { return 'https://example.test/poster.jpg'; }
}
class Video { public static $iframeAllowAttributes = 'allow="autoplay; fullscreen"'; }
class PlayerSkins
{
    public static function isYoutubeIntegrationEnabled() { return true; }
    public static function getPlaysinline() { return 'playsinline'; }
}
function isValidM3U8Link($url, ...$options) { return substr($url, -5) === '.m3u8'; }
function parseVideos($url) { return $url; }
function addQueryStringParameter($url, $name, $value) { return $url; }
function getLiveUsersLabelHTML() { return ''; }

/**
 * GHSA-5ww9-655v-vqr4: play.php?url= puts the raw request URL in the dynamic link hash, and
 * liveVideo.php rendered it (id <= 0) unescaped in an HTML comment, an iframe src and the
 * YouTube tech source. The dynamic link must go through isValidURL() before it is rendered.
 */
class LiveLinksDynamicLinkXSSTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!function_exists(__NAMESPACE__ . '\isValidURL')) {
            $source = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
            preg_match('/function isValidURL\(\$url\)\s*\{.*?\n\}/s', $source, $matches);
            eval('namespace ' . __NAMESPACE__ . '; ' . $matches[0]);
        }
    }

    private function render($link)
    {
        $savedGet = $_GET;
        $savedRequest = $_REQUEST;
        try {
            $_GET = [];
            $_REQUEST = ['embed' => 1];
            $t = ['id' => -1, 'users_id' => 7, 'title' => 'Live', 'link' => $link];
            $video = ['id' => 0];
            $config = new class { public function getAutoplay() { return false; } };
            $template = file_get_contents(dirname(__DIR__, 2) . '/plugin/LiveLinks/view/liveVideo.php');
            ob_start();
            eval('namespace ' . __NAMESPACE__ . '; ?>' . $template);
            return [ob_get_clean(), $video['videoLink']];
        } finally {
            $_GET = $savedGet;
            $_REQUEST = $savedRequest;
        }
    }

    public function testMarkupInDynamicLinkIsNotRendered()
    {
        [$html, $videoLink] = $this->render('http://x/--><h1>INJECTED</h1><!--');
        $this->assertStringNotContainsString('<h1>', $html);
        $this->assertSame('', $videoLink);
    }

    public function testQuoteInDynamicYouTubeLinkDoesNotReachThePlayerSource()
    {
        [, $videoLink] = $this->render('https://www.youtube.com/watch?v=a"x');
        $this->assertSame('', $videoLink);
    }

    public function testValidDynamicLinksStillPlay()
    {
        [$html, $videoLink] = $this->render('https://media.example.test/live.m3u8');
        $this->assertSame('https://media.example.test/live.m3u8', $videoLink);
        $this->assertStringContainsString('src="https://media.example.test/live.m3u8" type="application/x-mpegURL"', $html);

        [$html, $videoLink] = $this->render('https://vimeo.com/1234567?h=ab&autoplay=1');
        $this->assertSame('https://vimeo.com/1234567?h=ab&autoplay=1', $videoLink);
        $this->assertStringContainsString('<iframe', $html);
    }
}
