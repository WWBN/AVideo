<?php

namespace React\Tests\Socket;

use Clue\React\Block;
use React\Dns\Resolver\Factory as ResolverFactory;
use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Socket\DnsConnector;
use React\Socket\SecureConnector;
use React\Socket\TcpConnector;

/** @group internet */
class IntegrationTest extends TestCase
{
    const TIMEOUT = 5.0;

    /** @test */
    public function gettingStuffFromGoogleShouldWork()
    {
        $loop = Factory::create();
        $connector = new Connector(array(), $loop);

        $conn = Block\await($connector->connect('google.com:80'), $loop);

        $this->assertContainsString(':80', $conn->getRemoteAddress());
        $this->assertNotEquals('google.com:80', $conn->getRemoteAddress());

        $conn->write("GET / HTTP/1.0\r\n\r\n");

        $response = $this->buffer($conn, $loop, self::TIMEOUT);

        $this->assertMatchesRegExp('#^HTTP/1\.0#', $response);
    }

    /** @test */
    public function gettingEncryptedStuffFromGoogleShouldWork()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported on legacy HHVM');
        }

        $loop = Factory::create();
        $secureConnector = new Connector(array(), $loop);

        $conn = Block\await($secureConnector->connect('tls://google.com:443'), $loop);

        $conn->write("GET / HTTP/1.0\r\n\r\n");

        $response = $this->buffer($conn, $loop, self::TIMEOUT);

        $this->assertMatchesRegExp('#^HTTP/1\.0#', $response);
    }

    /** @test */
    public function gettingEncryptedStuffFromGoogleShouldWorkIfHostIsResolvedFirst()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported on legacy HHVM');
        }

        $loop = Factory::create();

        $factory = new ResolverFactory();
        $dns = $factory->create('8.8.8.8', $loop);

        $connector = new DnsConnector(
            new SecureConnector(
                new TcpConnector($loop),
                $loop
            ),
            $dns
        );

        $conn = Block\await($connector->connect('google.com:443'), $loop);

        $conn->write("GET / HTTP/1.0\r\n\r\n");

        $response = $this->buffer($conn, $loop, self::TIMEOUT);

        $this->assertMatchesRegExp('#^HTTP/1\.0#', $response);
    }

    /** @test */
    public function gettingPlaintextStuffFromEncryptedGoogleShouldNotWork()
    {
        $loop = Factory::create();
        $connector = new Connector(array(), $loop);

        $conn = Block\await($connector->connect('google.com:443'), $loop);

        $this->assertContainsString(':443', $conn->getRemoteAddress());
        $this->assertNotEquals('google.com:443', $conn->getRemoteAddress());

        $conn->write("GET / HTTP/1.0\r\n\r\n");

        $response = $this->buffer($conn, $loop, self::TIMEOUT);

        $this->assertDoesNotMatchRegExp('#^HTTP/1\.0#', $response);
    }

    public function testConnectingFailsIfConnectorUsesInvalidDnsResolverAddress()
    {
        if (PHP_OS === 'Darwin') {
            $this->markTestSkipped('Skipped on macOS due to a bug in reactphp/dns (solved in reactphp/dns#171)');
        }

        $loop = Factory::create();

        $factory = new ResolverFactory();
        $dns = $factory->create('255.255.255.255', $loop);

        $connector = new Connector(array(
            'dns' => $dns
        ), $loop);

        $this->setExpectedException('RuntimeException');
        Block\await($connector->connect('google.com:80'), $loop, self::TIMEOUT);
    }

    public function testCancellingPendingConnectionWithoutTimeoutShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $loop = Factory::create();
        $connector = new Connector(array('timeout' => false), $loop);

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $promise = $connector->connect('8.8.8.8:80');
        $promise->cancel();
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testCancellingPendingConnectionShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $loop = Factory::create();
        $connector = new Connector(array(), $loop);

        gc_collect_cycles();
        $promise = $connector->connect('8.8.8.8:80');
        $promise->cancel();
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testWaitingForRejectedConnectionShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $loop = Factory::create();
        $connector = new Connector(array('timeout' => false), $loop);

        gc_collect_cycles();

        $wait = true;
        $promise = $connector->connect('127.0.0.1:1')->then(
            null,
            function ($e) use (&$wait) {
                $wait = false;
                throw $e;
            }
        );

        // run loop for short period to ensure we detect connection refused error
        Block\sleep(0.01, $loop);
        if ($wait) {
            Block\sleep(0.2, $loop);
            if ($wait) {
                Block\sleep(2.0, $loop);
                if ($wait) {
                    $this->fail('Connection attempt did not fail');
                }
            }
        }
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testWaitingForConnectionTimeoutDuringDnsLookupShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $loop = Factory::create();
        $connector = new Connector(array('timeout' => 0.001), $loop);

        gc_collect_cycles();

        $wait = true;
        $promise = $connector->connect('google.com:80')->then(
            null,
            function ($e) use (&$wait) {
                $wait = false;
                throw $e;
            }
        );

        // run loop for short period to ensure we detect a connection timeout error
        Block\sleep(0.01, $loop);
        if ($wait) {
            Block\sleep(0.2, $loop);
            if ($wait) {
                $this->fail('Connection attempt did not fail');
            }
        }
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testWaitingForConnectionTimeoutDuringTcpConnectionShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $loop = Factory::create();
        $connector = new Connector(array('timeout' => 0.000001), $loop);

        gc_collect_cycles();

        $wait = true;
        $promise = $connector->connect('8.8.8.8:53')->then(
            null,
            function ($e) use (&$wait) {
                $wait = false;
                throw $e;
            }
        );

        // run loop for short period to ensure we detect a connection timeout error
        Block\sleep(0.01, $loop);
        if ($wait) {
            Block\sleep(0.2, $loop);
            if ($wait) {
                $this->fail('Connection attempt did not fail');
            }
        }
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testWaitingForInvalidDnsConnectionShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $loop = Factory::create();
        $connector = new Connector(array('timeout' => false), $loop);

        gc_collect_cycles();

        $wait = true;
        $promise = $connector->connect('example.invalid:80')->then(
            null,
            function ($e) use (&$wait) {
                $wait = false;
                throw $e;
            }
        );

        // run loop for short period to ensure we detect a DNS error
        Block\sleep(0.01, $loop);
        if ($wait) {
            Block\sleep(0.2, $loop);
            if ($wait) {
                Block\sleep(2.0, $loop);
                if ($wait) {
                    $this->fail('Connection attempt did not fail');
                }
            }
        }
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    /**
     * @requires PHP 7
     */
    public function testWaitingForInvalidTlsConnectionShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $loop = Factory::create();
        $connector = new Connector(array(
            'tls' => array(
                'verify_peer' => true
            )
        ), $loop);

        gc_collect_cycles();

        $wait = true;
        $promise = $connector->connect('tls://self-signed.badssl.com:443')->then(
            null,
            function ($e) use (&$wait) {
                $wait = false;
                throw $e;
            }
        );

        // run loop for short period to ensure we detect a TLS error
        Block\sleep(0.1, $loop);
        if ($wait) {
            Block\sleep(0.4, $loop);
            if ($wait) {
                Block\sleep(self::TIMEOUT - 0.5, $loop);
                if ($wait) {
                    $this->fail('Connection attempt did not fail');
                }
            }
        }
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testWaitingForSuccessfullyClosedConnectionShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $loop = Factory::create();
        $connector = new Connector(array('timeout' => false), $loop);

        gc_collect_cycles();
        $promise = $connector->connect('google.com:80')->then(
            function ($conn) {
                $conn->close();
            }
        );
        Block\await($promise, $loop, self::TIMEOUT);
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testConnectingFailsIfTimeoutIsTooSmall()
    {
        $loop = Factory::create();

        $connector = new Connector(array(
            'timeout' => 0.001
        ), $loop);

        $this->setExpectedException('RuntimeException');
        Block\await($connector->connect('google.com:80'), $loop, self::TIMEOUT);
    }

    public function testSelfSignedRejectsIfVerificationIsEnabled()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported on legacy HHVM');
        }

        $loop = Factory::create();

        $connector = new Connector(array(
            'tls' => array(
                'verify_peer' => true
            )
        ), $loop);

        $this->setExpectedException('RuntimeException');
        Block\await($connector->connect('tls://self-signed.badssl.com:443'), $loop, self::TIMEOUT);
    }

    public function testSelfSignedResolvesIfVerificationIsDisabled()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported on legacy HHVM');
        }

        $loop = Factory::create();

        $connector = new Connector(array(
            'tls' => array(
                'verify_peer' => false
            )
        ), $loop);

        $conn = Block\await($connector->connect('tls://self-signed.badssl.com:443'), $loop, self::TIMEOUT);
        $conn->close();

        // if we reach this, then everything is good
        $this->assertNull(null);
    }
}
