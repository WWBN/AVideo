<?php

namespace Tests\Security\EncoderCallbackCsrfStubs {

    use Tests\Security\EncoderCallbackCsrfSessionTest as T;

    class AVideoLog
    {
        public static $SECURITY = 'SECURITY';
    }

    // Only password verification/session persistence are simulated; loginFromRequest() is real.
    class UserFixture
    {
        const USER_LOGGED = 0;
        public static $requestUsersId = 0;
        protected static $loginCheckedPassword = false;

        public static function getRequestCredentialsUsersId()
        {
            return self::$requestUsersId;
        }

        public function login($noPass = false, $encodedPass = false)
        {
            self::$loginCheckedPassword = true;
            $_SESSION['user'] = ['id' => 7];
            return self::USER_LOGGED;
        }
    }

    function inputToRequest()
    {
    }

    function _error_log($message, $level = null)
    {
    }

    function isCommandLineInterface()
    {
        return false;
    }

    function getRemoteAddrFromServerArray($server)
    {
        return '203.0.113.10';
    }

    function getRealIpAddr()
    {
        return '203.0.113.10';
    }

    function isLoopbackIP($ip)
    {
        return false;
    }

    function requestComesFromSameDomainAsMyAVideo()
    {
        return parse_url((string) getRefferOrOrigin(), PHP_URL_HOST) === 'site.example';
    }

    // Same body as the real one, but records the decision instead of calling forbiddenPage().
    function forbidIfIsUntrustedRequest($logMsg = '', $approveAVideoUserAgent = true)
    {
        T::$originChecks[] = $logMsg;
        if (isUntrustedRequest($logMsg, $approveAVideoUserAgent)) {
            T::$blocked[] = $logMsg;
        }
    }
}

namespace Tests\Security {

    use PHPUnit\Framework\TestCase;

    /**
     * GHSA-whh2-8p2q-5598: the Encoder callbacks skipped autoCSRFGuard() unconditionally, and
     * useVideoHashOrLogin() falls back to the caller's session, so a cross-site page could make a
     * logged-in user's browser replace, publish or upload videos. The Web Encoder calls them with cURL
     * and no cookies; a request carrying this site's session cookie now gets the normal origin check,
     * which still accepts AVideo clients (the Local Encoder keeps cookies and sends "AVideoEncoder").
     *
     * Runs the real autoCSRFGuard(), isUntrustedRequest(), isAVideoUserAgent(), getRefferOrOrigin()
     * and loginFromRequest(). Network helpers, credential/session persistence and forbiddenPage()
     * are simulated in their own namespace; no live session or credentials are used.
     */
    class EncoderCallbackCsrfSessionTest extends TestCase
    {
        const STUBS = 'Tests\\Security\\EncoderCallbackCsrfStubs';

        const ENCODER_CALLBACKS = [
            'objects/aVideoEncoder.json.php',
            'objects/aVideoEncoderLog.json.php',
            'objects/aVideoEncoderNotifyIsDone.json.php',
            'objects/aVideoEncoderReceiveImage.json.php',
            'objects/aVideoQueueEncoder.json.php',
        ];

        public static $originChecks = [];
        public static $blocked = [];

        private $saved = [];

        public static function setUpBeforeClass(): void
        {
            if (function_exists(self::STUBS . '\\autoCSRFGuard')) {
                return;
            }
            $files = [
                '/objects/functionsSecurity.php' => ['getCsrfBypassRelativePath', 'autoCSRFGuard', 'isUntrustedRequest'],
                '/objects/functionsAVideo.php' => ['isAVideoUserAgent'],
                '/objects/functions.php' => ['getRefferOrOrigin'],
            ];
            foreach ($files as $file => $names) {
                $source = file_get_contents(dirname(__DIR__, 2) . $file);
                foreach ($names as $name) {
                    $start = strpos($source, "\nfunction {$name}(");
                    if ($start === false) {
                        throw new \RuntimeException("Function {$name} not found");
                    }
                    $end = strpos($source, "\nfunction ", $start + 1);
                    eval('namespace ' . self::STUBS . '; ' . substr($source, $start, $end - $start));
                }
            }
            $source = file_get_contents(dirname(__DIR__, 2) . '/objects/user.php');
            $start = strpos($source, '    public static function loginFromRequest()');
            $end = strpos($source, '    public static function loginFromRequestToGet()', $start);
            if ($start === false || $end === false) {
                throw new \RuntimeException('loginFromRequest not found');
            }
            eval('namespace ' . self::STUBS . '; class User extends UserFixture {' . substr($source, $start, $end - $start) . '}');
        }

        protected function setUp(): void
        {
            foreach (['global', 'AVideoMobileAPP_UA', 'AVideoEncoder_UA', 'AVideoEncoderNetwork_UA', 'AVideoStreamer_UA', 'AVideoStorage_UA', 'AVideoRestreamer_UA', 'lastMatchedAVideoUserAgent'] as $key) {
                $this->saved['g'][$key] = [array_key_exists($key, $GLOBALS), $GLOBALS[$key] ?? null];
            }
            $this->saved['cookie'] = $_COOKIE;
            $this->saved['server'] = $_SERVER;
            $this->saved['request'] = $_REQUEST;
            $this->saved['session'] = [isset($_SESSION), $_SESSION ?? null];
            $GLOBALS['global'] = ['systemRootPath' => '/var/www/html/AVideo/'];
            $GLOBALS['AVideoMobileAPP_UA'] = 'AVideoMobileApp';
            $GLOBALS['AVideoEncoder_UA'] = 'AVideoEncoder';
            $_COOKIE = [];
            unset($_SERVER['HTTP_ORIGIN'], $_SERVER['HTTP_REFERER'], $_SERVER['HTTP_USER_AGENT']);
            $_SERVER['SCRIPT_NAME'] = '';
        }

        protected function tearDown(): void
        {
            foreach ($this->saved['g'] as $key => [$existed, $value]) {
                if ($existed) {
                    $GLOBALS[$key] = $value;
                } else {
                    unset($GLOBALS[$key]);
                }
            }
            $_COOKIE = $this->saved['cookie'];
            $_SERVER = $this->saved['server'];
            $_REQUEST = $this->saved['request'];
            if ($this->saved['session'][0]) {
                $_SESSION = $this->saved['session'][1];
            } else {
                unset($_SESSION);
            }
        }

        /** @return array [origin check ran, request refused] */
        private function guard($relativePath)
        {
            self::$originChecks = self::$blocked = [];
            $guard = self::STUBS . '\\autoCSRFGuard';
            $guard(basename($relativePath), '/var/www/html/AVideo/' . $relativePath);
            return [count(self::$originChecks) > 0, count(self::$blocked) > 0];
        }

        public function testWebEncoderCallWithoutCookiesIsAccepted()
        {
            // cURL from the Web Encoder: no cookie, no Origin/Referer.
            $_SERVER['HTTP_USER_AGENT'] = 'AVideoEncoder encoder.example';
            foreach (self::ENCODER_CALLBACKS as $path) {
                $this->assertSame([false, false], $this->guard($path), $path);
            }
            // Clients without an AVideo User-Agent keep working as before.
            $_SERVER['HTTP_USER_AGENT'] = 'curl/8.0';
            $this->assertSame([false, false], $this->guard('objects/aVideoEncoderNotifyIsDone.json.php'));
        }

        public function testCrossSiteBrowserRequestWithTheSessionCookieIsRefused()
        {
            $_COOKIE[session_name()] = 'victim-session';
            $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
            $_SERVER['HTTP_ORIGIN'] = 'https://attacker.example';
            foreach (self::ENCODER_CALLBACKS as $path) {
                $this->assertSame([true, true], $this->guard($path), $path);
            }
            // A GET with the Referer suppressed has no Origin either.
            unset($_SERVER['HTTP_ORIGIN']);
            $this->assertSame([true, true], $this->guard('objects/aVideoEncoder.json.php'));
        }

        public function testSameSiteRequestAndAVideoClientsWithCookiesAreAccepted()
        {
            $_COOKIE[session_name()] = 'session';
            $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
            $_SERVER['HTTP_REFERER'] = 'https://site.example/mvideos';
            $this->assertSame([true, false], $this->guard('objects/aVideoEncoderReceiveImage.json.php'));
            // Match getRefferOrOrigin(): Referer takes precedence when both headers are present.
            $_SERVER['HTTP_ORIGIN'] = 'https://attacker.example';
            $this->assertSame([true, false], $this->guard('objects/aVideoEncoderReceiveImage.json.php'));

            // Local Encoder (httpx keeps the session cookie) and the Mobile App.
            unset($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_ORIGIN']);
            foreach (['AVideoEncoder local-encoder/1.0', 'AVideoMobileApp/3'] as $ua) {
                $_SERVER['HTTP_USER_AGENT'] = $ua;
                foreach (self::ENCODER_CALLBACKS as $path) {
                    $this->assertSame([true, false], $this->guard($path), "{$ua} {$path}");
                }
            }
        }

        public function testEmptySessionCookieOrOtherCookiesKeepTheEncoderPath()
        {
            $_COOKIE['someOtherCookie'] = '1';
            $_COOKIE[session_name()] = '';
            $_SERVER['HTTP_ORIGIN'] = 'https://attacker.example';
            $this->assertSame([false, false], $this->guard('objects/aVideoEncoderNotifyIsDone.json.php'));
        }

        public function testSameBasenameElsewhereIsNotExempted()
        {
            // Matched on the path relative to the site root, like the built-in bypass list.
            $_SERVER['HTTP_ORIGIN'] = 'https://attacker.example';
            $this->assertSame([true, true], $this->guard('plugin/Other/aVideoEncoder.json.php'));
        }

        public function testOtherBypassEntriesAreUnchanged()
        {
            $_COOKIE[session_name()] = 'victim-session';
            $_SERVER['HTTP_ORIGIN'] = 'https://attacker.example';
            $this->assertSame([false, false], $this->guard('plugin/CDN/status.json.php'));
            $this->assertSame([false, false], $this->guard('objects/login.json.php'));
            $this->assertSame([true, true], $this->guard('objects/videoAddNew.json.php'));
        }

        public function testSessionLoginBypassRequiresCredentialsForTheSameAccount()
        {
            $_COOKIE[session_name()] = 'session';
            $_SESSION = ['user' => ['id' => 7]];
            $_SERVER['HTTP_ORIGIN'] = 'https://attacker.example';
            $user = self::STUBS . '\\User';
            foreach ([0, 8, 7] as $verifiedUsersId) {
                unset($GLOBALS['global']['bypassSameDomainCheck'], $GLOBALS['global']['loggedInFromRequestCredentials']);
                $_REQUEST = ['user' => 'fixture', 'pass' => 'fixture'];
                $user::$requestUsersId = $verifiedUsersId;
                $this->assertSame($user::USER_LOGGED, $user::loginFromRequest());
                foreach (self::ENCODER_CALLBACKS as $path) {
                    $this->assertSame($verifiedUsersId === 7 ? [false, false] : [true, true], $this->guard($path), $path);
                }
            }
        }

        public function testFreshVerifiedLoginKeepsTheHeadlessClientExemption()
        {
            // login() regenerates the session cookie before autoCSRFGuard() runs.
            // Reuse the existing cookie marker, without starting or constructing a real session.
            $_COOKIE[session_name()] = 'session';
            $user = self::STUBS . '\\User';
            foreach ([7, 0] as $verifiedUsersId) {
                unset($GLOBALS['global']['bypassSameDomainCheck'], $GLOBALS['global']['loggedInFromRequestCredentials']);
                $_SESSION = [];
                $_REQUEST = ['user' => 'fixture', 'pass' => 'fixture'];
                $user::$requestUsersId = $verifiedUsersId;
                $this->assertSame($user::USER_LOGGED, $user::loginFromRequest());
                // A password check made by nested remember-me login is not this request's credentials.
                foreach (self::ENCODER_CALLBACKS as $path) {
                    $this->assertSame($verifiedUsersId === 7 ? [false, false] : [true, true], $this->guard($path), $path);
                }
            }
        }
    }
}
