<?php

namespace React\Tests\Socket;

use React\Socket\Connector;
use React\Promise\Promise;

class ConnectorTest extends TestCase
{
    public function testConstructWithoutLoopAssignsLoopAutomatically()
    {
        $connector = new Connector();

        $ref = new \ReflectionProperty($connector, 'connectors');
        $ref->setAccessible(true);
        $connectors = $ref->getValue($connector);

        $ref = new \ReflectionProperty($connectors['tcp'], 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($connectors['tcp']);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    public function testConstructWithLoopAssignsGivenLoop()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $connector = new Connector(array(), $loop);

        $ref = new \ReflectionProperty($connector, 'connectors');
        $ref->setAccessible(true);
        $connectors = $ref->getValue($connector);

        $ref = new \ReflectionProperty($connectors['tcp'], 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($connectors['tcp']);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    public function testConstructWithContextAssignsGivenContext()
    {
        $tcp = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();

        $connector = new Connector(array(
            'tcp' => $tcp,
            'dns' => false,
            'timeout' => false
        ));

        $ref = new \ReflectionProperty($connector, 'connectors');
        $ref->setAccessible(true);
        $connectors = $ref->getValue($connector);

        $this->assertSame($tcp, $connectors['tcp']);
    }

    public function testConstructWithLegacyContextSignatureAssignsGivenContext()
    {
        $tcp = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();

        $connector = new Connector(null, array(
            'tcp' => $tcp,
            'dns' => false,
            'timeout' => false
        ));

        $ref = new \ReflectionProperty($connector, 'connectors');
        $ref->setAccessible(true);
        $connectors = $ref->getValue($connector);

        $this->assertSame($tcp, $connectors['tcp']);
    }

    public function testConstructWithLegacyLoopSignatureAssignsGivenLoop()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $connector = new Connector($loop);

        $ref = new \ReflectionProperty($connector, 'connectors');
        $ref->setAccessible(true);
        $connectors = $ref->getValue($connector);

        $ref = new \ReflectionProperty($connectors['tcp'], 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($connectors['tcp']);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    public function testConstructWithInvalidContextThrows()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Connector('foo');
    }

    public function testConstructWithInvalidLoopThrows()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Connector(array(), 'foo');
    }

    public function testConstructWithContextTwiceThrows()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Connector(array(), array());
    }

    public function testConstructWithLoopTwiceThrows()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $this->setExpectedException('InvalidArgumentException');
        new Connector($loop, $loop);
    }

    public function testConstructWithNullContextAndLoopThrows()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $this->setExpectedException('InvalidArgumentException');
        new Connector(null, $loop);
    }

    public function testConstructWithLoopAndNullContextThrows()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $this->setExpectedException('InvalidArgumentException');
        new Connector($loop, null);
    }

    public function testConnectorUsesTcpAsDefaultScheme()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $promise = new Promise(function () { });
        $tcp = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $tcp->expects($this->once())->method('connect')->with('127.0.0.1:80')->willReturn($promise);

        $connector = new Connector(array(
            'tcp' => $tcp
        ), $loop);

        $connector->connect('127.0.0.1:80');
    }

    public function testConnectorPassedThroughHostnameIfDnsIsDisabled()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $promise = new Promise(function () { });
        $tcp = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $tcp->expects($this->once())->method('connect')->with('tcp://google.com:80')->willReturn($promise);

        $connector = new Connector(array(
            'tcp' => $tcp,
            'dns' => false
        ), $loop);

        $connector->connect('tcp://google.com:80');
    }

    public function testConnectorWithUnknownSchemeAlwaysFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = new Connector(array(), $loop);

        $promise = $connector->connect('unknown://google.com:80');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testConnectorWithDisabledTcpDefaultSchemeAlwaysFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = new Connector(array(
            'tcp' => false
        ), $loop);

        $promise = $connector->connect('google.com:80');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testConnectorWithDisabledTcpSchemeAlwaysFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = new Connector(array(
            'tcp' => false
        ), $loop);

        $promise = $connector->connect('tcp://google.com:80');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testConnectorWithDisabledTlsSchemeAlwaysFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = new Connector(array(
            'tls' => false
        ), $loop);

        $promise = $connector->connect('tls://google.com:443');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testConnectorWithDisabledUnixSchemeAlwaysFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = new Connector(array(
            'unix' => false
        ), $loop);

        $promise = $connector->connect('unix://demo.sock');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testConnectorUsesGivenResolverInstance()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $promise = new Promise(function () { });
        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->once())->method('resolve')->with('google.com')->willReturn($promise);

        $connector = new Connector(array(
            'dns' => $resolver,
            'happy_eyeballs' => false,
        ), $loop);

        $connector->connect('google.com:80');
    }

    public function testConnectorUsesResolvedHostnameIfDnsIsUsed()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $promise = new Promise(function ($resolve) { $resolve('127.0.0.1'); });
        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->once())->method('resolve')->with('google.com')->willReturn($promise);

        $promise = new Promise(function () { });
        $tcp = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $tcp->expects($this->once())->method('connect')->with('tcp://127.0.0.1:80?hostname=google.com')->willReturn($promise);

        $connector = new Connector(array(
            'tcp' => $tcp,
            'dns' => $resolver,
            'happy_eyeballs' => false,
        ), $loop);

        $connector->connect('tcp://google.com:80');
    }
}
