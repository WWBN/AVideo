<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base Test Case
 * 
 * This class provides common functionality for all tests in the project.
 * Extend this class instead of PHPUnit\Framework\TestCase to get additional utilities.
 * 
 * Features:
 * - Setup/teardown hooks for test isolation
 * - Helper methods for mocking
 * - Common assertions
 * - Test data helpers
 * 
 * @example
 * class MyTest extends Tests\TestCase {
 *     public function testSomething() {
 *         $mock = $this->createMockObject('ClassName');
 *         // Your test code here
 *     }
 * }
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Setup method called before each test
     * Override this in your test class if you need specific setup
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset global state if needed
        $this->resetGlobalState();
    }

    /**
     * Teardown method called after each test
     * Override this in your test class if you need specific cleanup
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up any test artifacts
        $this->cleanupTestArtifacts();
    }

    /**
     * Reset global variables to a clean state
     * 
     * This prevents state bleeding between tests
     */
    protected function resetGlobalState(): void
    {
        // Reset $_REQUEST, $_POST, $_GET, $_SERVER if needed for API tests
        $_REQUEST = [];
        $_POST = [];
        $_GET = [];
    }

    /**
     * Clean up any files or resources created during tests
     */
    protected function cleanupTestArtifacts(): void
    {
        // Override in subclasses if needed
    }

    /**
     * Create a mock object with fluent method configuration
     * 
     * @param string $className The class to mock
     * @param array $methods Methods to mock (empty = all methods)
     * @return \PHPUnit\Framework\MockObject\MockObject
     * 
     * @example
     * $mock = $this->createMockObject('Video', ['save', 'setDuration']);
     * $mock->method('save')->willReturn(true);
     */
    protected function createMockObject(string $className, array $methods = [])
    {
        $builder = $this->getMockBuilder($className)
            ->disableOriginalConstructor();
        
        if (!empty($methods)) {
            $builder->onlyMethods($methods);
        }
        
        return $builder->getMock();
    }

    /**
     * Create a partial mock that only mocks specific methods
     * 
     * @param string $className The class to mock
     * @param array $methods Methods to mock
     * @param array $constructorArgs Arguments for the constructor
     * @return \PHPUnit\Framework\MockObject\MockObject
     * 
     * @example
     * $mock = $this->createPartialMock('Video', ['save'], [123]);
     */
    protected function createPartialMockObject(string $className, array $methods, array $constructorArgs = [])
    {
        $builder = $this->getMockBuilder($className)
            ->onlyMethods($methods);
        
        if (!empty($constructorArgs)) {
            $builder->setConstructorArgs($constructorArgs);
        }
        
        return $builder->getMock();
    }

    /**
     * Assert that an array has specific keys
     * 
     * @param array $expected Expected keys
     * @param array $actual Actual array
     * @param string $message Optional message
     * 
     * @example
     * $this->assertArrayHasKeys(['id', 'name'], $result);
     */
    protected function assertArrayHasKeys(array $expected, array $actual, string $message = ''): void
    {
        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $actual, $message ?: "Array missing key: {$key}");
        }
    }

    /**
     * Assert that a value is a successful response array
     * 
     * @param array $response The response to check
     * @param string $message Optional message
     * 
     * @example
     * $this->assertSuccessResponse($result);
     */
    protected function assertSuccessResponse(array $response, string $message = ''): void
    {
        $this->assertArrayHasKey('success', $response, $message ?: 'Response missing success key');
        $this->assertTrue($response['success'], $message ?: 'Response success is not true');
    }

    /**
     * Assert that a value is an error response array
     * 
     * @param array $response The response to check
     * @param string $expectedError Optional expected error message
     * @param string $message Optional message
     * 
     * @example
     * $this->assertErrorResponse($result, 'Hook not allowed');
     */
    protected function assertErrorResponse(array $response, string $expectedError = null, string $message = ''): void
    {
        $this->assertArrayHasKey('error', $response, $message ?: 'Response missing error key');
        
        if ($expectedError !== null) {
            $this->assertEquals($expectedError, $response['error'], $message ?: 'Error message does not match');
        }
    }

    /**
     * Invoke a private or protected method on an object
     * 
     * Useful for testing private methods in isolation
     * 
     * @param object $object The object instance
     * @param string $methodName The method name
     * @param array $parameters Method parameters
     * @return mixed The method's return value
     * 
     * @example
     * $result = $this->invokePrivateMethod($obj, 'privateMethod', [1, 2]);
     */
    protected function invokePrivateMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Get a private or protected property value from an object
     * 
     * @param object $object The object instance
     * @param string $propertyName The property name
     * @return mixed The property value
     * 
     * @example
     * $value = $this->getPrivateProperty($obj, 'privateVar');
     */
    protected function getPrivateProperty($object, string $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        
        return $property->getValue($object);
    }

    /**
     * Set a private or protected property value on an object
     * 
     * @param object $object The object instance
     * @param string $propertyName The property name
     * @param mixed $value The value to set
     * 
     * @example
     * $this->setPrivateProperty($obj, 'privateVar', 'new value');
     */
    protected function setPrivateProperty($object, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
