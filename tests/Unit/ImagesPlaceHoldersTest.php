<?php

namespace Tests\PlaceholderImages;

use PHPUnit\Framework\TestCase;

$placeholderSource = file_get_contents(dirname(__DIR__, 2) . '/objects/images.php');
eval('namespace ' . __NAMESPACE__ . '; ' . substr($placeholderSource, 5));

function getURL($path)
{
    $GLOBALS['placeholderURLCalls']++;
    return $GLOBALS['global']['webSiteRootURL'] . 'assets/' . rawurlencode($path) . '?cache=fixture';
}

class ImagesPlaceHoldersTest extends TestCase
{
    private $fixture;
    private $previousGlobal;
    private $previousAccept;

    protected function setUp(): void
    {
        $this->fixture = sys_get_temp_dir() . '/avideo-placeholder-' . bin2hex(random_bytes(8)) . '/';
        mkdir($this->fixture . 'view/img/placeholders', 0777, true);
        file_put_contents($this->fixture . 'view/img/placeholders/imageLandscape.webp', 'fixture');
        $this->previousGlobal = $GLOBALS['global'] ?? [];
        $this->previousAccept = $_SERVER['HTTP_ACCEPT'] ?? null;
        $GLOBALS['global'] = ['systemRootPath' => $this->fixture, 'webSiteRootURL' => 'https://cdn.example.test/'];
        $GLOBALS['placeholderURLCalls'] = 0;
        $_SERVER['HTTP_ACCEPT'] = 'image/webp';
    }

    protected function tearDown(): void
    {
        $GLOBALS['global'] = $this->previousGlobal;
        if ($this->previousAccept === null) {
            unset($_SERVER['HTTP_ACCEPT']);
        } else {
            $_SERVER['HTTP_ACCEPT'] = $this->previousAccept;
        }
        unlink($this->fixture . 'view/img/placeholders/imageLandscape.webp');
        rmdir($this->fixture . 'view/img/placeholders');
        rmdir($this->fixture . 'view/img');
        rmdir($this->fixture . 'view');
        rmdir($this->fixture);
    }

    public function testRecognizesOriginalWebPAbsoluteAndCDNPaths()
    {
        $this->assertTrue(ImagesPlaceHolders::isDefaultImage('view/img/placeholders/imageLandscape.png'));
        $this->assertTrue(ImagesPlaceHolders::isDefaultImage($this->fixture . 'view/img/placeholders/imageLandscape.webp'));
        $this->assertTrue(ImagesPlaceHolders::isDefaultImage('imageLandscape.webp'));
        $this->assertTrue(ImagesPlaceHolders::isDefaultImage(getURL('view/img/placeholders/imageLandscape.webp')));
        $this->assertFalse(ImagesPlaceHolders::isDefaultImage('https://media.example.test/video_123.webp'));
    }

    public function testEachPlaceholderURLIsResolvedOnceForManyThumbnails()
    {
        $this->assertFalse(ImagesPlaceHolders::isDefaultImage('videos/first-thumbnail.webp'));
        $this->assertSame(count(ImagesPlaceHolders::$placeholders), $GLOBALS['placeholderURLCalls']);
        for ($i = 0; $i < 50; $i++) {
            $this->assertFalse(ImagesPlaceHolders::isDefaultImage('videos/thumbnail-' . $i . '.webp'));
        }
        $this->assertSame(count(ImagesPlaceHolders::$placeholders), $GLOBALS['placeholderURLCalls']);
    }

    public function testCustomPlaceholderAndSiteChangesDoNotReuseStaleValues()
    {
        $GLOBALS['global']['imageLandscape'] = 'branding/first.png';
        $this->assertTrue(ImagesPlaceHolders::isDefaultImage('branding/first.png'));
        $GLOBALS['global']['imageLandscape'] = 'branding/second.png';
        $this->assertFalse(ImagesPlaceHolders::isDefaultImage('branding/first.png'));
        $this->assertTrue(ImagesPlaceHolders::isDefaultImage('branding/second.png'));
        $this->assertTrue(ImagesPlaceHolders::isDefaultImage(getURL('branding/second.png')));

        $GLOBALS['global']['webSiteRootURL'] = 'https://other.example.test/';
        $this->assertTrue(ImagesPlaceHolders::isDefaultImage(getURL('branding/second.png')));
    }
}
