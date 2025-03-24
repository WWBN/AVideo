<?php

declare(strict_types=1);

namespace OneSignal\Response\Segment;

use DateTimeImmutable;
use OneSignal\Response\AbstractResponse;

final class ListSegmentsResponse implements AbstractResponse
{
    /**
     * @var non-negative-int
     */
    protected int $totalCount;

    /**
     * @var int<0, 2147483648>
     */
    protected int $offset;

    /**
     * @var int<0, 2147483648>
     */
    protected int $limit;

    /**
     * @var list<Segment>
     */
    protected array $segments;

    /**
     * @param non-negative-int   $totalCount
     * @param int<0, 2147483648> $limit
     * @param int<0, 2147483648> $offset
     * @param list<Segment>      $segments
     */
    public function __construct(int $totalCount, int $offset, int $limit, array $segments)
    {
        $this->totalCount = $totalCount;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->segments = $segments;
    }

    public static function makeFromResponse(array $response): self
    {
        $segments = array_map(
            static function (array $segment): Segment {
                return new Segment(
                    $segment['id'],
                    $segment['name'],
                    new DateTimeImmutable($segment['created_at']),
                    new DateTimeImmutable($segment['updated_at']),
                    $segment['app_id'],
                    $segment['read_only'],
                    $segment['is_active'],
                );
            },
            $response['segments']
        );

        return new static(
            $response['total_count'],
            $response['offset'],
            $response['limit'],
            $segments
        );
    }

    /**
     * @return non-negative-int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return int<0, 2147483648>
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return int<0, 2147483648>
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return list<Segment>
     */
    public function getSegments(): array
    {
        return $this->segments;
    }
}
