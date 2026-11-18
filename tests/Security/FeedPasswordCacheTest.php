<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

class FeedPasswordCacheTest extends TestCase
{
    private $site;

    protected function setUp(): void
    {
        $this->site = sys_get_temp_dir() . '/avideo-feed-cache-' . bin2hex(random_bytes(8));
        foreach (['feed', 'videos', 'objects', 'cache', 'plugin/PlayLists'] as $directory) {
            mkdir($this->site . '/' . $directory, 0700, true);
        }
        foreach (glob(APP_ROOT . '/feed/*.php') as $file) {
            copy($file, $this->site . '/feed/' . basename($file));
        }
        copy(APP_ROOT . '/objects/feedDescriptionHTML.php', $this->site . '/objects/feedDescriptionHTML.php');
        // Exercise the production recursive conversion and password-presence normalization.
        $functions = file_get_contents(APP_ROOT . '/objects/functions.php');
        $helpers = '';
        foreach (['object_to_array', 'cleanUpRowFromDatabase'] as $name) {
            preg_match('/^function ' . $name . '\([^\n]+\n\{.*?^\}/ms', $functions, $match);
            $this->assertNotEmpty($match, $name);
            $helpers .= $match[0] . "\n";
        }
        file_put_contents($this->site . '/objects/helpers.php', "<?php\n" . $helpers);
        file_put_contents($this->site . '/videos/configuration.php', <<<'PHP'
<?php
require_once dirname(__DIR__) . '/objects/helpers.php';
require_once $GLOBALS['feedAutoload'];
$global['systemRootPath'] = dirname(__DIR__) . '/';
$global['webSiteRootURL'] = 'https://example.invalid/';
$global['VizioSourceID'] = 'test';
$global['VizioAppID'] = 'test';
function getRowCount() { return 10; }
function getURL($value) { return 'https://example.invalid/' . $value; }
function getCacheDir() { return $GLOBALS['global']['systemRootPath'] . 'cache/'; }
function forbiddenPage($message) { echo $message; exit; }
function _error_log($message) {}
function _ob_start() { ob_start(); }
function _ob_end_clean() { ob_end_clean(); }
function _json_encode($value) { return json_encode($value); }
function br2nl($value) { return $value; }
function UTF8encode($value) { return $value; }
function _substr($value, $start, $length) { return substr($value, $start, $length); }
function durationToSeconds($value) { return 60; }
function getResolutionTextRoku($value) { return 'HD'; }
function recreateCache() { return false; }
function mime_content_type_per_filename($value) { return 'video/mp4'; }
function addQueryStringParameter($url, $key, $value) { return $url . '?' . $key . '=' . $value; }
function getVideosURL($filename, $recreate = false) {
    if ($GLOBALS['feedScenario'] === 'cached') { throw new Exception('Expected cached output'); }
    return [['type' => 'video', 'url' => 'https://example.invalid/' . $filename . '.mp4',
        'path' => $GLOBALS['global']['systemRootPath'] . 'videos/media.mp4']];
}
class AVideoConf {
    function getWebSiteTitle() { return 'Site'; }
    function getContactEmail() { return 'contact@example.invalid'; }
}
$config = new AVideoConf();
class AVideoPlugin {
    static function getDataObject($name) { return (object) ['disableFeeds' => false]; }
    static function isEnabledByName($name) { return false; }
}
class User { static function getId() { return 0; } }
class PlaybackAccess {
    static function isRestricted($id) { return $id === 99 || ($id === 23 && $GLOBALS['feedScenario'] === 'restricted'); }
}
class ImagesPlaceHolders {
    static function isDefaultImage($path) { return true; }
}
class ObjectYPT {
    static function getCache($key, $ttl) {
        $file = getCacheDir() . md5($key);
        return file_exists($file) ? json_decode(file_get_contents($file)) : null;
    }
    static function setCache($key, $value) {
        file_put_contents(getCacheDir() . md5($key), json_encode($value));
    }
}
PHP
        );
        file_put_contents($this->site . '/objects/video.php', <<<'PHP'
<?php
class Video {
    static $videoTypeVideo = 'video';
    static $videoTypeAudio = 'audio';
    function __construct(...$args) {}
    function getFilename() { return 'image'; }
    static function getAllVideos(...$args) {
        if (in_array($GLOBALS['feedScenario'], ['rows', 'missingFlag'], true)) { throw new Exception('Expected cached rows'); }
        return self::rows();
    }
    static function rows() {
        $rows = [];
        foreach ([22 => 'protected', 21 => 'public', 99 => 'group', 23 => 'second'] as $id => $name) {
            $row = ['id' => $id, 'title' => $name, 'filename' => $name, 'description' => $name,
                'video_password' => ($id === 22 || ($id === 23 && $GLOBALS['feedScenario'] === 'passwordAdded')) ? 'test-password' : '', 'duration' => '00:01:00',
                'created' => '2026-01-01 00:00:00', 'category' => 'documentary', 'categories_id' => 1,
                'rrating' => '', 'clean_title' => $name, 'filesize' => 2000];
            $rows[] = cleanUpRowFromDatabase($row);
        }
        return $rows;
    }
    static function getVideoLight($id, $refreshCache = false) {
        if (!$refreshCache) { throw new Exception('Password lookup must bypass cache'); }
        foreach (self::rows() as $row) {
            if ($row['id'] === $id) { return $row; }
        }
        return false;
    }
    static function getSourceFileURL($filename, ...$args) {
        if ($GLOBALS['feedScenario'] === 'cached') { throw new Exception('Expected cached output'); }
        if ($filename === 'protected' || $filename === 'group') { throw new Exception('Restricted source reached'); }
        return 'https://example.invalid/' . $filename . '.mp4';
    }
    static function getResolutionFromFilename($filename) { return 720; }
    static function getVideoTypeText($filename) { return 'MP4'; }
    static function getRokuImage($id) { return 'https://example.invalid/poster.jpg'; }
    static function getPoster($id) { return self::getRokuImage($id); }
    static function getImageFromID($id) { return (object) ['posterPortraitPath' => '/poster.jpg']; }
    static function getSourceFile(...$args) { return ['path' => '/poster.jpg']; }
    static function getVideoFromFileName($filename) { return ['filesize' => 2000]; }
    static function getLink($id, ...$args) { return 'https://example.invalid/video/' . $id; }
    static function getLinkToVideo($id, ...$args) { return self::getLink($id); }
    static function htmlDescription($value) { return $value; }
}
PHP
        );
        file_put_contents($this->site . '/plugin/PlayLists/PlayLists.php', <<<'PHP'
<?php
class PlayList {
    function __construct($id) {}
    function getName() { return 'Program'; }
    static function getVideosIdFromPlaylist($id) { return [22, 21, 99, 23]; }
    static function sortVideos($rows, $ids) { return $rows; }
    static function canSee($id, $user) { return true; }
}
class PlayLists {
    static function getNameOrSerieTitle($id) { return 'Program'; }
    static function getLink($id) { return 'https://example.invalid/program/' . $id; }
    static function getDescriptionIfIsSerie($id) { return 'Program description'; }
}
PHP
        );
        file_put_contents($this->site . '/videos/media.mp4', str_repeat('x', 2000));
        file_put_contents($this->site . '/run.php', <<<'PHP'
<?php
$GLOBALS['feedAutoload'] = $argv[1];
$GLOBALS['feedScenario'] = $argv[2];
$_GET = $_POST = $_REQUEST = [];
if ($argv[3]) { $_REQUEST[$argv[3]] = 1; }
if ($argv[4]) { $_REQUEST['program_id'] = 123; }
$_REQUEST['rowCount'] = 10;
$_SERVER['REQUEST_URI'] = '/feed/index.php';
require 'videos/configuration.php';
require 'objects/video.php';
$suffix = json_encode($_REQUEST);
if ($argv[2] === 'legacy') {
    foreach (['feedCacheRSSv2', 'feedCacheMRSS', 'feedCache_ROKU', 'feedCache_ROKU_SearchFeed', 'feedCache_VIZIO_ConfigurationFeed'] as $prefix) {
        ObjectYPT::setCache($prefix . $suffix, 'protected-old-output.mp4');
    }
} elseif ($argv[2] === 'movies') {
    ObjectYPT::setCache('feedCache_ROKU_movies' . $suffix, [(object) ['id' => 'video_22', 'url' => 'protected-old-movie.mp4']]);
} elseif (in_array($argv[2], ['rows', 'missingFlag'], true)) {
    $cachedRows = Video::rows();
    if ($argv[2] === 'missingFlag') {
        foreach ($cachedRows as &$cachedRow) { unset($cachedRow['video_password']); }
        unset($cachedRow);
    }
    ObjectYPT::setCache('feedCache' . md5($suffix), $cachedRows);
}
chdir('feed');
require 'index.php';
PHP
        );
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->site, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->site);
    }

    private function render($scenario, $format, $program)
    {
        $process = proc_open([PHP_BINARY, 'run.php', APP_ROOT . '/vendor/autoload.php', $scenario, $format, $program ? '1' : ''],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $this->site);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors . $output);
        $this->assertSame('', $errors);
        return $output;
    }

    public static function formats()
    {
        foreach (['', 'mrss', 'roku', 'rokuSearch', 'vizio'] as $format) {
            foreach ([false, true] as $program) {
                yield ($format ?: 'rss') . ($program ? '/program' : '/catalog') => [$format, $program];
            }
        }
    }

    private function assertPublicOutput($output)
    {
        $this->assertStringNotContainsString('protected', $output);
        $this->assertStringNotContainsString('video_22', $output);
        $this->assertStringNotContainsString('video_99', $output);
        $this->assertStringContainsString('public', $output);
        $this->assertStringContainsString('second', $output);
    }

    /** @dataProvider formats */
    public function testRealRenderersDiscardLegacyOutputCaches($format, $program)
    {
        $this->assertPublicOutput($this->render('legacy', $format, $program));
    }

    /** @dataProvider formats */
    public function testRealRenderersFilterObjectRowsAndReuseSafeOutput($format, $program)
    {
        $first = $this->render('rows', $format, $program);
        $this->assertPublicOutput($first);
        $second = $this->render('cached', $format, $program);
        $this->assertSame(preg_replace('/<!-- (?:NO )?cache -->/', '', $first), preg_replace('/<!-- (?:NO )?cache -->/', '', $second));
    }

    /** @dataProvider formats */
    public function testOutputCacheTracksFilteredVideoIds($format, $program)
    {
        $this->assertPublicOutput($this->render('rows', $format, $program));
        $output = $this->render('restricted', $format, $program);
        $this->assertStringNotContainsString('second', $output);
        $this->assertStringContainsString('public', $output);
    }

    /** @dataProvider formats */
    public function testCachedRowsUseCurrentPasswordState($format, $program)
    {
        $this->assertPublicOutput($this->render('rows', $format, $program));
        $output = $this->render('passwordAdded', $format, $program);
        $this->assertStringNotContainsString('second', $output);
        $this->assertStringContainsString('public', $output);
        $this->assertPublicOutput($this->render('cached', $format, $program));
    }

    /** @dataProvider formats */
    public function testCachedRowsWithoutPasswordFlagRemainProtected($format, $program)
    {
        $this->assertPublicOutput($this->render('missingFlag', $format, $program));
    }

    public function testRokuDiscardsLegacyMovieCache()
    {
        $this->assertPublicOutput($this->render('movies', 'roku', false));
    }
}
