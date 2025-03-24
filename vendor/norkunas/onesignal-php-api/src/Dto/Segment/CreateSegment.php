<?php

declare(strict_types=1);

namespace OneSignal\Dto\Segment;

use OneSignal\Dto\AbstractDto;
use OneSignal\Dto\Filter\AbstractFilter;

class CreateSegment implements AbstractDto
{
    /**
     * @var non-empty-string|null
     */
    protected ?string $name = null;

    /**
     * @var array<int, AbstractFilter>
     */
    protected ?array $filters = null;

    /**
     * @param non-empty-string|null           $name
     * @param array<int, AbstractFilter>|null $filters
     */
    public function __construct(?string $name = null, ?array $filters = null)
    {
        $this->name = $name;
        $this->filters = $filters;
    }

    /**
     * @param non-empty-string $name
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param list<AbstractFilter> $filters
     */
    public function filters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return array{
     *     name?: non-empty-string,
     *     filters?: array<mixed>
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'filters' => $this->filters !== null
                ? array_map(
                    static function (AbstractFilter $filter): array {
                        return $filter->toArray();
                    },
                    $this->filters
                )
                : null,
        ]);
    }
}
