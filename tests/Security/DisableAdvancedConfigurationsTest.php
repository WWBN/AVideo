<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * $global['disableAdvancedConfigurations'] (sites whose admin account is open on purpose) keeps
 * the settings listed in getDangerousPluginSettings() out of reach of the web UI, and the FFmpeg
 * conversion options are not read from the database at all while it is on.
 */
class DisableAdvancedConfigurationsTest extends TestCase
{
    private $savedGlobal;
    private $savedAdvancedCustom;

    protected function setUp(): void
    {
        global $global, $advancedCustom;
        require_once \APP_ROOT . '/objects/dangerousConfigurations.php';
        require_once \APP_ROOT . '/objects/functionsFFMPEG.php';
        $this->savedGlobal = $global;
        $this->savedAdvancedCustom = $advancedCustom;
    }

    protected function tearDown(): void
    {
        global $global, $advancedCustom;
        $global = $this->savedGlobal;
        $advancedCustom = $this->savedAdvancedCustom;
    }

    private function setFlag($value): void
    {
        global $global;
        if (!is_array($global)) {
            $global = [];
        }
        $global['disableAdvancedConfigurations'] = $value;
    }

    public function testCatalogMatchesPluginNamesWithoutCase(): void
    {
        $this->assertTrue(isDangerousConfigKey('CustomizeAdvanced', 'ffmpegParameters'));
        $this->assertTrue(isDangerousConfigKey('customizeadvanced', 'ffmpegParameters'));
        $this->assertTrue(isDangerousConfigKey('Live', 'restreamerURL'));
        $this->assertTrue(isDangerousConfigKey('API', 'standAloneFFMPEG'));
        $this->assertFalse(isDangerousConfigKey('CustomizeAdvanced', 'showHumanTimingOnVideoItem'));
        $this->assertFalse(isDangerousConfigKey('UnknownPlugin', 'ffmpegParameters'));
    }

    public function testSubmittedDangerousValuesAreReplacedByTheStoredOnesWhenTheFlagIsOn(): void
    {
        $this->setFlag(1);
        $stored = (object) ['ffmpegParameters' => '-c:v libx264', 'encoderNetwork' => 'https://network.example/', 'other' => 'a'];
        $submitted = (object) ['ffmpegParameters' => '-c:v libx264 -crf 1', 'encoderNetwork' => 'http://10.0.0.1/', 'other' => 'b'];

        $result = keepStoredDangerousPluginSettings('CustomizeAdvanced', $submitted, $stored, $changed);

        $this->assertSame('-c:v libx264', $result->ffmpegParameters);
        $this->assertSame('https://network.example/', $result->encoderNetwork);
        $this->assertSame('b', $result->other, 'settings outside the catalog are still editable');
        $this->assertSame(['ffmpegParameters', 'encoderNetwork'], $changed);
        $this->assertSame('-c:v libx264 -crf 1', $submitted->ffmpegParameters, 'the submitted object is not modified');
    }

    public function testDangerousValueWithoutStoredValueIsRemovedSoThePluginDefaultApplies(): void
    {
        $this->setFlag(1);
        $result = keepStoredDangerousPluginSettings('Live', (object) ['restreamerURL' => 'http://127.0.0.1/', 'x' => 1], (object) [], $changed);
        $this->assertFalse(property_exists($result, 'restreamerURL'));
        $this->assertSame(1, $result->x);
        $this->assertSame(['restreamerURL'], $changed);
    }

    public function testUnchangedOrAbsentValuesAreNotReportedAsDropped(): void
    {
        $this->setFlag(1);
        $stored = (object) ['webhook_url' => 'https://hooks.example/x'];
        keepStoredDangerousPluginSettings('SlackNotify', (object) ['webhook_url' => 'https://hooks.example/x'], $stored, $changed);
        $this->assertSame([], $changed);
        $result = keepStoredDangerousPluginSettings('SlackNotify', (object) [], $stored, $changed);
        $this->assertSame([], $changed);
        $this->assertSame('https://hooks.example/x', $result->webhook_url);
    }

    public function testEverythingStaysEditableWhenTheFlagIsOff(): void
    {
        $this->setFlag(0);
        $submitted = (object) ['ffmpegParameters' => '-c:v libx265', 'encoderNetwork' => 'https://other.example/'];
        $result = keepStoredDangerousPluginSettings('CustomizeAdvanced', $submitted, (object) ['ffmpegParameters' => '-c:v libx264'], $changed);
        $this->assertSame($submitted, $result);
        $this->assertSame([], $changed);
    }

    public function testStoredFFmpegParametersAreIgnoredWhenTheFlagIsOn(): void
    {
        global $advancedCustom;
        $advancedCustom = (object) ['ffmpegParameters' => '-c:v libx265 -crf 30'];

        $this->setFlag(0);
        $this->assertSame(['-c:v', 'libx265', '-crf', '30'], getFFmpegConversionParametersArgs());

        $this->setFlag(1);
        $this->assertSame(parseFFmpegConversionParameters(getFFmpegConversionDefaultParameters()), getFFmpegConversionParametersArgs());
        // A value that would be rejected (and logged) is not even read while the flag is on.
        $advancedCustom = (object) ['ffmpegParameters' => '-c:v libx264; touch /tmp/x'];
        $this->assertSame(parseFFmpegConversionParameters(getFFmpegConversionDefaultParameters()), getFFmpegConversionParametersArgs());
    }

    public function testSaveEndpointsKeepDangerousSettingsBeforeValidatingOrSaving(): void
    {
        $save = file_get_contents(\APP_ROOT . '/admin/save.json.php');
        $this->assertMatchesRegularExpression('/isAdvancedConfigurationsDisabled\(\).*getDangerousConfigKeys.*applyAdminPluginValues/s', $save);

        $add = file_get_contents(\APP_ROOT . '/objects/pluginAddDataObject.json.php');
        $this->assertMatchesRegularExpression('/keepStoredDangerousPluginSettings.*getAdminPluginFieldValidators.*setObject_data/s', $add);

        $functions = file_get_contents(\APP_ROOT . '/admin/functions.php');
        $this->assertStringContainsString("require_once dirname(__DIR__) . '/objects/dangerousConfigurations.php';", $functions);
    }

    public function testLiveServersCannotBeChangedWhenTheFlagIsOn(): void
    {
        foreach (['add.json.php' => 'new Live_servers', 'delete.json.php' => 'new Live_servers'] as $file => $sideEffect) {
            $source = file_get_contents(\APP_ROOT . '/plugin/Live/view/Live_servers/' . $file);
            $this->assertSame(1, preg_match("/if \(!empty\(\\\$global\['disableAdvancedConfigurations'\]\)\) \{\R\s*forbiddenPage\(/", $source, $match, PREG_OFFSET_CAPTURE), $file);
            $this->assertLessThan(strpos($source, $sideEffect), $match[0][1], "{$file}: the guard runs before the record is loaded/saved");
        }
    }

    public function testVideosFolderDeniesEveryExtensionApacheRunsAsPHP(): void
    {
        $core = file_get_contents(\APP_ROOT . '/objects/htaccess_for_videos.conf');
        $scheduler = file_get_contents(\APP_ROOT . '/plugin/Scheduler/htaccess.sample.txt');
        foreach ([$core, $scheduler] as $htaccess) {
            preg_match('/FilesMatch "\(\?i\)\\\\\.\(([^)]+)\)\$">/i', $htaccess, $deny);
            $this->assertNotEmpty($deny);
            // Debian/Ubuntu run ".+\.ph(ar|p|tml)$" (and older configs .pht) through PHP.
            foreach (['php', 'php5', 'php7', 'phtml', 'phar', 'pht', 'PHAR'] as $extension) {
                $this->assertMatchesRegularExpression('/(?i)\.(' . $deny[1] . ')$/', "shell.{$extension}", $extension);
            }
        }
    }

    public function testVideosHtaccessVersionsDoNotFightEachOther(): void
    {
        // adminSecurityCheck() replaces videos/.htaccess when its version is lower than the core
        // template; the Scheduler watchdog rewrites it unless it carries exactly its own version.
        // The core template must stay below the watchdog one, or each would undo the other (and
        // the core one would drop the watchdog's HLS/key rewrite rules).
        preg_match('/# version +([0-9.]+)/', file_get_contents(\APP_ROOT . '/objects/htaccess_for_videos.conf'), $core);
        preg_match("/htaccessVersion = '([0-9.]+)'/", file_get_contents(\APP_ROOT . '/plugin/Scheduler/watchDog.php'), $watchdog);
        $this->assertTrue(version_compare($core[1], '5.2', '>'), 'sites with the 5.2 copy get the new deny list');
        $this->assertTrue(version_compare($core[1], '5.9', '<'), 'a watchdog file is never replaced by the core template');
        $this->assertTrue(version_compare($watchdog[1], '5.9', '>'));
    }

    public function testConfigurableValuesReachTheShellEscaped(): void
    {
        $exec = file_get_contents(\APP_ROOT . '/objects/functionsExec.php');
        $this->assertStringContainsString("exec('nc -zv ' . escapeshellarg(\$host) . ' ' . intval(\$port) . ' 2>&1'", $exec);
        $statistic = file_get_contents(\APP_ROOT . '/objects/video_statistic.php');
        $this->assertStringNotContainsString("'\$cacheFileName'", $statistic);
        foreach (['/objects/Object.php', '/plugin/Cache/Cache.php'] as $file) {
            $this->assertStringNotContainsString('rm -R {$dir}', file_get_contents(\APP_ROOT . $file), $file);
        }
        $this->assertStringNotContainsString('find {$cacheDir}*', file_get_contents(\APP_ROOT . '/plugin/Cache/crontab.php'));
    }
}
