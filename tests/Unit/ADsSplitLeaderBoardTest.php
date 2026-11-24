<?php

namespace Tests\Unit\ADsSplitFixtures;

class State
{
    public static $ad;
    public static $mobile = false;
    public static $video = false;
    public static $owner = 0;
    public static $showAds = true;
    public static $admin = false;
    public static $apiImages = [];
    // images[type][users_id] = html
    public static $images = [];
}

class stdClass extends \stdClass
{
}

class User
{
    public static function isAdmin()
    {
        return State::$admin;
    }
}

class AVideoPlugin
{
    public static function getObjectDataIfEnabled($name)
    {
        return State::$ad;
    }

    public static function showAds($videos_id)
    {
        return State::$showAds;
    }
}

function isInfiniteScroll()
{
    return false;
}

function isBot()
{
    return false;
}

function isMobile()
{
    return State::$mobile;
}

function isVideo()
{
    return State::$video;
}

function getVideos_id()
{
    return State::$video ? 10 : 0;
}

function getUsers_idOwnerFromRequest()
{
    return State::$owner;
}

function _empty($value)
{
    return empty($value) || (is_string($value) && in_array(strtolower($value), ['false', 'null'], true));
}

function getAdsDebugTag($adCode, $adsType = '')
{
    return $adCode;
}

function __($text)
{
    return $text;
}

// Runs the production methods against the stubs above; getAdsHTML() is replaced by State::$images.
$adsSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/ADs.php');
$adsMethods = '';
$adsMethodNames = ['getAllAdsPositions', 'getSettingsType', 'getSplitPositions', 'isSplitEnabled', 'isSplitActive', 'getMobileType', 'isAdsMobile',
    'isAdsDebug', 'giveGoogleATimeout', 'addLabel', 'getAdsFromUsersId', 'debug', 'getAdsCode', 'formatPlacement', 'getSplitAdsCode', 'getAdsPositionsInfo', 'getAdsCacheKey', 'getEmptyDataObject'];
foreach ($adsMethodNames as $adsMethodName) {
    if (!preg_match('/(public|private) (?:static )?function ' . $adsMethodName . '\(/', $adsSource, $adsMatch, PREG_OFFSET_CAPTURE)) {
        $adsMethods .= "public static function {$adsMethodName}() { throw new \\RuntimeException('ADs::{$adsMethodName}() not found'); }\n";
        continue;
    }
    $adsStart = $adsMatch[0][1];
    $adsDepth = 0;
    for ($adsIndex = strpos($adsSource, '{', $adsStart); $adsIndex < strlen($adsSource); $adsIndex++) {
        if ($adsSource[$adsIndex] === '{') {
            $adsDepth++;
        } elseif ($adsSource[$adsIndex] === '}' && --$adsDepth === 0) {
            break;
        }
    }
    $adsMethods .= substr($adsSource, $adsStart, $adsIndex - $adsStart + 1) . "\n";
}
preg_match('/const AdsPositions = \[.*?\];/s', $adsSource, $adsPositions);
preg_match('/const AdsSplitPositions = \[.*?\];/s', $adsSource, $adsSplitPositions);
eval('namespace ' . __NAMESPACE__ . ';
class ADs {
    ' . $adsPositions[0] . '
    ' . $adsSplitPositions[0] . '

    public static function addDataObjectHelper($name, $title, $description) {}

    public static function getAdsHTML($type, $is_regular_user = false)
    {
        $users_id = intval($is_regular_user);
        if (empty(State::$images[$type][$users_id])) {
            return false;
        }
        return ["html" => State::$images[$type][$users_id], "paths" => [$type]];
    }

    public static function getAds($type, $is_regular_user = false)
    {
        return State::$apiImages[$type][intval($is_regular_user)] ?? [];
    }

    ' . $adsMethods . '
}');
unset($adsSource, $adsMethods, $adsMethodNames, $adsMethodName, $adsMatch, $adsStart, $adsDepth, $adsIndex, $adsPositions, $adsSplitPositions);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\Unit\ADsSplitFixtures\ADs;
use Tests\Unit\ADsSplitFixtures\State;

/**
 * leaderBoardTopSplit: on desktop the 728x90 leaderBoardTop becomes leaderBoardTopLeft + leaderBoardTopRight,
 * each through the normal getAdsCode() rules; phones keep leaderBoardTopMobile.
 */
class ADsSplitLeaderBoardTest extends TestCase
{
    private $requestBeforeTest;

    protected function setUp(): void
    {
        $this->requestBeforeTest = $_REQUEST;
        $ad = new \stdClass();
        foreach (ADs::getAllAdsPositions() as $value) {
            $type = $value[0];
            $ad->$type = (object) ['type' => 'textarea', 'value' => ''];
            $ad->{"{$type}Label"} = $type;
            $ad->{"{$type}ShowOnVideoPlayerPage"} = true;
            $ad->{"{$type}AllowUserToModify"} = true;
        }
        // split slots have no settings of their own
        unset($ad->leaderBoardTopLeftShowOnVideoPlayerPage, $ad->leaderBoardTopRightShowOnVideoPlayerPage);
        $ad->leaderBoardTopSplit = false;
        State::$ad = $ad;
        State::$admin = false;
        $_REQUEST = [];
        State::$mobile = false;
        State::$video = false;
        State::$owner = 0;
        State::$showAds = true;
        State::$images = [
            'leaderBoardTop' => [0 => '<img id="whole">'],
            'leaderBoardTopMobile' => [0 => '<img id="mobile">'],
            'leaderBoardTopLeft' => [0 => '<img id="left">'],
            'leaderBoardTopRight' => [0 => '<img id="right">'],
        ];
        State::$apiImages = [];
    }

    protected function tearDown(): void
    {
        $_REQUEST = $this->requestBeforeTest;
    }

    public function testSplitOffKeepsTheOriginalBanner()
    {
        $this->assertStringContainsString('<img id="whole">', ADs::getAdsCode('leaderBoardTop'));
    }

    public function testSingleBannerHasOneCaptionAndPreservesItsCreativeExactly()
    {
        $this->assertSame('<div class="ADsPlacement ADsPlacement-banner">'
            . '<div class="ADsPlacementLabel text-muted">Advertisement</div>'
            . '<div class="ADsPlacementContent"><img id="whole"></div></div>', ADs::getAdsCode('leaderBoardTop'));
    }

    public function testSplitBannerHasOneSharedCaptionAndUnwrappedHalves()
    {
        State::$ad->leaderBoardTopSplit = true;
        $html = ADs::getAdsCode('leaderBoardTop');
        $this->assertSame(1, substr_count($html, 'ADsPlacementLabel'));
        $this->assertStringContainsString('ADsPlacement-split', $html);
        $this->assertSame('<img id="left">', ADs::getAdsCode('leaderBoardTopLeft'));
        unset(State::$images['leaderBoardTopLeft'], State::$images['leaderBoardTopRight']);
        $this->assertFalse(ADs::getAdsCode('leaderBoardTop'));
    }

    public function testMobileCreativeUsesItsOwnPlacementWithoutTheDesktopSplit()
    {
        State::$ad->leaderBoardTopSplit = true;
        State::$mobile = true;
        $html = ADs::getAdsCode('leaderBoardTop');
        $this->assertStringContainsString('ADsPlacement-rectangle', $html);
        $this->assertStringNotContainsString('ADsSplitLeaderBoard', $html);
        $this->assertSame(1, substr_count($html, 'ADsPlacementLabel'));
        $this->assertStringContainsString('<img id="mobile">', $html);
    }

    public function testSplitOnShowsLeftThenRight()
    {
        State::$ad->leaderBoardTopSplit = true;
        $html = ADs::getAdsCode('leaderBoardTop');
        $this->assertStringContainsString('class="ADsSplitLeaderBoard"', $html);
        $this->assertStringNotContainsString('id="whole"', $html);
        $this->assertLessThan(strpos($html, 'id="right"'), strpos($html, 'id="left"'));
        $this->assertStringContainsString('ADsSplitSlot-left" data-ads-type="leaderBoardTopLeft"><img id="left">', $html);
        $this->assertStringContainsString('ADsSplitSlot-right" data-ads-type="leaderBoardTopRight"><img id="right">', $html);
    }

    public function testOnlyOneSideKeepsItsPlace()
    {
        State::$ad->leaderBoardTopSplit = true;
        unset(State::$images['leaderBoardTopLeft']);
        $html = ADs::getAdsCode('leaderBoardTop');
        $this->assertStringContainsString('ADsSplitSlot-left" data-ads-type="leaderBoardTopLeft"></div>', $html);
        $this->assertStringContainsString('ADsSplitSlot-right" data-ads-type="leaderBoardTopRight"><img id="right">', $html);
    }

    public function testNothingIsRenderedWithoutAnAd()
    {
        State::$ad->leaderBoardTopSplit = true;
        unset(State::$images['leaderBoardTopLeft'], State::$images['leaderBoardTopRight']);
        $this->assertFalse(ADs::getAdsCode('leaderBoardTop'));
    }

    public function testPhonesKeepTheMobilePosition()
    {
        State::$ad->leaderBoardTopSplit = true;
        State::$mobile = true;
        $this->assertStringContainsString('<img id="mobile">', ADs::getAdsCode('leaderBoardTop'));
    }

    public function testTheVideoPageSettingOfTheBannerAppliesToTheHalves()
    {
        State::$ad->leaderBoardTopSplit = true;
        State::$video = true;
        $this->assertStringContainsString('id="left"', ADs::getAdsCode('leaderBoardTop'));
        State::$ad->leaderBoardTopShowOnVideoPlayerPage = false;
        $this->assertFalse(ADs::getAdsCode('leaderBoardTop'));
        $this->assertFalse(ADs::getAdsCode('leaderBoardTopRight'));
    }

    public function testPaidUsersSeeNoHalves()
    {
        State::$ad->leaderBoardTopSplit = true;
        State::$showAds = false;
        $this->assertFalse(ADs::getAdsCode('leaderBoardTop'));
    }

    public function testCreatorAdsReplaceTheirSideOnly()
    {
        State::$ad->leaderBoardTopSplit = true;
        State::$owner = 7;
        State::$images['leaderBoardTopLeft'][7] = '<img id="creator-left">';
        $html = ADs::getAdsCode('leaderBoardTop');
        $this->assertStringContainsString('id="creator-left"', $html);
        $this->assertStringNotContainsString('id="left"', $html);
        $this->assertStringContainsString('id="right"', $html);
    }

    public function testOtherPositionsAreNotSplit()
    {
        State::$ad->leaderBoardTopSplit = true;
        State::$images['leaderBoardTop2'] = [0 => '<img id="second">'];
        $this->assertStringContainsString('<img id="second">', ADs::getAdsCode('leaderBoardTop2'));
    }

    public function testSplitSlotsAreNotPartOfAdsPositions()
    {
        // the API and other code that lists AdsPositions keep the same positions
        foreach (ADs::AdsPositions as $value) {
            $this->assertNotContains($value[0], ['leaderBoardTopLeft', 'leaderBoardTopRight']);
        }
        $this->assertSame('leaderBoardTop', ADs::getSettingsType('leaderBoardTopRight'));
        $this->assertSame('sideRectangle', ADs::getSettingsType('sideRectangle'));
    }

    public function testJavascriptCodeRendersOnBothSidesAndIsKeptAcrossToggles()
    {
        State::$ad->leaderBoardTopSplit = true;
        unset(State::$images['leaderBoardTopLeft'], State::$images['leaderBoardTopRight']);
        $left = '<script>document.write("LEFT JS")</script>';
        $right = '<script>document.write("RIGHT JS")</script>';
        State::$ad->leaderBoardTopLeft->value = $left;
        State::$ad->leaderBoardTopRight->value = $right;
        $html = ADs::getAdsCode('leaderBoardTop');
        $this->assertStringContainsString('<script doNotSepareteTag ', $html);
        $this->assertLessThan(strpos($html, 'RIGHT JS'), strpos($html, 'LEFT JS'));
        State::$ad->leaderBoardTopSplit = false;
        $this->assertStringContainsString('<img id="whole">', ADs::getAdsCode('leaderBoardTop'));
        State::$ad->leaderBoardTopSplit = true;
        $this->assertSame($html, ADs::getAdsCode('leaderBoardTop'));
        $this->assertSame($left, State::$ad->leaderBoardTopLeft->value);
        $this->assertSame($right, State::$ad->leaderBoardTopRight->value);
    }

    public function testMixedImageAndCodeAndEachMissingSide()
    {
        State::$ad->leaderBoardTopSplit = true;
        unset(State::$images['leaderBoardTopRight']);
        State::$ad->leaderBoardTopRight->value = '<a href="/right">RIGHT CODE</a>';
        $html = ADs::getAdsCode('leaderBoardTop');
        $this->assertStringContainsString('id="left"', $html);
        $this->assertStringContainsString('href="/right"', $html);
        State::$ad->leaderBoardTopRight->value = '';
        $this->assertStringContainsString('data-ads-type="leaderBoardTopRight"></div>', ADs::getAdsCode('leaderBoardTop'));
        unset(State::$images['leaderBoardTopLeft']);
        $this->assertFalse(ADs::getAdsCode('leaderBoardTop'));
    }

    public function testAdsenseCodeUsesTheTimeoutWrapperInsideItsSlot()
    {
        State::$ad->leaderBoardTopSplit = true;
        unset(State::$images['leaderBoardTopLeft']);
        State::$ad->leaderBoardTopLeft->value = '<ins class="adsbygoogle"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
        $html = ADs::getAdsCode('leaderBoardTop');
        $this->assertStringContainsString('startGoogleAd(', $html);
        $this->assertStringNotContainsString('(adsbygoogle = window.adsbygoogle || []).push({});', $html);
        $this->assertStringContainsString('min-width:250px', $html);
        $css = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/ADs.css');
        $this->assertStringContainsString('.ADsSplitSlot [id^="adContainer"]', $css);
        $this->assertStringContainsString('min-width: 0 !important', $css);
    }

    public function testSecondBannerAndRectangleMobileMappingIsIdempotent()
    {
        State::$mobile = true;
        State::$images['leaderBoardTopMobile2'][0] = 'SECOND MOBILE';
        State::$images['sideRectangle'][0] = 'RECTANGLE';
        $this->assertStringContainsString('SECOND MOBILE', ADs::getAdsCode('leaderBoardTop2'));
        $this->assertStringContainsString('SECOND MOBILE', ADs::getAdsCode('leaderBoardTopMobile2'));
        $this->assertStringContainsString('RECTANGLE', ADs::getAdsCode('sideRectangle'));
        foreach (ADs::AdsPositions as $position) {
            $mobile = ADs::getMobileType($position[0]);
            $this->assertContains($mobile, array_column(ADs::AdsPositions, 0));
            $this->assertSame($mobile, ADs::getMobileType($mobile));
        }
    }

    public function testSiteImageFallbackRespectsAdFreeViewers()
    {
        State::$owner = 7;
        State::$showAds = false;
        $this->assertEmpty(ADs::getAdsCode('leaderBoardTop'));
        State::$showAds = true;
        $this->assertStringContainsString('<img id="whole">', ADs::getAdsCode('leaderBoardTop'));
    }

    public function testAllLeaderboardSplitsKeepIndependentSettingsAndData()
    {
        foreach (['leaderBoardTop', 'leaderBoardTop2', 'channelLeaderBoardTop', 'leaderBoardMiddle'] as $parent) {
            State::$images[$parent][0] = 'ORIGINAL ' . $parent;
            $slots = ADs::getSplitPositions($parent);
            $this->assertCount(2, $slots);
            foreach ($slots as $side => $slot) {
                State::$images[$slot][0] = $parent . ' ' . $side;
                $this->assertSame($parent, ADs::getSettingsType($slot));
            }
            State::$ad->{$parent . 'Split'} = false;
            $this->assertStringContainsString('ORIGINAL ' . $parent, ADs::getAdsCode($parent));
            State::$ad->{$parent . 'Split'} = true;
            $html = ADs::getAdsCode($parent);
            $this->assertStringContainsString($parent . ' left', $html);
            $this->assertStringContainsString($parent . ' right', $html);
            State::$ad->{$parent . 'Split'} = false;
            $this->assertStringContainsString('ORIGINAL ' . $parent, ADs::getAdsCode($parent));
            State::$ad->{$parent . 'Split'} = true;
            $this->assertSame($html, ADs::getAdsCode($parent));
        }
    }

    public function testCreatorPermissionAppliesToTheHalvesAtDisplayTime()
    {
        State::$owner = 7;
        State::$ad->leaderBoardTopSplit = true;
        State::$ad->leaderBoardTopAllowUserToModify = false;
        State::$images['leaderBoardTopLeft'][7] = 'CREATOR';
        $html = ADs::getAdsCode('leaderBoardTop');
        $this->assertStringNotContainsString('CREATOR', $html);
        $this->assertStringContainsString('id="left"', $html);
        State::$ad->leaderBoardTopAllowUserToModify = true;
        $this->assertStringContainsString('CREATOR', ADs::getAdsCode('leaderBoardTop'));
    }

    public function testPreviewDeviceOverrideRequiresAdminAndDebug()
    {
        $_REQUEST = ['AdsDevice' => 'mobile', 'AdsDebug' => 1];
        $this->assertFalse(ADs::isAdsMobile());
        State::$admin = true;
        $this->assertTrue(ADs::isAdsMobile());
        unset($_REQUEST['AdsDebug']);
        $this->assertFalse(ADs::isAdsMobile());
        State::$mobile = true;
        $_REQUEST = ['AdsDevice' => 'desktop', 'AdsDebug' => 1];
        $this->assertFalse(ADs::isAdsMobile());
        $_REQUEST['AdsDevice'] = ['mobile'];
        $this->assertTrue(ADs::isAdsMobile());
    }

    public function testShowAdsPositionsDoesNotCutTheAdInsideAHalf()
    {
        // the debug text sits above the ad; a fixed 90px half with overflow hidden cut its bottom
        $css = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/ADs.css');
        $this->assertMatchesRegularExpression('/\.AdsDebug \.ADsSplitSlot\s*\{\s*height:\s*auto;/', $css);
    }

    public function testRealPagePreviewCanBeClosedAndStopsLoading()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/editor.php');
        $js = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/editor.js');
        $this->assertStringContainsString('id="ADsPreviewLoad" aria-expanded="false" aria-controls="ADsPreviewBody"', $source);
        $this->assertStringContainsString('setPreviewOpen(!isPreviewOpen())', $js);
        $this->assertStringContainsString("$('#ADsPreviewFrame').attr('src', 'about:blank')", $js);
    }

    public function testEditorWaitsForTheVisibleTabBeforeCreatingCroppie()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/editor.php');
        $js = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/editor.js');
        $this->assertStringNotContainsString('onclick="restartForm(', $source);
        $this->assertStringContainsString("if ($('#adsTabs' + type).hasClass('active'))", $source);
        $this->assertStringContainsString("'shown.bs.tab'", $js);
        $this->assertStringContainsString("restartForm($(this).data('type'))", $js);
        $this->assertStringContainsString("clearTimeout(<?php echo \$croppie1['createCroppie']; ?>Timeout)", $source);
        $this->assertStringContainsString("<?php echo \$croppie1['restartCroppie']; ?> = restartCroppie", $source);
    }

    public function testApiKeepsItsPositionsAndUsesTheSameMobileMapping()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/API/API.php');
        $start = strpos($source, 'foreach (ADs::AdsPositions as $key => $value)');
        $end = strpos($source, 'return new ApiObject("", false, $array);', $start);
        $loop = substr($source, $start, $end - $start);
        $this->assertSame(2, substr_count($loop, 'ADs::getAds(ADs::getMobileType($type),'));
        $this->assertStringNotContainsString('getAllAdsPositions', $loop);
        $this->assertCount(11, ADs::AdsPositions);
        $this->assertSame(['leaderBoardBigVideo', 'leaderBoardTop', 'leaderBoardTop2', 'channelLeaderBoardTop',
            'leaderBoardMiddle', 'sideRectangle', 'leaderBoardBigVideoMobile', 'leaderBoardTopMobile',
            'leaderBoardTopMobile2', 'channelLeaderBoardTopMobile', 'leaderBoardMiddleMobile'], array_column(ADs::AdsPositions, 0));
        foreach (['label', 'type', 'desktop', 'mobile', 'isValid', 'isGlobal', 'urls'] as $key) {
            $this->assertStringContainsString("'{$key}' =>", $loop);
        }
        State::$apiImages['leaderBoardTopMobile2'][0] = [['imageURL' => '/second.png', 'url' => '/second', 'txt' => ['title' => 'Second']]];
        State::$apiImages['sideRectangle'][7] = [['imageURL' => '/rectangle.png', 'url' => '/rectangle', 'txt' => ['title' => 'Rectangle']]];
        $runLoop = eval('namespace Tests\\Unit\\ADsSplitFixtures; return static function ($ad, $users_id) { $array = ["ads" => []]; ' . $loop . ' return $array; };');
        $response = $runLoop(State::$ad, 7);
        $this->assertCount(11, $response['ads']);
        $byType = array_column($response['ads'], null, 'type');
        $this->assertSame('/second.png', $byType['leaderBoardTop2']['mobile']['urls'][0]['image']);
        $this->assertTrue($byType['leaderBoardTop2']['mobile']['isGlobal']);
        $this->assertSame('/second.png', $byType['leaderBoardTopMobile2']['mobile']['urls'][0]['image']);
        $this->assertSame($byType['sideRectangle']['desktop'], $byType['sideRectangle']['mobile']);
        $this->assertFalse($byType['sideRectangle']['mobile']['isGlobal']);
    }

    public function testPositionDescriptionsAndMapsIncludeAllGeneralizedHalvesAndMobileSlots()
    {
        $info = ADs::getAdsPositionsInfo();
        $this->assertNotEmpty($info['leaderBoardTopMobile2']['map']);
        $this->assertArrayNotHasKey('notDisplayed', $info['leaderBoardTopMobile2']);
        foreach (ADs::AdsSplitPositions as $position) {
            $this->assertArrayHasKey($position[0], $info);
            $this->assertSame($info[$position[2]]['map'], $info[$position[0]]['map']);
            $this->assertSame($info[$position[2]]['page'], $info[$position[0]]['page']);
            $this->assertArrayNotHasKey('mobile', $info[$position[0]]);
        }
    }

    public function testCachedAdFragmentsChangeWhenSettingsImagesOrViewerRulesChange()
    {
        $original = ADs::getAdsCacheKey('leaderBoardTop2');
        $this->assertSame($original, ADs::getAdsCacheKey('leaderBoardTop2'));
        State::$ad->leaderBoardTop2Split = true;
        $split = ADs::getAdsCacheKey('leaderBoardTop2');
        $this->assertNotSame($original, $split);
        State::$ad->leaderBoardTop2Left->value = '<script>renderAd()</script>';
        $code = ADs::getAdsCacheKey('leaderBoardTop2');
        $this->assertNotSame($split, $code);
        State::$apiImages['leaderBoardTop2Left'][0] = [['imageURL' => '/left.png', 'txt' => ['url' => '/one']]];
        $image = ADs::getAdsCacheKey('leaderBoardTop2');
        $this->assertNotSame($code, $image);
        State::$apiImages['leaderBoardTop2Left'][0][0]['txt']['url'] = '/two';
        $metadata = ADs::getAdsCacheKey('leaderBoardTop2');
        $this->assertNotSame($image, $metadata);
        State::$showAds = false;
        $this->assertNotSame($metadata, ADs::getAdsCacheKey('leaderBoardTop2'));
        State::$showAds = true;
        State::$mobile = true;
        $mobile = ADs::getAdsCacheKey('leaderBoardTop2');
        $this->assertNotSame($metadata, $mobile);
        State::$owner = 7;
        $this->assertNotSame($mobile, ADs::getAdsCacheKey('leaderBoardTop2'));
        State::$ad = false;
        $this->assertSame('disabled', ADs::getAdsCacheKey('leaderBoardTop2'));
    }

    public function testFlixIncludesTheAdFingerprintBeforeReadingItsHtmlCache()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/YouPHPFlix2/view/modeFlixCategory.php');
        $fingerprint = strpos($source, "ADs::getAdsCacheKey('leaderBoardTop2')");
        $this->assertNotFalse($fingerprint);
        $this->assertLessThan(strpos($source, 'ObjectYPT::getCache('), $fingerprint);
    }

    public function testAllSplitsDefaultToOffAndKeepTheirOwnDimensions()
    {
        $hadConfig = array_key_exists('config', $GLOBALS);
        $before = $GLOBALS['config'] ?? null;
        $GLOBALS['config'] = new class {
            public function getAdsense() { return ''; }
        };
        try {
            $defaults = (new ADs())->getEmptyDataObject();
            foreach (['leaderBoardTop', 'leaderBoardTop2', 'channelLeaderBoardTop', 'leaderBoardMiddle'] as $parent) {
                $this->assertFalse($defaults->{$parent . 'Split'});
                $this->assertSame(728, $defaults->{$parent . 'Width'});
                foreach (ADs::getSplitPositions($parent) as $slot) {
                    $this->assertSame(364, $defaults->{$slot . 'Width'});
                    $this->assertSame(90, $defaults->{$slot . 'Height'});
                    $this->assertSame('', $defaults->$slot->value);
                    $this->assertFalse(property_exists($defaults, $slot . 'ShowOnVideoPlayerPage'));
                }
            }
        } finally {
            if ($hadConfig) { $GLOBALS['config'] = $before; } else { unset($GLOBALS['config']); }
        }
    }

    public function testStandaloneEmbedsDoNotCallAnUnavailableBootstrapCarousel()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/ADs.php');
        $start = strpos($source, 'public function getFooterCode()');
        $end = strpos($source, 'private static function getAdsHighlightFooterCode()', $start);
        $footer = substr($source, $start, $end - $start);
        $this->assertStringContainsString("if (typeof $.fn.carousel === 'function')", $footer);
    }
}
