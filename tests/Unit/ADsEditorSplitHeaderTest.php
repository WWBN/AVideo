<?php

namespace Tests\Unit\ADsEditorHeaderFixtures;

function __($text)
{
    return $text;
}

class ADs
{
    public static function getSplitPositions($type)
    {
        return $type === 'leaderBoardTop' ? ['left' => 'leaderBoardTopLeft', 'right' => 'leaderBoardTopRight'] : [];
    }
}

// Runs the production editor helpers (plugin/ADs/editor.php) against the stubs above.
$editorSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/editor.php');
$editorFunctions = '';
foreach (['getADsEditorSizeText', 'getADsEditorStatus', 'getADsEditorThumb', 'getADsEditorSplitHeader'] as $editorFunction) {
    if (!preg_match('/function ' . $editorFunction . '\(/', $editorSource, $editorMatch, PREG_OFFSET_CAPTURE)) {
        $editorFunctions .= "function {$editorFunction}() { throw new \\RuntimeException('{$editorFunction}() not found'); }\n";
        continue;
    }
    $editorStart = $editorMatch[0][1];
    $editorDepth = 0;
    for ($editorIndex = strpos($editorSource, '{', $editorStart); $editorIndex < strlen($editorSource); $editorIndex++) {
        if ($editorSource[$editorIndex] === '{') {
            $editorDepth++;
        } elseif ($editorSource[$editorIndex] === '}' && --$editorDepth === 0) {
            break;
        }
    }
    $editorFunctions .= substr($editorSource, $editorStart, $editorIndex - $editorStart + 1) . "\n";
}
eval('namespace ' . __NAMESPACE__ . '; ' . $editorFunctions);
unset($editorSource, $editorFunctions, $editorFunction, $editorMatch, $editorStart, $editorDepth, $editorIndex);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

use function Tests\Unit\ADsEditorHeaderFixtures\getADsEditorSplitHeader;

/**
 * The header shared by a 728x90 banner and its halves in the ADs editor: from any of the three tabs the
 * admin can choose the layout and move to the whole banner, the left half or the right half.
 */
class ADsEditorSplitHeaderTest extends TestCase
{
    private function positions($withHalves = true)
    {
        $positions = [
            'leaderBoardTop' => ['type' => 'leaderBoardTop', 'side' => '', 'width' => 728, 'height' => 90, 'images' => 1, 'firstImage' => 'https://example.com/whole.png?cache=1', 'code' => false],
        ];
        if ($withHalves) {
            $positions['leaderBoardTopLeft'] = ['type' => 'leaderBoardTopLeft', 'side' => 'left', 'width' => 364, 'height' => 90, 'images' => 0, 'firstImage' => '', 'code' => true];
            $positions['leaderBoardTopRight'] = ['type' => 'leaderBoardTopRight', 'side' => 'right', 'width' => 364, 'height' => 90, 'images' => 0, 'firstImage' => '', 'code' => false];
        }
        return $positions;
    }

    private function switcherButtons($html)
    {
        preg_match_all('/<button type="button" class="btn (btn-primary|btn-default) ADsSelectPosition ADsSplitSwitcherButton ADsSplitSwitcher-(\w+)" data-type="(\w+)"/', $html, $matches, PREG_SET_ORDER);
        $buttons = [];
        foreach ($matches as $match) {
            $buttons[$match[2]] = ['type' => $match[3], 'current' => $match[1] === 'btn-primary'];
        }
        return $buttons;
    }

    public function testEveryPartCanBeReachedFromALeftHalfTab()
    {
        $html = getADsEditorSplitHeader($this->positions(), 'leaderBoardTop', 'leaderBoardTopLeft', true, false);
        $this->assertSame([
            'whole' => ['type' => 'leaderBoardTop', 'current' => false],
            'left' => ['type' => 'leaderBoardTopLeft', 'current' => true],
            'right' => ['type' => 'leaderBoardTopRight', 'current' => false],
        ], $this->switcherButtons($html));
        $this->assertStringNotContainsString('aria-hidden="true">Left', $html);
        $this->assertStringContainsString('This ad goes on the LEFT half of the banner.', $html);
    }

    public function testTheRightHalfAndTheWholeBannerShareTheSameSwitcher()
    {
        foreach (['leaderBoardTop' => 'whole', 'leaderBoardTopRight' => 'right'] as $current => $part) {
            $buttons = $this->switcherButtons(getADsEditorSplitHeader($this->positions(), 'leaderBoardTop', $current, true, true));
            $this->assertCount(3, $buttons);
            $this->assertTrue($buttons[$part]['current'], $current);
        }
    }

    public function testLayoutRadiosReflectTheSavedSplitOnEveryTab()
    {
        foreach ([false, true] as $enabled) {
            $html = getADsEditorSplitHeader($this->positions(), 'leaderBoardTop', 'leaderBoardTopRight', true, $enabled);
            $this->assertMatchesRegularExpression('/value="single" data-type="leaderBoardTop"' . ($enabled ? '>' : ' checked>') . '/', $html);
            $this->assertMatchesRegularExpression('/value="split" data-type="leaderBoardTop"' . ($enabled ? ' checked>' : '>') . '/', $html);
        }
        // "Turn on" links focus the parent tab's radio
        $this->assertStringContainsString('id="ADsSplitToggleleaderBoardTop"', getADsEditorSplitHeader($this->positions(), 'leaderBoardTop', 'leaderBoardTop', true, false));
    }

    public function testLayoutSizesFollowTheConfiguredBanner()
    {
        $html = getADsEditorSplitHeader($this->positions(), 'leaderBoardTop', 'leaderBoardTop', true, false);
        $this->assertStringContainsString('<small>728 &times; 90 px</small>', $html);
        $this->assertStringContainsString('<small>2 &times; 364 &times; 90 px</small>', $html);

        $positions = $this->positions();
        $positions['leaderBoardTop']['width'] = 970;
        $positions['leaderBoardTop']['height'] = 250;
        $positions['leaderBoardTopLeft']['width'] = 485;
        $positions['leaderBoardTopLeft']['height'] = 250;
        $html = getADsEditorSplitHeader($positions, 'leaderBoardTop', 'leaderBoardTopLeft', true, true);
        $this->assertStringContainsString('<small>970 &times; 250 px</small>', $html);
        $this->assertStringContainsString('<small>2 &times; 485 &times; 250 px</small>', $html);
    }

    public function testCreatorsSeeTheLayoutButCannotChangeItAndHaveNothingToSwitchWhileItIsOff()
    {
        $html = getADsEditorSplitHeader($this->positions(false), 'leaderBoardTop', 'leaderBoardTop', false, false);
        $this->assertStringNotContainsString('ADsSplitModeInput', $html);
        $this->assertStringContainsString('ADsSplitSwitcher hidden', $html);
        $this->assertSame([], $this->switcherButtons($html));
    }

    public function testThumbnailsAreEscapedAndHiddenWithoutImages()
    {
        $positions = $this->positions();
        $positions['leaderBoardTop']['firstImage'] = 'https://example.com/a.png?x="><b>';
        $html = getADsEditorSplitHeader($positions, 'leaderBoardTop', 'leaderBoardTop', true, false);
        $this->assertStringContainsString('src="https://example.com/a.png?x=&quot;&gt;&lt;b&gt;"', $html);
        $this->assertStringContainsString('<img class="ADsThumb hidden" data-type="leaderBoardTopLeft" src=""', $html);
    }

    public function testBannersWithoutHalvesHaveNoHeader()
    {
        $this->assertSame('', getADsEditorSplitHeader(['sideRectangle' => ['type' => 'sideRectangle']], 'sideRectangle', 'sideRectangle', true, false));
    }
}
