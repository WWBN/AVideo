<?php
// Isolated real User methods; never bootstrap an installation or touch real accounts.
class Permissions
{
    public static $allowed = true;
    public static function canAdminUsers() { return self::$allowed; }
}
class AVideoLog { public static $SECURITY = 1; public static $ERROR = 2; }
function _error_log($message, $level = null) {}
function isCommandLineInterface() { return !empty($GLOBALS['fixtureCLI']); }
function _empty($value) { return empty($value); }
function getRealIpAddr() { return '127.0.0.1'; }
class sqlDAL
{
    public static $row;
    public static $locked = false;
    public static $failLock = false;
    public static $gets = 0;
    public static $releases = 0;
    public static $writes = 0;
    public static $promoteOnLock = false;
    public static $failTargetRead = false;
    public static function readSql($sql, $formats = '', $values = [], $refresh = false)
    {
        if (strpos($sql, 'GET_LOCK') !== false) {
            if (!$refresh) throw new RuntimeException('Lock query was cached');
            self::$gets++;
            if (self::$failLock) return false;
            self::$locked = true;
            if (self::$promoteOnLock) self::$row['isAdmin'] = 1;
            return ['locked' => 1];
        }
        if (strpos($sql, 'RELEASE_LOCK') !== false) {
            if (!$refresh) throw new RuntimeException('Unlock query was cached');
            self::$releases++;
            self::$locked = false;
            return ['released' => 1];
        }
        if (strpos($sql, 'SELECT * FROM users WHERE  id = ?') === 0) {
            if (!$refresh) throw new RuntimeException('Target read was cached');
            return self::$row;
        }
        if ($sql === 'SELECT isAdmin FROM users WHERE id = ? FOR UPDATE') {
            if (!self::$locked || !$refresh) throw new RuntimeException('Target authorization read was not locked/fresh');
            if (self::$failTargetRead) return false;
            return ['isAdmin' => self::$row['isAdmin']];
        }
        return [];
    }
    public static function fetchAssoc($res) { return $res; }
    public static function num_rows($res) { return 0; }
    public static function close($res) {}
    public static function writeSql($sql, $formats = '', $values = [])
    {
        if (!self::$locked) throw new RuntimeException('Account write happened without a lock');
        self::$writes++;
        return true;
    }
}
function checkFixture($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}
$root = dirname(__DIR__, 2);
require_once $root . '/objects/UserAccountMutationLock.php';
$source = file_get_contents($root . '/objects/user.php');
eval(substr($source, strpos($source, 'class User')));
$mysqlDatabase = 'isolated-user-mutation-fixture';
$config = null;
$advancedCustomUser = new stdClass();
$_SESSION = ['user' => ['id' => 5, 'isAdmin' => 0]];
$_SERVER['HTTP_USER_AGENT'] = 'fixture';
sqlDAL::$row = ['id' => 7, 'user' => 'target', 'password' => str_repeat('a', 32), 'isAdmin' => 0, 'channelName' => 'target'];

// Nested endpoint/core locks must remain held until the outer operation ends.
$outer = UserAccountMutationLock::acquire(7);
$inner = UserAccountMutationLock::acquire(7);
unset($inner);
checkFixture(sqlDAL::$locked && sqlDAL::$gets === 1, 'Nested call released or reacquired the lock');
unset($outer);
checkFixture(!sqlDAL::$locked && sqlDAL::$releases === 1, 'Outer scope did not release the lock');

// A stale object cannot delete a target promoted before its lock was obtained.
$target = new User(7);
sqlDAL::$promoteOnLock = true;
checkFixture(!$target->delete() && sqlDAL::$writes === 0, 'Concurrent promotion bypassed core deletion');
checkFixture(!sqlDAL::$locked, 'Denied deletion leaked a lock');
sqlDAL::$promoteOnLock = false;

// Full admin and CLI retain their permissions; delegated and self deletion of ordinary users work.
$_SESSION['user']['isAdmin'] = 1;
checkFixture($target->delete(), 'Real admin could not delete another admin');
$_SESSION['user']['isAdmin'] = 0;
$fixtureCLI = true;
checkFixture($target->delete(), 'CLI admin-target deletion failed');
$fixtureCLI = false;
sqlDAL::$row['isAdmin'] = 0;
checkFixture($target->delete(), 'Delegated ordinary-target deletion failed');
Permissions::$allowed = false;
$_SESSION['user']['id'] = 7;
checkFixture($target->delete(), 'Ordinary self deletion failed');
Permissions::$allowed = true;

// Existing-account saves participate in the same lock, including full-admin promotion.
$_SESSION['user']['isAdmin'] = 1;
$target->setIsAdmin(1);
checkFixture($target->save() === 7 && !sqlDAL::$locked, 'Admin promotion did not lock/release its write');
$before = sqlDAL::$writes;
sqlDAL::$failLock = true;
checkFixture(!$target->save() && !$target->delete() && sqlDAL::$writes === $before, 'Lock failure allowed writes');
checkFixture(!sqlDAL::$locked, 'Failed lock was treated as acquired');
sqlDAL::$failLock = false;
sqlDAL::$failTargetRead = true;
checkFixture(!UserAccountMutationLock::acquire(7) && !sqlDAL::$locked, 'Failed target read leaked its lock');
echo "Account mutation regression: passed\n";
