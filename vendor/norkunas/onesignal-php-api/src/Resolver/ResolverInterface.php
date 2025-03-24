<?php

declare(strict_types=1);

namespace OneSignal\Resolver;

interface ResolverInterface
{
    /**
     * Resolve options array.
     *
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public function resolve(array $data): array;
}
