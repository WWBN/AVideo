<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/InstallerTestEnvironment.php';

class InstallersConsistencyTest extends TestCase
{
    private $fixtures = [];

    private function projects()
    {
        $encoder = getenv('AVIDEO_TEST_ENCODER_ROOT');
        $network = getenv('AVIDEO_TEST_NETWORK_ROOT');
        if (!$encoder || !$network) {
            $this->markTestSkipped('Set AVIDEO_TEST_ENCODER_ROOT and AVIDEO_TEST_NETWORK_ROOT for cross-project checks.');
        }
        return ['Streamer' => dirname(__DIR__, 2), 'Encoder' => $encoder, 'Network' => $network];
    }

    private function fixture($source)
    {
        $fixture = new InstallerTestEnvironment($source);
        $this->fixtures[] = $fixture;
        return $fixture;
    }

    protected function tearDown(): void
    {
        foreach ($this->fixtures as $fixture) { $fixture->cleanup(); }
        parent::tearDown();
    }

    public function testInstallerImagesMatchCanonicalImages()
    {
        $root = dirname(__DIR__, 2);
        foreach (['favicon.png', 'logo.png'] as $image) {
            $this->assertFileEquals($root . '/view/img/' . $image, $root . '/install/assets/' . $image);
        }
    }

    public function testSharedAssetsMatchAllThreeInstallers()
    {
        $projects = $this->projects();
        foreach (['installer.css', 'installer.js', 'ubuntu-help-functions.php', 'ubuntu-help.php', 'assets/favicon.png', 'assets/logo.png'] as $asset) {
            foreach (['Encoder', 'Network'] as $product) {
                $this->assertFileEquals($projects['Streamer'] . '/install/' . $asset, $projects[$product] . '/install/' . $asset, $product . ': ' . $asset);
            }
        }
    }

    public function testCompletedPagesDoNotExecuteOrExposeConfiguration()
    {
        foreach ($this->projects() as $product => $source) {
            $fixture = $this->fixture($source);
            $fixture->write($product === 'Network' ? 'configuration.php' : 'videos/configuration.php', '<?php throw new Exception("SECRET_SENTINEL");');
            foreach (['index.php', 'checkConfiguration.php'] as $file) {
                $result = $fixture->run([$fixture->path('install/' . $file)]);
                $this->assertSame(0, $result['exit'], $result['stderr']);
                foreach (['SECRET_SENTINEL', $fixture->root, 'php.ini', 'Ubuntu setup help', 'configurationForm', 'install_csrf_token', 'name="token"'] as $text) {
                    $this->assertStringNotContainsString($text, $result['stdout'], $product . ': ' . $file);
                }
                $this->assertStringContainsString('Installation', $result['stdout']);
            }
            if ($product === 'Streamer') {
                $result = $fixture->run([$fixture->path('install/cli.php')]);
                $this->assertSame(0, $result['exit']);
                $this->assertSame('', $result['stdout']);
                $this->assertFileDoesNotExist($fixture->path('videos/.initial_admin_password.php'));
            }
        }
    }

    public function testCliIsolatesEncoderAndPropagatesFailureWithoutLoggingPasswords()
    {
        $projects = $this->projects();
        $fixture = $this->fixture($projects['Streamer']);
        $fixture->copyDirectory($projects['Encoder'] . '/install', 'Encoder/install');
        $fixture->write('install/checkConfiguration.php', '<?php require __DIR__ . "/installer.php"; $installerSucceeded = true;');
        $fixture->write('install/installPluginsTables.php', '<?php');
        $fixture->write('Encoder/install/checkConfiguration.php', '<?php require __DIR__ . "/installer.php"; $installerSucceeded = $_POST["inputPassword"] === getenv("SYSTEM_ADMIN_PASSWORD") && $_POST["databasePort"] === "33317";');
        $environment = ['SYSTEM_ADMIN_PASSWORD' => 'cli-qa-secret', 'DB_MYSQL_HOST' => 'database',
            'DB_MYSQL_PORT' => '33317', 'DB_MYSQL_NAME' => 'qa', 'SERVER_NAME' => 'example.test',
            'ENCODER_DB_MYSQL_HOST' => null, 'ENCODER_DB_MYSQL_PORT' => null];
        $result = $fixture->run([$fixture->path('install/cli.php')], '', $environment);
        $this->assertSame(0, $result['exit'], $result['stderr']);
        $this->assertStringNotContainsString('cli-qa-secret', $result['stdout'] . $result['stderr']);
        $fixture->write('Encoder/install/checkConfiguration.php', '<?php $installerSucceeded = false;');
        $result = $fixture->run([$fixture->path('install/cli.php')], '', $environment);
        $this->assertSame(1, $result['exit']);
        $fixture->write('Encoder/install/checkConfiguration.php', '<?php $installerSucceeded = true;');
        $environment['SYSTEM_ADMIN_PASSWORD'] = '';
        $result = $fixture->run([$fixture->path('install/cli.php')], '', $environment);
        $this->assertSame(0, $result['exit'], $result['stderr']);
        $passwordFile = $fixture->path('videos/.initial_admin_password.php');
        $password = $fixture->run(['-r', 'echo require $argv[1];', $passwordFile]);
        $this->assertSame(0, $password['exit']);
        $this->assertSame(32, strlen($password['stdout']));
        $this->assertStringNotContainsString($password['stdout'], $result['stdout'] . $result['stderr']);
        $this->assertSame('', $fixture->run([$passwordFile])['stdout']);
    }
}
