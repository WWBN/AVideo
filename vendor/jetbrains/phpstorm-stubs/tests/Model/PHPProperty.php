<?php
declare(strict_types=1);

namespace StubTests\Model;

use PhpParser\Node\Stmt\Property;
use ReflectionProperty;
use stdClass;

class PHPProperty extends BasePHPElement
{
    public string $type = '';
    public string $access = '';
    public bool $is_static = false;

    public function __construct(public ?string $parentName = null){
    }

    /**
     * @param ReflectionProperty $reflectionObject
     * @return $this
     */
    public function readObjectFromReflection($reflectionObject): static
    {
        $this->name = $reflectionObject->getName();
        if ($reflectionObject->isProtected()) {
            $access = 'protected';
        } elseif ($reflectionObject->isPrivate()) {
            $access = 'private';
        } else {
            $access = 'public';
        }
        $this->access = $access;
        $this->is_static = $reflectionObject->isStatic();
        $this->type = "";
        if ($reflectionObject->hasType()) {
            $reflectionNamedType = $reflectionObject->getType();
            if (isset($reflectionNamedType)){
                $this->type = $reflectionNamedType->getName();
            }
        }
        return $this;
    }

    /**
     * @param Property $node
     * @return $this
     */
    public function readObjectFromStubNode($node): static
    {
        $this->name = $node->props[0]->name->name;
        $this->is_static = $node->isStatic();
        if ($node->isProtected()) {
            $access = 'protected';
        } elseif ($node->isPrivate()) {
            $access = 'private';
        } else {
            $access = 'public';
        }
        $this->access = $access;


        $this->type = $node->type->name ?? "";

        $parentNode = $node->getAttribute('parent');
        if ($parentNode !== null){
            $this->parentName = $this->getFQN($parentNode);
        }
        return $this;
    }

    public function readMutedProblems(stdClass|array $jsonData): void
    {
        foreach ($jsonData as $property) {
            if ($property->name === $this->name && !empty($property->problems)) {
                foreach ($property->problems as $problem) {
                    $this->mutedProblems[] = match ($problem) {
                        'missing property' => StubProblemType::STUB_IS_MISSED,
                        default => -1
                    };
                }
                return;
            }
        }
    }
}
