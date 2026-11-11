<?php

namespace Tests\Security\ADsEditorFixtures;

class Forbidden extends \RuntimeException
{
}

class UserState
{
    public static $admin = false;
    public static $id = 0;
}

class User
{
    public static function isAdmin()
    {
        return UserState::$admin;
    }

    public static function getId()
    {
        return UserState::$id;
    }
}

function getVideosDir()
{
    return '/var/www/videos/';
}

function getCDN()
{
    return 'https://example.test/';
}

function make_path($path)
{
    return true;
}

function _glob($dir, $pattern)
{
    return [];
}

function forbiddenPage($msg = '')
{
    throw new Forbidden($msg);
}

// Runs the production methods against the stubs above without accessing installation data.
$adsSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/ADs.php');
$adsMethods = '';
foreach (['getAdsPath', 'getEditableAdsUsersId', 'saveAdsHTML'] as $adsMethodName) {
    $adsStart = strpos($adsSource, "public static function {$adsMethodName}(");
    if ($adsStart === false) {
        $adsMethods .= "public static function {$adsMethodName}() { throw new \\RuntimeException('ADs::{$adsMethodName}() not found'); }\n";
        continue;
    }
    $adsDepth = 0;
    for ($adsIndex = strpos($adsSource, '{', $adsStart); $adsIndex < strlen($adsSource); $adsIndex++) {
        if ($adsSource[$adsIndex] === '{') {
            $adsDepth++;
        } elseif ($adsSource[$adsIndex] === '}' && --$adsDepth === 0) {
            break;
        }
    }
    $adsMethods .= substr($adsSource, $adsStart, $adsIndex - $adsStart + 1) . "\n";
}
eval('namespace ' . __NAMESPACE__ . ';
class ADs {
    const AdsPositions = [["leaderBoardTop", 1], ["sideBar", 0]];
    public static $parameterUpdates = [];

    public function updateParameter($name, $value)
    {
        self::$parameterUpdates[] = [$name, $value];
        return true;
    }

    ' . $adsMethods . '
}');
unset($adsSource, $adsMethods, $adsMethodName, $adsStart, $adsDepth, $adsIndex);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\ADsEditorFixtures\ADs;
use Tests\Security\ADsEditorFixtures\Forbidden;
use Tests\Security\ADsEditorFixtures\UserState;

/**
 * GHSA-58h3-9722-fgpm: deleteImage.json.php appended the request's is_regular_user to the ads folder, so a user
 * with "Can have custom ads" deleted other users' ads, the site-wide ads, or files outside videos/ADs/ via "../".
 */
class ADsEditorOwnershipTest extends TestCase
{
    private $request;

    protected function setUp(): void
    {
        $this->request = $_REQUEST;
        UserState::$admin = false;
        UserState::$id = 5;
        ADs::$parameterUpdates = [];
    }

    protected function tearDown(): void
    {
        $_REQUEST = $this->request;
    }

    public function testAdsPathNeverContainsAPathFragment()
    {
        $this->assertSame('/var/www/videos/ADs/leaderBoardTop/', ADs::getAdsPath('leaderBoardTop', '../../')['path']);
        $this->assertSame('/var/www/videos/ADs/leaderBoardTop/', ADs::getAdsPath('leaderBoardTop', '../../../')['path']);
        $this->assertSame('/var/www/videos/ADs/leaderBoardTop/7/', ADs::getAdsPath('leaderBoardTop', '7/../../')['path']);
        $this->assertSame('https://example.test/videos/ADs/leaderBoardTop/', ADs::getAdsPath('leaderBoardTop', '../../')['url']);
        $this->assertFalse(ADs::getAdsPath('../leaderBoardTop', 0));
    }

    public function testAdsPathKeepsLegitimateOwners()
    {
        $this->assertSame('/var/www/videos/ADs/leaderBoardTop/', ADs::getAdsPath('leaderBoardTop', false)['path']);
        $this->assertSame('/var/www/videos/ADs/leaderBoardTop/', ADs::getAdsPath('leaderBoardTop', 0)['path']);
        $this->assertSame('/var/www/videos/ADs/leaderBoardTop/', ADs::getAdsPath('leaderBoardTop')['path']);
        $this->assertSame('/var/www/videos/ADs/sideBar/7/', ADs::getAdsPath('sideBar', 7)['path']);
        $this->assertSame('/var/www/videos/ADs/sideBar/7/', ADs::getAdsPath('sideBar', '7')['path']);
        $this->assertSame('https://example.test/videos/ADs/sideBar/7/', ADs::getAdsPath('sideBar', 7)['url']);
    }

    public function testRegularUserAlwaysEditsTheirOwnAds()
    {
        foreach (['7', '0', '', 'false', '../../', '5'] as $requested) {
            $_REQUEST['is_regular_user'] = $requested;
            $this->assertSame(5, ADs::getEditableAdsUsersId(), "is_regular_user={$requested}");
        }
        unset($_REQUEST['is_regular_user']);
        $this->assertSame(5, ADs::getEditableAdsUsersId(), 'site-wide ads are not the default for a regular user');
    }

    public function testRegularUserWithoutSessionIsRefused()
    {
        UserState::$id = 0;
        $_REQUEST['is_regular_user'] = '0';
        $this->expectException(Forbidden::class);
        ADs::getEditableAdsUsersId();
    }

    public function testAdminChoosesTheAdsFolder()
    {
        UserState::$admin = true;
        UserState::$id = 1;
        // the admin editor sends is_regular_user=false for the site-wide ads and its own id with ?customAds=1
        $_REQUEST['is_regular_user'] = 'false';
        $this->assertSame(0, ADs::getEditableAdsUsersId());
        $_REQUEST['is_regular_user'] = '1';
        $this->assertSame(1, ADs::getEditableAdsUsersId());
        $_REQUEST['is_regular_user'] = '7';
        $this->assertSame(7, ADs::getEditableAdsUsersId());
        $_REQUEST['is_regular_user'] = '../../';
        $this->assertSame(0, ADs::getEditableAdsUsersId());
        unset($_REQUEST['is_regular_user']);
        $this->assertSame(0, ADs::getEditableAdsUsersId());
    }

    public function testEditorEndpointsTakeTheOwnerFromTheSession()
    {
        $root = dirname(__DIR__, 2) . '/plugin/ADs/';
        foreach (['deleteImage.json.php', 'saveImage.json.php', 'saveImagesMetadata.php'] as $file) {
            $source = file_get_contents($root . $file);
            $this->assertStringContainsString('ADs::getEditableAdsUsersId()', $source, $file);
            $this->assertStringNotContainsString("\$_REQUEST['is_regular_user']", $source, $file);
        }
    }

    /**
     * @dataProvider deleteOwnerProvider
     */
    public function testDeleteOnlyRefreshesSiteWideAdsForTheSiteWideFolder($admin, $requested, $refresh)
    {
        UserState::$admin = $admin;
        $_REQUEST = ['fileName' => 'abc123', 'is_regular_user' => $requested];
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/ADs/deleteImage.json.php');
        $start = strpos($source, '$fileName = preg_replace');
        $end = strrpos($source, 'die(json_encode($result));');
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $this->assertGreaterThan($start, $end);

        // Execute the real deletion/refresh code; _glob() returns no files so unlink() is never reached.
        $delete = eval('namespace Tests\\Security\\ADsEditorFixtures; return static function ($type) {
            $result = new \\stdClass();
            ' . substr($source, $start, $end - $start) . '
            return $result;
        };');
        $result = $delete('leaderBoardTop');

        $this->assertFalse($result->error);
        $this->assertSame($refresh, $result->save);
        $this->assertSame($refresh ? [['leaderBoardTop', '']] : [], ADs::$parameterUpdates);
    }

    public static function deleteOwnerProvider(): array
    {
        return [
            'creator requesting site-wide ads' => [false, 'false', false],
            'creator requesting another owner' => [false, '7', false],
            'creator requesting traversal' => [false, '../../', false],
            'admin editing site-wide ads' => [true, 'false', true],
            'admin editing custom ads' => [true, '5', false],
        ];
    }
}
