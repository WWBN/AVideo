<?php

declare(strict_types=1);

namespace OneSignal\Resolver;

use OneSignal\Apps;
use OneSignal\Devices;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppOutcomesResolver implements ResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(array $data): array
    {
        return (new OptionsResolver())
            ->setDefined('outcome_names')
            ->setAllowedTypes('outcome_names', 'string[]')
            ->setDefined('outcome_time_range')
            ->setAllowedTypes('outcome_time_range', 'string')
            ->setAllowedValues('outcome_time_range', [Apps::OUTCOME_TIME_RANGE_HOUR, Apps::OUTCOME_TIME_RANGE_DAY, Apps::OUTCOME_TIME_RANGE_MONTH])
            ->setDefault('outcome_time_range', Apps::OUTCOME_TIME_RANGE_HOUR)
            ->setDefined('outcome_platforms')
            ->setAllowedTypes('outcome_platforms', 'int[]')
            ->setAllowedValues('outcome_platforms', static function (array $platforms): bool {
                $intersect = array_intersect($platforms, [
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

                return count($intersect) === count($platforms);
            })
            ->setNormalizer('outcome_platforms', static function (Options $options, array $value): string {
                return implode(',', $value);
            })
            ->setDefined('outcome_attribution')
            ->setAllowedTypes('outcome_attribution', 'string')
            ->setAllowedValues('outcome_attribution', [
                Apps::OUTCOME_ATTRIBUTION_TOTAL,
                Apps::OUTCOME_ATTRIBUTION_DIRECT,
                Apps::OUTCOME_ATTRIBUTION_INFLUENCED,
                Apps::OUTCOME_ATTRIBUTION_UNATTRIBUTED,
            ])
            ->setDefault('outcome_attribution', Apps::OUTCOME_ATTRIBUTION_TOTAL)
            ->setRequired(['outcome_names'])
            ->resolve($data);
    }
}
