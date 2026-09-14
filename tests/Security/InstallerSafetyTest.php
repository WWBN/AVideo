<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/install/installer.php';

class InstallerSafetyTest extends TestCase
{
    public function testGeneratedConfigurationPreservesLiteralCredentials()
    {
        $credential = "quote' slash\\ dollar\$ line\nend";
        $data = ['databaseHost' => 'localhost', 'databasePort' => '3306', 'databaseName' => 'avideo',
            'databaseUser' => 'test', 'databasePass' => $credential, 'salt' => $credential,
            'webSiteRootURL' => 'https://example.com:8443/subdir/'];
        $source = installerConfig($data);
        // Parse the generated PHP and execute only its assignments, never the app bootstrap.
        token_get_all($source, TOKEN_PARSE);
        $assignments = substr(explode('// Do not change the bootstrap below.', $source)[0], 5);
        $settings = (static function ($php) {
            $global = [];
            eval($php);
            return [$global, $mysqlPass];
        })($assignments);
        $this->assertSame($credential, $settings[1]);
        $this->assertSame($credential, $settings[0]['salt']);
        $this->assertSame('/subdir/', $settings[0]['webSiteRootPath']);
        $this->assertSame($data['webSiteRootURL'], $settings[0]['webSiteRootURL']);
    }

    public function testArrayFieldsAreRejectedWithoutTypeErrors()
    {
        $this->expectException(InstallerFailure::class);
        installerField(['install_csrf_token' => ['bad']], 'install_csrf_token');
    }

    public function testDatabaseIdentifiersCannotBecomeSql()
    {
        $this->expectException(InstallerFailure::class);
        installerValidate(['databaseHost' => 'localhost', 'databasePort' => '3306',
            'databaseUser' => 'test', 'databasePass' => '', 'databaseName' => 'test`; DROP DATABASE production; --'], true);
    }

    public function testConfigurationUrlCannotContainCredentials()
    {
        $this->expectException(InstallerFailure::class);
        installerValidateURL('https://user:password@example.com/', 'Site URL');
    }
}
