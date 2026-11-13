<?php

use PHPUnit\Framework\TestCase;

class FeedPlaylistBootstrapTest extends TestCase
{
    private $site;
    private $files = [];

    protected function setUp(): void
    {
        $this->site = sys_get_temp_dir() . '/avideo-feed-' . bin2hex(random_bytes(8));
        foreach (['feed', 'videos', 'objects', 'plugin/PlayLists'] as $directory) {
            mkdir($this->site . '/' . $directory, 0700, true);
        }
        $this->write('feed/index.php', file_get_contents(APP_ROOT . '/feed/index.php'));
        $this->write('videos/configuration.php', <<<'PHP'
<?php
$global['systemRootPath'] = dirname(__DIR__) . '/';
$global['webSiteRootURL'] = 'https://example.invalid/';
function getRowCount() { return 10; }
function getURL($value) { return $value; }
function object_to_array($value) { return (array) $value; }
function forbiddenPage($message) { echo json_encode(['denied' => $message]); exit; }
class AVideoConf {
    function getWebSiteTitle() { return 'Site'; }
    function getContactEmail() { return 'contact@example.invalid'; }
}
$config = new AVideoConf();
class AVideoPlugin {
    static function getDataObject($name) { return (object) ['disableFeeds' => $GLOBALS['feedScenario'] === 'disabled']; }
}
class User { static function getId() { return 0; } }
class ObjectYPT {
    static function getCache($key, $ttl) { return $GLOBALS['feedScenario'] === 'warm' ? [['id' => 21]] : []; }
    static function setCache($key, $value) {}
}
class PlaybackAccess { static function isRestricted($id) { return $id === 99; } }
PHP
        );
        // Video intentionally does not load PlayList: that must not depend on a previous catalog call.
        $this->write('objects/video.php', <<<'PHP'
<?php
class Video {
    static function getAllVideos(...$args) {
        $GLOBALS['feedVideoArguments'] = $args;
        return empty($_REQUEST['program_id']) ? [['id' => 21]] : [['id' => 21], ['id' => 99]];
    }
}
PHP
        );
        $this->write('plugin/PlayLists/PlayLists.php', <<<'PHP'
<?php
require_once $global['systemRootPath'] . 'objects/playlist.php';
class PlayLists {
    static function getNameOrSerieTitle($id) { return 'Program'; }
    static function getLink($id) { return 'https://example.invalid/program/' . $id; }
    static function getDescriptionIfIsSerie($id) { return 'Program description'; }
}
PHP
        );
        $this->write('objects/playlist.php', <<<'PHP'
<?php
class PlayList {
    function __construct($id) {}
    static function getVideosIdFromPlaylist($id) { return [21, 99]; }
    static function sortVideos($rows, $ids) { return $rows; }
    static function canSee($id, $user) { return $GLOBALS['feedScenario'] !== 'private'; }
}
PHP
        );
        foreach (['rss.php', 'mrss.php', 'roku.json.php', 'roku.search.json.php', 'vizio.json.php'] as $format) {
            $this->write('feed/' . $format, '<?php echo json_encode(["title" => $title, "rows" => $rows, "args" => $GLOBALS["feedVideoArguments"] ?? null]);');
        }
    }

    private function write($name, $contents)
    {
        $filename = $this->site . '/' . $name;
        file_put_contents($filename, $contents);
        $this->files[] = $filename;
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $filename) { unlink($filename); }
        foreach (['feed', 'videos', 'objects', 'plugin/PlayLists', 'plugin'] as $directory) { rmdir($this->site . '/' . $directory); }
        rmdir($this->site);
    }

    private function render($scenario, $format = '', $program = true)
    {
        $script = '$GLOBALS["feedScenario"]=$argv[1]; $_POST=[]; $_GET=[]; $_REQUEST=[]; '
            . 'if ($argv[2]) $_REQUEST["program_id"]=123; if ($argv[3]) $_REQUEST[$argv[3]]=1; require "index.php";';
        $process = proc_open([PHP_BINARY, '-r', $script, $scenario, $program ? '1' : '', $format],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $this->site . '/feed');
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertSame('', $errors);
        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    }

    public function testColdPlaylistFeedLoadsItsOwnDependencyAndKeepsVisibility()
    {
        foreach (['', 'mrss', 'roku', 'rokuSearch', 'vizio'] as $format) {
            $result = $this->render('cold', $format);
            $this->assertSame('Program', $result['title']);
            $this->assertSame([['id' => 21]], $result['rows']);
            $this->assertSame(['viewable', false, false, [21, 99], false, false], $result['args']);
        }
    }

    public function testWarmPlaylistFeedAlsoLoadsDependency()
    {
        $this->assertSame('Program', $this->render('warm')['title']);
    }

    public function testPrivatePlaylistRemainsDenied()
    {
        $this->assertSame(['denied' => 'Permission denied'], $this->render('private'));
    }

    public function testDisabledFeedsRemainDenied()
    {
        $this->assertSame(['denied' => 'Feeds are disabled'], $this->render('disabled'));
    }

    public function testOrdinaryFeedStillUsesCatalogScope()
    {
        $result = $this->render('cold', '', false);
        $this->assertSame('Site', $result['title']);
        $this->assertSame(['viewable', false], $result['args']);
    }
}
