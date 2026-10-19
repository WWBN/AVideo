<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ChannelAboutTinyMCETest extends TestCase
{
    public function testRenderedMediaConfiguration()
    {
        $node = '';
        $binary = PHP_OS_FAMILY === 'Windows' ? 'node.exe' : 'node';
        foreach (explode(PATH_SEPARATOR, getenv('PATH') ?: '') as $directory) {
            $candidate = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $binary;
            if (is_file($candidate) && is_executable($candidate)) {
                $node = $candidate;
                break;
            }
        }
        if ($node === '') {
            $this->markTestSkipped('Node is required for the TinyMCE configuration checks.');
        }

        $html = $this->renderEditor(true);
        $this->assertStringContainsString('paste_data_images: false', $html);
        $this->assertStringContainsString('remove: true', $html);
        $output = $this->runProcess([$node, '--preserve-symlinks-main', \APP_ROOT . '/tests/channel-about-tinymce.test.cjs'], $html);
        $this->assertStringContainsString('# fail 0', $output, $output);
    }

    public function testSimpleEditorsWithoutMediaKeepTheirImageBehavior()
    {
        // Gift, PayPerViewLive and VideoPlaylistScheduler use the simple editor without media
        $html = $this->renderEditor(false);
        $this->assertStringContainsString('remove: false', $html);
        $this->assertStringNotContainsString('paste_data_images', $html);
        $this->assertStringNotContainsString('image_uploadtab', $html);
        $this->assertStringNotContainsString('urlconverter_callback', $html);
        $this->assertStringNotContainsString('media_url_resolver', $html);
        $this->assertStringNotContainsString('sandbox_iframes_exclusions', $html);
        $this->assertStringNotContainsString('image media', $html);
    }

    private function renderEditor($allowImageAndMedia)
    {
        // Render in a separate process so template helpers cannot replace suite-wide globals.
        $renderer = <<<'PHP'
$global = ['systemRootPath' => $argv[1] . '/', 'webSiteRootURL' => 'https://example.com/avideo/'];
$_SESSION = ['language' => 'en_US'];
$id = 'reviewAbout';
$simpleMode = true;
$allowImageAndMedia = $argv[2] === '1';
function getURL($url) { return $url; }
function __($text) { return $text; }
include $global['systemRootPath'] . 'objects/functionsGetTinyMCE.php';
PHP;
        return $this->runProcess([PHP_BINARY, '-r', $renderer, \APP_ROOT, $allowImageAndMedia ? '1' : '0']);
    }

    private function runProcess(array $command, $input = '')
    {
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $output . $errors);
        return $output;
    }
}
