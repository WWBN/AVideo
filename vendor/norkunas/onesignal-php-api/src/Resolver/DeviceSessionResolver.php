<?php

declare(strict_types=1);

namespace OneSignal\Resolver;

use OneSignal\Devices;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceSessionResolver implements ResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(array $data): array
    {
        return (new OptionsResolver())
            ->setDefined('identifier')
            ->setAllowedTypes('identifier', 'string')
            ->setDefined('language')
            ->setAllowedTypes('language', 'string')
            ->setDefined('timezone')
            ->setAllowedTypes('timezone', 'int')
            ->setDefined('game_version')
            ->setAllowedTypes('game_version', 'string')
            ->setDefined('device_os')
            ->setAllowedTypes('device_os', 'string')
            // @todo: remove "device_model" later (this option is probably deprecated as it is removed from documentation)
            ->setDefined('device_model')
            ->setAllowedTypes('device_model', 'string')
            ->setDefined('ad_id')
            ->setAllowedTypes('ad_id', 'string')
            ->setDefined('sdk')
            ->setAllowedTypes('sdk', 'string')
            ->setDefined('tags')
            ->setAllowedTypes('tags', 'array')
            ->setDefined('device_type')
            ->setAllowedTypes('device_type', 'int')
            ->setAllowedValues('device_type', [
                Devices::IOS,
                Devices::ANDROID,
                Devices::AMAZON,
                Devices::WINDOWS_PHONE,
                Devices::WINDOWS_PHONE_MPNS,
                Devices::CHROME_APP,
                Devices::CHROME_WEB,
                Devices::WINDOWS_PHONE_WNS,
                Devices::SAFARI,
                Devices::FIREFOX,
                Devices::MACOS,
                Devices::ALEXA,
                Devices::EMAIL,
                Devices::HUAWEI,
                Devices::SMS,
            ])
            ->resolve($data);
    }
}
