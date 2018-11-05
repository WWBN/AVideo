<?php

namespace React\Tests\Dns\Config;

use React\Tests\Dns\TestCase;
use React\Dns\Config\HostsFile;

class HostsFileTest extends TestCase
{
    public function testLoadsFromDefaultPath()
    {
        $hosts = HostsFile::loadFromPathBlocking();

        $this->assertInstanceOf('React\Dns\Config\HostsFile', $hosts);
    }

    public function testDefaultShouldHaveLocalhostMapped()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('Not supported on Windows');
        }

        $hosts = HostsFile::loadFromPathBlocking();

        $this->assertContains('127.0.0.1', $hosts->getIpsForHost('localhost'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLoadThrowsForInvalidPath()
    {
        HostsFile::loadFromPathBlocking('does/not/exist');
    }

    public function testContainsSingleLocalhostEntry()
    {
        $hosts = new HostsFile('127.0.0.1 localhost');

        $this->assertEquals(array('127.0.0.1'), $hosts->getIpsForHost('localhost'));
        $this->assertEquals(array(), $hosts->getIpsForHost('example.com'));
    }

    public function testNonIpReturnsNothingForInvalidHosts()
    {
        $hosts = new HostsFile('a b');

        $this->assertEquals(array(), $hosts->getIpsForHost('a'));
        $this->assertEquals(array(), $hosts->getIpsForHost('b'));
    }

    public function testIgnoresIpv6ZoneId()
    {
        $hosts = new HostsFile('fe80::1%lo0 localhost');

        $this->assertEquals(array('fe80::1'), $hosts->getIpsForHost('localhost'));
    }

    public function testSkipsComments()
    {
        $hosts = new HostsFile('# start' . PHP_EOL .'#127.0.0.1 localhost' . PHP_EOL . '127.0.0.2 localhost # example.com');

        $this->assertEquals(array('127.0.0.2'), $hosts->getIpsForHost('localhost'));
        $this->assertEquals(array(), $hosts->getIpsForHost('example.com'));
    }

    public function testContainsSingleLocalhostEntryWithCaseIgnored()
    {
        $hosts = new HostsFile('127.0.0.1 LocalHost');

        $this->assertEquals(array('127.0.0.1'), $hosts->getIpsForHost('LOCALHOST'));
    }

    public function testEmptyFileContainsNothing()
    {
        $hosts = new HostsFile('');

        $this->assertEquals(array(), $hosts->getIpsForHost('example.com'));
    }

    public function testSingleEntryWithMultipleNames()
    {
        $hosts = new HostsFile('127.0.0.1 localhost example.com');

        $this->assertEquals(array('127.0.0.1'), $hosts->getIpsForHost('example.com'));
        $this->assertEquals(array('127.0.0.1'), $hosts->getIpsForHost('localhost'));
    }

    public function testMergesEntriesOverMultipleLines()
    {
        $hosts = new HostsFile("127.0.0.1 localhost\n127.0.0.2 localhost\n127.0.0.3 a localhost b\n127.0.0.4 a localhost");

        $this->assertEquals(array('127.0.0.1', '127.0.0.2', '127.0.0.3', '127.0.0.4'), $hosts->getIpsForHost('localhost'));
    }

    public function testMergesIpv4AndIpv6EntriesOverMultipleLines()
    {
        $hosts = new HostsFile("127.0.0.1 localhost\n::1 localhost");

        $this->assertEquals(array('127.0.0.1', '::1'), $hosts->getIpsForHost('localhost'));
    }

    public function testReverseLookup()
    {
        $hosts = new HostsFile('127.0.0.1 localhost');

        $this->assertEquals(array('localhost'), $hosts->getHostsForIp('127.0.0.1'));
        $this->assertEquals(array(), $hosts->getHostsForIp('192.168.1.1'));
    }

    public function testReverseSkipsComments()
    {
        $hosts = new HostsFile("# start\n#127.0.0.1 localhosted\n127.0.0.2\tlocalhost\t# example.com\n\t127.0.0.3\t\texample.org\t\t");

        $this->assertEquals(array(), $hosts->getHostsForIp('127.0.0.1'));
        $this->assertEquals(array('localhost'), $hosts->getHostsForIp('127.0.0.2'));
        $this->assertEquals(array('example.org'), $hosts->getHostsForIp('127.0.0.3'));
    }

    public function testReverseNonIpReturnsNothing()
    {
        $hosts = new HostsFile('127.0.0.1 localhost');

        $this->assertEquals(array(), $hosts->getHostsForIp('localhost'));
        $this->assertEquals(array(), $hosts->getHostsForIp('127.0.0.1.1'));
    }

    public function testReverseNonIpReturnsNothingForInvalidHosts()
    {
        $hosts = new HostsFile('a b');

        $this->assertEquals(array(), $hosts->getHostsForIp('a'));
        $this->assertEquals(array(), $hosts->getHostsForIp('b'));
    }

    public function testReverseLookupReturnsLowerCaseHost()
    {
        $hosts = new HostsFile('127.0.0.1 LocalHost');

        $this->assertEquals(array('localhost'), $hosts->getHostsForIp('127.0.0.1'));
    }

    public function testReverseLookupChecksNormalizedIpv6()
    {
        $hosts = new HostsFile('FE80::00a1 localhost');

        $this->assertEquals(array('localhost'), $hosts->getHostsForIp('fe80::A1'));
    }

    public function testReverseLookupIgnoresIpv6ZoneId()
    {
        $hosts = new HostsFile('fe80::1%lo0 localhost');

        $this->assertEquals(array('localhost'), $hosts->getHostsForIp('fe80::1'));
    }

    public function testReverseLookupReturnsMultipleHostsOverSingleLine()
    {
        $hosts = new HostsFile("::1 ip6-localhost ip6-loopback");

        $this->assertEquals(array('ip6-localhost', 'ip6-loopback'), $hosts->getHostsForIp('::1'));
    }

    public function testReverseLookupReturnsMultipleHostsOverMultipleLines()
    {
        $hosts = new HostsFile("::1 ip6-localhost\n::1 ip6-loopback");

        $this->assertEquals(array('ip6-localhost', 'ip6-loopback'), $hosts->getHostsForIp('::1'));
    }
}
