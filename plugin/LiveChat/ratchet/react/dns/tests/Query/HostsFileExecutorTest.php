<?php

namespace React\Tests\Dns\Query;

use React\Tests\Dns\TestCase;
use React\Dns\Query\HostsFileExecutor;
use React\Dns\Query\Query;
use React\Dns\Model\Message;

class HostsFileExecutorTest extends TestCase
{
    private $hosts;
    private $fallback;
    private $executor;

    public function setUp()
    {
        $this->hosts = $this->getMockBuilder('React\Dns\Config\HostsFile')->disableOriginalConstructor()->getMock();
        $this->fallback = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $this->executor = new HostsFileExecutor($this->hosts, $this->fallback);
    }

    public function testDoesNotTryToGetIpsForMxQuery()
    {
        $this->hosts->expects($this->never())->method('getIpsForHost');
        $this->fallback->expects($this->once())->method('query');

        $this->executor->query('8.8.8.8', new Query('google.com', Message::TYPE_MX, Message::CLASS_IN, 0));
    }

    public function testFallsBackIfNoIpsWereFound()
    {
        $this->hosts->expects($this->once())->method('getIpsForHost')->willReturn(array());
        $this->fallback->expects($this->once())->method('query');

        $this->executor->query('8.8.8.8', new Query('google.com', Message::TYPE_A, Message::CLASS_IN, 0));
    }

    public function testReturnsResponseMessageIfIpsWereFound()
    {
        $this->hosts->expects($this->once())->method('getIpsForHost')->willReturn(array('127.0.0.1'));
        $this->fallback->expects($this->never())->method('query');

        $ret = $this->executor->query('8.8.8.8', new Query('google.com', Message::TYPE_A, Message::CLASS_IN, 0));
    }

    public function testFallsBackIfNoIpv4Matches()
    {
        $this->hosts->expects($this->once())->method('getIpsForHost')->willReturn(array('::1'));
        $this->fallback->expects($this->once())->method('query');

        $ret = $this->executor->query('8.8.8.8', new Query('google.com', Message::TYPE_A, Message::CLASS_IN, 0));
    }

    public function testReturnsResponseMessageIfIpv6AddressesWereFound()
    {
        $this->hosts->expects($this->once())->method('getIpsForHost')->willReturn(array('::1'));
        $this->fallback->expects($this->never())->method('query');

        $ret = $this->executor->query('8.8.8.8', new Query('google.com', Message::TYPE_AAAA, Message::CLASS_IN, 0));
    }

    public function testFallsBackIfNoIpv6Matches()
    {
        $this->hosts->expects($this->once())->method('getIpsForHost')->willReturn(array('127.0.0.1'));
        $this->fallback->expects($this->once())->method('query');

        $ret = $this->executor->query('8.8.8.8', new Query('google.com', Message::TYPE_AAAA, Message::CLASS_IN, 0));
    }

    public function testDoesReturnReverseIpv4Lookup()
    {
        $this->hosts->expects($this->once())->method('getHostsForIp')->with('127.0.0.1')->willReturn(array('localhost'));
        $this->fallback->expects($this->never())->method('query');

        $this->executor->query('8.8.8.8', new Query('1.0.0.127.in-addr.arpa', Message::TYPE_PTR, Message::CLASS_IN, 0));
    }

    public function testFallsBackIfNoReverseIpv4Matches()
    {
        $this->hosts->expects($this->once())->method('getHostsForIp')->with('127.0.0.1')->willReturn(array());
        $this->fallback->expects($this->once())->method('query');

        $this->executor->query('8.8.8.8', new Query('1.0.0.127.in-addr.arpa', Message::TYPE_PTR, Message::CLASS_IN, 0));
    }

    public function testDoesReturnReverseIpv6Lookup()
    {
        $this->hosts->expects($this->once())->method('getHostsForIp')->with('2a02:2e0:3fe:100::6')->willReturn(array('ip6-localhost'));
        $this->fallback->expects($this->never())->method('query');

        $this->executor->query('8.8.8.8', new Query('6.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.1.0.e.f.3.0.0.e.2.0.2.0.a.2.ip6.arpa', Message::TYPE_PTR, Message::CLASS_IN, 0));
    }

    public function testFallsBackForInvalidAddress()
    {
        $this->hosts->expects($this->never())->method('getHostsForIp');
        $this->fallback->expects($this->once())->method('query');

        $this->executor->query('8.8.8.8', new Query('example.com', Message::TYPE_PTR, Message::CLASS_IN, 0));
    }

    public function testReverseFallsBackForInvalidIpv4Address()
    {
        $this->hosts->expects($this->never())->method('getHostsForIp');
        $this->fallback->expects($this->once())->method('query');

        $this->executor->query('8.8.8.8', new Query('::1.in-addr.arpa', Message::TYPE_PTR, Message::CLASS_IN, 0));
    }

    public function testReverseFallsBackForInvalidLengthIpv6Address()
    {
        $this->hosts->expects($this->never())->method('getHostsForIp');
        $this->fallback->expects($this->once())->method('query');

        $this->executor->query('8.8.8.8', new Query('abcd.ip6.arpa', Message::TYPE_PTR, Message::CLASS_IN, 0));
    }

    public function testReverseFallsBackForInvalidHexIpv6Address()
    {
        $this->hosts->expects($this->never())->method('getHostsForIp');
        $this->fallback->expects($this->once())->method('query');

        $this->executor->query('8.8.8.8', new Query('zZz.ip6.arpa', Message::TYPE_PTR, Message::CLASS_IN, 0));
    }
}
