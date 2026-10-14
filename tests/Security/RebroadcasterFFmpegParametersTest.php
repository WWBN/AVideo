<?php

namespace Tests\Security\RebroadcasterFFmpegParameters;

use PHPUnit\Framework\TestCase;

/**
 * Rebroadcaster->ffmpegParameters (separately distributed plugin) is parsed into allowlisted
 * arguments before it reaches the shell. Only the parsing methods are loaded from the real file.
 */
class RebroadcasterFFmpegParametersTest extends TestCase
{
    private static function loadParser()
    {
        $file = \APP_ROOT . '/plugin/Rebroadcaster/Rebroadcaster.php';
        if (!is_file($file)) {
            return false;
        }
        if (!class_exists(__NAMESPACE__ . '\\RebroadcasterParser', false)) {
            $source = file_get_contents($file);
            $code = '';
            foreach (['getPlaybackOptimizedFFmpegParameters', 'getFFmpegParameterRules', 'parseFFmpegParameters'] as $method) {
                $position = strpos($source, "function {$method}(");
                if ($position === false) {
                    return false;
                }
                $start = strrpos(substr($source, 0, $position), "\n") + 1;
                $end = strpos($source, "\n    }\n", $position) + 7;
                $code .= substr($source, $start, $end - $start) . "\n";
            }
            eval('namespace ' . __NAMESPACE__ . '; class RebroadcasterParser {' . str_replace(['self::', 'private static'], ['static::', 'public static'], $code) . '}');
        }
        return true;
    }

    protected function setUp(): void
    {
        if (!self::loadParser()) {
            $this->markTestSkipped('The separately distributed Rebroadcaster plugin is not installed');
        }
    }

    /**
     * @dataProvider legitimateProfiles
     */
    public function testLegitimateProfilesAreAccepted($profile): void
    {
        $this->assertIsArray(RebroadcasterParser::parseFFmpegParameters($profile, $error), $profile . ' ' . $error);
    }

    public function legitimateProfiles(): array
    {
        return [
            'default' => ["-max_muxing_queue_size 2048 -c:v libx264 -pix_fmt yuv420p -r 30 -g 60 -keyint_min 60 -sc_threshold 0 -tune zerolatency -b:v 2800k -maxrate 3200k -bufsize 6000k -preset veryfast -vf \"scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2\" -c:a aac -b:a 128k -ar 48000 -ac 2 "],
            'legacy 6000k' => ['-c:v libx264 -preset veryfast -b:v 6000k -minrate 6000k -maxrate 6000k -bufsize 12000k -c:a aac -b:a 128k '],
            'x264opts' => ['-c:v libx264 -preset veryfast -x264opts keyint=60:no-scenecut -c:a aac -b:a 128k'],
            'stream specifier preset' => ['-c:v libx264 -preset:v veryfast -tune:v zerolatency -c:a aac'],
            'global header flag' => ['-c:v libx264 -flags +global_header -c:a aac'],
            'nvenc' => ['-c:v h264_nvenc -preset p4 -b:v 3000k -c:a aac -b:a 128k -ar 44100'],
        ];
    }

    /**
     * @dataProvider rejectedProfiles
     */
    public function testShellSyntaxFilesAndExtraOutputsAreRejected($profile): void
    {
        $this->assertFalse(RebroadcasterParser::parseFFmpegParameters($profile));
    }

    public function rejectedProfiles(): array
    {
        return [
            'command separator' => ['-c:v libx264; id'],
            'command substitution' => ['-c:v $(id)'],
            'extra output' => ['-c:v libx264 /var/www/html/x.php'],
            'extra input' => ['-i /etc/passwd'],
            'file reading filter' => ['-vf movie=/etc/passwd'],
            'x264 stats file' => ['-x264-params pass=1:stats=/tmp/x'],
            'x264opts stats file' => ['-x264opts pass=1:stats=/tmp/x'],
            'flags with other syntax' => ['-flags $HOME'],
        ];
    }
}
