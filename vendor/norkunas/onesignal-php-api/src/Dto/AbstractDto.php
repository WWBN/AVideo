<?php

declare(strict_types=1);

namespace OneSignal\Dto;

interface AbstractDto
{
    /**
     * @return array<mixed>
     */
    public function toArray(): array;
}
