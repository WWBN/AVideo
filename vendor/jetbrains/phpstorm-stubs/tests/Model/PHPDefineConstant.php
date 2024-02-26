<?php
declare(strict_types=1);

namespace StubTests\Model;

use PhpParser\Node\Expr\FuncCall;
use function is_float;
use function is_string;

class PHPDefineConstant extends PHPConst
{
    /**
     * @param array $reflectionObject
     * @return $this
     */
    public function readObjectFromReflection($reflectionObject): static
    {
        if (is_string($reflectionObject[0])) {
            $this->name = utf8_encode($reflectionObject[0]);
        } else {
            $this->name = $reflectionObject[0];
        }
        $constantValue = $reflectionObject[1];
        if ($constantValue !== null) {
            if (is_resource($constantValue)) {
                $this->value = 'PHPSTORM_RESOURCE';
            } elseif (is_string($constantValue) || is_float($constantValue)) {
                $this->value = utf8_encode((string)$constantValue);
            } else {
                $this->value = $constantValue;
            }
        } else {
            $this->value = null;
        }
        return $this;
    }

    /**
     * @param FuncCall $node
     * @return $this
     */
    public function readObjectFromStubNode($node): static
    {
        $constName = $this->getConstantFQN($node, $node->args[0]->value->value);
        if (in_array($constName, ['null', 'true', 'false'])) {
            $constName = strtoupper($constName);
        }
        $this->name = $constName;
        $this->value = $this->getConstValue($node->args[1]);
        $this->collectTags($node);
        return $this;
    }
}
