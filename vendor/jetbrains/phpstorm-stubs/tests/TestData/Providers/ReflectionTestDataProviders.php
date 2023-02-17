<?php
declare(strict_types=1);

namespace StubTests\TestData\Providers;

use Generator;

class ReflectionTestDataProviders
{

    public static function constantProvider(): ?Generator
    {
        foreach (ReflectionStubsSingleton::getReflectionStubs()->getConstants() as $constant) {
            yield "constant {$constant->name}" => [$constant];
        }
    }

    public static function functionProvider(): ?Generator
    {
        foreach (ReflectionStubsSingleton::getReflectionStubs()->getFunctions() as $function) {
            yield "function {$function->name}" => [$function];
        }
    }

    public static function classProvider(): ?Generator
    {
        foreach (ReflectionStubsSingleton::getReflectionStubs()->getClasses() as $class) {
            //exclude classes from PHPReflectionParser
            if (strncmp($class->name, 'PHP', 3) !== 0) {
                yield "class {$class->name}" => [$class];
            }
        }
    }

    public static function interfaceProvider(): ?Generator
    {
        foreach (ReflectionStubsSingleton::getReflectionStubs()->getInterfaces() as $interface) {
            yield "interface {$interface->name}" => [$interface];
        }
    }
}
