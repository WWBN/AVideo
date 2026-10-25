<?php

namespace Tests\UserDisplayLookup;

use PHPUnit\Framework\TestCase;

$userDisplaySource = file_get_contents(dirname(__DIR__, 2) . '/objects/user.php');
eval('namespace ' . __NAMESPACE__ . '; use \stdClass; ' . substr($userDisplaySource, strpos($userDisplaySource, 'class User')));

class sqlDAL
{
    public static $row;
    public static $cache = [];
    public static $reads = 0;
    public static function readSql($sql, $formats = '', $values = [], $refresh = false)
    {
        $key = $sql . json_encode($values);
        if ($refresh || !array_key_exists($key, self::$cache)) {
            self::$reads++;
            self::$cache[$key] = self::$row;
        }
        return self::$cache[$key];
    }
    public static function fetchAssoc($result) { return $result; }
    public static function close($result) {}
}

function encryptString($value)
{
    $GLOBALS['displayHashCalls']++;
    return base64_encode(json_encode($value));
}

class UserDisplayLookupTest extends TestCase
{
    protected function setUp(): void
    {
        sqlDAL::$row = ['id' => 7, 'user' => 'fixture', 'channelName' => 'Fixture Channel', 'photoURL' => 'https://example.test/avatar.png', 'password' => str_repeat('a', 32), 'isAdmin' => 0];
        sqlDAL::$cache = [];
        sqlDAL::$reads = 0;
        $GLOBALS['displayHashCalls'] = 0;
    }

    public function testChannelNamesShareTheRequestLookupAndKeepOutputFiltering()
    {
        sqlDAL::$row['channelName'] = '<b>Fixture Channel</b>';
        for ($i = 0; $i < 100; $i++) {
            $this->assertSame('Fixture Channel', User::_getChannelName(7));
        }
        $this->assertSame(1, sqlDAL::$reads);
        $this->assertSame(0, $GLOBALS['displayHashCalls']);
    }

    public function testPhotosDoNotGenerateUnusedLoginCredentials()
    {
        for ($i = 0; $i < 50; $i++) {
            $this->assertSame('https://example.test/avatar.png', User::getPhoto(7));
        }
        $this->assertSame(1, sqlDAL::$reads);
        $this->assertSame(0, $GLOBALS['displayHashCalls']);
    }

    public function testAuthorizationAndCredentialReadsRemainFreshAfterDisplayCaching()
    {
        $this->assertSame('Fixture Channel', User::_getChannelName(7));
        $this->assertSame(0, (new User(7))->getIsAdmin());
        sqlDAL::$row['isAdmin'] = 1;
        sqlDAL::$row['password'] = str_repeat('b', 32);
        $this->assertSame(1, (new User(7))->getIsAdmin());
        $token = User::getUserHash(7);
        $payload = json_decode(base64_decode(substr($token, strlen('_user_hash_'))), true);
        $this->assertSame(str_repeat('b', 32), $payload['p']);
        $this->assertSame(4, sqlDAL::$reads);
    }
}
