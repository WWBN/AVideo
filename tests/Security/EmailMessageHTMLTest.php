<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * "Notify Subscribers" sends the TinyMCE message of any uploader inside the site-branded e-mail.
 * The formatting must reach the subscribers as HTML (it was escaped and shown as tags), while
 * scripts, forms, iframes and other markup are still removed.
 */
class EmailMessageHTMLTest extends TestCase
{
    protected function setUp(): void
    {
        require_once \APP_ROOT . '/objects/emailMessageHTML.php';
    }

    private function purify($html)
    {
        return purifyEmailMessageHTML($html, sys_get_temp_dir());
    }

    public function testTinyMCEFormattingIsKeptAsHTML()
    {
        $html = $this->purify('<p>This is a <strong>test</strong> of the <em>email</em> feature</p>'
            . '<p>Feedback to <a href="mailto:contact@example.com">contact@example.com</a></p>'
            . '<p><img src="https://example.com/videos/tmpImages/a.jpg?cache=1" alt="" width="402" height="270"></p>'
            . '<ul><li>one</li></ul><p style="text-align: center;"><span style="color: #e03e2d;">red</span></p>');

        $this->assertStringContainsString('<strong>test</strong>', $html);
        $this->assertStringContainsString('<em>email</em>', $html);
        $this->assertStringContainsString('<a href="mailto:contact@example.com">', $html);
        $this->assertStringContainsString('<img src="https://example.com/videos/tmpImages/a.jpg?cache=1"', $html);
        $this->assertStringContainsString('<li>one</li>', $html);
        $this->assertStringContainsString('text-align:center;', $html);
        $this->assertStringContainsString('color:#e03e2d;', $html);
        $this->assertStringNotContainsString('&lt;p&gt;', $html);
    }

    public function testMediaBecomesALink()
    {
        $html = $this->purify('<p>test media</p><p><video controls="controls" width="300" height="150"><source src="https://example.com/channel/Me/video/25/my-video"></video></p>'
            . '<p><iframe src="https://www.youtube.com/embed/abc" width="560" height="314"></iframe></p>');

        $this->assertStringContainsString('<a href="https://example.com/channel/Me/video/25/my-video"', $html);
        $this->assertStringContainsString('<a href="https://www.youtube.com/embed/abc"', $html);
        $this->assertStringNotContainsString('<video', $html);
        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringNotContainsString('<source', $html);
    }

    public function testUnsafeMarkupIsRemoved()
    {
        $html = $this->purify('<p onclick="x()">hi</p><script>alert(1)</script><style>p{display:none}</style>'
            . '<form action="https://evil.example/"><input name="password"></form>'
            . '<a href="javascript:alert(1)">link</a><img src="data:image/png;base64,AAAA">'
            . '<div style="position:absolute;top:0">over</div><iframe src="javascript:alert(1)"></iframe>');

        foreach (['onclick', '<script', 'alert(1)', '<style', '<form', '<input', 'javascript:', 'data:image', 'position', '<iframe'] as $needle) {
            $this->assertStringNotContainsStringIgnoringCase($needle, $html);
        }
        $this->assertStringContainsString('hi', $html);
        $this->assertStringContainsString('over', $html);
    }

    public function testPlainTextKeepsLineBreaksAndIsEscaped()
    {
        $this->assertSame("Hello &amp; welcome<br />\nsee you", $this->purify("Hello & welcome\nsee you"));
        $this->assertSame('', $this->purify('   '));
    }
}
