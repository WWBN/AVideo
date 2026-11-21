<?php

namespace Tests\Security\BookmarkSaveFixtures;

class Forbidden extends \RuntimeException
{
}

// The endpoint source runs inside this namespace, where `new stdClass()` would not resolve.
class stdClass extends \stdClass
{
}

class State
{
    public static $admin = false;
    public static $usersId = 0;
    // videos_id => owner users_id
    public static $videoOwners = [];
    // bookmark id => videos_id
    public static $bookmarks = [];
    public static $saved = [];
}

class User
{
    public static function isAdmin()
    {
        return State::$admin;
    }
}

class Video
{
    public static function canEdit($videos_id)
    {
        if (State::$admin) {
            return true;
        }
        return !empty($videos_id) && isset(State::$videoOwners[$videos_id]) && State::$videoOwners[$videos_id] === State::$usersId;
    }
}

class BookmarkTable
{
    private $id, $name, $timeInSeconds, $videos_id;

    public function __construct($id = '')
    {
        if (!empty($id) && isset(State::$bookmarks[$id])) {
            $this->id = $id;
            $this->videos_id = State::$bookmarks[$id];
        }
    }

    public function getVideos_id()
    {
        return $this->videos_id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setTimeInSeconds($timeInSeconds)
    {
        $this->timeInSeconds = $timeInSeconds;
    }

    public function setVideos_id($videos_id)
    {
        $this->videos_id = $videos_id;
    }

    public function save()
    {
        State::$saved[] = ['id' => $this->id, 'videos_id' => $this->videos_id, 'name' => $this->name];
        return empty($this->id) ? 99 : $this->id;
    }

    public static function getAllFromVideo($videos_id)
    {
        return [];
    }
}

function forbidIfIsUntrustedRequest($label = '')
{
}

function forbiddenPage($msg = '', $log = false)
{
    throw new Forbidden($msg);
}

function __($text)
{
    return $text;
}

/**
 * Runs the real plugin/Bookmark/page/bookmarkSave.json.php body (without its require lines)
 * against the stubs above. No database and no file writes.
 */
class BookmarkSaveRunner
{
    public static function run(array $request)
    {
        $bookmarkSaveSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/Bookmark/page/bookmarkSave.json.php');
        $bookmarkSaveSource = preg_replace('/^\s*require_once[^\n]*\n/m', '', $bookmarkSaveSource);
        $bookmarkSaveSource = preg_replace('/^<\?php/', '', $bookmarkSaveSource);
        $bookmarkSaveOldRequest = $_REQUEST;
        $_REQUEST = $request;
        ob_start();
        try {
            eval('namespace ' . __NAMESPACE__ . '; ' . $bookmarkSaveSource);
        } finally {
            $bookmarkSaveOutput = ob_get_clean();
            $_REQUEST = $bookmarkSaveOldRequest;
        }
        return $bookmarkSaveOutput;
    }
}

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\BookmarkSaveFixtures\BookmarkSaveRunner;
use Tests\Security\BookmarkSaveFixtures\Forbidden;
use Tests\Security\BookmarkSaveFixtures\State;

/**
 * GHSA-w5xw-88j8-fmwp: bookmarkSave.json.php checked only the destination videos_id, so an uploader
 * could overwrite/move a bookmark of another user's video by sending its id.
 */
class BookmarkSaveOwnershipTest extends TestCase
{
    protected function setUp(): void
    {
        State::$admin = false;
        State::$usersId = 2;
        State::$videoOwners = [101 => 2, 105 => 3];
        State::$bookmarks = [7 => 105, 8 => 101];
        State::$saved = [];
    }

    private function request(array $extra)
    {
        return array_merge(['name' => 'Chapter', 'timeInSeconds' => 1, 'videos_id' => 101], $extra);
    }

    public function testCannotTakeOverBookmarkOfAnotherUsersVideo()
    {
        try {
            BookmarkSaveRunner::run($this->request(['id' => 7]));
            $this->fail('Expected the request to be refused');
        } catch (Forbidden $e) {
            $this->assertSame([], State::$saved);
        }
    }

    public function testOwnerCanEditOwnBookmark()
    {
        $output = BookmarkSaveRunner::run($this->request(['id' => 8]));
        $this->assertSame([['id' => 8, 'videos_id' => 101, 'name' => 'Chapter']], State::$saved);
        $this->assertFalse(json_decode($output)->error);
    }

    public function testOwnerCanCreateBookmarkWithoutId()
    {
        BookmarkSaveRunner::run($this->request([]));
        $this->assertSame([['id' => null, 'videos_id' => 101, 'name' => 'Chapter']], State::$saved);
    }

    public function testUnknownIdIsTreatedAsNewBookmark()
    {
        BookmarkSaveRunner::run($this->request(['id' => 12345]));
        $this->assertSame([['id' => null, 'videos_id' => 101, 'name' => 'Chapter']], State::$saved);
    }

    public function testOwnerCanMoveBookmarkBetweenOwnVideos()
    {
        State::$videoOwners[102] = 2;
        BookmarkSaveRunner::run($this->request(['id' => 8, 'videos_id' => 102]));
        $this->assertSame([['id' => 8, 'videos_id' => 102, 'name' => 'Chapter']], State::$saved);
    }

    public function testAdminCanEditAnyBookmark()
    {
        State::$admin = true;
        BookmarkSaveRunner::run($this->request(['id' => 7]));
        $this->assertSame([['id' => 7, 'videos_id' => 101, 'name' => 'Chapter']], State::$saved);
    }
}
