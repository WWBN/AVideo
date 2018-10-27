<?php

namespace React\Tests\Dns;

use React\Tests\Dns\TestCase;
use React\EventLoop\Factory as LoopFactory;
use React\Dns\Resolver\Resolver;
use React\Dns\Resolver\Factory;

class FunctionalTest extends TestCase
{
    public function setUp()
    {
        $this->loop = LoopFactory::create();

        $factory = new Factory();
        $this->resolver = $factory->create('8.8.8.8', $this->loop);
    }

    public function testResolveLocalhostResolves()
    {
        $promise = $this->resolver->resolve('localhost');
        $promise->then($this->expectCallableOnce(), $this->expectCallableNever());

        $this->loop->run();
    }

    public function testResolveGoogleResolves()
    {
        $promise = $this->resolver->resolve('google.com');
        $promise->then($this->expectCallableOnce(), $this->expectCallableNever());

        $this->loop->run();
    }

    public function testResolveInvalidRejects()
    {
        $promise = $this->resolver->resolve('example.invalid');
        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());

        $this->loop->run();
    }

    public function testResolveCancelledRejectsImmediately()
    {
        $promise = $this->resolver->resolve('google.com');
        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
        $promise->cancel();

        $time = microtime(true);
        $this->loop->run();
        $time = microtime(true) - $time;

        $this->assertLessThan(0.1, $time);
    }

    public function testInvalidResolverDoesNotResolveGoogle()
    {
        $factory = new Factory();
        $this->resolver = $factory->create('255.255.255.255', $this->loop);

        $promise = $this->resolver->resolve('google.com');
        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
    }
}
