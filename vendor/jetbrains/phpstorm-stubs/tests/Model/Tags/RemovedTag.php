<?php
declare(strict_types=1);

namespace StubTests\Model\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\Types\Context;

class RemovedTag extends BaseTag
{
    private const REGEX_VECTOR = '(?:\d\S*|[^\s\:]+\:\s*\$[^\$]+\$)';

    public function __construct(private ?string $version = null, Description $description = null)
    {
        $this->name = 'removed';
        $this->description = $description;
    }

    public static function create(?string $body, ?DescriptionFactory $descriptionFactory = null, ?Context $context = null): RemovedTag
    {
        if (empty($body)) {
            return new self();
        }

        $matches = [];
        if ($descriptionFactory !== null) {
            if (!preg_match('/^(' . self::REGEX_VECTOR . ')\s*(.+)?$/sux', $body, $matches)) {
                return new self(null, $descriptionFactory->create($body, $context));
            }

            return new self(
                $matches[1],
                $descriptionFactory->create($matches[2] ?? '', $context)
            );
        }
        return new self();
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function __toString(): string
    {
        return "PhpStorm internal '@removed' tag";
    }
}
