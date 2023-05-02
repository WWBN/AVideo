<?php
/**
 * PHPUnit provides it's own .phpstorm.meta.php starting from PHPUnit 9,
 * this bridge is identical to it and exists to support PHPUnit <= 8
 */

namespace PHPSTORM_META {

    override(
        \PHPUnit\Framework\TestCase::createMock(0),
        type(0)
    );

    override(
        \PHPUnit\Framework\TestCase::createStub(0),
        type(0)
    );

    override(
        \PHPUnit\Framework\TestCase::createConfiguredMock(0),
        type(0)
    );

    override(
        \PHPUnit\Framework\TestCase::createPartialMock(0),
        type(0)
    );

    override(
        \PHPUnit\Framework\TestCase::createTestProxy(0),
        type(0)
    );

    override(
        \PHPUnit\Framework\TestCase::getMockForAbstractClass(0),
        type(0)
    );

    override(
        \PHPUnit_Framework_TestCase::createMock(0),
        type(0)
    );

    override(
        \PHPUnit_Framework_TestCase::createConfiguredMock(0),
        type(0)
    );

    override(
        \PHPUnit_Framework_TestCase::createPartialMock(0),
        type(0)
    );

    override(
        \PHPUnit_Framework_TestCase::getMockForAbstractClass(0),
        type(0)
    );

    override(
        \PHPUnit_Framework_TestCase::getMock(0),
        type(0)
    );

    override(
        \PHPUnit_Framework_TestCase::getMockWithoutInvokingTheOriginalConstructor(0),
        type(0)
    );
}
