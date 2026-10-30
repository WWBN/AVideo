<?php

namespace Tests\Fixtures\DonationLink;

use PHPUnit\Framework\TestCase;

class AVideoPlugin
{
    public static $obj = null;

    public static function getDataObjectIfEnabled($name)
    {
        return self::$obj;
    }
}

class User
{
    public static $admins = [];

    public static function isAdmin($users_id = 0): bool
    {
        return in_array($users_id, self::$admins, true);
    }
}

class Permissions
{
    public static $allowed = [];
    public static $calls = [];

    public static function hasPermission($type, $pluginName, $users_id = 0)
    {
        self::$calls[] = [$type, $pluginName, $users_id];
        return in_array($users_id, self::$allowed, true);
    }
}

class CustomizeUserDonationLinkTest extends TestCase
{
    private static $loaded = false;

    protected function setUp(): void
    {
        if (!self::$loaded) {
            $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/CustomizeUser/CustomizeUser.php');
            $this->assertSame(1, preg_match('/const PERMISSION_CAN_USE_DONATION_LINK = (\d+);/', $source, $const));
            $this->assertSame(1, preg_match('/(static function canUseDonationLink\(\$users_id\)\s*\{.*?\n    \})/s', $source, $method));
            $body = str_replace("class_exists('Permissions')", "class_exists('" . __NAMESPACE__ . "\\\\Permissions')", $method[1]);
            eval('namespace ' . __NAMESPACE__ . '; class CustomizeUser { const PERMISSION_CAN_USE_DONATION_LINK = ' . $const[1] . '; ' . $body . ' }');
            self::$loaded = true;
        }
        User::$admins = [];
        Permissions::$allowed = [];
        Permissions::$calls = [];
    }

    private function config($allow, $mode = null)
    {
        $obj = new \stdClass();
        $obj->allowDonationLink = $allow;
        if ($mode !== null) {
            $o = new \stdClass();
            $o->value = $mode;
            $obj->donationLinkWhoCanUse = $o;
        }
        AVideoPlugin::$obj = $obj;
    }

    public function testDisabledOrMissingPluginBlocksEveryone(): void
    {
        AVideoPlugin::$obj = null;
        $this->assertFalse(CustomizeUser::canUseDonationLink(5));
        $this->config(false, 'everyone');
        User::$admins = [1];
        $this->assertFalse(CustomizeUser::canUseDonationLink(1));
    }

    public function testEveryoneModeIsTheDefaultForOldSettings(): void
    {
        $this->config(true);
        $this->assertTrue(CustomizeUser::canUseDonationLink(5));
        $this->config(true, 'everyone');
        $this->assertTrue(CustomizeUser::canUseDonationLink(5));
        $this->assertSame([], Permissions::$calls);
        $this->assertFalse(CustomizeUser::canUseDonationLink(0));
    }

    public function testUserGroupModeChecksTheLinkOwnerPermission(): void
    {
        $this->config(true, 'usergroup');
        Permissions::$allowed = [7];
        User::$admins = [1];
        $this->assertTrue(CustomizeUser::canUseDonationLink(7));
        $this->assertFalse(CustomizeUser::canUseDonationLink(5));
        $this->assertTrue(CustomizeUser::canUseDonationLink(1));
        $this->assertSame([[0, 'CustomizeUser', 7], [0, 'CustomizeUser', 5]], Permissions::$calls);
    }

    public function testStoredPlainStringModeIsAccepted(): void
    {
        $this->config(true);
        AVideoPlugin::$obj->donationLinkWhoCanUse = 'usergroup';
        $this->assertFalse(CustomizeUser::canUseDonationLink(5));
    }
}
