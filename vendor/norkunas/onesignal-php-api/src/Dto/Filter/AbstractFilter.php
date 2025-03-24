<?php

declare(strict_types=1);

namespace OneSignal\Dto\Filter;

use OneSignal\Dto\AbstractDto;

abstract class AbstractFilter implements AbstractDto
{
    public const GT = '>';

    public const LT = '<';

    public const EQ = '=';

    public const NEQ = '!=';
}
