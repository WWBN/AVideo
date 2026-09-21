<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/InstallerTestEnvironment.php';

/** @group installer-integration */
class InstallerIntegrationTest extends TestCase
{
    private $fixture;
    private $connection;
    private $client;
    private $url;
    private $databases = [];
    private $prefix;
    private $host;
    private $port;
    private $user;
    private $password;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('AVIDEO_INSTALLER_MYSQL_TESTS') !== '1') {
            $this->markTestSkipped('Set AVIDEO_INSTALLER_MYSQL_TESTS=1 to use a disposable test MySQL/MariaDB server.');
        }
        foreach (['mysqli', 'curl'] as $extension) {
            $this->assertTrue(extension_loaded($extension), 'Enable PHP ' . $extension . ' for integration tests.');
        }
        $this->assertTrue(function_exists('proc_open'), 'PHP proc_open is required.');
        $this->host = getenv('AVIDEO_TEST_DB_HOST') ?: '127.0.0.1';
        $this->port = (int) (getenv('AVIDEO_TEST_DB_PORT') ?: 3306);
        $this->user = getenv('AVIDEO_TEST_DB_USER') ?: 'root';
        $this->password = getenv('AVIDEO_TEST_DB_PASSWORD') ?: '';
        $this->prefix = 'avideo_phpunit_' . bin2hex(random_bytes(8));
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->connection = new mysqli($this->host, $this->user, $this->password, '', $this->port);
        $this->connection->set_charset('utf8mb4');
        $source = dirname(__DIR__, 2);
        $this->fixture = new InstallerTestEnvironment($source);
        $this->fixture->prepareStreamer($source);
        $check = $this->fixture->run(['-r', 'require "install/installer.php"; echo json_encode(installerChecks());']);
        $this->assertSame(0, $check['exit'], $check['stderr']);
        foreach (json_decode($check['stdout'], true, 512, JSON_THROW_ON_ERROR) as $requirement) {
            $this->assertTrue($requirement['ok'], $requirement['label'] . ': configure the test PHP runtime with AVIDEO_TEST_PHP_ARGS.');
        }
        $this->url = $this->fixture->startServer();
        $this->client = curl_init();
        curl_setopt_array($this->client, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => '', CURLOPT_CONNECTTIMEOUT => 1, CURLOPT_TIMEOUT => 20, CURLOPT_PROXY => '']);
        for ($attempt = 0; $attempt < 50; ++$attempt) {
            curl_setopt($this->client, CURLOPT_URL, $this->url);
            if (curl_exec($this->client) !== false) { return; }
            usleep(100000);
        }
        $this->fail('The test PHP server did not start.');
    }

    protected function tearDown(): void
    {
        try {
            if ($this->client) { curl_close($this->client); }
            if ($this->fixture) { $this->fixture->cleanup(); }
        } finally {
            if ($this->connection) {
                foreach ($this->databases as $database) {
                    // Only generated names from this test can reach destructive cleanup.
                    if (!preg_match('/^' . preg_quote($this->prefix, '/') . '_[0-9]+$/D', $database)) {
                        throw new RuntimeException('Refusing to drop an unrelated database.');
                    }
                    $this->connection->query('DROP DATABASE IF EXISTS `' . $database . '`');
                }
                $this->connection->close();
            }
            parent::tearDown();
        }
    }

    private function request($data = null)
    {
        curl_setopt($this->client, CURLOPT_URL, $this->url . ($data === null ? 'index.php' : 'checkConfiguration.php'));
        if ($data === null) { curl_setopt($this->client, CURLOPT_HTTPGET, true); }
        else { curl_setopt($this->client, CURLOPT_POSTFIELDS, http_build_query($data)); }
        $body = curl_exec($this->client);
        $this->assertNotFalse($body, 'The test HTTP request failed.');
        return $data === null ? $body : json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    private function payload()
    {
        $this->assertSame(1, preg_match('/name="install_csrf_token" value="([^"]+)"/', $this->request(), $matches));
        $name = $this->prefix . '_' . count($this->databases);
        $this->databases[] = $name;
        return ['install_csrf_token' => $matches[1], 'databaseHost' => $this->host, 'databasePort' => (string) $this->port,
            'databaseName' => $name, 'databaseUser' => $this->user, 'databasePass' => $this->password, 'createTables' => '2',
            'webSiteRootURL' => 'https://example.test:8443/video/', 'webSiteTitle' => "Video's title", 'mainLanguage' => 'en_US',
            'contactEmail' => 'owner@example.test', 'systemAdminPass' => "qa'Password\\special", 'confirmSystemAdminPass' => "qa'Password\\special"];
    }

    private function databaseExists($name)
    {
        $statement = $this->connection->prepare('SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?');
        $statement->bind_param('s', $name);
        $statement->execute();
        $statement->bind_result($count);
        $statement->fetch();
        $statement->close();
        return (int) $count > 0;
    }

    private function userCount($database)
    {
        $this->assertContains($database, $this->databases);
        return (int) $this->connection->query('SELECT COUNT(*) FROM `' . $database . '`.users')->fetch_row()[0];
    }

    private function createDatabase($name, $import = false)
    {
        $this->assertContains($name, $this->databases);
        $this->connection->query('CREATE DATABASE `' . $name . '`');
        if (!$import) { return; }
        $this->connection->select_db($name);
        $this->connection->multi_query(file_get_contents($this->fixture->path('install/database.sql')));
        do {
            $result = $this->connection->store_result();
            if ($result) { $result->free(); }
        } while ($this->connection->more_results() && $this->connection->next_result());
    }

    public function testValidationAndConnectionChecksDoNotCreateData()
    {
        $data = $this->payload();
        $this->assertNotEmpty($this->request([])['error']);
        foreach (['databasePort' => '0', 'contactEmail' => 'bad', 'confirmSystemAdminPass' => 'wrong'] as $key => $value) {
            $this->assertNotEmpty($this->request(array_replace($data, [$key => $value]))['error']);
        }
        $this->assertFalse($this->request(array_replace($data, ['action' => 'test']))['error']);
        $this->assertFalse($this->databaseExists($data['databaseName']));
        $this->assertFileDoesNotExist($this->fixture->path('videos/configuration.php'));
        $result = $this->request(array_replace($data, ['databaseHost' => '127.0.0.1', 'databasePort' => '1']));
        $this->assertNotEmpty($result['error']);
        $this->assertSame('connection', $result['stage']);
    }

    public function testInstallationLockPreventsConcurrentWrites()
    {
        $data = $this->payload();
        $lock = fopen($this->fixture->path('videos/.installer.lock'), 'c');
        $this->assertTrue(flock($lock, LOCK_EX));
        try {
            $result = $this->request($data);
            $this->assertNotEmpty($result['error']);
            $this->assertSame('configuration', $result['stage']);
            $this->assertFalse($this->databaseExists($data['databaseName']));
        } finally { flock($lock, LOCK_UN); fclose($lock); }
    }

    public function testCompleteInstallationLocksSetupAndPreservesPopulatedDatabase()
    {
        $data = $this->payload();
        $this->assertFalse($this->request($data)['error']);
        $this->assertSame(1, $this->userCount($data['databaseName']));
        $this->assertSame(1, (int) $this->connection->query('SELECT COUNT(*) FROM `' . $data['databaseName'] . '`.plugins')->fetch_row()[0]);
        $path = $this->fixture->path('videos/configuration.php');
        $this->assertSame(0, $this->fixture->run(['-l', $path])['exit']);
        $configuration = file_get_contents($path);
        $this->assertNotEmpty($this->request($data)['error']);
        $this->assertSame($configuration, file_get_contents($path));
        $page = $this->request();
        foreach ([$this->fixture->root, 'install_csrf_token', 'Ubuntu setup help', 'configurationForm', 'php.ini'] as $text) {
            $this->assertStringNotContainsString($text, $page);
        }
        $this->assertSame([], array_diff(array_keys($this->request([])), ['error', 'msg', 'success']));
        unlink($path);
        $this->assertNotEmpty($this->request($data)['error']);
        $this->assertSame(1, $this->userCount($data['databaseName']));
    }

    /** @dataProvider installationModes */
    public function testExistingEmptyDatabaseModes($mode)
    {
        $data = $this->payload();
        $data['createTables'] = $mode;
        $this->createDatabase($data['databaseName'], $mode === '0');
        $this->assertFalse($this->request($data)['error']);
        $this->assertSame(1, $this->userCount($data['databaseName']));
    }

    public function installationModes(): array { return [['1'], ['0']]; }

    public function testSeedFailureRollsBackAndCanBeRetried()
    {
        $data = $this->payload();
        $data['createTables'] = '0';
        $this->createDatabase($data['databaseName'], true);
        $this->connection->query("CREATE TRIGGER qa_failure BEFORE INSERT ON configurations FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test failure'");
        $this->assertNotEmpty($this->request($data)['error']);
        $this->assertSame(0, $this->userCount($data['databaseName']));
        $this->assertFileDoesNotExist($this->fixture->path('videos/configuration.php'));
        $this->connection->query('DROP TRIGGER qa_failure');
        $this->assertFalse($this->request($data)['error']);
        $this->assertSame(1, $this->userCount($data['databaseName']));
    }

    public function testSchemaImportFailureCanBeRetried()
    {
        $data = $this->payload();
        $schema = file_get_contents($this->fixture->path('install/database.sql'));
        $this->fixture->write('install/database.sql', str_replace('CREATE TABLE IF NOT EXISTS `categories`', "BROKEN SQL;\nCREATE TABLE IF NOT EXISTS `categories`", $schema));
        try {
            $result = $this->request($data);
            $this->assertNotEmpty($result['error']);
            $this->assertSame('tables', $result['stage']);
            $this->assertFileDoesNotExist($this->fixture->path('videos/configuration.php'));
        } finally { $this->fixture->write('install/database.sql', $schema); }
        $this->assertFalse($this->request($data)['error']);
    }

    public function testCliEndpointAcceptsIntegerModeAndLegacyLanguage()
    {
        $data = $this->payload();
        $data['createTables'] = 2;
        $data['mainLanguage'] = 'en';
        unset($data['install_csrf_token']);
        $result = $this->fixture->run(['-r', '$_POST=json_decode(stream_get_contents(STDIN),true); require $argv[1]; exit(!empty($installerSucceeded) ? 0 : 1);', $this->fixture->path('install/checkConfiguration.php')], json_encode($data));
        $this->assertSame(0, $result['exit']);
        $this->assertTrue(json_decode($result['stdout'], true, 512, JSON_THROW_ON_ERROR)['success']);
    }

    public function testEnvironmentCliUsesIndependentWorkingDirectoryAndOptionalDatabasePassword()
    {
        $data = $this->payload();
        $this->createDatabase($data['databaseName']);
        $environment = ['SYSTEM_ADMIN_PASSWORD' => $data['systemAdminPass'], 'DB_MYSQL_HOST' => $this->host,
            'DB_MYSQL_PORT' => $this->port, 'DB_MYSQL_NAME' => $data['databaseName'], 'DB_MYSQL_USER' => $this->user,
            'DB_MYSQL_PASSWORD' => $this->password !== '' ? $this->password : null, 'CONTACT_EMAIL' => $data['contactEmail'],
            'WEBSITE_TITLE' => $data['webSiteTitle'], 'MAIN_LANGUAGE' => $data['mainLanguage'], 'SERVER_NAME' => 'example.test'];
        $result = $this->fixture->run([$this->fixture->path('install/cli.php')], '', $environment);
        $this->assertSame(0, $result['exit']);
        $this->assertFileExists($this->fixture->path('videos/configuration.php'));
        $this->assertStringNotContainsString($data['systemAdminPass'], $result['stdout'] . $result['stderr']);
    }
}
