<?php

namespace Tests\Unit\UserGroupsInsertId;

use PHPUnit\Framework\TestCase;

class Fixture
{
    public static $writeSucceeds = true;
    public static $invalidations = 0;
}

class sqlDAL
{
    public static function writeSql($sql, $formats, $values)
    {
        $GLOBALS['global']['mysqli']->insert_id = strpos($sql, 'INSERT') === 0 ? 902 : 0;
        return Fixture::$writeSucceeds;
    }
    public static function readSql($sql, $formats, $values) { return true; }
    public static function fetchAssoc($result) { return ['id' => 42, 'group_name' => 'Existing group']; }
    public static function close($result) {}
}

class ObjectYPT
{
    public static function deleteCache($name, $subdirs)
    {
        Fixture::$invalidations++;
        // The real invalidation can execute DELETE and reset mysqli::insert_id.
        $GLOBALS['global']['mysqli']->insert_id = 0;
    }
}

function _substr($text, $start, $length) { return mb_substr($text, $start, $length); }

$source = file_get_contents(dirname(__DIR__, 2) . '/objects/userGroups.php');
eval('namespace ' . __NAMESPACE__ . ';' . substr($source, strpos($source, 'class UserGroups')));

class UserGroupsInsertIdTest extends TestCase
{
    private $previousGlobal;

    protected function setUp(): void
    {
        $this->previousGlobal = $GLOBALS['global'] ?? null;
        $GLOBALS['global'] = ['mysqli' => (object) ['insert_id' => 0]];
        Fixture::$writeSucceeds = true;
        Fixture::$invalidations = 0;
    }

    protected function tearDown(): void
    {
        $GLOBALS['global'] = $this->previousGlobal;
    }

    public function testInsertReturnsTheGroupIdEvenWhenInvalidationWritesToMysql(): void
    {
        $group = new UserGroups(0, 'Contest judges');
        $this->assertSame(902, $group->save());
        $this->assertSame(1, Fixture::$invalidations);
        $this->assertSame(0, $GLOBALS['global']['mysqli']->insert_id);
    }

    public function testUpdatingAGroupReturnsItsExistingId(): void
    {
        $group = new UserGroups(42);
        $group->setGroup_name('Updated judges');
        $this->assertSame(42, $group->save());
        $this->assertSame(1, Fixture::$invalidations);
    }

    public function testFailedWriteDoesNotInvalidateOrReturnAnId(): void
    {
        Fixture::$writeSucceeds = false;
        $this->assertFalse((new UserGroups(0, 'Contest judges'))->save());
        $this->assertSame(0, Fixture::$invalidations);
    }
}
