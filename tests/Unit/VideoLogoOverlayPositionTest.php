<?php

namespace Tests\Unit\VideoLogoOverlayFixtures;

class State
{
    public static $obj;
    public static $isVideo = true;
}

function getCDN()
{
    return 'https://cdn.example/';
}

function isAVideoMobileApp()
{
    return false;
}

function isVideo()
{
    return State::$isVideo;
}

function isLiveLink() { return false; }
function isLive() { return false; }
function getVideos_id() { return 12; }

class Video
{
    public static function getVideoLight($id) { return ['users_id' => 7]; }
}

class User
{
    public static function getPhoto($id) { return 'https://channel.example/photo.png'; }
    public static function getChannelLink($id) { return 'https://channel.example/'; }
}

class PlayerSkins
{
    public static $js;

    public static function addOnPlayerReady($js)
    {
        self::$js = $js;
    }
}

class stdClass extends \stdClass
{
}

class AVideoPlugin
{
    public static function getObjectData($name)
    {
        return State::$obj;
    }
}

class PluginTags
{
    public static $FREE = 'free';
}

// Runs the production class against the stubs above (no PluginAbstract, no configuration.php).
$videoLogoOverlaySource = file_get_contents(dirname(__DIR__, 2) . '/plugin/VideoLogoOverlay/VideoLogoOverlay.php');
$videoLogoOverlaySource = preg_replace('/^<\?php\s*global \$global;\s*require_once [^;]+;/', '', $videoLogoOverlaySource);
$videoLogoOverlaySource = str_replace('class VideoLogoOverlay extends PluginAbstract', 'class VideoLogoOverlay', $videoLogoOverlaySource);
eval('namespace ' . __NAMESPACE__ . ';' . $videoLogoOverlaySource);

// Exercise the real URL/cache helpers, including the session cache shared with the editor.
$videoLogoFunctionsSource = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
foreach (['getURL', 'getRelativePath', 'fixTestURL', 'addQueryStringParameter', 'fixURLQuery'] as $videoLogoFunctionName) {
    preg_match('/^function ' . $videoLogoFunctionName . '\(.*?^\}/ms', $videoLogoFunctionsSource, $videoLogoFunctionMatches);
    eval('namespace ' . __NAMESPACE__ . ';' . $videoLogoFunctionMatches[0]);
}

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\VideoLogoOverlayFixtures\State;
use Tests\Unit\VideoLogoOverlayFixtures\VideoLogoOverlay;
use Tests\Unit\VideoLogoOverlayFixtures\PlayerSkins;
use function Tests\Unit\VideoLogoOverlayFixtures\getURL;

/**
 * Issue #10697: the "Edit Logo" editor saved the position label ("Center", "Top Right") instead of
 * its key, getClass() matched nothing and the logo was drawn below the player, hidden by its overflow.
 */
class VideoLogoOverlayPositionTest extends TestCase
{
    private function setStoredPosition($value)
    {
        $obj = (new VideoLogoOverlay())->getEmptyDataObject();
        $obj->position->value = $value;
        State::$obj = $obj;
    }

    public function testNormalizePositionAcceptsKeysAndLabels()
    {
        $this->assertSame('top right', VideoLogoOverlay::normalizePosition('top right'));
        $this->assertSame('top right', VideoLogoOverlay::normalizePosition('Top Right'));
        $this->assertSame('center', VideoLogoOverlay::normalizePosition(' Center '));
        $this->assertSame('bottom left', VideoLogoOverlay::normalizePosition('BOTTOM LEFT'));
    }

    public function testNormalizePositionRejectsUnlistedValues()
    {
        $this->assertSame('', VideoLogoOverlay::normalizePosition('middle'));
        $this->assertSame('', VideoLogoOverlay::normalizePosition(''));
        $this->assertSame('', VideoLogoOverlay::normalizePosition(['top']));
        $this->assertSame('', VideoLogoOverlay::normalizePosition(null));
    }

    public function testAllPositionsKeepTheirClassesAndEditorSelection()
    {
        $editor = file_get_contents(dirname(__DIR__, 2) . '/plugin/VideoLogoOverlay/page/editor.php');
        preg_match('/\$currentPosition = VideoLogoOverlay::normalizePosition.*?\n\s*\}/s', $editor, $matches);
        $this->assertNotEmpty($matches);
        $positions = (new VideoLogoOverlay())->getEmptyDataObject()->position->type;
        foreach ($positions as $positionKey => $positionLabel) {
            foreach ([$positionKey, $positionLabel, ' ' . strtoupper($positionKey) . ' '] as $stored) {
                $this->setStoredPosition($stored);
                $expectedClass = 'VideoLogoOverlay-' . str_replace(' ', '-', $positionLabel);
                $this->assertSame('VideoLogoOverlay ' . $expectedClass, VideoLogoOverlay::getClass());
                $o = State::$obj;
                ob_start();
                try {
                    eval('namespace Tests\\Unit\\VideoLogoOverlayFixtures;' . $matches[0]);
                    $html = ob_get_contents();
                } finally {
                    ob_end_clean();
                }
                $this->assertStringContainsString("<option value='" . $positionKey . "' selected='selected'>" . $positionLabel . '</option>', $html);
                $this->assertSame(1, substr_count($html, "selected='selected'"));
                $this->assertSame(7, substr_count($html, '<option '));
            }
        }
    }

    public function testSaveNormalizesPositionAndBoundsOpacityWithoutResettingOtherSettings()
    {
        $save = file_get_contents(dirname(__DIR__, 2) . '/plugin/VideoLogoOverlay/page/editorSave.php');
        preg_match('/\$position = VideoLogoOverlay::normalizePosition.*?(?=\$o->url)/s', $save, $matches);
        $this->assertNotEmpty($matches);
        $savedPost = $_POST;
        try {
            foreach ([
                [['position' => ' Bottom Left ', 'opacity' => '0'], 'bottom left', 0],
                [['position' => 'top', 'opacity' => '100'], 'top', 100],
                [['position' => 'Top Right', 'opacity' => '150'], 'top right', 100],
                [['position' => 'center', 'opacity' => '-10'], 'center', 0],
                [['position' => 'bottom right', 'opacity' => '42.9'], 'bottom right', 42],
                [['position' => 'invalid', 'opacity' => 'invalid'], 'top left', 37],
                [['position' => ['center'], 'opacity' => ['10']], 'top left', 37],
                [[], 'top left', 37],
            ] as [$post, $expectedPosition, $expectedOpacity]) {
                $o = (new VideoLogoOverlay())->getEmptyDataObject();
                $o->position->value = 'top left';
                $o->opacity = 37;
                $o->url = 'https://channel.example/';
                $_POST = $post;
                eval('namespace Tests\\Unit\\VideoLogoOverlayFixtures;' . $matches[0]);
                $this->assertSame($expectedPosition, $o->position->value);
                $this->assertSame($expectedOpacity, $o->opacity);
                $this->assertTrue($o->useUserChannelImageAsLogo);
                $this->assertSame('https://channel.example/', $o->url);
            }
        } finally {
            $_POST = $savedPost;
        }
    }

    public function testGetClassUsesStoredKey()
    {
        $this->setStoredPosition('top right');
        $this->assertSame('VideoLogoOverlay VideoLogoOverlay-Top-Right', VideoLogoOverlay::getClass());
    }

    public function testGetClassRepairsLabelSavedByOldEditor()
    {
        $this->setStoredPosition('Center');
        $this->assertSame('VideoLogoOverlay VideoLogoOverlay-Center', VideoLogoOverlay::getClass());

        $this->setStoredPosition('Bottom Right');
        $this->assertSame('VideoLogoOverlay VideoLogoOverlay-Bottom-Right', VideoLogoOverlay::getClass());
    }

    public function testEditorSubmitsKeyAndSaveValidatesIt()
    {
        $root = dirname(__DIR__, 2) . '/plugin/VideoLogoOverlay/page/';
        $editor = file_get_contents($root . 'editor.php');
        $save = file_get_contents($root . 'editorSave.php');

        $this->assertStringContainsString('foreach ($o->position->type as $key => $value)', $editor);
        $this->assertStringContainsString("<option value='\" . htmlspecialchars(\$key, ENT_QUOTES)", $editor);
        $this->assertStringContainsString('VideoLogoOverlay::normalizePosition($_POST[\'position\']', $save);
        $this->assertStringNotContainsString('$o->position->value = $_POST[\'position\'];', $save);
    }

    public function testLogoKeepsOriginAfterEditorWarmsCDNCache()
    {
        global $global;
        $savedGlobal = $global;
        $hadSession = isset($_SESSION);
        $savedSession = $_SESSION ?? null;
        $savedRequest = $_REQUEST;
        $root = str_replace('\\', '/', sys_get_temp_dir()) . '/avideo-logo-' . bin2hex(random_bytes(6)) . '/';
        mkdir($root . 'videos', 0700, true);
        $file = $root . 'videos/logoOverlay.png';
        file_put_contents($file, 'logo fixture');
        try {
            $global = ['systemRootPath' => $root, 'webSiteRootURL' => 'https://origin.example/avideo/'];
            $_SESSION = [];
            $_REQUEST = [];
            $this->setStoredPosition('Top Right');
            State::$obj->useUserChannelImageAsLogo = false;
            $this->assertStringStartsWith('https://cdn.example/', getURL('videos/logoOverlay.png'));

            (new VideoLogoOverlay())->getFooterCode();
            $expected = 'https://origin.example/avideo/videos/logoOverlay.png?cache=' . filemtime($file) . '_' . filectime($file);
            $this->assertStringContainsString('src="' . $expected . '"', PlayerSkins::$js);
            $this->assertStringContainsString('VideoLogoOverlay-Top-Right', PlayerSkins::$js);

            touch($file, filemtime($file) + 60);
            clearstatcache(true, $file);
            (new VideoLogoOverlay())->getFooterCode();
            $this->assertStringNotContainsString($expected, PlayerSkins::$js);
            $this->assertStringContainsString('src="https://origin.example/avideo/videos/logoOverlay.png?cache=', PlayerSkins::$js);
        } finally {
            $global = $savedGlobal;
            if ($hadSession) {
                $_SESSION = $savedSession;
            } else {
                unset($_SESSION);
            }
            $_REQUEST = $savedRequest;
            unlink($file);
            rmdir($root . 'videos');
            rmdir($root);
        }
    }

    public function testChannelPhotoStillOverridesSiteLogoAndLink()
    {
        global $global;
        $savedGlobal = $global;
        try {
            $global = ['systemRootPath' => sys_get_temp_dir() . '/', 'webSiteRootURL' => 'https://origin.example/'];
            $this->setStoredPosition('Bottom Left');
            State::$obj->url = 'https://site-link.example/';
            State::$obj->opacity = 0;
            (new VideoLogoOverlay())->getFooterCode();
            $this->assertStringContainsString('src="https://channel.example/photo.png"', PlayerSkins::$js);
            $this->assertStringContainsString('href="https://channel.example/"', PlayerSkins::$js);
            $this->assertStringContainsString('VideoLogoOverlay-Bottom-Left VideoLogoOverlay-User', PlayerSkins::$js);
            $this->assertStringContainsString('opacity: 0;', PlayerSkins::$js);
            $this->assertStringNotContainsString('logoOverlay.png', PlayerSkins::$js);
        } finally {
            $global = $savedGlobal;
        }
    }

    public function testNonVideoPageDoesNotRegisterOverlay()
    {
        State::$isVideo = false;
        PlayerSkins::$js = null;
        try {
            $this->assertSame('', (new VideoLogoOverlay())->getFooterCode());
            $this->assertNull(PlayerSkins::$js);
        } finally {
            State::$isVideo = true;
        }
    }
}
