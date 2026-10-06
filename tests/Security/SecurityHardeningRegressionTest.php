<?php

namespace Tests\Security;

use Tests\TestCase;

class SecurityHardeningRegressionTest extends TestCase
{
    /**
     * @test
     * Regression: remember-me restoration must pass the signed, time-limited
     * _user_hash_ token to encryptPasswordVerify() without unwrapping it into
     * the database password hash first. Arbitrary password hashes must remain
     * rejected by the hardened verifier.
     */
    public function testRememberMeKeepsSignedTokenIntactForValidation()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/user.php');
        $start = strpos($source, 'private static function recreateLoginFromCookie()');
        $end = strpos($source, 'public static function isLogged(', $start);
        $method = substr($source, $start, $end - $start);

        $this->assertStringContainsString(
            'new User(0, $userCookie->user, $userCookie->pass)',
            $method
        );
        $this->assertStringNotContainsString(
            '$user->setPassword($userCookie->pass, true)',
            $method
        );

        $verifier = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
        $this->assertStringContainsString(
            'User::getPasswordFromUserHashIfTheItIsValid($password)',
            $verifier
        );
        $this->assertStringNotContainsString('$password === $hash', $verifier);
    }

    /**
     * @test
     */
    public function testVideoNotFoundEscapesHtmlBeforeEmbeddingInJavascript()
    {
        $payload = '<img src=x onerror=alert(document.domain)>';
        $encoded = json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        $this->assertStringNotContainsString('<img', $encoded);
        $this->assertStringContainsString('\\u003Cimg', $encoded);
        $this->assertStringContainsString('\\u003E', $encoded);
    }

    /**
     * @test
     */
    public function testEncryptPassRequiresAuthAndDoesNotEchoPlaintext()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/encryptPass.json.php');

        // Must apply rate limiting via the shared helper.
        $this->assertStringContainsString('enforceRateLimit(', $source);

        // Must gate strictly on the admin session before doing anything useful.
        // A token derived from public streamer information would be forgeable and
        // must not provide an authentication bypass.
        $this->assertStringContainsString('User::isAdmin()', $source);
        $this->assertStringContainsString("http_response_code(401)", $source);
        $this->assertStringNotContainsString('hash_hmac(', $source);
        $this->assertStringNotContainsString("\$_REQUEST['token']", $source);

        // Must NOT reflect the plaintext password back to the caller.
        $this->assertStringNotContainsString("\$obj->password", $source);
    }

    /**
     * @test
     */
    public function testFfmpegMonitorKillsProcessesWithoutRequiringPosixExtension()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/admin/ffmpegMonitor.php');

        $this->assertStringContainsString("function_exists('posix_kill')", $source);
        $this->assertStringContainsString('posix_kill($pid, $signal)', $source);
        $this->assertStringContainsString("function_exists('exec')", $source);
        $this->assertStringContainsString('killProcess($pid)', $source);
        $this->assertStringNotContainsString('exec($command', $source);
    }

    /**
     * @test
     * CVE-class: CORS misconfiguration – reflected Origin with credentials
     * Ensures allowOrigin() never blindly echoes an arbitrary Origin header
     * back with Access-Control-Allow-Credentials:true, which would allow any
     * attacker-controlled page to make credentialed cross-origin requests and
     * read session-authenticated responses (session theft / account takeover).
     */
    public function testAllowOriginDoesNotReflectArbitraryOriginWithCredentials()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');

        // The function must derive the site's own origin for comparison.
        $this->assertStringContainsString('$siteOrigin', $source);
        $this->assertStringContainsString('$isSameOrigin', $source);

        // Credentials must only be granted after the same-origin check.
        // Verify the only code path that emits Allow-Credentials is guarded by $isSameOrigin.
        $credentialsHeaderPattern = '/header\s*\(\s*["\']Access-Control-Allow-Credentials:\s*true["\']\s*\)/';
        preg_match_all($credentialsHeaderPattern, $source, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as [$headerCall, $offset]) {
            // Look backwards from this header() call to find the nearest enclosing if-condition.
            $preceding = substr($source, 0, $offset);
            // The $allowAll early-return path uses $siteOriginForAllowAll (not $isSameOrigin) for its
            // same-origin guard and exits before $isSameOrigin is assigned. Skip the loop validation
            // for those occurrences — they are covered by the $siteOriginForAllowAll assertions below.
            if (!str_contains($preceding, '$isSameOrigin')) {
                continue;
            }
            // The enclosing branch must reference $isSameOrigin – never a raw $requestOrigin / $HTTP_ORIGIN.
            $lastIfPos = strrpos($preceding, 'if (');
            $this->assertNotFalse($lastIfPos, 'Access-Control-Allow-Credentials header must be inside a conditional.');
            $ifClause = substr($source, $lastIfPos, $offset - $lastIfPos);
            $this->assertStringContainsString('$isSameOrigin', $ifClause,
                'Access-Control-Allow-Credentials:true must only be set when $isSameOrigin is true.'
            );
        }

        // Verify the $allowAll path: credentialed third-party CORS must require
        // an explicit public-resource opt-in, so sensitive API endpoints that use
        // allowOrigin(true) stay wildcard/non-credentialed by default.
        $this->assertStringContainsString(
            '$canUsePublicCredentialedCors',
            $source,
            'allowOrigin($allowAll=true) must require an explicit public-resource opt-in before credentialed CORS.'
        );
        $this->assertStringContainsString("'publicResource' => false", $source);
        $this->assertStringContainsString("'allowCredentialedPublicResource' => false", $source);
        // The old dangerous pattern inside $allowAll — emit Allow-Credentials for any non-empty
        // $requestOrigin without a comparison — must not exist.
        $this->assertDoesNotMatchRegularExpression(
            '/if\s*\(\s*!empty\s*\(\s*\$requestOrigin\s*\)\s*\)\s*\{\s*\n[^}]*Access-Control-Allow-Credentials/',
            $source,
            'allowOrigin($allowAll=true) must not grant credentials to any non-empty origin without comparison.'
        );

        // The old dangerous pattern (reflect any origin + credentials) must not exist.
        $this->assertDoesNotMatchRegularExpression(
            '/header\s*\(\s*"Access-Control-Allow-Origin:\s*"\s*\.\s*\$HTTP_ORIGIN\s*\)/',
            $source,
            'allowOrigin() must not reflect $HTTP_ORIGIN unconditionally.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/header\s*\(\s*"Access-Control-Allow-Origin:\s*"\s*\.\s*\$requestOrigin\s*\)[^;]*\n[^}]*header\s*\(\s*"Access-Control-Allow-Credentials/',
            $source,
            'allowOrigin() must not set Access-Control-Allow-Origin + Allow-Credentials outside the same-origin guard.'
        );
    }

    /**
     * @test
     * Regression: same-site subdomains/aliases (for example vizio.example.com
     * requesting assets from example.com) should still be allowed for
     * non-credentialed CORS even when the configured site host and the actual
     * request host differ because of redirects/CDN aliases.
     */
    public function testAllowOriginChecksCurrentHostFamilyForTrustedSubdomains()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');

        $this->assertStringContainsString('isTrustedOriginFamilyForCORS', $source);
        $this->assertStringContainsString("['HTTP_HOST']", $source);
        $this->assertStringContainsString('$currentHost', $source);
        $this->assertStringContainsString('getBaseDomainForCORS', $source);
    }

    /**
     * @test
     * CVE-class: Unauthenticated CORS-exposed session ID endpoint
     * Ensures phpsessionid.json.php does not call allowOrigin(), which would
     * permit any cross-origin page to fetch the victim's session cookie via a
     * credentialed request and perform a full account takeover.
     */
    public function testPhpSessionIdEndpointDoesNotCallAllowOrigin()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/phpsessionid.json.php');

        // Strip single-line (//) and multi-line (/* */) PHP comments so that
        // explanatory comments mentioning the function name do not cause a
        // false positive.
        $codeOnly = preg_replace('/\/\/[^\n]*|\/\*.*?\*\//s', '', $source);

        $this->assertStringNotContainsString(
            'allowOrigin()',
            $codeOnly,
            'phpsessionid.json.php must not call allowOrigin(): the endpoint is ' .
            'same-origin only and CORS headers would allow cross-origin session theft.'
        );
    }

    /**
     * @test
     * Regression: this endpoint is loaded on every page by view/js/session.js.
     * If it creates a new PHP session when the browser did not send the real
     * AVideo session cookie, the Set-Cookie response can shadow the logged-in
     * cookie and make the next page look logged out.
     */
    public function testPhpSessionIdEndpointDoesNotStartOrCreateSession()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/phpsessionid.json.php');
        $codeOnly = preg_replace('/\/\/[^\n]*|\/\*.*?\*\//s', '', $source);

        $this->assertStringNotContainsString('session_start', $codeOnly);
        $this->assertStringNotContainsString('session_set_cookie_params', $codeOnly);
        $this->assertStringNotContainsString('setcookie', strtolower($codeOnly));
    }

    /**
     * @test
     */
    public function testPlainTextAlertHelpersDefaultToTextContent()
    {
        $script = file_get_contents(dirname(__DIR__, 2) . '/view/js/script.js');

        $this->assertStringContainsString('function avideoCreateAlertContent(msg, allowHTML)', $script);
        $this->assertStringContainsString('span.textContent = msg;', $script);
        $this->assertStringContainsString('function avideoConfirmHTML(msg)', $script);
        $this->assertStringContainsString('function avideoAlertOnceHTML(title, msg, type, uid)', $script);
    }

    /**
     * @test
     * Regression: a user-controlled PGP public key must not be able to break
     * out of the profile textarea and execute markup in an administrator's page.
     */
    public function testLoginControlPgpPublicKeyIsEscapedInTextarea()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/LoginControl/profileTabContent.php');
        $payload = '</textarea><img src=x onerror=alert(document.domain)>';
        $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');

        $this->assertStringContainsString(
            "htmlspecialchars((string) LoginControl::getPGPKey(\$users_id), ENT_QUOTES, 'UTF-8')",
            $source
        );
        $this->assertStringNotContainsString(
            '<?php echo LoginControl::getPGPKey($users_id); ?>',
            $source
        );

        $dom = new \DOMDocument();
        $previousUseInternalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML('<!doctype html><html><body><textarea id="publicKey">' . $escaped . '</textarea></body></html>');
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseInternalErrors);

        $textarea = $dom->getElementById('publicKey');
        $this->assertSame(0, $dom->getElementsByTagName('img')->length);
        $this->assertNotNull($textarea);
        $this->assertSame($payload, $textarea->textContent);
    }

    /**
     * @test
     * Regression: Unicode searches against older latin1 tables must not emit
     * raw LIKE comparisons, otherwise MySQL can fail at prepare time with
     * "Illegal mix of collations".
     */
    public function testVideoSearchUsesCollationSafeLikeClauses()
    {
        $bootGrid = file_get_contents(dirname(__DIR__, 2) . '/objects/bootGrid.php');
        $video = file_get_contents(dirname(__DIR__, 2) . '/objects/video.php');

        $this->assertStringContainsString('getCollationSafeLike', $bootGrid);
        $this->assertStringContainsString('utf8mb4_unicode_ci', $bootGrid);
        $this->assertStringNotContainsString('$like[] = " {$value} LIKE', $bootGrid);
        $this->assertStringNotContainsString('LIKE _utf8', $bootGrid);

        $this->assertSame(3, substr_count($video, '$sql .= self::getSearchSQLFromPost();'));
        $this->assertSame(1, substr_count($video, 'private static function getSearchSQLFromPost'));
        $this->assertStringContainsString("BootGrid::getCollationSafeLike('t.name'", $video);
        $this->assertStringNotContainsString('t.name LIKE', $video);
    }

    /**
     * @test
     * Regression: new User(0, $user, $pass) never loads the id, so live
     * preauthorization with a normal password always failed with
     * "Invalid credentials". It must verify the credentials first and then
     * load the user by the verified id, without logging the session in.
     */
    public function testLivePreauthorizationVerifiesPasswordCredentials()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/Live/Objects/StreamAuthCache.php');
        $start = strpos($source, 'public static function processPreauthorization(');
        $method = substr($source, $start);

        $this->assertStringContainsString('->getVerifiedCredentialsUserId()', $method);
        $this->assertStringContainsString('$user = new User($users_id);', $method);
        $this->assertStringNotContainsString('->login(', $method);

        $user = file_get_contents(dirname(__DIR__, 2) . '/objects/user.php');
        $this->assertStringContainsString('public function getVerifiedCredentialsUserId(', $user);
    }

    /**
     * @test
     * Regression (CVE-2026-105089, incomplete fix of GHSA-v7vx-v9q9-qhw3):
     * setTrailer1() only applies FILTER_VALIDATE_URL, which accepts raw quotes,
     * angle brackets, HTML entities and javascript: URLs. Every template that
     * renders trailer1 must require isValidURL() and HTML-escape the output.
     */
    public function testTrailerTemplatesValidateAndEscapeTrailerUrl()
    {
        $root = dirname(__DIR__, 2);
        $sinks = [
            '/plugin/YouPHPFlix2/view/row_info.php' => '$value[\'trailer1\']',
            '/plugin/YouPHPFlix2/view/BigVideoButtons.php' => '$video[\'trailer1\']',
            '/plugin/YouPHPFlix2/view/BigVideo.php' => '$video[\'trailer1\']',
            '/view/channelPlaylistItems.php' => '$serie[\'trailer1\']',
        ];
        foreach ($sinks as $file => $var) {
            $source = file_get_contents($root . $file);
            $this->assertStringContainsString("isValidURL({$var})", $source, $file);
            $this->assertStringNotContainsString("!empty({$var})", $source, $file);
            $this->assertMatchesRegularExpression('/echo htmlspecialchars\([^;]*parseVideos\(' . preg_quote($var, '/') . '/', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/echo (addQueryStringParameter\()?parseVideos\(' . preg_quote($var, '/') . '/', $source, $file);
        }
    }

    /**
     * @test
     * Regression (CVE-2026-105086): setTitle() and save() both run safeString(),
     * which decodes entities last, so a doubly-encoded title such as
     * &&&amp;amp;lt;lt;img ...&&&amp;amp;gt;gt; was stored as a real <img> tag
     * (or a raw double quote). The stored title must never contain either.
     */
    public function testVideoTitleDoubleEncodingCannotProduceTagsOrQuotes()
    {
        $root = dirname(__DIR__, 2);
        if (!function_exists('safeStringRegressionCopy')) {
            preg_match('/\nfunction safeString\(.*?\n}\n/s', str_replace("\r\n", "\n", file_get_contents($root . '/objects/functions.php')), $m);
            $this->assertNotEmpty($m);
            eval(str_replace(['function safeString(', 'return safeString('], ['function safeStringRegressionCopy(', 'return safeStringRegressionCopy('], $m[0]));
        }

        $video = file_get_contents($root . '/objects/video.php');
        $this->assertStringContainsString(
            "\$this->title = ((safeString(\$this->title)));\n            // same quote handling as setTitle()",
            str_replace("\r\n", "\n", $video)
        );

        $quotes = function ($title) {
            return str_replace(['"', "\\"], ["''", ""], $title);
        };
        $payloads = [
            '&&&amp;amp;lt;lt;img src=x onerror=alert(1)&&&amp;amp;gt;gt;',
            'x &&&amp;amp;quot;quot; onmouseover=alert(1) y',
            '&&&&amp;amp;amp;amp;lt;lt;lt;lt;svg onload=alert(1)&&&&amp;amp;amp;amp;gt;gt;gt;gt;',
        ];
        foreach ($payloads as $payload) {
            $stored = $payload;
            for ($i = 0; $i < 3; $i++) { // setTitle() and then repeated save() calls
                $stored = $quotes(safeStringRegressionCopy($stored));
                $this->assertDoesNotMatchRegularExpression('/<[a-z!\/]/i', $stored, $payload);
                $this->assertStringNotContainsString('"', $stored, $payload);
            }
        }

        $this->assertSame('Tom & Jerry', $quotes(safeStringRegressionCopy('Tom & Jerry')));
        $this->assertSame("Rock 'n' Roll", $quotes(safeStringRegressionCopy("Rock 'n' Roll")));
    }

    public function testLegacyNonJsonMutatingEndpointsRejectCrossOriginRequests()
    {
        // autoCSRFGuard() only runs for *.json.php, so these must guard themselves before mutating
        $root = dirname(__DIR__, 2);
        foreach (['playlistStatus', 'playlistRemoveVideo', 'videoSuggest', 'videoPinOnChannel'] as $name) {
            $source = file_get_contents("{$root}/objects/{$name}.php");
            $guard = strpos($source, "forbidIfIsUntrustedRequest('{$name}');");
            $this->assertNotFalse($guard, "{$name}.php must call forbidIfIsUntrustedRequest()");
            $this->assertMatchesRegularExpression('/->(save|addVideo)\(/', $source, $name);
            preg_match('/->(save|addVideo)\(/', $source, $m, PREG_OFFSET_CAPTURE);
            $this->assertLessThan($m[0][1], $guard, "{$name}.php must check the request origin before mutating");
        }
    }
}
