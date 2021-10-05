<?php

namespace React\Tests\Stream;

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

    protected function expectCallableOnceWith($value)
    {
        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($value);

        return $callback;
    }

    protected function expectCallableNever()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method('__invoke');

        return $mock;
    }

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

    public function setExpectedException($exception, $exceptionMessage = '', $exceptionCode = null)
    {
        if (method_exists($this, 'expectException')) {
            // PHPUnit 5.2+
            $this->expectException($exception);
            if ($exceptionMessage !== '') {
                $this->expectExceptionMessage($exceptionMessage);
            }
            if ($exceptionCode !== null) {
                $this->expectExceptionCode($exceptionCode);
            }
        } else {
            // legacy PHPUnit 4 - PHPUnit 5.1
            parent::setExpectedException($exception, $exceptionMessage, $exceptionCode);
        }
    }

    public function assertContainsString($needle, $haystack)
    {
        if (method_exists($this, 'assertStringContainsString')) {
            // PHPUnit 7.5+
            $this->assertStringContainsString($needle, $haystack);
        } else {
            // legacy PHPUnit 4 - PHPUnit 7.5
            $this->assertContains($needle, $haystack);
        }
    }

    public function assertContainsStringIgnoringCase($needle, $haystack)
    {
        if (method_exists($this, 'assertStringContainsStringIgnoringCase')) {
            // PHPUnit 7.5+
            $this->assertStringContainsStringIgnoringCase($needle, $haystack);
        } else {
            // legacy PHPUnit 4 - PHPUnit 7.5
            $this->assertContains($needle, $haystack, '', true);
        }
    }

    public function assertSameIgnoringCase($expected, $actual)
    {
        if (method_exists($this, 'assertEqualsIgnoringCase')) {
            // PHPUnit 7.5+
            $this->assertEqualsIgnoringCase($expected, $actual);
        } else {
            // legacy PHPUnit 4 - PHPUnit 7.5
            $this->assertSame($expected, $actual);
        }
    }
}
