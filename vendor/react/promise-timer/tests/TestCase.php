<?php

namespace React\Tests\Promise\Timer;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
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
        if (method_exists('PHPUnit\Framework\MockObject\MockBuilder', 'addMethods')) {
            // PHPUnit 9+
            return $this->getMockBuilder('stdClass')->addMethods(array('__invoke'))->getMock();
        } else {
            // legacy PHPUnit 4 - PHPUnit 9
            return $this->getMockBuilder('stdClass')->setMethods(array('__invoke'))->getMock();
        }
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
