<?php

declare(strict_types=1);

namespace OneSignal\Resolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceFocusResolver implements ResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(array $data): array
    {
        return (new OptionsResolver())
            ->setDefault('state', 'ping')
            ->setAllowedTypes('state', 'string')
            ->setRequired('active_time')
            ->setAllowedTypes('active_time', 'int')
            ->resolve($data);
    }
}
