<?php

/** Apply only fields submitted by the configuration form, preserving omitted settings. */
function applySiteConfigurationValues($config, array $values, $allowAdvanced)
{
    $fields = [
        'contactEmail' => 'setContactEmail', 'language' => 'setLanguage',
        'webSiteTitle' => 'setWebSiteTitle', 'description' => 'setDescription',
        'authCanComment' => 'setAuthCanComment', 'authCanUploadVideos' => 'setAuthCanUploadVideos',
        'authCanViewChart' => 'setAuthCanViewChart', 'head' => 'setHead',
        'adsense' => 'setAdsense', 'autoplay' => 'setAutoplay',
    ];
    if ($allowAdvanced) {
        $fields += [
            'disable_analytics' => 'setDisable_analytics', 'allow_download' => 'setAllow_download',
            'session_timeout' => 'setSession_timeout', 'encoder_url' => 'setEncoderURL',
            'smtp' => 'setSmtp', 'smtpAuth' => 'setSmtpAuth', 'smtpSecure' => 'setSmtpSecure',
            'smtpHost' => 'setSmtpHost', 'smtpUsername' => 'setSmtpUsername',
            'smtpPassword' => 'setSmtpPassword', 'smtpPort' => 'setSmtpPort',
        ];
    }
    foreach ($fields as $field => $setter) {
        if (array_key_exists($field, $values) && is_scalar($values[$field])) {
            $config->$setter($values[$field]);
        }
    }
    // Theme selectors live elsewhere; an absent/empty legacy field must not reset them.
    if (isset($values['theme']) && is_string($values['theme']) && $values['theme'] !== '') {
        $config->setTheme($values['theme']);
    }
}
