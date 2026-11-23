<?php

namespace Tests\VideoLogoOverlayLiveScheduleXSS;

use PHPUnit\Framework\TestCase;

class Live_schedule
{
    public static $receivedId;

    public static function getUsers_idOrCompany($live_schedule_id)
    {
        self::$receivedId = $live_schedule_id;
        return 7;
    }
}

/**
 * GHSA-mh4m-2gxx-cc3x: VideoLogoOverlay::getFooterCode() printed $_REQUEST['live_schedule'] inside a
 * JS comment of the player-ready <script> on public live pages. The value is a live_schedule id, so it
 * is now cast to an integer before being printed or used (the lookup already applied intval()).
 */
class VideoLogoOverlayLiveScheduleXSSTest extends TestCase
{
    private static $block;
    private $savedRequest;

    public static function setUpBeforeClass(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/VideoLogoOverlay/VideoLogoOverlay.php');
        preg_match('/if \(!empty\(\$_REQUEST\[\'live_schedule\'\]\)\) \{(.*?)\n\s*\} else \{/s', $source, $matches);
        self::$block = $matches[1];
    }

    protected function setUp(): void
    {
        $this->savedRequest = $_REQUEST;
        Live_schedule::$receivedId = null;
    }

    protected function tearDown(): void
    {
        $_REQUEST = $this->savedRequest;
    }

    private function runBlock($requestValue)
    {
        $_REQUEST = ['live_schedule' => $requestValue];
        $js = '';
        $users_id = 0;
        eval('namespace ' . __NAMESPACE__ . '; ' . self::$block);
        return [$js, $users_id];
    }

    public function testNumericScheduleIdIsKept(): void
    {
        list($js, $users_id) = $this->runBlock('15');
        $this->assertSame('/* VideoLogoOverlay live schedule 15 */', $js);
        $this->assertSame(15, Live_schedule::$receivedId);
        $this->assertSame(7, $users_id);
    }

    public function testCommentAndScriptBreakoutsAreNotReflected(): void
    {
        foreach (['1*/</script><b>x</b>/*', '1 */ alert(1); /*', '"><b>'] as $value) {
            list($js) = $this->runBlock($value);
            $this->assertMatchesRegularExpression('#^/\* VideoLogoOverlay live schedule \d+ \*/$#', $js, $value);
            $this->assertIsInt(Live_schedule::$receivedId, $value);
        }
    }

    public function testRequestValueIsReadOnlyThroughIntval(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/VideoLogoOverlay/VideoLogoOverlay.php');
        $this->assertSame(2, substr_count($source, "\$_REQUEST['live_schedule']"));
        $this->assertSame(1, substr_count($source, "intval(\$_REQUEST['live_schedule'])"));
    }
}
