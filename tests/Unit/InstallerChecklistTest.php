<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/install/checklist-functions.php';

class InstallerChecklistTest extends TestCase
{
    private $site;
    private $files = [];
    private $directories = [];

    private function createSite()
    {
        if ($this->site) { return; }
        $this->site = sys_get_temp_dir() . '/avideo-checklist-' . bin2hex(random_bytes(8));
        $this->makeDirectory($this->site);
        foreach (['index.php', 'installer.php', 'checklist-functions.php', 'ubuntu-help-functions.php', 'ubuntu-help.php', 'database.sql'] as $name) {
            $this->writeFile('install/' . $name, file_get_contents(dirname(__DIR__, 2) . '/install/' . $name));
        }
        $this->writeFile('objects/bcp47.php', file_get_contents(dirname(__DIR__, 2) . '/objects/bcp47.php'));
        $this->writeFile('locale/en_US.php', '<?php');
        $this->makeDirectory($this->site . '/videos');
        foreach (['vendor/autoload.php', 'vendor/erusev/parsedown/Parsedown.php', 'node_modules/jquery/dist/jquery.min.js'] as $name) {
            $this->writeFile($name, '');
        }
    }

    private function makeDirectory($path)
    {
        if (is_dir($path)) { return; }
        $parent = dirname($path);
        if (!is_dir($parent)) { $this->makeDirectory($parent); }
        mkdir($path, 0700);
        $this->directories[] = $path;
    }

    private function writeFile($name, $content)
    {
        $path = $this->site . '/' . $name;
        $this->makeDirectory(dirname($path));
        file_put_contents($path, $content);
        $this->files[$path] = $path;
        return $path;
    }

    protected function tearDown(): void
    {
        // Only remove the exact temporary files/directories created by this test.
        foreach (array_reverse($this->files) as $path) { unlink($path); }
        foreach (array_reverse($this->directories) as $path) { rmdir($path); }
        parent::tearDown();
    }

    private function runPhp($code, array $ini = [])
    {
        if (!function_exists('proc_open')) { $this->markTestSkipped('PHP process isolation requires proc_open.'); }
        $this->createSite();
        $command = [PHP_BINARY];
        foreach ($ini as $name => $value) { $command[] = '-d'; $command[] = $name . '=' . $value; }
        // Separate PHP processes isolate ini settings and installer globals from PHPUnit.
        $command[] = '-r';
        $command[] = 'require $argv[1]; require $argv[2]; ' . $code;
        $command[] = $this->site . '/install/installer.php';
        $command[] = $this->site . '/install/checklist-functions.php';
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $this->site);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertSame('', $errors);
        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    }

    private function checks($setup = '', array $ini = [])
    {
        return array_column($this->runPhp($setup . ' echo json_encode(installerAdditionalChecks());', $ini), null, 'label');
    }

    /** @dataProvider limitValues */
    public function testLimitsHandleUnitsUnlimitedValuesAndInvalidInput($setting, $value, $expected)
    {
        $this->assertSame($expected, installerChecklistLimit($setting, $value));
    }

    public function limitValues(): array
    {
        return [
            ['memory_limit', '-1', 'passed'], ['memory_limit', '512M', 'passed'],
            ['memory_limit', '128M', 'failed'], ['post_max_size', '0', 'passed'],
            ['post_max_size', '8G', 'passed'], ['post_max_size', '8M', 'failed'],
            ['upload_max_filesize', '100m', 'passed'], ['upload_max_filesize', '2M', 'failed'],
            ['max_execution_time', '0', 'passed'], ['max_execution_time', '7200', 'passed'],
            ['max_execution_time', '30', 'failed'], ['memory_limit', 'invalid', 'unknown'],
        ];
    }

    public function testToolDiscoveryHandlesSpacesDuplicatesAndRelativePathsWithoutExecutingFiles()
    {
        $this->createSite();
        $binary = $this->writeFile('test tools/checklist-tool' . (PHP_OS_FAMILY === 'Windows' ? '.exe' : ''), 'This fixture must never be executed.');
        chmod($binary, 0755);
        $directory = dirname($binary);
        $this->assertSame([realpath($binary)], installerChecklistPrograms(['checklist-tool'], $directory . PATH_SEPARATOR . $directory));
        $this->assertSame([], installerChecklistPrograms(['checklist-tool'], '.'));
    }

    public function testMissingToolsAndRemoteServicesAreNotReportedAsInstalledOrBroken()
    {
        $checks = $this->checks('putenv("PATH=" . installerRoot() . "empty-path");');
        foreach (['FFmpeg executable', 'Encoder connection and processing', 'Scheduled tasks and yt-dlp updates'] as $label) {
            $this->assertSame('unknown', $checks[$label]['status'], $label);
        }
        $this->assertSame('failed', $checks['HTMLPurifier serializer cache']['status']);
        $this->assertSame('passed', $checks['PHP system temporary directory']['status']);
    }

    public function testApacheModulesDistinguishUnavailableMissingAndNotApplicable()
    {
        $checks = $this->checks('$_SERVER["SERVER_SOFTWARE"]="Apache";');
        $this->assertSame('unknown', $checks['Apache mod_rewrite']['status']);
        $checks = $this->checks('$_SERVER["SERVER_SOFTWARE"]="nginx";');
        $this->assertSame('na', $checks['Apache mod_rewrite']['status']);
        $checks = $this->checks('function apache_get_modules() { return ["mod_rewrite", "mod_headers"]; }');
        $this->assertSame('passed', $checks['Apache mod_rewrite']['status']);
        $this->assertSame('failed', $checks['Apache mod_xsendfile']['status']);
    }

    /** @dataProvider postLimits */
    public function testPostCapacityAcceptsEqualLimitsAndAtLeastTwoGigabytes($postSize, $uploadSize, $expected)
    {
        $checks = $this->checks('', ['upload_max_filesize' => $uploadSize, 'post_max_size' => $postSize]);
        $this->assertSame($expected, $checks['POST capacity for file uploads']['status']);
    }

    public function postLimits(): array
    {
        return [
            ['8G', '8G', 'passed'], ['9G', '8G', 'passed'], ['0', '8G', 'passed'],
            ['2G', '8G', 'passed'], ['3G', '8G', 'passed'], ['2048M', '8G', 'passed'],
            ['2047M', '8G', 'failed'], ['1G', '8G', 'failed'], ['2G', '2G', 'passed'],
            ['512M', '512M', 'passed'], ['256M', '512M', 'failed'],
        ];
    }

    public function testDiagnosticsWorkWhenProcessFunctionsAndUploadsAreDisabled()
    {
        $checks = $this->checks('', ['disable_functions' => 'exec,shell_exec,proc_open', 'file_uploads' => '0']);
        foreach (['PHP exec', 'PHP shell_exec', 'PHP proc_open', 'PHP file uploads'] as $label) {
            $this->assertSame('failed', $checks[$label]['status'], $label);
        }
    }

    /** @dataProvider httpsValues */
    public function testRequestHttpsUsesRuntimeState($https, $expected)
    {
        $checks = $this->checks('$_SERVER["HTTPS"]=' . var_export($https, true) . '; $_SERVER["HTTP_X_FORWARDED_PROTO"]="https";');
        $this->assertSame($expected, $checks['HTTPS on this request']['status']);
    }

    public function httpsValues(): array
    {
        return [['off', 'unknown'], ['on', 'passed']];
    }

    public function testWindowsConvertIsNotDetectedAsImageMagick()
    {
        if (PHP_OS_FAMILY !== 'Windows') { $this->markTestSkipped('Windows executable name collision.'); }
        $this->createSite();
        $this->writeFile('test tools/convert.exe', 'Windows convert is not ImageMagick.');
        $checks = $this->checks('putenv("PATH=" . installerRoot() . "test tools");');
        $this->assertSame('unknown', $checks['ImageMagick executable']['status']);
    }

    public function testRenderedChecklistContainsAllStatesAndKeepsRequiredReadinessSeparate()
    {
        $result = $this->runPhp('$_SERVER["SERVER_SOFTWARE"]="nginx"; ob_start(); require __DIR__ . "/install/index.php"; $html=ob_get_clean(); echo json_encode(["html"=>$html,"ready"=>$ready,"required"=>$checks]);');
        $this->assertStringContainsString('Installation checklist', $result['html']);
        foreach (['passed', 'failed', 'unknown', 'na'] as $status) {
            $this->assertStringContainsString('requirement-item ' . $status, $result['html']);
        }
        $this->assertSame(!in_array(false, array_column($result['required'], 'ok'), true), $result['ready']);
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try { $document->loadHTML($result['html']); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
        $xpath = new DOMXPath($document);
        $this->assertSame($result['ready'] ? 0 : 1, $xpath->query('//*[@id="installButton"]/@disabled')->length);
        $this->assertSame($result['ready'] ? '1' : '0', $xpath->evaluate('string(//*[@id="configurationForm"]/@data-ready)'));
    }

    public function testCompletedSetupDoesNotExecuteConfigurationOrDisplayDiagnostics()
    {
        $this->createSite();
        $this->writeFile('videos/configuration.php', '<?php throw new Exception("CONFIG_MUST_NOT_EXECUTE");');
        $this->assertSame([], $this->checks());
        $page = $this->runPhp('ob_start(); require __DIR__ . "/install/index.php"; echo json_encode(ob_get_clean());');
        $this->assertStringContainsString('Installation complete', $page);
        foreach (['CONFIG_MUST_NOT_EXECUTE', 'requirement-item', 'configurationForm', 'PHP system temporary directory'] as $text) {
            $this->assertStringNotContainsString($text, $page);
        }
    }

    public function testUbuntuHelpOnlyAppearsForMissingDependencies()
    {
        foreach ([true, false] as $available) {
            $checks = [['label' => 'FFmpeg', 'ok' => $available, 'detail' => 'Media tools']];
            $page = $this->runPhp('function h($value) { return htmlspecialchars($value, ENT_QUOTES, "UTF-8"); } $checks = ' . var_export($checks, true) . '; ob_start(); require __DIR__ . "/install/ubuntu-help.php"; echo json_encode(ob_get_clean());');
            if ($available) {
                $this->assertStringNotContainsString('Ubuntu setup help', $page);
            } else {
                $this->assertStringContainsString('Ubuntu setup help', $page);
            }
        }
    }
}
