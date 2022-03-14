<?php

declare(strict_types=1);

namespace OneSignal\Resolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class AppResolver implements ResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(array $data): array
    {
        return (new OptionsResolver())
            ->setRequired('name')
            ->setAllowedTypes('name', 'string')
            ->setDefined('apns_env')
            ->setAllowedTypes('apns_env', 'string')
            ->setAllowedValues('apns_env', ['sandbox', 'production'])
            ->setDefined('apns_p12')
            ->setAllowedTypes('apns_p12', 'string')
            ->setDefined('apns_p12_password')
            ->setAllowedTypes('apns_p12_password', 'string')
            ->setDefined('gcm_key')
            ->setAllowedTypes('gcm_key', 'string')
            ->setDefined('android_gcm_sender_id')
            ->setAllowedTypes('android_gcm_sender_id', 'string')
            ->setDefined('chrome_key')
            ->setAllowedTypes('chrome_key', 'string')
            ->setDefined('safari_apns_p12')
            ->setAllowedTypes('safari_apns_p12', 'string')
            ->setDefined('chrome_web_key')
            ->setAllowedTypes('chrome_web_key', 'string')
            ->setDefined('safari_apns_p12_password')
            ->setAllowedTypes('safari_apns_p12_password', 'string')
            ->setDefined('site_name')
            ->setAllowedTypes('site_name', 'string')
            ->setDefined('safari_site_origin')
            ->setAllowedTypes('safari_site_origin', 'string')
            ->setDefined('safari_icon_16_16')
            ->setAllowedTypes('safari_icon_16_16', 'string')
            ->setDefined('safari_icon_32_32')
            ->setAllowedTypes('safari_icon_32_32', 'string')
            ->setDefined('safari_icon_64_64')
            ->setAllowedTypes('safari_icon_64_64', 'string')
            ->setDefined('safari_icon_128_128')
            ->setAllowedTypes('safari_icon_128_128', 'string')
            ->setDefined('safari_icon_256_256')
            ->setAllowedTypes('safari_icon_256_256', 'string')
            ->setDefined('chrome_web_origin')
            ->setAllowedTypes('chrome_web_origin', 'string')
            ->setDefined('chrome_web_gcm_sender_id')
            ->setAllowedTypes('chrome_web_gcm_sender_id', 'string')
            ->setDefined('chrome_web_default_notification_icon')
            ->setAllowedTypes('chrome_web_default_notification_icon', 'string')
            ->setDefined('chrome_web_sub_domain')
            ->setAllowedTypes('chrome_web_sub_domain', 'string')
            ->setDefined('organization_id')
            ->setAllowedTypes('organization_id', 'string')
            ->resolve($data);
    }
}
