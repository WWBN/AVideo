<?php

declare(strict_types=1);

namespace OneSignal\Resolver;

use OneSignal\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationHistoryResolver implements ResolverInterface
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $data): array
    {
        return (new OptionsResolver())
            ->setRequired('events')
            ->setAllowedTypes('events', 'string')
            ->setAllowedValues('events', ['sent', 'clicked'])
            ->setRequired('email')
            ->setAllowedTypes('email', 'string')
            ->setDefault('app_id', $this->config->getApplicationId())
            ->setAllowedTypes('app_id', 'string')
            ->resolve($data);
    }
}
