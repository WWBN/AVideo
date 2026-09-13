<?php

namespace Tests\Unit;

use Tests\TestCase;

class SiteConfigurationFormTest extends TestCase
{
    private function configuration()
    {
        require_once APP_ROOT . '/objects/configurationForm.php';
        return new class {
            public $calls = [];
            public function __call($name, $arguments)
            {
                $this->calls[$name] = $arguments[0];
            }
        };
    }

    public function testOrdinarySaveDoesNotResetThemeModeOrOmittedSettings(): void
    {
        $config = $this->configuration();
        applySiteConfigurationValues($config, ['webSiteTitle' => 'New title', 'theme' => ''], true);
        $this->assertSame(['setWebSiteTitle' => 'New title'], $config->calls);
        applySiteConfigurationValues($config, ['contactEmail' => 'admin@example.com'], true);
        $this->assertArrayNotHasKey('setTheme', $config->calls);
        $this->assertArrayNotHasKey('setMode', $config->calls);
        $this->assertArrayNotHasKey('setSmtpPassword', $config->calls);
    }

    public function testExplicitEmptyAndFalseValuesAreSaved(): void
    {
        $config = $this->configuration();
        applySiteConfigurationValues($config, ['head' => '', 'autoplay' => false,
            'smtpPassword' => '0', 'smtpSecure' => '', 'smtpPort' => '2525'], true);
        $this->assertSame('', $config->calls['setHead']);
        $this->assertFalse($config->calls['setAutoplay']);
        $this->assertSame('0', $config->calls['setSmtpPassword']);
        $this->assertSame('', $config->calls['setSmtpSecure']);
        $this->assertSame('2525', $config->calls['setSmtpPort']);
    }

    public function testDisabledAdvancedSettingsRemainUntouched(): void
    {
        $config = $this->configuration();
        applySiteConfigurationValues($config, ['smtpHost' => 'changed', 'smtp' => false,
            'encoder_url' => 'changed', 'language' => 'pt_BR'], false);
        $this->assertSame(['setLanguage' => 'pt_BR'], $config->calls);
    }

    public function testLegacyExplicitThemeStillWorksAndUnknownValuesAreIgnored(): void
    {
        $config = $this->configuration();
        applySiteConfigurationValues($config, ['theme' => 'cerulean', 'mode' => 'Youtube',
            'language' => ['invalid'], 'unknown' => 'value'], true);
        $this->assertSame(['setTheme' => 'cerulean'], $config->calls);
    }
}
