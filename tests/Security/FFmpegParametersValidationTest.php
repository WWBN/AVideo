<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * CustomizeAdvanced->ffmpegParameters is parsed into separate, allowlisted FFmpeg arguments
 * (objects/functionsFFMPEG.php) and validated when an admin saves plugin settings
 * (admin/functions.php). Real conversions with these profiles are exercised manually with ffmpeg.
 */
class FFmpegParametersValidationTest extends TestCase
{
    protected function setUp(): void
    {
        require_once \APP_ROOT . '/objects/functionsFFMPEG.php';
        require_once \APP_ROOT . '/admin/functions.php';
    }

    public function testDefaultAndCommonProfilesParseIntoSeparateArguments(): void
    {
        $this->assertSame(
            ['-c:v', 'libx264', '-preset', 'veryfast', '-crf', '23', '-c:a', 'aac', '-b:a', '128k'],
            parseFFmpegConversionParameters(getFFmpegConversionDefaultParameters())
        );
        $this->assertSame([], parseFFmpegConversionParameters(''));
        foreach ([
            '-c:v libx265 -preset fast -crf 28 -c:a aac -b:a 96k -movflags +faststart',
            '-c:v h264_nvenc -preset p4 -cq 23 -c:a aac -b:a 128k',
            '-c:v libx264 -vf scale=-2:720 -pix_fmt yuv420p -r 30000/1001 -c:a aac -ar 48000 -ac 2',
            '-c copy -bsf:a aac_adtstoasc',
            '-c:v libx264 -strict -2 -map 0:v:0 -map 0:a -sn -y',
        ] as $profile) {
            $this->assertIsArray(parseFFmpegConversionParameters($profile), $profile);
        }
    }

    /**
     * @dataProvider rejectedParameters
     */
    public function testParametersOutsideTheAllowlistAreRejected($parameters): void
    {
        $this->assertFalse(parseFFmpegConversionParameters($parameters, $error));
        $this->assertNotSame('', $error);
    }

    public function rejectedParameters(): array
    {
        return [
            'extra input' => ['-i other.mp4'],
            'muxer choice' => ['-f mp4'],
            'extra output file' => ['-c:v libx264 second.mp4'],
            'filter graph' => ['-filter_complex null'],
            'filter other than scale' => ['-vf fps=10'],
            'crf out of range' => ['-crf 99'],
            'unknown codec' => ['-c:v something'],
            'missing value' => ['-crf'],
            'quoted value' => ["-c:v 'libx264'"],
            'double quotes' => ['-c:v "libx264"'],
            'tab separator' => ["-c:v\tlibx264"],
            'newline' => ["-c:v libx264\n-crf 23"],
            'nul byte' => ["-c:v libx264\0"],
            'too long' => [str_repeat('-an ', 200)],
            'array' => [['-an']],
            'object' => [(object) ['value' => '-an']],
        ];
    }

    public function testShellStringEscapesEachArgumentOnItsOwn(): void
    {
        $command = implodeFFMPEGArgsForShell(['ffmpeg', '-threads', '4', '-i', '/videos/a b/index.m3u8', '-c:v', 'libx264', '/videos/out.mp4']);
        $this->assertSame("ffmpeg -threads 4 -i '/videos/a b/index.m3u8' -c:v libx264 /videos/out.mp4", str_replace('"', "'", $command));
    }

    public function testLocalInputGetsNoUserAgentAndKeepsTheOutputLast(): void
    {
        global $advancedCustom;
        $saved = $advancedCustom;
        $advancedCustom = (object) ['ffmpegParameters' => '-c:v libx264 -crf 20'];
        try {
            $args = buildFFMPEGCommandArgs('/videos/source.mp4', '/videos/out.mp4');
        } finally {
            $advancedCustom = $saved;
        }
        $this->assertNotContains('-user_agent', $args);
        $this->assertSame(['-i', '/videos/source.mp4', '-c:v', 'libx264', '-crf', '20', '/videos/out.mp4'], array_slice($args, -7));
    }

    public function testWindowsShellStringCannotExpandEnvironmentVariables(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $this->markTestSkipped('Requires the Windows command shell');
        }
        $variable = 'AVIDEO_FFMPEG_QUOTING_TEST';
        $previous = getenv($variable);
        putenv($variable . '=unexpected-environment-expansion');
        try {
            $command = implodeFFMPEGArgsForShell(['echo', 'https://example.test/%' . $variable . '%/clip.mp4']);
            exec($command, $output, $status);
            $this->assertSame(0, $status);
            $this->assertStringNotContainsString('unexpected-environment-expansion', implode('\n', $output));
        } finally {
            putenv($previous === false ? $variable : $variable . '=' . $previous);
        }
    }

    public function testConfiguredWrapperPreservesQuotedPathSegments(): void
    {
        global $global;
        $saved = $global;
        try {
            foreach (['"/opt/ffmpeg tools/"', "'/opt/ffmpeg tools/'"] as $prefix) {
                $global['ffmpeg'] = 'nice -n 10 ' . $prefix;
                $global['ffmpegGPU'] = false;
                $this->assertSame(['nice', '-n', '10', '/opt/ffmpeg tools/ffmpeg'], getFFmpegExecutableArgs());
            }
        } finally {
            $global = $saved;
        }
    }

    public function testOptionalNumericFieldsCanBeClearedWithoutRejectingOtherSettings(): void
    {
        $plugin = (object) [
            'autoHideNavbarInSeconds' => 0,
            'typedNumber' => (object) ['type' => 'number', 'value' => 1],
            'text' => 'old',
            'ffmpegParameters' => getFFmpegConversionDefaultParameters(),
        ];
        $values = ['autoHideNavbarInSeconds' => '', 'typedNumber' => '', 'text' => 'new'];
        $saved = applyAdminPluginValues($plugin, $values, array_keys($values), 'CustomizeAdvanced');
        $this->assertSame('', $saved->autoHideNavbarInSeconds);
        $this->assertSame('', $saved->typedNumber->value);
        $this->assertSame('new', $saved->text);
        $this->assertSame(getFFmpegConversionDefaultParameters(), $saved->ffmpegParameters);
    }

    public function testEditableFieldsComeFromThePluginObject(): void
    {
        $plugin = (object) [
            'enabled' => true,
            'text' => 'a',
            'selection' => (object) ['type' => ['a' => 'A'], 'value' => 'a'],
            'nested' => (object) ['internal' => true],
            'list' => ['x'],
        ];
        $this->assertSame(['enabled', 'text', 'selection'], getAdminPluginEditableFields($plugin));
    }

    public function testRenderedTextareaWithNullValueRemainsEditable(): void
    {
        $plugin = (object) ['text' => (object) ['type' => 'textarea', 'value' => null, 'help' => 'Keep this']];
        $fields = getAdminPluginEditableFields($plugin);
        $this->assertSame(array_keys(jsonToFormElements($plugin)), $fields);
        $saved = applyAdminPluginValues($plugin, ['text' => 'New content'], $fields);
        $this->assertSame('New content', $saved->text->value);
        $this->assertSame('Keep this', $saved->text->help);
        $this->assertNull($plugin->text->value);
    }

    public function testDirectExecutionKeepsArgumentsAndMergesStderrIntoTheLog(): void
    {
        $literal = '/videos/a b.mp4;$(echo unchanged)';
        $this->assertSame(0, execFFMPEGArgs([PHP_BINARY, '-r', 'echo $argv[1];', $literal], '', $output));
        $this->assertSame([$literal], $output);
        $log = tempnam(sys_get_temp_dir(), 'ffmpeg-log-');
        try {
            $this->assertSame(7, execFFMPEGArgs([PHP_BINARY, '-r', 'fwrite(STDOUT, "out\n"); fwrite(STDERR, "err\n"); exit(7);'], $log, $output));
            $this->assertSame([], $output);
            $this->assertSame("out\nerr\n", str_replace("\r\n", "\n", file_get_contents($log)));
        } finally {
            unlink($log);
        }
    }

    public function testValidFfmpegParametersAreSaved(): void
    {
        $plugin = (object) ['ffmpegParameters' => getFFmpegConversionDefaultParameters()];
        $saved = applyAdminPluginValues($plugin, ['ffmpegParameters' => '-c:v libx265 -crf 28'], ['ffmpegParameters'], 'CustomizeAdvanced');
        $this->assertSame('-c:v libx265 -crf 28', $saved->ffmpegParameters);
    }

    public function testRejectedValueKeepsEveryPreviousValue(): void
    {
        $plugin = (object) ['ffmpegParameters' => getFFmpegConversionDefaultParameters(), 'text' => 'old'];
        try {
            applyAdminPluginValues($plugin, ['ffmpegParameters' => '-f mp4', 'text' => 'new'], ['ffmpegParameters', 'text'], 'customizeadvanced');
            $this->fail('The invalid value was accepted');
        } catch (\AdminPluginSettingsValidationException $e) {
            $this->assertStringContainsString('ffmpegParameters', $e->getMessage());
        }
        $this->assertSame(getFFmpegConversionDefaultParameters(), $plugin->ffmpegParameters);
        $this->assertSame('old', $plugin->text);
    }

    public function testTypeRulesForSelectsNumbersAndText(): void
    {
        $plugin = (object) [
            'selection' => (object) ['type' => ['0' => 'Off', '1' => 'On'], 'value' => '0'],
            'amount' => 10,
            'typedNumber' => (object) ['type' => 'number', 'value' => 1],
            'text' => 'x',
        ];
        $saved = applyAdminPluginValues($plugin, ['selection' => '1', 'amount' => '25', 'typedNumber' => '3.5', 'text' => 'y'], ['selection', 'amount', 'typedNumber', 'text']);
        $this->assertSame('1', $saved->selection->value);
        $this->assertSame('25', $saved->amount);
        foreach ([
            'selection' => '2',
            'amount' => 'ten',
            'typedNumber' => 'one',
            'text' => str_repeat('a', 65536),
        ] as $field => $value) {
            try {
                applyAdminPluginValues($plugin, [$field => $value], [$field]);
                $this->fail("{$field} accepted an invalid value");
            } catch (\AdminPluginSettingsValidationException $e) {
                $this->assertStringContainsString($field, $e->getMessage());
            }
        }
    }

    public function testFieldsOutsideTheEditableListAreIgnored(): void
    {
        $plugin = (object) ['text' => 'keep', 'nested' => (object) ['internal' => 'keep']];
        $saved = applyAdminPluginValues($plugin, ['nested' => 'x', 'unknown' => 'x'], ['nested', 'unknown']);
        $this->assertSame('keep', $saved->nested->internal);
        $this->assertFalse(property_exists($saved, 'unknown'));
    }
}
