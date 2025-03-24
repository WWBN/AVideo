<?php

declare(strict_types=1);

namespace OneSignal\Dto\Filter;

final class AppVersionFilter extends AbstractFilter
{
    /**
     * @var self::GT|self::LT|self::EQ|self::NEQ
     */
    protected string $relation;

    protected string $value;

    /**
     * @param self::GT|self::LT|self::EQ|self::NEQ $relation
     */
    public function __construct(string $relation, string $value)
    {
        $this->relation = $relation;
        $this->value = $value;
    }

    /**
     * @return array{
     *     field: 'app_version',
     *     relation: self::GT|self::LT|self::EQ|self::NEQ,
     *     value: string
     * }
     */
    public function toArray(): array
    {
        return [
            'field' => 'app_version',
            'relation' => $this->relation,
            'value' => $this->value,
        ];
    }
}
