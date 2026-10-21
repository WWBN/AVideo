<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/objects/PHPRuntimeSecurity.php';

class PHPRuntimeSecurityTest extends TestCase
{
    private function assess($version, $sapi = 'fpm-fcgi')
    {
        preg_match('/^(\d+)\.(\d+)\.(\d+)/', $version, $parts);
        return PHPRuntimeSecurity::evaluate($version, (int) $parts[1] * 10000 + (int) $parts[2] * 100 + (int) $parts[3], $sapi);
    }

    public static function releaseBoundaries()
    {
        foreach (['8.2' => 35, '8.3' => 36, '8.4' => 27, '8.5' => 12] as $branch => $patch) {
            foreach ([-1, 0, 1] as $offset) {
                yield $branch . ':' . $offset => [$branch . '.' . ($patch + $offset), $branch . '.' . $patch, $offset < 0 ? 'potentially_affected' : 'meets_version_criteria'];
            }
        }
    }

    /** @dataProvider releaseBoundaries */
    public function testEachBranchHasItsOwnInclusiveBoundary($version, $fixed, $status)
    {
        $assessment = $this->assess($version);
        $this->assertSame($fixed, $assessment['fixed_version']);
        $this->assertSame($status, $assessment['status']);
    }

    public static function uncertainBuilds()
    {
        yield ['8.2.35RC1', 'unknown', 'prerelease'];
        yield ['8.3.36-dev', 'unknown', 'prerelease'];
        yield ['8.4.28alpha1', 'unknown', 'prerelease'];
        yield ['8.5.13beta2', 'unknown', 'prerelease'];
        yield ['8.5.12-snapshot', 'unknown', 'prerelease'];
        yield ['8.6.0', 'unknown', 'unmapped_branch'];
        yield ['9.0.0', 'unknown', 'unmapped_branch'];
        yield ['8.2.34-1ubuntu1', 'potentially_affected', 'below_fixed_version'];
        yield ['8.3.35+deb12u9', 'potentially_affected', 'below_fixed_version'];
        yield ['8.4.26-1.el9.remi', 'potentially_affected', 'below_fixed_version'];
        yield ['8.2.35-1ubuntu1', 'unknown', 'vendor_build'];
        yield ['8.3.37+deb12u9', 'unknown', 'vendor_build'];
        yield ['8.4.27-custom', 'unknown', 'vendor_build'];
        yield ['8.5.12pl1', 'unknown', 'unrecognized_version'];
        yield ['7.3.33', 'potentially_affected', 'legacy_branch'];
        yield ['7.4.33+vendor.100', 'potentially_affected', 'legacy_branch'];
        yield ['8.0.30', 'potentially_affected', 'legacy_branch'];
        yield ['8.1.99', 'potentially_affected', 'legacy_branch'];
    }

    /** @dataProvider uncertainBuilds */
    public function testOldFuturePrereleaseAndVendorBuildsAreExplicit($version, $status, $reason)
    {
        $assessment = $this->assess($version);
        $this->assertSame($status, $assessment['status']);
        $this->assertSame($reason, $assessment['reason']);
    }

    public function testDetectionFailureNeverPasses()
    {
        foreach ([[null, null, null], ['', 0, 'fpm-fcgi'], ['unknown', 80235, 'fpm-fcgi'], ['8.2.35', 80234, 'fpm-fcgi'], ['8.2.35', '80235', 'fpm-fcgi'], ['8.2.35', 80235, null], ['8.2.35.1', 80235, 'fpm-fcgi']] as $input) {
            $this->assertSame('unknown', PHPRuntimeSecurity::evaluate(...$input)['status']);
        }
        $detected = PHPRuntimeSecurity::detect();
        $this->assertSame(PHP_VERSION, $detected['version']);
        $this->assertSame(PHP_VERSION_ID, $detected['version_id']);
        $this->assertSame(PHP_SAPI, $detected['sapi']);
        $this->assertSame('unknown', $detected['status'], 'The test CLI cannot establish the website runtime status.');
        $failure = $this->fixture(['action' => 'failure']);
        $this->assertSame('unknown', $failure['assessment']['status']);
        $this->assertSame('detection_failed', $failure['assessment']['reason']);
        $this->assertSame('PHP runtime security check failed', $failure['log']);
    }

    public function testNoWebSapiIsExemptAndNonWebSapisRemainUnknown()
    {
        foreach (['apache2handler', 'apache2filter', 'fpm-fcgi', 'cgi-fcgi', 'cgi', 'litespeed', 'frankenphp', 'cli-server'] as $sapi) {
            $this->assertSame('potentially_affected', $this->assess('8.2.34', $sapi)['status']);
            $this->assertSame('meets_version_criteria', $this->assess('8.2.35', $sapi)['status']);
        }
        foreach (['cli', 'phpdbg', 'unknown', 'new-web-sapi', ''] as $sapi) {
            $this->assertSame('unknown', $this->assess('8.2.35', $sapi)['status']);
        }
    }

    private function fixture(array $input)
    {
        $process = proc_open([PHP_BINARY, dirname(__DIR__) . '/fixtures/php-runtime-security.php'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($process);
        fwrite($pipes[0], json_encode($input));
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertSame('', $errors);
        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    }

    private function render(array $assessment, array $extra = [])
    {
        return $this->fixture(array_merge(['action' => 'render', 'logged' => true, 'admin' => true, 'assessment' => $assessment], $extra));
    }

    public function testOnlyAnAuthenticatedAdministratorSeesTheNotice()
    {
        $assessment = $this->assess('8.2.34');
        foreach ([[false, false], [false, true], [true, false]] as $roles) {
            $this->assertSame('', $this->render($assessment, ['logged' => $roles[0], 'admin' => $roles[1]]));
        }
        $html = $this->render($assessment);
        foreach (['role="status"', 'Potentially affected', '8.2.34', 'fpm-fcgi', '8.2.35', 'CVE-2026-109618', 'CVE-2026-109619', 'Updating AVideo alone does not fix PHP', 'not every worker'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        foreach (PHPRuntimeSecurity::SOURCES as $url) {
            $this->assertStringContainsString($url, $html);
        }
        $this->assertStringNotContainsString('dismiss', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function testUnknownAndPassingNoticesDoNotClaimGeneralSecurity()
    {
        $this->assertStringContainsString('Unknown status', $this->render(PHPRuntimeSecurity::evaluate(null, null, null)));
        $passing = $this->assess('8.4.27');
        $this->assertSame('', $this->render($passing));
        $this->assertStringContainsString('does not establish general system security', $this->render($passing, ['show_passing' => true]));
        $this->assertStringContainsString('no longer receive upstream', $this->render($this->assess('8.1.33')));
    }

    public function testVendorBuildCannotImplyAnAutomaticallyVerifiedBackport()
    {
        $assessment = $this->assess('8.2.34+deb12u99');
        $html = $this->render($assessment);
        $this->assertStringContainsString('Potentially affected; confirm the fix with your hosting provider.', $html);
        $this->assertStringContainsString('does not verify a backport', $html);
        $this->assertStringNotContainsString('vulnerability confirmed', strtolower($html));
    }

    private function changelog($firstLine = 'php8.5 (8.5.4-0ubuntu1.5) resolute-security; urgency=medium', $extra = '')
    {
        // Shape of the real Ubuntu entry (php8.3 8.3.6-0ubuntu0.24.04.13 / php8.5 8.5.4-0ubuntu1.5).
        return $firstLine . "\n\n  * SECURITY UPDATE: Remote RCE via state corruption issue\n    - debian/patches/CVE-2026-109618_109619.patch: Avoid state corruption via\n      bailout in Zend/zend.c, Zend/zend_alloc.c.\n    - CVE-2026-109618\n    - CVE-2026-109619\n" . $extra
            . "\n -- Ubuntu Security <security@ubuntu.com>  Mon, 19 Oct 2026 11:16:58 -0400\n\nphp8.5 (8.5.4-0ubuntu1.4) resolute-security; urgency=medium\n";
    }

    private function maps($engineFile, $deleted = false)
    {
        return "55d0c0000000-55d0c0001000 r--p 00000000 08:01 101                        /usr/sbin/apache2\n"
            . "7f3a10000000-7f3a10400000 r-xp 00000000 08:01 202                        " . $engineFile . ($deleted ? ' (deleted)' : '') . "\n"
            . "7f3a20000000-7f3a20200000 r-xp 00000000 08:01 303                        /usr/lib/x86_64-linux-gnu/libc.so.6\n";
    }

    public static function distributionBackports()
    {
        yield 'Ubuntu 26.04 mod_php' => ['8.5.4', 'apache2handler', '/usr/lib/apache2/modules/libphp8.5.so', 'php8.5 (8.5.4-0ubuntu1.5) resolute-security; urgency=medium', 'php8.5 8.5.4-0ubuntu1.5'];
        yield 'Ubuntu 24.04 mod_php' => ['8.3.6', 'apache2handler', '/usr/lib/apache2/modules/libphp8.3.so', 'php8.3 (8.3.6-0ubuntu0.24.04.13) noble-security; urgency=medium', 'php8.3 8.3.6-0ubuntu0.24.04.13'];
        yield 'Debian FPM with a suffix' => ['8.2.29-1~deb12u1', 'fpm-fcgi', '/usr/sbin/php-fpm8.2', 'php8.2 (8.2.29-1~deb12u1) bookworm-security; urgency=medium', 'php8.2 8.2.29-1~deb12u1'];
        yield 'Vendor build at the minimum' => ['8.4.27-1ubuntu1', 'fpm-fcgi', '/usr/sbin/php-fpm8.4', 'php8.4 (8.4.27-1ubuntu1) resolute-security; urgency=medium', 'php8.4 8.4.27-1ubuntu1'];
        yield 'Ubuntu 22.04 legacy branch, CGI' => ['8.1.2', 'cgi-fcgi', '/usr/bin/php-cgi8.1', 'php8.1 (8.1.2-1ubuntu2.25) jammy-security; urgency=medium', 'php8.1 8.1.2-1ubuntu2.25'];
    }

    /** @dataProvider distributionBackports */
    public function testDistributionChangelogOfTheRunningPackageConfirmsTheBackport($version, $sapi, $engineFile, $firstLine, $package)
    {
        $assessment = PHPRuntimeSecurity::evaluateDistributionEvidence($this->assess($version, $sapi), $this->maps($engineFile), $this->changelog($firstLine));
        $this->assertSame('distribution_backport', $assessment['status']);
        $this->assertSame('distribution_changelog', $assessment['reason']);
        $this->assertSame($package, $assessment['package']);
        $this->assertSame('', $this->render($assessment), 'Like a fixed release, a confirmed backport shows no notice on ordinary pages.');
        $html = $this->render($assessment, ['show_passing' => true]);
        foreach (['alert-info', 'Fixed by the installed distribution package', 'does not establish general system security', $package, $version] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('Potentially affected', $html);
    }

    public function testIncompleteDistributionEvidenceChangesNothing()
    {
        $assessment = $this->assess('8.5.4', 'apache2handler');
        $maps = $this->maps('/usr/lib/apache2/modules/libphp8.5.so');
        $cases = [
            'maps unreadable' => [$assessment, null, $this->changelog()],
            'changelog unreadable' => [$assessment, $maps, null],
            'engine compiled elsewhere' => [$assessment, $this->maps('/usr/local/lib/libphp.so'), $this->changelog()],
            'engine of another branch' => [$assessment, $this->maps('/usr/lib/apache2/modules/libphp8.4.so'), $this->changelog()],
            'engine of another SAPI' => [$assessment, $this->maps('/usr/sbin/php-fpm8.5'), $this->changelog()],
            'engine path only as a prefix' => [$assessment, $this->maps('/usr/lib/apache2/modules/libphp8.5.so.bak'), $this->changelog()],
            'package of another version' => [$assessment, $maps, $this->changelog('php8.5 (8.5.5-0ubuntu1) resolute-security; urgency=medium')],
            'package of a longer version' => [$assessment, $maps, $this->changelog('php8.5 (8.5.41-0ubuntu1) resolute-security; urgency=medium')],
            'package of another branch' => [$assessment, $maps, $this->changelog('php8.4 (8.5.4-0ubuntu1.5) resolute-security; urgency=medium')],
            'version not in the newest entry' => [$assessment, $maps, "\n" . $this->changelog()],
            'one CVE missing' => [$assessment, $maps, str_replace('CVE-2026-109619', 'CVE-2026-1096190', $this->changelog())],
            'fix reverted' => [$assessment, $maps, $this->changelog('php8.5 (8.5.4-0ubuntu1.6) resolute-security; urgency=medium', "    - debian/patches/CVE-2026-109619.patch: dropped, caused a regression\n")],
            'non-Debian web SAPI' => [$this->assess('8.5.4', 'litespeed'), $maps, $this->changelog()],
            'test web server' => [$this->assess('8.5.4', 'cli-server'), $maps, $this->changelog()],
            'CLI' => [$this->assess('8.5.4', 'cli'), $maps, $this->changelog()],
            'prerelease' => [$this->assess('8.5.4RC1', 'apache2handler'), $maps, $this->changelog()],
            'detection failed' => [PHPRuntimeSecurity::evaluate(null, null, null), $maps, $this->changelog()],
            'already a fixed release' => [$this->assess('8.5.12', 'apache2handler'), $maps, $this->changelog('php8.5 (8.5.12-1) resolute-security; urgency=medium')],
        ];
        foreach ($cases as $name => $case) {
            $this->assertSame($case[0], PHPRuntimeSecurity::evaluateDistributionEvidence(...$case), $name);
        }
    }

    public function testAProcessStartedBeforeThePackageUpdateMustBeRestarted()
    {
        $assessment = PHPRuntimeSecurity::evaluateDistributionEvidence($this->assess('8.5.4', 'apache2handler'), $this->maps('/usr/lib/apache2/modules/libphp8.5.so', true), $this->changelog());
        $this->assertSame('potentially_affected', $assessment['status']);
        $this->assertSame('restart_required', $assessment['reason']);
        $html = $this->render($assessment);
        foreach (['alert-warning', 'Potentially affected', 'php8.5 8.5.4-0ubuntu1.5', 'Restart Apache or PHP-FPM'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }

    public function testDistributionFilesAreReadOnlyFromTheExpectedLocalPaths()
    {
        $directory = sys_get_temp_dir() . '/avideo-runtime-doc-' . bin2hex(random_bytes(8));
        mkdir($directory . '/php8.5-common', 0700, true);
        try {
            $this->assertNull(PHPRuntimeSecurity::readDistributionChangelog('8.5', $directory));
            file_put_contents($directory . '/php8.5-common/changelog.Debian.gz', gzencode($this->changelog()));
            $this->assertSame($this->changelog(), PHPRuntimeSecurity::readDistributionChangelog('8.5', $directory . '/'));
            $this->assertNull(PHPRuntimeSecurity::readDistributionChangelog('8.4', $directory));
            $this->assertNull(PHPRuntimeSecurity::readProcessMaps($directory . '/missing-maps'));
        } finally {
            if (file_exists($directory . '/php8.5-common/changelog.Debian.gz')) { unlink($directory . '/php8.5-common/changelog.Debian.gz'); }
            rmdir($directory . '/php8.5-common');
            rmdir($directory);
        }
    }

    public function testOutputAndTranslationsAreEscapedExactlyOnce()
    {
        $assessment = PHPRuntimeSecurity::evaluate('8.2.34<script>alert("x")</script>', 80234, 'fpm-fcgi<img src=x>');
        $html = $this->render($assessment, ['translations' => ['PHP runtime security' => 'PHP "quoted" <img src=x> & translated']]);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&quot;quoted&quot;', $html);
        $this->assertStringNotContainsString('&amp;quot;', $html);
    }

    public function testNoticeStringsExistInTheMaintainedLocaleCatalogs()
    {
        require_once dirname(__DIR__, 2) . '/objects/LocaleTranslator.php';
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/PHPRuntimeSecurity.php');
        preg_match_all("/__\('([^']*)'/", $source, $matches);
        foreach (['en_US', 'pt_BR', 'fa_IR'] as $language) {
            $catalog = LocaleTranslator::readLanguage($language, dirname(__DIR__, 2) . '/locale/');
            $this->assertSame([], $catalog['errors']);
            foreach (array_unique($matches[1]) as $text) {
                $this->assertArrayHasKey($text, $catalog['entries'], $language . ': ' . $text);
                $this->assertNotSame('', $catalog['entries'][$text]);
            }
        }
    }

    public function testTheObservationHasNoSessionOrSharedResultCache()
    {
        $savedSession = $_SESSION ?? [];
        try {
            $_SESSION['sessionCache']['PHPRuntimeSecurity'] = $this->assess('8.2.35');
            $this->assertSame('meets_version_criteria', $this->assess('8.2.35')['status']);
            $this->assertSame('potentially_affected', $this->assess('8.2.34')['status']);
            $this->assertSame('unknown', PHPRuntimeSecurity::evaluate(null, null, null)['status']);
            $this->assertSame('potentially_affected', $this->assess('8.2.34')['status']);
        } finally {
            $_SESSION = $savedSession;
        }
        foreach ([[true, true, true], [true, false, false], [false, true, false], [false, false, false]] as $role) {
            $cache = $this->fixture(['action' => 'cache', 'logged' => $role[0], 'admin' => $role[1]]);
            $this->assertSame($role[2], $cache['skip']);
            $this->assertSame('', $cache['output']);
        }
    }

    public function testNonHtmlContentTypesAreExcluded()
    {
        foreach (['/objects/response.json.php', '/plugin/API/get.json.php', '/plugin/API/index.php', '/sample.json'] as $script) {
            $this->assertFalse(PHPRuntimeSecurity::isHTMLResponse([], $script));
            $this->assertSame('', $this->render($this->assess('8.2.34'), ['script' => $script]));
        }
        foreach (['application/json', 'application/problem+json; charset=utf-8', 'video/mp4', 'audio/mpeg', 'application/vnd.apple.mpegurl', 'text/plain', 'text/event-stream'] as $type) {
            $this->assertFalse(PHPRuntimeSecurity::isHTMLResponse(['Cache-Control: private', 'Content-Type: ' . $type]));
        }
        foreach (['text/html', 'text/html; charset=UTF-8', 'application/xhtml+xml'] as $type) {
            $this->assertTrue(PHPRuntimeSecurity::isHTMLResponse(['content-type: ' . $type]));
        }
    }

    public function testInfiniteScrollNavbarFragmentDoesNotGainANotice()
    {
        $html = $this->fixture(['action' => 'navbar-scroll', 'logged' => true, 'admin' => true]);
        $this->assertMatchesRegularExpression('/^<!-- navbar hidden line=\d+ -->$/', $html);
        $this->assertStringNotContainsString('CVE-', $html);
    }

    public function testRealWebProcessKeepsPublicApiMediaAndWebhookResponsesUnchanged()
    {
        $directory = sys_get_temp_dir() . '/avideo-runtime-test-' . bin2hex(random_bytes(8));
        mkdir($directory, 0700);
        $fixture = dirname(__DIR__) . '/fixtures/php-runtime-security.php';
        file_put_contents($directory . '/router.php', '<?php define("AVIDEO_RUNTIME_TEST_SERVER", true); require ' . var_export($fixture, true) . ';');
        $listener = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        $this->assertIsResource($listener, $error);
        $address = stream_socket_get_name($listener, false);
        fclose($listener);
        $process = proc_open([PHP_BINARY, '-S', $address, $directory . '/router.php'], [0 => ['pipe', 'r'], 1 => ['file', $directory . '/stdout', 'a'], 2 => ['file', $directory . '/stderr', 'a']], $pipes, $directory);
        try {
            $this->assertIsResource($process);
            fclose($pipes[0]);
            $ready = false;
            for ($attempt = 0; $attempt < 40; $attempt++) {
                $connection = @stream_socket_client('tcp://' . $address, $errno, $error, 0.1);
                if ($connection) { fclose($connection); $ready = true; break; }
                usleep(50000);
            }
            $this->assertTrue($ready, 'Isolated loopback PHP server did not start.');
            foreach ([['html', false, false], ['html', true, false], ['html', false, true], ['api', true, true], ['media', true, true], ['webhook', true, true], ['html', true, true]] as $request) {
                $query = http_build_query(['action' => 'response', 'format' => $request[0], 'logged' => (int) $request[1], 'admin' => (int) $request[2], 'show_passing' => 1]);
                $body = file_get_contents('http://' . $address . '/?' . $query, false, stream_context_create(['http' => ['timeout' => 3]]));
                if ($request === ['html', true, true]) {
                    $this->assertStringContainsString(PHP_VERSION, $body);
                    $this->assertStringContainsString('cli-server', $body, 'Use the actual HTTP process SAPI, not the test runner CLI.');
                    $this->assertStringContainsString('CVE-2026-109618', $body);
                    $this->assertStringContainsString('Cache-Control: private, no-store, max-age=0', implode("\n", $http_response_header));
                } else {
                    $this->assertSame($request[0] === 'api' ? '{"error":false,"msg":"unchanged"}' : 'UNCHANGED', $body);
                    $this->assertStringNotContainsString('PHP', $body);
                }
            }
        } finally {
            if (is_resource($process)) { proc_terminate($process); proc_close($process); }
            foreach (['router.php', 'stdout', 'stderr'] as $name) {
                if (file_exists($directory . '/' . $name)) { unlink($directory . '/' . $name); }
            }
            rmdir($directory);
        }
    }
}
