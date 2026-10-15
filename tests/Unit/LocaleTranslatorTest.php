<?php

namespace Tests\Unit;

use Tests\TestCase;

require_once APP_ROOT . '/objects/LocaleTranslator.php';

/**
 * objects/LocaleTranslator.php: finds the texts to translate, reads and rewrites locale/*.php
 * (sorted, each text once, always valid PHP) and checks AI translations before storing them.
 * Every file written here goes to a temporary folder, never to the repository.
 */
class LocaleTranslatorTest extends TestCase
{
    private $dir;
    private $originalGlobal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalGlobal = $GLOBALS['global'] ?? null;
        if (empty($GLOBALS['global']['systemRootPath'])) {
            $GLOBALS['global']['systemRootPath'] = APP_ROOT . '/';
        }
        $this->dir = sys_get_temp_dir() . '/avideo-locale-translator-' . bin2hex(random_bytes(6)) . '/';
        mkdir($this->dir, 0777, true);
        \LocaleTranslator::$requestHandler = null;
    }

    protected function tearDown(): void
    {
        \LocaleTranslator::$requestHandler = null;
        foreach (scandir($this->dir) as $file) {
            if (is_file($this->dir . $file)) {
                unlink($this->dir . $file);
            }
        }
        @rmdir($this->dir);
        if ($this->originalGlobal === null) {
            unset($GLOBALS['global']);
        } else {
            $GLOBALS['global'] = $this->originalGlobal;
        }
        parent::tearDown();
    }

    private function includeLanguage($file)
    {
        $hadOriginal = array_key_exists('t', $GLOBALS);
        $original = $GLOBALS['t'] ?? null;
        $GLOBALS['t'] = [];
        include $file;
        $translations = $GLOBALS['t'];
        if ($hadOriginal) {
            $GLOBALS['t'] = $original;
        } else {
            unset($GLOBALS['t']);
        }
        return $translations;
    }

    private function hostileStrings()
    {
        return [
            "It's",
            'Say "hi"',
            'Back\\slash \\\' and \\\\',
            'Cost: $price {$x} ${y}',
            'Ends with backslash \\',
            "Line one\nline two\ttab",
            '?> <?php echo "pwned"; ?>',
            '*/ /* comment',
            "Null \0 byte",
            'Ação ✓ 日本語',
            '123',
            '  leading and trailing  ',
            '%s of %d <b>bold</b>',
        ];
    }

    public function testExtractsLiteralCallsOnlyAndDecodesThem()
    {
        $code = <<<'PHP'
<?php
echo __('Simple');
echo __("Double \"quoted\" \$x\tTab");
echo __( 'Spaces' );
echo __('With HTML', true);
echo __(
    'Multi line call'
);
echo __('It\'s');
echo __('Concat ' . $x);
echo __($var);
echo $obj->__('Method');
echo Foo::__('Static');
// echo __('Commented');
/* echo __('Block comment'); */
echo printJSString('From printJSString', true);
echo \__('Fully qualified');
?>
<script>var a = __('From inline JS'); var b = __("Inline \"JS\"", true); c.__('Member');</script>
PHP;
        $strings = [];
        \LocaleTranslator::extractFromPHP($code, $strings);
        $found = array_keys($strings);
        sort($found);
        $expected = ["Double \"quoted\" \$x\tTab", 'From inline JS', 'From printJSString', 'Fully qualified', 'Inline "JS"', "It's", 'Multi line call', 'Simple', 'Spaces', 'With HTML'];
        sort($expected);
        $this->assertSame($expected, $found);
    }

    public function testExtractsJavaScriptCalls()
    {
        $strings = [];
        \LocaleTranslator::extractFromJS("x = __('It\\'s'); y = __(\"Tab\\there\", true); z = obj.__('no'); w = my__('no'); v = __('A' + b);", $strings);
        $this->assertSame(["It's", "Tab\there"], array_keys($strings));
    }

    public function testDecodesPHPStringLiteralsLikePHP()
    {
        $literals = [
            "'plain'", "'It\\'s'", "'back\\\\slash'", "'keeps \\n literal'",
            '"tab\\tnew\\nline"', '"quote \\" dollar \\$"', '"octal \\101 hex \\x41"', '"unicode \\u{1F600}"',
            '"unknown \\d escape"', '"ends \\\\"', "b'binary'",
        ];
        foreach ($literals as $literal) {
            $this->assertSame(eval('return ' . $literal . ';'), \LocaleTranslator::decodePHPString($literal), $literal);
        }
    }

    public function testGeneratedFileIsValidPHPAndKeepsEveryStringExactly()
    {
        $entries = [];
        foreach ($this->hostileStrings() as $string) {
            $entries[$string] = 'T: ' . $string;
        }
        $result = \LocaleTranslator::saveTranslations('pt_BR', $entries, $this->dir);
        $this->assertSame(count($entries), $result['added']);

        $file = $this->dir . 'pt_BR.php';
        // A text with a PHP closing tag and echo stays inside a string: including the file prints nothing.
        $this->expectOutputString('');
        $included = $this->includeLanguage($file);
        $this->assertCount(count($entries), $included);
        foreach ($entries as $key => $value) {
            $this->assertSame($value, $included[$key], json_encode($key));
        }
    }

    public function testFileIsSortedWithoutDuplicatesAndKeepsTheLastValue()
    {
        file_put_contents($this->dir . 'es.php', "<?php\nglobal \$t;\n// Section comment\n\$t['Zebra'] = 'Cebra';\n\$t['apple'] = 'manzana';\n\$t['Zebra'] = 'Cebra 2';\n\$t[\"Banana\"] = \"Plátano\";\n");
        $result = \LocaleTranslator::saveTranslations('es', ['Cherry' => 'Cereza', 'apple' => 'Manzana'], $this->dir);

        $this->assertSame(['added' => 1, 'updated' => 1, 'total' => 4, 'duplicatesRemoved' => 1], $result);
        $expected = "<?php\nglobal \$t;\n\n\$t['apple'] = 'Manzana';\n\$t['Banana'] = 'Plátano';\n\$t['Cherry'] = 'Cereza';\n\$t['Zebra'] = 'Cebra 2';\n";
        $this->assertSame($expected, file_get_contents($this->dir . 'es.php'));
        $this->assertSame([], glob($this->dir . '.*.tmp'));
    }

    public function testFileThatOnlyOverridesAnotherLanguageKeepsItsInclude()
    {
        file_put_contents($this->dir . 'en_US.php', "<?php\nglobal \$t;\n\$t['Color'] = 'Color';\n\$t['Save'] = 'Save';\n");
        file_put_contents($this->dir . 'en_CA.php', "<?php\n// comment\ninclude __DIR__ . '/en_US.php';\n\n\$t['Color'] = 'Colour';\n");
        \LocaleTranslator::normalizeLanguage('en_CA', $this->dir);

        $this->assertSame(['Color' => 'Colour', 'Save' => 'Save'], $this->includeLanguage($this->dir . 'en_CA.php'));
        $this->assertSame(['en_US'], \LocaleTranslator::readLanguage('en_CA', $this->dir)['includes']);
        $this->assertFalse(\LocaleTranslator::canTranslateLanguage('en_CA', $this->dir));
    }

    public function testRefusesToRewriteAFileItDoesNotUnderstand()
    {
        $original = "<?php\nglobal \$t;\n\$t['a'] = 'b' . 'c';\n";
        file_put_contents($this->dir . 'fr.php', $original);
        try {
            \LocaleTranslator::saveTranslations('fr', ['x' => 'y'], $this->dir);
            $this->fail('A file with unknown statements must not be rewritten');
        } catch (\Exception $e) {
            $this->assertStringContainsString('fix it by hand', $e->getMessage());
        }
        $this->assertSame($original, file_get_contents($this->dir . 'fr.php'));
    }

    public function testRejectsLanguageCodesThatAreNotLanguageFiles()
    {
        foreach (['../x', 'index', 'save', 'function', 'pt-BR', 'PT', '', 'en_US/../x', 'a.b'] as $lang) {
            $this->assertFalse(\LocaleTranslator::isValidLanguageCode($lang), $lang);
        }
        foreach (['pt', 'pt_BR', 'zh_Hant_TW', 'en_US'] as $lang) {
            $this->assertTrue(\LocaleTranslator::isValidLanguageCode($lang), $lang);
        }
        $this->expectException(\Exception::class);
        \LocaleTranslator::saveTranslations('../evil', ['a' => 'b'], $this->dir);
    }

    public function testMissingTextsUseExactKeys()
    {
        $missing = \LocaleTranslator::getMissingStrings(['Save', 'save', 'Delete', 'Empty'], ['Save' => 'Salvar', 'Empty' => '']);
        $this->assertSame(['save', 'Delete', 'Empty'], $missing);
    }

    public function testTranslationChecks()
    {
        $this->assertSame('', \LocaleTranslator::getTranslationProblem('Hello %s', 'Olá %s'));
        $this->assertSame('', \LocaleTranslator::getTranslationProblem('Click <a href="x">here</a>', 'Clique <a href="x">aqui</a>'));
        $this->assertSame('', \LocaleTranslator::getTranslationProblem("It's", 'É "isso"'));
        $this->assertNotSame('', \LocaleTranslator::getTranslationProblem('Hello %s', 'Olá'));
        $this->assertNotSame('', \LocaleTranslator::getTranslationProblem('%s of %d', '%d de %d'));
        $this->assertNotSame('', \LocaleTranslator::getTranslationProblem('Click <a href="x">here</a>', 'Clique <a href="y">aqui</a>'));
        $this->assertNotSame('', \LocaleTranslator::getTranslationProblem('Save', '<img src=x onerror=alert(1)>'));
        $this->assertNotSame('', \LocaleTranslator::getTranslationProblem('Save', 'Salvar <b>'));
        $this->assertNotSame('', \LocaleTranslator::getTranslationProblem('Save', '   '));
        $this->assertSame('  Salvar ', \LocaleTranslator::keepSurroundingSpaces('  Save ', 'Salvar'));
    }

    public function testAIRequestUsesStructuredOutputAndKeepsTheKeyInTheHeaderOnly()
    {
        $config = ['apiKey' => 'sk-ant-test-key', 'model' => 'claude-opus-5-5', 'effort' => 'low', 'batchSize' => 40];
        $request = \LocaleTranslator::buildAIRequest(['Save', 'Delete'], 'pt_BR', 'Portuguese (Brazil)', $config);

        $this->assertSame('json_schema', $request['body']['output_config']['format']['type']);
        $this->assertSame('default', $request['body']['fallbacks']);
        $this->assertContains('anthropic-beta: server-side-fallback-2026-07-01', $request['headers']);
        $this->assertContains('x-api-key: sk-ant-test-key', $request['headers']);
        $this->assertStringNotContainsString('sk-ant-test-key', json_encode($request['body']));
        $this->assertStringContainsString('"text": "Delete"', $request['body']['messages'][0]['content']);

        $config['model'] = 'claude-haiku-5-5';
        $request = \LocaleTranslator::buildAIRequest(['Save'], 'pt_BR', 'Portuguese (Brazil)', $config);
        $this->assertArrayNotHasKey('fallbacks', $request['body']);
    }

    public function testAIResponseErrors()
    {
        $ok = json_encode(['stop_reason' => 'end_turn', 'content' => [['type' => 'thinking', 'thinking' => ''], ['type' => 'text', 'text' => json_encode(['translations' => [['id' => '1', 'text' => 'Salvar']]])]]]);
        $this->assertSame(['1' => 'Salvar'], \LocaleTranslator::parseAIResponse($ok, 200));

        $cases = [
            [json_encode(['error' => ['message' => 'invalid x-api-key']]), 401, 'invalid x-api-key'],
            [json_encode(['stop_reason' => 'refusal', 'content' => []]), 200, 'declined'],
            [json_encode(['stop_reason' => 'max_tokens', 'content' => []]), 200, 'cut off'],
            [json_encode(['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'not json']]]), 200, 'no translations'],
        ];
        foreach ($cases as $case) {
            try {
                \LocaleTranslator::parseAIResponse($case[0], $case[1]);
                $this->fail('Expected an exception for ' . $case[2]);
            } catch (\Exception $e) {
                $this->assertStringContainsString($case[2], $e->getMessage());
            }
        }
    }

    public function testTranslateNextBatchSavesCheckedTranslationsAndKeepsEnglishCatalogComplete()
    {
        file_put_contents($this->dir . 'en_US.php', "<?php\nglobal \$t;\n\$t['Save'] = 'Save';\n");
        file_put_contents($this->dir . 'pt_BR.php', "<?php\nglobal \$t;\n\$t['Save'] = 'Salvar';\n");
        $sent = [];
        \LocaleTranslator::$requestHandler = function ($request) use (&$sent) {
            $sent[] = $request['body']['messages'][0]['content'];
            $translations = [
                ['id' => '1', 'text' => 'Excluir %s'],
                ['id' => '2', 'text' => 'Olá'], // %s dropped: must be rejected
                ['id' => '3', 'text' => "É \"isso\" \$x"],
            ];
            $body = ['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => json_encode(['translations' => $translations])]]];
            return [json_encode($body), 200];
        };
        $config = ['apiKey' => 'test', 'model' => 'claude-opus-5-5', 'effort' => 'low', 'batchSize' => 40];
        $strings = ['Delete %s', 'Hello %s', "It's", 'Save'];

        $result = \LocaleTranslator::translateNextBatch('pt_BR', $strings, 0, $this->dir, $config);

        $this->assertSame(2, $result['translated']);
        $this->assertSame(['Hello %s'], array_keys($result['rejected']));
        $this->assertSame(1, $result['missing']);
        $this->assertCount(1, $sent);
        $this->assertStringNotContainsString('"Save"', $sent[0]);
        $this->assertSame(['Delete %s' => 'Excluir %s', "It's" => "É \"isso\" \$x", 'Save' => 'Salvar'], $this->includeLanguage($this->dir . 'pt_BR.php'));
        // en_US lists every translated text, so the other languages keep the same keys.
        $this->assertSame(['Delete %s' => 'Delete %s', "It's" => "It's", 'Save' => 'Save'], $this->includeLanguage($this->dir . 'en_US.php'));

        // The rejected text is skipped with the offset: nothing else is sent.
        $result = \LocaleTranslator::translateNextBatch('pt_BR', $strings, 1, $this->dir, $config);
        $this->assertSame(0, $result['translated']);
        $this->assertCount(1, $sent);
    }

    public function testEveryRepositoryLanguageFileIsReadExactlyAsPHPIncludesIt()
    {
        $localeDir = APP_ROOT . '/locale/';
        $languages = \LocaleTranslator::listLanguageFiles($localeDir);
        $this->assertArrayHasKey('en_US', $languages);
        foreach ($languages as $lang => $file) {
            $parsed = \LocaleTranslator::readLanguage($lang, $localeDir);
            $this->assertSame([], $parsed['errors'], $lang);
            $effective = \LocaleTranslator::getEffectiveEntries($lang, $localeDir);
            $included = $this->includeLanguage($file);
            $this->assertCount(count($included), $effective, $lang);
            foreach ($included as $key => $value) {
                $this->assertSame((string) $value, $effective[(string) $key], "{$lang}: {$key}");
            }
            // Rebuilding the file from what was read must give back the same entries.
            $php = \LocaleTranslator::buildLanguageCode($parsed['entries'], $parsed['includes']);
            $this->assertTrue(\LocaleTranslator::validateLanguageCode($php, $parsed['entries'], $parsed['includes']));
        }
    }

    public function testNoOtherPHPFileIsAddedToTheLocaleFolder()
    {
        // ?lang=, the API locale endpoint and the language selector include/list every file there.
        $files = array_map('basename', glob(APP_ROOT . '/locale/*.php'));
        $nonLanguage = array_values(array_filter($files, function ($file) {
            return !\LocaleTranslator::isValidLanguageCode(basename($file, '.php'));
        }));
        sort($nonLanguage);
        $this->assertSame(['function.js.php', 'function.php', 'index.php', 'save.php'], $nonLanguage);
    }
}
