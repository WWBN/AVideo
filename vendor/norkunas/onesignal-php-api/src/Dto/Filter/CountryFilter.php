<?php

declare(strict_types=1);

namespace OneSignal\Dto\Filter;

final class CountryFilter extends AbstractFilter
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return array{
     *     field: 'country',
     *     relation: self::EQ,
     *     value: string
     * }
     */
    public function toArray(): array
    {
        return [
            'field' => 'country',
            'relation' => self::EQ,
            'value' => $this->value,
        ];
    }
}
