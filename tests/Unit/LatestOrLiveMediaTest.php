<?php

namespace Tests\LatestOrLiveMedia;

use PHPUnit\Framework\TestCase;

class Fixture { public static $url; public static $sessionActive = true; }
class AVideoPlugin { public static function loadPlugin($name) {} }
class LiveLinks
{
    public static function getSourceLink($id) { return Fixture::$url; }
    public static function userCanWatch($user, $id) { return true; }
    public static function getImage($id) { return 'https://example.test/poster.jpg'; }
}
class LiveLinksTable
{
    public function __construct($id) {}
    public function getEnd_date() { return date('Y-m-d H:i:s', time() + 3600); }
}
class User { public static function getId() { return 7; } }
class Video { public static $iframeAllowAttributes = 'allow="autoplay; fullscreen"'; }
class PlayerSkins
{
    public static function isYoutubeIntegrationEnabled() { return true; }
    public static function getPlaysinline() { return 'playsinline'; }
}
function convertFromDefaultTimezoneTimeToMyTimezone($date) { return $date; }
function isValidM3U8Link($url, ...$options) { return substr($url, -5) === '.m3u8'; }
function parseVideos($url) { return 'https://player.example.test/embed'; }
function addQueryStringParameter($url, $name, $value) { return $url . '?' . $name . '=' . $value; }
function getLiveUsersLabelHTML() { return ''; }
function forbiddenPage($message) { throw new \RuntimeException($message); }
function isSessionStarted() { return Fixture::$sessionActive; }
function _session_write_close() { Fixture::$sessionActive = false; }
function _session_start() { Fixture::$sessionActive = true; }

class LatestOrLiveMediaTest extends TestCase
{
    private function render($url)
    {
        $savedGet = $_GET;
        $savedRequest = $_REQUEST;
        try {
            Fixture::$url = $url;
            Fixture::$sessionActive = true;
            $_GET = [];
            $_REQUEST = ['embed' => 'original'];
            $video = ['id' => 42, 'link' => $url];
            $isLiveLink = 42;
            $config = new class { public function getAutoplay() { return true; } };
            $global = ['systemRootPath' => dirname(__DIR__, 2) . '/'];
            $source = file_get_contents($global['systemRootPath'] . 'plugin/Live/latestOrLive.php');
            $start = strpos($source, '$liveLinkMediaTag =');
            $end = strpos($source, '//var_dump($liveVideo', $start);
            $source = substr($source, $start, $end - $start);
            $template = file_get_contents($global['systemRootPath'] . 'plugin/LiveLinks/view/liveVideo.php');
            $include = 'include $global[\'systemRootPath\'] . \'plugin/LiveLinks/view/liveVideo.php\';';
            $source = str_replace($include, 'eval(' . var_export('namespace ' . __NAMESPACE__ . '; ?>' . $template, true) . ');', $source);
            eval('namespace ' . __NAMESPACE__ . '; ' . $source);
            $this->assertSame('original', $_REQUEST['embed']);
            $this->assertTrue(Fixture::$sessionActive);
            return [$liveLinkMediaTag, $_GET['isEmbedded'] ?? null];
        } finally {
            $_GET = $savedGet;
            $_REQUEST = $savedRequest;
        }
    }

    public function testYouTubeUsesTheExistingYouTubeTechInsteadOfAnHLSSource()
    {
        [$html, $tech] = $this->render('https://www.youtube.com/watch?v=abcDEF12345');
        $this->assertSame('y', $tech);
        $this->assertStringContainsString('id="mainVideo"', $html);
        $this->assertStringNotContainsString('application/x-mpegURL', $html);
    }

    public function testHLSKeepsItsNativeMediaSource()
    {
        [$html, $tech] = $this->render('https://media.example.test/live.m3u8');
        $this->assertNull($tech);
        $this->assertStringContainsString('src="https://media.example.test/live.m3u8" type="application/x-mpegURL"', $html);
    }

    public function testOtherProvidersUseTheExistingIframeRenderer()
    {
        [$html, $tech] = $this->render('https://vimeo.com/1234567');
        $this->assertSame('e', $tech);
        $this->assertStringContainsString('<iframe', $html);
        $this->assertStringContainsString('https://player.example.test/embed?autoplay=1', $html);
    }
}
