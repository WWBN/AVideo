<?php

namespace React\Tests\Socket;

use React\Promise;
use React\Promise\Deferred;
use React\Socket\DnsConnector;

class DnsConnectorTest extends TestCase
{
    private $tcp;
    private $resolver;
    private $connector;

    /**
     * @before
     */
    public function setUpMocks()
    {
        $this->tcp = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $this->resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();

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

    public function testRejectsWithTcpConnectorRejectionIfGivenIp()
    {
        $promise = Promise\reject(new \RuntimeException('Connection failed'));
        $this->resolver->expects($this->never())->method('resolve');
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80'))->willReturn($promise);

        $promise = $this->connector->connect('1.2.3.4:80');
        $promise->cancel();

        $this->setExpectedException('RuntimeException', 'Connection failed');
        $this->throwRejection($promise);
    }

    public function testRejectsWithTcpConnectorRejectionAfterDnsIsResolved()
    {
        $promise = Promise\reject(new \RuntimeException('Connection failed'));
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->willReturn(Promise\resolve('1.2.3.4'));
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80?hostname=example.com'))->willReturn($promise);

        $promise = $this->connector->connect('example.com:80');
        $promise->cancel();

        $this->setExpectedException('RuntimeException', 'Connection failed');
        $this->throwRejection($promise);
    }

    public function testSkipConnectionIfDnsFails()
    {
        $promise = Promise\reject(new \RuntimeException('DNS error'));
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.invalid'))->willReturn($promise);
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('example.invalid:80');

        $this->setExpectedException('RuntimeException', 'Connection to example.invalid:80 failed during DNS lookup: DNS error');
        $this->throwRejection($promise);
    }

    public function testRejectionExceptionUsesPreviousExceptionIfDnsFails()
    {
        $exception = new \RuntimeException();

        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.invalid'))->willReturn(Promise\reject($exception));

        $promise = $this->connector->connect('example.invalid:80');

        $promise->then(null, function ($e) {
            throw $e->getPrevious();
        })->then(null, $this->expectCallableOnceWith($this->identicalTo($exception)));
    }

    public function testCancelDuringDnsCancelsDnsAndDoesNotStartTcpConnection()
    {
        $pending = new Promise\Promise(function () { }, $this->expectCallableOnce());
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->will($this->returnValue($pending));
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('example.com:80');
        $promise->cancel();

        $this->setExpectedException('RuntimeException', 'Connection to example.com:80 cancelled during DNS lookup');
        $this->throwRejection($promise);
    }

    public function testCancelDuringTcpConnectionCancelsTcpConnectionIfGivenIp()
    {
        $pending = new Promise\Promise(function () { }, $this->expectCallableOnce());
        $this->resolver->expects($this->never())->method('resolve');
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80'))->willReturn($pending);

        $promise = $this->connector->connect('1.2.3.4:80');
        $promise->cancel();
    }

    public function testCancelDuringTcpConnectionCancelsTcpConnectionAfterDnsIsResolved()
    {
        $pending = new Promise\Promise(function () { }, $this->expectCallableOnce());
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->willReturn(Promise\resolve('1.2.3.4'));
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80?hostname=example.com'))->willReturn($pending);

        $promise = $this->connector->connect('example.com:80');
        $promise->cancel();
    }

    public function testCancelDuringTcpConnectionCancelsTcpConnectionWithTcpRejectionAfterDnsIsResolved()
    {
        $first = new Deferred();
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->willReturn($first->promise());
        $pending = new Promise\Promise(function () { }, function () {
            throw new \RuntimeException('Connection cancelled');
        });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80?hostname=example.com'))->willReturn($pending);

        $promise = $this->connector->connect('example.com:80');
        $first->resolve('1.2.3.4');

        $promise->cancel();

        $this->setExpectedException('RuntimeException', 'Connection cancelled');
        $this->throwRejection($promise);
    }

    public function testRejectionDuringDnsLookupShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $dns = new Deferred();
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->willReturn($dns->promise());
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('example.com:80');
        $dns->reject(new \RuntimeException('DNS failed'));
        unset($promise, $dns);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testRejectionAfterDnsLookupShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();

        $dns = new Deferred();
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->willReturn($dns->promise());

        $tcp = new Deferred();
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80?hostname=example.com'))->willReturn($tcp->promise());

        $promise = $this->connector->connect('example.com:80');
        $dns->resolve('1.2.3.4');
        $tcp->reject(new \RuntimeException('Connection failed'));
        unset($promise, $dns, $tcp);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testRejectionAfterDnsLookupShouldNotCreateAnyGarbageReferencesAgain()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();

        $dns = new Deferred();
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->willReturn($dns->promise());

        $tcp = new Deferred();
        $dns->promise()->then(function () use ($tcp) {
            $tcp->reject(new \RuntimeException('Connection failed'));
        });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80?hostname=example.com'))->willReturn($tcp->promise());

        $promise = $this->connector->connect('example.com:80');
        $dns->resolve('1.2.3.4');

        unset($promise, $dns, $tcp);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testCancelDuringDnsLookupShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();

        $dns = new Deferred(function () {
            throw new \RuntimeException();
        });
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->willReturn($dns->promise());
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('example.com:80');

        $promise->cancel();
        unset($promise, $dns);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testCancelDuringTcpConnectionShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();

        $dns = new Deferred();
        $this->resolver->expects($this->once())->method('resolve')->with($this->equalTo('example.com'))->willReturn($dns->promise());
        $tcp = new Promise\Promise(function () { }, function () {
            throw new \RuntimeException('Connection cancelled');
        });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80?hostname=example.com'))->willReturn($tcp);

        $promise = $this->connector->connect('example.com:80');
        $dns->resolve('1.2.3.4');

        $promise->cancel();
        unset($promise, $dns, $tcp);

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
