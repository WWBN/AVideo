<?php

namespace Tests\Security\ApiVideoFromProgramFixtures;

class ApiVideoFromProgramState
{
    public static $rows = [];
    public static $canSee = true;
    public static $forbidden = false;
    public static $relatedRows = [];
    public static $extraRelatedRows = [];
    public static $cache = [];
    public static $lastCacheSuffix;
    public static $lastSectionURL;
}

class ForbiddenPageException extends \RuntimeException
{
}

class CacheReadException extends \RuntimeException
{
}

class ObjectYPT
{
    public static function getCacheGlobal($name, $lifetime) { throw new CacheReadException($name); }
}

class AVideoPlugin
{
    public static function loadPlugin($name) { return new \stdClass(); }
    public static function isEnabledByName($name) { return false; }
    public static function getVideoWhereClause() { return ''; }
}

class sqlDAL
{
    public static function readSql($sql, $formats = '', $values = [])
    {
        if (strpos($sql, 'SELECT * FROM videos v') === 0) {
            return strpos($sql, 'categories_id !=') !== false
                ? ApiVideoFromProgramState::$extraRelatedRows : ApiVideoFromProgramState::$relatedRows;
        }
        preg_match('/v.status IN \(([^)]+)\)/', $sql, $matches);
        $statuses = str_getcsv($matches[1], ',', "'");
        return array_values(array_filter(ApiVideoFromProgramState::$rows, function ($row) use ($statuses) {
            return in_array($row['status'], $statuses, true);
        }));
    }
    public static function fetchAllAssoc($rows) { return $rows; }
    public static function close($result) {}
}

class PlayList
{
    public function __construct($id) {}
    public static function canSee($playlists_id, $users_id) { return ApiVideoFromProgramState::$canSee; }
    public function getNameOrSerieTitle() { return 'Program'; }
    public function getModified() { return '2026-11-13 00:00:00'; }
    public function getUsers_id() { return 5; }
}

class User
{
    public function __construct($id) {}
    public static function getId() { return 0; }
    public static function isLogged() { return false; }
    public static function isAdmin() { return false; }
    public function getChannelName() { return 'channel'; }
    public function getPhotoDB() { return 'photo.jpg'; }
    public function getBackground() { return 'bg.jpg'; }
    public function getChannelLink() { return 'channel/link'; }
}

class Category
{
    public static function getCategory($id) { return ['id' => $id]; }
}

class VideoDependencies
{
    public static function getHigherVideoPathFromID($id) { return 'media/' . $id . '.mp4'; }
    public static function getTags($id) { return []; }
    public static function getMediaSession($id) { return []; }
    public static function getImageFromFilename_($filename, $type) { return (object) ['poster' => $filename . '.jpg']; }
    public static function getVideoLight($id) { return ['id' => $id, 'categories_id' => 7, 'serie_playlists_id' => 0]; }
    public static function getImageFromFilename($filename) { return ['poster' => $filename . '.jpg']; }
    public static function getBlankExternalOptions() { return ['videoStartSeconds' => '00:00:00']; }
    public static function getUserGroupsCanSeeSQL($alias) { return ''; }
}

class CacheHandler
{
    public function getCache($suffix, $lifetime = 60, $logInfo = false)
    {
        ApiVideoFromProgramState::$lastCacheSuffix = $suffix;
        return ApiVideoFromProgramState::$cache[$suffix] ?? null;
    }
}

class ApiObject
{
    public $error;
    public $response;
    public function __construct($message = '', $error = true, $response = []) { $this->error = $error; $this->response = $response; }
}

function __($text) { return $text; }
function forbiddenPage($message = '')
{
    ApiVideoFromProgramState::$forbidden = true;
    throw new ForbiddenPageException($message);
}
function getSearchVar() { return ''; }
function cleanSearchVar() {}
function reloadSearchVar() {}
function convertVideoToMP3FileIfNotExists($id) { return 'media/' . $id . '.mp3'; }
function isForKidsSet() { return false; }
function getRequestUniqueString() { return 'fixture-request'; }
function getVideoListVisibilityCacheKey() { return 'fixture-visibility'; }
function addQueryStringParameter($url, $name, $value)
{
    return $url . (strpos($url, '?') === false ? '?' : '&') . $name . '=' . urlencode($value);
}
function url_get_contents_with_cache($url, $lifeTime, $ctx, $timeout, $debug, $maintainSession)
{
    ApiVideoFromProgramState::$lastSectionURL = $url;
    return ApiVideoFromProgramState::$cache[$url] ?? json_encode(['response' => ['videos' => [], 'totalRows' => 0]]);
}

// Runs the production API::get_api_video_from_program() body and its PlayLists helpers without the database.
$apiVideoFromProgramExtract = function ($file, $signature) {
    $source = file_get_contents(dirname(__DIR__, 2) . '/' . $file);
    $start = strpos($source, $signature);
    if ($start === false) {
        throw new \RuntimeException('Production method not found: ' . $signature);
    }
    $depth = 0;
    for ($index = strpos($source, '{', $start); $index < strlen($source); $index++) {
        if ($source[$index] === '{') {
            $depth++;
        } elseif ($source[$index] === '}' && --$depth === 0) {
            break;
        }
    }
    return substr($source, $start, $index - $start + 1);
};
eval('namespace ' . __NAMESPACE__ . '; ' . $apiVideoFromProgramExtract('objects/functions.php', 'function cleanUpRowFromDatabase(&$row)'));
eval('namespace ' . __NAMESPACE__ . '; ' . $apiVideoFromProgramExtract('objects/functions.php', 'function parseDurationToSeconds($str)'));
eval('namespace ' . __NAMESPACE__ . '; ' . $apiVideoFromProgramExtract('objects/functions.php', 'function durationToSeconds($str)'));
eval('namespace ' . __NAMESPACE__ . '; class Video extends VideoDependencies {
    const STATUS_ACTIVE = "a", STATUS_ACTIVE_AND_ENCODING = "k", STATUS_FANS_ONLY = "f",
        STATUS_UNLISTED = "u", STATUS_UNLISTED_BUT_SEARCHABLE = "s", STATUS_SCHEDULED_RELEASE_DATE = "h";
    ' . $apiVideoFromProgramExtract('objects/video.php', 'public static function getViewableStatus(') . '
    ' . $apiVideoFromProgramExtract('objects/video.php', 'public static function getRelatedMovies(') . '
}');
// Exercise the real catalog key and lookup, with an in-memory cache backend.
$apiVideoFromProgramCacheSource = file_get_contents(dirname(__DIR__, 2) . '/objects/Object.php');
$apiVideoFromProgramCacheStart = strpos($apiVideoFromProgramCacheSource, 'class VideosListCacheHandler');
$apiVideoFromProgramCacheEnd = strpos($apiVideoFromProgramCacheSource, 'class VideoCacheHandler', $apiVideoFromProgramCacheStart);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($apiVideoFromProgramCacheSource, $apiVideoFromProgramCacheStart, $apiVideoFromProgramCacheEnd - $apiVideoFromProgramCacheStart));
unset($apiVideoFromProgramCacheSource, $apiVideoFromProgramCacheStart, $apiVideoFromProgramCacheEnd);
eval('namespace ' . __NAMESPACE__ . '; class PlayLists {
    ' . $apiVideoFromProgramExtract('plugin/PlayLists/PlayLists.php', 'static function getOnlyVideosAndAudioIDFromPlaylistLight(') . '
    ' . $apiVideoFromProgramExtract('plugin/PlayLists/PlayLists.php', 'static function videosToPlaylist(') . '
}');
eval('namespace ' . __NAMESPACE__ . '; class API { '
    . $apiVideoFromProgramExtract('plugin/API/API.php', 'public function get_api_video_from_program($parameters)')
    . $apiVideoFromProgramExtract('plugin/API/API.php', 'public function get_api_audio_from_program($parameters)') . ' }');
eval('namespace ' . __NAMESPACE__ . '; use stdClass; class SectionFirstPage { '
    . $apiVideoFromProgramExtract('plugin/API/API.php', 'public function __construct($type, $title, $endpoint, $rowCount, $childs = array())') . '
    public $type, $title, $endpoint, $nextEndpoint, $rowCount, $endpointResponse, $totalRows, $childs, $executionTime;
}');
eval('namespace ' . __NAMESPACE__ . '; use stdClass; class YouPHPFlix2 { '
    . $apiVideoFromProgramExtract('plugin/YouPHPFlix2/YouPHPFlix2.php', 'static function API_get_firstPage($parameters)') . ' }');
unset($apiVideoFromProgramExtract);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\ApiVideoFromProgramFixtures\API;
use Tests\Security\ApiVideoFromProgramFixtures\ApiVideoFromProgramState;
use Tests\Security\ApiVideoFromProgramFixtures\ForbiddenPageException;
use Tests\Security\ApiVideoFromProgramFixtures\Video;
use Tests\Security\ApiVideoFromProgramFixtures\VideosListCacheHandler;
use Tests\Security\ApiVideoFromProgramFixtures\SectionFirstPage;
use Tests\Security\ApiVideoFromProgramFixtures\YouPHPFlix2;
use Tests\Security\ApiVideoFromProgramFixtures\CacheReadException;
use function Tests\Security\ApiVideoFromProgramFixtures\addQueryStringParameter;

/**
 * GHSA-7qrc-6vhr-4rhj: APIName=video_from_program with playlists_id returned the raw `SELECT *` videos rows of the
 * playlist, including the stored video_password (bcrypt hash or legacy plaintext), to anonymous callers.
 */
class ApiVideoFromProgramPasswordTest extends TestCase
{
    protected function setUp(): void
    {
        ApiVideoFromProgramState::$rows = [
            ['playlists_id' => 9, 'videos_id' => 1, 'id' => 1, 'title' => 'public', 'video_password' => '', 'status' => 'a'],
            ['playlists_id' => 9, 'videos_id' => 2, 'id' => 2, 'title' => 'legacy password', 'video_password' => 'SuperSecret!', 'status' => 'a'],
            ['playlists_id' => 9, 'videos_id' => 3, 'id' => 3, 'title' => 'bcrypt password', 'video_password' => '$2y$10$abcdefghijklmnopqrstuv', 'status' => 'u'],
        ];
        foreach (ApiVideoFromProgramState::$rows as &$row) {
            $row += ['filename' => 'video_' . $row['id'], 'duration' => '00:01:00', 'categories_id' => 7, 'type' => 'video'];
        }
        unset($row);
        ApiVideoFromProgramState::$canSee = true;
        ApiVideoFromProgramState::$forbidden = false;
        ApiVideoFromProgramState::$cache = [];
    }

    private function response()
    {
        $result = (new API())->get_api_video_from_program(['playlists_id' => 9]);
        $this->assertFalse($result->error);
        return $result->response;
    }

    public function testStoredVideoPasswordIsNeverReturned()
    {
        $json = json_encode($this->response());
        $this->assertStringNotContainsString('SuperSecret!', $json);
        $this->assertStringNotContainsString('$2y$', $json);
    }

    public function testPasswordProtectedVideosAreStillListedWithThePresenceFlag()
    {
        $videos = $this->response()['videos'];
        $this->assertSame([1, 2, 3], array_column($videos, 'id'));
        $this->assertSame(['', '1', '1'], array_column($videos, 'video_password'));
    }

    public function testPlaylistMetadataIsUnchanged()
    {
        $response = $this->response();
        $this->assertSame('Program', $response['playlist_name']);
        $this->assertSame(5, $response['users_id']);
        $this->assertSame(1, $response['videos_id']);
        $this->assertSame('media/1.mp4', $response['path']);
        $this->assertSame(180, $response['totalPlaylistDuration']);
    }

    public function testPlaylistTheCallerCannotSeeIsStillForbidden()
    {
        ApiVideoFromProgramState::$canSee = false;
        try {
            (new API())->get_api_video_from_program(['playlists_id' => 9]);
            $this->fail('forbiddenPage() was not called');
        } catch (ForbiddenPageException $e) {
            $this->assertTrue(ApiVideoFromProgramState::$forbidden);
        }
    }

    public function testOtherPasswordColumnsAreRemovedBeforeAudioPlaylistEnrichment()
    {
        ApiVideoFromProgramState::$rows[1]['encoderPassword'] = 'fixture-secret';
        $response = (new API())->get_api_audio_from_program(['playlists_id' => 9, 'index' => 1])->response;
        $this->assertArrayNotHasKey('encoderPassword', $response['videos'][1]);
        $this->assertSame('1', $response['videos'][1]['video_password']);
        $this->assertSame('media/2.mp3', $response['videos'][1]['mp3']);
        $this->assertSame(1, $response['index']);
        $this->assertSame(2, $response['nextIndex']);
        $this->assertSame(60, $response['currentPlaylistTime']);
        $this->assertSame(180, $response['totalPlaylistDuration']);
        $this->assertSame('media/2.mp4', $response['path']);
    }

    public function testRelatedVideoQueriesNeverReturnStoredPasswords()
    {
        ApiVideoFromProgramState::$relatedRows = [ApiVideoFromProgramState::$rows[1]];
        ApiVideoFromProgramState::$extraRelatedRows = [ApiVideoFromProgramState::$rows[2]];
        ApiVideoFromProgramState::$extraRelatedRows[0]['encoderPassword'] = 'fixture-secret';
        $rows = Video::getRelatedMovies(50, 10, false);
        $this->assertSame([2, 3], array_column($rows, 'id'));
        $this->assertSame(['1', '1'], array_column($rows, 'video_password'));
        $this->assertArrayNotHasKey('encoderPassword', $rows[1]);
        $this->assertSame('legacy password', $rows[0]['title']);
        $this->assertSame(['poster' => 'video_2.jpg'], $rows[0]['images']);
        $this->assertSame('SuperSecret!', ApiVideoFromProgramState::$relatedRows[0]['video_password']);
    }

    public function testCatalogDoesNotReuseSnapshotsContainingRawRelatedPasswords()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/Object.php');
        $this->assertSame(1, preg_match('/\$cacheParameters = (array\(.*?\));/s', $source, $matches));
        $parameters = eval('return ' . $matches[1] . ';');
        $legacyVars = ['users_id' => 0, 'requestUniqueString' => 'fixture-request',
            'listingVisibility' => 'fixture-visibility', 'tagFormatVersion' => 2];
        foreach ($parameters as $name) {
            $legacyVars[$name] = $_REQUEST[$name] ?? null;
        }
        ApiVideoFromProgramState::$cache[md5(json_encode($legacyVars))] = (object) [
            'rows' => [['id' => 50, 'relatedVideos' => ApiVideoFromProgramState::$rows]],
        ];
        $this->assertNull((new VideosListCacheHandler())->getCacheWithAutoSuffix(3600));
        $currentSnapshot = (object) ['rows' => [['id' => 50, 'relatedVideos' => [['video_password' => '1']]]]];
        ApiVideoFromProgramState::$cache[ApiVideoFromProgramState::$lastCacheSuffix] = $currentSnapshot;
        $this->assertSame($currentSnapshot, (new VideosListCacheHandler())->getCacheWithAutoSuffix(3600));
    }

    public function testSectionsDoNotReuseHttpSnapshotsContainingRawPlaylistPasswords()
    {
        $endpoint = 'https://fixture/plugin/API/get.json.php?APIName=video_from_program&playlists_id=9';
        $legacyURL = $endpoint;
        foreach (['current' => 1, 'videoType' => 'audio_and_video_and_serie', 'noRelated' => 1,
            'rowCount' => 10, '_listingContext' => 'fixture-visibility'] as $name => $value) {
            $legacyURL = addQueryStringParameter($legacyURL, $name, $value);
        }
        ApiVideoFromProgramState::$cache[$legacyURL] = json_encode(['response' => [
            'videos' => ApiVideoFromProgramState::$rows, 'totalRows' => 3,
        ]]);
        $section = new SectionFirstPage('PlayList', 'Program', $endpoint, 10);
        $this->assertNotSame($legacyURL, ApiVideoFromProgramState::$lastSectionURL);
        $this->assertSame([], $section->endpointResponse->videos);
        $this->assertSame(addQueryStringParameter(addQueryStringParameter(addQueryStringParameter(
            $endpoint, 'current', 1), 'videoType', 'audio_and_video_and_serie'), 'noRelated', 1), $section->endpoint);
    }

    public function testFlixFirstPageDoesNotReuseSnapshotsContainingRawPlaylistPasswords()
    {
        $parameters = ['APIName' => 'firstPage'];
        $legacyKey = 'YouPHPFlix2_API_get_firstPage_' . md5(json_encode($parameters)) . '_fixture-visibility';
        try {
            YouPHPFlix2::API_get_firstPage($parameters);
            $this->fail('The cache lookup was not exercised');
        } catch (CacheReadException $read) {
            $this->assertNotSame($legacyKey, $read->getMessage());
        }
    }
}
