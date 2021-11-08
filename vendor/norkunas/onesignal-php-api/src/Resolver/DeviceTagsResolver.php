<?php

declare(strict_types=1);

namespace OneSignal\Resolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceTagsResolver implements ResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(array $data): array
    {
        return (new OptionsResolver())
            ->setDefined('tags')
            ->setAllowedTypes('tags', 'array')
            ->setRequired(['tags'])
            ->resolve($data);
    }
}
