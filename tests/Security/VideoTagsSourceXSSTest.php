<?php

namespace Tests\Security\VideoTagsSourceFixtures;

class VideoTags
{
    public static $TagTypeSource = 'source';
}

class LinkVideo
{
    private $link;

    public function __construct($link)
    {
        $this->link = $link;
    }

    public function getVideoDownloadedLink()
    {
        return $this->link;
    }
}

function __($text)
{
    return $text;
}

class TagCacheRead extends \RuntimeException
{
}

class VideoCacheHandler
{
    public function __construct($filename = '', $id = 0)
    {
    }

    public function getCache($suffix, $lifetime = 60)
    {
        throw new TagCacheRead($suffix);
    }
}

class PlayListCacheHandler extends VideoCacheHandler
{
}

class User
{
    public static function getId()
    {
        return 7;
    }
}

function getLanguage()
{
    return 'en_US';
}

function TimeLogStart($name)
{
    return $name;
}

function cleanSearchVar()
{
}

function reloadSearchVar()
{
}

function getRequestUniqueString()
{
    return 'test-request';
}

function getVideoListVisibilityCacheKey()
{
    return 'test-visibility';
}

function extractTagsCallable($tagsSource, $signature)
{
    $tagsStart = strpos($tagsSource, $signature);
    if ($tagsStart === false) {
        throw new \RuntimeException($signature . ' not found');
    }
    $tagsDepth = 0;
    for ($tagsIndex = strpos($tagsSource, '{', $tagsStart); $tagsIndex < strlen($tagsSource); $tagsIndex++) {
        if ($tagsSource[$tagsIndex] === '{') {
            $tagsDepth++;
        } elseif ($tagsSource[$tagsIndex] === '}' && --$tagsDepth === 0) {
            return substr($tagsSource, $tagsStart, $tagsIndex - $tagsStart + 1);
        }
    }
    throw new \RuntimeException($signature . ' has no closing brace');
}

// Run the production source producer and cache entry points without loading the database/bootstrap.
$tagsSource = file_get_contents(dirname(__DIR__, 2) . '/objects/functionTags.php');
eval('namespace ' . __NAMESPACE__ . '; use stdClass; '
    . extractTagsCallable($tagsSource, 'function getVideoTagsSource(')
    . extractTagsCallable($tagsSource, 'function getVideoTags('));
$tagsVideoSource = file_get_contents(dirname(__DIR__, 2) . '/objects/video.php');
eval('namespace ' . __NAMESPACE__ . '; class Video {'
    . extractTagsCallable($tagsVideoSource, 'public static function getTags(')
    . extractTagsCallable($tagsVideoSource, 'static function getInfo(')
    . extractTagsCallable($tagsVideoSource, 'static function getInfoPersonal(') . '}');
$tagsPlaylistSource = file_get_contents(dirname(__DIR__, 2) . '/objects/playlist.php');
eval('namespace ' . __NAMESPACE__ . '; class Playlist {
    public static function getSqlFromPost() { return ""; }
    ' . extractTagsCallable($tagsPlaylistSource, 'public static function getVideosFromPlaylist(') . '}');
$tagsObjectSource = file_get_contents(dirname(__DIR__, 2) . '/objects/Object.php');
eval('namespace ' . __NAMESPACE__ . '; class VideosListCacheHandler {
    public function getSuffix() { return $this->getCacheSufix(); }
    ' . extractTagsCallable($tagsObjectSource, 'private function getCacheSufix(') . '}');
unset($tagsSource, $tagsVideoSource, $tagsPlaylistSource, $tagsObjectSource);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\VideoTagsSourceFixtures\LinkVideo;
use Tests\Security\VideoTagsSourceFixtures\TagCacheRead;
use Tests\Security\VideoTagsSourceFixtures\Video;
use Tests\Security\VideoTagsSourceFixtures\Playlist;
use Tests\Security\VideoTagsSourceFixtures\VideosListCacheHandler;
use function Tests\Security\VideoTagsSourceFixtures\getVideoTagsSource;
use function Tests\Security\VideoTagsSourceFixtures\getVideoTags;

/**
 * GHSA-2qq6-qfcq-jmr9: a videoDownloadedLink sent in a JSON body to aVideoEncoder.json.php skips the bootstrap
 * filter, and parse_url() keeps markup in the host, which the videos manager renders as HTML in the "Source" tag.
 */
class VideoTagsSourceXSSTest extends TestCase
{
    private function sourceTag($link)
    {
        $tags = getVideoTagsSource(new LinkVideo($link), '');
        $this->assertCount(1, $tags);
        return $tags[0];
    }

    public function testMarkupInTheHostIsEscaped()
    {
        $tag = $this->sourceTag('http://<img src=x onerror=alert(document.domain)>/a');
        $this->assertSame('danger', $tag->type);
        $this->assertStringNotContainsString('<', $tag->text);
        $this->assertStringNotContainsString('>', $tag->text);
        $this->assertSame('&lt;img src=x onerror=alert(document.domain)&gt;', $tag->text);

        $tag = $this->sourceTag('http://<img src=x onerror="a()" \'b\'>/a');
        $this->assertStringNotContainsString('"', $tag->text);
        $this->assertStringNotContainsString("'", $tag->text);
    }

    public function testFormEncodedValueIsNotEncodedTwice()
    {
        // the bootstrap filter already stores a form-encoded value as entities
        $tag = $this->sourceTag('http://&lt;img src=x&gt;/a');
        $this->assertSame('&lt;img src=x&gt;', $tag->text);
    }

    public function testRealHostsAreUnchanged()
    {
        $this->assertSame('www.youtube.com', $this->sourceTag('https://www.youtube.com/watch?v=abc&list=x')->text);
        $this->assertSame('vimeo.com', $this->sourceTag('https://vimeo.com/123')->text);
        $this->assertSame('192.168.0.10', $this->sourceTag('http://192.168.0.10:8080/file.mp4')->text);
        $this->assertSame('exemplo-ção.com.br', $this->sourceTag('https://exemplo-ção.com.br/v.mp4')->text);
    }

    public function testLocalFileAndEmptyLinkKeepTheirTags()
    {
        $tag = $this->sourceTag('video.mp4');
        $this->assertSame('info', $tag->type);
        $this->assertSame('Local File', $tag->text);
        $this->assertSame([], getVideoTagsSource(new LinkVideo(''), ''));
        $this->assertSame([], getVideoTagsSource(new LinkVideo('https://example.com/'), 'other'));
    }

    /**
     * @dataProvider legacyTagCacheProvider
     */
    public function testPreEscapeCachesAreNotRead($entryPoint, $legacySuffix)
    {
        try {
            if ($entryPoint === 'getVideoTags') {
                getVideoTags(123, 'source');
            } elseif ($entryPoint === 'getTags') {
                Video::getTags(123, 'source');
            } elseif ($entryPoint === 'getInfo') {
                Video::getInfo(['id' => 123, 'filename' => 'test-video']);
            } else {
                Video::getInfoPersonal(['id' => 123, 'filename' => 'test-video']);
            }
            $this->fail('The production entry point did not consult the cache');
        } catch (TagCacheRead $read) {
            $this->assertNotSame($legacySuffix, $read->getMessage(), 'Old cache entries may contain raw Source markup');
            $this->assertStringEndsWith($entryPoint === 'getInfoPersonal' ? '_7' : '_en_US', $read->getMessage());
            if ($entryPoint === 'getTags' || $entryPoint === 'getVideoTags') {
                $this->assertStringContainsString('_source_', $read->getMessage());
            }
        }
    }

    public static function legacyTagCacheProvider(): array
    {
        return [
            'Video::getTags' => ['getTags', 'getTags_source_en_US'],
            'getVideoTags' => ['getVideoTags', 'getTags_source_en_US'],
            'Video::getInfo includes tags' => ['getInfo', 'getVideoInfo_en_US'],
            'Video::getInfoPersonal includes tags' => ['getInfoPersonal', 'getInfoPersonal_7'],
        ];
    }

    public function testPlaylistSnapshotsDoNotReuseLegacyTagCaches()
    {
        $savedPost = $_POST;
        try {
            Playlist::getVideosFromPlaylist(123);
            $this->fail('The playlist did not consult the cache');
        } catch (TagCacheRead $read) {
            $this->assertFalse(strlen($read->getMessage()) === 32 && ctype_xdigit($read->getMessage()), 'The legacy playlist key was only md5($sql)');
        } finally {
            $_POST = $savedPost;
        }
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/playlist.php');
        $this->assertStringNotContainsString('$suffixVideo = "Video{$row[\'id\']}";', $source);
    }

    public function testApiCatalogSnapshotsDoNotReuseLegacyTagCaches()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/Object.php');
        $method = \Tests\Security\VideoTagsSourceFixtures\extractTagsCallable($source, 'private function getCacheSufix(');
        $this->assertSame(1, preg_match('/\$cacheParameters = (array\(.*?\));/s', $method, $matches));
        $parameters = eval('return ' . $matches[1] . ';');
        $legacyVars = ['users_id' => 7, 'requestUniqueString' => 'test-request', 'listingVisibility' => 'test-visibility'];
        foreach ($parameters as $name) {
            $legacyVars[$name] = $_REQUEST[$name] ?? null;
        }
        $this->assertNotSame(md5(json_encode($legacyVars)), (new VideosListCacheHandler())->getSuffix());
    }
}
