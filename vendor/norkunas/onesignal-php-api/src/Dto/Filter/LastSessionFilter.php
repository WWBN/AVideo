<?php

declare(strict_types=1);

namespace OneSignal\Dto\Filter;

final class LastSessionFilter extends AbstractFilter
{
    /**
     * @var self::GT|self::LT
     */
    protected string $relation;

    /**
     * @var int|float
     */
    protected $hoursAgo;

    /**
     * @param self::GT|self::LT $relation
     * @param int|float         $hoursAgo
     */
    public function __construct(string $relation, $hoursAgo)
    {
        $this->relation = $relation;
        $this->hoursAgo = $hoursAgo;
    }

    /**
     * @return array{
     *     field: 'last_session',
     *     relation: self::GT|self::LT,
     *     hours_ago: int|float
     * }
     */
    public function toArray(): array
    {
        return [
            'field' => 'last_session',
            'relation' => $this->relation,
            'hours_ago' => $this->hoursAgo,
        ];
    }
}
