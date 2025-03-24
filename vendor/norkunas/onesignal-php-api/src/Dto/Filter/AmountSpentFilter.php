<?php

declare(strict_types=1);

namespace OneSignal\Dto\Filter;

final class AmountSpentFilter extends AbstractFilter
{
    public const GT = '>';

    public const LT = '<';

    public const EQ = '=';

    /**
     * @var self::GT|self::LT|self::EQ
     */
    protected string $relation;

    /**
     * @var int|float
     */
    protected $value;

    /**
     * @param self::GT|self::LT|self::EQ $relation
     * @param int|float                  $value
     */
    public function __construct(string $relation, $value)
    {
        $this->relation = $relation;
        $this->value = $value;
    }

    /**
     * @return array{
     *     field: 'amount_spent',
     *     relation: self::GT|self::LT|self::EQ,
     *     value: int|float
     * }
     */
    public function toArray(): array
    {
        return [
            'field' => 'amount_spent',
            'relation' => $this->relation,
            'value' => $this->value,
        ];
    }
}
