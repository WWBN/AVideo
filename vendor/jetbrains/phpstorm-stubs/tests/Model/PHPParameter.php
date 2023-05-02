<?php
declare(strict_types=1);

namespace StubTests\Model;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\UnionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use stdClass;

class PHPParameter extends BasePHPElement
{
    public string $type = '';
    public bool $is_vararg = false;
    public bool $is_passed_by_ref = false;

    /**
     * @param ReflectionParameter $reflectionObject
     * @return $this
     */
    public function readObjectFromReflection($reflectionObject): static
    {
        $this->name = $reflectionObject->name;
        $this->type = self::convertReflectionTypeToString($reflectionObject->getType());
        $this->is_vararg = $reflectionObject->isVariadic();
        $this->is_passed_by_ref = $reflectionObject->isPassedByReference();
        return $this;
    }

    /**
     * @param Param $node
     * @return $this
     */
    public function readObjectFromStubNode($node): static
    {
        // #[LanguageLevelTypeAware(["8.0" => "OpenSSLCertificate|string"], default: "resource|string")]
        $this->name = $node->var->name;

        $typeFromAttribute = self::findTypeFromAttribute($node->attrGroups);
        if ($typeFromAttribute != null) {
            $this->type = $typeFromAttribute;
        } else {
            $this->type = self::convertParsedTypeToString($node->type);
        }
        if($node->default instanceof Expr\ConstFetch && $node->default->name->parts[0] === "null"){
            $this->type .= "|null";
        }

        $this->is_vararg = $node->variadic;
        $this->is_passed_by_ref = $node->byRef;
        return $this;
    }

    public function readMutedProblems(stdClass|array $jsonData): void
    {
        foreach ($jsonData as $parameter) {
            if ($parameter->name === $this->name && !empty($parameter->problems)) {
                foreach ($parameter->problems as $problem) {
                    $this->mutedProblems[] = match ($problem) {
                        'parameter type mismatch' => StubProblemType::PARAMETER_TYPE_MISMATCH,
                        'parameter reference' => StubProblemType::PARAMETER_REFERENCE,
                        'parameter vararg' => StubProblemType::PARAMETER_VARARG,
                        'has scalar typehint' => StubProblemType::PARAMETER_HAS_SCALAR_TYPEHINT,
                        'parameter name mismatch' => StubProblemType::PARAMETER_NAME_MISMATCH,
                        default => -1
                    };
                }
                return;
            }
        }
    }
}
