<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * parseVideos() builds the iframe URL for embed videos and for LiveLinks that are not YouTube.
 * vimeo.com pages (including live event pages) send X-Frame-Options: sameorigin, so they must be
 * turned into player.vimeo.com/video/ID (unlisted videos keep their ?h= hash) or event/ID/embed.
 */
class ParseVideosVimeoTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // objects/functions.php loads the whole application, so only these pure helpers are evaluated
        $source = file_get_contents(\APP_ROOT . '/objects/functions.php');
        foreach (['fixURL', 'appendParams', 'parseVideos'] as $name) {
            if (!function_exists($name)) {
                eval(self::functionSource($source, $name));
            }
        }
    }

    private static function functionSource($source, $name)
    {
        $tokens = token_get_all($source);
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) {
                continue;
            }
            $j = $i + 1;
            while (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                $j++;
            }
            if (!is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING || $tokens[$j][1] !== $name) {
                continue;
            }
            $code = '';
            $depth = 0;
            $opened = false;
            for ($k = $i; $k < $count; $k++) {
                $text = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];
                $code .= $text;
                if ($text === '{' || (is_array($tokens[$k]) && in_array($tokens[$k][0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                    $depth++;
                    $opened = true;
                } elseif ($text === '}') {
                    $depth--;
                    if ($opened && $depth === 0) {
                        return $code;
                    }
                }
            }
        }
        throw new \RuntimeException("{$name}() not found in objects/functions.php");
    }

    public function testPublicVideoLinksAreUnchanged()
    {
        $this->assertSame('//player.vimeo.com/video/76979871', \parseVideos('https://vimeo.com/76979871'));
        $this->assertSame('//player.vimeo.com/video/76979871', \parseVideos('https://player.vimeo.com/video/76979871'));
        $this->assertSame('//player.vimeo.com/video/76979871', \parseVideos('https://vimeo.com/channels/staffpicks/76979871'));
        $this->assertSame('//player.vimeo.com/video/76979871', \parseVideos('https://vimeo.com/76979871?share=copy'));
        $this->assertSame('//player.vimeo.com/video/76979871', \parseVideos('https://vimeo.com/76979871/description'));
    }

    public function testUnlistedVideoKeepsItsHash()
    {
        $this->assertSame('//player.vimeo.com/video/76979871?h=abc123def4', \parseVideos('https://vimeo.com/76979871/abc123def4'));
        $this->assertSame('//player.vimeo.com/video/76979871?h=abc123def4', \parseVideos('https://vimeo.com/76979871/abc123def4?share=copy'));
        $this->assertSame('//player.vimeo.com/video/76979871?h=abc123def4', \parseVideos('https://player.vimeo.com/video/76979871?h=abc123def4'));
        $this->assertSame('//player.vimeo.com/video/76979871?h=abc123def4', \parseVideos('<iframe src="https://player.vimeo.com/video/76979871?h=abc123def4&amp;badge=0"></iframe>'));
    }

    public function testLiveEventPageBecomesItsEmbedPage()
    {
        $this->assertSame('//vimeo.com/event/1234567/embed', \parseVideos('https://vimeo.com/event/1234567'));
        $this->assertSame('//vimeo.com/event/1234567/embed', \parseVideos('https://vimeo.com/event/1234567/?share=copy'));
        $this->assertSame('https://vimeo.com/event/1234567/embed', \parseVideos('https://vimeo.com/event/1234567/embed'));
    }

    public function testYouTubeLinksAreNotAffected()
    {
        $this->assertStringStartsWith('//www.youtube.com/embed/abcDEF12345?', \parseVideos('https://www.youtube.com/watch?v=abcDEF12345'));
    }
}
