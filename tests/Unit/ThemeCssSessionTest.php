<?php

namespace Tests\Unit\ThemeCssSession;

use PHPUnit\Framework\TestCase;

/**
 * view/theme.css.php is loaded as a stylesheet by the Encoder pages. It used to open its own
 * "themeCSSSession" session; PHP kept that session's ID and the main session started later in the
 * same request reused it, so the response replaced the browser's login cookie and opening the
 * Encoder logged the user out of the Streamer (issue #10674). The file must use only the normal
 * startup session.
 */
class ThemeCssSessionTest extends TestCase
{
    private function getCalledFunctions(string $file): array
    {
        $tokens = token_get_all(file_get_contents($file));
        $calls = [];
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i]) || !in_array($tokens[$i][0], [T_STRING, T_NAME_FULLY_QUALIFIED], true)) {
                continue;
            }
            for ($j = $i + 1; $j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE; $j++);
            if ($j < $count && $tokens[$j] === '(') {
                $calls[] = strtolower(ltrim($tokens[$i][1], '\\'));
            }
        }
        return $calls;
    }

    public function testThemeCssDoesNotOpenItsOwnSession(): void
    {
        $calls = $this->getCalledFunctions(dirname(__DIR__, 2) . '/view/theme.css.php');

        foreach (['session_name', 'session_start', 'session_id', '_session_start'] as $function) {
            $this->assertNotContains($function, $calls, "view/theme.css.php must not call {$function}()");
        }
        $this->assertContains('getcurrenttheme', $calls);
    }

    /** @dataProvider sessionCases */
    public function testThemeCssPreservesTheMainSession(bool $loggedIn, bool $active, string $theme, string $expectedTheme): void
    {
        $directory = sys_get_temp_dir() . '/avideo-theme-session-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700);
        $driver = <<<'PHP'
<?php
require $argv[1] . '/objects/functionsPHP.php';
$global = ['systemRootPath' => $argv[1] . '/'];
$selectedTheme = $argv[4];
$databaseClosed = false;
function getCurrentTheme() { return $GLOBALS['selectedTheme']; }
function _mysql_close() { $GLOBALS['databaseClosed'] = true; }
session_name('themeCssTestMain');
session_id('main-theme-css-regression');
session_start();
$_SESSION = ['marker' => 'preserved'];
if ($argv[2] === '1') { $_SESSION['user'] = ['id' => 2022]; }
if ($argv[3] !== '1') { session_write_close(); }
register_shutdown_function(function () {
    echo "\nSESSION_RESULT:" . json_encode([
        'name' => session_name(), 'id' => session_id(), 'status' => session_status(),
        'marker' => $_SESSION['marker'] ?? null, 'user' => $_SESSION['user']['id'] ?? 0,
        'databaseClosed' => $GLOBALS['databaseClosed'],
    ]);
});
require $argv[1] . '/view/theme.css.php';
PHP;
        file_put_contents($directory . '/driver.php', $driver);
        try {
            $process = proc_open([
                PHP_BINARY, '-d', 'session.save_handler=files', '-d', 'session.save_path=' . $directory,
                '-d', 'session.serialize_handler=php', '-d', 'session.use_strict_mode=0',
                $directory . '/driver.php', dirname(__DIR__, 2), $loggedIn ? '1' : '0', $active ? '1' : '0', $theme,
            ], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $this->assertIsResource($process);
            $output = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $errors);
            $this->assertSame('', $errors);
            $this->assertStringContainsString(
                '@import url("css/custom/' . $expectedTheme . '.css?cache=' . filemtime(dirname(__DIR__, 2) . '/view/css/custom/' . $expectedTheme . '.css') . '");',
                $output
            );
            $this->assertStringContainsString("\nSESSION_RESULT:", $output);
            $state = json_decode(explode("\nSESSION_RESULT:", $output, 2)[1], true);
            $this->assertSame([
                'name' => 'themeCssTestMain', 'id' => 'main-theme-css-regression', 'status' => PHP_SESSION_NONE,
                'marker' => 'preserved', 'user' => $loggedIn ? 2022 : 0, 'databaseClosed' => true,
            ], $state);
            $savedSession = file_get_contents($directory . '/sess_main-theme-css-regression');
            $this->assertStringContainsString('marker|s:9:"preserved";', $savedSession);
            if ($loggedIn) {
                $this->assertStringContainsString('user|a:1:{s:2:"id";i:2022;}', $savedSession);
            }
        } finally {
            foreach (glob($directory . '/*') as $file) { unlink($file); }
            rmdir($directory);
        }
    }

    public function sessionCases(): array
    {
        return [
            'guest active' => [false, true, 'default', 'default'],
            'logged in active' => [true, true, 'cyborg', 'cyborg'],
            'guest closed' => [false, false, 'default', 'default'],
            'logged in closed' => [true, false, 'cyborg', 'cyborg'],
            'missing stylesheet' => [true, true, 'no-such-theme-10674', 'default'],
            'invalid theme name' => [true, true, '../invalid', 'default'],
        ];
    }
}
