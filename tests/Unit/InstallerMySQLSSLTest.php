<?php

namespace Tests\Unit\InstallerMySQLSSL;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/install/installer.php';

// The installer must connect exactly like the site will with the $global['mysqlSSL'] value it writes;
// it cannot load objects/functionsMySQL.php, so compare it with the runtime function here.
$installerSSLSource = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/objects/functionsMySQL.php'));
$installerSSLStart = strpos($installerSSLSource, 'function getMySQLSSLConnectionSettings(');
$installerSSLEnd = strpos($installerSSLSource, "\nfunction ", $installerSSLStart + 1);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($installerSSLSource, $installerSSLStart, $installerSSLEnd - $installerSSLStart));
unset($installerSSLSource, $installerSSLStart, $installerSSLEnd);

class RecordingMySQLi
{
    public $ssl = null;
    public $flags = null;
    public function ssl_set($key, $cert, $ca, $capath, $cipher) { $this->ssl = [$key, $cert, $ca, $capath, $cipher]; return true; }
    public function real_connect($host, $user, $pass, $database, $port, $socket, $flags) { $this->flags = $flags; return true; }
}

class InstallerMySQLSSLTest extends TestCase
{
    private function form(array $extra = [])
    {
        return $extra + ['databaseHost' => 'db.example.com', 'databasePort' => '3306', 'databaseName' => 'avideo',
            'databaseUser' => 'avideo', 'databasePass' => 'secret', 'createTables' => '2'];
    }

    private function configuredSSL(array $data)
    {
        $data += ['webSiteRootURL' => 'https://example.com/', 'salt' => 'x'];
        $source = installerConfig($data);
        token_get_all($source, TOKEN_PARSE);
        $assignments = substr(explode('// Do not change the bootstrap below.', $source)[0], 5);
        return (static function ($php) {
            $global = [];
            eval($php);
            return array_key_exists('mysqlSSL', $global) ? $global['mysqlSSL'] : 'unset';
        })($assignments);
    }

    public function testDefaultFormKeepsThePlainConnection()
    {
        $data = \installerValidate($this->form(), true);
        $this->assertSame('0', $data['databaseSSL']);
        $this->assertSame('unset', $this->configuredSSL($data));
        $mysqli = new RecordingMySQLi();
        \installerConnect($mysqli, $data);
        $this->assertSame(0, $mysqli->flags);
        $this->assertNull($mysqli->ssl);
    }

    public function testEachOptionWritesTheSettingTheSiteUsesAndConnectsTheSameWay()
    {
        $cases = [
            'encrypt' => [['databaseSSL' => 'encrypt'], true],
            'verify with system CAs' => [['databaseSSL' => 'verify'], ['verifyServerCert' => true]],
            'verify with a CA file' => [['databaseSSL' => 'verify', 'databaseSSLCA' => __FILE__], ['ca' => __FILE__]],
        ];
        foreach ($cases as $name => [$fields, $expected]) {
            $data = \installerValidate($this->form($fields), true);
            $written = $this->configuredSSL($data);
            $this->assertSame($expected, $written, $name);
            $runtime = getMySQLSSLConnectionSettings($written);
            $mysqli = new RecordingMySQLi();
            \installerConnect($mysqli, $data);
            $this->assertSame($runtime['flags'], $mysqli->flags, $name);
            $this->assertSame($runtime['ca'], $mysqli->ssl[2] ?? null, $name);
        }
    }

    public function testInvalidChoicesAreRejected()
    {
        foreach ([['databaseSSL' => 'yes'], ['databaseSSL' => ['verify']],
            ['databaseSSL' => 'encrypt', 'databaseSSLCA' => __FILE__],
            ['databaseSSL' => '0', 'databaseSSLCA' => __FILE__],
            ['databaseSSL' => 'verify', 'databaseSSLCA' => __DIR__ . '/missing-ca.pem'],
            ['databaseSSL' => 'verify', 'databaseSSLCA' => __DIR__]] as $fields) {
            try {
                \installerValidate($this->form($fields), true);
                $this->fail('Accepted ' . json_encode($fields));
            } catch (\InstallerFailure $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }

    public function testSSLErrorsExplainWhatToChange()
    {
        $this->assertStringContainsString('only accepts encrypted connections', \installerDatabaseMessage(3159));
        $this->assertStringContainsString('encrypted connection could not be established', \installerDatabaseMessage(2002, ['databaseSSL' => 'verify']));
        $this->assertStringContainsString('Check the service, host, and port', \installerDatabaseMessage(2002, ['databaseSSL' => '0']));
        $data = \installerValidate($this->form(), true);
        $this->assertStringContainsString('Encrypted connection (SSL/TLS)', \installerDatabaseHelp(1045, $data)['text']);
        $data = \installerValidate($this->form(['databaseSSL' => 'encrypt']), true);
        $this->assertStringNotContainsString('Encrypted connection (SSL/TLS)', \installerDatabaseHelp(1045, $data)['text']);
    }
}
