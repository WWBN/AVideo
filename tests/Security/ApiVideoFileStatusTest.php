<?php

namespace Tests\Security\ApiVideoFileStatusFixtures;

class ApiVideoFileState
{
    public static $status = 'a';
    public static $password = '';
    public static $apiSecretValid = false;
    public static $isAdmin = false;
    public static $userId = 0;
    public static $canAdminVideos = false;
    public static $canModerate = false;
    public static $canWatch = true;
    public static $statuses = [];
    public static $mandatoryAllowed = true;
    public static $sourcesCalls = 0;
}

class VideoDependencies
{
    private $id;
    public function __construct($title, $filename, $id) { $this->id = $id; }
    public function getStatus() { return array_key_exists($this->id, ApiVideoFileState::$statuses) ? ApiVideoFileState::$statuses[$this->id] : ApiVideoFileState::$status; }
    public function getVideo_password() { return ApiVideoFileState::$password; }
    public function getFilename() { return 'v_fixture'; }
    public function getDuration_in_seconds() { return 60; }
    public function getTitle() { return 'Fixture'; }
    public static function getHigherVideoPathFromID($id) { return 'videos/v_fixture/v_fixture.mp4'; }
    public static function getImageFromFilename($filename, $type = '') { return (object) ['poster' => $filename . '.jpg', 'thumbsJpg' => $filename . '_thumbs.jpg']; }
    public static function getVideoLight($videos_id, $refreshCache = false) { return ['users_id' => 12, 'users_id_company' => 34]; }
    public static function getHigestResolution($filename) { return ['url' => 'private.mp4']; }
    public static function verifyVideoPassword($given, $stored) { return $given === $stored; }
}

class User
{
    public function __construct($id) {}
    public function getIsAdmin() { return ApiVideoFileState::$isAdmin; }
    public static function getId() { return ApiVideoFileState::$userId; }
    public static function isAdmin() { return ApiVideoFileState::$isAdmin; }
    public static function canWatchVideoWithAds($videos_id) { return ApiVideoFileState::$canWatch; }
}

class Permissions
{
    public static function canAdminVideos() { return ApiVideoFileState::$canAdminVideos; }
    public static function canModerateVideos() { return ApiVideoFileState::$canModerate; }
}

class ApiObject
{
    public $error;
    public $message;
    public $response;
    public function __construct($message = '', $error = true, $response = [])
    {
        $this->message = $message;
        $this->error = $error;
        $this->response = $response;
    }
}

function getSources($filename, $returnArray = false) {
    ApiVideoFileState::$sourcesCalls++;
    return $returnArray ? [['src' => 'videos/' . $filename . '/' . $filename . '.mp4']]
        : '<source src="videos/' . $filename . '/' . $filename . '.mp4">';
}

class AVideoPlugin
{
    public static function loadPluginIfEnabled($name) { return new CustomizeUser(); }
    public static function getDataObjectIfEnabled($name) { return new \stdClass(); }
}

class VideoHLS
{
    public static function getMP3ANDMP4DownloadLinks($videos_id) { return [['name' => 'MP4', 'url' => 'download.json.php?token=' . $videos_id]]; }
}

function method_exists($class, $method) { return \method_exists(__NAMESPACE__ . '\\' . $class, $method); }
function _error_log($message) {}

class CustomizeUser
{
    public function videoPasswordIsGood($id) { return ApiVideoFileState::$password === ''; }
    public static function canDownloadVideos() { return true; }
}

function class_exists($name) { return \class_exists(__NAMESPACE__ . '\\' . $name); }
function TimeLogStart($name) { return $name; }
function TimeLogEnd($name, $line, $limit) {}
function getResolutionFromFilename($src, $withP) { return '720'; }
function mime_content_type_per_filename($src) { return 'video/mp4'; }
// Same entry shape as getVideosURL_V2(): no videos_id key.
function getVideosURL($filename) { return ['mp4' => ['filename' => $filename . '.mp4', 'path' => '/videos/' . $filename . '.mp4', 'url' => 'private.mp4', 'url_noCDN' => 'private.mp4', 'type' => 'video', 'format' => 'mp4']]; }
function _json_decode($value) { return json_decode((string) $value); }
function parseDurationToSeconds($value) { return 0; }

class PlayListElement
{
    public $sources;
    public function __construct($title, $description, $duration, $sources) { $this->sources = $sources; }
}

// Runs the production API::get_api_video_file() and Video::getViewableStatus() bodies without the database.
$apiVideoFileExtract = function ($file, $signature) {
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
eval('namespace ' . __NAMESPACE__ . '; class Video extends VideoDependencies {
    const STATUS_ACTIVE = "a", STATUS_ACTIVE_AND_ENCODING = "k", STATUS_FANS_ONLY = "f",
        STATUS_UNLISTED = "u", STATUS_UNLISTED_BUT_SEARCHABLE = "s", STATUS_SCHEDULED_RELEASE_DATE = "h";
    public static $videoTypeEmbed = "embed", $videoTypeVideo = "video", $videoTypeAudio = "audio", $videoTypeSerie = "serie";
    ' . $apiVideoFileExtract('objects/video.php', 'public static function getViewableStatus(') . '
    ' . $apiVideoFileExtract('objects/video.php', 'public static function canViewStatus(') . '
    ' . $apiVideoFileExtract('objects/video.php', 'public static function canEdit(') . '
    ' . $apiVideoFileExtract('objects/video.php', 'public static function isOwner(') . '
}');
eval('namespace ' . __NAMESPACE__ . '; use stdClass; class API {
    public static function isAPISecretValid() { return ApiVideoFileState::$apiSecretValid; }
    private function startResponseObject($parameters) { return new stdClass(); }
    public static function filter(&$row, $parameters = [], $secret = false) { return self::filterVideoSources($row, $parameters, $secret); }
    public static function filterEnvelope(&$obj) { self::filterMandatoryPlaybackSources($obj); }
    ' . $apiVideoFileExtract('plugin/API/API.php', 'public function get_api_video_file($parameters)') . '
    ' . $apiVideoFileExtract('plugin/API/API.php', 'private static function filterVideoSources(') . '
    ' . $apiVideoFileExtract('plugin/API/API.php', 'private static function filterMandatoryPlaybackSources(') . '
}');
eval('namespace ' . __NAMESPACE__ . '; use stdClass; class PlaybackAccess {
    public static function isAllowed($id) { return ApiVideoFileState::$mandatoryAllowed; }
    ' . $apiVideoFileExtract('objects/PlaybackAccess.php', 'public static function filterPlayableFiles(') . '
    ' . $apiVideoFileExtract('objects/PlaybackAccess.php', 'public static function filterVideoRows(') . '
}');
$apiVideoFilePlaylistSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/PlayLists/PlayListElement.php');
$apiVideoFilePlaylistStart = strpos($apiVideoFilePlaylistSource, 'class playListSource');
$apiVideoFilePlaylistEnd = strpos($apiVideoFilePlaylistSource, 'class playListThumbnail', $apiVideoFilePlaylistStart);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($apiVideoFilePlaylistSource, $apiVideoFilePlaylistStart, $apiVideoFilePlaylistEnd - $apiVideoFilePlaylistStart));
unset($apiVideoFilePlaylistSource, $apiVideoFilePlaylistStart, $apiVideoFilePlaylistEnd);
// Issues the VideoHLS/CDN download tokens used by downloadButtons.php.
eval('namespace ' . __NAMESPACE__ . '; ' . $apiVideoFileExtract('objects/functions.php', 'function getMP3ANDMP4DownloadLinksFromHLS('));
// The playlist player builds its sources from getVideosURL() entries; run the real loop.
eval('namespace ' . __NAMESPACE__ . '; use stdClass; class PlayListPlayerFixture {
    public $videos = [];
    ' . $apiVideoFileExtract('plugin/PlayLists/PlayLists.php', 'public function getPlayListData()') . '
}');
unset($apiVideoFileExtract);

function renderSeoSources()
{
    $videos_id = 101;
    $seoSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/PlayerSkins/seo.php');
    $seoStart = strpos($seoSource, '$video = new Video');
    $seoEnd = strpos($seoSource, '$keywords =', $seoStart);
    eval('namespace ' . __NAMESPACE__ . '; ' . substr($seoSource, $seoStart, $seoEnd - $seoStart));
    $seoRenderStart = strpos($seoSource, 'if ($canWatchThisVideo && $videoPasswordOk)');
    $seoRenderEnd = strpos($seoSource, '?>', $seoRenderStart);
    ob_start();
    eval('namespace ' . __NAMESPACE__ . '; ' . substr($seoSource, $seoRenderStart, $seoRenderEnd - $seoRenderStart));
    return ob_get_clean();
}

function openGraphSource()
{
    $videos_id = 101;
    $video = ['filename' => 'v_fixture'];
    $ogSource = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsOpenGraph.php');
    $ogFunction = strpos($ogSource, 'function getOpenGraphVideo(');
    $ogStart = strpos($ogSource, '$sourceFileURL =', $ogFunction);
    $ogEnd = strpos($ogSource, '$videoType =', $ogStart);
    eval('namespace ' . __NAMESPACE__ . '; ' . substr($ogSource, $ogStart, $ogEnd - $ogStart));
    return $sourceFileURL;
}

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\ApiVideoFileStatusFixtures\API;
use Tests\Security\ApiVideoFileStatusFixtures\ApiVideoFileState;
use Tests\Security\ApiVideoFileStatusFixtures\PlaybackAccess;
use Tests\Security\ApiVideoFileStatusFixtures\playListSource;
use function Tests\Security\ApiVideoFileStatusFixtures\renderSeoSources;
use function Tests\Security\ApiVideoFileStatusFixtures\openGraphSource;

/**
 * GHSA-m3c9-p37h-4mh3: APIName=video_file returned the filename and playable sources of scheduled (h),
 * unpublished (p) and draft (c) videos to anonymous callers, although the watch page refuses them.
 */
class ApiVideoFileStatusTest extends TestCase
{
    protected function setUp(): void
    {
        ApiVideoFileState::$status = 'a';
        ApiVideoFileState::$password = '';
        ApiVideoFileState::$apiSecretValid = false;
        ApiVideoFileState::$isAdmin = false;
        ApiVideoFileState::$userId = 0;
        ApiVideoFileState::$canAdminVideos = false;
        ApiVideoFileState::$canModerate = false;
        ApiVideoFileState::$canWatch = true;
        ApiVideoFileState::$statuses = [];
        ApiVideoFileState::$mandatoryAllowed = true;
        ApiVideoFileState::$sourcesCalls = 0;
    }

    private function call($parameters = [])
    {
        return (new API())->get_api_video_file($parameters + ['videos_id' => 101]);
    }

    public function nonPublicStatusProvider()
    {
        return [['h'], ['p'], ['c'], ['e'], ['x'], ['d'], ['t'], ['b'], ['r'], ['i'], [null], ['']];
    }

    /**
     * @dataProvider nonPublicStatusProvider
     */
    public function testAnonymousCallerIsRefusedForNonPublicStatus($status)
    {
        ApiVideoFileState::$status = $status;
        $result = $this->call();
        $this->assertTrue($result->error);
        $this->assertSame('You cannot watch this video', $result->message);
        $this->assertStringNotContainsString('v_fixture', json_encode($result));
    }

    public function publicStatusProvider()
    {
        return [['a'], ['k'], ['f'], ['u'], ['s']];
    }

    /**
     * @dataProvider publicStatusProvider
     */
    public function testPublicAndUnlistedVideosStillReturnTheirSources($status)
    {
        ApiVideoFileState::$status = $status;
        $result = $this->call();
        $this->assertFalse($result->error);
        $this->assertSame('v_fixture', $result->response->filename);
        $this->assertNotEmpty($result->response->sources);
    }

    public function testEditorsAndModeratorsStillGetTheFilesOfTheirScheduledVideo()
    {
        ApiVideoFileState::$status = 'h';
        ApiVideoFileState::$userId = 12;
        $this->assertFalse($this->call()->error);

        ApiVideoFileState::$userId = 99;
        ApiVideoFileState::$canModerate = true;
        $this->assertFalse($this->call()->error);
    }

    public function testApiSecretCallersAreUnchanged()
    {
        ApiVideoFileState::$status = 'c';
        ApiVideoFileState::$apiSecretValid = true;
        ApiVideoFileState::$canWatch = false;
        $this->assertFalse($this->call()->error);
    }

    public function testExistingWatchAndPasswordChecksStillApply()
    {
        ApiVideoFileState::$canWatch = false;
        $this->assertSame('You cannot watch this video', $this->call()->message);

        ApiVideoFileState::$canWatch = true;
        ApiVideoFileState::$password = 'fixture-pass';
        $this->assertSame('Video password required', $this->call()->message);
        $this->assertFalse($this->call(['video_password' => 'fixture-pass'])->error);
    }

    public function testEditorStillNeedsTheWatchCheckForAPublicVideo()
    {
        ApiVideoFileState::$userId = 12;
        ApiVideoFileState::$canWatch = false;
        $this->assertTrue($this->call()->error);
    }

    public function testStaffAndCompanyAffiliateUseRealEditAndOwnershipChecks()
    {
        ApiVideoFileState::$status = 'c';
        ApiVideoFileState::$userId = 34;
        $this->assertFalse($this->call()->error);
        ApiVideoFileState::$userId = 99;
        $this->assertTrue($this->call()->error);
        ApiVideoFileState::$canAdminVideos = true;
        $this->assertFalse($this->call()->error);
        ApiVideoFileState::$canAdminVideos = false;
        ApiVideoFileState::$isAdmin = true;
        $this->assertFalse($this->call()->error);
    }

    /** @dataProvider nonPublicStatusProvider */
    public function testSeoDoesNotCallSourcesForNonPublicVideos($status)
    {
        ApiVideoFileState::$status = $status;
        $this->assertSame('', renderSeoSources());
        $this->assertSame(0, ApiVideoFileState::$sourcesCalls);
    }

    public function testSeoKeepsOwnerPreviewAndExistingWatchAndPasswordChecks()
    {
        ApiVideoFileState::$status = 'h';
        ApiVideoFileState::$userId = 12;
        renderSeoSources();
        $this->assertSame(1, ApiVideoFileState::$sourcesCalls);
        ApiVideoFileState::$canWatch = false;
        renderSeoSources();
        $this->assertSame(1, ApiVideoFileState::$sourcesCalls);
        ApiVideoFileState::$canWatch = true;
        ApiVideoFileState::$password = 'protected';
        renderSeoSources();
        $this->assertSame(1, ApiVideoFileState::$sourcesCalls);
    }

    private function mediaRow($id)
    {
        $files = [
            'jpg' => ['type' => 'image', 'url' => 'poster.jpg'],
            'mp4' => ['type' => 'video', 'url' => 'private.mp4'],
        ];
        return ['id' => $id, 'filename' => 'v_fixture', 'status' => 'a', 'title' => 'Retained metadata',
            'videos' => ['mp4' => 'private.mp4'], 'sources' => [['src' => 'private.mp4']],
            'VideoUrl' => $files, 'videosURL' => $files, 'firstVideo' => 'private.mp4',
            'videoLink' => 'private.mp4', 'videoDownloadedLink' => 'private.mp4', 'mp3' => 'private.mp3'];
    }

    /** @dataProvider nonPublicStatusProvider */
    public function testPlaylistResponsesFilterCurrentStatusAfterReadingCachedRows($status)
    {
        ApiVideoFileState::$statuses = [101 => $status];
        $row = $this->mediaRow(101); // The cached status is deliberately still public.
        $result = PlaybackAccess::filterVideoRows([$row], true)[0];
        $this->assertSame('Retained metadata', $result['title']);
        $this->assertStringNotContainsString('private.mp', json_encode($result));
        $this->assertSame(['jpg'], array_keys($result['VideoUrl']));
        $this->assertSame([$row], PlaybackAccess::filterVideoRows([$row])); // Legacy internal callers unchanged.
    }

    public function testPlaylistStatusFilteringPropagatesToNestedRowsAndObjects()
    {
        ApiVideoFileState::$statuses = [101 => 'c'];
        $row = $this->mediaRow(102);
        $row['playlist'] = [(object) $this->mediaRow(101)];
        $row['relatedVideos'] = [$this->mediaRow(101)];
        $row['next_video'] = $this->mediaRow(101);
        $result = PlaybackAccess::filterVideoRows((object) ['entry' => (object) $row], true);
        $this->assertSame($row['sources'], $result->entry->sources);
        $this->assertSame([], $result->entry->playlist[0]->sources);
        $this->assertSame([], $result->entry->relatedVideos[0]['sources']);
        $this->assertSame([], $result->entry->next_video['sources']);
    }

    /** @dataProvider publicStatusProvider */
    public function testPlaylistPublicStatusesAndMandatoryRulesRemainEffective($status)
    {
        ApiVideoFileState::$status = $status;
        $row = $this->mediaRow(101);
        $this->assertSame([$row], PlaybackAccess::filterVideoRows([$row], true));
        ApiVideoFileState::$mandatoryAllowed = false;
        $this->assertSame([], PlaybackAccess::filterVideoRows([$row], true)[0]['sources']);
    }

    public function testPlaylistOwnerAndModeratorPreviewRemainAvailable()
    {
        ApiVideoFileState::$status = 'h';
        $row = $this->mediaRow(101);
        ApiVideoFileState::$userId = 12;
        $this->assertSame([$row], PlaybackAccess::filterVideoRows([$row], true));
        ApiVideoFileState::$userId = 99;
        ApiVideoFileState::$canModerate = true;
        $this->assertSame([$row], PlaybackAccess::filterVideoRows([$row], true));
    }

    public function testLiveRowsDoNotUseAnUnrelatedVodStatus()
    {
        ApiVideoFileState::$statuses = [101 => 'c'];
        $live = ['id' => 101, 'status' => 'a', 'videos' => ['m3u8' => 'live.m3u8']];
        $this->assertSame([$live], PlaybackAccess::filterVideoRows([$live], true));
    }

    public function testApiFiltersNestedPlaylistSourcesOnFreshAndCachedResponses()
    {
        ApiVideoFileState::$statuses = [101 => 'h'];
        $row = $this->mediaRow(101);
        $this->assertFalse(API::filter($row)); // Stops the caller from generating an MP3.
        $this->assertSame([], $row['sources']);
        $row = $this->mediaRow(101);
        $this->assertTrue(API::filter($row, [], true));
        $this->assertNotEmpty($row['sources']);

        $series = $this->mediaRow(102);
        $series['playlist'] = [$this->mediaRow(101)];
        foreach (['rows', 'videos'] as $field) {
            $envelope = (object) [$field => [$series]];
            API::filterEnvelope($envelope);
            $this->assertSame([], $envelope->$field[0]['playlist'][0]['sources']);
            $this->assertNotEmpty($envelope->$field[0]['sources']);
        }
    }

    public function testPlaylistJsonEndpointsOptIntoStatusFiltering()
    {
        foreach (['objects/playlistsFromUser.json.php', 'objects/playlists.json.php', 'objects/playlistsVideos.json.php'] as $file) {
            $source = file_get_contents(dirname(__DIR__, 2) . '/' . $file);
            $this->assertMatchesRegularExpression('/PlaybackAccess::filterVideoRows\([^;]+, true\)/', $source, $file);
        }
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/API/API.php');
        $this->assertSame(2, substr_count($source, "PlaybackAccess::filterVideoRows(\$row[\$key]['videos'], true)"));
    }

    /** @dataProvider nonPublicStatusProvider */
    public function testOpenGraphAndPlaylistPlayerDoNotExposeNonPublicSources($status)
    {
        ApiVideoFileState::$status = $status;
        $this->assertSame('', openGraphSource());
        $this->assertSame('', (new playListSource('private.mp4', 101))->src);
    }

    private function playListPlayerSources($rows)
    {
        unset($GLOBALS['playListData'], $GLOBALS['messagesFromPlayList']);
        $player = new \Tests\Security\ApiVideoFileStatusFixtures\PlayListPlayerFixture();
        $player->videos = $rows;
        $sources = [];
        foreach ($player->getPlayListData() as $element) {
            $sources[] = implode(',', array_column($element->sources, 'src'));
        }
        return $sources;
    }

    private function playListRow($id, $type = 'video')
    {
        return ['id' => $id, 'videos_id' => $id, 'type' => $type, 'filename' => 'v_fixture', 'title' => 'T',
            'description' => '', 'duration' => '', 'externalOptions' => '{}', 'videoLink' => 'https://external.example/embed'];
    }

    public function testPlaylistPlayerChecksTheRowVideoBecauseSourceEntriesHaveNoVideosId()
    {
        // As in production, Video(0) has no status: a source checked as video 0 would be refused for everyone.
        ApiVideoFileState::$statuses = [0 => null, 101 => 'a', 102 => 'h', 103 => 'a'];
        $sources = $this->playListPlayerSources([$this->playListRow(101), $this->playListRow(102), $this->playListRow(103, 'embed')]);
        $this->assertSame(['private.mp4', '', 'https://external.example/embed'], $sources);

        ApiVideoFileState::$userId = 12; // owner preview of the scheduled video
        $this->assertSame('private.mp4', $this->playListPlayerSources([$this->playListRow(102)])[0]);
    }

    /** @dataProvider nonPublicStatusProvider */
    public function testDownloadTokensAreNotIssuedForNonPublicVideos($status)
    {
        ApiVideoFileState::$status = $status;
        $this->assertSame([], \Tests\Security\ApiVideoFileStatusFixtures\getMP3ANDMP4DownloadLinksFromHLS(101, 'video'));
    }

    public function testDownloadTokensRemainForPublicVideosAndOwnerPreview()
    {
        $this->assertCount(1, \Tests\Security\ApiVideoFileStatusFixtures\getMP3ANDMP4DownloadLinksFromHLS(101, 'video'));
        ApiVideoFileState::$status = 'h';
        ApiVideoFileState::$userId = 12;
        $this->assertCount(1, \Tests\Security\ApiVideoFileStatusFixtures\getMP3ANDMP4DownloadLinksFromHLS(101, 'video'));
    }

    public function testPlaylistEmbedEntryDoesNotReuseThePreviousVideoFiles()
    {
        ApiVideoFileState::$statuses = [0 => null, 102 => 'h', 103 => 'a'];
        $sources = $this->playListPlayerSources([$this->playListRow(102), $this->playListRow(103, 'embed')]);
        $this->assertSame(['', 'https://external.example/embed'], $sources);
    }

    public function testPlaylistEmbedPagePassesTheRowVideoIdToPlaylistSources()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PlayLists/embed.php');
        $this->assertSame(0, substr_count($source, "\$value2['videos_id']"));
        $this->assertSame(2, substr_count($source, "new playListSource(\$value2['url'], \$value['videos_id'])"));
    }

    public function testOpenGraphAndPlaylistPlayerPreservePreviewAndPublicPlayback()
    {
        $this->assertSame('private.mp4', openGraphSource());
        $this->assertSame('private.mp4', (new playListSource('private.mp4', 101))->src);
        ApiVideoFileState::$status = 'h';
        ApiVideoFileState::$userId = 12;
        $this->assertSame('private.mp4', openGraphSource());
        $this->assertSame('private.mp4', (new playListSource('private.mp4', 101))->src);
        ApiVideoFileState::$userId = 99;
        ApiVideoFileState::$apiSecretValid = true;
        $this->assertSame('private.mp4', (new playListSource('private.mp4', 101))->src);
    }
}
