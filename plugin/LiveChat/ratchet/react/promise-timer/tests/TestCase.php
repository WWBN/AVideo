<?php

namespace React\Tests\Promise\Timer;

use React\EventLoop\Factory;
use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected $loop;

    public function setUp()
    {
        $this->loop = Factory::create();
    }

    protected function expectCallableOnce()
    {
        $mock = $this->createCallableMock();

        $mock
            ->expects($this->once())
            ->method('__invoke');

        return $mock;
    }

    protected function expectCallableNever()
    {
        $mock = $this->createCallableMock();

        $mock
            ->expects($this->never())
            ->method('__invoke');

        return $mock;
    }

    /**
     * @link https://github.com/reactphp/react/blob/master/tests/React/Tests/Socket/TestCase.php (taken from reactphp/react)
     */
    protected function createCallableMock()
    {
        return $this->getMockBuilder('React\Tests\Promise\Timer\CallableStub')->getMock();
    }

    protected function expectPromiseRejected($promise)
    {
        return $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
    }

    protected function expectPromiseResolved($promise)
    {
        return $promise->then($this->expectCallableOnce(), $this->expectCallableNever());
    }

    protected function expectPromisePending($promise)
    {
        return $promise->then($this->expectCallableNever(), $this->expectCallableNever());
    }
}
