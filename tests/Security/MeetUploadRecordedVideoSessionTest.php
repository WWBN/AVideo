<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * GHSA-qxvm-r42f-5p8j / GHSA-ggfr-w8qv-5cq3: plugin/Meet/uploadRecordedVideo.json.php must credit the
 * recording to the user named in the filename without giving the caller a session logged in as that
 * user. The caller controls the session ID it arrives with (its cookie or ?PHPSESSID=), so the user
 * must never be written to that session, and the request's own session ID must not be sent back.
 *
 * Runs the endpoint's real session block in a child PHP process against a real "files" session store.
 */
class MeetUploadRecordedVideoSessionTest extends TestCase
{
    private const ENDPOINT = APP_ROOT . '/plugin/Meet/uploadRecordedVideo.json.php';
    private const CALLER_SESSION_ID = 'callerchosensessionid0123456789';

    private $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/avideo-meet-session-' . bin2hex(random_bytes(8));
        mkdir($this->dir . '/sessions', 0700, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/sessions/*') ?: [] as $file) {
            unlink($file);
        }
        foreach (glob($this->dir . '/*.php') ?: [] as $file) {
            unlink($file);
        }
        @rmdir($this->dir . '/sessions');
        @rmdir($this->dir);
    }

    private function getSessionBlock(): string
    {
        $source = file_get_contents(self::ENDPOINT);
        // Everything between the unknown-user check and the temporary upload file.
        $matched = preg_match(
            "/forbiddenPage\('Recording references unknown or inactive user'\);\s*\}\s*(.*?)\s*\\\$tmpFile = /s",
            $source,
            $match
        );
        $this->assertSame(1, $matched, 'Session block not found in uploadRecordedVideo.json.php');
        $this->assertStringContainsString("\$_SESSION['user'] = \$userRow;", $match[1]);
        return $match[1];
    }

    private function runSessionBlock(int $strictMode = 0, bool $authenticatedCaller = false, bool $failSessionIdCreation = false): array
    {
        $sessionIdFailure = $failSessionIdCreation ? <<<'PHP'
namespace MeetUploadSessionTestHarness;
use \User;
function session_create_id() {
    trigger_error('Session handler could not create an ID', E_USER_WARNING);
    return false;
}
PHP
            : '';
        file_put_contents($this->dir . '/block.php', "<?php\n" . $sessionIdFailure . "\n" . $this->getSessionBlock() . "\n");
        $sessionPath = var_export($this->dir . '/sessions', true);
        $blockFile = var_export($this->dir . '/block.php', true);
        $callerId = var_export(self::CALLER_SESSION_ID, true);
        $helpersFile = var_export(APP_ROOT . '/objects/functionsPHP.php', true);
        $userSource = file_get_contents(APP_ROOT . '/objects/user.php');
        $videoSource = file_get_contents(APP_ROOT . '/objects/video.php');
        $userMethods = '';
        foreach (['getUserName', 'getUserPass'] as $method) {
            $this->assertSame(1, preg_match('/public static function ' . $method . '\(\).*?^    }/ms', $userSource, $match));
            $userMethods .= $match[0] . "\n";
        }
        $this->assertSame(1, preg_match('/public function queue\(.*?^        }/ms', $videoSource, $match));
        $queueMethod = $match[0];
        $callerUser = $authenticatedCaller ? "['id' => 42, 'user' => 'caller', 'isAdmin' => 0]" : 'null';
        file_put_contents($this->dir . '/run.php', <<<PHP
<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
ini_set('session.save_handler', 'files');
ini_set('session.save_path', {$sessionPath});
ini_set('session.use_strict_mode', '0');
// Load the real session helpers without the site's configuration/database preload.
\$global = ['webSiteRootURL' => 'https://streamer.example/'];
require {$helpersFile};
function _error_log(\$message, \$level = null) {}
class AVideoLog {
    public static \$ERROR = 1;
    public static \$PERFORMANCE = 2;
}
class User {
    public static function isLogged() { return !empty(\$_SESSION['user']['id']); }
    public static function canUpload() { return self::isLogged(); }
    public static function getUserHash(\$id) { return 'test-encoder-credential-for-' . (int) \$id; }
    {$userMethods}
}
class Video {
    public function getFilename() { return 'recording'; }
    public function getId() { return 9; }
    public static function getDefaultEncoderFormat() { return 'testFormat'; }
    public static function postToEncoderQueue(\$fields) { return \$fields; }
    {$queueMethod}
}
function getToken(\$ttl, \$salt) { return 'test-chunk-token'; }

// What configuration.php leaves behind: the caller's own session, with an ID the caller chose.
session_id({$callerId});
session_start();
\$_SESSION['callerData'] = 'kept';
if ({$callerUser} !== null) {
    \$_SESSION['user'] = {$callerUser};
}
session_write_close();
ini_set('session.use_strict_mode', '{$strictMode}');
_session_start();

\$users_id = 1;
// getUserFromID() returns profile/permission data, without a passhash.
\$userRow = ['id' => 1, 'user' => 'admin', 'isAdmin' => 1];
require {$blockFile};
\$useCookiesAtStart = ini_get('session.use_cookies');

// Code on the upload path closes and reopens the session (e.g. url_get_contents to this site).
_session_write_close();
_session_start();
\$result = [
    'sessionId' => session_id(),
    'useCookies' => \$useCookiesAtStart,
    'userIdAfterReopen' => \$_SESSION['user']['id'] ?? null,
    'callerDataAfterSwitch' => \$_SESSION['callerData'] ?? null,
    'encoderFields' => (new Video())->queue(),
];
session_write_close();
echo json_encode(\$result);
PHP
        );

        $output = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($this->dir . '/run.php') . ' 2>&1');
        $result = json_decode((string) $output, true);
        $this->assertIsArray($result, 'Child process failed: ' . $output);
        return $result;
    }

    public function testCallerSessionNeverReceivesTheSelectedUser(): void
    {
        $result = $this->runSessionBlock();

        $callerFile = $this->dir . '/sessions/sess_' . self::CALLER_SESSION_ID;
        $this->assertFileExists($callerFile);
        $callerData = file_get_contents($callerFile);
        $this->assertStringContainsString('kept', $callerData, 'The caller session must be left as it was');
        $this->assertStringNotContainsString('isAdmin', $callerData, 'The selected user was written to the caller session');
        $this->assertStringNotContainsString('admin', $callerData);

        $this->assertNotSame(self::CALLER_SESSION_ID, $result['sessionId'], 'The upload must not run on the caller session ID');
    }

    public function testUploadSessionIdIsNeverSentAsCookie(): void
    {
        $result = $this->runSessionBlock();
        $this->assertSame('0', (string) $result['useCookies'], 'session.use_cookies must be off before the upload session starts');
    }

    public function testUploadStillRunsAsTheSelectedUserAfterSessionReopen(): void
    {
        $result = $this->runSessionBlock();
        $this->assertSame(1, $result['userIdAfterReopen'], 'aVideoQueueEncoder needs the user in-process, also after a close/reopen');
    }

    public function testEndpointDoesNotLogTheCallerIn(): void
    {
        $source = file_get_contents(self::ENDPOINT);
        $this->assertDoesNotMatchRegularExpression('/->login\s*\(/', $source);
        $this->assertDoesNotMatchRegularExpression('/session_regenerate_id\s*\(/', $source);
    }

    public function testSessionIdFallbackDoesNotEmitWarningsOrReuseTheCallerSession(): void
    {
        $result = $this->runSessionBlock(0, true, true);
        $this->assertNotSame(self::CALLER_SESSION_ID, $result['sessionId']);
        $this->assertSame('0', (string) $result['useCookies']);
        $this->assertSame(1, $result['userIdAfterReopen']);
        $this->assertSame('test-encoder-credential-for-1', $result['encoderFields']['pass']);
        $callerData = file_get_contents($this->dir . '/sessions/sess_' . self::CALLER_SESSION_ID);
        $this->assertStringContainsString('i:42;', $callerData);
        $this->assertStringNotContainsString('admin', $callerData);
        $this->assertStringNotContainsString('test-encoder-credential', $callerData);
    }

    public static function ownerRows(): array
    {
        return [
            'active' => [['id' => 1, 'status' => 'a'], 'ALLOWED'],
            'inactive' => [['id' => 1, 'status' => 'i'], 'FORBIDDEN'],
            'unknown' => [false, 'FORBIDDEN'],
        ];
    }

    /**
     * The former login(true, true) only accepted active accounts; getUserFromID() does not filter by status.
     * @dataProvider ownerRows
     */
    public function testOnlyActiveOwnersAreAccepted($userRow, string $expected): void
    {
        $source = file_get_contents(self::ENDPOINT);
        $this->assertSame(
            1,
            preg_match("/\n(if \([^\n]*\) \{\s*forbiddenPage\('Recording references unknown or inactive user'\);\s*\})/", $source, $match),
            'Owner check not found in uploadRecordedVideo.json.php'
        );
        file_put_contents($this->dir . '/guard.php', "<?php\n"
            . "function forbiddenPage(\$message) { echo 'FORBIDDEN'; exit; }\n"
            . '$userRow = ' . var_export($userRow, true) . ";\n"
            . $match[1] . "\necho 'ALLOWED';\n");
        $output = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($this->dir . '/guard.php') . ' 2>&1');
        $this->assertSame($expected, trim((string) $output));
    }

    public static function sessionConfigurations(): array
    {
        return [[0, false], [1, false], [0, true], [1, true]];
    }

    /** @dataProvider sessionConfigurations */
    public function testEncoderReceivesSelectedUsersCredentialWithoutChangingCaller(int $strictMode, bool $authenticatedCaller): void
    {
        $result = $this->runSessionBlock($strictMode, $authenticatedCaller);
        $this->assertSame('admin', $result['encoderFields']['user']);
        $this->assertSame('test-encoder-credential-for-1', $result['encoderFields']['pass']);
        $this->assertSame('https://streamer.example/', $result['encoderFields']['notifyURL']);
        $this->assertNull($result['callerDataAfterSwitch']);
        $this->assertSame(1, $result['userIdAfterReopen']);
        $this->assertSame('0', (string) $result['useCookies']);
        $this->assertNotSame(self::CALLER_SESSION_ID, $result['sessionId']);

        $callerData = file_get_contents($this->dir . '/sessions/sess_' . self::CALLER_SESSION_ID);
        $this->assertStringContainsString('kept', $callerData);
        $this->assertStringNotContainsString('admin', $callerData);
        $this->assertStringNotContainsString('test-encoder-credential', $callerData);
        if ($authenticatedCaller) {
            $this->assertStringContainsString('i:42;', $callerData);
        }
    }
}
