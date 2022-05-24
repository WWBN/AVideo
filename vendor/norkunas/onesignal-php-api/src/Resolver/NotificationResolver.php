<?php

declare(strict_types=1);

namespace OneSignal\Resolver;

use DateTimeInterface;
use OneSignal\Config;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationResolver implements ResolverInterface
{
    public const SEND_AFTER_FORMAT = 'Y-m-d H:i:sO';
    public const DELIVERY_TIME_OF_DAY_FORMAT = 'g:iA';

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
            ->setDefined('name')
            ->setAllowedTypes('name', 'string')
            ->setDefined('contents')
            ->setAllowedTypes('contents', 'array')
            ->setDefined('headings')
            ->setAllowedTypes('headings', 'array')
            ->setDefined('subtitle')
            ->setAllowedTypes('subtitle', 'array')
            ->setDefined('isIos')
            ->setAllowedTypes('isIos', 'bool')
            ->setDefined('isAndroid')
            ->setAllowedTypes('isAndroid', 'bool')
            ->setDefined('isWP')
            ->setAllowedTypes('isWP', 'bool')
            ->setDefined('isWP_WNS')
            ->setAllowedTypes('isWP_WNS', 'bool')
            ->setDefined('isAdm')
            ->setAllowedTypes('isAdm', 'bool')
            ->setDefined('isChrome')
            ->setAllowedTypes('isChrome', 'bool')
            ->setDefined('isChromeWeb')
            ->setAllowedTypes('isChromeWeb', 'bool')
            ->setDefined('isFirefox')
            ->setAllowedTypes('isFirefox', 'bool')
            ->setDefined('isSafari')
            ->setAllowedTypes('isSafari', 'bool')
            ->setDefined('isAnyWeb')
            ->setAllowedTypes('isAnyWeb', 'bool')
            ->setDefined('included_segments')
            ->setAllowedTypes('included_segments', 'array')
            ->setDefined('excluded_segments')
            ->setAllowedTypes('excluded_segments', 'array')
            ->setDefined('include_player_ids')
            ->setAllowedTypes('include_player_ids', 'array')
            ->setDefined('include_ios_tokens')
            ->setAllowedTypes('include_ios_tokens', 'array')
            ->setDefined('include_android_reg_ids')
            ->setAllowedTypes('include_android_reg_ids', 'array')
            ->setDefined('include_external_user_ids')
            ->setAllowedTypes('include_external_user_ids', 'array')
            ->setDefined('channel_for_external_user_ids')
            ->setAllowedTypes('channel_for_external_user_ids', 'string')
            ->setAllowedValues('channel_for_external_user_ids', ['push', 'email'])
            ->setDefined('include_email_tokens')
            ->setAllowedTypes('include_email_tokens', 'array')
            ->setDefined('include_wp_uris')
            ->setAllowedTypes('include_wp_uris', 'array')
            ->setDefined('include_wp_wns_uris')
            ->setAllowedTypes('include_wp_wns_uris', 'array')
            ->setDefined('include_amazon_reg_ids')
            ->setAllowedTypes('include_amazon_reg_ids', 'array')
            ->setDefined('include_chrome_reg_ids')
            ->setAllowedTypes('include_chrome_reg_ids', 'array')
            ->setDefined('include_chrome_web_reg_ids')
            ->setAllowedTypes('include_chrome_web_reg_ids', 'array')
            ->setDefined('app_ids')
            ->setAllowedTypes('app_ids', 'array')
            ->setDefined('filters')
            ->setAllowedTypes('filters', 'array')
            ->setNormalizer('filters', function (Options $options, array $values) {
                return $this->normalizeFilters($options, $values);
            })
            ->setDefined('ios_badgeType')
            ->setAllowedTypes('ios_badgeType', 'string')
            ->setAllowedValues('ios_badgeType', ['None', 'SetTo', 'Increase'])
            ->setDefined('ios_badgeCount')
            ->setAllowedTypes('ios_badgeCount', 'int')
            ->setDefined('ios_sound')
            ->setAllowedTypes('ios_sound', 'string')
            ->setDefined('android_sound')
            ->setAllowedTypes('android_sound', 'string')
            ->setDefined('adm_sound')
            ->setAllowedTypes('adm_sound', 'string')
            ->setDefined('wp_sound')
            ->setAllowedTypes('wp_sound', 'string')
            ->setDefined('wp_wns_sound')
            ->setAllowedTypes('wp_wns_sound', 'string')
            ->setDefined('data')
            ->setAllowedTypes('data', 'array')
            ->setDefined('buttons')
            ->setAllowedTypes('buttons', 'array')
            ->setNormalizer('buttons', function (Options $options, array $values) {
                return $this->normalizeButtons($values);
            })
            ->setDefined('android_channel_id')
            ->setAllowedTypes('android_channel_id', 'string')
            ->setDefined('existing_android_channel_id')
            ->setAllowedTypes('existing_android_channel_id', 'string')
            ->setDefined('android_background_layout')
            ->setAllowedTypes('android_background_layout', 'array')
            ->setAllowedValues('android_background_layout', function (array $layouts) {
                return $this->filterAndroidBackgroundLayout($layouts);
            })
            ->setDefined('small_icon')
            ->setAllowedTypes('small_icon', 'string')
            ->setDefined('large_icon')
            ->setAllowedTypes('large_icon', 'string')
            ->setDefined('ios_attachments')
            ->setAllowedTypes('ios_attachments', 'array')
            ->setAllowedValues('ios_attachments', function (array $attachments) {
                return $this->filterIosAttachments($attachments);
            })
            ->setDefined('big_picture')
            ->setAllowedTypes('big_picture', 'string')
            ->setDefined('adm_small_icon')
            ->setAllowedTypes('adm_small_icon', 'string')
            ->setDefined('adm_large_icon')
            ->setAllowedTypes('adm_large_icon', 'string')
            ->setDefined('adm_big_picture')
            ->setAllowedTypes('adm_big_picture', 'string')
            ->setDefined('web_buttons')
            ->setAllowedTypes('web_buttons', 'array')
            ->setAllowedValues('web_buttons', function (array $buttons) {
                return $this->filterWebButtons($buttons);
            })
            ->setDefined('ios_category')
            ->setAllowedTypes('ios_category', 'string')
            ->setDefined('chrome_icon')
            ->setAllowedTypes('chrome_icon', 'string')
            ->setDefined('chrome_big_picture')
            ->setAllowedTypes('chrome_big_picture', 'string')
            ->setDefined('chrome_web_icon')
            ->setAllowedTypes('chrome_web_icon', 'string')
            ->setDefined('chrome_web_image')
            ->setAllowedTypes('chrome_web_image', 'string')
            ->setDefined('chrome_web_badge')
            ->setAllowedTypes('chrome_web_badge', 'string')
            ->setDefined('firefox_icon')
            ->setAllowedTypes('firefox_icon', 'string')
            ->setDefined('url')
            ->setAllowedTypes('url', 'string')
            ->setAllowedValues('url', function (string $value) {
                return $this->filterUrl($value);
            })
            ->setDefined('web_url')
            ->setAllowedTypes('web_url', 'string')
            ->setAllowedValues('web_url', function (string $value) {
                return $this->filterUrl($value);
            })
            ->setDefined('app_url')
            ->setAllowedTypes('app_url', 'string')
            ->setDefined('send_after')
            ->setAllowedTypes('send_after', DateTimeInterface::class)
            ->setNormalizer('send_after', function (Options $options, DateTimeInterface $value) {
                return $this->normalizeDateTime($options, $value, self::SEND_AFTER_FORMAT);
            })
            ->setDefined('delayed_option')
            ->setAllowedTypes('delayed_option', 'string')
            ->setAllowedValues('delayed_option', ['timezone', 'last-active'])
            ->setDefined('delivery_time_of_day')
            ->setAllowedTypes('delivery_time_of_day', DateTimeInterface::class)
            ->setNormalizer('delivery_time_of_day', function (Options $options, DateTimeInterface $value) {
                return $this->normalizeDateTime($options, $value, self::DELIVERY_TIME_OF_DAY_FORMAT);
            })
            ->setDefined('android_led_color')
            ->setAllowedTypes('android_led_color', 'string')
            ->setDefined('android_accent_color')
            ->setAllowedTypes('android_accent_color', 'string')
            ->setDefined('android_visibility')
            ->setAllowedTypes('android_visibility', 'int')
            ->setAllowedValues('android_visibility', [-1, 0, 1])
            ->setDefined('collapse_id')
            ->setAllowedTypes('collapse_id', 'string')
            ->setDefined('content_available')
            ->setAllowedTypes('content_available', 'bool')
            ->setDefined('mutable_content')
            ->setAllowedTypes('mutable_content', 'bool')
            ->setDefined('android_background_data')
            ->setAllowedTypes('android_background_data', 'bool')
            ->setDefined('amazon_background_data')
            ->setAllowedTypes('amazon_background_data', 'bool')
            ->setDefined('template_id')
            ->setAllowedTypes('template_id', 'string')
            ->setDefined('android_group')
            ->setAllowedTypes('android_group', 'string')
            ->setDefined('android_group_message')
            ->setAllowedTypes('android_group_message', 'array')
            ->setDefined('adm_group')
            ->setAllowedTypes('adm_group', 'string')
            ->setDefined('adm_group_message')
            ->setAllowedTypes('adm_group_message', 'array')
            ->setDefined('thread_id')
            ->setAllowedTypes('thread_id', 'string')
            ->setDefined('summary_arg')
            ->setAllowedTypes('summary_arg', 'string')
            ->setDefined('summary_arg_count')
            ->setAllowedTypes('summary_arg_count', 'int')
            ->setDefined('ttl')
            ->setAllowedTypes('ttl', 'int')
            ->setDefined('priority')
            ->setAllowedTypes('priority', 'int')
            ->setDefault('app_id', $this->config->getApplicationId())
            ->setAllowedTypes('app_id', 'string')
            ->setDefined('email_subject')
            ->setAllowedTypes('email_subject', 'string')
            ->setDefined('email_body')
            ->setAllowedTypes('email_body', 'string')
            ->setDefined('email_from_name')
            ->setAllowedTypes('email_from_name', 'string')
            ->setDefined('email_from_address')
            ->setAllowedTypes('email_from_address', 'string')
            ->setDefined('external_id')
            ->setAllowedTypes('external_id', 'string')
            ->setDefined('web_push_topic')
            ->setAllowedTypes('web_push_topic', 'string')
            ->setDefined('apns_push_type_override')
            ->setAllowedTypes('apns_push_type_override', 'string')
            ->setAllowedValues('apns_push_type_override', ['voip'])
            ->setDefined('sms_from')
            ->setAllowedTypes('sms_from', 'string')
            ->setDefined('sms_media_urls')
            ->setAllowedTypes('sms_media_urls', 'array')
            ->resolve($data);
    }

    private function normalizeFilters(Options $options, array $values): array
    {
        $filters = [];

        foreach ($values as $filter) {
            if (isset($filter['field'])) {
                $filters[] = $filter;
            } elseif (isset($filter['operator'])) {
                $filters[] = ['operator' => 'OR'];
            }
        }

        return $filters;
    }

    /**
     * @param mixed $value
     */
    private function filterUrl($value): bool
    {
        return (bool) filter_var($value, FILTER_VALIDATE_URL);
    }

    private function normalizeButtons(array $values): array
    {
        $buttons = [];

        foreach ($values as $button) {
            if (!isset($button['text'])) {
                continue;
            }

            $buttons[] = [
                'id' => $button['id'] ?? random_int(0, PHP_INT_MAX),
                'text' => $button['text'],
                'icon' => $button['icon'] ?? null,
            ];
        }

        return $buttons;
    }

    private function filterAndroidBackgroundLayout(array $layouts): bool
    {
        if (count($layouts) === 0) {
            return false;
        }

        $requiredKeys = ['image', 'headings_color', 'contents_color'];

        foreach ($layouts as $k => $v) {
            if (!is_string($v) || !in_array($k, $requiredKeys, true)) {
                return false;
            }
        }

        return true;
    }

    private function filterIosAttachments(array $attachments): bool
    {
        foreach ($attachments as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                return false;
            }
        }

        return true;
    }

    private function filterWebButtons(array $buttons): bool
    {
        $requiredKeys = ['id', 'text', 'icon', 'url'];

        foreach ($buttons as $button) {
            if (!is_array($button)) {
                return false;
            }

            if (count(array_intersect_key(array_flip($requiredKeys), $button)) !== count($requiredKeys)) {
                return false;
            }
        }

        return true;
    }

    private function normalizeDateTime(Options $options, DateTimeInterface $value, string $format): string
    {
        return $value->format($format);
    }
}
