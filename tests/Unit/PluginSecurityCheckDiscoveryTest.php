<?php

namespace Tests\PluginSecurityCheckDiscovery;

use PHPUnit\Framework\TestCase;

class Fixture { public static $root; }
$securityDiscoverySource = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsSecurity.php');
preg_match('/function includeSecurityChecks\(\)\s*\{.*?\n\}/s', $securityDiscoverySource, $securityDiscoveryFunction);
$securityDiscoveryCode = str_replace('__DIR__', 'Fixture::$root . \'/objects\'', $securityDiscoveryFunction[0]);
eval('namespace ' . __NAMESPACE__ . '; ' . $securityDiscoveryCode);

class PluginSecurityCheckDiscoveryTest extends TestCase
{
    public function testDiscoversNormalAndHiddenPluginsAndIgnoresOrdinaryFiles()
    {
        Fixture::$root = sys_get_temp_dir() . '/avideo-security-discovery-' . bin2hex(random_bytes(8));
        mkdir(Fixture::$root . '/objects', 0777, true);
        mkdir(Fixture::$root . '/plugin/Normal', 0777, true);
        mkdir(Fixture::$root . '/plugin/.hidden');
        mkdir(Fixture::$root . '/plugin/WithoutCheck');
        file_put_contents(Fixture::$root . '/plugin/ordinary.txt', 'fixture');
        file_put_contents(Fixture::$root . '/plugin/Normal/securityCheck.php', '<?php $GLOBALS[\'securityChecksExecuted\'][] = \'normal\';');
        file_put_contents(Fixture::$root . '/plugin/.hidden/securityCheck.php', '<?php $GLOBALS[\'securityChecksExecuted\'][] = \'hidden\';');
        $previousCalls = $GLOBALS['securityChecksExecuted'] ?? null;
        $previousStandalone = $GLOBALS['isStandAlone'] ?? null;
        $GLOBALS['securityChecksExecuted'] = [];
        $GLOBALS['isStandAlone'] = false;
        try {
            includeSecurityChecks();
            $this->assertSame(['hidden', 'normal'], $GLOBALS['securityChecksExecuted']);
            $GLOBALS['securityChecksExecuted'] = [];
            $GLOBALS['isStandAlone'] = true;
            $this->assertFalse(includeSecurityChecks());
            $this->assertSame([], $GLOBALS['securityChecksExecuted']);
        } finally {
            $GLOBALS['securityChecksExecuted'] = $previousCalls;
            $GLOBALS['isStandAlone'] = $previousStandalone;
            unlink(Fixture::$root . '/plugin/Normal/securityCheck.php');
            unlink(Fixture::$root . '/plugin/.hidden/securityCheck.php');
            unlink(Fixture::$root . '/plugin/ordinary.txt');
            rmdir(Fixture::$root . '/plugin/Normal');
            rmdir(Fixture::$root . '/plugin/.hidden');
            rmdir(Fixture::$root . '/plugin/WithoutCheck');
            rmdir(Fixture::$root . '/plugin');
            rmdir(Fixture::$root . '/objects');
            rmdir(Fixture::$root);
        }
    }
}
