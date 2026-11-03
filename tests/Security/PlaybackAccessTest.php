<?php

namespace Tests\Security\MandatoryPlayback;

use PHPUnit\Framework\TestCase;

// Execute the production decision and User methods without the application/database bootstrap.
$playbackPolicySource = file_get_contents(dirname(__DIR__, 2) . '/objects/PlaybackAccess.php');
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use InvalidArgumentException; use Throwable;' . substr($playbackPolicySource, 5));
$playbackUserSource = file_get_contents(dirname(__DIR__, 2) . '/objects/user.php');
$playbackUserStart = strpos($playbackUserSource, '    private static function setCacheWatchVideo(');
$playbackUserEnd = strpos($playbackUserSource, '    public function delete()', $playbackUserStart);
eval('namespace ' . __NAMESPACE__ . '; class User {
    public function __construct($id = 0) {}
    public static function getId() { return Fixture::$user; }
    public static function isLogged() { return (bool) Fixture::$user; }
    public static function isAdmin($id = null) { return ($id ?: Fixture::$user) === 1; }
    public function getStatus() { return "a"; }
' . substr($playbackUserSource, $playbackUserStart, $playbackUserEnd - $playbackUserStart) . '}');
$playbackHlsSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/VideoHLS/VideoHLS.php');
$playbackHlsStart = strpos($playbackHlsSource, '    static function getToken(');
$playbackHlsEnd = strpos($playbackHlsSource, '    static function getRequestIpCandidates(', $playbackHlsStart);
eval('namespace ' . __NAMESPACE__ . '; use stdClass; use Throwable; class VideoHLS {
    public static function verifyToken($token) { return Fixture::$validToken; }
    public static function getRequestIpCandidates() { return ["127.0.0.1"]; }
' . substr($playbackHlsSource, $playbackHlsStart, $playbackHlsEnd - $playbackHlsStart) . '}');
$playbackApiSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/API/API.php');
$playbackApiStart = strpos($playbackApiSource, '    private static function filterMandatoryPlaybackSources(');
$playbackApiEnd = strpos($playbackApiSource, '    private static function filterVideoSources(', $playbackApiStart);
eval('namespace ' . __NAMESPACE__ . '; use stdClass; class API {
    public static function isAPISecretValid() { return false; }
    public static function filter(&$obj) { self::filterMandatoryPlaybackSources($obj); }
' . substr($playbackApiSource, $playbackApiStart, $playbackApiEnd - $playbackApiStart) . '}');
$playbackPlaylistSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/PlayLists/PlayListElement.php');
eval('namespace ' . __NAMESPACE__ . ';' . substr($playbackPlaylistSource, strpos($playbackPlaylistSource, 'class playListSource')));

class Fixture
{
    public static $installed = true, $user = 82, $fail = false, $pluginCalls = 0, $validToken = true;
    public static $rules = [], $groups = [], $categories = [], $videos = [];
}
class ObjectYPT
{
    public static function isTableInstalled($table) { return Fixture::$installed; }
    public static function getSessionCache($key, $ttl) { return true; } // Deliberately stale grant.
    public static function getCache($key, $ttl) { return true; }
    public static function setSessionCache($key, $value) {}
    public static function setCache($key, $value) {}
}
class sqlDAL
{
    public static function readSql($sql, $types = '', $args = [], $fresh = false)
    {
        if (Fixture::$fail) { return false; }
        if (strpos($sql, 'SELECT mode, groups_json') === 0) {
            return (object) ['row' => Fixture::$rules[$args[0] . ':' . $args[1]] ?? false];
        }
        if (strpos($sql, 'SELECT categories_id') === 0) {
            return (object) ['row' => ['categories_id' => Fixture::$videos[$args[0]] ?? 0]];
        }
        if (strpos($sql, 'SELECT parentId') === 0) {
            return (object) ['row' => ['parentId' => Fixture::$categories[$args[0]] ?? 0]];
        }
        return (object) ['row' => ['id' => $args[0]]];
    }
    public static function fetchAssoc($res) { return $res->row; }
    public static function close($res) {}
    public static function writeSql($sql, $types, $args)
    {
        $key = $args[0] . ':' . $args[1];
        if (strpos($sql, 'DELETE') === 0) { unset(Fixture::$rules[$key]); }
        else { Fixture::$rules[$key] = ['mode' => $args[2], 'groups_json' => $args[3]]; }
        return true;
    }
}
class UserGroups
{
    public static function getUserGroups($id) { return array_map(function ($group) { return ['id' => $group]; }, Fixture::$groups[$id] ?? []); }
    public static function getVideosAndCategoriesUserGroups($id) { return []; }
}
class Video
{
    const STATUS_INACTIVE = 'i';
    public function __construct($a = '', $b = '', $id = 0) {}
    public function getStatus() { return 'a'; }
    public function getUsers_id() { return 1; }
}
class AVideoPlugin
{
    public static function userCanWatchVideo($user, $video) { Fixture::$pluginCalls++; return true; }
    public static function userCanWatchVideoWithAds($user, $video) { Fixture::$pluginCalls++; return true; }
}
class AVideoLog { public static $ERROR = 1; }
function _error_log($message, $level = null) {}
function isVideo() { return false; }
function forbiddenPage($message, $log = false) { throw new \RuntimeException('Forbidden'); }
function encryptString($value) { return base64_encode($value); }
function decryptString($value) { return base64_decode($value); }
function getAToken() { return 'fixture-device'; }
function class_exists($name) { return \class_exists(__NAMESPACE__ . '\\' . $name) || \class_exists($name); }
function TimeLogStart($name) { return $name; }
function TimeLogEnd($name, $line, $limit = null) {}

class PlaybackAccessTest extends TestCase
{
    protected function setUp(): void
    {
        Fixture::$installed = true;
        Fixture::$fail = false;
        Fixture::$user = 82;
        Fixture::$pluginCalls = 0;
        Fixture::$validToken = true;
        $_SERVER['HTTP_USER_AGENT'] = 'Fixture player';
        Fixture::$rules = ['category:91' => ['mode' => 'require', 'groups_json' => '[8,11]']];
        Fixture::$groups = [82 => [10], 83 => [8], 84 => [11], 85 => []];
        Fixture::$videos = [3505 => 91, 3506 => 92, 3306 => 7];
        Fixture::$categories = [91 => 0, 92 => 91, 7 => 0];
        $property = new \ReflectionProperty(PlaybackAccess::class, 'rules');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    public function testPpvAndAdsCannotOverrideTheRequiredGroupOrAnOldSessionGrant(): void
    {
        $this->assertFalse(User::canWatchVideo(3505));
        $this->assertFalse(User::canWatchVideoWithAds(3505));
        $this->assertSame(0, Fixture::$pluginCalls);
    }

    public function testRequiredGroupAllowsNormalChecksAndDoesNotAffectOtherMovies(): void
    {
        $this->assertTrue(User::canWatchVideoWithAds(3505, 83));
        $this->assertTrue(User::canWatchVideoWithAds(3505, 84));
        $this->assertTrue(User::canWatchVideoWithAds(3306, 82));
        $this->assertGreaterThan(0, Fixture::$pluginCalls);
    }

    public function testVisitorExpiredSubscriberAndSubcategoryAreBlocked(): void
    {
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 0));
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 85));
        $this->assertFalse(PlaybackAccess::isAllowed(3506, 82));
        $this->assertTrue(PlaybackAccess::isAllowed(3506, 83));
    }

    public function testAdminAndExplicitGuestAreDifferentEvenInTheAdminTester(): void
    {
        Fixture::$user = 1;
        $this->assertTrue(PlaybackAccess::isAllowed(3505, 1));
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 0));
    }

    public function testVideoOverrideAndReturnToInheritance(): void
    {
        Fixture::$user = 1;
        PlaybackAccess::saveRule('video', 3505, 'require', [11]);
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 83));
        $this->assertTrue(PlaybackAccess::isAllowed(3505, 84));
        PlaybackAccess::saveRule('video', 3505, 'unrestricted', []);
        $this->assertTrue(PlaybackAccess::isAllowed(3505, 82));
        PlaybackAccess::saveRule('video', 3505, 'inherit', []);
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 82));
        $this->assertTrue(PlaybackAccess::isAllowed(3505, 83));
    }

    public function testNoMigrationPreservesLegacyPlayback(): void
    {
        Fixture::$installed = false;
        $this->assertTrue(PlaybackAccess::isAllowed(3505, 82));
    }

    public function testBrokenRuleOrDatabaseFailureDoesNotGrantAccess(): void
    {
        Fixture::$rules['category:91']['groups_json'] = 'invalid JSON';
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 83));
        Fixture::$fail = true;
        $this->assertFalse(PlaybackAccess::isAllowed(3306, 83));
    }

    public function testInheritanceCycleFailsClosed(): void
    {
        Fixture::$rules = [];
        Fixture::$categories = [91 => 92, 92 => 91];
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 83));
    }

    public function testInvalidSavedModeFailsClosed(): void
    {
        Fixture::$rules['category:91']['mode'] = 'invalid';
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 83));
    }

    public function testEmptyRequiredSelectionIsRejected(): void
    {
        Fixture::$user = 1;
        $this->expectException(\InvalidArgumentException::class);
        PlaybackAccess::saveRule('category', 91, 'require', []);
    }

    public function testNonAdminCannotSaveRules(): void
    {
        $this->expectException(\RuntimeException::class);
        PlaybackAccess::saveRule('category', 91, 'unrestricted', []);
    }

    public function testMalformedGroupIdsAreRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PlaybackAccess::normalizeGroups(['8 OR 1=1']);
    }

    public function testUnauthorizedUserCannotMintAnHlsToken(): void
    {
        $this->assertSame('', VideoHLS::getToken(3505));
    }

    public function testSignedHlsIdentityIsBoundToTheVideoAndCurrentMembership(): void
    {
        Fixture::$user = 83;
        $token = VideoHLS::getToken(3505);
        $otherVideoToken = VideoHLS::getToken(3306);
        $this->assertNotSame('', $token);
        Fixture::$user = 0;
        $this->assertTrue(VideoHLS::canAccessMandatoryRule(3505, $token));
        $this->assertFalse(VideoHLS::canAccessMandatoryRule(3505, $otherVideoToken));
        Fixture::$validToken = false;
        $this->assertFalse(VideoHLS::canAccessMandatoryRule(3505, $token));
        Fixture::$validToken = true;
        Fixture::$groups[83] = [];
        $this->assertFalse(VideoHLS::canAccessMandatoryRule(3505, $token));
        $this->assertTrue(VideoHLS::canAccessMandatoryRule(3306));
    }

    public function testAnonymousHlsCannotUseAnOldTokenWithoutUserIdentity(): void
    {
        Fixture::$user = 0;
        $token = encryptString(json_encode(['videos_id' => 3505, 'time' => time()]));
        $this->assertFalse(VideoHLS::canAccessMandatoryRule(3505, $token));
    }

    public function testCachedApiKeepsMetadataButRemovesPlaybackIncludingRelatedMovies(): void
    {
        $row = ['id' => 3505, 'title' => 'After Dark', 'sources' => ['old.mp4'], 'videos' => ['old.m3u8'], 'videoLink' => 'old.mp4'];
        $public = ['id' => 3306, 'sources' => ['normal.mp4'], 'relatedVideos' => [$row]];
        $obj = (object) ['rows' => [$row, $public]];
        API::filter($obj);
        $this->assertSame('After Dark', $obj->rows[0]['title']);
        $this->assertSame([], $obj->rows[0]['sources']);
        $this->assertSame('', $obj->rows[0]['videoLink']);
        $this->assertSame(['normal.mp4'], $obj->rows[1]['sources']);
        $this->assertSame([], $obj->rows[1]['relatedVideos'][0]['sources']);
    }

    public function testPlaylistCannotDiscloseADeniedMp4Source(): void
    {
        $source = new playListSource('private.mp4', 3505);
        $this->assertSame('', $source->src);
        $this->assertSame(3505, $source->videos_id);
    }
}
