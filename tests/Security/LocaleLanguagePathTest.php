<?php

namespace Tests\Security\LocaleFixtures;

// Execute the production setLanguage()/isValidLanguageCode() without loading the configuration.
$localeSource = file_get_contents(dirname(__DIR__, 2) . '/locale/function.php');
$localeStart = strpos($localeSource, 'function setLanguage(');
$localeEnd = strpos($localeSource, 'function getLanguage()', $localeStart);
eval('namespace ' . __NAMESPACE__ . '; ' . substr($localeSource, $localeStart, $localeEnd - $localeStart));

function flag2Lang($flagCode)
{
    return $flagCode;
}

function _session_start()
{
}

namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use function Tests\Security\LocaleFixtures\isValidLanguageCode;
use function Tests\Security\LocaleFixtures\setLanguage;

/**
 * The language code (from ?lang=, Accept-Language or the session) is joined to locale/ and included,
 * so it must never be able to leave that directory (GHSA-r3jc-85h3-h7g2, bypass of CVE-2023-47862).
 */
class LocaleLanguagePathTest extends TestCase
{
    private $tmpRoot;
    private $originalSystemRootPath;

    protected function setUp(): void
    {
        global $global;
        $this->originalSystemRootPath = $global['systemRootPath'] ?? null;
        $this->tmpRoot = sys_get_temp_dir() . '/avideo-locale-test-' . bin2hex(random_bytes(6)) . '/';
        mkdir($this->tmpRoot . 'locale', 0777, true);
        file_put_contents($this->tmpRoot . 'locale/en_US.php', '<?php $GLOBALS["localeTestIncluded"][] = "en_US";');
        file_put_contents($this->tmpRoot . 'locale/pt.php', '<?php $GLOBALS["localeTestIncluded"][] = "pt";');
        file_put_contents($this->tmpRoot . 'locale/save.php', '<?php $GLOBALS["localeTestIncluded"][] = "save";');
        file_put_contents($this->tmpRoot . 'outside.php', '<?php $GLOBALS["localeTestIncluded"][] = "outside";');
        $global['systemRootPath'] = $this->tmpRoot;
        $GLOBALS['localeTestIncluded'] = [];
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        global $global;
        foreach (['locale/en_US.php', 'locale/pt.php', 'locale/save.php', 'outside.php'] as $file) {
            @unlink($this->tmpRoot . $file);
        }
        @rmdir($this->tmpRoot . 'locale');
        @rmdir($this->tmpRoot);
        $global['systemRootPath'] = $this->originalSystemRootPath;
        unset($GLOBALS['localeTestIncluded']);
        $_SESSION = [];
    }

    public function traversalProvider()
    {
        return [
            'plain traversal' => ['../outside'],
            'overlapping traversal' => ['....//outside'],
            'overlapping traversal with suffix' => ['....//outside_US'],
            'backslash traversal' => ['..\\outside'],
            'absolute path' => ['/etc/passwd'],
            'non-language file in locale' => ['save'],
        ];
    }

    /**
     * @dataProvider traversalProvider
     */
    public function testLanguageCodeCannotIncludeAnotherFile($lang)
    {
        $this->assertFalse(setLanguage($lang));
        $this->assertSame([], $GLOBALS['localeTestIncluded']);
        $this->assertArrayNotHasKey('language', $_SESSION);
    }

    public function testRealLanguageCodeStillLoads()
    {
        $this->assertTrue(setLanguage('en_US'));
        $this->assertSame('en_US', $_SESSION['language']);
        $this->assertSame(['en_US'], $GLOBALS['localeTestIncluded']);
    }

    public function testBrowserHeaderStillFallsBackToBaseLanguage()
    {
        // Accept-Language "pt-BR;q=0.9" reaches setLanguage() as "pt_BR;q=0.9" and has always resolved to pt.
        $this->assertTrue(setLanguage('pt_BR;q=0.9'));
        $this->assertSame('pt', $_SESSION['language']);
        $this->assertSame(['pt'], $GLOBALS['localeTestIncluded']);
    }

    public function testValidLanguageCodes()
    {
        foreach (['en_US', 'pt_BR', 'zh_Hant_TW', 'fa_IR', 'es', 'en-US'] as $lang) {
            $this->assertTrue(isValidLanguageCode($lang), $lang);
        }
        foreach (['', '..', '....//x', 'a/b', 'a\\b', 'function.js', 'index', 'Function', null, ['en']] as $lang) {
            $this->assertFalse(isValidLanguageCode($lang), var_export($lang, true));
        }
    }

    public function testSessionLanguageIsValidatedBeforeTheInclude()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/locale/function.php');
        $start = strpos($source, 'function includeLangFile()');
        $end = strpos($source, 'function __(', $start);
        $method = substr($source, $start, $end - $start);

        $this->assertStringContainsString("if (!isValidLanguageCode(\$_SESSION['language'])) {", $method);
        $this->assertLessThan(
            strpos($method, '@include_once'),
            strpos($method, 'isValidLanguageCode(')
        );
    }
}
