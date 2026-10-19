<?php

namespace Tests\Fixtures\CatalogVisibility;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/fixtures/catalog-visibility.php';

class CatalogPlaybackSourcesTest extends TestCase
{
    private function row($password = ''): array
    {
        return ['id' => 1, 'title' => 'Restricted title', 'images' => ['poster' => 'poster.jpg'],
            'video_password' => $password, 'videos' => ['mp4' => 'private.mp4'],
            'sources' => [['src' => 'private.mp4']], 'videoLink' => 'private.m3u8',
            'videoDownloadedLink' => 'download.mp4', 'playlist' => [['videoLink' => 'episode.mp4']]];
    }

    public function testListingMetadataDoesNotAuthorizePlayback(): void
    {
        Fixture::reset();
        $row = $this->row();
        APIPlayback::filter($row, [], false);
        $this->assertSame('Restricted title', $row['title']);
        $this->assertSame(['poster' => 'poster.jpg'], $row['images']);
        $this->assertEquals(new \stdClass(), $row['videos']);
        $this->assertSame([], $row['sources']);
        $this->assertSame('', $row['videoLink']);
        $this->assertSame('', $row['videoDownloadedLink']);
        $this->assertSame([], $row['playlist']);
    }

    public function testAuthorizedViewerKeepsSources(): void
    {
        Fixture::reset();
        Fixture::$canWatch = true;
        $row = $expected = $this->row();
        APIPlayback::filter($row, [], false);
        $this->assertSame($expected, $row);
    }

    public function testCorrectPasswordDoesNotBypassGroupPermission(): void
    {
        Fixture::reset();
        $row = $this->row('1');
        APIPlayback::filter($row, ['video_password' => 'fixture-password'], false);
        $this->assertSame([], $row['sources']);
    }

    public function testPasswordStillRequiredForAuthorizedGroup(): void
    {
        Fixture::reset();
        Fixture::$canWatch = true;
        $row = $this->row('1');
        APIPlayback::filter($row, ['video_password' => 'wrong'], false);
        $this->assertSame([], $row['sources']);
        $row = $expected = $this->row('1');
        APIPlayback::filter($row, ['video_password' => 'fixture-password'], false);
        $this->assertSame($expected, $row);
    }

    public function testFilterReportsWhetherPlaybackIsAllowed(): void
    {
        Fixture::reset();
        $row = $this->row();
        $this->assertFalse(APIPlayback::filter($row, [], false));
        Fixture::$canWatch = true;
        $row = $this->row();
        $this->assertTrue(APIPlayback::filter($row, [], false));
        Fixture::$canWatch = false;
        $row = $this->row();
        $this->assertTrue(APIPlayback::filter($row, [], true));
    }

    public function testPlaylistMp3IsOnlyBuiltWhenPlaybackIsAllowed(): void
    {
        // The MP3 URL is playable, so APIName=video&playlist=1 must not return it for a listed restricted video.
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/API/API.php');
        $this->assertSame(1, preg_match_all('/convertVideoToMP3FileIfNotExists\(\$value\[\'id\'\]\)/', $source));
        $this->assertStringContainsString('$canPlay = self::filterVideoSources($rows[$key], $parameters, $isAPISecretValid);', $source);
        $this->assertStringContainsString("\$rows[\$key]['mp3'] = \$canPlay ? convertVideoToMP3FileIfNotExists(\$value['id']) : false;", $source);
    }

    public function testTrustedAPISecretKeepsExistingSourceAccess(): void
    {
        Fixture::reset();
        $row = $expected = $this->row('1');
        APIPlayback::filter($row, [], true);
        $this->assertSame($expected, $row);
    }
}
