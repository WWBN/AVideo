<?php

namespace Tests\Unit;

use Tests\TestCase;

require_once APP_ROOT . '/install/updateStreamerURL-functions.php';

/**
 * install/updateStreamerURL-functions.php: what install/updateStreamerURL.php changes when the
 * site moves to another domain. Only the site address changes; e-mails, folders, other domains
 * and serialized values are kept, and configuration.php keeps every other byte.
 */
class UpdateStreamerURLTest extends TestCase
{
    private $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = str_replace('\\', '/', sys_get_temp_dir()) . '/avideo-url-change-' . bin2hex(random_bytes(6));
        mkdir($this->dir . '/sites', 0777, true);
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->dir);
        parent::tearDown();
    }

    private function map($certName = null, $old = 'https://old.com/', $new = 'https://new.net/')
    {
        return \siteURLChangeCreateMap($old, $new, $certName);
    }

    private function applySite($exitCode)
    {
        $file = $this->dir . '/sites/avideo.conf';
        file_put_contents($file, "<VirtualHost *:80>\n    ServerName old.com\n    DocumentRoot /var/www/old.com\n</VirtualHost>\n");
        $files = \siteURLChangeListConfigFiles([$this->dir . '/sites'], false);
        $plan = \siteURLChangePlanServerFiles($files, $this->map());
        $group = ['plan' => $plan, 'test' => [PHP_BINARY, '-r', "exit({$exitCode});"], 'reload' => null];
        ob_start();
        $result = \siteURLChangeApplyServerFiles('Apache', $group, $this->map(), $this->dir . '/backup');
        $output = ob_get_clean();
        return [$result, $output, realpath($file)];
    }

    public function testWebServerFilesAreRestoredWhenTheCheckFails()
    {
        [$result, $output, $file] = $this->applySite(1);
        $this->assertFalse($result);
        $this->assertStringContainsString('check failed', $output);
        $this->assertStringContainsString('ServerName old.com', file_get_contents($file));
        $this->assertSame([], glob(dirname($file) . '/.*.tmp-*'));
    }

    public function testWebServerFilesChangeWhenTheCheckPasses()
    {
        [$result, $output, $file] = $this->applySite(0);
        $this->assertTrue($result);
        $this->assertStringContainsString("ServerName new.net\n    DocumentRoot /var/www/old.com", file_get_contents($file));
        $copy = $this->dir . '/backup/files/' . ltrim(str_replace(':', '', $file), '/');
        $this->assertStringContainsString('ServerName old.com', file_get_contents($copy));
    }

    public function testInvalidConfigurationIsNotWritten()
    {
        $file = $this->dir . '/configuration.php';
        file_put_contents($file, "<?php\n\$global['webSiteRootURL'] = 'https://old.com/';\n");
        try {
            \siteURLChangePrepareFile($file, "<?php\n\$global['webSiteRootURL'] = 'https://new.net/'\n", true);
            $this->fail('A file that does not pass php -l must not be prepared');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('php -l', $e->getMessage());
        }
        $this->assertSame("<?php\n\$global['webSiteRootURL'] = 'https://old.com/';\n", file_get_contents($file));
        $this->assertSame([], glob($this->dir . '/.*.tmp-*'));

        $prepared = \siteURLChangePrepareFile($file, "<?php\n\$global['webSiteRootURL'] = 'https://new.net/';\n", true);
        \siteURLChangeCommitFile($prepared, $this->dir . '/backup');
        $this->assertStringContainsString('https://new.net/', file_get_contents($file));
    }

    public function testEveryPlainTextSettingWithTheAddressChanges()
    {
        $code = "<?php\n\$global['webSiteRootURL'] = 'https://old.com/';\n\$global['systemRootPath'] = '/var/www/old.com/';\n"
            . "\$global['logfile'] = '/var/www/old.com/videos/avideo.log';\n\$global['corsOrigin'] = 'https://old.com';\n"
            . "\$global['other'] = 'https://cdn.old.com/';\n\$mysqlHost = 'old.com';\n\$mysqlPass = 'https://old.com/';\n"
            . "\$global['list'] = ['https://old.com/'];\n// \$global['webSiteRootURL'] = 'https://old.com/';\n";
        $result = \siteURLChangeReplacePHPStringLiterals($code, $this->map());
        $this->assertSame(['global.webSiteRootURL', 'global.corsOrigin'], array_column($result['changes'], 'name'));
        $this->assertStringContainsString("\$global['corsOrigin'] = 'https://new.net';", $result['content']);
        $this->assertStringContainsString("\$global['systemRootPath'] = '/var/www/old.com/';", $result['content']);
        $this->assertStringContainsString("\$mysqlHost = 'old.com';", $result['content']);
        $this->assertStringContainsString("\$mysqlPass = 'https://old.com/';", $result['content']);
        // The array is not a plain text setting: reported (line 9), not changed; the commented line is ignored.
        $this->assertSame([8, 9], $result['remaining']);
    }

    public function testMentionsInOtherServerFiles()
    {
        $file = $this->dir . '/hosts';
        file_put_contents($file, "127.0.0.1 localhost\n127.0.0.1 old.com www.old.com\n10.0.0.2 cdn.old.com\n# old.com\n");
        $mentions = \siteURLChangeFindMentions([$file], $this->map());
        $this->assertSame(["{$file}:2" => '127.0.0.1 old.com www.old.com'], $mentions);
    }

    public function testGeneratedCacheFilesWithAddresses()
    {
        mkdir($this->dir . '/videos/permanentCache', 0777, true);
        mkdir($this->dir . '/videos/EPGCache', 0777, true);
        foreach (['permanentCache/getVideosPaths_v1_0.tmp', 'permanentCache/other.tmp', 'EPGCache/abc.cache'] as $name) {
            file_put_contents($this->dir . '/videos/' . $name, '{}');
        }
        $files = array_map('basename', \siteURLChangeListGeneratedCacheFiles($this->dir . '/videos/'));
        sort($files);
        $this->assertSame(['abc.cache', 'getVideosPaths_v1_0.tmp'], $files);
    }

    public function testHistoryAndPaymentTablesAreSkipped()
    {
        // Its restreamer URL is used again to check/stop running restreams.
        $this->assertFalse(\siteURLChangeIsSkippedTable('live_restreams_logs'));
        foreach (['CachesInDB', 'videos_statistics', 'audit', 'wallet_log', 'btc_invoices', 'PayPalYPT_checkout', 'live_transmitions_history', 'ip2location_db3', 'logs'] as $table) {
            $this->assertTrue(\siteURLChangeIsSkippedTable($table), $table);
        }
        foreach (['plugins', 'configurations', 'live_servers', 'videos', 'categories', 'users', 'MessageOfTheDay', 'login_control'] as $table) {
            $this->assertFalse(\siteURLChangeIsSkippedTable($table), $table);
        }
    }

    public function testNormalizesAndRejectsAddresses()
    {
        $this->assertSame('https://new.net/', \siteURLChangeNormalizeURL(' HTTPS://New.NET '));
        $this->assertSame('https://new.net:8443/AVideo/', \siteURLChangeNormalizeURL('https://new.net:8443/AVideo'));
        foreach (['new.net', 'ftp://new.net/', 'https://new.net/?a=1', 'https://user:pass@new.net/', 'https://new.net/a b/', "https://new.net/'/", 'https://new_net/', 'https://new.net/../', ''] as $url) {
            $this->assertFalse(\siteURLChangeNormalizeURL($url), $url);
        }
        $this->assertTrue(\siteURLChangeCanHaveCertificate('new.net'));
        $this->assertFalse(\siteURLChangeCanHaveCertificate('192.168.0.10'));
        $this->assertFalse(\siteURLChangeCanHaveCertificate('vlu.localhost'));
    }

    public function testReplacesPluginParameterAddresses()
    {
        $map = $this->map();
        $json = json_encode([
            'server' => 'rtmp://old.com/live',
            'playerServer' => 'https://old.com:8443/live',
            'stats' => 'http://old.com:8080/stat',
            'host' => 'old.com',
            'www' => 'https://www.old.com/page',
            'ftp' => 'ftp://user:secret@old.com/videos/',
            'encoded' => 'https://site.org/?u=' . rawurlencode('https://old.com/v/1'),
            'email' => 'admin@old.com',
            'folder' => '/var/www/old.com/videos/',
            'subdomain' => 'https://cdn.old.com/',
            'other' => 'https://old.com.br/ https://myold.com/ https://old-com.org/',
            'text' => 'Visit old.com today',
        ]);
        $result = json_decode(\siteURLChangeReplaceValue($json, $map), true);
        $this->assertSame('rtmp://new.net/live', $result['server']);
        $this->assertSame('https://new.net:8443/live', $result['playerServer']);
        $this->assertSame('http://new.net:8080/stat', $result['stats']);
        $this->assertSame('new.net', $result['host']);
        $this->assertSame('https://www.new.net/page', $result['www']);
        $this->assertSame('ftp://user:secret@new.net/videos/', $result['ftp']);
        $this->assertSame('https://site.org/?u=' . rawurlencode('https://new.net/v/1'), $result['encoded']);
        $this->assertSame('admin@old.com', $result['email']);
        $this->assertSame('/var/www/old.com/videos/', $result['folder']);
        $this->assertSame('https://cdn.old.com/', $result['subdomain']);
        $this->assertSame('https://old.com.br/ https://myold.com/ https://old-com.org/', $result['other']);
        $this->assertSame('Visit old.com today', $result['text']);
    }

    public function testWholeValueAndSchemeChange()
    {
        $this->assertSame('new.net', \siteURLChangeReplaceValue('old.com', $this->map()));
        $this->assertSame('www.new.net', \siteURLChangeReplaceValue('WWW.old.com', $this->map()));
        $this->assertSame('old.company', \siteURLChangeReplaceValue('old.company', $this->map()));
        // The exact site URL takes the new scheme; other URLs keep theirs.
        $map = $this->map(null, 'http://old.com/', 'https://new.net/');
        $this->assertSame('https://new.net/videos/a.mp4 http://new.net:8080/stat', \siteURLChangeReplaceValue('http://old.com/videos/a.mp4 http://old.com:8080/stat', $map));
        $this->assertSame('{"u":"https:\/\/new.net\/x"}', \siteURLChangeReplaceValue('{"u":"http:\/\/old.com\/x"}', $map));
    }

    public function testDoubleEscapedLiveParameters()
    {
        // Older Live plugin parameters are saved with the slashes escaped twice.
        $value = '{"server":"rtmp:\\\\/\\\\/old.com\\\\/live","playerServer":"https:\\\\/\\\\/old.com:8443\\\\/live","crt":"\\\\/etc\\\\/letsencrypt\\\\/live\\\\/old.com\\\\/fullchain.pem"}';
        $expected = '{"server":"rtmp:\\\\/\\\\/new.net\\\\/live","playerServer":"https:\\\\/\\\\/new.net:8443\\\\/live","crt":"\\\\/etc\\\\/letsencrypt\\\\/live\\\\/new.net\\\\/fullchain.pem"}';
        $this->assertSame($expected, \siteURLChangeReplaceValue($value, $this->map('new.net')));
        $map = $this->map(null, 'http://old.com/', 'https://new.net/');
        $this->assertSame('"https:\\\\/\\\\/new.net\\\\/x" https%3A%2F%2Fnew.net%2Fy', \siteURLChangeReplaceValue('"http:\\\\/\\\\/old.com\\\\/x" http%3A%2F%2Fold.com%2Fy', $map));
    }

    public function testNewDomainContainingTheOldOneIsReplacedOnce()
    {
        $map = $this->map(null, 'https://old.com/', 'https://tv.old.com/');
        $this->assertSame('https://tv.old.com/a rtmp://tv.old.com/live', \siteURLChangeReplaceValue('https://old.com/a rtmp://old.com/live', $map));
        $config = "ServerName old.com\nServerAlias www.old.com\n";
        $this->assertSame("ServerName tv.old.com\nServerAlias www.tv.old.com\n", \siteURLChangeReplaceServerConfig($config, $map));
    }

    public function testCertificatePathsChangeOnlyWithTheNewCertificate()
    {
        $value = json_encode(['server_crt_file' => '/etc/letsencrypt/live/old.com/fullchain.pem', 'server_key_file' => '/etc/letsencrypt/live/old.com-0001/privkey.pem']);
        $this->assertSame($value, \siteURLChangeReplaceValue($value, $this->map()));
        $result = json_decode(\siteURLChangeReplaceValue($value, $this->map('new.net')), true);
        $this->assertSame('/etc/letsencrypt/live/new.net/fullchain.pem', $result['server_crt_file']);
        $this->assertSame('/etc/letsencrypt/live/new.net/privkey.pem', $result['server_key_file']);
    }

    public function testSerializedValuesAreNotChanged()
    {
        $value = serialize(['url' => 'https://old.com/']);
        $this->assertTrue(\siteURLChangeIsSerialized($value));
        $this->assertSame($value, \siteURLChangeReplaceValue($value, $this->map()));
    }

    public function testApacheSiteKeepsFoldersAndChangesNames()
    {
        $config = implode("\n", [
            '<VirtualHost *:443>',
            '    ServerName old.com',
            '    ServerAlias www.old.com cdn.old.com',
            '    DocumentRoot /var/www/old.com/AVideo',
            '    <Directory /var/www/old.com/AVideo>',
            '    ServerAdmin admin@old.com',
            '    RewriteCond %{SERVER_NAME} =old.com [OR]',
            '    Redirect permanent /x https://old.com/y',
            '    SSLCertificateFile /etc/letsencrypt/live/old.com/fullchain.pem',
            '</VirtualHost>',
        ]);
        $withoutCertificate = \siteURLChangeReplaceServerConfig($config, $this->map());
        $this->assertStringContainsString('ServerName new.net', $withoutCertificate);
        $this->assertStringContainsString('ServerAlias www.new.net cdn.old.com', $withoutCertificate);
        $this->assertStringContainsString('DocumentRoot /var/www/old.com/AVideo', $withoutCertificate);
        $this->assertStringContainsString('<Directory /var/www/old.com/AVideo>', $withoutCertificate);
        $this->assertStringContainsString('ServerAdmin admin@old.com', $withoutCertificate);
        $this->assertStringContainsString('RewriteCond %{SERVER_NAME} =new.net [OR]', $withoutCertificate);
        $this->assertStringContainsString('Redirect permanent /x https://new.net/y', $withoutCertificate);
        $this->assertStringContainsString('/etc/letsencrypt/live/old.com/fullchain.pem', $withoutCertificate);
        $withCertificate = \siteURLChangeReplaceServerConfig($withoutCertificate, $this->map('new.net'));
        $this->assertStringContainsString('SSLCertificateFile /etc/letsencrypt/live/new.net/fullchain.pem', $withCertificate);
        $this->assertCount(4, \siteURLChangeChangedLines($config, $withoutCertificate));
    }

    public function testNginxKeepsHttpCallbacksAndLineEndings()
    {
        $config = "rtmp {\r\n  on_publish http://old.com/plugin/Live/on_publish.php;\r\n}\r\nserver {\r\n  server_name old.com www.old.com;\r\n  ssl_certificate /etc/letsencrypt/live/old.com/fullchain.pem;\r\n  root /home/old.com/www;\r\n}\r\n";
        $result = \siteURLChangeReplaceServerConfig($config, $this->map('new.net'));
        $this->assertSame("rtmp {\r\n  on_publish http://new.net/plugin/Live/on_publish.php;\r\n}\r\nserver {\r\n  server_name new.net www.new.net;\r\n  ssl_certificate /etc/letsencrypt/live/new.net/fullchain.pem;\r\n  root /home/old.com/www;\r\n}\r\n", $result);
    }

    public function testEncoderSiteMatchingAndAllowedList()
    {
        $this->assertTrue(\siteURLChangeIsSameSite('http://OLD.com', 'https://old.com/'));
        $this->assertFalse(\siteURLChangeIsSameSite('https://old.com/other/', 'https://old.com/'));
        $this->assertFalse(\siteURLChangeIsSameSite('', 'https://old.com/'));
        $list = "https://other.org/\nhttps://old.com/\nhttp://old.com";
        $this->assertSame("https://other.org/\nhttps://new.net/", \siteURLChangeReplaceAllowedStreamers($list, 'https://old.com/', 'https://new.net/'));
        $this->assertSame("https://other.org/\r\nhttps://new.net/", \siteURLChangeReplaceAllowedStreamers("https://other.org/\r\nhttps://new.net/\r\nhttps://old.com/", 'https://old.com/', 'https://new.net/'));
        $this->assertSame("https://other.org/", \siteURLChangeReplaceAllowedStreamers('https://other.org/', 'https://old.com/', 'https://new.net/'));
        $this->assertSame('', \siteURLChangeReplaceAllowedStreamers('', 'https://old.com/', 'https://new.net/'));
    }

    public function testConfigurationFileChangesOnlyTheAddress()
    {
        $code = "<?php\n\$global['configurationVersion'] = 3.1;\n\$global['webSiteRootURL'] = 'https://old.com/';\n"
            . "\$global['systemRootPath'] = '/var/www/old.com/';\n\$mysqlPass = 'p\\'a\$s\\\\';\n\$mysqlPort = 3306;\n"
            . "// \$global['webSiteRootURL'] = 'https://commented.com/';\n"
            . "require_once \$global['systemRootPath'] . 'objects/include_config.php';\n";
        $assignments = \siteURLChangeReadPHPAssignments($code);
        $this->assertSame('https://old.com/', $assignments['global.webSiteRootURL']['value']);
        $this->assertSame("p'a\$s\\", $assignments['mysqlPass']['value']);
        $this->assertSame(3306, $assignments['mysqlPort']['value']);

        $new = \siteURLChangeSetPHPAssignments($code, ['global.webSiteRootURL' => "https://new.net/"]);
        $this->assertSame(str_replace("'https://old.com/'", "'https://new.net/'", $code), $new);
    }

    public function testOldInstallerBlockKeepsTheRuntimeAddress()
    {
        // Written by older installers: the expression must stay as it is, only the text changes.
        $code = "<?php\nif(false && !empty(\$_SERVER['SERVER_NAME'])) {\n"
            . "    \$global['webSiteRootURL'] = \"http\".(!empty(\$_SERVER['HTTPS'])?\"s\":\"\").\"://\".\$_SERVER['SERVER_NAME'].\$subDir;\n"
            . "}else{\n    \$global['webSiteRootURL'] = 'https://old.com/';\n}\n";
        $this->assertSame(1, \siteURLChangeCountExpressionAssignments($code, 'global.webSiteRootURL'));
        $new = \siteURLChangeSetPHPAssignments($code, ['global.webSiteRootURL' => 'https://new.net/']);
        $this->assertSame(str_replace("'https://old.com/'", "'https://new.net/'", $code), $new);
    }

    public function testInvalidJSONIsDetected()
    {
        $this->assertTrue(\siteURLChangeKeepsJSONValid('{"a":"https://old.com/"}', '{"a":"https://new.net/"}'));
        $this->assertFalse(\siteURLChangeKeepsJSONValid('{"a":"x"}', '{"a":"x}'));
        $this->assertTrue(\siteURLChangeKeepsJSONValid('{"a":broken', '{"a":broken2'));
        $this->assertTrue(\siteURLChangeKeepsJSONValid('plain text', 'plain'));
    }

    public function testConfigurationFileWithAnExpressionIsNotChanged()
    {
        $code = "<?php\n\$global['webSiteRootURL'] = getenv('SITE_URL');\n";
        $this->assertFalse(\siteURLChangeReadPHPAssignments($code)['global.webSiteRootURL']['literal']);
        $this->expectException(\RuntimeException::class);
        \siteURLChangeSetPHPAssignments($code, ['global.webSiteRootURL' => 'https://new.net/']);
    }

    public function testConfigurationFileWithDoubleQuotesIsChanged()
    {
        $code = "<?php\n\$global[\"webSiteRootURL\"] = \"https://old.com/\";\n";
        $this->assertSame("<?php\n\$global[\"webSiteRootURL\"] = 'https://new.net/';\n", \siteURLChangeSetPHPAssignments($code, ['global.webSiteRootURL' => 'https://new.net/']));
    }
}
