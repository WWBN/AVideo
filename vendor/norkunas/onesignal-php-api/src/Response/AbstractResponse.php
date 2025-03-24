<?php

declare(strict_types=1);

namespace OneSignal\Response;

interface AbstractResponse
{
    /**
     * @param array<mixed> $request
     */
    public static function makeFromResponse(array $request): self;
}
