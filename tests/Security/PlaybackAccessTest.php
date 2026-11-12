<?php

namespace Tests\Security\MandatoryPlayback;

use PHPUnit\Framework\TestCase;

// Execute the production decision and User methods without the application/database bootstrap.
$playbackPolicySource = file_get_contents(dirname(__DIR__, 2) . '/objects/PlaybackAccess.php');
eval('namespace ' . __NAMESPACE__ . '; use RuntimeException; use InvalidArgumentException; use Throwable; use stdClass;' . substr($playbackPolicySource, 5));
$playbackUserSource = file_get_contents(dirname(__DIR__, 2) . '/objects/user.php');
$playbackUserStart = strpos($playbackUserSource, '    private static function setCacheWatchVideo(');
$playbackUserEnd = strpos($playbackUserSource, '    public function delete()', $playbackUserStart);
eval('namespace ' . __NAMESPACE__ . '; class User {
    public function __construct($id = 0) {}
    public static function getId() { return Fixture::$user; }
    public static function isLogged() { return (bool) Fixture::$user; }
    public static function isAdmin($id = null) { return ($id ?: Fixture::$user) === 1; }
    public static function getPhoto($id) { return "photo.jpg"; }
    public function getStatus() { return "a"; }
' . substr($playbackUserSource, $playbackUserStart, $playbackUserEnd - $playbackUserStart) . '}');
// VideoHLS is distributed separately (gitignored here); its tests are skipped when it is not installed.
$playbackHlsFile = dirname(__DIR__, 2) . '/plugin/VideoHLS/VideoHLS.php';
if (is_file($playbackHlsFile)) {
    $playbackHlsSource = file_get_contents($playbackHlsFile);
    $playbackHlsStart = strpos($playbackHlsSource, '    static function getToken(');
    $playbackHlsEnd = strpos($playbackHlsSource, '    static function getRequestIpCandidates(', $playbackHlsStart);
    eval('namespace ' . __NAMESPACE__ . '; use stdClass; use Throwable; class VideoHLS {
    public static function verifyToken($token) { return Fixture::$validToken; }
    public static function getRequestIpCandidates() { return ["127.0.0.1"]; }
' . substr($playbackHlsSource, $playbackHlsStart, $playbackHlsEnd - $playbackHlsStart) . '}');
}
$playbackApiSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/API/API.php');
$playbackApiStart = strpos($playbackApiSource, '    private static function filterMandatoryPlaybackSources(');
$playbackApiEnd = strpos($playbackApiSource, '    private static function filterVideoSources(', $playbackApiStart);
eval('namespace ' . __NAMESPACE__ . '; use stdClass; class API {
    public static function isAPISecretValid() { return false; }
    public static function filter(&$obj) { self::filterMandatoryPlaybackSources($obj); }
' . substr($playbackApiSource, $playbackApiStart, $playbackApiEnd - $playbackApiStart) . '}');
$playbackPlaylistSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/PlayLists/PlayListElement.php');
eval('namespace ' . __NAMESPACE__ . ';' . substr($playbackPlaylistSource, strpos($playbackPlaylistSource, 'class playListSource')));
$playbackDownloadSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/CustomizeUser/CustomizeUser.php');
$playbackDownloadStart = strpos($playbackDownloadSource, '    public static function canDownloadVideosFromVideo(');
$playbackDownloadEnd = strpos($playbackDownloadSource, '    public static function canShareVideosFromVideo(', $playbackDownloadStart);
eval('namespace ' . __NAMESPACE__ . '; class CustomizeUser {
    public static function canDownloadVideos() { return true; }
    public static function canDownloadVideosFromUser($id) { return true; }
' . substr($playbackDownloadSource, $playbackDownloadStart, $playbackDownloadEnd - $playbackDownloadStart) . '}');

class Fixture
{
    public static $installed = true, $coreAvailable = true, $user = 82, $fail = false, $schemaFail = false, $pluginCalls = 0, $validToken = true, $cacheClears = 0, $sourceCalls = 0, $targetVideo = 3505;
    public static $rules = [], $groups = [], $categories = [], $videos = [];
}
class ObjectYPT
{
    public static function isTableInstalled($table) { return Fixture::$installed && !Fixture::$schemaFail; }
    public static function getSessionCache($key, $ttl) { return true; } // Deliberately stale grant.
    public static function getCache($key, $ttl) { return true; }
    public static function setSessionCache($key, $value) {}
    public static function setCache($key, $value) {}
    public static function deleteALLCache() { Fixture::$cacheClears++; return true; }
}
class sqlDAL
{
    public static function readSql($sql, $types = '', $args = [], $fresh = false)
    {
        if (Fixture::$fail) { return false; }
        if (strpos($sql, 'SHOW TABLES') === 0) {
            return Fixture::$schemaFail ? false : (object) ['count' => (int) Fixture::$installed];
        }
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
    public static function num_rows($res) { return $res->count; }
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
    public function getCategories_id() { return 91; }
    public function getCan_download() { return 1; }
    public static function getURLFriendly($id, $embed = false) { return 'https://fixture/video/' . $id; }
    public static function getVideo($id) { return ['id' => $id, 'filename' => 'fixture', 'type' => 'video', 'title' => 'Fixture title', 'next_videos_id' => 3306, 'users_id' => 1]; }
    public static function getCleanFilenameFromFile($filename) { return $filename; }
}
class Category { public function __construct($id) {} public function getAllow_download() { return true; } }
class AVideoPlugin
{
    public static function userCanWatchVideo($user, $video) { Fixture::$pluginCalls++; return true; }
    public static function userCanWatchVideoWithAds($user, $video) { Fixture::$pluginCalls++; return true; }
    public static function getObjectDataIfEnabled($plugin) { return (object) []; }
}
class AVideoLog { public static $ERROR = 1; }
function _error_log($message, $level = null) {}
function isVideo() { return false; }
function forbiddenPage($message, $log = false) { throw new \RuntimeException('Forbidden'); }
function encryptString($value) { return base64_encode($value); }
function decryptString($value) { return base64_decode($value); }
function getAToken() { return 'fixture-device'; }
function class_exists($name) { return ($name !== 'PlaybackAccess' || Fixture::$coreAvailable) && (\class_exists(__NAMESPACE__ . '\\' . $name) || \class_exists($name)); }
function TimeLogStart($name) { return $name; }
function TimeLogEnd($name, $line, $limit = null) {}
function header($value) {}
function getVideoIDFromURL($url) { return Fixture::$targetVideo; }
function getSources($filename, $asArray = false) { Fixture::$sourceCalls++; return [['src' => 'fixture.mp4']]; }
function getVideosURL_V2($filename) { return ['jpg' => ['url' => 'poster.jpg']]; }

class PlaybackAccessTest extends TestCase
{
    protected function setUp(): void
    {
        Fixture::$installed = true;
        Fixture::$coreAvailable = true;
        Fixture::$fail = false;
        Fixture::$schemaFail = false;
        Fixture::$user = 82;
        Fixture::$pluginCalls = 0;
        Fixture::$validToken = true;
        Fixture::$cacheClears = 0;
        Fixture::$sourceCalls = 0;
        Fixture::$targetVideo = 3505;
        $_SERVER['HTTP_USER_AGENT'] = 'Fixture player';
        Fixture::$rules = ['category:91' => ['mode' => 'require', 'groups_json' => '[8,11]']];
        Fixture::$groups = [82 => [10], 83 => [8], 84 => [11], 85 => []];
        Fixture::$videos = [3505 => 91, 3506 => 92, 3306 => 7];
        Fixture::$categories = [91 => 0, 92 => 91, 7 => 0];
        $property = new \ReflectionProperty(PlaybackAccess::class, 'rules');
        $property->setValue(null, []);
    }

    private function requireVideoHLS(string $file = 'VideoHLS.php'): void
    {
        if (!is_file(dirname(__DIR__, 2) . '/plugin/VideoHLS/' . $file)) {
            $this->markTestSkipped('The separately distributed VideoHLS plugin is not installed');
        }
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

    public function testFailedSchemaLookupCannotMasqueradeAsAnAbsentMigration(): void
    {
        Fixture::$schemaFail = true;
        $this->assertFalse(PlaybackAccess::isAllowed(3505, 82));
        $this->assertTrue(PlaybackAccess::isRestricted(3505));
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
        $this->requireVideoHLS();
        $this->assertSame('', VideoHLS::getToken(3505));
    }

    public function testOlderCoreWithoutTheOptionalHelperKeepsLegacyHlsBehavior(): void
    {
        $this->requireVideoHLS();
        Fixture::$coreAvailable = false;
        $this->assertNotSame('', VideoHLS::getToken(3505));
        $this->assertTrue(VideoHLS::canAccessMandatoryRule(3505));
    }

    public function testSignedHlsIdentityIsBoundToTheVideoAndCurrentMembership(): void
    {
        $this->requireVideoHLS();
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
        $this->requireVideoHLS();
        Fixture::$user = 0;
        $token = encryptString(json_encode(['videos_id' => 3505, 'time' => time()]));
        $this->assertFalse(VideoHLS::canAccessMandatoryRule(3505, $token));
    }

    public function testAdminHlsBypassSurvivesAMalformedSavedRule(): void
    {
        $this->requireVideoHLS();
        Fixture::$rules['category:91']['groups_json'] = 'invalid JSON';
        Fixture::$rules['category:91']['mode'] = 'invalid';
        Fixture::$user = 1;
        $this->assertTrue(VideoHLS::canAccessMandatoryRule(3505));
        Fixture::$user = 0;
        $token = encryptString(json_encode(['videos_id' => 3505, 'users_id' => 1]));
        $this->assertTrue(VideoHLS::canAccessMandatoryRule(3505, $token));
        $this->assertFalse(VideoHLS::canAccessMandatoryRule(3505));
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

    public function testNestedPlaylistRowsAndCachedMp3AreFilteredWithoutChangingMetadata(): void
    {
        $files = ['mp4' => ['type' => 'video', 'url' => 'restricted.mp4'], 'jpg' => ['type' => 'image', 'url' => 'poster.jpg']];
        $restricted = ['id' => 3505, 'title' => 'After Dark', 'videos' => ['mp4' => 'restricted.mp4'], 'sources' => ['restricted.mp4'],
            'VideoUrl' => $files, 'videosURL' => $files, 'videoLink' => 'linked.mp4', 'videoDownloadedLink' => 'download.mp4',
            'firstVideo' => 'restricted.mp4', 'mp3' => ['url' => 'restricted.mp3']];
        $normal = ['id' => 3306, 'title' => 'Series', 'sources' => ['normal.mp4'], 'playlist' => [$restricted]];
        $rows = PlaybackAccess::filterVideoRows([$normal]);
        $episode = $rows[0]['playlist'][0];
        $this->assertSame('After Dark', $episode['title']);
        // The media map is a JSON object for clients (get_api_video normalizes it to stdClass too).
        $this->assertSame('{}', json_encode($episode['videos']));
        $this->assertSame([], $episode['sources']);
        $this->assertSame(['jpg' => $files['jpg']], $episode['VideoUrl']);
        $this->assertSame(['jpg' => $files['jpg']], $episode['videosURL']);
        $this->assertSame('', $episode['videoLink']);
        $this->assertSame('', $episode['videoDownloadedLink']);
        $this->assertSame('', $episode['firstVideo']);
        $this->assertFalse($episode['mp3']);
        $this->assertSame(['normal.mp4'], $rows[0]['sources']);
        Fixture::$user = 83;
        $this->assertSame([$normal], PlaybackAccess::filterVideoRows([$normal]));
    }

    public function testKeyPathCannotSelectAKeyOutsideTheAuthorizedVideoDirectory(): void
    {
        $this->requireVideoHLS();
        $this->assertTrue(VideoHLS::isKeyRequestPathValid('video_name', 'enc_key'));
        $this->assertTrue(VideoHLS::isKeyRequestPathValid('video_name', 'audio/enc_key'));
        $this->assertTrue(VideoHLS::isKeyRequestPathValid('video_name', '{$pathToVideo}enc_key'));
        foreach ([['video_name', '../restricted/enc_key'], ['video_name', '..\\restricted\\enc_key'], ['.', 'restricted/enc_key'], ['../restricted', 'enc_key'], ['video_name', '{$pathToVideo}../restricted/enc_key']] as $params) {
            $this->assertFalse(VideoHLS::isKeyRequestPathValid(...$params));
        }
    }

    public function testDecodedCacheObjectsAreFilteredAndKeepTheirJsonShape(): void
    {
        $json = '{"rows":[{"id":3505,"title":"After Dark","videos":{"mp4":"private.mp4"},"sources":[{"src":"private.mp4"}],"VideoUrl":{"mp4":{"type":"video","url":"private.mp4"},"jpg":{"type":"image","url":"poster.jpg"}}}]}';
        $obj = json_decode($json);
        API::filter($obj);
        $this->assertSame('After Dark', $obj->rows[0]->title);
        $this->assertEquals(new \stdClass(), $obj->rows[0]->videos);
        $this->assertSame([], $obj->rows[0]->sources);
        $this->assertFalse(isset($obj->rows[0]->VideoUrl->mp4));
        $this->assertSame('poster.jpg', $obj->rows[0]->VideoUrl->jpg->url);
        Fixture::$user = 83;
        $obj = json_decode($json);
        API::filter($obj);
        $this->assertSame($json, json_encode($obj));
    }

    public function testSparseDecodedCatalogObjectsAreAlsoFiltered(): void
    {
        $obj = json_decode('{"rows":{"3":{"id":3505,"sources":["private.mp4"]}}}');
        API::filter($obj);
        $this->assertSame([], $obj->rows->{'3'}->sources);
    }

    public function testFailedHlsVideoLookupCannotFallThroughToTheKeyCache(): void
    {
        $this->requireVideoHLS('downloadProtection.php');
        Fixture::$fail = true;
        $handler = file_get_contents(dirname(__DIR__, 2) . '/plugin/VideoHLS/downloadProtection.php');
        $start = strpos($handler, '    $mandatoryFilename =');
        $end = strpos($handler, '    if (!empty($mandatoryVideo', $start);
        $this->assertNotFalse($start);
        $previousGet = $_GET;
        try {
            $_GET['filename'] = 'fixture';
            $this->expectException(\RuntimeException::class);
            eval('namespace ' . __NAMESPACE__ . ';' . substr($handler, $start, $end - $start));
        } finally {
            $_GET = $previousGet;
        }
    }

    public function testGalleryDownloadPermissionIncludesTheMandatoryPrerequisite(): void
    {
        $this->assertFalse(CustomizeUser::canDownloadVideosFromVideo(3505));
        $this->assertTrue(CustomizeUser::canDownloadVideosFromVideo(3306));
        Fixture::$user = 83;
        $this->assertTrue(CustomizeUser::canDownloadVideosFromVideo(3505));
    }

    /** @dataProvider sourceViewers */
    public function testInfoFromUrlKeepsMetadataButDoesNotBuildDeniedSources($user, $video, $allowed): void
    {
        global $global;
        $previousGlobal = $global;
        $previousGet = $_GET;
        try {
            $global = ['systemRootPath' => dirname(__DIR__, 2) . '/', 'webSiteRootURL' => 'https://fixture/'];
            $_GET = ['url' => 'https://fixture/video/' . $video];
            Fixture::$user = $user;
            Fixture::$targetVideo = $video;
            $route = file_get_contents(dirname(__DIR__, 2) . '/view/infoFromURL.php');
            $route = str_replace('die(json_encode($obj));', 'return $obj;', $route);
            $obj = eval('namespace ' . __NAMESPACE__ . '; use stdClass; ?>' . $route);
            $this->assertFalse($obj->error);
            $this->assertSame('Fixture title', $obj->title);
            $this->assertSame($allowed ? [['src' => 'fixture.mp4']] : [], $obj->sources);
            $this->assertSame((int) $allowed, Fixture::$sourceCalls);
        } finally {
            $global = $previousGlobal;
            $_GET = $previousGet;
        }
    }

    /** @dataProvider sourceViewers */
    public function testAutoplayDoesNotBuildDeniedNextVideoSources($user, $video, $allowed): void
    {
        Fixture::$user = $user;
        $autoPlayVideo = ['id' => $video, 'filename' => 'fixture', 'url' => 'https://fixture/next'];
        $watch = file_get_contents(dirname(__DIR__, 2) . '/view/modeYoutube.php');
        $start = strpos($watch, '    if (!empty($autoPlayVideo) && !empty($autoPlayVideo[\'filename\'])');
        $end = strpos($watch, '    TimeLogEnd(', $start);
        eval('namespace ' . __NAMESPACE__ . ';' . substr($watch, $start, $end - $start));
        $this->assertSame($allowed ? [['src' => 'fixture.mp4']] : [], $autoPlaySources);
        $this->assertSame((int) $allowed, Fixture::$sourceCalls);
    }

    public static function sourceViewers(): array
    {
        return ['visitor' => [0, 3505, false], 'ppv only' => [82, 3505, false], 'expired' => [85, 3505, false],
            'member' => [83, 3505, true], 'unconfigured' => [0, 3306, true]];
    }

    public function testSavingARuleClearsSharedFeedAndCatalogCaches(): void
    {
        Fixture::$user = 1;
        PlaybackAccess::saveRule('video', 3306, 'require', [8]);
        $this->assertSame(1, Fixture::$cacheClears);
    }

    public function testRestrictionIsViewerIndependentAndFailsClosed(): void
    {
        Fixture::$user = 1; // even for an administrator: shared feed outputs must not include the title
        $this->assertTrue(PlaybackAccess::isRestricted(3505));
        $this->assertTrue(PlaybackAccess::isRestricted(3506));
        $this->assertFalse(PlaybackAccess::isRestricted(3306));
        Fixture::$rules['video:3505'] = ['mode' => 'unrestricted', 'groups_json' => '[]'];
        (new \ReflectionProperty(PlaybackAccess::class, 'rules'))->setValue(null, []);
        $this->assertFalse(PlaybackAccess::isRestricted(3505));
        Fixture::$fail = true;
        $this->assertTrue(PlaybackAccess::isRestricted(3306));
    }

    public function testNoMigrationKeepsFeedsUnchanged(): void
    {
        Fixture::$installed = false;
        $this->assertFalse(PlaybackAccess::isRestricted(3505));
    }

    public function testListedFilesKeepImagesButDropMediaForADeniedViewer(): void
    {
        $files = [
            'mp4_720' => ['type' => 'video', 'url' => 'a_720.mp4'],
            'm3u8' => ['type' => 'video', 'url' => 'a/index.m3u8'],
            'mp3' => ['type' => 'audio', 'url' => 'a.mp3'],
            'unknown' => ['url' => 'a.bin'],
            'jpg' => ['type' => 'image', 'url' => 'a.jpg'],
        ];
        $this->assertSame(['jpg'], array_keys(PlaybackAccess::filterPlayableFiles(3505, $files)));
        Fixture::$user = 83;
        $this->assertSame($files, PlaybackAccess::filterPlayableFiles(3505, $files));
        Fixture::$user = 0;
        $this->assertSame($files, PlaybackAccess::filterPlayableFiles(3306, $files));
        $this->assertSame([], PlaybackAccess::filterPlayableFiles(3505, []));
    }

    public function testSourceEmittingRoutesApplyTheRule(): void
    {
        $playbackRepoDir = dirname(__DIR__, 2) . '/';
        $playlist = file_get_contents($playbackRepoDir . 'objects/playlistsVideos.json.php');
        $this->assertMatchesRegularExpression('/\[\'VideoUrl\'\] = PlaybackAccess::filterPlayableFiles\(/', $playlist);
        $feed = file_get_contents($playbackRepoDir . 'feed/index.php');
        $this->assertLessThan(strpos($feed, "include \$global['systemRootPath'] . 'feed/roku.json.php'"), strpos($feed, 'PlaybackAccess::isRestricted('));
        $embed = file_get_contents($playbackRepoDir . 'view/videoEmbeded.php');
        $this->assertNotFalse(strpos($embed, 'PlaybackAccess::isAllowed($video[\'id\'])'));
        $this->assertLessThan(strpos($embed, 'getSources('), strpos($embed, 'PlaybackAccess::isAllowed($video[\'id\'])'));
        $watch = file_get_contents($playbackRepoDir . 'view/modeYoutube.php');
        $this->assertStringContainsString("PlaybackAccess::isRestricted(\$videos_id)\n    && (Video::allowFreePlayWithAdsIsEnabled() || PlaybackAccess::isAllowedByTitlePurchase(\$videos_id))", str_replace("\r\n", "\n", $watch));
        $this->assertStringNotContainsString('PlaybackAccess::getVideoRule(', $watch);
    }
}
