<?php
declare(strict_types=1);

namespace StubTests\Model;

use PhpParser\Node\Stmt\ClassMethod;
use ReflectionMethod;
use stdClass;

class PHPMethod extends PHPFunction
{
    public string $access;
    public bool $is_static;
    public bool $is_final;
    public string $parentName;

    /**
     * @param ReflectionMethod $reflectionObject
     * @return $this
     */
    public function readObjectFromReflection($reflectionObject): static
    {
        $this->name = $reflectionObject->name;
        $this->is_static = $reflectionObject->isStatic();
        $this->is_final = $reflectionObject->isFinal();
        foreach ($reflectionObject->getParameters() as $parameter) {
            $this->parameters[] = (new PHPParameter())->readObjectFromReflection($parameter);
        }

        if ($reflectionObject->isProtected()) {
            $access = 'protected';
        } elseif ($reflectionObject->isPrivate()) {
            $access = 'private';
        } else {
            $access = 'public';
        }
        $this->access = $access;
        return $this;
    }

    /**
     * @param ClassMethod $node
     * @return $this
     */
    public function readObjectFromStubNode($node): static
    {
        $this->parentName = $this->getFQN($node->getAttribute('parent'));
        $this->name = $node->name->name;

        $this->returnType = self::convertParsedTypeToString($node->getReturnType());
        $this->collectTags($node);
        $this->checkDeprecationTag($node);
        $this->checkReturnTag($node);

        if (strncmp($this->name, 'PS_UNRESERVE_PREFIX_', 20) === 0) {
            $this->name = substr($this->name, strlen('PS_UNRESERVE_PREFIX_'));
        }
        foreach ($node->getParams() as $parameter) {
            $this->parameters[] = (new PHPParameter())->readObjectFromStubNode($parameter);
        }

        $this->is_final = $node->isFinal();
        $this->is_static = $node->isStatic();
        if ($node->isPrivate()) {
            $this->access = 'private';
        } elseif ($node->isProtected()) {
            $this->access = 'protected';
        } else {
            $this->access = 'public';
        }
        return $this;
    }

    public function readMutedProblems(stdClass|array $jsonData): void
    {
        foreach ($jsonData as $method) {
            if ($method->name === $this->name) {
                if (!empty($method->problems)) {
                    foreach ($method->problems as $problem) {
                        $this->mutedProblems[] = match ($problem) {
                            'parameter mismatch' => StubProblemType::FUNCTION_PARAMETER_MISMATCH,
                            'missing method' => StubProblemType::STUB_IS_MISSED,
                            'deprecated method' => StubProblemType::FUNCTION_IS_DEPRECATED,
                            'absent in meta' => StubProblemType::ABSENT_IN_META,
                            'wrong access' => StubProblemType::FUNCTION_ACCESS,
                            default => -1
                        };
                    }
                }
                if (!empty($method->parameters)) {
                    foreach ($this->parameters as $parameter) {
                        $parameter->readMutedProblems($method->parameters);
                    }
                }
                return;
            }
        }
    }
}
