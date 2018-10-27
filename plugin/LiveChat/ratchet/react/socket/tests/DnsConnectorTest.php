<?php

namespace React\Tests\Socket;

use React\Socket\DnsConnector;
use React\Promise;

class DnsConnectorTest extends TestCase
{
    private $tcp;
    private $resolver;
    private $connector;

    public function setUp()
    {
        $this->tcp = $this->getMock('React\Socket\ConnectorInterface');
        $this->resolver = $this->getMockBuilder('React\Dns\Resolver\Resolver')->disableOriginalConstructor()->getMock();

        $this->connector = new DnsConnector($this->tcp, $this->resolver);
    }

    public function testPassByResolverIfGivenIp()
    {
        $this->resolver->expects($this->never())->method('resolve');
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('127.0.0.1:80'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('127.0.0.1:80');
    }

    public function testPassThroughResolverIfGivenHost()
    {
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('google.com'))->will($this->returnValue(Promise\resolve('1.2.3.4')));
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80?hostname=google.com'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('google.com:80');
    }

    public function testPassThroughResolverIfGivenHostWhichResolvesToIpv6()
    {
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('google.com'))->will($this->returnValue(Promise\resolve('::1')));
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('[::1]:80?hostname=google.com'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('google.com:80');
    }

    public function testPassByResolverIfGivenCompleteUri()
    {
        $this->resolver->expects($this->never())->method('resolve');
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('scheme://127.0.0.1:80/path?query#fragment'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('scheme://127.0.0.1:80/path?query#fragment');
    }

    public function testPassThroughResolverIfGivenCompleteUri()
    {
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('google.com'))->will($this->returnValue(Promise\resolve('1.2.3.4')));
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('scheme://1.2.3.4:80/path?query&hostname=google.com#fragment'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('scheme://google.com:80/path?query#fragment');
    }

    public function testPassThroughResolverIfGivenExplicitHost()
    {
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('google.com'))->will($this->returnValue(Promise\resolve('1.2.3.4')));
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('scheme://1.2.3.4:80/?hostname=google.de'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('scheme://google.com:80/?hostname=google.de');
    }

    public function testRejectsImmediatelyIfUriIsInvalid()
    {
        $this->resolver->expects($this->never())->method('resolve');
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('////');

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
    }

    public function testSkipConnectionIfDnsFails()
    {
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.invalid'))->will($this->returnValue(Promise\reject()));
        $this->tcp->expects($this->never())->method('connect');

        $this->connector->connect('example.invalid:80');
    }

    public function testCancelDuringDnsCancelsDnsAndDoesNotStartTcpConnection()
    {
        $pending = new Promise\Promise(function () { }, $this->expectCallableOnce());
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->will($this->returnValue($pending));
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('example.com:80');
        $promise->cancel();

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
    }

    public function testCancelDuringTcpConnectionCancelsTcpConnection()
    {
        $pending = new Promise\Promise(function () { }, function () { throw new \Exception(); });
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->will($this->returnValue(Promise\resolve('1.2.3.4')));
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80?hostname=example.com'))->will($this->returnValue($pending));

        $promise = $this->connector->connect('example.com:80');
        $promise->cancel();

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
    }
}
