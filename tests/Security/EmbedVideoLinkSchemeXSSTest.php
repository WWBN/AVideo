<?php

namespace Tests\EmbedVideoLinkScheme;

use PHPUnit\Framework\TestCase;

/**
 * GHSA-vq8m-9xp3-76fg: Video::save() kept any videoLink accepted by FILTER_VALIDATE_URL, which
 * does not check the scheme, so an uploader could store javascript://%0a... on an embed video.
 * PlayerSkins and view/videoEmbeded.php put it in an iframe src, where it ran in the site origin.
 * The link must be http(s) (isValidURL()) when saved and again before it is rendered.
 */
class EmbedVideoLinkSchemeXSSTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!function_exists(__NAMESPACE__ . '\isValidURL')) {
            $source = file_get_contents(dirname(__DIR__, 2) . '/objects/functions.php');
            preg_match('/function isValidURL\(\$url\)\s*\{.*?\n\}/s', $source, $matches);
            eval('namespace ' . __NAMESPACE__ . '; ' . $matches[0]);
        }
    }

    private static function source($file)
    {
        return file_get_contents(dirname(__DIR__, 2) . '/' . $file);
    }

    public function testScriptSchemesPassFilterValidateUrlButNotIsValidURL()
    {
        foreach (['javascript://%0aalert(1)', 'JavaScript://example.com/%0aalert(1)', 'data://text/html,x', 'vbscript://x'] as $link) {
            $this->assertNotFalse(filter_var($link, FILTER_VALIDATE_URL), $link);
            $this->assertFalse(isValidURL($link), $link);
        }
    }

    public function testEmbedLinksStillValid()
    {
        foreach ([
            'https://www.youtube.com/watch?v=abcDEF12345',
            'https://vimeo.com/76979871/abc123def4?share=copy',
            'http://media.example.test/video/12/title',
            'https://player.example.test/embed?id=1&autoplay=0',
        ] as $link) {
            $this->assertTrue(isValidURL($link), $link);
        }
    }

    public function testSaveRequiresAnHttpVideoLink()
    {
        $source = self::source('objects/video.php');
        $start = strpos($source, 'public function save(');
        $this->assertNotFalse($start);
        $save = substr($source, $start, strpos($source, "\n        public function ", $start + 1) - $start);

        $this->assertStringContainsString('if (!isValidURL($this->videoLink)) {', $save);
        $this->assertStringNotContainsString('filter_var($this->videoLink, FILTER_VALIDATE_URL)', $save);
    }

    public function testEmbedIframesRevalidateStoredLinks()
    {
        foreach (['plugin/PlayerSkins/PlayerSkins.php', 'view/videoEmbeded.php'] as $file) {
            $source = self::source($file);
            $this->assertStringContainsString("\$url = isValidURL(\$video['videoLink']) ? parseVideos(\$video['videoLink']) : '';", $source, $file);
            $this->assertStringNotContainsString("\$url = parseVideos(\$video['videoLink']);", $source, $file);
        }
    }
}
