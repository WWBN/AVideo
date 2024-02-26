<?php
declare(strict_types=1);

namespace StubTests;

use PHPUnit\Framework\TestCase;
use StubTests\Model\PHPMethod;
use StubTests\Model\StubProblemType;
use StubTests\Parsers\Visitors\MetaOverrideFunctionsParser;
use StubTests\TestData\Providers\PhpStormStubsSingleton;
use StubTests\TestData\Providers\ReflectionStubsSingleton;
use function array_filter;
use function array_pop;

class StubsMetaInternalTagTest extends TestCase
{
    private static array $overridenFunctionsInMeta;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$overridenFunctionsInMeta = (new MetaOverrideFunctionsParser())->overridenFunctions;
    }

    public function testFunctionInternalMetaTag(): void
    {
        $functions = PhpStormStubsSingleton::getPhpStormStubs()->getFunctions();
        foreach ($functions as $function) {
            if ($function->hasInternalMetaTag) {
                $reflectionFunctions = array_filter(ReflectionStubsSingleton::getReflectionStubs()->getFunctions(),
                    fn($refFunction) => $refFunction->name === $function->name);
                $reflectionFunction = array_pop($reflectionFunctions);
                if ($reflectionFunction->hasMutedProblem(StubProblemType::ABSENT_IN_META)) {
                    static::markTestSkipped('function intentionally not added to meta');
                } else {
                    self::checkInternalMetaInOverride($function->name);
                }
            }
        }
    }

    public function testMethodsInternalMetaTag(): void
    {
        foreach (PhpStormStubsSingleton::getPhpStormStubs()->getClasses() as $className => $class) {
            foreach ($class->methods as $methodName => $method) {
                if ($method->hasInternalMetaTag) {
                    $refClass = ReflectionStubsSingleton::getReflectionStubs()->getClass($className);
                    if ($refClass !== null){
                        $reflectionMethods = array_filter($refClass->methods,
                        fn($refMethod) => $refMethod->name === $methodName);
                        /** @var PHPMethod $reflectionMethod */
                        $reflectionMethod = array_pop($reflectionMethods);
                        if ($reflectionMethod->hasMutedProblem(StubProblemType::ABSENT_IN_META)) {
                            static::markTestSkipped('method intentionally not added to meta');
                        } else {
                            self::checkInternalMetaInOverride($className . '::' . $methodName);
                        }
                    }
                } else {
                    $this->expectNotToPerformAssertions();
                }
            }
        }
    }

    private static function checkInternalMetaInOverride(string $elementName): void
    {
        self::assertContains($elementName, self::$overridenFunctionsInMeta,
            "$elementName contains @meta in phpdoc but isn't added to 'override()' functions in meta file");
    }
}
