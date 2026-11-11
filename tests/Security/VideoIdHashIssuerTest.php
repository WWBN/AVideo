<?php

namespace Tests\Security\VideoIdHashIssuerFixtures;

class State
{
    public static $currentUser = 0;
    public static $loggedAs = 0;
    public static $passwords = [];
    public static $owners = [];
    public static $credentialLogins = 0;
    public static $passwordCache = [];
    public static $times = [];
}

class User
{
    const USER_LOGGED = 0;
    const USER_NOT_FOUND = 2;
    private $id;

    public function __construct($id = 0)
    {
        $this->id = intval($id);
    }

    public static function getId()
    {
        return State::$currentUser;
    }

    public static function isLogged()
    {
        return !empty(State::$currentUser);
    }

    public static function canUpload()
    {
        return self::isLogged();
    }

    public function getIsAdmin()
    {
        return $this->id === 1;
    }

    public function login($noPass = false)
    {
        if (self::isLogged()) {
            return self::USER_LOGGED;
        }
        if (!isset(State::$passwords[$this->id])) {
            return self::USER_NOT_FOUND;
        }
        State::$currentUser = $this->id;
        $_SESSION['user']['id'] = $this->id;
        State::$loggedAs = $this->id;
        return self::USER_LOGGED;
    }

    public static function loginFromRequest()
    {
        State::$credentialLogins++;
        return self::USER_NOT_FOUND;
    }
}

class Permissions
{
    public static function canAdminVideos()
    {
        return in_array(State::$currentUser, [1, 5], true);
    }
}

class sqlDAL
{
    public static function readSql($sql, $formats, $values, $refreshCache = false)
    {
        $id = $values[0];
        if ($refreshCache || !array_key_exists($id, State::$passwordCache)) {
            State::$passwordCache[$id] = isset(State::$passwords[$id]) ? ['password' => State::$passwords[$id]] : false;
        }
        return State::$passwordCache[$id];
    }

    public static function fetchAssoc($row)
    {
        return $row;
    }

    public static function close($res)
    {
    }
}

function time()
{
    return empty(State::$times) ? \time() : array_shift(State::$times);
}

function encryptString($string)
{
    return base64_encode($string);
}

function decryptString($string)
{
    return base64_decode((string) $string, true);
}

function getToken($ttl, $scope)
{
    return 'test-chunk-token';
}

function extractCallable($source, $signature)
{
    $start = strpos($source, $signature);
    if ($start === false) {
        throw new \RuntimeException($signature . ' not found');
    }
    $depth = 0;
    for ($index = strpos($source, '{', $start); $index < strlen($source); $index++) {
        if ($source[$index] === '{') {
            $depth++;
        } elseif ($source[$index] === '}' && --$depth === 0) {
            return substr($source, $start, $index - $start + 1);
        }
    }
    throw new \RuntimeException($signature . ' has no closing brace');
}

// Runs the production hash minting/validation and useVideoHashOrLogin() against the in-memory tables above.
$hashVideoSource = file_get_contents(dirname(__DIR__, 2) . '/objects/video.php');
$hashMethods = '';
foreach ([
    'public function getVideoIdHash(',
    'public static function getVideoIdHashIssuedTo(',
    'public static function getVideoIdFromHash(',
    'private static function getVideoOwnerFingerprint(',
    'private static function getUserPasswordFingerprint(',
    'public static function canEdit(',
    'public static function canEncoderEdit(',
] as $hashSignature) {
    $hashMethods .= extractCallable($hashVideoSource, $hashSignature) . "\n";
}
eval('namespace ' . __NAMESPACE__ . '; use stdClass;
class Video {
    const VIDEO_ID_HASH_LIFETIME = 86400;
    protected $id;
    public function __construct($title, $filename = null, $id = null, $refresh = false) { $this->id = $id ?? $title; }
    public function getId() { return $this->id; }
    public static function getOwner($videos_id) { return State::$owners[$videos_id] ?? null; }
    public static function isOwner($videos_id, $users_id) { return self::getOwner($videos_id) === $users_id; }
    ' . $hashMethods . '
}');
eval('namespace ' . __NAMESPACE__ . '; '
    . extractCallable(file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php'), 'function useVideoHashOrLogin('));
eval('namespace ' . __NAMESPACE__ . ';' . substr(file_get_contents(dirname(__DIR__, 2) . '/objects/EncoderJobStatus.php'), 5));
unset($hashVideoSource, $hashMethods, $hashSignature);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\VideoIdHashIssuerFixtures\State;
use Tests\Security\VideoIdHashIssuerFixtures\Video;
use Tests\Security\VideoIdHashIssuerFixtures\EncoderJobStatus;
use function Tests\Security\VideoIdHashIssuerFixtures\useVideoHashOrLogin;

/**
 * GHSA-8wjr-gw73-9f2g: a Videos Admin passed canEncoderEdit() on an admin's video and received a video_id_hash
 * that useVideoHashOrLogin() turned into a password-less login as the owner, i.e. an administrator session.
 */
class VideoIdHashIssuerTest extends TestCase
{
    private $request;
    private $session;
    private $global;

    protected function setUp(): void
    {
        global $global;
        $this->request = $_REQUEST;
        $this->session = $_SESSION ?? null;
        $this->global = $global;
        $global['saltV2'] = 'test-salt';
        $_SESSION = [];
        State::$currentUser = 0;
        State::$loggedAs = 0;
        State::$credentialLogins = 0;
        State::$passwordCache = [];
        State::$times = [];
        // 1 = admin, 5 = Videos Admin, 6 = uploader
        State::$passwords = [1 => 'admin-hash', 5 => 'videos-admin-hash', 6 => 'uploader-hash'];
        State::$owners = [10 => 1, 20 => 6, 30 => null];
    }

    protected function tearDown(): void
    {
        global $global;
        $_REQUEST = $this->request;
        $_SESSION = $this->session;
        $global = $this->global;
    }

    private function mint($videos_id, $requester)
    {
        State::$currentUser = $requester;
        $hash = (new Video($videos_id))->getVideoIdHash();
        State::$currentUser = 0;
        return $hash;
    }

    private function replay($hash)
    {
        $_SESSION = [];
        State::$currentUser = 0;
        State::$loggedAs = 0;
        $_REQUEST['video_id_hash'] = $hash;
        useVideoHashOrLogin();
        return State::$loggedAs;
    }

    public function testVideosAdminHashForAnAdminVideoDoesNotOpenTheAdminSession()
    {
        $hash = $this->mint(10, 5);
        $this->assertSame(10, Video::getVideoIdFromHash($hash), 'the hash still authorizes the job for that video');
        $this->assertSame(5, $this->replay($hash));
    }

    public function testNonOwnerHashForAnotherUsersVideoLogsInAsTheRequester()
    {
        $this->assertSame(5, $this->replay($this->mint(20, 5)));
        $this->assertSame(1, $this->replay($this->mint(20, 1)), 'an admin hash logs in as the admin');
    }

    public function testOwnerHashStillLogsInAsTheOwner()
    {
        $this->assertSame(1, $this->replay($this->mint(10, 1)));
        $this->assertSame(6, $this->replay($this->mint(20, 6)));
    }

    public function testExpiryBetweenChecksCannotFallBackToTheAdminOwner()
    {
        $hash = $this->mint(10, 5);
        $expires = json_decode(base64_decode($hash))->timeout;
        // The first check accepts the hash; a second check one second later rejects it.
        State::$times = [$expires, $expires + 1];
        $this->assertSame(5, $this->replay($hash), 'resolve the video and issuer together, never fall back to owner 1');
    }

    public function testExpiredIssuerHashCannotLogIn()
    {
        $hash = $this->mint(10, 5);
        State::$times = [json_decode(base64_decode($hash))->timeout + 1];
        $this->assertSame(0, $this->replay($hash));
        $this->assertSame(1, State::$credentialLogins);
    }

    public function testPreflightRenewsTheIssuerForHashOnlyCallbacks()
    {
        foreach ([1, 5, 6] as $requester) {
            $hash = $this->mint(20, $requester);
            $_SESSION = [];
            $_REQUEST = [];
            $status = EncoderJobStatus::inspect(20, $hash);
            $this->assertSame('ready', $status['code']);
            $this->assertSame($requester, Video::getVideoIdHashIssuedTo($status['video_id_hash']));
            $this->assertSame($requester, $this->replay($status['video_id_hash']));
            $this->assertTrue(Video::canEncoderEdit(20), 'media, log and alert callbacks accept the renewed hash');
            $this->assertTrue(Video::canEdit(20), 'image and completion callbacks accept the issuer permissions');
            if ($requester === 6) {
                $this->assertFalse(Video::canEncoderEdit(10), 'an ordinary uploader cannot edit another owner');
            }
        }
    }

    public function testIssuerPasswordChangeRevokesTheHash()
    {
        $hash = $this->mint(20, 5);
        State::$passwords[5] = 'changed';
        $this->assertFalse(Video::getVideoIdFromHash($hash));
        $this->assertSame(0, Video::getVideoIdHashIssuedTo($hash));
        $this->assertSame(0, $this->replay($hash));
        $this->assertSame(1, State::$credentialLogins, 'falls back to the request credentials');
    }

    public function testOwnerPasswordChangeStillRevokesTheHash()
    {
        $hash = $this->mint(20, 5);
        State::$passwords[6] = 'changed';
        $this->assertFalse(Video::getVideoIdFromHash($hash));
    }

    public function testIssuerCannotBeRemovedOrSwapped()
    {
        $payload = json_decode(base64_decode($this->mint(10, 5)));
        unset($payload->issuedToFingerprint);
        $this->assertFalse(Video::getVideoIdFromHash(base64_encode(json_encode($payload))));

        $payload = json_decode(base64_decode($this->mint(10, 5)));
        $payload->issuedTo = 1;
        $this->assertFalse(Video::getVideoIdFromHash(base64_encode(json_encode($payload))));
    }

    public function testHashWithoutIssuerKeepsTheOwnerUntilItExpires()
    {
        // minted before this change, or by a request with no logged user
        $hash = $this->mint(20, 0);
        $this->assertFalse(property_exists(json_decode(base64_decode($hash)), 'issuedTo'));
        $this->assertSame(6, $this->replay($hash));
    }

    public function testLegacyOwnerFingerprintIsUnchanged()
    {
        global $global;
        foreach ([20 => 'uploader-hash', 30 => 'no-owner'] as $videos_id => $password) {
            // Payload and binding from the pre-issuer implementation, independent of the new minter.
            $payload = (object) [
                'videos_id' => $videos_id,
                'time' => time(),
                'timeout' => time() + 86400,
                'ownerFingerprint' => substr(hash('sha256', $password . $global['saltV2']), 0, 16),
            ];
            $hash = base64_encode(json_encode($payload));
            $this->assertSame($videos_id, Video::getVideoIdFromHash($hash));
            $this->assertSame($payload->ownerFingerprint, json_decode(base64_decode($this->mint($videos_id, 0)))->ownerFingerprint);
        }
    }

    public function testOwnerlessVideoKeepsTheCredentialFallback()
    {
        $hash = $this->mint(30, 5);
        $this->assertSame(30, Video::getVideoIdFromHash($hash));
        $this->assertSame(0, $this->replay($hash));
        $this->assertSame(1, State::$credentialLogins);
    }
}
