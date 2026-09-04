<?php

namespace Tests\Unit;

use Tests\TestCase;

// isCommandLineInterface() is stubbed in tests/bootstrap.php (global namespace) so
// get_browser_name()'s early CLI short-circuit can be bypassed via $_GET['ignoreCommandLineInterface'].

if (!function_exists('isAVideoMobileApp')) {
    require_once \APP_ROOT . '/objects/functionsAVideo.php';
}

if (!function_exists('get_browser_name')) {
    require_once \APP_ROOT . '/objects/functionsBrowser.php';
}

/**
 * Regression coverage for get_browser_name()'s AVideoMobileApp/AVideoEncoder/AVideoStreamer
 * detection: those detectors match case-sensitively against the real UA, so
 * get_browser_name() must pass the original-case $user_agent to them, not its own
 * lowercased $t (this exact mix-up previously made every AVideo-internal UA fall through
 * to "Other"/"[Bot] Other").
 */
class FunctionsBrowserAVideoDetectionTest extends TestCase
{
    private $originalSalt;
    private $originalSaltV2;

    protected function setUp(): void
    {
        parent::setUp();
        $_GET['ignoreCommandLineInterface'] = 1;

        // isAVideoStreamer() always reads $global['saltV2']/$global['salt']; give both a
        // deterministic default so every test here is independent of real site config.
        global $global;
        $this->originalSalt = $global['salt'] ?? null;
        $this->originalSaltV2 = $global['saltV2'] ?? null;
        $global['salt'] = 'phpunit-default-test-salt';
        $global['saltV2'] = 'phpunit-default-test-salt-v2';
    }

    protected function tearDown(): void
    {
        global $global;
        $global['salt'] = $this->originalSalt;
        $global['saltV2'] = $this->originalSaltV2;
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testMobileAppUserAgentIsDetected()
    {
        $this->assertSame('AVideo Mobile App', \get_browser_name('AVideoMobileApp/2.0 (iPhone)'));
    }

    /**
     * @test
     */
    public function testEncoderUserAgentIsDetectedAndCapturesTrailingIdentifier()
    {
        $this->assertSame('AVideo Encoder myapp.example.com', \get_browser_name('AVideoEncoder myapp.example.com'));
    }

    /**
     * @test
     */
    public function testStreamerUserAgentIsDetectedWhenSaltMatches()
    {
        global $global;
        $ua = 'AVideoStreamer_' . md5($global['saltV2']);

        $this->assertStringStartsWith('AVideo Streamer', \get_browser_name($ua));
    }

    /**
     * @test
     */
    public function testRegularBrowserUserAgentIsNotMisdetectedAsAVideo()
    {
        $chrome = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36';
        $this->assertSame('Chrome', \get_browser_name($chrome));
    }
}
