<?php

namespace Tests\Security\ApiLiveRowUserFieldsFixtures;

class Video
{
    public static $videoTypeSerie = 'serie';
}

class User
{
    public static function getId() { return 0; }
}

class CacheHandler
{
    public static $snapshots = [];
    protected $suffix;

    public function getCache($suffix, $lifetime = 60, $logInfo = false)
    {
        $this->suffix = $suffix;
        return self::$snapshots[$suffix] ?? null;
    }

    public function setCache($value)
    {
        self::$snapshots[$this->suffix] = $value;
    }
}

function getRequestUniqueString() { return 'fixture-request'; }
function getVideoListVisibilityCacheKey() { return 'fixture-visibility'; }

function object_to_array($value)
{
    return json_decode(json_encode($value), true);
}

$apiLiveRowExtract = function ($file, $signature, $endSignature = null, $from = null) {
    $source = file_get_contents(dirname(__DIR__, 2) . '/' . $file);
    $offset = $from === null ? 0 : strpos($source, $from);
    $start = strpos($source, $signature, $offset);
    if ($offset === false || $start === false) {
        throw new \RuntimeException('Production code not found: ' . $signature);
    }
    if ($endSignature !== null) {
        $end = strpos($source, $endSignature, $start);
        if ($end === false) {
            throw new \RuntimeException('Production code not found: ' . $endSignature);
        }
        return substr($source, $start, $end - $start);
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
eval('namespace ' . __NAMESPACE__ . '; ' . $apiLiveRowExtract('objects/functionsSecurity.php', 'function getSensitiveUserFields()'));
eval('namespace ' . __NAMESPACE__ . '; ' . $apiLiveRowExtract('objects/functionsSecurity.php', 'function removeSensitiveUserFields(&$data)'));
eval('namespace ' . __NAMESPACE__ . '; ' . $apiLiveRowExtract('objects/Object.php', 'class VideosListCacheHandler extends CacheHandler'));

// The production loop prologue of API::get_api_video(): sanitization, then the skip for rows without a filename.
$apiLiveRowLoop = $apiLiveRowExtract(
    'plugin/API/API.php',
    'foreach ($rows as $key => $value) {',
    'if ($value[\'type\'] == Video::$videoTypeSerie) {',
    'public function get_api_video($parameters)'
);
eval('namespace ' . __NAMESPACE__ . '; function runGetApiVideoLoop($rows, $isAPISecretValid, $isUserLoggedIn, $currentUserId) {
    $reached = [];
    ' . $apiLiveRowLoop . '
        $reached[] = $key;
    }
    return [$rows, $reached];
}');
unset($apiLiveRowExtract, $apiLiveRowLoop);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\ApiLiveRowUserFieldsFixtures\CacheHandler;
use Tests\Security\ApiLiveRowUserFieldsFixtures\VideosListCacheHandler;
use function Tests\Security\ApiLiveRowUserFieldsFixtures\getSensitiveUserFields;
use function Tests\Security\ApiLiveRowUserFieldsFixtures\runGetApiVideoLoop;

/**
 * GHSA-75v5-gj76-crfw: APIName=video&catName=... merged live rows from getLiveVideosObject() (streamer email, isAdmin,
 * canStream, canUpload, emailVerified, user) into the response, and the loop skipped rows without a filename before
 * removeSensitiveUserFields() ran, so anonymous callers got the streamer's private fields.
 */
class ApiLiveRowUserFieldsTest extends TestCase
{
    private function liveRow()
    {
        return [
            'id' => 1234567, 'isLive' => 1, 'users_id' => 5, 'title' => 'Live now',
            'user' => 'streamer', 'name' => 'Streamer', 'email' => 'streamer@example.invalid', 'isAdmin' => 1,
            'photoURL' => 'videos/userPhoto/photo5.png', 'canStream' => 1, 'canUpload' => 1, 'emailVerified' => 1,
            'channelName' => 'streamerchannel', 'identification' => 'Streamer', 'UserPhoto' => 'https://site/photo5.png',
            'stats' => ['key' => 'k', 'users_id' => 5], 'videos' => ['m3u8' => ['url' => 'https://site/live/k.m3u8']],
            'images' => ['poster' => 'https://site/live/poster.jpg'],
        ];
    }

    private function vodRow()
    {
        return ['id' => 9, 'users_id' => 6, 'filename' => 'video_9', 'type' => 'video',
            'user' => 'owner', 'email' => 'owner@example.invalid', 'isAdmin' => 0, 'title' => 'VOD'];
    }

    public function testAnonymousCallerDoesNotGetTheStreamerPrivateFields()
    {
        [$rows] = runGetApiVideoLoop([$this->liveRow(), $this->vodRow()], false, false, 0);
        foreach (['email', 'isAdmin', 'canStream', 'canUpload', 'user', 'photoURL'] as $field) {
            $this->assertArrayNotHasKey($field, $rows[0], $field);
            $this->assertArrayNotHasKey($field, $rows[1], $field);
        }
        $this->assertStringNotContainsString('example.invalid', json_encode($rows));
    }

    public function testLiveRowKeepsItsPublicFieldsAndIsStillSkipped()
    {
        [$rows, $reached] = runGetApiVideoLoop([$this->liveRow(), $this->vodRow()], false, false, 0);
        $this->assertSame([1], $reached);
        foreach (['id', 'isLive', 'users_id', 'title', 'name', 'channelName', 'identification', 'UserPhoto', 'stats', 'videos', 'images', 'emailVerified'] as $field) {
            $this->assertSame($this->liveRow()[$field], $rows[0][$field], $field);
        }
    }

    public function testStreamerOwnRowAndApiSecretCallersKeepTheFields()
    {
        [$rows] = runGetApiVideoLoop([$this->liveRow()], false, true, 5);
        $this->assertSame('streamer@example.invalid', $rows[0]['email']);

        [$rows] = runGetApiVideoLoop([$this->liveRow()], true, false, 0);
        $this->assertSame('streamer@example.invalid', $rows[0]['email']);

        [$rows] = runGetApiVideoLoop([$this->liveRow()], false, true, 6);
        $this->assertArrayNotHasKey('email', $rows[0]);
    }

    public function testRowsLoseExactlyTheSharedSensitiveFields()
    {
        $live = $this->liveRow();
        $vod = $this->vodRow();
        foreach (getSensitiveUserFields() as $field) {
            $live[$field] = $vod[$field] = 'private';
        }
        // A VOD entry without a filename takes the same early-continue path as a live entry.
        $withoutFilename = $vod;
        unset($withoutFilename['filename']);
        $input = [$live, $vod, $withoutFilename];
        [$rows, $reached] = runGetApiVideoLoop($input, false, false, 0);
        $this->assertSame([1], $reached);
        foreach ($input as $key => $expected) {
            foreach (getSensitiveUserFields() as $field) {
                unset($expected[$field]);
            }
            $this->assertSame($expected, $rows[$key]);
        }
    }

    public function testCatalogDoesNotReuseSnapshotsContainingLiveUserFields()
    {
        $savedRequest = $_REQUEST;
        try {
            $_REQUEST = ['APIName' => 'video', 'catName' => 'live-category'];
            $source = file_get_contents(dirname(__DIR__, 2) . '/objects/Object.php');
            $this->assertSame(1, preg_match('/\$cacheParameters = (array\(.*?\));/s', $source, $matches));
            $parameters = eval('return ' . $matches[1] . ';');
            // Exact catalog namespace immediately before the live-user-field fix.
            $legacyVars = ['users_id' => 0, 'requestUniqueString' => 'fixture-request',
                'listingVisibility' => 'fixture-visibility', 'tagFormatVersion' => 2,
                'videoPasswordFormatVersion' => 1];
            foreach ($parameters as $name) {
                $legacyVars[$name] = $_REQUEST[$name] ?? null;
            }
            $legacySnapshot = (object) ['rows' => [$this->liveRow()], 'totalRows' => 1];
            CacheHandler::$snapshots[md5(json_encode($legacyVars))] = $legacySnapshot;
            $cache = new VideosListCacheHandler();
            $this->assertNull($cache->getCacheWithAutoSuffix(3600), 'Legacy cache bypasses the sanitization loop');

            [$rows] = runGetApiVideoLoop([$this->liveRow()], false, false, 0);
            $currentSnapshot = (object) ['rows' => $rows, 'totalRows' => 1];
            $cache->setCache($currentSnapshot);
            $this->assertSame($currentSnapshot, (new VideosListCacheHandler())->getCacheWithAutoSuffix(3600));
        } finally {
            $_REQUEST = $savedRequest;
            CacheHandler::$snapshots = [];
        }
    }
}
