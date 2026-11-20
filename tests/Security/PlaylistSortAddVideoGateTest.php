<?php

namespace Tests\Security\PlaylistSortFixtures;

// Stubs for the save loop of objects/playlistSort.php (never loads videos/configuration.php).
class PlaylistSortState
{
    public static $playlistVideos = [];
    public static $addable = [];
    public static $added = [];
    public static $securityLogs = [];
}

class AVideoLog
{
    public static $SECURITY = 'SECURITY';
}

function _error_log($message, $level = '')
{
    if ($level === AVideoLog::$SECURITY) {
        PlaylistSortState::$securityLogs[] = $message;
    }
}

function mysqlBeginTransaction()
{
}

function mysqlCommit()
{
}

class User
{
    public static function getId()
    {
        return 3;
    }
}

class PlayLists
{
    public static function canAddVideoOnPlaylist($videos_id)
    {
        return !empty(PlaylistSortState::$addable[$videos_id]);
    }
}

class PlayList
{
    public static function getVideosIDFromPlaylistLight($playlists_id)
    {
        $rows = [];
        foreach (PlaylistSortState::$playlistVideos as $videos_id) {
            $rows[] = ['playlists_id' => $playlists_id, 'videos_id' => (string) $videos_id];
        }
        return $rows;
    }

    public static function deleteCacheDir($playlists_id, $schedule = false)
    {
    }

    public function getId()
    {
        return 4;
    }

    public function addVideo($videos_id, $add, $order = 0, $_deleteCache = true)
    {
        PlaylistSortState::$added[] = [(int) $videos_id, (int) $order];
        return true;
    }
}

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\PlaylistSortFixtures\PlayList;
use Tests\Security\PlaylistSortFixtures\PlaylistSortState;

/**
 * GHSA-fp8w-c8xj-25wx: playlistSort.php must only reorder videos already in the playlist; a video
 * that is not in it must pass PlayLists::canAddVideoOnPlaylist(), like playListAddVideo.json.php.
 */
class PlaylistSortAddVideoGateTest extends TestCase
{
    private static $loop;

    public static function setUpBeforeClass(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/playlistSort.php');
        // This boundary also exists before the fix, so removing the gate causes assertion failures.
        $start = strpos($source, '$result = false;');
        self::assertNotFalse($start, 'save loop start not found in objects/playlistSort.php');
        $end = strpos($source, 'PlayList::deleteCacheDir(', $start);
        self::assertNotFalse($end, 'save loop end not found in objects/playlistSort.php');
        self::$loop = substr($source, $start, $end - $start);
    }

    protected function setUp(): void
    {
        PlaylistSortState::$playlistVideos = [];
        PlaylistSortState::$addable = [];
        PlaylistSortState::$added = [];
        PlaylistSortState::$securityLogs = [];
    }

    private function runLoop(array $list)
    {
        $this->assertNotEmpty(self::$loop, 'save loop not found in objects/playlistSort.php');
        $obj = new PlayList();
        $o = new \stdClass();
        $o->savedPLItem = [];
        $count = 1;
        $result = false;
        $_POST['list'] = $list;
        eval('namespace Tests\Security\PlaylistSortFixtures; ' . self::$loop);
        unset($_POST['list']);
        return [$o, $result];
    }

    public function testVideoNotInPlaylistThatCannotBeAddedIsRefused(): void
    {
        PlaylistSortState::$playlistVideos = [7];
        [$o, $result] = $this->runLoop(['1', '7']);

        $this->assertSame([[7, 1]], PlaylistSortState::$added);
        $this->assertFalse($o->savedPLItem[0]['resp']);
        $this->assertTrue($o->savedPLItem[1]['resp']);
        $this->assertTrue($result);
        $this->assertCount(1, PlaylistSortState::$securityLogs);
    }

    public function testReorderingVideosAlreadyInPlaylistIsUnchanged(): void
    {
        // Already in the playlist: kept even if the owner could no longer add it today.
        PlaylistSortState::$playlistVideos = [5, 9, 2];
        $this->runLoop(['9', '2', '5']);

        $this->assertSame([[9, 1], [2, 2], [5, 3]], PlaylistSortState::$added);
        $this->assertSame([], PlaylistSortState::$securityLogs);
    }

    public function testNewVideoTheUserMayAddIsStillAccepted(): void
    {
        PlaylistSortState::$playlistVideos = [5];
        PlaylistSortState::$addable = [8 => true];
        $this->runLoop(['5', '8', '', '0']);

        $this->assertSame([[5, 1], [8, 2]], PlaylistSortState::$added);
    }

    public function testRefusedLastVideoKeepsPreviousSaveResult(): void
    {
        PlaylistSortState::$playlistVideos = [7];
        [$o, $result] = $this->runLoop(['7', '1']);

        $this->assertSame([[7, 1]], PlaylistSortState::$added);
        $this->assertTrue($result);
        $this->assertSame(['resp' => false, 'videos_id' => '1', 'order' => 0], $o->savedPLItem[1]);
        $this->assertCount(1, PlaylistSortState::$securityLogs);
    }

    public function testAllNewForbiddenVideosAreRefused(): void
    {
        [$o, $result] = $this->runLoop(['1', '2']);

        $this->assertSame([], PlaylistSortState::$added);
        $this->assertFalse($result);
        $this->assertSame([false, false], array_column($o->savedPLItem, 'resp'));
        $this->assertSame([0, 0], array_column($o->savedPLItem, 'order'));
        $this->assertCount(2, PlaylistSortState::$securityLogs);
    }

    public function testEquivalentIntegerIdsUseTheSameMembershipCheckAsAddVideo(): void
    {
        PlaylistSortState::$playlistVideos = [7];
        [$o, $result] = $this->runLoop(['007', 7]);

        $this->assertSame([[7, 1], [7, 2]], PlaylistSortState::$added);
        $this->assertTrue($result);
        $this->assertSame([], PlaylistSortState::$securityLogs);
    }
}
