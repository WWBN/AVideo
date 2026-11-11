<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * GHSA-2qxw-wm6h-5jr7: the RSS feed decodes HTML entities in the channel About text and video
 * descriptions, and feed/rss-style.xsl prints the result with disable-output-escaping on the site
 * origin. Text stored entity-encoded (User::setAbout() runs xss_esc()) must not come back as live
 * markup with script, event handlers or javascript: links.
 */
class FeedDescriptionHTMLTest extends TestCase
{
    protected function setUp(): void
    {
        require_once \APP_ROOT . '/objects/feedDescriptionHTML.php';
    }

    private function feed($text)
    {
        return feedDescriptionHTML($text, sys_get_temp_dir());
    }

    private function assertNoActiveContent($html)
    {
        $this->assertDoesNotMatchRegularExpression('/<[a-z][^>]*[\s\/"\']on[a-z]+\s*=/i', $html);
        $this->assertDoesNotMatchRegularExpression('/<(script|svg|iframe|object|embed|style)\b/i', $html);
        $this->assertStringNotContainsStringIgnoringCase('javascript:', $html);
    }

    public static function encodedPayloadProvider()
    {
        return [
            'About text after xss_esc()' => [htmlspecialchars('<img src=x onerror=alert(document.domain)>', ENT_QUOTES, 'UTF-8')],
            'slash-separated handler' => ['&lt;svg/onload=alert(1)&gt;'],
            'quoted handler' => ['&lt;img src=x onerror="alert(1)"&gt;'],
            'double-encoded' => ['&amp;lt;img src=x onerror=alert(1)&amp;gt;'],
            'triple-encoded' => ['&amp;amp;lt;img src=x onerror=alert(1)&amp;amp;gt;'],
            'javascript link' => ['&lt;a href="javascript:alert(1)"&gt;click&lt;/a&gt;'],
            'script block' => ['&lt;script&gt;alert(1)&lt;/script&gt;text'],
            'raw markup' => ['<p onclick=alert(1)>x</p><img src=x onerror=alert(1)><iframe src="https://example.com/"></iframe>'],
        ];
    }

    /**
     * @dataProvider encodedPayloadProvider
     */
    public function testDecodedMarkupHasNoActiveContent($text)
    {
        $this->assertNoActiveContent($this->feed($text));
    }

    public function testFormattingIsKept()
    {
        $html = $this->feed('<p class="intro">Hello <strong>world</strong> <a href="https://example.com/x" target="_blank">link</a></p><ul><li>one</li></ul>');
        $this->assertStringContainsString('<p class="intro">', $html);
        $this->assertStringContainsString('<strong>world</strong>', $html);
        $this->assertStringContainsString('href="https://example.com/x"', $html);
        $this->assertStringContainsString('<li>one</li>', $html);
    }

    public function testEntityEncodedFormattingIsDecodedOnce()
    {
        $this->assertSame('<p>text</p>', $this->feed('&lt;p&gt;text&lt;/p&gt;'));
    }

    public function testInlineCssAndScriptBlocksAreRemoved()
    {
        $html = $this->feed('<p style="color:red">a</p><script>alert(1)</script><style>p{}</style>b');
        $this->assertStringNotContainsString('style', $html);
        $this->assertStringNotContainsString('alert', $html);
        $this->assertStringContainsString('<p>a</p>', $html);
    }

    public function testPlainTextAndEmptyValues()
    {
        $this->assertSame('', $this->feed(''));
        $this->assertSame('', $this->feed(null));
        $this->assertSame('Bible Study on Sunday', $this->feed('Bible Study on Sunday'));
        $this->assertStringNotContainsString(']]>', $this->feed('a ]]> b'));
    }

    /**
     * Applies the real feed/rss-style.xsl, the way Chromium/Safari render /channel/<name>/feed.
     */
    public function testRealStylesheetOutputHasNoActiveContent()
    {
        if (!class_exists('XSLTProcessor')) {
            $this->markTestSkipped('ext-xsl is not available');
        }
        $description = $this->feed(htmlspecialchars('<img src=x onerror=alert(document.domain)><svg/onload=alert(1)>', ENT_QUOTES, 'UTF-8'));
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>t</title>'
            . '<description><![CDATA[ ' . $description . ' ]]></description>'
            . '<item><title>i</title><description><![CDATA[ ' . $this->feed('&lt;svg/onload=alert(1)&gt;<b>ok</b>') . ' ]]></description></item>'
            . '</channel></rss>';
        $xmlDoc = new \DOMDocument();
        $this->assertTrue($xmlDoc->loadXML($xml));
        $xsl = new \DOMDocument();
        $this->assertTrue($xsl->load(\APP_ROOT . '/feed/rss-style.xsl'));
        $processor = new \XSLTProcessor();
        $processor->importStylesheet($xsl);
        $html = $processor->transformToXML($xmlDoc);

        // the page itself has a <style> block, so only handlers, scripts and svg are checked here
        $this->assertDoesNotMatchRegularExpression('/<[a-z][^>]*[\s\/"\']on[a-z]+\s*=/i', $html);
        $this->assertDoesNotMatchRegularExpression('/<(script|svg)\b/i', $html);
        $this->assertStringContainsString('<b>ok</b>', $html);
    }
}
