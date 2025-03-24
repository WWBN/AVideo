<?php

declare(strict_types=1);

namespace OneSignal\Dto\Filter;

final class SessionTimeFilter extends AbstractFilter
{
    /**
     * @var self::GT|self::LT
     */
    protected string $relation;

    protected int $value;

    /**
     * @param self::GT|self::LT $relation
     */
    public function __construct(string $relation, int $value)
    {
        $this->relation = $relation;
        $this->value = $value;
    }

    /**
     * @return array{
     *     field: 'session_time',
     *     relation: self::GT|self::LT,
     *     value: int
     * }
     */
    public function toArray(): array
    {
        return [
            'field' => 'session_time',
            'relation' => $this->relation,
            'value' => $this->value,
        ];
    }
}
