<?php

namespace React\Tests\Socket;

use React\Socket\SecureServer;
use React\Socket\TcpServer;
use React\Promise\Promise;

class SecureServerTest extends TestCase
{
    /**
     * @before
     */
    public function setUpSkipTest()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported on legacy HHVM');
        }
    }

    public function testConstructWithoutLoopAssignsLoopAutomatically()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();

        $server = new SecureServer($tcp);

        $ref = new \ReflectionProperty($server, 'encryption');
        $ref->setAccessible(true);
        $encryption = $ref->getValue($server);

        $ref = new \ReflectionProperty($encryption, 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($encryption);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    public function testGetAddressWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('getAddress')->willReturn('tcp://127.0.0.1:1234');

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $server = new SecureServer($tcp, $loop, array());

        $this->assertEquals('tls://127.0.0.1:1234', $server->getAddress());
    }

    public function testGetAddressWillReturnNullIfTcpServerReturnsNull()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('getAddress')->willReturn(null);

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $server = new SecureServer($tcp, $loop, array());

        $this->assertNull($server->getAddress());
    }

    public function testPauseWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('pause');

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $server = new SecureServer($tcp, $loop, array());

        $server->pause();
    }

    public function testResumeWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('resume');

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $server = new SecureServer($tcp, $loop, array());

        $server->resume();
    }

    public function testCloseWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('close');

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $server = new SecureServer($tcp, $loop, array());

        $server->close();
    }

    public function testConnectionWillBeClosedWithErrorIfItIsNotAStream()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $tcp = new TcpServer(0, $loop);

        $connection = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();
        $connection->expects($this->once())->method('close');

        $server = new SecureServer($tcp, $loop, array());

        $server->on('error', $this->expectCallableOnce());

        $tcp->emit('connection', array($connection));
    }

    public function testConnectionWillTryToEnableEncryptionAndWaitForHandshake()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $tcp = new TcpServer(0, $loop);

        $connection = $this->getMockBuilder('React\Socket\Connection')->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('getRemoteAddress')->willReturn('tcp://127.0.0.1:1234');
        $connection->expects($this->never())->method('close');

        $server = new SecureServer($tcp, $loop, array());

        $pending = new Promise(function () { });

        $encryption = $this->getMockBuilder('React\Socket\StreamEncryption')->disableOriginalConstructor()->getMock();
        $encryption->expects($this->once())->method('enable')->willReturn($pending);

        $ref = new \ReflectionProperty($server, 'encryption');
        $ref->setAccessible(true);
        $ref->setValue($server, $encryption);

        $ref = new \ReflectionProperty($server, 'context');
        $ref->setAccessible(true);
        $ref->setValue($server, array());

        $server->on('error', $this->expectCallableNever());
        $server->on('connection', $this->expectCallableNever());

        $tcp->emit('connection', array($connection));
    }

    public function testConnectionWillBeClosedWithErrorIfEnablingEncryptionFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $tcp = new TcpServer(0, $loop);

        $connection = $this->getMockBuilder('React\Socket\Connection')->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('getRemoteAddress')->willReturn('tcp://127.0.0.1:1234');
        $connection->expects($this->once())->method('close');

        $server = new SecureServer($tcp, $loop, array());

        $error = new \RuntimeException('Original');

        $encryption = $this->getMockBuilder('React\Socket\StreamEncryption')->disableOriginalConstructor()->getMock();
        $encryption->expects($this->once())->method('enable')->willReturn(\React\Promise\reject($error));

        $ref = new \ReflectionProperty($server, 'encryption');
        $ref->setAccessible(true);
        $ref->setValue($server, $encryption);

        $ref = new \ReflectionProperty($server, 'context');
        $ref->setAccessible(true);
        $ref->setValue($server, array());

        $error = null;
        $server->on('error', $this->expectCallableOnce());
        $server->on('error', function ($e) use (&$error) {
            $error = $e;
        });

        $tcp->emit('connection', array($connection));

        $this->assertInstanceOf('RuntimeException', $error);
        $this->assertEquals('Connection from tcp://127.0.0.1:1234 failed during TLS handshake: Original', $error->getMessage());
    }

    public function testSocketErrorWillBeForwarded()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $tcp = new TcpServer(0, $loop);

        $server = new SecureServer($tcp, $loop, array());

        $server->on('error', $this->expectCallableOnce());

        $tcp->emit('error', array(new \RuntimeException('test')));
    }
}
