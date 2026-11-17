<?php

namespace Tests\Unit\MySQLSSLConnectionSettings;

use PHPUnit\Framework\TestCase;

// Only the pure settings function is loaded; connecting to a real SSL-only server was checked manually
// (wiki technical/Technical-Database.md, "Encrypted (SSL/TLS) connection").
$mysqlSSLSource = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/objects/functionsMySQL.php'));
$mysqlSSLStart = strpos($mysqlSSLSource, 'function getMySQLSSLConnectionSettings(');
$mysqlSSLEnd = strpos($mysqlSSLSource, "\nfunction ", $mysqlSSLStart + 1);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($mysqlSSLSource, $mysqlSSLStart, $mysqlSSLEnd - $mysqlSSLStart));
unset($mysqlSSLSource, $mysqlSSLStart, $mysqlSSLEnd);

class MySQLSSLConnectionSettingsTest extends TestCase
{
    public function testUnsetKeepsThePlainConnection()
    {
        $this->assertFalse(getMySQLSSLConnectionSettings(null));
        $this->assertFalse(getMySQLSSLConnectionSettings(false));
        $this->assertFalse(getMySQLSSLConnectionSettings([]));
    }

    public function testTrueEncryptsWithoutCheckingTheCertificate()
    {
        $settings = getMySQLSSLConnectionSettings(true);
        $this->assertFalse($settings['verifyServerCert']);
        $this->assertSame(MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT, $settings['flags']);
        $this->assertNull($settings['ca']);
    }

    public function testCertificateIsCheckedWhenACAIsGiven()
    {
        $settings = getMySQLSSLConnectionSettings(['ca' => '/etc/mysql/ca.pem']);
        $this->assertTrue($settings['verifyServerCert']);
        $this->assertSame(MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_VERIFY_SERVER_CERT, $settings['flags']);
        $this->assertSame('/etc/mysql/ca.pem', $settings['ca']);

        $settings = getMySQLSSLConnectionSettings(['capath' => '/etc/ssl/certs']);
        $this->assertTrue($settings['verifyServerCert']);
    }

    public function testVerifyServerCertOverridesTheDefault()
    {
        $settings = getMySQLSSLConnectionSettings(['ca' => '/etc/mysql/ca.pem', 'verifyServerCert' => false]);
        $this->assertFalse($settings['verifyServerCert']);
        $this->assertSame(MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT, $settings['flags']);

        $settings = getMySQLSSLConnectionSettings(['verifyServerCert' => true]);
        $this->assertTrue($settings['verifyServerCert']);
        $this->assertSame(MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_VERIFY_SERVER_CERT, $settings['flags']);
    }

    public function testClientCertificateAndUnknownOrInvalidValues()
    {
        $settings = getMySQLSSLConnectionSettings(['cert' => '/c.pem', 'key' => '/k.pem', 'cipher' => '', 'ca' => ['x'], 'other' => 'y']);
        $this->assertSame('/c.pem', $settings['cert']);
        $this->assertSame('/k.pem', $settings['key']);
        $this->assertNull($settings['cipher']);
        $this->assertNull($settings['ca']);
        $this->assertArrayNotHasKey('other', $settings);
        $this->assertFalse($settings['verifyServerCert']);
    }
}
