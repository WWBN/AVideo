<?php

namespace Tests\Security\MarkdownFixtures;

// Execute the production ParsedownSafeWithLinks + markDownToHTML() without loading the configuration.
$source = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsSecurity.php');
$start = strpos($source, 'class ParsedownSafeWithLinks');
$end = strpos($source, 'function getAToken');
eval('namespace ' . __NAMESPACE__ . '; use Parsedown; ' . substr($source, $start, $end - $start));

$source = file_get_contents(dirname(__DIR__, 2) . '/objects/comment.php');
$start = strpos($source, '    static function fixCommentText(');
eval('namespace ' . __NAMESPACE__ . '; class CommentFixture {' . substr($source, $start));
$source = file_get_contents(dirname(__DIR__, 2) . '/locale/function.php');
$start = strpos($source, 'function textToLink(');
$end = strpos($source, 'function br2nl(', $start);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($source, $start, $end - $start));

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Tests\Security\MarkdownFixtures\CommentFixture;
use function Tests\Security\MarkdownFixtures\markDownToHTML;
use function Tests\Security\MarkdownFixtures\linkifyTimestamps;
use function Tests\Security\MarkdownFixtures\textToLink;

/**
 * Comment Markdown must never render script-capable HTML (raw <img>/<a> blocks, event attributes,
 * attribute break-out through the bare-URL linkifier).
 */
class CommentMarkdownXSSTest extends TestCase
{
    public function maliciousCommentsProvider()
    {
        return [
            'block img unquoted onerror' => ['<img src=x onerror=alert(1)>'],
            'block img quoted onerror' => ['<img src="x" onerror="alert(1)">'],
            'block img then raw script' => ["<img src=\"https://a/b.png\">\n<script>alert(1)</script>"],
            'inline img onerror' => ['hi <img src="x" onerror="alert(1)">'],
            'raw a onmouseover' => ['<a href="javascript:alert(1)" onmouseover="alert(2)">x</a>'],
            'img style linkify breakout' => ['<img src="https://a/b.png" style="Xhttps://a/x/onerror=alert(1)//">'],
            'img class linkify breakout' => ['<img src="https://a/b.png" class="Xhttps://a/x/onerror=alert(1)//">'],
            'markdown img alt linkify breakout' => ['![Xhttps://a/x/onerror=alert(1)//](https://a/b.png)'],
            'markdown link title linkify breakout' => ['[l](https://a/b "Xhttps://a/x/onmouseover=alert(1)//")'],
            'raw div onclick' => ['<div onclick=alert(1)>x</div>'],
            'multiline link title breakout' => ["[x](https://a \"hello\nXhttps://a/x/onmouseover=alert(1)//\")"],
            'multiline image alt breakout' => ["![hello\nXhttps://a/x/onerror=alert(1)//](https://a/b.png)"],
            'multiline image title breakout' => ["![x](https://a/b.png \"hello\nXhttps://a/x/onerror=alert(1)//\")"],
        ];
    }

    /**
     * @dataProvider maliciousCommentsProvider
     */
    public function testRenderedCommentHasNoScriptCapableMarkup($comment)
    {
        $html = markDownToHTML($comment);
        // Parse it like a browser does, so an attribute break-out shows up as a real attribute
        $dom = $this->parse($html);
        $this->assertSame(0, $dom->getElementsByTagName('script')->length, $html);
        foreach ($dom->getElementsByTagName('*') as $element) {
            foreach ($element->attributes as $attribute) {
                $this->assertStringStartsNotWith('on', strtolower($attribute->name), $html);
            }
        }
        $this->assertDoesNotMatchRegularExpression('/^\s*(javascript|vbscript|data):/i', $this->urls($html), $html);
        // libxml does not split attributes on "/" like browsers do, so also reject a tag injected
        // inside a quoted attribute value (alt="<a href="...), which breaks the attribute out
        $this->assertDoesNotMatchRegularExpression('/<[a-z][^>]*="[^"]*</i', $html, $html);
    }

    public function testMarkdownLinksWithDisallowedSchemesAreStripped()
    {
        foreach (['[x](javascript:alert(1))', '![x](javascript:alert(1))', '<javascript://alert(1)>', '<a href="vbscript:x">l</a>', '<img src="data:image/svg+xml,x">', '<img src=" javascript:alert(1)">', '<img src=" vbscript:x">', '<img src=" data:image/svg+xml,x">', "<img src=\"java\nscript:alert(1)\">"] as $comment) {
            $this->assertDoesNotMatchRegularExpression('/(javascript|vbscript|data):/i', $this->urls(markDownToHTML($comment)), $comment);
        }
    }

    public function testMultilineAttributesRemainIntact()
    {
        $html = markDownToHTML("![first\nsecond](https://a/b.png \"line one\nline two\")");
        $image = $this->parse($html)->getElementsByTagName('img')->item(0);
        $this->assertSame("first\nsecond", $image->getAttribute('alt'));
        $this->assertSame("line one\nline two", $image->getAttribute('title'));
        $this->assertStringContainsString('img-responsive', $image->getAttribute('class'));
        $this->assertStringNotContainsString('<br', $html);
        $this->assertStringContainsString("first<br />\nsecond", markDownToHTML("first\nsecond"));
    }

    public function testTimestampsOnlyLinkifyTextAndDoNotNestLinks()
    {
        $html = markDownToHTML('![00:00/onerror=alert(1)//](https://a/00:00.png) [link](https://a "00:00") and 01:23');
        $result = linkifyTimestamps($html);
        $image = $this->parse($result)->getElementsByTagName('img')->item(0);
        $this->assertSame('00:00/onerror=alert(1)//', $image->getAttribute('alt'));
        $this->assertSame('https://a/00:00.png', $image->getAttribute('src'));
        $this->assertFalse($image->hasAttribute('onclick'));
        $this->assertStringContainsString('title="00:00"', $result);
        $this->assertStringContainsString('player.currentTime(83)', $result);
        $this->assertSame($result, linkifyTimestamps($result));
        $linked = markDownToHTML('[01:23](https://a)');
        $this->assertSame($linked, linkifyTimestamps($linked));
    }

    public function testCommentFormattingPreservesSanitizedAttributes()
    {
        $html = markDownToHTML('<img src="da\ta:image/svg+xml,x">');
        $result = linkifyTimestamps(CommentFixture::fixCommentText(textToLink($html)));
        $this->assertSame($html, $result);
        $this->assertDoesNotMatchRegularExpression('/^data:/i', $this->urls($result));

        $html = markDownToHTML('![first\nsecond](https://a/b.png)');
        $result = CommentFixture::fixCommentText(textToLink($html));
        $this->assertSame($html, $result);
        $this->assertSame('first\nsecond', $this->parse($result)->getElementsByTagName('img')->item(0)->getAttribute('alt'));
        $this->assertSame('first<br/>second', CommentFixture::fixCommentText('first\nsecond'));

        foreach (['ftp://a/x', 'file:///tmp/x', 'custom:x'] as $url) {
            $this->assertSame('', $this->urls(markDownToHTML('<img src="' . $url . '">')));
        }
        $this->assertSame('/videos/a.png', $this->urls(markDownToHTML('<img src="/videos/a.png">')));
        $this->assertSame('a.png', $this->urls(markDownToHTML('<img src="a.png">')));
    }

    public function testQuotedGreaterThanDoesNotEndRawTags()
    {
        $html = markDownToHTML('<img src="https://a/b.png" class="x > Xhttps://a/x/onerror=alert(1)//" style="width:100px">');
        $image = $this->parse($html)->getElementsByTagName('img')->item(0);
        $this->assertSame('x > Xhttps://a/x/onerror=alert(1)//', $image->getAttribute('class'));
        $this->assertSame('width:100px', $image->getAttribute('style'));
        $this->assertSame(0, $this->parse($html)->getElementsByTagName('a')->length);
        $html = markDownToHTML('<a href="https://a/x?q=>" target="_blank">link</a>');
        $link = $this->parse($html)->getElementsByTagName('a')->item(0);
        $this->assertSame('https://a/x?q=>', $link->getAttribute('href'));
        $this->assertSame('_blank', $link->getAttribute('target'));
    }

    public function testMarkdownBlocksStillRenderAndEscapeMarkup()
    {
        $html = markDownToHTML("- **first**\n- second\n\n> quoted\n\n```html\n<img src=x onerror=alert(1)>\n```\n\ntext <img src=\"https://a/b.png\"> end");
        $dom = $this->parse($html);
        $this->assertSame(2, $dom->getElementsByTagName('li')->length);
        $this->assertSame(1, $dom->getElementsByTagName('blockquote')->length);
        $this->assertSame(1, $dom->getElementsByTagName('pre')->length);
        $this->assertSame(1, $dom->getElementsByTagName('strong')->length);
        $this->assertSame(1, $dom->getElementsByTagName('img')->length);
        $this->assertFalse($dom->getElementsByTagName('img')->item(0)->hasAttribute('onerror'));
    }

    private function parse($html)
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8"><body>' . $html . '</body>');
        return $dom;
    }

    private function urls($html)
    {
        $urls = [];
        foreach ($this->parse($html)->getElementsByTagName('*') as $element) {
            foreach (['href', 'src'] as $name) {
                if ($element->hasAttribute($name)) {
                    $urls[] = $element->getAttribute($name);
                }
            }
        }
        return implode("\n", $urls);
    }

    public function testLegitimateCommentMarkupStillRenders()
    {
        $html = markDownToHTML('![pic](https://site/videos/uploads/comments/1/a.png)');
        $this->assertStringContainsString('<img src="https://site/videos/uploads/comments/1/a.png"', $html);
        $this->assertStringContainsString('img-responsive', $html);

        $html = markDownToHTML('<img src="https://a/b.png" class="c" style="width:100px">');
        $this->assertStringContainsString('<img src="https://a/b.png" class="c" style="width:100px">', $html);

        $html = markDownToHTML('<a href="https://a" target="_blank">l</a> and [t](https://x.com) **b**');
        $this->assertStringContainsString('<a href="https://a" target="_blank">l</a>', $html);
        $this->assertStringContainsString('<a href="https://x.com">t</a>', $html);
        $this->assertStringContainsString('<strong>b</strong>', $html);

        $html = markDownToHTML('see Xhttps://example.com/page ok');
        $this->assertStringContainsString('<a href="Xhttps://example.com/page" target="_blank" rel="noopener noreferrer">', $html);
    }
}
