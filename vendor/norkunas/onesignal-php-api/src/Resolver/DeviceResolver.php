<?php

declare(strict_types=1);

namespace OneSignal\Resolver;

use OneSignal\Config;
use OneSignal\Devices;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceResolver implements ResolverInterface
{
    private $config;
    private $isNewDevice;

    public function __construct(Config $config, bool $isNewDevice)
    {
        $this->config = $config;
        $this->isNewDevice = $isNewDevice;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $data): array
    {
        $resolver = (new OptionsResolver())
            ->setDefined('identifier')
            ->setAllowedTypes('identifier', 'string')
            ->setDefined('identifier_auth_hash')
            ->setAllowedTypes('identifier_auth_hash', 'string')
            ->setDefined('language')
            ->setAllowedTypes('language', 'string')
            ->setDefined('timezone')
            ->setAllowedTypes('timezone', 'int')
            ->setDefined('game_version')
            ->setAllowedTypes('game_version', 'string')
            ->setDefined('device_model')
            ->setAllowedTypes('device_model', 'string')
            ->setDefined('device_os')
            ->setAllowedTypes('device_os', 'string')
            ->setDefined('ad_id')
            ->setAllowedTypes('ad_id', 'string')
            ->setDefined('sdk')
            ->setAllowedTypes('sdk', 'string')
            ->setDefined('session_count')
            ->setAllowedTypes('session_count', 'int')
            ->setDefined('tags')
            ->setAllowedTypes('tags', 'array')
            ->setDefined('amount_spent')
            ->setAllowedTypes('amount_spent', 'float')
            ->setDefined('created_at')
            ->setAllowedTypes('created_at', 'int')
            ->setDefined('playtime')
            ->setAllowedTypes('playtime', 'int')
            ->setDefined('badge_count')
            ->setAllowedTypes('badge_count', 'int')
            ->setDefined('last_active')
            ->setAllowedTypes('last_active', 'int')
            ->setDefined('notification_types')
            ->setAllowedTypes('notification_types', 'int')
            ->setAllowedValues('notification_types', [1, -2])
            ->setDefined('test_type')
            ->setAllowedTypes('test_type', 'int')
            ->setAllowedValues('test_type', [1, 2])
            ->setDefined('long')
            ->setAllowedTypes('long', 'double')
            ->setDefined('lat')
            ->setAllowedTypes('lat', 'double')
            ->setDefined('country')
            ->setAllowedTypes('country', 'string')
            ->setDefined('external_user_id')
            ->setAllowedTypes('external_user_id', 'string')
            ->setDefined('external_user_id_auth_hash')
            ->setAllowedTypes('external_user_id_auth_hash', 'string')
            ->setDefault('app_id', $this->config->getApplicationId())
            ->setAllowedTypes('app_id', 'string');

        if ($this->isNewDevice) {
            $resolver
                ->setRequired('device_type')
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
                ]);
        } else {
            $resolver
                ->setDefined('ip')
                ->setAllowedTypes('ip', 'string')
                ->setAllowedValues('ip', static function (string $ip): bool {
                    return (bool) filter_var($ip, FILTER_VALIDATE_IP);
                });
        }

        return $resolver->resolve($data);
    }

    public function setIsNewDevice(bool $isNewDevice): void
    {
        $this->isNewDevice = $isNewDevice;
    }

    public function getIsNewDevice(): bool
    {
        return $this->isNewDevice;
    }
}
