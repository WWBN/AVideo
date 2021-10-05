<?php

namespace React\Tests\Socket;

use React\Promise;
use React\Promise\Deferred;
use React\Socket\SecureConnector;

class SecureConnectorTest extends TestCase
{
    private $loop;
    private $tcp;
    private $connector;

    /**
     * @before
     */
    public function setUpConnector()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported on legacy HHVM');
        }

        $this->loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $this->tcp = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $this->connector = new SecureConnector($this->tcp, $this->loop);
    }

    public function testConstructWithoutLoopAssignsLoopAutomatically()
    {
        $connector = new SecureConnector($this->tcp);

        $ref = new \ReflectionProperty($connector, 'streamEncryption');
        $ref->setAccessible(true);
        $streamEncryption = $ref->getValue($connector);

        $ref = new \ReflectionProperty($streamEncryption, 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($streamEncryption);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    public function testConnectionWillWaitForTcpConnection()
    {
        $pending = new Promise\Promise(function () { });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80'))->will($this->returnValue($pending));

        $promise = $this->connector->connect('example.com:80');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);
    }

    public function testConnectionWithCompleteUriWillBePassedThroughExpectForScheme()
    {
        $pending = new Promise\Promise(function () { });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80/path?query#fragment'))->will($this->returnValue($pending));

        $this->connector->connect('tls://example.com:80/path?query#fragment');
    }

    public function testConnectionToInvalidSchemeWillReject()
    {
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('tcp://example.com:80');

        $promise->then(null, $this->expectCallableOnce());
    }

    public function testCancelDuringTcpConnectionCancelsTcpConnection()
    {
        $pending = new Promise\Promise(function () { }, $this->expectCallableOnce());
        $this->tcp->expects($this->once())->method('connect')->with('example.com:80')->willReturn($pending);

        $promise = $this->connector->connect('example.com:80');
        $promise->cancel();
    }

    public function testCancelDuringTcpConnectionCancelsTcpConnectionAndRejectsWithTcpRejection()
    {
        $pending = new Promise\Promise(function () { }, function () { throw new \RuntimeException('Connection cancelled'); });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80'))->will($this->returnValue($pending));

        $promise = $this->connector->connect('example.com:80');
        $promise->cancel();

        $this->setExpectedException('RuntimeException', 'Connection cancelled');
        $this->throwRejection($promise);
    }

    public function testConnectionWillBeClosedAndRejectedIfConnectionIsNoStream()
    {
        $connection = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();
        $connection->expects($this->once())->method('close');

        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80'))->willReturn(Promise\resolve($connection));

        $promise = $this->connector->connect('example.com:80');

        $this->setExpectedException('UnexpectedValueException', 'Base connector does not use internal Connection class exposing stream resource');
        $this->throwRejection($promise);
    }

    public function testStreamEncryptionWillBeEnabledAfterConnecting()
    {
        $connection = $this->getMockBuilder('React\Socket\Connection')->disableOriginalConstructor()->getMock();

        $encryption = $this->getMockBuilder('React\Socket\StreamEncryption')->disableOriginalConstructor()->getMock();
        $encryption->expects($this->once())->method('enable')->with($connection)->willReturn(new \React\Promise\Promise(function () { }));

        $ref = new \ReflectionProperty($this->connector, 'streamEncryption');
        $ref->setAccessible(true);
        $ref->setValue($this->connector, $encryption);

        $pending = new Promise\Promise(function () { }, function () { throw new \RuntimeException('Connection cancelled'); });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80'))->willReturn(Promise\resolve($connection));

        $promise = $this->connector->connect('example.com:80');
    }

    public function testConnectionWillBeRejectedIfStreamEncryptionFailsAndClosesConnection()
    {
        $connection = $this->getMockBuilder('React\Socket\Connection')->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('close');

        $encryption = $this->getMockBuilder('React\Socket\StreamEncryption')->disableOriginalConstructor()->getMock();
        $encryption->expects($this->once())->method('enable')->willReturn(Promise\reject(new \RuntimeException('TLS error', 123)));

        $ref = new \ReflectionProperty($this->connector, 'streamEncryption');
        $ref->setAccessible(true);
        $ref->setValue($this->connector, $encryption);

        $pending = new Promise\Promise(function () { }, function () { throw new \RuntimeException('Connection cancelled'); });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80'))->willReturn(Promise\resolve($connection));

        $promise = $this->connector->connect('example.com:80');

        try {
            $this->throwRejection($promise);
        } catch (\RuntimeException $e) {
            $this->assertEquals('Connection to example.com:80 failed during TLS handshake: TLS error', $e->getMessage());
            $this->assertEquals(123, $e->getCode());
            $this->assertNull($e->getPrevious());
        }
    }

    public function testCancelDuringStreamEncryptionCancelsEncryptionAndClosesConnection()
    {
        $connection = $this->getMockBuilder('React\Socket\Connection')->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('close');

        $pending = new Promise\Promise(function () { }, function () {
            throw new \Exception('Ignored');
        });
        $encryption = $this->getMockBuilder('React\Socket\StreamEncryption')->disableOriginalConstructor()->getMock();
        $encryption->expects($this->once())->method('enable')->willReturn($pending);

        $ref = new \ReflectionProperty($this->connector, 'streamEncryption');
        $ref->setAccessible(true);
        $ref->setValue($this->connector, $encryption);

        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80'))->willReturn(Promise\resolve($connection));

        $promise = $this->connector->connect('example.com:80');
        $promise->cancel();

        $this->setExpectedException('RuntimeException', 'Connection to example.com:80 cancelled during TLS handshake');
        $this->throwRejection($promise);
    }

    public function testRejectionDuringConnectionShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $tcp = new Deferred();
        $this->tcp->expects($this->once())->method('connect')->willReturn($tcp->promise());

        $promise = $this->connector->connect('example.com:80');
        $tcp->reject(new \RuntimeException());
        unset($promise, $tcp);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testRejectionDuringTlsHandshakeShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();

        $connection = $this->getMockBuilder('React\Socket\Connection')->disableOriginalConstructor()->getMock();

        $tcp = new Deferred();
        $this->tcp->expects($this->once())->method('connect')->willReturn($tcp->promise());

        $tls = new Deferred();
        $encryption = $this->getMockBuilder('React\Socket\StreamEncryption')->disableOriginalConstructor()->getMock();
        $encryption->expects($this->once())->method('enable')->willReturn($tls->promise());

        $ref = new \ReflectionProperty($this->connector, 'streamEncryption');
        $ref->setAccessible(true);
        $ref->setValue($this->connector, $encryption);

        $promise = $this->connector->connect('example.com:80');
        $tcp->resolve($connection);
        $tls->reject(new \RuntimeException());
        unset($promise, $tcp, $tls);

        $this->assertEquals(0, gc_collect_cycles());
    }

    private function throwRejection($promise)
    {
        $ex = null;
        $promise->then(null, function ($e) use (&$ex) {
            $ex = $e;
        });

        throw $ex;
    }
}
