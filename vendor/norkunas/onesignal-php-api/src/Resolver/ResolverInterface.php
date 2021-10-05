<?php

declare(strict_types=1);

namespace OneSignal\Resolver;

interface ResolverInterface
{
    /**
     * Resolve options array.
     */
    public function resolve(array $data): array;
}
