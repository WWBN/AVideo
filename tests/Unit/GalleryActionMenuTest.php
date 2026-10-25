<?php

namespace Tests\GalleryActionMenu;

use PHPUnit\Framework\TestCase;

class User
{
    public static function getNameIdentificationById($id) { return 'Channel'; }
    public static function getEmailVerifiedIcon($id) { return ''; }
}

class Video
{
    public static function getVideoImagewithHoverAnimationFromVideosId($video, ...$options) { return '<div>Thumbnail</div>'; }
}

class CustomizeUser
{
    public static function canDownloadVideosFromVideo($id) { return false; }
}

class AVideoPlugin
{
    public static $echoed = '', $returned = '', $calls = 0;

    public static function getGalleryActionButton($id)
    {
        self::$calls++;
        echo self::$echoed;
        return self::$returned;
    }
}

function __($text) { return $text; }
function getGalleryColsCSSClass(...$columns) { return 'col-sm-4'; }
function getLabelTags($video) {}

class GalleryActionMenuTest extends TestCase
{
    private $previousGlobals;

    public static function setUpBeforeClass(): void
    {
        // Load the real renderer and HTML helpers without bootstrapping a site/database.
        foreach (['strip_specific_tags', 'isHTMLEmpty'] as $name) {
            self::loadFunction('/objects/functions.php', $name);
        }
        self::loadFunction('/plugin/Gallery/functions.php', 'createGallerySectionVideo');
    }

    private static function loadFunction($path, $name)
    {
        $source = file_get_contents(dirname(__DIR__, 2) . $path);
        $start = strpos($source, "\nfunction {$name}(");
        if ($start === false) {
            throw new \RuntimeException("Function {$name} not found");
        }
        $end = strpos($source, "\nfunction ", $start + 1);
        if ($end === false) {
            throw new \RuntimeException("End of function {$name} not found");
        }
        eval('namespace ' . __NAMESPACE__ . '; ' . substr($source, $start, $end - $start));
    }

    protected function setUp(): void
    {
        $this->previousGlobals = [];
        foreach (['advancedCustom', '_lastCanDownloadVideosFromVideoReason'] as $key) {
            $this->previousGlobals[$key] = [array_key_exists($key, $GLOBALS), $GLOBALS[$key] ?? null];
        }
        $GLOBALS['advancedCustom'] = (object) [
            'showEllipsisMenuOnVideoItem' => true,
            'doNotDisplayViews' => true,
        ];
        $GLOBALS['_lastCanDownloadVideosFromVideoReason'] = '';
        AVideoPlugin::$echoed = AVideoPlugin::$returned = '';
        AVideoPlugin::$calls = 0;
    }

    protected function tearDown(): void
    {
        foreach ($this->previousGlobals as $key => [$existed, $value]) {
            if ($existed) {
                $GLOBALS[$key] = $value;
            } else {
                unset($GLOBALS[$key]);
            }
        }
    }

    private function render($details = true)
    {
        ob_start();
        try {
            createGallerySectionVideo(['id' => 42, 'users_id' => 7], true, 0, 0, 0, 0, $details);
            return ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }

    public static function emptyActions(): array
    {
        return [
            'no output' => ['', ''],
            'whitespace' => [" \n\t", ''],
            'trailer diagnostic comment' => ['<!-- invalid trailer URL -->', ''],
            'empty playlist wrapper' => ['<div><!-- no actions --><span></span></div>', ''],
            'returned comment' => ['', '<!-- no actions -->'],
        ];
    }

    /** @dataProvider emptyActions */
    public function testNoOptionsControlIsRenderedWithoutActions($echoed, $returned)
    {
        AVideoPlugin::$echoed = $echoed;
        AVideoPlugin::$returned = $returned;
        $html = $this->render();
        $this->assertStringNotContainsString('aria-label="Options"', $html);
        $this->assertStringNotContainsString('videoButtonOptions', $html);
        $this->assertSame(1, AVideoPlugin::$calls);
    }

    public function testEchoedAndReturnedActionsArePreservedOnce()
    {
        AVideoPlugin::$echoed = '<button>Echoed action</button>';
        AVideoPlugin::$returned = '<a href="/action">Returned action</a>';
        $html = $this->render();
        $this->assertStringContainsString('aria-label="Options"', $html);
        $this->assertSame(1, substr_count($html, AVideoPlugin::$echoed));
        $this->assertSame(1, substr_count($html, AVideoPlugin::$returned));
        $this->assertLessThan(strpos($html, AVideoPlugin::$returned), strpos($html, AVideoPlugin::$echoed));
        $this->assertSame(1, AVideoPlugin::$calls);
    }

    public function testIconOnlyActionsRemainAvailable()
    {
        AVideoPlugin::$returned = '<button aria-label="Action"><i class="fa fa-play"></i></button>';
        $html = $this->render();
        $this->assertStringContainsString('aria-label="Options"', $html);
        $this->assertStringContainsString(AVideoPlugin::$returned, $html);
    }

    public function testInlineActionsArePreservedWhenEllipsisIsDisabled()
    {
        $GLOBALS['advancedCustom']->showEllipsisMenuOnVideoItem = false;
        AVideoPlugin::$echoed = '<button>Inline action</button>';
        $html = $this->render();
        $this->assertStringNotContainsString('aria-label="Options"', $html);
        $this->assertSame(1, substr_count($html, AVideoPlugin::$echoed));
        $this->assertSame(1, AVideoPlugin::$calls);
    }

    public function testCardsWithoutDetailsDoNotInvokeActionHooks()
    {
        $this->assertStringNotContainsString('aria-label="Options"', $this->render(false));
        $this->assertSame(0, AVideoPlugin::$calls);
    }
}
