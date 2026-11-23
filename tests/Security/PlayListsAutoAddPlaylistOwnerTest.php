<?php

namespace Tests\Security\PlayListsAutoAddFixtures;

// Stubs for the auto-add methods of plugin/PlayLists/PlayLists.php (never loads videos/configuration.php).
class AutoAddState
{
    public static $playlistOwners = [];
    public static $videoOwners = [];
    public static $externalOptions = [];
    public static $added = [];
    public static $securityLogs = [];
    public static $currentUserId = 2;
    public static $isAdmin = false;
    public static $manageAll = false;
    public static $canWatch = true;
}

class AVideoLog
{
    public static $SECURITY = 'SECURITY';
}

function _error_log($message, $level = '')
{
    if ($level === AVideoLog::$SECURITY) {
        AutoAddState::$securityLogs[] = $message;
    }
}

function canWatchVideoIncludingPassword($videos_id)
{
    return !empty($videos_id) && AutoAddState::$canWatch;
}

class Permissions
{
    public static function hasPermission($permission, $plugin)
    {
        return AutoAddState::$manageAll;
    }
}

class AVideoPlugin
{
    public static function getObjectData($plugin)
    {
        return (object) ['usersCanOnlyCreatePlayListsFromTheirContent' => false];
    }
}

class User
{
    private $id;

    public function __construct($id)
    {
        $this->id = intval($id);
    }

    public static function getId()
    {
        return AutoAddState::$currentUserId;
    }

    public static function isLogged()
    {
        return !empty(self::getId());
    }

    public static function isAdmin()
    {
        return AutoAddState::$isAdmin;
    }

    public function addExternalOptions($name, $value)
    {
        AutoAddState::$externalOptions[$this->id][$name] = $value;
        return 1;
    }

    public function getExternalOption($name)
    {
        return AutoAddState::$externalOptions[$this->id][$name] ?? null;
    }
}

class Video
{
    private $id;

    public function __construct($title = '', $filename = '', $id = 0)
    {
        $this->id = intval($id);
    }

    public function getUsers_id()
    {
        return AutoAddState::$videoOwners[$this->id] ?? 0;
    }
}

class PlayList
{
    private $id;

    public function __construct($id)
    {
        $this->id = intval($id);
    }

    public function getUsers_id()
    {
        // The real class returns the database value as a string.
        return isset(AutoAddState::$playlistOwners[$this->id]) ? (string) AutoAddState::$playlistOwners[$this->id] : null;
    }

    public function addVideo($videos_id, $add, $order = 0)
    {
        AutoAddState::$added[] = [$this->id, intval($videos_id)];
        return true;
    }
}

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\PlayListsAutoAddFixtures\AutoAddState;

/**
 * GHSA-4rj2-3rcg-34fm: the "auto add new videos" playlist must belong to the user. Saving another
 * user's playlist is refused, and onNewVideo() never inserts into a playlist the video owner does not own.
 */
class PlayListsAutoAddPlaylistOwnerTest extends TestCase
{
    private const METHODS = ['setAutoAddPlaylist', 'isAutoAddPlaylistOwnedBy', 'getAutoAddPlaylist', 'onNewVideo', 'addVideo', 'canAddVideoOnPlaylist', 'canManagePlaylist', 'canManageAllPlaylists'];

    public static function setUpBeforeClass(): void
    {
        if (class_exists('Tests\\Security\\PlayListsAutoAddFixtures\\PlayLists', false)) {
            return;
        }
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/PlayLists/PlayLists.php');
        $methods = [];
        foreach (self::METHODS as $name) {
            $methods[] = self::extractMethod($source, $name);
        }
        eval('namespace Tests\Security\PlayListsAutoAddFixtures; class PlayLists { const PERMISSION_CAN_MANAGE_ALL_PLAYLISTS = 0; ' . implode("\n", $methods) . ' }');
    }

    private static function extractMethod($source, $name)
    {
        $match = [];
        self::assertSame(1, preg_match('/(public|private|protected|static|\s)+function\s+' . $name . '\s*\(/', $source, $match, PREG_OFFSET_CAPTURE), "method {$name} not found");
        $start = $match[0][1];
        $open = strpos($source, '{', $start);
        $depth = 0;
        $length = strlen($source);
        for ($i = $open; $i < $length; $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $start, $i - $start + 1);
                }
            }
        }
        self::fail("method {$name} has no closing brace");
    }

    protected function setUp(): void
    {
        AutoAddState::$playlistOwners = [10 => 1, 20 => 2];
        AutoAddState::$videoOwners = [100 => 2, 101 => 1];
        AutoAddState::$externalOptions = [];
        AutoAddState::$added = [];
        AutoAddState::$securityLogs = [];
        AutoAddState::$currentUserId = 2;
        AutoAddState::$isAdmin = false;
        AutoAddState::$manageAll = false;
        AutoAddState::$canWatch = true;
    }

    private function playLists()
    {
        return new PlayListsAutoAddFixtures\PlayLists();
    }

    public function testSavingAnotherUsersPlaylistIsRefused(): void
    {
        $this->assertFalse(PlayListsAutoAddFixtures\PlayLists::setAutoAddPlaylist(2, '10'));
        $this->assertSame([], AutoAddState::$externalOptions);
        $this->assertCount(1, AutoAddState::$securityLogs);
    }

    public function testSavingAMissingPlaylistIsRefused(): void
    {
        $this->assertFalse(PlayListsAutoAddFixtures\PlayLists::setAutoAddPlaylist(2, 999));
        $this->assertSame([], AutoAddState::$externalOptions);
    }

    public function testRejectedSelectionPreservesTheExistingSelection(): void
    {
        AutoAddState::$externalOptions[2]['autoadd_playlist'] = 20;
        $this->assertFalse(PlayListsAutoAddFixtures\PlayLists::setAutoAddPlaylist(2, 10));
        $this->assertSame(20, AutoAddState::$externalOptions[2]['autoadd_playlist']);
    }

    public function testAutoAddHasNoAdminOrDelegateException(): void
    {
        AutoAddState::$isAdmin = true;
        AutoAddState::$manageAll = true;
        $this->assertFalse(PlayListsAutoAddFixtures\PlayLists::setAutoAddPlaylist(2, 10));
        AutoAddState::$externalOptions[2]['autoadd_playlist'] = 10;
        $this->assertFalse($this->playLists()->onNewVideo(100));
        $this->assertSame([], AutoAddState::$added);
    }

    public function testSavingOwnPlaylistAndDoNotAddStillWork(): void
    {
        $this->assertSame(1, PlayListsAutoAddFixtures\PlayLists::setAutoAddPlaylist(2, '20'));
        $this->assertSame(20, AutoAddState::$externalOptions[2]['autoadd_playlist']);

        $this->assertSame(1, PlayListsAutoAddFixtures\PlayLists::setAutoAddPlaylist(2, '0'));
        $this->assertSame(0, AutoAddState::$externalOptions[2]['autoadd_playlist']);
        $this->assertSame([], AutoAddState::$securityLogs);
    }

    public function testNewVideoGoesToTheOwnersOwnPlaylist(): void
    {
        AutoAddState::$externalOptions[2]['autoadd_playlist'] = 20;
        AutoAddState::$currentUserId = 1;

        $this->assertTrue($this->playLists()->onNewVideo(100));
        $this->assertSame([[20, 100]], AutoAddState::$added);
    }

    public function testValueSavedBeforeTheFixDoesNotInjectIntoAnotherUsersPlaylist(): void
    {
        AutoAddState::$externalOptions[2]['autoadd_playlist'] = 10;

        $this->assertFalse($this->playLists()->onNewVideo(100));
        $this->assertSame([], AutoAddState::$added);
        $this->assertCount(1, AutoAddState::$securityLogs);
    }

    public function testDeletedPlaylistIsIgnoredWithoutALoggedInSession(): void
    {
        AutoAddState::$currentUserId = 0;
        AutoAddState::$externalOptions[2]['autoadd_playlist'] = 20;
        $this->assertTrue($this->playLists()->onNewVideo(100));
        AutoAddState::$added = [];
        unset(AutoAddState::$playlistOwners[20]);
        $this->assertFalse($this->playLists()->onNewVideo(100));
        $this->assertSame([], AutoAddState::$added);
    }

    public function testNoAutoAddPlaylistDoesNothing(): void
    {
        $this->assertFalse($this->playLists()->onNewVideo(101));
        $this->assertFalse($this->playLists()->onNewVideo(0));
        $this->assertSame([], AutoAddState::$added);
        $this->assertSame([], AutoAddState::$securityLogs);
    }

    /** @dataProvider playlistAttachmentProvider */
    public function testExplicitPlaylistAttachmentChecksVideoAccessAndPlaylistManagement($endpoint, $playlistId, $canWatch, $isAdmin, $manageAll, $expected): void
    {
        AutoAddState::$canWatch = $canWatch;
        AutoAddState::$isAdmin = $isAdmin;
        AutoAddState::$manageAll = $manageAll;
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/' . $endpoint);
        $requestArray = $endpoint === 'videoAddNew.json.php' ? '$_REQUEST' : '$_POST';
        $start = strpos($source, "if (isset({$requestArray}['playlists_id'])) {");
        self::assertNotFalse($start, 'Playlist attachment block not found');
        $open = strpos($source, '{', $start);
        $depth = 1;
        $end = $open + 1;
        while ($end < strlen($source) && $depth > 0) {
            if ($source[$end] === '{') {
                $depth++;
            } elseif ($source[$end] === '}') {
                $depth--;
            }
            $end++;
        }
        self::assertSame(0, $depth, 'Playlist attachment block is incomplete');
        $block = substr($source, $start, $end - $start);
        $attach = eval('namespace Tests\Security\PlayListsAutoAddFixtures; return function ($resp) { $obj = (object) ["save" => $resp]; ' . $block . ' };');
        $originalPost = $_POST;
        $originalRequest = $_REQUEST;
        try {
            $_POST = $_REQUEST = ['playlists_id' => $playlistId];
            $attach(100);
        } finally {
            $_POST = $originalPost;
            $_REQUEST = $originalRequest;
        }
        self::assertSame($expected, AutoAddState::$added);
    }

    public static function playlistAttachmentProvider(): array
    {
        $cases = [];
        foreach (['videoAddNew.json.php', 'videoEditLight.php'] as $endpoint) {
            $cases[$endpoint . ' foreign playlist, denied video'] = [$endpoint, 10, false, false, false, []];
            $cases[$endpoint . ' foreign playlist, visible video'] = [$endpoint, 10, true, false, false, []];
            $cases[$endpoint . ' own playlist, visible video'] = [$endpoint, 20, true, false, false, [[20, 100]]];
            $cases[$endpoint . ' own playlist, denied video'] = [$endpoint, 20, false, false, false, []];
            $cases[$endpoint . ' admin manual attachment'] = [$endpoint, 10, true, true, false, [[10, 100]]];
            $cases[$endpoint . ' delegate manual attachment'] = [$endpoint, 10, true, false, true, [[10, 100]]];
            $cases[$endpoint . ' missing playlist'] = [$endpoint, 999, true, false, false, []];
        }
        return $cases;
    }
}
