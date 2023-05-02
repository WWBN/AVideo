<?php

namespace StubTests;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPUnit\Framework\TestCase;
use StubTests\Model\PHPConst;
use StubTests\Model\StubsContainer;
use StubTests\Parsers\ExpectedFunctionArgumentsInfo;
use StubTests\Parsers\MetaExpectedArgumentsCollector;
use StubTests\TestData\Providers\PhpStormStubsSingleton;

class StubsMetaExpectedArgumentsTest extends TestCase
{
    /**
     * @var ExpectedFunctionArgumentsInfo[]
     */
    private static array $expectedArguments;
    /**
     * @var string[]
     */
    private static array $registeredArgumentsSet;
    private static array $functionsFqns;
    private static array $methodsFqns;
    private static array $constantsFqns;

    public static function setUpBeforeClass(): void
    {
        $argumentsCollector = new MetaExpectedArgumentsCollector();
        self::$expectedArguments = $argumentsCollector->getExpectedArgumentsInfos();
        self::$registeredArgumentsSet = $argumentsCollector->getRegisteredArgumentsSet();
        $stubs = PhpStormStubsSingleton::getPhpStormStubs();
        self::$functionsFqns = array_map(function (Model\PHPFunction $func) {
            return self::toPresentableFqn((string)$func->name);
        }, $stubs->getFunctions());
        self::$methodsFqns = self::getMethodsFqns($stubs);
        self::$constantsFqns = self::getConstantsFqns($stubs);
    }

    private static function flatten(array $array): array
    {
        $return = [];
        array_walk_recursive($array, function ($a) use (&$return) {
            $return[$a] = $a;
        });
        return $return;
    }

    public static function getConstantsFqns(StubsContainer $stubs): array
    {
        $constants = array_map(function (PHPConst $constant) {
            return self::toPresentableFqn((string)$constant->name);
        }, $stubs->getConstants());
        foreach ($stubs->getClasses() as $class) {
            foreach ($class->constants as $classConstant) {
                $name = self::getClassMemberFqn($class->name, $classConstant->name);
                $constants[$name] = $name;
            }
        }
        return $constants;
    }

    public static function getMethodsFqns(StubsContainer $stubs): array
    {
        return self::flatten(
            array_map(function (Model\PHPClass $class) {
                return array_map(function (Model\PHPMethod $method) use ($class) {
                    return self::getClassMemberFqn($class->name, $method->name);
                }, $class->methods);
            }, $stubs->getClasses()));
    }


    public function testFunctionReferencesExists()
    {
        foreach (self::$expectedArguments as $argument) {
            $expr = $argument->getFunctionReference();
            if ($expr instanceof FuncCall) {
                $fqn = self::toPresentableFqn($expr->name);
                self::assertArrayHasKey($fqn, self::$functionsFqns, "Can't resolve function " . $fqn);
            } else if ($expr instanceof StaticCall) {
                if ((string)$expr->name !== '__construct') {
                    $fqn = self::getClassMemberFqn($expr->class, $expr->name);
                    self::assertArrayHasKey($fqn, self::$methodsFqns, "Can't resolve method " . $fqn);
                }
            } else if ($expr !== null) {
                self::fail('First argument should be function reference or method reference, got: ' . get_class($expr));
            }
        }
    }

    public function testConstantsExists()
    {
        foreach (self::$expectedArguments as $argument) {
            $expectedArguments = $argument->getExpectedArguments();
            self::assertNotEmpty($expectedArguments, 'Expected arguments should not be empty for ' . $argument);
            foreach ($expectedArguments as $constantReference) {
                if ($constantReference instanceof ClassConstFetch) {
                    $fqn = self::getClassMemberFqn($constantReference->class, $constantReference->name);
                    self::assertArrayHasKey($fqn, self::$constantsFqns, "Can't resolve class constant " . $fqn);
                } else if ($constantReference instanceof ConstFetch) {
                    $fqn = self::toPresentableFqn((string)$constantReference->name);
                    self::assertArrayHasKey($fqn, self::$constantsFqns, "Can't resolve constant " . $fqn);
                }
            }
        }
    }

    public function testRegisteredArgumentsSetExists()
    {
        foreach (self::$expectedArguments as $argument) {
            $usedArgumentsSet = [];
            foreach ($argument->getExpectedArguments() as $argumentsSet) {
                if ($argumentsSet instanceof FuncCall && ((string)$argumentsSet->name) === 'argumentsSet') {
                    $args = $argumentsSet->args;
                    self::assertGreaterThanOrEqual(1, count($args), 'argumentsSet call should provide set name');
                    $name = $args[0]->value->value;
                    self::assertContains($name, self::$registeredArgumentsSet, 'Can\'t find registered argument set: ' . $name);
                    self::assertArrayNotHasKey($name, $usedArgumentsSet, $name . ' argumentsSet used more then once for ' . self::getFqn($argument->getFunctionReference()));
                    $usedArgumentsSet[$name] = $name;
                }
            }
        }
    }


    public function testStringLiteralsSingleQuoted()
    {
        foreach (self::$expectedArguments as $argument) {
            foreach ($argument->getExpectedArguments() as $literalArgument) {
                if ($literalArgument instanceof String_) {
                    self::assertEquals(String_::KIND_SINGLE_QUOTED, $literalArgument->getAttribute('kind'), 'String literals as expectedArguments should be single-quoted');
                }
            }
        }
    }

    public function testExpectedArgumentsAreUnique()
    {
        $functionsFqnsWithIndeces = [];
        foreach (self::$expectedArguments as $argument) {
            if ($argument->getIndex() < 0) {
                continue;
            }
            $functionReferenceFqn = self::getFqn($argument->getFunctionReference());
            $index = $argument->getIndex();
            if (array_key_exists($functionReferenceFqn, $functionsFqnsWithIndeces)) {
                $indices = $functionsFqnsWithIndeces[$functionReferenceFqn];
                self::assertNotContains($index, $indices, 'Expected arguments for ' . $functionReferenceFqn . ' with index ' . ((string)$index) . ' already registered');
                $indices[] = $index;
            } else {
                $functionsFqnsWithIndeces[$functionReferenceFqn] = [$index];
            }
        }
    }

    public function testExpectedReturnValuesAreUnique()
    {
        $expectedReturnValuesFunctionsFqns = [];
        foreach (self::$expectedArguments as $argument) {
            if ($argument->getIndex() >= 0 || $argument->getFunctionReference() === null) {
                continue;
            }
            $functionReferenceFqn = self::getFqn($argument->getFunctionReference());
            self::assertArrayNotHasKey($functionReferenceFqn, $expectedReturnValuesFunctionsFqns, 'Expected return values for ' . $functionReferenceFqn . ' already registered');
            $expectedReturnValuesFunctionsFqns[$functionReferenceFqn] = $functionReferenceFqn;
        }
    }

    public function testRegisteredArgumentsSetAreUnique()
    {
        $registeredArgumentsSet = [];
        foreach(self::$registeredArgumentsSet as $name) {
            self::assertArrayNotHasKey($name, $registeredArgumentsSet, 'Set with name ' . $name . ' already registered');
            $registeredArgumentsSet[$name] = $name;
        }
    }

    public function testReferencesAreAbsolute()
    {
        foreach (self::$expectedArguments as $argument) {
            $expr = $argument->getFunctionReference();
            if ($expr !== null) {
                $name = $expr instanceof StaticCall ? $expr->class : $expr->name;
                self::assertTrue($name->getAttribute('originalName')->isFullyQualified(), self::getFqn($expr) . ' should be fully qualified');
            }
        }
    }


    private static function getClassMemberFqn($className, $memberName): string
    {
        return self::toPresentableFqn($className) . '.' . $memberName;
    }

    private static function toPresentableFqn(string $name): string
    {
        if (str_starts_with($name, '\\')) {
            return substr($name, 1);
        }
        return $name;
    }

    private static function getFqn(?Expr $expr): string
    {
        return $expr instanceof StaticCall ? self::getClassMemberFqn($expr->class, $expr->name) : self::toPresentableFqn($expr->name);
    }
}
