<?php
declare(strict_types=1);

namespace StubTests\Parsers\Visitors;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;
use RuntimeException;
use SplFileInfo;
use StubTests\Parsers\StubParser;

class MetaOverrideFunctionsParser extends NodeVisitorAbstract
{
    private const OVERRIDE_FUNCTION = 'override';

    /**
     * @var string[]
     */
    public array $overridenFunctions;

    public function __construct()
    {
        $this->overridenFunctions = [];
        StubParser::processStubs($this, null,
            fn(SplFileInfo $file): bool => $file->getFilename() === '.phpstorm.meta.php');
    }

    /**
     * @param Node $node
     * @return void
     * @throws RuntimeException
     */
    public function enterNode(Node $node): void
    {
        if ($node instanceof Node\Expr\FuncCall && (string)$node->name === self::OVERRIDE_FUNCTION) {
            $args = $node->args;
            if (count($args) < 2) {
                throw new RuntimeException('Expected at least 2 arguments for override call');
            }
            $this->overridenFunctions[] = self::getOverrideFunctionName($args[0]);
        }
    }

    private static function getOverrideFunctionName($param): string
    {
        $paramValue = $param->value;
        $targetFunction = null;
        if ($paramValue instanceof Expr\StaticCall) {
            $targetFunction = $paramValue->class . '::' . $paramValue->name;
        } else {
            $targetFunction = (string)$paramValue->name;
        }
        return $targetFunction;
    }
}
