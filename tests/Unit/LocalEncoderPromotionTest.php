<?php

namespace Tests\Unit\LocalEncoderPromotion;

use PHPUnit\Framework\TestCase;

// Load the real plugin class without bootstrapping the live database or plugin loader.
$pluginSource = file_get_contents(dirname(__DIR__, 2) . '/plugin/CustomizeAdvanced/CustomizeAdvanced.php');
eval('namespace ' . __NAMESPACE__ . ';' . strstr(substr($pluginSource, strpos($pluginSource, 'class CustomizeAdvanced')), "\nclass SocialMedias", true));

class PluginAbstract {}
class AVideoPlugin
{
    public static $settings;
    public static function getDataObjectIfEnabled($name) { return self::$settings; }
}
class User
{
    public static $canUpload = true;
    public static function canUpload() { return self::$canUpload; }
}
function __($text) { return $text; }

class LocalEncoderPromotionTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['global'] = ['webSiteRootURL' => 'https://video.example.test/subsite/'];
        AVideoPlugin::$settings = (object) ['showLocalEncoder' => true];
        User::$canUpload = true;
    }

    public function testTheDownloadPageStaysOnTheCurrentSiteAndSubdirectory()
    {
        $this->assertSame('https://video.example.test/subsite/plugin/CustomizeAdvanced/localEncoder.php', CustomizeAdvanced::getLocalEncoderURL());
    }

    public function testDisabledPromotionHasNoPageLinkOrUploadMenuItem()
    {
        AVideoPlugin::$settings->showLocalEncoder = false;
        $this->assertSame('', CustomizeAdvanced::getLocalEncoderURL());
        $this->assertSame('', (new CustomizeAdvanced())->getUploadMenuButton());
    }

    public function testDisabledOrUnavailablePluginDoesNotAdvertiseThePage()
    {
        foreach ([false, null, new \stdClass()] as $settings) {
            AVideoPlugin::$settings = $settings;
            $this->assertSame('', CustomizeAdvanced::getLocalEncoderURL());
        }
    }

    public function testTheMenuLinkDoesNotGrantOrRequireNewUploadPermissions()
    {
        $plugin = new CustomizeAdvanced();
        $this->assertStringContainsString('localEncoder.php', $plugin->getUploadMenuButton());
        User::$canUpload = false;
        $this->assertSame('', $plugin->getUploadMenuButton());
    }

    public function testTheMenuEscapesTheSiteAddressAndDoesNotSendCredentials()
    {
        $GLOBALS['global']['webSiteRootURL'] = 'https://video.example.test/" onmouseover="test/';
        $html = (new CustomizeAdvanced())->getUploadMenuButton();
        $this->assertStringNotContainsString(' onmouseover="', $html);
        $this->assertStringContainsString('&quot;', $html);
        $this->assertStringNotContainsString('pass=', $html);
        $this->assertStringNotContainsString('user=', $html);
    }

    public function testBrowserLauncherStateAndMessageValidation()
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
            $this->markTestSkipped('Node is required for the browser launcher checks.');
        }
        $script = dirname(__DIR__) . '/local-encoder-launcher.test.cjs';
        $process = proc_open([$node, '--test', $script], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $output . $errors);
    }
}
