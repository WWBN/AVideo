<?php

namespace Tests\Security;

use Tests\TestCase;

class SecurityHardeningRegressionTest extends TestCase
{
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

        // Must gate on admin session or HMAC token before doing anything useful.
        $this->assertStringContainsString('User::isAdmin()', $source);
        $this->assertStringContainsString('hash_equals(', $source);
        $this->assertStringContainsString("http_response_code(401)", $source);

        // Must NOT reflect the plaintext password back to the caller.
        $this->assertStringNotContainsString("\$obj->password", $source);
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
            // The enclosing branch must reference $isSameOrigin – never a raw $requestOrigin / $HTTP_ORIGIN.
            $lastIfPos = strrpos($preceding, 'if (');
            $this->assertNotFalse($lastIfPos, 'Access-Control-Allow-Credentials header must be inside a conditional.');
            $ifClause = substr($source, $lastIfPos, $offset - $lastIfPos);
            $this->assertStringContainsString('$isSameOrigin', $ifClause,
                'Access-Control-Allow-Credentials:true must only be set when $isSameOrigin is true.'
            );
        }

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
     */
    public function testPlainTextAlertHelpersDefaultToTextContent()
    {
        $script = file_get_contents(dirname(__DIR__, 2) . '/view/js/script.js');

        $this->assertStringContainsString('function avideoCreateAlertContent(msg, allowHTML)', $script);
        $this->assertStringContainsString('span.textContent = msg;', $script);
        $this->assertStringContainsString('function avideoConfirmHTML(msg)', $script);
        $this->assertStringContainsString('function avideoAlertOnceHTML(title, msg, type, uid)', $script);
    }
}
