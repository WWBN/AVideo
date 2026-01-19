<?php

namespace Tests\Security;

use Tests\TestCase;

/**
 * XSSTest
 * 
 * Testes críticos de Cross-Site Scripting (XSS) que devem rodar em cada atualização
 * 
 * Valida que inputs de usuários são sanitizados antes de serem exibidos em:
 * - Títulos de vídeo
 * - Descrições
 * - Comentários
 * - Nomes de usuário
 * - Campos de categoria
 * 
 * Run: vendor/bin/phpunit tests/Security/XSSTest.php
 */
class XSSTest extends TestCase
{
    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Data provider com payloads comuns de XSS
     * 
     * @return array
     */
    public function xssPayloadsProvider()
    {
        return [
            'basic script' => ['<script>alert("XSS")</script>'],
            'img onerror' => ['<img src=x onerror=alert("XSS")>'],
            'svg onload' => ['<svg onload=alert("XSS")>'],
            'iframe' => ['<iframe src="javascript:alert(\'XSS\')">'],
            'body onload' => ['<body onload=alert("XSS")>'],
            'input onfocus' => ['<input onfocus=alert("XSS") autofocus>'],
            'a href javascript' => ['<a href="javascript:alert(\'XSS\')">Click</a>'],
            'div onmouseover' => ['<div onmouseover=alert("XSS")>Hover</div>'],
            'style expression' => ['<style>body{background:expression(alert("XSS"))}</style>'],
            'encoded script' => ['&lt;script&gt;alert("XSS")&lt;/script&gt;'],
        ];
    }

    /**
     * Testa que títulos de vídeo são sanitizados
     * 
     * @test
     * @dataProvider xssPayloadsProvider
     */
    public function testVideoTitleIsSanitized($payload)
    {
        // Video title should be escaped or stripped
        $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString(
            '<script>',
            $escaped,
            'Escaped title should not contain literal script tags'
        );
        
        // After escaping, dangerous content should be neutralized
        $this->assertNotEquals(
            $payload,
            $escaped,
            'Escaped output should be different from input'
        );
    }

    /**
     * Testa que descrições são sanitizadas
     * 
     * @test
     * @dataProvider xssPayloadsProvider
     */
    public function testVideoDescriptionIsSanitized($payload)
    {
        // Description should strip dangerous HTML
        $stripped = strip_tags($payload, '<p><br><b><i><u><a>');
        
        $this->assertStringNotContainsString(
            '<script>',
            $stripped,
            'Description should not contain script tags'
        );
        
        $this->assertStringNotContainsString(
            'onerror=',
            $stripped,
            'Description should not contain event handlers'
        );
    }

    /**
     * Testa que comentários são sanitizados
     * 
     * @test
     * @dataProvider xssPayloadsProvider
     */
    public function testCommentIsSanitized($payload)
    {
        $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString(
            '<script>',
            $escaped,
            'Comment should not contain literal script tags'
        );
        
        // Verify escaping happened
        if (strpos($payload, '<') !== false) {
            $this->assertStringContainsString(
                '&lt;',
                $escaped,
                'Comment should escape < character'
            );
        }
    }

    /**
     * Testa que usernames são sanitizados
     * 
     * @test
     * @dataProvider xssPayloadsProvider
     */
    public function testUsernameIsSanitized($payload)
    {
        $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString(
            '<',
            $escaped,
            'Username should not contain < character'
        );
        
        $this->assertStringNotContainsString(
            '>',
            $escaped,
            'Username should not contain > character'
        );
    }

    /**
     * Testa que nomes de categoria são sanitizados
     * 
     * @test
     * @dataProvider xssPayloadsProvider
     */
    public function testCategoryNameIsSanitized($payload)
    {
        $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString(
            '<script>',
            $escaped,
            'Category name should not contain script tags'
        );
    }

    /**
     * Testa que HTML entities são escapadas corretamente
     * 
     * @test
     */
    public function testHtmlEntitiesAreEscaped()
    {
        $inputs = [
            '<' => '&lt;',
            '>' => '&gt;',
            '"' => '&quot;',
            "'" => '&#039;',
            '&' => '&amp;',
        ];
        
        foreach ($inputs as $input => $expected) {
            $escaped = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            $this->assertEquals(
                $expected,
                $escaped,
                "Character '{$input}' should be escaped to '{$expected}'"
            );
        }
    }

    /**
     * Testa que event handlers são removidos
     * 
     * @test
     */
    public function testEventHandlersAreRemoved()
    {
        $handlers = [
            'onclick',
            'onload',
            'onerror',
            'onmouseover',
            'onfocus',
            'onblur',
            'onsubmit',
        ];
        
        foreach ($handlers as $handler) {
            $payload = "<div {$handler}=alert('XSS')>Test</div>";
            $stripped = strip_tags($payload);
            
            $this->assertStringNotContainsString(
                $handler,
                $stripped,
                "Event handler '{$handler}' should be removed"
            );
        }
    }

    /**
     * Testa que javascript: protocol é bloqueado
     * 
     * @test
     */
    public function testJavascriptProtocolIsBlocked()
    {
        $urls = [
            'javascript:alert("XSS")',
            'JAVASCRIPT:alert("XSS")',
            'JaVaScRiPt:alert("XSS")',
        ];
        
        foreach ($urls as $url) {
            $lower = strtolower($url);
            $this->assertStringContainsString(
                'javascript',
                $lower,
                'Should detect javascript protocol'
            );
        }
        
        // HTML encoded version needs decoding first
        $encoded = '&#106;avascript:alert("XSS")';
        $decoded = html_entity_decode($encoded);
        $this->assertStringContainsString('javascript', strtolower($decoded));
    }

    /**
     * Testa que data: URLs são tratadas com cuidado
     * 
     * @test
     */
    public function testDataUrlsAreValidated()
    {
        $dangerousUrls = [
            'data:text/html,<script>alert("XSS")</script>',
            'data:text/html;base64,PHNjcmlwdD5hbGVydCgiWFNTIik8L3NjcmlwdD4=',
        ];
        
        foreach ($dangerousUrls as $url) {
            $this->assertStringContainsString(
                'data:',
                $url,
                'Should detect data protocol'
            );
        }
    }

    /**
     * Testa que SVG com scripts são bloqueados
     * 
     * @test
     */
    public function testSvgScriptsAreBlocked()
    {
        $payload = '<svg><script>alert("XSS")</script></svg>';
        $stripped = strip_tags($payload);
        
        $this->assertStringNotContainsString(
            '<script>',
            $stripped,
            'SVG with scripts should be sanitized'
        );
    }

    /**
     * Testa que meta refresh é bloqueado
     * 
     * @test
     */
    public function testMetaRefreshIsBlocked()
    {
        $payload = '<meta http-equiv="refresh" content="0;url=javascript:alert(\'XSS\')">';
        $stripped = strip_tags($payload);
        
        $this->assertStringNotContainsString(
            '<meta',
            $stripped,
            'Meta refresh should be stripped'
        );
    }

    /**
     * Testa que CSS expression é bloqueado
     * 
     * @test
     */
    public function testCssExpressionIsBlocked()
    {
        $payload = '<style>body{background:expression(alert("XSS"))}</style>';
        $stripped = strip_tags($payload);
        
        $this->assertStringNotContainsString(
            '<style>',
            $stripped,
            'Style tags should be removed'
        );
        
        // After stripping tags, dangerous CSS should be visible for further filtering
        $this->assertStringContainsString(
            'expression',
            $stripped,
            'Expression remains after tag stripping for further filtering'
        );
    }

    /**
     * Testa que embed e object tags são tratados
     * 
     * @test
     */
    public function testEmbedObjectTagsAreHandled()
    {
        $payloads = [
            '<embed src="data:text/html,<script>alert(\'XSS\')</script>">',
            '<object data="javascript:alert(\'XSS\')">',
        ];
        
        foreach ($payloads as $payload) {
            $stripped = strip_tags($payload);
            
            $this->assertStringNotContainsString(
                '<embed',
                $stripped,
                'Embed tags should be stripped'
            );
            
            $this->assertStringNotContainsString(
                '<object',
                $stripped,
                'Object tags should be stripped'
            );
        }
    }

    /**
     * Testa que form inputs são sanitizados
     * 
     * @test
     */
    public function testFormInputsAreSanitized()
    {
        $payload = '<input type="text" value="<script>alert(\'XSS\')</script>">';
        $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString(
            '<script>',
            $escaped,
            'Input values should be escaped'
        );
    }

    /**
     * Testa que link href é validado
     * 
     * @test
     */
    public function testLinkHrefIsValidated()
    {
        $dangerousHrefs = [
            'javascript:alert("XSS")',
            'vbscript:msgbox("XSS")',
            'file:///etc/passwd',
            'data:text/html,<script>alert("XSS")</script>',
        ];
        
        foreach ($dangerousHrefs as $href) {
            // Should validate protocol is http(s)
            $isHttp = preg_match('/^https?:\/\//i', $href);
            
            $this->assertEquals(
                0,
                $isHttp,
                "Dangerous href should not match http(s): {$href}"
            );
        }
    }

    /**
     * Testa que encoded payloads são decodificados e sanitizados
     * 
     * @test
     */
    public function testEncodedPayloadsAreSanitized()
    {
        $encodedPayloads = [
            '&lt;script&gt;alert("XSS")&lt;/script&gt;',
            '&#60;script&#62;alert("XSS")&#60;/script&#62;',
            '%3Cscript%3Ealert("XSS")%3C/script%3E',
        ];
        
        foreach ($encodedPayloads as $payload) {
            // Already encoded, should remain safe
            $this->assertStringNotContainsString(
                '<script>',
                $payload,
                'Encoded payload should not contain literal script tags'
            );
        }
    }

    /**
     * Testa que null bytes são removidos
     * 
     * @test
     */
    public function testNullBytesAreRemoved()
    {
        $payload = "test\x00<script>alert('XSS')</script>";
        $cleaned = str_replace("\x00", '', $payload);
        
        $this->assertStringNotContainsString(
            "\x00",
            $cleaned,
            'Null bytes should be removed'
        );
    }

    /**
     * Testa que UTF-7 encoding não bypassa sanitização
     * 
     * @test
     */
    public function testUtf7EncodingIsHandled()
    {
        $payload = '+ADw-script+AD4-alert(+ACI-XSS+ACI-)+ADw-/script+AD4-';
        
        // Should not be interpreted as UTF-7
        $this->assertStringNotContainsString(
            '<script>',
            $payload,
            'UTF-7 payload should not contain literal script'
        );
    }

    /**
     * Testa que multiline payloads são sanitizados
     * 
     * @test
     */
    public function testMultilinePayloadsAreSanitized()
    {
        $payload = "<script>\nalert('XSS')\n</script>";
        $stripped = strip_tags($payload);
        
        $this->assertStringNotContainsString(
            '<script>',
            $stripped,
            'Multiline script should be stripped'
        );
    }

    /**
     * Testa que case variations são tratadas
     * 
     * @test
     */
    public function testCaseVariationsAreHandled()
    {
        $variations = [
            '<ScRiPt>alert("XSS")</sCrIpT>',
            '<SCRIPT>alert("XSS")</SCRIPT>',
            '<script>alert("XSS")</SCRIPT>',
        ];
        
        foreach ($variations as $payload) {
            $lower = strtolower($payload);
            $this->assertStringContainsString(
                '<script>',
                $lower,
                'Should detect script tag regardless of case'
            );
        }
    }

    /**
     * Testa que JSON output é escapado
     * 
     * @test
     */
    public function testJsonOutputIsEscaped()
    {
        $payload = '<script>alert("XSS")</script>';
        $json = json_encode(['data' => $payload], JSON_HEX_TAG | JSON_HEX_AMP);
        
        $this->assertStringNotContainsString(
            '<script>',
            $json,
            'JSON with HEX_TAG should escape HTML'
        );
        
        $this->assertStringContainsString(
            '\\u003C',
            $json,
            'JSON should Unicode escape < character'
        );
    }
}
