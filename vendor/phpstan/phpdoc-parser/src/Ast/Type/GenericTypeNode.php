<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;

class GenericTypeNode implements TypeNode
{

	use NodeAttributes;

	/** @var IdentifierTypeNode */
	public $type;

	/** @var TypeNode[] */
	public $genericTypes;

	public function __construct(IdentifierTypeNode $type, array $genericTypes)
	{
		$this->type = $type;
		$this->genericTypes = $genericTypes;
	}


	public function __toString(): string
	{
		return $this->type . '<' . implode(', ', $this->genericTypes) . '>';
	}

}
