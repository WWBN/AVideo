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
        foreach (['users', 'categories', 'configurations', 'plugins', 'videos', 'rate_limits', 'playback_access_rules'] as $table) {
            $this->assertContains($table, $tables);
        }
        $this->assertSame($tables, array_values(array_unique($tables)));
    }

    public function testFreshInstallerVersionMatchesLatestCoreMigration()
    {
        $versions = array_map(function ($file) {
            return substr(basename($file), strlen('updateDb.v'), -strlen('.sql'));
        }, glob(dirname(__DIR__, 2) . '/updatedb/updateDb.v*.sql'));
        usort($versions, 'version_compare');
        $latest = end($versions);
        $entryPoint = file_get_contents(dirname(__DIR__, 2) . '/install/checkConfiguration.php');
        $this->assertSame(1, preg_match('/\$schemaVersion\s*=\s*\'([^\']+)\'/', $entryPoint, $matches));
        $this->assertSame($latest, $matches[1]);
        $parameters = (new ReflectionFunction('installerSeed'))->getParameters();
        $this->assertSame($latest, $parameters[2]->getDefaultValue());
    }

    public function testPlaybackSchemaMatchesUpgradeMigration()
    {
        $pattern = '/CREATE TABLE IF NOT EXISTS `playback_access_rules`\s*\(.*?;/s';
        $definitions = [];
        foreach (['install/database.sql', 'updatedb/updateDb.v30.3.sql'] as $path) {
            $sql = file_get_contents(dirname(__DIR__, 2) . '/' . $path);
            $this->assertSame(1, preg_match($pattern, $sql, $matches));
            $definitions[] = preg_replace('/\s+/', '', $matches[0]);
        }
        $this->assertSame($definitions[0], $definitions[1]);
    }
}
