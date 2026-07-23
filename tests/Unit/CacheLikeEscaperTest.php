<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Tests for CacheLikeEscaper (plugin/Cache/Objects/CacheLikeEscaper.php).
 *
 * This class has zero dependency on configuration.php / the DB layer, so it can
 * be required directly and unit tested without a live MySQL connection.
 *
 * Background / regression being guarded against:
 * CachesInDB::hashName() turns every non-alphanumeric character into `_`
 * (e.g. "video/220515221820_v75c8/" -> "hashName_video_video_220515221820_v75c8_").
 * `_` and `%` are SQL LIKE wildcards. Without escaping, a prefix delete query such as
 *   WHERE name LIKE 'hashName_video_video_123_%'
 * would also match unrelated rows such as "hashNameXvideoXvideoX123X" because each
 * `_` matches any single character. CacheLikeEscaper::escapeLikePrefix() must be used
 * together with `ESCAPE '\\'` in the SQL so `_`/`%`/`\` are matched literally.
 */
class CacheLikeEscaperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists('CacheLikeEscaper')) {
            require_once \APP_ROOT . '/plugin/Cache/Objects/CacheLikeEscaper.php';
        }
    }

    /**
     * Translates a SQL `LIKE <pattern> ESCAPE '\\'` expression into a PHP regex,
     * mirroring MySQL's LIKE semantics, so we can assert matching behavior
     * without a live database connection.
     */
    private function likeMatches($subject, $pattern, $escaped = true)
    {
        $regex = '';
        $len = strlen($pattern);
        for ($i = 0; $i < $len; $i++) {
            $char = $pattern[$i];
            if ($escaped && $char === '\\' && $i + 1 < $len) {
                // Escaped metacharacter: match literally.
                $regex .= preg_quote($pattern[$i + 1], '/');
                $i++;
                continue;
            }
            if ($char === '%') {
                $regex .= '.*';
            } elseif ($char === '_') {
                $regex .= '.';
            } else {
                $regex .= preg_quote($char, '/');
            }
        }
        return (bool) preg_match('/^' . $regex . '$/s', $subject);
    }

    public function testEscapesUnderscore()
    {
        $this->assertSame('foo\\_bar', \CacheLikeEscaper::escapeLikePrefix('foo_bar'));
    }

    public function testEscapesPercent()
    {
        $this->assertSame('foo\\%bar', \CacheLikeEscaper::escapeLikePrefix('foo%bar'));
    }

    public function testEscapesBackslash()
    {
        $this->assertSame('foo\\\\bar', \CacheLikeEscaper::escapeLikePrefix('foo\\bar'));
    }

    public function testEscapesBackslashBeforeWildcardWithoutDoubleEscaping()
    {
        // A literal backslash immediately followed by a literal underscore must
        // become \\\_ (escaped backslash + escaped underscore), not \\_ (which
        // would be interpreted by MySQL as an escaped underscore only).
        $this->assertSame('a\\\\\\_b', \CacheLikeEscaper::escapeLikePrefix('a\\_b'));
    }

    public function testEscapesCombinationOfAllMetacharacters()
    {
        $input = 'a_b%c\\d';
        $expected = 'a\\_b\\%c\\\\d';
        $this->assertSame($expected, \CacheLikeEscaper::escapeLikePrefix($input));
    }

    public function testEscapingInsertsBackslashBeforeEachUnderscore()
    {
        $escaped = \CacheLikeEscaper::escapeLikePrefix('hashName_video_video_123');
        $this->assertSame('hashName\\_video\\_video\\_123', $escaped);
    }

    /**
     * Regression test: hashName_video_video_123_ must NOT match
     * hashNameXvideoXvideoX123X once the prefix is escaped.
     */
    public function testEscapedPrefixDoesNotMatchUnrelatedKeyWithSingleCharDifferences()
    {
        $prefix = 'hashName_video_video_123_';
        $unrelated = 'hashNameXvideoXvideoX123X';

        $escapedPattern = \CacheLikeEscaper::escapeLikePrefix($prefix) . '%';
        $this->assertFalse(
            $this->likeMatches($unrelated, $escapedPattern, true),
            'Escaped prefix must not match a key that only differs by single-character substitutions for "_"'
        );

        // Demonstrate the regression this guards against: the SAME unrelated key
        // WOULD match if the prefix were used unescaped (the historical bug).
        $unescapedPattern = $prefix . '%';
        $this->assertTrue(
            $this->likeMatches($unrelated, $unescapedPattern, false),
            'Sanity check: unescaped "_" is a SQL LIKE wildcard and matches any single character'
        );
    }

    public function testEscapedPrefixStillMatchesTheIntendedKey()
    {
        $prefix = 'hashName_video_video_123_';
        $intended = 'hashName_video_video_123_abc.cache';

        $escapedPattern = \CacheLikeEscaper::escapeLikePrefix($prefix) . '%';
        $this->assertTrue($this->likeMatches($intended, $escapedPattern, true));
    }

    public function testEscapedPrefixDoesNotMatchSimilarButDifferentPrefix()
    {
        // "...123_" (video id 123) must not match a cache key for video id "1234"
        // just because it shares the first three digits.
        $prefix = 'hashName_video_video_123_';
        $different = 'hashName_video_video_1234_abc';

        $escapedPattern = \CacheLikeEscaper::escapeLikePrefix($prefix) . '%';
        $this->assertFalse(
            $this->likeMatches($different, $escapedPattern, true),
            'Escaped prefix must not match a cache key belonging to a different (longer) id that merely shares a numeric prefix'
        );
    }
}
