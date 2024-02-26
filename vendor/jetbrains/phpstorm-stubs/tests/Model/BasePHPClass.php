<?php
declare(strict_types=1);

namespace StubTests\Model;

abstract class BasePHPClass extends BasePHPElement
{
    use PHPDocElement;

    /**
     * @var PHPMethod[]
     */
    public array $methods = [];

    /**
     * @var PHPConst[]
     */
    public array $constants = [];
}
