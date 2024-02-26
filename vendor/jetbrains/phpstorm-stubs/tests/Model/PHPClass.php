<?php
declare(strict_types=1);

namespace StubTests\Model;

use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\Node\Stmt\Class_;
use ReflectionClass;
use stdClass;

class PHPClass extends BasePHPClass
{
    public false|string|null $parentClass = null;
    public array $interfaces = [];
    /** @var PHPProperty[] */
    public array $properties = [];

    /**
     * @param ReflectionClass $reflectionObject
     * @return $this
     */
    public function readObjectFromReflection($reflectionObject): static
    {
        $this->name = $reflectionObject->getName();
        $parent = $reflectionObject->getParentClass();
        if ($parent !== false) {
            $this->parentClass = $parent->getName();
        }
        $this->interfaces = $reflectionObject->getInterfaceNames();

        foreach ($reflectionObject->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() !== $this->name) {
                continue;
            }
            $this->methods[$method->name] = (new PHPMethod())->readObjectFromReflection($method);
        }

        foreach ($reflectionObject->getReflectionConstants() as $constant) {
            if ($constant->getDeclaringClass()->getName() !== $this->name) {
                continue;
            }
            $this->constants[$constant->name] = (new PHPConst())->readObjectFromReflection($constant);
        }

        foreach ($reflectionObject->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() !== $this->name) {
                continue;
            }
            $this->properties[$property->name] = (new PHPProperty())->readObjectFromReflection($property);
        }
        return $this;
    }

    /**
     * @param Class_ $node
     * @return $this
     */
    public function readObjectFromStubNode($node): static
    {
        $this->name = $this->getFQN($node);
        $this->collectTags($node);
        if (!empty($node->extends)) {
            $this->parentClass = '';
            foreach ($node->extends->parts as $part) {
                $this->parentClass .= "\\$part";
            }
            $this->parentClass = ltrim($this->parentClass, "\\");
        }
        if (!empty($node->implements)) {
            foreach ($node->implements as $interfaceObject) {
                $interfaceFQN = '';
                foreach ($interfaceObject->parts as $interface) {
                    $interfaceFQN .= "\\$interface";
                }
                $this->interfaces[] = ltrim($interfaceFQN, "\\");
            }
        }
        foreach ($node->getProperties() as $property) {
            $propertyName = $property->props[0]->name->name;
            $this->properties[$propertyName] = (new PHPProperty($this->name))->readObjectFromStubNode($property);
        }
        if ($node->getDocComment() !== null) {
            $docBlock = DocBlockFactory::createInstance()->create($node->getDocComment()->getText());
            /** @var PropertyRead[] $properties */
            $properties = array_merge($docBlock->getTagsByName("property-read"),
                $docBlock->getTagsByName("property"));
            foreach ($properties as $property) {
                $propertyName = $property->getVariableName();
                assert($propertyName !== "", "@property name is empty in class $this->name");
                $newProperty = new PHPProperty($this->name);
                $newProperty->is_static = false;
                $newProperty->access = "public";
                $newProperty->name = $propertyName;
                $newProperty->parentName = $this->name;
                $newProperty->type = "" . $property->getType();
                assert(!array_key_exists($propertyName, $this->properties),
                    "Property '$propertyName' is already declared in class '$this->name'");
                $this->properties[$propertyName] = $newProperty;
            }
        }

        return $this;
    }

    public function readMutedProblems(stdClass|array $jsonData): void
    {
        foreach ($jsonData as $class) {
            if ($class->name === $this->name) {
                if (!empty($class->problems)) {
                    foreach ($class->problems as $problem) {
                        $this->mutedProblems[] = match ($problem) {
                            'wrong parent' => StubProblemType::WRONG_PARENT,
                            'wrong interface' => StubProblemType::WRONG_INTERFACE,
                            'missing class' => StubProblemType::STUB_IS_MISSED,
                            default => -1,
                        };
                    }
                }
                if (!empty($class->methods)) {
                    foreach ($this->methods as $method) {
                        $method->readMutedProblems($class->methods);
                    }
                }
                if (!empty($class->constants)) {
                    foreach ($this->constants as $constant) {
                        $constant->readMutedProblems($class->constants);
                    }
                }
                return;
            }
        }
    }
}
