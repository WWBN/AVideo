<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/install/installer.php';

class InstallerTest extends TestCase
{
    private function form()
    {
        return ['databaseHost' => 'localhost', 'databasePort' => '3306', 'databaseName' => 'avideo-test',
            'databaseUser' => 'root', 'databasePass' => '', 'createTables' => 2,
            'webSiteRootURL' => 'https://example.com:8443/videos', 'webSiteTitle' => 'My videos',
            'contactEmail' => 'owner@example.com', 'mainLanguage' => 'en', 'systemAdminPass' => 'Test-only password'];
    }

    public function testCliDefaultsAndUrlNormalization()
    {
        $data = installerValidate($this->form());
        $this->assertSame('2', $data['createTables']);
        $this->assertSame('en_US', $data['mainLanguage']);
        $this->assertSame('https://example.com:8443/videos/', $data['webSiteRootURL']);
        $this->assertSame(installerRoot(), $data['systemRootPath']);
    }

    public function testDatabaseTestDoesNotRequireAdministratorFields()
    {
        $data = installerValidate(array_slice($this->form(), 0, 6, true), true);
        $this->assertArrayNotHasKey('systemAdminPass', $data);
        $this->assertSame('', $data['databasePass']);
    }

    public function testPasswordConfirmationIsValidated()
    {
        $data = $this->form();
        $data['confirmSystemAdminPass'] = 'different';
        $this->expectException(InstallerFailure::class);
        installerValidate($data);
    }

    public function testSiteTitleUsesCharacterLimit()
    {
        $data = $this->form();
        $data['webSiteTitle'] = str_repeat('é', 45);
        $this->assertSame($data['webSiteTitle'], installerValidate($data)['webSiteTitle']);
        $data['webSiteTitle'] .= 'é';
        $this->expectException(InstallerFailure::class);
        installerValidate($data);
    }

    public function testUrlDetectionPreservesPortAndSubdirectory()
    {
        $server = $_SERVER;
        try {
            $_SERVER['HTTPS'] = 'off';
            $_SERVER['HTTP_HOST'] = 'example.com:8080';
            $_SERVER['SCRIPT_NAME'] = '/avideo/install/index.php';
            $this->assertSame('http://example.com:8080/avideo/', installerURL());
        } finally { $_SERVER = $server; }
    }

    public function testSchemaTableCatalogMatchesInstallation()
    {
        $tables = installerSchemaTables();
        foreach (['users', 'categories', 'configurations', 'plugins', 'videos', 'rate_limits'] as $table) {
            $this->assertContains($table, $tables);
        }
        $this->assertSame($tables, array_values(array_unique($tables)));
    }
}
