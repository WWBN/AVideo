<?php

namespace Tests\Security\ApiTagsFixtures;

class ApiTagsState
{
    public static $rows = [];
    public static $hidePrivateVideos = false;
    public static $isAdmin = false;
    public static $groupAllowed = [];
    public static $missingPaths = [];
    public static $pathLookups = [];
    public static $mp3Ids = [];
    public static $query;
    public static $imageMode = 'object';
}

class AVideoPlugin
{
    public static function loadPluginIfEnabled($name) { return new \stdClass(); }
}

class VideoTags
{
    public static function getAll($users_id) { return [['id' => 1, 'name' => 'news', 'subscription' => 1], ['id' => 2, 'name' => 'other', 'subscription' => null]]; }
}

class ImagesPlaceHolders
{
    public static $RETURN_URL = 1;
    public static function getVideoPlaceholder($type) { return 'placeholder.jpg'; }
}

class sqlDAL
{
    public static function readSql($sql, $formats, $values)
    {
        ApiTagsState::$query = $sql;
        if (!is_array(ApiTagsState::$rows)) {
            return ApiTagsState::$rows;
        }
        preg_match('/v.status IN \(([^)]+)\)/', $sql, $matches);
        $statuses = str_getcsv($matches[1], ',', "'");
        return array_values(array_filter(ApiTagsState::$rows, function ($row) use ($statuses) {
            return in_array($row['status'], $statuses, true);
        }));
    }
    public static function fetchAllAssoc($rows) { return $rows; }
    public static function close($result) {}
}

class Category
{
    public static function getCategory($id) { return ['id' => $id]; }
}

class User
{
    public static function getId() { return 99; }
    public static function isAdmin() { return ApiTagsState::$isAdmin; }
}

class VideoDependencies
{
    const SORT_TYPE_VIEWABLE = 'viewable';
    const SORT_TYPE_VIEWABLENOTUNLISTED = 'viewableNotUnlisted';
    public static function userGroupAndVideoGroupMatch($users_id, $videos_id) { return !empty(ApiTagsState::$groupAllowed[$videos_id]); }
    public static function getHigherVideoPathFromID($id)
    {
        ApiTagsState::$pathLookups[] = $id;
        if (count(ApiTagsState::$pathLookups) > 50) {
            throw new \RuntimeException('Playlist selection did not terminate');
        }
        return in_array($id, ApiTagsState::$missingPaths, true) ? false : 'media/' . $id . '.mp4';
    }
    public static function getTags($id) { return ['video_id' => $id]; }
    public static function getMediaSession($id) { return ['video_id' => $id]; }
    public static function getImageFromFilename_($filename, $type)
    {
        if (ApiTagsState::$imageMode === 'empty') {
            return [];
        }
        $images = ['poster' => $filename . '.jpg'];
        return ApiTagsState::$imageMode === 'array' ? $images : (object) $images;
    }
}

class ApiObject
{
    public $error;
    public $response;
    public function __construct($message = '', $error = true, $response = []) { $this->error = $error; $this->response = $response; }
}

function isToHidePrivateVideos() { return ApiTagsState::$hidePrivateVideos; }
function getSearchVar() { return ''; }
function convertVideoToMP3FileIfNotExists($id)
{
    ApiTagsState::$mp3Ids[] = $id;
    return 'media/' . $id . '.mp3';
}

// Runs the production API::get_api_tags() and cleanUpRowFromDatabase() bodies without the database.
$apiTagsExtract = function ($file, $signature) {
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
eval('namespace ' . __NAMESPACE__ . '; ' . $apiTagsExtract('objects/functions.php', 'function cleanUpRowFromDatabase(&$row)'));
eval('namespace ' . __NAMESPACE__ . '; ' . $apiTagsExtract('objects/functions.php', 'function parseDurationToSeconds($str)'));
eval('namespace ' . __NAMESPACE__ . '; ' . $apiTagsExtract('objects/functions.php', 'function durationToSeconds($str)'));
eval('namespace ' . __NAMESPACE__ . '; class TagsHasVideos {
    public static function isTableInstalled() { return true; }
    public static function getTableName() { return "tags_has_videos"; }
    ' . $apiTagsExtract('plugin/VideoTags/Objects/TagsHasVideos.php', 'static function getAllVideosFromTagsId(') . '
}');
eval('namespace ' . __NAMESPACE__ . '; class Video extends VideoDependencies {
    const STATUS_ACTIVE = "a", STATUS_ACTIVE_AND_ENCODING = "k", STATUS_FANS_ONLY = "f",
        STATUS_UNLISTED = "u", STATUS_UNLISTED_BUT_SEARCHABLE = "s", STATUS_SCHEDULED_RELEASE_DATE = "h";
    ' . $apiTagsExtract('objects/video.php', 'public static function getViewableStatus(') . '
}');
eval('namespace ' . __NAMESPACE__ . '; class PlayLists { ' . $apiTagsExtract('plugin/PlayLists/PlayLists.php', 'static function videosToPlaylist(') . ' }');
eval('namespace ' . __NAMESPACE__ . '; class API { ' . $apiTagsExtract('plugin/API/API.php', 'public function get_api_tags($parameters)') . ' }');
unset($apiTagsExtract);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\ApiTagsFixtures\API;
use Tests\Security\ApiTagsFixtures\ApiTagsState;

/**
 * GHSA-3w98-8958-84rh: APIName=tags returned the raw videos rows of subscribed tags (stored video_password, unlisted
 * videos, group-restricted videos even with hidePrivateVideos) and thv.* replaced the video id with the link row id.
 */
class ApiTagsVideoListingTest extends TestCase
{
    protected function setUp(): void
    {
        // the tags_has_videos row id (id) differs from the video id (videos_id), as with SELECT v.*, thv.*
        ApiTagsState::$rows = [
            ['id' => 50, 'videos_id' => 1, 'title' => 'public', 'video_password' => '', 'status' => 'a'],
            ['id' => 51, 'videos_id' => 2, 'title' => 'legacy password', 'video_password' => 'SuperSecret!', 'status' => 'a'],
            ['id' => 52, 'videos_id' => 3, 'title' => 'bcrypt password', 'video_password' => '$2y$10$abcdefghijklmnopqrstuv', 'status' => 'a'],
            ['id' => 53, 'videos_id' => 4, 'title' => 'group restricted', 'video_password' => '', 'status' => 'a'],
        ];
        ApiTagsState::$query = null;
        ApiTagsState::$imageMode = 'object';
        ApiTagsState::$missingPaths = ApiTagsState::$pathLookups = ApiTagsState::$mp3Ids = [];
        ApiTagsState::$hidePrivateVideos = false;
        ApiTagsState::$isAdmin = false;
        ApiTagsState::$groupAllowed = [1 => true, 2 => true, 3 => true, 4 => false];
        foreach (ApiTagsState::$rows as &$row) {
            $row += ['filename' => 'video_' . $row['videos_id'], 'duration' => '00:01:00', 'categories_id' => 7, 'type' => 'video'];
        }
        unset($row);
    }

    private function videos()
    {
        $result = (new API())->get_api_tags([]);
        $this->assertFalse($result->error);
        return $result->response[0]['videos']['videos'];
    }

    public function testStoredVideoPasswordIsNeverReturned()
    {
        $json = json_encode($this->videos());
        $this->assertStringNotContainsString('SuperSecret!', $json);
        $this->assertStringNotContainsString('$2y$', $json);
        $this->assertSame(['', '1', '1', ''], array_column($this->videos(), 'video_password'));
    }

    public function testUnlistedVideosAreNotRequested()
    {
        ApiTagsState::$rows[1]['status'] = 'u';
        ApiTagsState::$rows[2]['status'] = 's';
        $this->assertSame([1, 4], array_column($this->videos(), 'id'));
        $this->assertStringNotContainsString("'u'", ApiTagsState::$query);
    }

    public function testRowsKeepTheVideoId()
    {
        $this->assertSame([1, 2, 3, 4], array_column($this->videos(), 'id'));
    }

    public function testGroupRestrictedVideosFollowHidePrivateVideos()
    {
        $this->assertCount(4, $this->videos());
        ApiTagsState::$hidePrivateVideos = true;
        $this->assertSame([1, 2, 3], array_column($this->videos(), 'id'));
        ApiTagsState::$isAdmin = true;
        $this->assertCount(4, $this->videos());
    }

    public function testUnsubscribedTagsGetNoVideos()
    {
        $result = (new API())->get_api_tags([]);
        $this->assertSame([], $result->response[1]['videos']);
    }

    public function testFullyFilteredTagKeepsAnEmptyPlaylistEnvelopeWithoutWarnings()
    {
        ApiTagsState::$hidePrivateVideos = true;
        ApiTagsState::$groupAllowed = [];
        $tag = (new API())->get_api_tags([])->response[0];
        $this->assertSame([], $tag['videos']['videos']);
        $this->assertSame(0, $tag['videos']['index']);
        $this->assertSame(0, $tag['videos']['nextIndex']);
        $this->assertSame(0, $tag['videos']['videos_id']);
        $this->assertSame('', $tag['videos']['path']);
        $this->assertSame(0, $tag['videos']['percentage_progress']);
        $this->assertSame('placeholder.jpg', $tag['photo']);
    }

    public function testNoRowsAndUnlistedOnlyTagsKeepTheEmptyPlaylistContract()
    {
        foreach ([false, [], [array_replace(ApiTagsState::$rows[0], ['status' => 'u'])]] as $rows) {
            ApiTagsState::$rows = $rows;
            $tag = (new API())->get_api_tags(['index' => 9, 'audioOnly' => true])->response[0];
            $this->assertSame([], $tag['videos']['videos']);
            $this->assertSame(0, $tag['videos']['index']);
            $this->assertSame(0, $tag['videos']['duration_seconds']);
            $this->assertSame('placeholder.jpg', $tag['photo']);
        }
        $this->assertSame([], ApiTagsState::$mp3Ids);
    }

    public function testIndexIsNormalizedAfterTheListingShrinks()
    {
        ApiTagsState::$hidePrivateVideos = true;
        $playlist = (new API())->get_api_tags(['index' => 3])->response[0]['videos'];
        $this->assertSame(0, $playlist['index']);
        $this->assertSame(1, $playlist['nextIndex']);
        $this->assertSame(1, $playlist['videos_id']);
        $this->assertSame(0, $playlist['percentage_progress']);
    }

    public function testSelectionTerminatesWhenFallbackVideoHasNoPath()
    {
        ApiTagsState::$hidePrivateVideos = true;
        ApiTagsState::$missingPaths = [1];
        $playlist = (new API())->get_api_tags(['index' => 3])->response[0]['videos'];
        $this->assertSame([2, 3], array_column($playlist['videos'], 'id'));
        $this->assertSame(0, $playlist['index']);
        $this->assertSame(2, $playlist['videos_id']);
    }

    public function testRemovedSelectedVideoKeepsIndexesAlignedWithTheReturnedArray()
    {
        ApiTagsState::$missingPaths = [2];
        $playlist = (new API())->get_api_tags(['index' => 1])->response[0]['videos'];
        $this->assertSame([1, 3, 4], array_column($playlist['videos'], 'id'));
        $this->assertSame(1, $playlist['index']);
        $this->assertSame(2, $playlist['nextIndex']);
        $this->assertSame(3, $playlist['videos'][$playlist['index']]['id']);
        $this->assertSame(3, $playlist['videos_id']);
    }

    public function testAllUnavailableVideosLeaveNoSelectedVideo()
    {
        ApiTagsState::$missingPaths = [1, 2, 3, 4];
        $playlist = (new API())->get_api_tags(['index' => 2])->response[0]['videos'];
        $this->assertSame([], $playlist['videos']);
        $this->assertSame(0, $playlist['videos_id']);
        $this->assertSame(0, $playlist['nextIndex']);
        $this->assertSame('', $playlist['title']);
        $this->assertSame('', $playlist['path']);
    }

    public function testAudioAndMetadataUseVideoIdsAndTheFirstPoster()
    {
        $tag = (new API())->get_api_tags(['index' => 1, 'audioOnly' => true])->response[0];
        $this->assertSame([1, 2, 3, 4], ApiTagsState::$mp3Ids);
        $this->assertSame(1, $tag['videos']['index']);
        $this->assertSame(2, $tag['videos']['videos_id']);
        $this->assertSame('media/2.mp4', $tag['videos']['path']);
        $this->assertSame(['video_id' => 2], $tag['videos']['videos'][1]['media_session']);
        $this->assertSame('video_1.jpg', $tag['photo']);
    }

    public function testPhotoAcceptsImageObjectsAndArraysAndKeepsThePlaceholderWithoutAPoster()
    {
        foreach (['object', 'array', 'empty'] as $mode) {
            ApiTagsState::$imageMode = $mode;
            ApiTagsState::$pathLookups = [];
            $tag = (new API())->get_api_tags([])->response[0];
            $this->assertSame($mode === 'empty' ? 'placeholder.jpg' : 'video_1.jpg', $tag['photo']);
        }
    }
}
