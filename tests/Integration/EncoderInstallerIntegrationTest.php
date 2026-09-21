<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/InstallerTestEnvironment.php';

/** @group installer-integration */
class EncoderInstallerIntegrationTest extends TestCase
{
    private $fixture;
    private $connection;
    private $client;
    private $url;
    private $mockStreamerUrl;
    private $databases = [];
    private $prefix;
    private $host;
    private $port;
    private $user;
    private $password;
    private $adminPassword = "test'password\\safe";
    private $adminHash;

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
        $this->prefix = 'avideo_encoder_phpunit_' . bin2hex(random_bytes(8));
        $this->adminHash = md5(hash('whirlpool', sha1($this->adminPassword)));
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->connection = new mysqli($this->host, $this->user, $this->password, '', $this->port);
        $this->connection->set_charset('utf8mb4');
        $source = dirname(__DIR__, 2) . '/.compose/encoder';
        $this->fixture = new InstallerTestEnvironment($source);
        $this->fixture->prepareEncoder();
        $this->fixture->write('mock-streamer.php', "<?php header('Content-Type: application/json'); parse_str(file_get_contents('php://input'), \$data); \$hash = trim(file_get_contents(__DIR__ . '/streamer-hash.txt')); echo json_encode(['isAdmin' => (isset(\$data['pass']) && \$data['pass'] === \$hash && isset(\$data['encodedPass']) && \$data['encodedPass'] === 'true')]);");
        $this->fixture->write('streamer-hash.txt', $this->adminHash);
        $this->mockStreamerUrl = $this->fixture->startRouter('mock-streamer.php');
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
        $page = $this->request();
        $this->assertStringContainsString('Ubuntu setup help', $page);
        $this->assertStringContainsString('assets/logo.png', $page);
        $this->assertSame(1, preg_match('/name="install_csrf_token" value="([^"]+)"/', $page, $matches));
        $name = $this->prefix . '_' . count($this->databases);
        $this->databases[] = $name;
        return ['install_csrf_token' => $matches[1], 'databaseHost' => $this->host, 'databasePort' => (string) $this->port,
            'databaseName' => $name, 'databaseUser' => $this->user, 'databasePass' => $this->password,
            'tablesPrefix' => 'qa_', 'createTables' => '2',
            'webSiteRootURL' => 'https://encoder.example.test:8443/sub/', 'siteURL' => $this->mockStreamerUrl,
            'inputUser' => 'admin', 'inputPassword' => $this->adminPassword,
            'allowedStreamers' => "https://one.example.test/\nhttps://two.example.test/", 'defaultPriority' => '3'];
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

    private function createDatabase($name)
    {
        $this->assertContains($name, $this->databases);
        $this->connection->query('CREATE DATABASE `' . $name . '`');
    }

    private function importSchema($name)
    {
        $this->connection->select_db($name);
        $this->connection->multi_query(file_get_contents($this->fixture->path('install/database.sql')));
        do {
            $result = $this->connection->store_result();
            if ($result) { $result->free(); }
        } while ($this->connection->more_results() && $this->connection->next_result());
        $this->connection->select_db('information_schema');
    }

    public function testValidationCsrfAdministratorVerificationAndCredentialRedaction()
    {
        $data = $this->payload();
        $config = $this->fixture->path('videos/configuration.php');
        foreach ([
            ['install_csrf_token' => 'bad'],
            ['tablesPrefix' => 'bad`'],
            ['defaultPriority' => '11'],
            ['databasePort' => '0'],
            ['inputPassword' => 'wrong'],
            ['allowedStreamers' => 'javascript:bad'],
        ] as $change) {
            $bad = array_replace($data, $change);
            $result = $this->request($bad);
            $this->assertNotEmpty($result['error'], json_encode($result));
            $this->assertFileDoesNotExist($config);
            $raw = json_encode($result);
            $this->assertStringNotContainsString($this->adminPassword, $raw);
            $this->assertStringNotContainsString($this->adminHash, $raw);
        }
    }

    public function testConnectionTestMakesNoDatabaseOrFileChanges()
    {
        $data = $this->payload();
        $result = $this->request(array_replace($data, ['action' => 'test']));
        $this->assertFalse($result['error'], json_encode($result));
        $this->assertFalse($this->databaseExists($data['databaseName']));
        $this->assertFileDoesNotExist($this->fixture->path('videos/configuration.php'));
    }

    public function testCompleteInstallationFormatsPrefixAndNondefaultPort()
    {
        $data = $this->payload();
        $result = $this->request($data);
        $this->assertTrue(!empty($result['installed']), json_encode($result));
        $this->connection->select_db($data['databaseName']);
        $row = $this->connection->query('SELECT pass FROM ' . $data['tablesPrefix'] . 'streamers')->fetch_row();
        $this->assertSame($this->adminHash, $row[0]);
        $formats = (int) $this->connection->query('SELECT COUNT(*) FROM ' . $data['tablesPrefix'] . 'formats')->fetch_row()[0];
        $this->assertSame(35, $formats);
        $priority = (int) $this->connection->query('SELECT defaultPriority FROM ' . $data['tablesPrefix'] . 'configurations_encoder')->fetch_row()[0];
        $this->assertSame(3, $priority);
        $path = $this->fixture->path('videos/configuration.php');
        $this->assertSame(0, $this->fixture->run(['-l', $path])['exit']);
        $configuration = file_get_contents($path);
        $runtime = $this->fixture->run([$path]);
        $this->assertSame(0, $runtime['exit'], $runtime['stderr']);
        $this->assertStringContainsString('TCP/IP', $runtime['stdout']);
        $second = $this->request($data);
        $this->assertNotEmpty($second['error']);
        $this->assertSame($configuration, file_get_contents($path));
    }

    public function testInstalledPagesDiscloseNoSetupDetails()
    {
        $data = $this->payload();
        $this->assertTrue(!empty($this->request($data)['installed']));
        $config = $this->fixture->path('videos/configuration.php');
        file_put_contents($config, '<?php throw new Exception("secret sentinel");');
        $page = $this->request();
        foreach (['secret sentinel', $this->fixture->root, 'install_csrf_token', 'php.ini', 'Ubuntu setup help', 'configurationForm'] as $secret) {
            $this->assertStringNotContainsString($secret, $page, $secret);
        }
        $this->assertStringContainsString('Installation complete.', $page);
        $empty = $this->request([]);
        $this->assertSame([], array_diff(array_keys($empty), ['error', 'msg', 'success']));
        unlink($config);
    }

    public function testMultiplePrefixesCoexistWithUnrelatedData()
    {
        $data = $this->payload();
        $this->assertTrue(!empty($this->request($data)['installed']));
        $config = $this->fixture->path('videos/configuration.php');
        $this->connection->select_db($data['databaseName']);
        $this->connection->query('CREATE TABLE unrelated (id INT)');
        $this->connection->query('INSERT INTO unrelated VALUES (7)');
        $second = $this->payload();
        $second['databaseName'] = $data['databaseName'];
        $second['tablesPrefix'] = 'second_';
        $result = $this->request($second);
        $this->assertTrue(!empty($result['installed']), json_encode($result));
        $this->connection->select_db($data['databaseName']);
        $this->assertSame('7', (string) $this->connection->query('SELECT id FROM unrelated')->fetch_row()[0]);
        $this->assertSame(1, (int) $this->connection->query('SELECT COUNT(*) FROM qa_streamers')->fetch_row()[0]);
        unlink($config);
        $again = $this->request($second);
        $this->assertNotEmpty($again['error']);
    }

    public function testSeedFailureRollsBackAndCanBeRetried()
    {
        $data = $this->payload();
        $this->createDatabase($data['databaseName']);
        $this->importSchema($data['databaseName']);
        $this->connection->select_db($data['databaseName']);
        $this->connection->query("CREATE TRIGGER qa_failure BEFORE INSERT ON qa_configurations_encoder FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test failure'");
        $data['createTables'] = '0';
        $result = $this->request($data);
        $this->assertNotEmpty($result['error'], json_encode($result));
        $this->assertSame('records', $result['stage']);
        $this->assertFileDoesNotExist($this->fixture->path('videos/configuration.php'));
        $this->connection->select_db($data['databaseName']);
        $this->assertSame(0, (int) $this->connection->query('SELECT COUNT(*) FROM qa_streamers')->fetch_row()[0]);
        $this->assertSame(0, (int) $this->connection->query('SELECT COUNT(*) FROM qa_formats')->fetch_row()[0]);
        $this->connection->query('DROP TRIGGER qa_failure');
        $result = $this->request($data);
        $this->assertTrue(!empty($result['installed']), json_encode($result));
    }

    public function testSchemaImportFailureStopsBeforeConfigurationPublication()
    {
        $data = $this->payload();
        $schema = file_get_contents($this->fixture->path('install/database.sql'));
        $broken = str_replace('`name` VARCHAR(45) NOT NULL', '`name` INVALID_TYPE NOT NULL', $schema);
        $this->fixture->write('install/database.sql', $broken);
        try {
            $result = $this->request($data);
            $this->assertNotEmpty($result['error'], json_encode($result));
            $this->assertSame('tables', $result['stage']);
            $this->assertFileDoesNotExist($this->fixture->path('videos/configuration.php'));
        } finally {
            $this->fixture->write('install/database.sql', $schema);
        }
        $result = $this->request($data);
        $this->assertTrue(!empty($result['installed']), json_encode($result));
    }

    /** @dataProvider installationModes */
    public function testExistingDatabaseModes($mode)
    {
        $data = $this->payload();
        $data['createTables'] = $mode;
        $data['tablesPrefix'] = '';
        $this->createDatabase($data['databaseName']);
        if ($mode === '0') {
            $this->importSchema($data['databaseName']);
        }
        $result = $this->request($data);
        $this->assertTrue(!empty($result['installed']), json_encode($result));
    }

    public function installationModes(): array { return [['1'], ['0']]; }

    public function testCliInvocationFromDifferentWorkingDirectory()
    {
        $data = $this->payload();
        $this->createDatabase($data['databaseName']);
        $environment = [
            'SERVER_URL' => $data['webSiteRootURL'],
            'DB_MYSQL_HOST' => $this->host,
            'DB_MYSQL_PORT' => (string) $this->port,
            'DB_MYSQL_NAME' => $data['databaseName'],
            'DB_MYSQL_USER' => $this->user,
            'DB_MYSQL_PASSWORD' => $this->password !== '' ? $this->password : null,
            'STREAMER_URL' => $data['siteURL'],
            'STREAMER_USER' => 'admin',
            'STREAMER_PASSWORD' => $this->adminPassword,
        ];
        $result = $this->fixture->run([$this->fixture->path('install/cli.php')], '', $environment);
        $this->assertSame(0, $result['exit'], $result['stdout'] . $result['stderr']);
        $this->assertFileExists($this->fixture->path('videos/configuration.php'));
        $this->assertStringNotContainsString($this->adminPassword, $result['stdout'] . $result['stderr']);
    }
}
