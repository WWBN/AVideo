<?php

declare(strict_types=1);

namespace OneSignal\Dto\Filter;

final class TagFilter extends AbstractFilter
{
    public const EXISTS = 'exists';

    public const NOT_EXISTS = 'not_exists';

    public const TIME_ELAPSED_GT = 'time_elapsed_gt';

    public const TIME_ELAPSED_LT = 'time_elapsed_lt';

    /**
     * @var self::GT|self::LT|self::EQ|self::NEQ|self::EXISTS|self::NOT_EXISTS|self::TIME_ELAPSED_GT|self::TIME_ELAPSED_LT
     */
    protected string $relation;

    protected string $key;

    /**
     * @var string|int|null
     */
    protected $value;

    /**
     * @param self::GT|self::LT|self::EQ|self::NEQ|self::EXISTS|self::NOT_EXISTS|self::TIME_ELAPSED_GT|self::TIME_ELAPSED_LT $relation
     * @param string|int|null                                                                                                $value
     */
    public function __construct(string $relation, string $key, $value = null)
    {
        $this->relation = $relation;
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return array{
     *     field: 'tag',
     *     relation: self::GT|self::LT|self::EQ|self::NEQ|self::EXISTS|self::NOT_EXISTS|self::TIME_ELAPSED_GT|self::TIME_ELAPSED_LT,
     *     key: string,
     *     value: string|int|null
     * }
     */
    public function toArray(): array
    {
        return [
            'field' => 'tag',
            'relation' => $this->relation,
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
