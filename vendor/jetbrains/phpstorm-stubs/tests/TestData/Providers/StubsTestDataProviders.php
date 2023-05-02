<?php
declare(strict_types=1);

namespace StubTests\TestData\Providers;

use Generator;

class StubsTestDataProviders
{
    public static function stubClassConstantProvider(): ?Generator
    {
        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getClasses() as $class) {
            foreach ($class->constants as $constant) {
                yield "constant {$class->name}::{$constant->name}" => [$class->name, $constant];
            }
        }

        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getInterfaces() as $interfaceName => $interface) {
            foreach ($interface->constants as $constantName => $constant) {
                yield "constant {$interfaceName}::{$constantName}" => [$interfaceName, $constant];
            }
        }
    }

    public static function stubConstantProvider(): ?Generator
    {
        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getConstants() as $constantName => $constant) {
            yield "constant {$constantName}" => [$constant];
        }
    }

    public static function stubFunctionProvider(): ?Generator
    {
        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getFunctions() as $functionName => $function) {
            yield "function {$functionName}" => [$function];
        }
    }

    public static function stubClassProvider(): ?Generator
    {
        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getClasses() as $class) {
            yield "class {$class->name}" => [$class];
        }

        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getInterfaces() as $interface) {
            yield "interface {$interface->name}" => [$interface];
        }
    }

    public static function coreStubMethodProvider(): ?Generator
    {
        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getCoreClasses() as $className => $class) {
            foreach ($class->methods as $methodName => $method) {
                yield "method {$className}::{$methodName}" => [$methodName, $method];
            }
        }

        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getCoreInterfaces() as $interfaceName => $interface) {
            foreach ($interface->methods as $methodName => $method) {
                yield "method {$interfaceName}::{$methodName}" => [$methodName, $method];
            }
        }
    }

    public static function stubMethodProvider(): ?Generator
    {
        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getClasses() as $className => $class) {
            foreach ($class->methods as $methodName => $method) {
                yield "method {$className}::{$methodName}" => [$methodName, $method];
            }
        }

        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getInterfaces() as $interfaceName => $interface) {
            foreach ($interface->methods as $methodName => $method) {
                yield "method {$interfaceName}::{$methodName}" => [$methodName, $method];
            }
        }
    }
}
