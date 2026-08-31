<?php

namespace Tests\Unit;

use Tests\TestCase;

class PersianLocaleTest extends TestCase
{
    /**
     * @return array<string, string>
     */
    private function loadLocale($locale)
    {
        $hadOriginal = array_key_exists('t', $GLOBALS);
        $original = $GLOBALS['t'] ?? null;
        $GLOBALS['t'] = [];

        require APP_ROOT . '/locale/' . $locale . '.php';
        $translations = $GLOBALS['t'];

        if ($hadOriginal) {
            $GLOBALS['t'] = $original;
        } else {
            unset($GLOBALS['t']);
        }

        return $translations;
    }

    /**
     * @return string[]
     */
    private function protectedTokens($value)
    {
        $pattern = '~%(?:\d+\$)?[-+0-9.]*[bcdeEfFgGosuxX](?![A-Za-z0-9])'
            . '|\{\{[^{}]+\}\}'
            . '|\{[A-Za-z_][A-Za-z0-9_]*\}'
            . '|_[A-Z][A-Z0-9_]*_'
            . '|</?[A-Za-z][^>]*>'
            . '|&(?:#[0-9]+|#x[0-9A-Fa-f]+|[A-Za-z][A-Za-z0-9]+);'
            . '|https?://[^\s\'"<>]+~';
        preg_match_all($pattern, $value, $matches);
        $tokens = $matches[0];
        sort($tokens);
        return $tokens;
    }

    /** @test */
    public function testPersianLocaleMatchesTheEnglishCatalogExactly()
    {
        $english = $this->loadLocale('en_US');
        $persian = $this->loadLocale('fa_IR');

        $this->assertSame(array_keys($english), array_keys($persian));
        $this->assertCount(count($english), $persian);
    }

    /** @test */
    public function testPersianLocaleHasNoEmptyTranslations()
    {
        foreach ($this->loadLocale('fa_IR') as $source => $translation) {
            $this->assertNotSame('', trim($translation), "Empty Persian translation: {$source}");
        }
    }

    /** @test */
    public function testPersianLocalePreservesRuntimeTokensAndMarkup()
    {
        foreach ($this->loadLocale('fa_IR') as $source => $translation) {
            $this->assertSame(
                $this->protectedTokens($source),
                $this->protectedTokens($translation),
                "Protected token mismatch: {$source}"
            );
        }
    }

    /** @test */
    public function testPersianLocaleUsesTheProductGlossary()
    {
        $persian = $this->loadLocale('fa_IR');

        $this->assertSame('بارگذاری', $persian['Upload']);
        $this->assertSame('دریافت', $persian['Download']);
        $this->assertSame('پخش زنده', $persian['Live']);
        $this->assertSame('رمزگذار', $persian['Encoder']);
        $this->assertSame('افزونه', $persian['Plugin']);
        $this->assertSame('پیشخوان', $persian['Dashboard']);
        $this->assertSame('گروه‌های کاربری', $persian['User Groups']);
        $this->assertSame('ثبت‌نام', $persian['Sign Up']);
    }

    /** @test */
    public function testFaIrIsRegisteredAndUsesRtlLayout()
    {
        $languageData = [];
        (static function (&$global) {
            require APP_ROOT . '/objects/bcp47.php';
        })($languageData);

        $this->assertSame('Persian (Iran)', $languageData['bcp47']['fa_IR']['label']);
        $this->assertSame('ir', $languageData['bcp47']['fa_IR']['flag']);

        $GLOBALS['isStandAlone'] = true;
        if (!function_exists('_isRTL')) {
            require_once APP_ROOT . '/locale/function.php';
        }
        $this->assertTrue(\_isRTL('fa_IR'));
        $this->assertTrue(\_isRTL('fa-IR'));
    }
}
