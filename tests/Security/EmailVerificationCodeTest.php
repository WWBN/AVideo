<?php

namespace Tests\Security\EmailVerificationFixtures;

function _error_log($message, $type = '') {}

function _json_decode($object, $associative = false)
{
    return json_decode($object, $associative);
}

class UserStore
{
    public static $rows = [];
}

// Runs the production User::verifyCode()/createVerificationCode()/decodeVerificationCode() bodies against an
// in-memory user table, without loading the configuration or the database.
$emailVerificationUserSource = file_get_contents(dirname(__DIR__, 2) . '/objects/user.php');
$emailVerificationMethods = '';
foreach (['public static function verifyCode(', 'public static function createVerificationCode(', 'public static function decodeVerificationCode('] as $emailVerificationSignature) {
    $emailVerificationStart = strpos($emailVerificationUserSource, $emailVerificationSignature);
    $emailVerificationDepth = 0;
    for ($emailVerificationIndex = strpos($emailVerificationUserSource, '{', $emailVerificationStart); $emailVerificationIndex < strlen($emailVerificationUserSource); $emailVerificationIndex++) {
        if ($emailVerificationUserSource[$emailVerificationIndex] === '{') {
            $emailVerificationDepth++;
        } elseif ($emailVerificationUserSource[$emailVerificationIndex] === '}' && --$emailVerificationDepth === 0) {
            break;
        }
    }
    $emailVerificationMethods .= substr($emailVerificationUserSource, $emailVerificationStart, $emailVerificationIndex - $emailVerificationStart + 1) . "\n";
}
eval('namespace ' . __NAMESPACE__ . '; use stdClass;
class User {
    private $id; private $recoverPass; private $emailVerified;
    public function __construct($id) {
        $this->id = (int) $id;
        $this->recoverPass = UserStore::$rows[$this->id]["recoverPass"] ?? null;
        $this->emailVerified = UserStore::$rows[$this->id]["emailVerified"] ?? 0;
    }
    public function getRecoverPass() { return strip_tags($this->recoverPass ?? ""); }
    public function setRecoverPass($forceChange = false) { $this->recoverPass = "token" . bin2hex(random_bytes(16)); return $this->recoverPass; }
    public function setEmailVerified($emailVerified) { $this->emailVerified = $emailVerified; }
    public function save() { UserStore::$rows[$this->id] = ["recoverPass" => $this->recoverPass, "emailVerified" => $this->emailVerified]; return $this->id; }
    ' . $emailVerificationMethods . '
}');
unset($emailVerificationUserSource, $emailVerificationMethods, $emailVerificationSignature, $emailVerificationStart, $emailVerificationDepth, $emailVerificationIndex);

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\EmailVerificationFixtures\User;
use Tests\Security\EmailVerificationFixtures\UserStore;

/**
 * GHSA-f72w-prhp-h385: the verification code is base64(JSON) whose salt field is the same for every user, so the
 * stored recoverPass is the only secret. A loose == accepted "recoverPass": true (any stored token) and "" (no token).
 */
class EmailVerificationCodeTest extends TestCase
{
    private $previousSalt;
    private $hadSalt;

    protected function setUp(): void
    {
        $this->hadSalt = isset($GLOBALS['global']['salt']);
        $this->previousSalt = $this->hadSalt ? $GLOBALS['global']['salt'] : null;
        $GLOBALS['global']['salt'] = 'test-salt-' . bin2hex(random_bytes(8));
        UserStore::$rows = [
            5 => ['recoverPass' => null, 'emailVerified' => 0],
            7 => ['recoverPass' => 'stored-' . bin2hex(random_bytes(16)), 'emailVerified' => 0],
            8 => ['recoverPass' => null, 'emailVerified' => 0],
        ];
    }

    protected function tearDown(): void
    {
        if ($this->hadSalt) {
            $GLOBALS['global']['salt'] = $this->previousSalt;
        } else {
            unset($GLOBALS['global']['salt']);
        }
    }

    private function forge($usersId, $recoverPass)
    {
        return base64_encode(json_encode(['users_id' => $usersId, 'salt' => hash('sha256', $GLOBALS['global']['salt']), 'recoverPass' => $recoverPass]));
    }

    public function testLinkFromTheVerificationEmailStillVerifies()
    {
        // the email link is urlencode(code); the browser request gives the decoded value back in $_GET
        $code = urldecode(urlencode(User::createVerificationCode(5)));
        $this->assertTrue(User::verifyCode($code));
        $this->assertSame(1, UserStore::$rows[5]['emailVerified']);
    }

    public function testForgedCodesAreRefused()
    {
        foreach ([[7, true], [7, 1], [7, 0], [7, null], [7, [] ], [7, 'wrong'], [8, ''], [8, false], [8, null], [8, 0]] as [$usersId, $recoverPass]) {
            $this->assertFalse(User::verifyCode($this->forge($usersId, $recoverPass)), json_encode([$usersId, $recoverPass]));
            $this->assertSame(0, UserStore::$rows[$usersId]['emailVerified'], json_encode([$usersId, $recoverPass]));
        }
        $this->assertFalse(User::verifyCode(base64_encode(json_encode(['users_id' => 8, 'salt' => hash('sha256', $GLOBALS['global']['salt'])]))));
    }

    public function testOnlyTheStoredTokenOfThatUserVerifies()
    {
        $this->assertFalse(User::verifyCode($this->forge(8, UserStore::$rows[7]['recoverPass'])));
        $this->assertTrue(User::verifyCode($this->forge(7, UserStore::$rows[7]['recoverPass'])));
    }

    public function testMalformedCodeIsRefused()
    {
        $this->assertFalse(User::verifyCode('not base64 json'));
        $this->assertFalse(User::verifyCode(base64_encode('"text"')));
    }
}
