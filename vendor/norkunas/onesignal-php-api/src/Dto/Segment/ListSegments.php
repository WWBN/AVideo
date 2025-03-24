<?php

declare(strict_types=1);

namespace OneSignal\Dto\Segment;

use OneSignal\Dto\AbstractDto;

class ListSegments implements AbstractDto
{
    /**
     * @var int<0, 2147483648>|null
     */
    protected ?int $limit = null;

    /**
     * @var int<0, 2147483648>|null
     */
    protected ?int $offset = null;

    /**
     * @param int<0, 2147483648>|null $limit
     * @param int<0, 2147483648>|null $offset
     */
    public function __construct(?int $limit = null, ?int $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * @param int<0, 2147483648> $limit
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param int<0, 2147483648> $offset
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'limit' => $this->limit,
            'offset' => $this->offset,
        ]);
    }
}
