<?php

namespace Tests\Security\PlaybackUnlock;

use PHPUnit\Framework\TestCase;

// Execute the production offer lookup without the application/database bootstrap.
$unlockPolicySource = file_get_contents(dirname(__DIR__, 2) . '/objects/PlaybackAccess.php');
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use InvalidArgumentException; use Throwable; use stdClass;' . substr($unlockPolicySource, 5));

class UnlockFixture
{
    public static $rules = [], $enabled = [], $subscriptionPlans = [], $ppvLinks = [], $ppvPlans = [], $ppvObject = null;
    public static $userGroups = [], $visibilityGroups = [];
    public static $listingGet = null, $video = null, $bot = false, $failListing = false;
    public static $pluginAccess = null, $ppvVideoPlans = [], $ownerStatus = 'a';
    public static $pluginChangesRequest = false;
    public static $rentalOption = false, $rentalPlans = [], $purchasedTitle = false;
}
class ObjectYPT
{
    public static function isTableInstalled($table) { return true; }
}
class sqlDAL
{
    public static function readSql($sql, $types = '', $args = [], $fresh = false)
    {
        if (strpos($sql, 'SHOW TABLES') === 0) {
            return (object) ['count' => 1];
        }
        if (strpos($sql, 'SELECT mode, groups_json') === 0) {
            return (object) ['row' => UnlockFixture::$rules[$args[0] . ':' . $args[1]] ?? false];
        }
        if (strpos($sql, 'SELECT categories_id') === 0) {
            return (object) ['row' => ['categories_id' => 0]];
        }
        return (object) ['row' => false];
    }
    public static function fetchAssoc($res) { return $res->row; }
    public static function num_rows($res) { return $res->count; }
    public static function close($res) {}
}
class AVideoPlugin
{
    public static function loadPluginIfEnabled($name) { return in_array($name, UnlockFixture::$enabled, true) ? new \stdClass() : false; }
    public static function getDataObject($name) { return UnlockFixture::$ppvObject ?: new \stdClass(); }
    public static function userCanWatchVideo($user, $video) {
        if (UnlockFixture::$pluginChangesRequest) {
            $_GET['page'] = 1; $_POST['current'] = 1; $_REQUEST['rowCount'] = 1000;
        }
        return UnlockFixture::$pluginAccess ?? empty(UnlockFixture::$visibilityGroups);
    }
}
class SubscriptionPlansTable
{
    public static function getAllActive()
    {
        UnlockFixture::$listingGet = $_GET;
        if (UnlockFixture::$failListing) { throw new \RuntimeException('Listing failed'); }
        return UnlockFixture::$subscriptionPlans;
    }
}
class Subscription
{
    public static function getBuyURL($videos_id = 0) { return 'https://fixture/plugin/Subscription/showPlans.php?videos_id=' . $videos_id; }
}
class Ppv_plans_has_users_groups
{
    public static function getAllPlansFromUserGroups($groups)
    {
        return array_values(array_filter(UnlockFixture::$ppvLinks, function ($link) use ($groups) {
            return in_array($link['users_groups_id'], $groups);
        }));
    }
    public static function getAllFromPPV($ppv_plans_id)
    {
        return array_values(array_filter(UnlockFixture::$ppvLinks, function ($link) use ($ppv_plans_id) {
            return $link['ppv_plans_id'] == $ppv_plans_id;
        }));
    }
}
class PPV_Plans
{
    public static function getFromDb($id) { return UnlockFixture::$ppvPlans[$id] ?? false; }
}
class PayPerView
{
    public static function getBuyURL($videos_id = 0) { return 'https://fixture/custom-ppv-checkout'; }
    public static function getAllPlansFromVideo($video, $activeOnly = true) { return UnlockFixture::$ppvVideoPlans; }
    public static function getRentalPlansOfVideo($video) { return UnlockFixture::$rentalPlans; }
    public static function canRentGroupRestrictedVideo($video, $plan = null)
    {
        $ids = array_map('intval', array_column(UnlockFixture::$rentalPlans, 'id'));
        return UnlockFixture::$rentalOption && ($plan === null ? !empty($ids) : in_array(intval($plan), $ids, true));
    }
    public static function purchaseSatisfiesPlaybackGroupRule($user, $video) { return UnlockFixture::$rentalOption && UnlockFixture::$purchasedTitle; }
}
class Video
{
    public static function getVideoLight($id) { return UnlockFixture::$video; }
    public static function getViewableStatus($showUnlisted = false) { return ['a', 'k', 'f', 'u', 's']; }
}
class User
{
    public static function getId() { return 82; }
    public static function isAdmin($id = null) { return false; }
    public static function getUserFromID($id) { return ['id' => $id, 'status' => UnlockFixture::$ownerStatus]; }
}
class UserGroups
{
    public static function getVideoGroups($id) { return array_map(function ($group) { return ['users_groups_id' => $group]; }, UnlockFixture::$visibilityGroups); }
    public static function getUserGroups($id) { return array_map(function ($group) { return ['id' => $group]; }, UnlockFixture::$userGroups); }
    public static function getVideosAndCategoriesUserGroups($id) { return array_map(function ($group) { return ['id' => $group]; }, UnlockFixture::$visibilityGroups); }
}
class AVideoLog { public static $ERROR = 1; }
function _error_log($message, $level = null) {}
function __($text) { return $text; }
function class_exists($name) { return \class_exists(__NAMESPACE__ . '\\' . $name) || \class_exists($name); }
function method_exists($class, $method) { return \method_exists(__NAMESPACE__ . '\\' . $class, $method) || \method_exists($class, $method); }
function getCurrentPage() { return intval($_REQUEST['current'] ?? 1); }
function getRowCount() { return intval($_REQUEST['rowCount'] ?? 1000); }
function unsetCurrentPage() { unset($_REQUEST['current']); }
function addQueryStringParameter($url, $name, $value)
{
    return $url . (strpos($url, '?') === false ? '?' : '&') . $name . '=' . rawurlencode((string) $value);
}
function getRedirectToVideo($videos_id) { return 'https://fixture/video/' . $videos_id; }
function getVideos_id() { return intval($_REQUEST['videos_id'] ?? 0); }
function isBot() { return UnlockFixture::$bot; }
function isContentTypeJson() { return false; }
function forbiddenPage($message, $log = false) { throw new \RuntimeException('Forbidden'); }

class PlaybackAccessUnlockTest extends TestCase
{
    protected function setUp(): void
    {
        global $global;
        $global['webSiteRootURL'] = 'https://fixture/';
        UnlockFixture::$rules = ['video:3505' => ['mode' => 'require', 'groups_json' => '[8,11]']];
        UnlockFixture::$enabled = ['Subscription', 'PayPerView'];
        UnlockFixture::$subscriptionPlans = [
            ['id' => 1, 'name' => 'After Dark', 'description' => 'Late night', 'price' => '9.99', 'how_many_days' => 30, 'is_featured' => 1, 'groups' => [['users_groups_id' => '8']]],
            ['id' => 2, 'name' => 'Ad-free', 'description' => '', 'price' => '4.99', 'how_many_days' => 30, 'is_featured' => 0, 'groups' => [['users_groups_id' => '1']]],
            ['id' => 3, 'name' => 'All-Access Annual Pass', 'description' => '', 'price' => '99', 'how_many_days' => 365, 'is_featured' => 0, 'groups' => [['users_groups_id' => '3'], ['users_groups_id' => '11']]],
        ];
        UnlockFixture::$ppvLinks = [
            ['ppv_plans_id' => 7, 'users_groups_id' => 8, 'status' => 'a'],
            ['ppv_plans_id' => 9, 'users_groups_id' => 11, 'status' => 'a'],
            ['ppv_plans_id' => 5, 'users_groups_id' => 10, 'status' => 'a'],
        ];
        UnlockFixture::$ppvPlans = [
            7 => ['id' => 7, 'name' => 'After Dark weekend', 'description' => '<b>48h</b>', 'value' => '3.5', 'hours_valid' => 48, 'status' => 'a'],
            9 => ['id' => 9, 'name' => 'Retired pass', 'description' => '', 'value' => '1', 'hours_valid' => 24, 'status' => 'i'],
            5 => ['id' => 5, 'name' => 'Video Rental PPV', 'description' => '', 'value' => '2', 'hours_valid' => 24, 'status' => 'a'],
        ];
        UnlockFixture::$ppvObject = null;
        UnlockFixture::$userGroups = [10];
        UnlockFixture::$visibilityGroups = [];
        UnlockFixture::$listingGet = null;
        UnlockFixture::$failListing = false;
        UnlockFixture::$bot = false;
        UnlockFixture::$pluginAccess = null;
        UnlockFixture::$ppvVideoPlans = [];
        UnlockFixture::$ownerStatus = 'a';
        UnlockFixture::$pluginChangesRequest = false;
        UnlockFixture::$rentalOption = UnlockFixture::$purchasedTitle = false;
        UnlockFixture::$rentalPlans = [];
        UnlockFixture::$video = ['id' => 3505, 'status' => 'a', 'users_id' => 1];
        (new \ReflectionProperty(PlaybackAccess::class, 'rules'))->setValue(null, []);
    }

    public function testOffersOnlyPlansThatGrantARequiredGroup(): void
    {
        $offers = PlaybackAccess::getUnlockOffers(3505);
        $this->assertSame([['subscription', 1], ['subscription', 3], ['ppv', 7]], array_map(function ($offer) {
            return [$offer['type'], $offer['id']];
        }, $offers));
        $this->assertTrue($offers[0]['featured']);
        $this->assertSame(9.99, $offers[0]['price']);
        $this->assertSame(365, $offers[1]['days']);
        $this->assertSame(48, $offers[2]['hours']);
    }

    public function testPlanMustAlsoReachTheVideoGroupsAfterPurchase(): void
    {
        // Video/category groups [11, 3]: plan 1 only grants 8, so the buyer would still be refused.
        UnlockFixture::$visibilityGroups = [11, 3];
        $offers = PlaybackAccess::getUnlockOffers(3505);
        $this->assertSame([['subscription', 3]], array_map(function ($offer) {
            return [$offer['type'], $offer['id']];
        }, $offers));
        // A viewer already in a video group only needs the rule group.
        UnlockFixture::$userGroups = [3];
        $this->assertSame([1, 3, 7], array_column(PlaybackAccess::getUnlockOffers(3505), 'id'));
    }

    public function testInactivePpvGroupLinkDoesNotCountAsGranted(): void
    {
        UnlockFixture::$visibilityGroups = [12];
        UnlockFixture::$ppvLinks[] = ['ppv_plans_id' => 7, 'users_groups_id' => 12, 'status' => 'i'];
        $this->assertSame([], PlaybackAccess::getUnlockOffers(3505));
        UnlockFixture::$ppvLinks[3]['status'] = 'a';
        $this->assertSame([7], array_column(PlaybackAccess::getUnlockOffers(3505), 'id'));
    }

    public function testRequiredGroupAloneDoesNotUnlockASeparatePaymentRestriction(): void
    {
        // Paid-only without visibility groups has no Subscription grant; an unpaid rental also denies it.
        UnlockFixture::$pluginAccess = false;
        $this->assertSame([], PlaybackAccess::getUnlockOffers(3505));
    }

    public function testPpvOfferMustBePurchasableAndGrantPositiveAccessTime(): void
    {
        UnlockFixture::$ppvPlans[7]['value'] = 0;
        $this->assertSame(['subscription', 'subscription'], array_column(PlaybackAccess::getUnlockOffers(3505), 'type'));
        UnlockFixture::$ppvPlans[7]['value'] = 3.5;
        UnlockFixture::$ppvPlans[7]['hours_valid'] = 0;
        $this->assertSame(['subscription', 'subscription'], array_column(PlaybackAccess::getUnlockOffers(3505), 'type'));
    }

    public function testExistingPaymentGrantCanSatisfyVisibilityAfterBuyingTheRequiredGroup(): void
    {
        UnlockFixture::$visibilityGroups = [12];
        UnlockFixture::$pluginAccess = true;
        $this->assertSame([1, 3, 7], array_column(PlaybackAccess::getUnlockOffers(3505), 'id'));
    }

    public function testWholePpvPlanPurchaseUnlocksItsLinkedTitle(): void
    {
        UnlockFixture::$pluginAccess = false;
        UnlockFixture::$ppvVideoPlans = [['ppv_plans_id' => 7, 'status' => 'a']];
        $this->assertSame([7], array_column(PlaybackAccess::getUnlockOffers(3505), 'id'));
        // PPV permits its linked title even without a matching visibility group.
        UnlockFixture::$visibilityGroups = [12];
        $this->assertSame([7], array_column(PlaybackAccess::getUnlockOffers(3505), 'id'));
        UnlockFixture::$ppvVideoPlans[0]['status'] = 'i';
        $this->assertSame([], PlaybackAccess::getUnlockOffers(3505));
    }

    public function testInactiveVideoOrOwnerHasNoOffers(): void
    {
        UnlockFixture::$ownerStatus = 'i';
        $this->assertSame([], PlaybackAccess::getUnlockOffers(3505));
        UnlockFixture::$ownerStatus = 'a';
        UnlockFixture::$video['status'] = 'i';
        $this->assertSame([], PlaybackAccess::getUnlockOffers(3505));
    }

    public function testPaymentLookupRestoresTheOriginalListingParameters(): void
    {
        $original = [$_GET, $_POST, $_REQUEST];
        try {
            $_GET = ['page' => '3']; $_POST = []; $_REQUEST = ['videos_id' => 3505];
            UnlockFixture::$pluginChangesRequest = true;
            PlaybackAccess::getUnlockOffers(3505);
            $this->assertSame([['page' => '3'], [], ['videos_id' => 3505]], [$_GET, $_POST, $_REQUEST]);
        } finally {
            [$_GET, $_POST, $_REQUEST] = $original;
        }
    }

    public function testCheckoutLinksReuseTheExistingFlows(): void
    {
        $offers = PlaybackAccess::getUnlockOffers(3505);
        $this->assertSame('https://fixture/plugin/Subscription/showPlans.php?videos_id=3505&plans_id=1', $offers[0]['url']);
        // The PPV checkout sells the group plan, never a rental of the refused title.
        $this->assertSame('https://fixture/plugin/PayPerView/subscribePlanPerUser.php?plans_id=7&redirectUri=' . rawurlencode('https://fixture/video/3505'), $offers[2]['url']);
        $this->assertStringNotContainsString('videos_id', $offers[2]['url']);
    }

    public function testCustomPayPerViewCheckoutIsRespected(): void
    {
        UnlockFixture::$ppvObject = (object) ['ifNeedToPayRedirectURL' => 'https://fixture/custom-ppv-checkout'];
        $offers = PlaybackAccess::getUnlockOffers(3505);
        $this->assertSame('https://fixture/custom-ppv-checkout', $offers[2]['url']);
    }

    public function testDisabledPluginsAndUnrestrictedTitlesHaveNoOffers(): void
    {
        UnlockFixture::$enabled = ['PayPerView'];
        $this->assertSame(['ppv'], array_column(PlaybackAccess::getUnlockOffers(3505), 'type'));
        UnlockFixture::$enabled = [];
        $this->assertSame([], PlaybackAccess::getUnlockOffers(3505));
        UnlockFixture::$enabled = ['Subscription', 'PayPerView'];
        $this->assertSame([], PlaybackAccess::getUnlockOffers(3306));
        UnlockFixture::$rules['video:3306'] = ['mode' => 'unrestricted', 'groups_json' => '[]'];
        $this->assertSame([], PlaybackAccess::getUnlockOffers(3306));
    }

    public function testPlanListingIgnoresTheWatchPageRequestAndRestoresIt(): void
    {
        $_GET = ['v' => '3505', 'searchPhrase' => 'x', 'sort' => ['name' => 'desc']];
        $_REQUEST['current'] = 3;
        $_REQUEST['rowCount'] = 12;
        try {
            PlaybackAccess::getUnlockOffers(3505);
            $this->assertSame([], UnlockFixture::$listingGet);
            $this->assertSame(['v' => '3505', 'searchPhrase' => 'x', 'sort' => ['name' => 'desc']], $_GET);
            $this->assertSame(3, $_REQUEST['current']);
            $this->assertSame(12, $_REQUEST['rowCount']);
        } finally {
            $_GET = [];
            unset($_REQUEST['current'], $_REQUEST['rowCount']);
        }
    }

    public function testListingRestoresAbsentAndOriginalRequestParametersEvenOnFailure(): void
    {
        $original = [$_GET, $_POST, $_REQUEST];
        try {
            foreach ([false, true] as $fail) {
                UnlockFixture::$failListing = $fail;
                foreach ([[], ['videos_id' => 3505, 'current' => '03', 'rowCount' => '12']] as $request) {
                    $_GET = ['searchPhrase' => 'watch title'];
                    $_POST = ['sort' => ['name' => 'desc']];
                    $_REQUEST = $request;
                    PlaybackAccess::getUnlockOffers(3505);
                    $this->assertSame($request, $_REQUEST);
                    $this->assertSame(['searchPhrase' => 'watch title'], $_GET);
                    $this->assertSame(['sort' => ['name' => 'desc']], $_POST);
                }
            }
        } finally {
            [$_GET, $_POST, $_REQUEST] = $original;
        }
    }

    public function testSubscriptionCheckoutOnlyListsPlansThatSatisfyBothRestrictions(): void
    {
        if (!is_file(dirname(__DIR__, 2) . '/plugin/Subscription/showPlans.php')) {
            $this->markTestSkipped('The separately distributed Subscription plugin is not installed');
        }
        $request = $_REQUEST;
        try {
            $_REQUEST['videos_id'] = 3505;
            UnlockFixture::$visibilityGroups = [11];
            $this->assertSame([3], array_column($this->subscriptionCheckoutPlans(), 'id'));
            UnlockFixture::$visibilityGroups = [12];
            $this->assertSame([], $this->subscriptionCheckoutPlans());
            unset($_REQUEST['videos_id']);
            $this->assertSame([1, 2, 3], array_column($this->subscriptionCheckoutPlans(), 'id'));
        } finally {
            $_REQUEST = $request;
        }
    }

    private function subscriptionCheckoutPlans(): array
    {
        $plans = UnlockFixture::$subscriptionPlans;
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/Subscription/showPlans.php');
        $start = strpos($source, '$videos_id = getVideos_id();');
        $end = strpos($source, '$obj = AVideoPlugin::', $start);
        eval('namespace ' . __NAMESPACE__ . ';' . substr($source, $start, $end - $start));
        return $plans;
    }

    public function testBotsAndUnpublishedTitlesKeepThePlainRefusal(): void
    {
        UnlockFixture::$bot = true;
        $this->assertForbidden();
        UnlockFixture::$bot = false;
        UnlockFixture::$video = ['id' => 3505, 'status' => 'i'];
        $this->assertForbidden();
        UnlockFixture::$video = false;
        $this->assertForbidden();
    }

    private function assertForbidden(): void
    {
        try {
            PlaybackAccess::showUnlockPage(3505);
            $this->fail('Expected the plain refusal');
        } catch (\RuntimeException $e) {
            $this->assertSame('Forbidden', $e->getMessage());
        }
    }

    public function testDeniedWatchPageShowsTheUnlockPageWithoutPlayableSources(): void
    {
        $root = dirname(__DIR__, 2) . '/';
        $watch = file_get_contents($root . 'view/modeYoutube.php');
        $denied = strpos($watch, 'if ($videos_id && !PlaybackAccess::isAllowed($videos_id)) {');
        $this->assertNotFalse($denied);
        $block = substr($watch, $denied, strpos($watch, '}', $denied) - $denied);
        $this->assertStringContainsString('PlaybackAccess::showUnlockPage($videos_id);', $block);
        $page = file_get_contents($root . 'view/playbackAccessUnlock.php');
        foreach (['getSources(', 'getVideosURL', 'getVideoLinkFromID'] as $sourceBuilder) {
            $this->assertStringNotContainsString($sourceBuilder, $page);
        }
        // The page is the end of the request: nothing after it may build the player.
        $policy = file_get_contents($root . 'objects/PlaybackAccess.php');
        $show = substr($policy, strpos($policy, 'public static function showUnlockPage('));
        $this->assertMatchesRegularExpression("/http_response_code\\(403\\);\\s*include [^;]*playbackAccessUnlock\\.php';\\s*exit;/", $show);
    }

    public function testTitleRentalIsOfferedOnlyWhenPayPerViewAllowsIt(): void
    {
        UnlockFixture::$rentalPlans = [['id' => 2, 'name' => '72-Hour Rental', 'description' => '', 'value' => '1.99', 'hours_valid' => 72, 'status' => 'a']];
        $this->assertSame([], array_filter(PlaybackAccess::getUnlockOffers(3505), function ($offer) { return $offer['type'] === 'rental'; }));
        UnlockFixture::$rentalOption = true;
        $rentals = array_values(array_filter(PlaybackAccess::getUnlockOffers(3505), function ($offer) { return $offer['type'] === 'rental'; }));
        $this->assertCount(1, $rentals);
        $this->assertSame(2, $rentals[0]['id']);
        $this->assertSame(72, $rentals[0]['hours']);
        $this->assertSame(1.99, $rentals[0]['price']);
        // The title's own rental checkout (videos_id), not the whole plan.
        $this->assertSame('https://fixture/plugin/PayPerView/subscribePlanPerUser.php?plans_id=2&videos_id=3505', $rentals[0]['url']);
        UnlockFixture::$ppvObject = (object) ['ifNeedToPayRedirectURL' => 'https://pay.example/'];
        $rentals = array_values(array_filter(PlaybackAccess::getUnlockOffers(3505), function ($offer) { return $offer['type'] === 'rental'; }));
        $this->assertSame('https://fixture/custom-ppv-checkout', $rentals[0]['url']);
    }

    public function testRentalOfAPlanAlreadySoldWholeIsNotListedTwice(): void
    {
        UnlockFixture::$rentalOption = true;
        UnlockFixture::$visibilityGroups = [8];
        UnlockFixture::$rentalPlans = [UnlockFixture::$ppvPlans[7]];
        $offers = PlaybackAccess::getUnlockOffers(3505);
        $planSeven = array_values(array_filter($offers, function ($offer) { return $offer['id'] === 7 && $offer['type'] !== 'subscription'; }));
        $this->assertCount(1, $planSeven);
        $this->assertSame('ppv', $planSeven[0]['type']);
    }

    public function testPurchaseOfTheTitleSatisfiesTheRuleOnlyWithTheOption(): void
    {
        UnlockFixture::$userGroups = [4];
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 82));
        UnlockFixture::$purchasedTitle = true;
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 82), 'A rental alone keeps the group-only rule by default');
        UnlockFixture::$rentalOption = true;
        $this->assertTrue(PlaybackAccess::isAllowed(3505, 82));
        UnlockFixture::$purchasedTitle = false;
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 82), 'The option alone does not unlock anything');
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 0), 'Visitors never pass');
        UnlockFixture::$enabled = ['Subscription'];
        UnlockFixture::$purchasedTitle = true;
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 82), 'Without PayPerView the rule stays group-only');
        $this->assertFalse(PlaybackAccess::canRentTitle(3505));
    }

    public function testWatchPageResolvesTheRentedTitleInsteadOfAFallbackVideo(): void
    {
        // A renter has no visibility group of the title: the group-filtered lookup would miss it and the page
        // would fall back to another video, as it already avoids in free-with-ads mode.
        UnlockFixture::$userGroups = [4];
        $this->assertFalse(PlaybackAccess::isAllowedByTitlePurchase(3505, 82));
        UnlockFixture::$rentalOption = UnlockFixture::$purchasedTitle = true;
        $this->assertTrue(PlaybackAccess::isAllowedByTitlePurchase(3505, 82));
        $this->assertFalse(PlaybackAccess::isAllowedByTitlePurchase(3505, 0));
        UnlockFixture::$rules = [];
        (new \ReflectionProperty(PlaybackAccess::class, 'rules'))->setValue(null, []);
        $this->assertFalse(PlaybackAccess::isAllowedByTitlePurchase(3505, 82), 'Only titles under a rule');
        $watch = file_get_contents(dirname(__DIR__, 2) . '/view/modeYoutube.php');
        $this->assertMatchesRegularExpression('/PlaybackAccess::isRestricted\(\$videos_id\)\s*&& \(Video::allowFreePlayWithAdsIsEnabled\(\) \|\| PlaybackAccess::isAllowedByTitlePurchase\(\$videos_id\)\)\) \{\s*\/\/[^\n]*\n\s*\$video = Video::getVideo\(\$videos_id, Video::SORT_TYPE_VIEWABLE, true, false, false, true\);/', $watch);
    }

    public function testTrailerIsOpenedOnlyForAValidWebAddress(): void
    {
        // avideoModalIframe() concatenates the address into src="...": a stored trailer with a quote or a
        // javascript: scheme (both pass the FILTER_VALIDATE_URL setter) would run script on click.
        $page = file_get_contents(dirname(__DIR__, 2) . '/view/playbackAccessUnlock.php');
        $this->assertMatchesRegularExpression("/if \\(isValidURL\\(\\\$video\\['trailer1'\\]\\)\\) \\{\\s*\\\$unlockTrailer = parseVideos\\(\\\$video\\['trailer1'\\]/", $page);
        $this->assertSame(1, substr_count($page, 'parseVideos('));
    }

    public function testOfferLayoutFitsAContentAreaNarrowerThanTheContainer(): void
    {
        // A docked panel (chat) can make the section narrower than Bootstrap's fixed .container width or
        // than one 250px card; the section clips overflow, so content must shrink instead.
        $css = file_get_contents(dirname(__DIR__, 2) . '/view/css/playbackAccessUnlock.css');
        $this->assertMatchesRegularExpression('/\.avideoUnlock-content \{[^}]*max-width: 100%;/', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(auto-fill, minmax(min(250px, 100%), 1fr));', $css);
        $this->assertMatchesRegularExpression('/\.avideoUnlock \{[^}]*overflow-wrap: break-word;/', $css);
    }
}
