<?php
declare(strict_types=1);

namespace StubTests\Model;

use PhpParser\Node\Const_;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeAbstract;
use ReflectionClassConstant;
use stdClass;

class PHPConst extends BasePHPElement
{
    use PHPDocElement;

    public ?string $parentName = null;
    public bool|int|string|float|null $value;

    /**
     * @param ReflectionClassConstant $reflectionObject
     * @return $this
     */
    public function readObjectFromReflection($reflectionObject): static
    {
        $this->name = $reflectionObject->name;
        $this->value = $reflectionObject->getValue();
        return $this;
    }

    /**
     * @param Const_ $node
     * @return $this
     */
    public function readObjectFromStubNode($node): static
    {
        $this->name = $this->getConstantFQN($node, $node->name->name);
        $this->value = $this->getConstValue($node);
        $this->collectTags($node);
        $parentNode = $node->getAttribute('parent');
        if ($parentNode instanceof ClassConst) {
            $this->parentName = $this->getFQN($parentNode->getAttribute('parent'));
        }
        return $this;
    }

    protected function getConstValue($node): int|string|null|bool|float
    {
        if (in_array('value', $node->value->getSubNodeNames(), true)) {
            return $node->value->value;
        }
        if (in_array('expr', $node->value->getSubNodeNames(), true)) {
            if ($node->value instanceof UnaryMinus) {
                return -$node->value->expr->value;
            }
            return $node->value->expr->value;
        }
        if (in_array('name', $node->value->getSubNodeNames(), true)) {
            $value = $node->value->name->parts[0] ?? $node->value->name->name;
            return $value === 'null' ? null : $value;
        }
        return null;
    }

    protected function getConstantFQN(NodeAbstract $node, string $nodeName): string
    {
        $namespace = '';
        $parentParentNode = $node->getAttribute('parent')->getAttribute('parent');
        if ($parentParentNode instanceof Namespace_ && !empty($parentParentNode->name)) {
            $namespace = '\\' . implode('\\', $parentParentNode->name->parts) . '\\';
        }

        return $namespace . $nodeName;
    }

    public function readMutedProblems(stdClass|array $jsonData): void
    {
        foreach ($jsonData as $constant) {
            if ($constant->name === $this->name && !empty($constant->problems)) {
                foreach ($constant->problems as $problem) {
                    $this->mutedProblems[] = match ($problem) {
                        'wrong value' => StubProblemType::WRONG_CONSTANT_VALUE,
                        'missing constant' => StubProblemType::STUB_IS_MISSED,
                        default => -1
                    };
                }
                return;
            }
        }
    }
}
