<?php

declare(strict_types=1);

namespace OneSignal\Dto\Filter;

final class LanguageFilter extends AbstractFilter
{
    /**
     * @var self::EQ|self::NEQ
     */
    protected string $relation;

    protected string $value;

    /**
     * @param self::EQ|self::NEQ $relation
     */
    public function __construct(string $relation, string $value)
    {
        $this->relation = $relation;
        $this->value = $value;
    }

    /**
     * @return array{
     *     field: 'language',
     *     relation: self::EQ|self::NEQ,
     *     value: string
     * }
     */
    public function toArray(): array
    {
        return [
            'field' => 'language',
            'relation' => $this->relation,
            'value' => $this->value,
        ];
    }
}
