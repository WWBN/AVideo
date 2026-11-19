<?php

use PHPUnit\Framework\TestCase;

class FeedMrssXmlTest extends TestCase
{
    public function testTextAndUrlsRemainValidXmlOnColdAndWarmCache()
    {
        $index = file_get_contents(APP_ROOT . '/feed/index.php');
        $this->assertSame(1, preg_match('/function feedText\(\$text\)\s*\{.*?\n\}/s', $index, $match));
        $script = <<<'PHP'
function br2nl($value) { return preg_replace('/<br\s*\/?\s*>/i', "\n", $value); }
function recreateCache() { return false; }
function _ob_start() { ob_start(); }
function _ob_end_clean() { ob_end_clean(); }
function getVideosURL($filename, $recreate) { return []; }
function mime_content_type_per_filename($value) { return 'video/mp4'; }
function durationToSeconds($value) { return 60; }
class ObjectYPT {
    public static $cache = ['feedCacheMRSSsample' => 'LEGACY INVALID & XML'];
    static function getCache($key, $ttl) { return self::$cache[$key] ?? false; }
    static function setCache($key, $value) { self::$cache[$key] = $value; }
}
class Video {
    static function getVideoFromFileName($filename) { return ['filesize' => 123]; }
    static function getSourceFileURL($filename, $full) { return 'https://media.example.invalid/video.mp4?a=1&b="two"'; }
    static function getLink($id, $slug) { return 'https://example.invalid/watch?a=1&b=2'; }
    static function getLinkToVideo(...$args) { return 'https://example.invalid/watch?a=1&b=2'; }
    static function htmlDescription($value) { return '<p>Safe description</p>'; }
    static function getPoster($id) { return 'https://example.invalid/poster.jpg?a=1&b=2'; }
}
$global = ['webSiteRootURL' => 'https://example.invalid/'];
$_SERVER['REQUEST_URI'] = '/channel/test/mrss?a=1&b=2';
$feedCacheSuffix = 'sample';
$title = 'Site &amp; <b>channel</b>';
$description = 'An &amp; overview';
$link = 'https://example.invalid/channel?a=1&b=2';
$logo = 'https://example.invalid/logo.png?a=1&b=2';
$rows = [['id' => 1, 'filename' => 'fixture', 'title' => 'Tom & Jerry <b>Tea</b> &amp; Café ]]>', 'clean_title' => 'fixture', 'created' => '2026-01-01 00:00:00', 'category' => 'A & B', 'duration' => '00:01:00']];
$documents = [];
for ($pass = 0; $pass < 2; $pass++) {
    ob_start();
    include $argv[1];
    $documents[] = ob_get_clean();
}
echo json_encode($documents);
PHP;
        $command = $match[0] . "\n" . $script;
        $process = proc_open([PHP_BINARY, '-r', $command, APP_ROOT . '/feed/mrss.php'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertSame('', $errors);
        $documents = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $previous = libxml_use_internal_errors(true);
        try {
            foreach ($documents as $document) {
                $xml = simplexml_load_string($document);
                $this->assertNotFalse($xml, json_encode(array_map(function ($error) { return trim($error->message); }, libxml_get_errors())));
                $this->assertSame('Site & channel', (string) $xml->channel->title);
                $this->assertSame('Tom & Jerry Tea & Café ]]>', (string) $xml->channel->item->title);
                $this->assertSame('Tom & Jerry Tea & Café ]]>', (string) $xml->channel->item->description);
                $this->assertSame('https://example.invalid/watch?a=1&b=2', (string) $xml->channel->item->guid);
                $this->assertSame('https://example.invalid/logo.png?a=1&b=2', (string) $xml->channel->image->url);
                $media = $xml->channel->item->children('http://search.yahoo.com/mrss/');
                $this->assertSame('https://media.example.invalid/video.mp4?a=1&b="two"', (string) $media->content->attributes()['url']);
                $this->assertSame('https://example.invalid/poster.jpg?a=1&b=2', (string) $media->content->children('http://search.yahoo.com/mrss/')->thumbnail->attributes()['url']);
                $this->assertSame('A & B', (string) $media->category);
                libxml_clear_errors();
            }
            $this->assertStringContainsString('<!-- NO cache -->', $documents[0]);
            $this->assertStringContainsString('<!-- cache -->', $documents[1]);
        } finally {
            libxml_use_internal_errors($previous);
        }
    }
}
