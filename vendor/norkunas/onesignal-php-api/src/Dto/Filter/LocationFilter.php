<?php

declare(strict_types=1);

namespace OneSignal\Dto\Filter;

final class LocationFilter extends AbstractFilter
{
    protected int $radius;

    protected float $lat;

    protected float $long;

    public function __construct(int $radius, float $lat, float $long)
    {
        $this->radius = $radius;
        $this->lat = $lat;
        $this->long = $long;
    }

    /**
     * @return array{
     *     field: 'location',
     *     radius: int,
     *     lat: float,
     *     long: float
     * }
     */
    public function toArray(): array
    {
        return [
            'field' => 'location',
            'radius' => $this->radius,
            'lat' => $this->lat,
            'long' => $this->long,
        ];
    }
}
