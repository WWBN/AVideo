<?php

namespace Tests\Security\ImportMP4OwnershipFixtures;

class State
{
    public static $currentUser = 0;
    public static $admins = [1];
    public static $videosAdmins = [];
    // filename => [id, owner]
    public static $videos = [];
    public static $endpointMode = false;
    public static $endpoint = '';
    public static $operations = [];
}

class User
{
    private $id;

    public function __construct($id = 0)
    {
        $this->id = intval($id);
    }

    public static function getId()
    {
        return State::$currentUser;
    }

    public static function canUpload()
    {
        return State::$currentUser !== 0;
    }

    public function getIsAdmin()
    {
        return in_array($this->id, State::$admins, true);
    }
}

class Permissions
{
    public static function canAdminVideos()
    {
        return in_array(State::$currentUser, State::$videosAdmins, true);
    }
}

class sqlDAL
{
    public static function readSql($sql, $types, $values)
    {
        $rows = [];
        foreach (State::$videos as $filename => $video) {
            foreach ($values as $value) {
                // Match the usual case-insensitive database collation.
                if (strcasecmp($filename, $value) === 0) {
                    $rows[] = ['id' => $video[0], 'filename' => $filename];
                    break;
                }
            }
        }
        return new \ArrayIterator($rows);
    }

    public static function fetchAssoc($result)
    {
        if (!$result->valid()) {
            return false;
        }
        $row = $result->current();
        $result->next();
        return $row;
    }

    public static function close($result)
    {
    }
}

function fixPath($path, $addLastSlash = false)
{
    return $path;
}

function getVideosDir()
{
    return '/srv/avideo/videos/';
}

// Endpoint execution stays entirely in memory; no fixture can touch repository media.
function header($value)
{
}

function forbiddenPage($message)
{
    throw new \RuntimeException('forbidden: ' . $message);
}

function endpointExit($output = '')
{
    throw new \RuntimeException('endpoint exit: ' . $output);
}

function realpath($path)
{
    // The listing fixture uses POSIX paths; imports reconstruct paths with the host separator.
    $separator = State::$endpoint === 'listFiles.json.php' ? '/' : DIRECTORY_SEPARATOR;
    return str_replace(['\\', '/'], $separator, $path);
}

function file_exists($path)
{
    return true;
}

function file_get_contents($path)
{
    if (State::$endpointMode) {
        State::$operations[] = 'read';
        return 'Imported description';
    }
    return \file_get_contents($path);
}

function copy($source, $destination)
{
    State::$operations[] = 'copy';
    throw new \RuntimeException('copy reached');
}

function unlink($path)
{
    throw new \RuntimeException('Unexpected unlink in an in-memory fixture');
}

function glob($pattern, $flags = 0)
{
    State::$operations[] = 'glob';
    return [];
}

function extractCallable($source, $signature)
{
    $start = strpos($source, $signature);
    if ($start === false) {
        throw new \RuntimeException($signature . ' not found');
    }
    $depth = 0;
    for ($index = strpos($source, '{', $start); $index < strlen($source); $index++) {
        if ($source[$index] === '{') {
            $depth++;
        } elseif ($source[$index] === '}' && --$depth === 0) {
            return substr($source, $start, $index - $start + 1);
        }
    }
    throw new \RuntimeException($signature . ' has no closing brace');
}

$importVideoSource = file_get_contents(dirname(__DIR__, 2) . '/objects/video.php');
$importMethods = '';
foreach ([
    'public static function canImportFromVideosPath(',
    'public static function getVideoFromFileNameLight(',
    'public static function canEdit(',
    'public static function getCleanFilenameFromFile(',
] as $importSignature) {
    $importMethods .= extractCallable($importVideoSource, $importSignature) . "\n";
}
// Execute the real lookup and filename cleaner against in-memory database rows.
eval('namespace ' . __NAMESPACE__ . ';
class Video {
    public static function forceAudio() { return false; }
    public static function forceArticle() { return false; }
    public static function isOwner($videos_id, $users_id) {
        foreach (State::$videos as $row) {
            if ($row[0] === $videos_id) {
                return $row[1] === $users_id;
            }
        }
        return false;
    }
    ' . $importMethods . '
}');
unset($importVideoSource, $importMethods, $importSignature);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\ImportMP4OwnershipFixtures\State;
use Tests\Security\ImportMP4OwnershipFixtures\Video;

/**
 * GHSA-g69x-xc9q-2g2v (incomplete fix of GHSA-83xq-8jxj-4rxm): Import MP4 only required the path to be inside videos/,
 * where every user's media lives, so an uploader could copy another user's video into their channel and delete it.
 */
class ImportMP4OwnershipTest extends TestCase
{
    const VIDEOS = '/srv/avideo/videos';
    private $originalGlobal;

    protected function setUp(): void
    {
        global $global;
        $this->originalGlobal = $global;
        $global['avideo_resolutions'] = [240, 360, 480, 540, 720, 1080, 1440, 2160];
        State::$currentUser = 0;
        State::$admins = [1];
        State::$videosAdmins = [];
        State::$videos = [
            'v_250101120000_vab12' => [10, 2],
            'v_250101130000_vcd34' => [11, 3],
            'oldflatvideo' => [12, 2],
        ];
    }

    protected function tearDown(): void
    {
        global $global;
        $global = $this->originalGlobal;
        State::$endpointMode = false;
        State::$operations = [];
    }

    public function testUploaderCannotImportAnotherUsersVideoFolder()
    {
        State::$currentUser = 3;
        $this->assertFalse(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/v_250101120000_vab12', 'v_250101120000_vab12_480.mp4'));
        $this->assertFalse(Video::canImportFromVideosPath(self::VIDEOS . '/', self::VIDEOS . '/v_250101120000_vab12'), 'listing must be refused too');
        $this->assertFalse(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/v_250101120000_vab12/sub', 'x.mp4'));
    }

    public function testUploaderCannotImportAnotherUsersFlatFileInVideosRoot()
    {
        State::$currentUser = 3;
        $this->assertFalse(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, 'oldflatvideo_480.mp4'));
        $this->assertFalse(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, 'v_250101120000_vab12_720.mp4'));
        foreach (['HD', 'SD', 'Low', 'offline', '4320'] as $resolution) {
            $this->assertFalse(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, 'oldflatvideo_' . $resolution . '.MP4'));
        }
        $this->assertFalse(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, 'oldflatvideores480.mp4'));
    }

    public function testOwnerAdminAndVideosAdminCanStillImportVideoFolders()
    {
        State::$currentUser = 2;
        $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/v_250101120000_vab12', 'v_250101120000_vab12_480.mp4'));
        $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, 'oldflatvideo_480.mp4'));

        State::$currentUser = 1;
        $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/v_250101130000_vcd34', 'v_250101130000_vcd34_480.mp4'));

        State::$currentUser = 7;
        State::$videosAdmins = [7];
        $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/v_250101130000_vcd34', 'v_250101130000_vcd34_480.mp4'));
    }

    public function testStagingFoldersAndUnknownFilesStayAllowed()
    {
        State::$currentUser = 3;
        $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/import', 'my movie.mp4'));
        $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/import/2026', 'v_250101120000_vab12_480.mp4'), 'only the folder under videos/ decides inside a subfolder');
        $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, 'new upload.mp4'));
        $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS . '/', self::VIDEOS), 'listing videos/ itself is unchanged');
    }

    public function testLegacyVideoNamesMustNotBeCleanedBeforeOwnershipLookup()
    {
        State::$currentUser = 3;
        foreach ([
            'Trip_HD_YPTuniqid_5a1b2c3d4e5f6.12345678',
            'Trip_240_YPTuniqid_5a1b2c3d4e5f6.12345678',
            'movie.release',
            'index_offline_video',
        ] as $filename) {
            State::$videos[$filename] = [20, 2];
            $this->assertFalse(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/' . $filename, 'index.mp4'), $filename);
            $this->assertFalse(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, $filename . '_480.mp4'), $filename . ' flat file');
            State::$currentUser = 2;
            $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/' . $filename, 'index.mp4'), $filename . ' owner');
            $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, $filename . '_480.mp4'), $filename . ' flat file owner');
            State::$currentUser = 3;
            unset(State::$videos[$filename]);
        }
    }

    public function testStagingNamesThatOnlyCleanToAVideoNameStayAllowed()
    {
        State::$currentUser = 3;
        foreach (['oldflatvideo.backup', 'oldflatvideo_HD', 'oldflatvideo_480', 'oldflatvideo_thumbsV2'] as $folder) {
            $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/' . $folder, 'new.mp4'), $folder);
        }
        $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, 'oldflatvideo.backup.mp4'));
        $this->assertTrue(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, 'oldflatvideo_thumbsV2.mp4'));
    }

    public function testCaseMatchingFollowsHostFilesystemRules()
    {
        State::$currentUser = 3;
        $expected = DIRECTORY_SEPARATOR !== '\\';
        $this->assertSame($expected, Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/OLDFLATVIDEO', 'new.mp4'));
        $this->assertSame($expected, Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, 'OLDFLATVIDEO.mp4'));
    }

    public function testNativeSeparatorsAndTrailingSlashUseTheSameVideoKey()
    {
        State::$currentUser = 3;
        $base = DIRECTORY_SEPARATOR === '\\' ? 'D:\\avideo\\videos' : self::VIDEOS;
        $separator = DIRECTORY_SEPARATOR;
        $this->assertFalse(Video::canImportFromVideosPath($base, $base . $separator . 'v_250101120000_vab12', 'video.mp4'));
        $this->assertFalse(Video::canImportFromVideosPath($base . '/', $base . $separator . 'v_250101120000_vab12' . $separator . 'hls' . $separator . '720'));
        $this->assertTrue(Video::canImportFromVideosPath($base . $separator, $base . $separator . 'import' . $separator . '2026', 'new.mp4'));
    }

    public function testBackslashIsAPathSeparatorOnlyOnWindows()
    {
        State::$currentUser = 3;
        $expected = DIRECTORY_SEPARATOR !== '\\';
        $this->assertSame($expected, Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/oldflatvideo\\incoming', 'new.mp4'));
        $this->assertSame($expected, Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS . '/\\oldflatvideo\\', 'new.mp4'));
    }

    public function testAllMatchingFlatFileKeysRequireEditPermission()
    {
        State::$currentUser = 3;
        State::$videos['oldflatvideo_480'] = [20, 3];
        $this->assertFalse(Video::canImportFromVideosPath(self::VIDEOS, self::VIDEOS, 'oldflatvideo_480.mp4'));
    }

    public function testImportEndpointStopsBeforeReadingCopyingOrDeletingVictimFiles()
    {
        State::$currentUser = 3;
        State::$videos['Trip_HD_YPTuniqid_5a1b2c3d4e5f6.12345678'] = [20, 2];
        foreach ([
            '/v_250101120000_vab12/index.mp4',
            '/oldflatvideo_480.mp4',
            '/Trip_HD_YPTuniqid_5a1b2c3d4e5f6.12345678/index.mp4',
        ] as $file) {
            $result = $this->executeEndpoint('import.json.php', ['fileURI' => self::VIDEOS . $file, 'delete' => 'true']);
            $this->assertSame('forbidden: Path not allowed', $result);
            $this->assertSame([], State::$operations);
        }
    }

    public function testImportEndpointStillReachesCopyForAStagingFolder()
    {
        State::$currentUser = 3;
        $result = $this->executeEndpoint('import.json.php', ['fileURI' => self::VIDEOS . '/oldflatvideo.backup/new.mp4', 'length' => 100]);
        $this->assertSame('copy reached', $result);
        $this->assertSame(['read', 'copy'], State::$operations);
    }

    public function testListEndpointRefusesVictimFolderBeforeGlobAndAllowsStaging()
    {
        State::$currentUser = 3;
        $this->assertSame('forbidden: Path not allowed', $this->executeEndpoint('listFiles.json.php', ['path' => self::VIDEOS . '/v_250101120000_vab12']));
        $this->assertSame([], State::$operations);
        $this->assertSame('[]', $this->executeEndpoint('listFiles.json.php', ['path' => self::VIDEOS . '/oldflatvideo.backup']));
        $this->assertSame(['glob'], State::$operations);
    }

    private function executeEndpoint($endpoint, array $post)
    {
        global $global;
        $previousPost = $_POST;
        $_POST = $post;
        $global['systemRootPath'] = '/srv/avideo/';
        $endpointCode = \file_get_contents(dirname(__DIR__, 2) . '/objects/' . $endpoint);
        // An early endpoint exit must fail assertions rather than terminate PHPUnit successfully.
        $endpointCode = str_replace(['die(', 'exit;'], ['endpointExit(', 'endpointExit();'], $endpointCode);
        State::$operations = [];
        State::$endpointMode = true;
        State::$endpoint = $endpoint;
        ob_start();
        try {
            // Keep the endpoint's generic local variable names out of the test's scope.
            (static function ($importEndpointCode) {
                eval('namespace Tests\\Security\\ImportMP4OwnershipFixtures; use stdClass; ' . substr($importEndpointCode, 5));
            })($endpointCode);
            return ob_get_contents();
        } catch (\RuntimeException $exception) {
            return $exception->getMessage();
        } finally {
            ob_end_clean();
            State::$endpointMode = false;
            $_POST = $previousPost;
        }
    }

    public function testEndpointsCheckOwnershipBeforeTouchingFiles()
    {
        $import = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/objects/import.json.php'));
        $guard = strpos($import, 'if (!Video::canImportFromVideosPath($allowedBase, $resolvedDir, basename($_POST[\'fileURI\']))) {' . "\n    forbiddenPage(");
        $this->assertNotFalse($guard, 'import.json.php must refuse another user\'s video files');
        $this->assertGreaterThan(strpos($import, '$_POST[\'fileURI\'] = $resolvedDir'), $guard);
        foreach (['file_get_contents(', 'copy($source', 'unlink($source)'] as $sink) {
            $this->assertLessThan(strpos($import, $sink), $guard, $sink . ' must run after the ownership check');
        }

        $list = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/objects/listFiles.json.php'));
        $listGuard = strpos($list, 'if (!Video::canImportFromVideosPath($allowedBase, $resolvedPath)) {' . "\n        forbiddenPage(");
        $this->assertNotFalse($listGuard, 'listFiles.json.php must refuse another user\'s video folder');
        $this->assertLessThan(strpos($list, 'glob($path'), $listGuard);
    }
}
