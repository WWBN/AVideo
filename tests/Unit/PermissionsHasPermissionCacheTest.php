<?php

namespace Tests\Fixtures\PermissionsCache;

use PHPUnit\Framework\TestCase;

class User
{
    public static $loggedId = 0;
    public static $isAdmin = false;

    public static function isLogged()
    {
        return !empty(self::$loggedId);
    }

    public static function isAdmin($users_id = 0): bool
    {
        return self::$isAdmin;
    }

    public static function getId()
    {
        return self::$loggedId;
    }
}

class UserGroups
{
    public static $groups = [];
    public static $calls = 0;

    public static function getUserGroups($users_id)
    {
        self::$calls++;
        return self::$groups[$users_id] ?? [];
    }
}

class Users_groups_permissions
{
    public static $permissions = [];

    public static function getAllFromUserGorup($users_groups_id)
    {
        return self::$permissions[$users_groups_id] ?? [];
    }
}

class PermissionsHasPermissionCacheTest extends TestCase
{
    private static $loaded = false;

    protected function setUp(): void
    {
        if (!self::$loaded) {
            $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/Permissions/Permissions.php');
            $this->assertSame(1, preg_match('/(static function hasPermission\(\$type, \$pluginName, \$users_id = 0\)\s*\{.*?\n    \})/s', $source, $method));
            eval('namespace ' . __NAMESPACE__ . '; class Permissions { ' . $method[1] . ' }');
            self::$loaded = true;
        }
        unset($GLOBALS['hasPermission']);
        User::$loggedId = 0;
        User::$isAdmin = false;
        UserGroups::$calls = 0;
        // user 7 is in group 70, which has permission 0 of the plugin Test; user 5 has no group
        UserGroups::$groups = [7 => [['id' => 70]], 5 => []];
        Users_groups_permissions::$permissions = [70 => ['Test' => [0]]];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['hasPermission']);
    }

    public function testCheckForAnotherUserDoesNotAnswerTheLoggedUser(): void
    {
        User::$loggedId = 5;
        $this->assertTrue(Permissions::hasPermission(0, 'Test', 7));
        $this->assertFalse(Permissions::hasPermission(0, 'Test'));
    }

    public function testLoggedUserResultIsCachedPerUser(): void
    {
        User::$loggedId = 7;
        $this->assertTrue(Permissions::hasPermission(0, 'Test'));
        $this->assertTrue(Permissions::hasPermission(0, 'Test'));
        $this->assertSame(1, UserGroups::$calls);
        User::$loggedId = 5;
        $this->assertFalse(Permissions::hasPermission(0, 'Test'));
        $this->assertFalse(Permissions::hasPermission(0, 'Test', 5));
        $this->assertTrue(Permissions::hasPermission(0, 'Test', 7));
    }

    public function testGuestAndAdminShortcutsAreKept(): void
    {
        $this->assertFalse(Permissions::hasPermission(0, 'Test'));
        User::$loggedId = 5;
        User::$isAdmin = true;
        $this->assertTrue(Permissions::hasPermission(0, 'Test'));
        $this->assertFalse(Permissions::hasPermission(0, 'Test', 5));
        $this->assertSame(1, UserGroups::$calls);
    }
}
