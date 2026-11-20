<?php

namespace Tests\Security\CommentsRepliesFixtures;

// Stubs for the list branch of objects/comments.json.php (never loads videos/configuration.php).
class ForbiddenFixture extends \Exception {}

class CommentsGateState
{
    public static $comments = [];
    public static $watchable = [];
    public static $editable = [];
    public static $canAdminComment = false;
    public static $getAllCommentsCalls = [];
}

function forbiddenPageIfCannotWatchVideo($videos_id)
{
    if (empty($videos_id)) {
        return;
    }
    if (empty(CommentsGateState::$watchable[$videos_id])) {
        throw new ForbiddenFixture('forbidden ' . $videos_id);
    }
}

class Comment
{
    public static function getComment($id)
    {
        return CommentsGateState::$comments[$id] ?? false;
    }
    public static function getAllComments($videoId = 0, $comments_id_pai = 'NULL', $video_owner_users_id = 0, $includeResponses = false)
    {
        CommentsGateState::$getAllCommentsCalls[] = [$videoId, $comments_id_pai];
        return [['id' => 99]];
    }
    public static function getTotalComments($videoId = 0, $comments_id_pai = 'NULL')
    {
        return 1;
    }
}

class Video
{
    public static function canEdit($videos_id)
    {
        return !empty(CommentsGateState::$editable[$videos_id]);
    }
}

class Permissions
{
    public static function canAdminComment()
    {
        return CommentsGateState::$canAdminComment;
    }
}

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\CommentsRepliesFixtures\CommentsGateState;
use Tests\Security\CommentsRepliesFixtures\ForbiddenFixture;

/**
 * GHSA-fchf-c8x4-gj76: comments.json.php?comments_id=<parent> without video_id must not return the
 * replies of a video the caller cannot watch.
 */
class CommentsRepliesWatchGateTest extends TestCase
{
    private static $branch;

    public static function setUpBeforeClass(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/comments.json.php');
        $start = strpos($source, 'if(empty($_REQUEST[\'id\'])){');
        $end = strpos($source, '$comments = Comment::addExtraInfo2InRows', $start);
        self::$branch = substr($source, $start, $end - $start);
    }

    protected function setUp(): void
    {
        CommentsGateState::$comments = [
            1 => ['id' => 1, 'videos_id' => 10, 'comments_id_pai' => null], // parent on restricted video 10
            2 => ['id' => 2, 'videos_id' => 20, 'comments_id_pai' => null], // parent on public video 20
        ];
        CommentsGateState::$watchable = [20 => true];
        CommentsGateState::$editable = [];
        CommentsGateState::$canAdminComment = false;
        CommentsGateState::$getAllCommentsCalls = [];
        $_POST = ['sort' => ['id' => 'DESC']];
    }

    private function run_branch(array $request)
    {
        $_REQUEST = $request;
        $comments = null;
        eval('namespace Tests\Security\CommentsRepliesFixtures; ' . self::$branch);
        return $comments;
    }

    public function testRepliesOfUnwatchableVideoAreRefusedWithoutVideoId()
    {
        $this->expectException(ForbiddenFixture::class);
        try {
            $this->run_branch(['video_id' => 0, 'comments_id' => 1]);
        } finally {
            $this->assertSame([], CommentsGateState::$getAllCommentsCalls);
        }
    }

    public function testRepliesOfWatchableVideoStillLoadWithoutVideoId()
    {
        $this->assertSame([['id' => 99]], $this->run_branch(['video_id' => 0, 'comments_id' => 2]));
    }

    public function testVideoPageRequestIsUnchanged()
    {
        // The watch page always sends video_id; that path is gated by video_id and filtered by it in SQL.
        $this->assertSame([['id' => 99]], $this->run_branch(['video_id' => 20, 'comments_id' => 2]));
        $this->assertSame([[20, 2]], CommentsGateState::$getAllCommentsCalls);
    }

    public function testCommentModeratorAndVideoEditorKeepAccessInCommentsManager()
    {
        CommentsGateState::$canAdminComment = true;
        $this->assertSame([['id' => 99]], $this->run_branch(['video_id' => 0, 'comments_id' => 1]));

        CommentsGateState::$canAdminComment = false;
        CommentsGateState::$editable = [10 => true];
        $this->assertSame([['id' => 99]], $this->run_branch(['video_id' => 0, 'comments_id' => 1]));
    }

    public function testUnknownParentReturnsTheSameAsBefore()
    {
        $this->assertSame([['id' => 99]], $this->run_branch(['video_id' => 0, 'comments_id' => 404]));
    }
}
