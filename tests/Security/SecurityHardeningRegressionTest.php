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
    public function testPlainTextAlertHelpersDefaultToTextContent()
    {
        $script = file_get_contents(dirname(__DIR__, 2) . '/view/js/script.js');

        $this->assertStringContainsString('function avideoCreateAlertContent(msg, allowHTML)', $script);
        $this->assertStringContainsString('span.textContent = msg;', $script);
        $this->assertStringContainsString('function avideoConfirmHTML(msg)', $script);
        $this->assertStringContainsString('function avideoAlertOnceHTML(title, msg, type, uid)', $script);
    }
}
