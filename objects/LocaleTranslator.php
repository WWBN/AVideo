<?php

/**
 * Builds and maintains the translation files in locale/ (one PHP file per language that fills $t).
 *
 * - Finds every text passed to __() / printJSString() in PHP and to __() in JavaScript.
 * - Reads a language file without executing it and rewrites it with var_export(), sorted
 *   alphabetically and with each text once, so no translation can break the PHP syntax.
 * - Every file is validated (syntax + round trip) before it atomically replaces the old one.
 * - Missing texts can be translated by the Claude API (configuration in getAIConfig()).
 *
 * Used by locale/index.php, locale/save.php, objects/localeTranslator.json.php and
 * install/translateLocale.php. It only needs $global['systemRootPath'], so tests can load it alone.
 *
 * Never add another .php file to locale/: ?lang=, the API locale endpoint and the language
 * selector include or list every file there as a language.
 */
class LocaleTranslator
{
    const SOURCE_LANGUAGE = 'en_US';
    const ANTHROPIC_URL = 'https://api.anthropic.com/v1/messages';
    const ANTHROPIC_VERSION = '2023-06-01';
    const DEFAULT_MODEL = 'claude-opus-5-5';
    const DEFAULT_EFFORT = 'low';
    const DEFAULT_BATCH_SIZE = 40;
    const MAX_BATCH_SIZE = 100;
    // Models that accept the server-side refusal fallback (fallbacks: "default").
    const FALLBACK_MODELS = ['claude-fable-5-1', 'claude-opus-5-5', 'claude-opus-5', 'claude-sonnet-5-5'];

    // Folders scanned for texts, relative to the root. Root .php files are scanned too.
    const SOURCE_DIRS = ['admin', 'feed', 'install', 'locale', 'objects', 'plugin', 'view'];
    // Folder names skipped at any depth.
    const SKIP_DIR_NAMES = ['vendor', 'node_modules', '.git'];

    const TRANSLATION_FUNCTIONS = ['__', 'printjsstring'];

    /**
     * A language code names a file in locale/ and must also match the language selector
     * (Layout::getAvailableFlags() lists /^[a-z]{2}(_.*)?.php$/).
     */
    public static function isValidLanguageCode($lang)
    {
        return is_string($lang) && preg_match('/^[a-z]{2}(_[A-Za-z0-9]+)*$/', $lang) === 1;
    }

    public static function getLocaleDir()
    {
        global $global;
        return rtrim($global['systemRootPath'], '/\\') . '/locale/';
    }

    /**
     * @return array language code => file path, sorted by code
     */
    public static function listLanguageFiles($dir = '')
    {
        if (empty($dir)) {
            $dir = self::getLocaleDir();
        }
        $languages = [];
        foreach (glob(rtrim($dir, '/\\') . '/*.php') as $file) {
            $lang = basename($file, '.php');
            if (self::isValidLanguageCode($lang)) {
                $languages[$lang] = $file;
            }
        }
        ksort($languages);
        return $languages;
    }

    // ------------------------------------------------------------------ extraction

    /**
     * Every distinct text found in the source code, sorted.
     *
     * @return string[]
     */
    public static function extractStrings($rootPath = '')
    {
        global $global;
        if (empty($rootPath)) {
            $rootPath = $global['systemRootPath'];
        }
        $rootPath = rtrim($rootPath, '/\\') . '/';
        $strings = [];
        foreach (glob($rootPath . '*.php') as $file) {
            self::extractFromFile($file, $strings);
        }
        foreach (self::SOURCE_DIRS as $dir) {
            if (is_dir($rootPath . $dir)) {
                self::extractFromDir($rootPath . $dir, $strings);
            }
        }
        return self::sortStrings(array_keys($strings));
    }

    private static function extractFromDir($dir, &$strings)
    {
        $isLocaleDir = basename($dir) === 'locale';
        $entries = scandir($dir);
        if ($entries === false) {
            return;
        }
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . '/' . $entry;
            if (is_dir($path)) {
                if (!in_array($entry, self::SKIP_DIR_NAMES, true) && !is_link($path)) {
                    self::extractFromDir($path, $strings);
                }
                continue;
            }
            if ($isLocaleDir && self::isValidLanguageCode(basename($entry, '.php'))) {
                continue; // translation files, not source code
            }
            self::extractFromFile($path, $strings);
        }
    }

    private static function extractFromFile($file, &$strings)
    {
        $isPHP = substr($file, -4) === '.php';
        $isJS = substr($file, -3) === '.js' && substr($file, -7) !== '.min.js';
        if (!$isPHP && !$isJS) {
            return;
        }
        $code = @file_get_contents($file);
        if ($code === false) {
            return;
        }
        if ($isPHP) {
            self::extractFromPHP($code, $strings);
        } else {
            self::extractFromJS($code, $strings);
        }
    }

    /**
     * Literal texts passed to __() or printJSString(), e.g. __('Save'), __("Save", true),
     * __( 'Save' ) or a call split across lines. Calls whose first argument is not a single
     * literal (a variable, a concatenation) are skipped: their text is only known at runtime.
     */
    public static function extractFromPHP($code, &$strings)
    {
        if (stripos($code, '__(') === false && stripos($code, 'printJSString') === false && stripos($code, '__ (') === false) {
            return;
        }
        $tokens = @token_get_all($code);
        $count = count($tokens);
        $nameTokens = [T_STRING];
        if (defined('T_NAME_FULLY_QUALIFIED')) {
            $nameTokens[] = T_NAME_FULLY_QUALIFIED;
        }
        $notACall = [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW, T_CONST];
        if (defined('T_NULLSAFE_OBJECT_OPERATOR')) {
            $notACall[] = T_NULLSAFE_OBJECT_OPERATOR;
        }
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (!is_array($token)) {
                continue;
            }
            if ($token[0] === T_INLINE_HTML) {
                self::extractFromJS($token[1], $strings);
                continue;
            }
            if (!in_array($token[0], $nameTokens, true) || !in_array(strtolower(ltrim($token[1], '\\')), self::TRANSLATION_FUNCTIONS, true)) {
                continue;
            }
            $previous = self::previousSignificantToken($tokens, $i);
            if ($previous !== null && is_array($tokens[$previous]) && in_array($tokens[$previous][0], $notACall, true)) {
                continue;
            }
            $open = self::nextSignificantToken($tokens, $i);
            if ($open === null || $tokens[$open] !== '(') {
                continue;
            }
            $literal = self::nextSignificantToken($tokens, $open);
            if ($literal === null || !is_array($tokens[$literal]) || $tokens[$literal][0] !== T_CONSTANT_ENCAPSED_STRING) {
                continue;
            }
            $after = self::nextSignificantToken($tokens, $literal);
            if ($after === null || ($tokens[$after] !== ',' && $tokens[$after] !== ')')) {
                continue;
            }
            $text = self::decodePHPString($tokens[$literal][1]);
            if ($text !== null && trim($text) !== '') {
                $strings[$text] = true;
            }
        }
    }

    private static function nextSignificantToken($tokens, $i)
    {
        $count = count($tokens);
        for ($j = $i + 1; $j < $count; $j++) {
            if (!is_array($tokens[$j]) || !in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                return $j;
            }
        }
        return null;
    }

    private static function previousSignificantToken($tokens, $i)
    {
        for ($j = $i - 1; $j >= 0; $j--) {
            if (!is_array($tokens[$j]) || !in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                return $j;
            }
        }
        return null;
    }

    /**
     * JavaScript calls of view/js/script.js __(), in .js files and in the HTML parts of PHP files.
     */
    public static function extractFromJS($code, &$strings)
    {
        if (strpos($code, '__(') === false) {
            return;
        }
        $regex = '/(?<![\w$.])__\(\s*(?:\'((?:[^\'\\\\\r\n]|\\\\.)*)\'|"((?:[^"\\\\\r\n]|\\\\.)*)")\s*[,)]/';
        if (!preg_match_all($regex, $code, $matches, PREG_SET_ORDER)) {
            return;
        }
        foreach ($matches as $match) {
            $raw = isset($match[2]) && $match[2] !== '' ? $match[2] : $match[1];
            $text = self::decodeJSString($raw);
            if (trim($text) !== '') {
                $strings[$text] = true;
            }
        }
    }

    /**
     * Value of a PHP T_CONSTANT_ENCAPSED_STRING token ('...' or "..." without variables),
     * computed without eval().
     */
    public static function decodePHPString($literal)
    {
        if (!is_string($literal) || strlen($literal) < 2) {
            return null;
        }
        if ($literal[0] === 'b' || $literal[0] === 'B') {
            $literal = substr($literal, 1);
        }
        $quote = $literal[0];
        if (($quote !== "'" && $quote !== '"') || substr($literal, -1) !== $quote) {
            return null;
        }
        $body = substr($literal, 1, -1);
        if ($quote === "'") {
            return preg_replace_callback("/\\\\([\\\\'])/", function ($m) {
                return $m[1];
            }, $body);
        }
        $simple = ['n' => "\n", 'r' => "\r", 't' => "\t", 'v' => "\v", 'e' => "\x1B", 'f' => "\f", '\\' => '\\', '$' => '$', '"' => '"'];
        return preg_replace_callback('/\\\\(?:([nrtvef\\\\$"])|([0-7]{1,3})|x([0-9A-Fa-f]{1,2})|u\{([0-9A-Fa-f]+)\})/', function ($m) use ($simple) {
            if (isset($m[1]) && $m[1] !== '') {
                return $simple[$m[1]];
            }
            if (isset($m[2]) && $m[2] !== '') {
                return chr(octdec($m[2]) & 255);
            }
            if (isset($m[3]) && $m[3] !== '') {
                return chr(hexdec($m[3]));
            }
            return self::codePointToUTF8(hexdec($m[4]));
        }, $body);
    }

    public static function decodeJSString($body)
    {
        $simple = ['n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'f' => "\f", 'v' => "\v", '0' => "\0"];
        return preg_replace_callback('/\\\\(?:u\{([0-9A-Fa-f]+)\}|u([0-9A-Fa-f]{4})|x([0-9A-Fa-f]{2})|(.))/s', function ($m) use ($simple) {
            if (isset($m[1]) && $m[1] !== '') {
                return self::codePointToUTF8(hexdec($m[1]));
            }
            if (isset($m[2]) && $m[2] !== '') {
                return self::codePointToUTF8(hexdec($m[2]));
            }
            if (isset($m[3]) && $m[3] !== '') {
                return self::codePointToUTF8(hexdec($m[3]));
            }
            return isset($simple[$m[4]]) ? $simple[$m[4]] : $m[4];
        }, $body);
    }

    private static function codePointToUTF8($codePoint)
    {
        if ($codePoint < 0x80) {
            return chr($codePoint);
        }
        if ($codePoint < 0x800) {
            return chr(0xC0 | ($codePoint >> 6)) . chr(0x80 | ($codePoint & 0x3F));
        }
        if ($codePoint < 0x10000) {
            return chr(0xE0 | ($codePoint >> 12)) . chr(0x80 | (($codePoint >> 6) & 0x3F)) . chr(0x80 | ($codePoint & 0x3F));
        }
        return chr(0xF0 | ($codePoint >> 18)) . chr(0x80 | (($codePoint >> 12) & 0x3F)) . chr(0x80 | (($codePoint >> 6) & 0x3F)) . chr(0x80 | ($codePoint & 0x3F));
    }

    // ------------------------------------------------------------------ language files

    public static function compareStrings($a, $b)
    {
        $a = (string) $a;
        $b = (string) $b;
        $result = strnatcasecmp($a, $b);
        return $result !== 0 ? $result : strcmp($a, $b);
    }

    public static function sortStrings(array $strings)
    {
        $strings = array_map('strval', $strings);
        usort($strings, [self::class, 'compareStrings']);
        return $strings;
    }

    /**
     * @return array text => translation, alphabetical, keys always strings
     */
    public static function sortEntries(array $entries)
    {
        $keys = self::sortStrings(array_keys($entries));
        $sorted = [];
        foreach ($keys as $key) {
            $sorted[$key] = (string) $entries[$key];
        }
        return $sorted;
    }

    /**
     * Reads a language file without executing it.
     *
     * Understands the statements these files contain: `global $t;`, `$t['text'] = 'translation';`
     * and `include __DIR__ . '/en_US.php';` (en_AU/en_CA only list what differs from en_US).
     * A repeated text keeps its last value, as PHP does. Anything else is reported in
     * 'errors', and such a file is never rewritten.
     *
     * @return array ['entries' => [], 'includes' => [], 'duplicates' => int, 'errors' => []]
     */
    public static function parseLanguageCode($code)
    {
        $result = ['entries' => [], 'includes' => [], 'duplicates' => 0, 'errors' => []];
        try {
            $tokens = token_get_all($code, TOKEN_PARSE);
        } catch (\Throwable $th) {
            $result['errors'][] = 'PHP syntax error: ' . $th->getMessage();
            return $result;
        }
        $statement = [];
        $line = 1;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (in_array($token[0], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                    continue;
                }
                if ($token[0] === T_CLOSE_TAG) {
                    $token = ';';
                } else {
                    if (empty($statement)) {
                        $line = $token[2];
                    }
                    $statement[] = $token;
                    continue;
                }
            }
            if ($token !== ';') {
                $statement[] = $token;
                continue;
            }
            if (!empty($statement)) {
                self::parseStatement($statement, $line, $result);
            }
            $statement = [];
        }
        if (!empty($statement)) {
            $result['errors'][] = "Line {$line}: incomplete statement";
        }
        return $result;
    }

    private static function parseStatement(array $statement, $line, array &$result)
    {
        $shape = '';
        foreach ($statement as $token) {
            $shape .= is_array($token) ? '{' . $token[0] . '}' : $token;
        }
        // global $t
        if ($shape === '{' . T_GLOBAL . '}{' . T_VARIABLE . '}' && $statement[1][1] === '$t') {
            return;
        }
        // $t['text'] = 'translation'
        if ($shape === '{' . T_VARIABLE . '}[{' . T_CONSTANT_ENCAPSED_STRING . '}]={' . T_CONSTANT_ENCAPSED_STRING . '}' && $statement[0][1] === '$t') {
            $key = self::decodePHPString($statement[2][1]);
            $value = self::decodePHPString($statement[5][1]);
            if ($key === null || $value === null) {
                $result['errors'][] = "Line {$line}: invalid string";
                return;
            }
            if (array_key_exists($key, $result['entries'])) {
                $result['duplicates']++;
                unset($result['entries'][$key]); // the last one wins, as in PHP
            }
            $result['entries'][$key] = $value;
            return;
        }
        // include __DIR__ . '/en_US.php'
        $includes = [T_INCLUDE, T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE];
        if (count($statement) === 4 && is_array($statement[0]) && in_array($statement[0][0], $includes, true)
            && is_array($statement[1]) && $statement[1][0] === T_DIR && $statement[2] === '.'
            && is_array($statement[3]) && $statement[3][0] === T_CONSTANT_ENCAPSED_STRING) {
            $path = self::decodePHPString($statement[3][1]);
            if (preg_match('/^\/([a-zA-Z0-9_]+)\.php$/', (string) $path, $m) && self::isValidLanguageCode($m[1])) {
                $result['includes'][] = $m[1];
                return;
            }
        }
        $result['errors'][] = "Line {$line}: unsupported statement";
    }

    /**
     * @return array same as parseLanguageCode(); a missing file is an empty language
     */
    public static function readLanguage($lang, $dir = '')
    {
        if (!self::isValidLanguageCode($lang)) {
            throw new Exception('Invalid language code');
        }
        if (empty($dir)) {
            $dir = self::getLocaleDir();
        }
        $file = rtrim($dir, '/\\') . '/' . $lang . '.php';
        clearstatcache(true, $file);
        if (!file_exists($file)) {
            return ['entries' => [], 'includes' => [], 'duplicates' => 0, 'errors' => [], 'exists' => false];
        }
        // The status page reads every file several times; reuse the result until the file changes.
        $version = filemtime($file) . ':' . filesize($file);
        if (isset(self::$readCache[$file]) && self::$readCache[$file]['version'] === $version) {
            return self::$readCache[$file]['result'];
        }
        $code = file_get_contents($file);
        if ($code === false) {
            throw new Exception('Unable to read the language file');
        }
        $result = self::parseLanguageCode($code);
        $result['exists'] = true;
        self::$readCache[$file] = ['version' => $version, 'result' => $result];
        return $result;
    }

    private static $readCache = [];

    /**
     * PHP source of a language file. Every text is a single-quoted literal, so quotes,
     * backslashes, `$`, `?>` or new lines are always data, never code.
     */
    public static function buildLanguageCode(array $entries, array $includes = [])
    {
        $php = "<?php\nglobal \$t;\n";
        foreach ($includes as $base) {
            if (!self::isValidLanguageCode($base)) {
                throw new Exception('Invalid included language');
            }
            $php .= "// Only the texts that differ from {$base}.php; everything else comes from that file.\n";
            $php .= 'include __DIR__ . ' . self::exportString('/' . $base . '.php') . ";\n";
        }
        $php .= "\n";
        foreach (self::sortEntries($entries) as $key => $value) {
            $php .= '$t[' . self::exportString($key) . '] = ' . self::exportString($value) . ";\n";
        }
        return $php;
    }

    /**
     * Same escaping as var_export(), which would split a NUL byte into a concatenation.
     */
    public static function exportString($text)
    {
        return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $text) . "'";
    }

    /**
     * The new file must be valid PHP and read back exactly as the entries it was built from.
     */
    public static function validateLanguageCode($php, array $entries, array $includes = [])
    {
        $parsed = self::parseLanguageCode($php);
        if (!empty($parsed['errors'])) {
            throw new Exception('Generated language file is invalid: ' . implode('; ', $parsed['errors']));
        }
        if ($parsed['duplicates'] !== 0 || $parsed['includes'] !== array_values($includes)) {
            throw new Exception('Generated language file does not match');
        }
        $expected = self::sortEntries($entries);
        if (count($parsed['entries']) !== count($expected)) {
            throw new Exception('Generated language file does not match');
        }
        foreach ($expected as $key => $value) {
            if (!array_key_exists($key, $parsed['entries']) || (string) $parsed['entries'][$key] !== $value) {
                throw new Exception('Generated language file does not match');
            }
        }
        return true;
    }

    /**
     * Adds or replaces translations in a language file (creating it when needed) and rewrites it
     * sorted, without duplicates. The read-modify-write runs under a lock, and the old file is
     * only replaced, atomically, after the new one passed validateLanguageCode().
     *
     * @param array $translations text => translation
     * @param bool $replaceAll true: the file keeps only $translations (plus its include)
     * @return array ['added' => int, 'updated' => int, 'total' => int, 'duplicatesRemoved' => int]
     */
    public static function saveTranslations($lang, array $translations, $dir = '', $replaceAll = false)
    {
        if (!self::isValidLanguageCode($lang)) {
            throw new Exception('Invalid language code');
        }
        if (empty($dir)) {
            $dir = self::getLocaleDir();
        }
        $dir = rtrim($dir, '/\\') . '/';
        if (!is_dir($dir)) {
            throw new Exception('The locale folder does not exist');
        }
        $file = $dir . $lang . '.php';

        $lock = fopen(rtrim(sys_get_temp_dir(), '/\\') . '/avideo-locale-' . md5($file) . '.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            throw new Exception('Unable to lock the language file');
        }
        try {
            $current = self::readLanguage($lang, $dir);
            if (!empty($current['errors'])) {
                throw new Exception("{$lang}.php has statements this tool does not understand; fix it by hand first: " . implode('; ', $current['errors']));
            }
            $entries = $replaceAll ? [] : $current['entries'];
            $added = 0;
            $updated = 0;
            foreach ($translations as $key => $value) {
                $key = (string) $key;
                $value = (string) $value;
                if (!array_key_exists($key, $entries)) {
                    $added++;
                } elseif ((string) $entries[$key] !== $value) {
                    $updated++;
                }
                $entries[$key] = $value;
            }
            $php = self::buildLanguageCode($entries, $current['includes']);
            self::validateLanguageCode($php, $entries, $current['includes']);
            self::writeFileAtomically($file, $php);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        return ['added' => $added, 'updated' => $updated, 'total' => count($entries), 'duplicatesRemoved' => $current['duplicates']];
    }

    /**
     * Rewrites a language file sorted and without repeated texts. The translations do not change.
     */
    public static function normalizeLanguage($lang, $dir = '')
    {
        return self::saveTranslations($lang, [], $dir);
    }

    private static function writeFileAtomically($file, $content)
    {
        $tmp = dirname($file) . '/.' . basename($file) . '.' . bin2hex(random_bytes(6)) . '.tmp';
        if (@file_put_contents($tmp, $content, LOCK_EX) !== strlen($content)) {
            @unlink($tmp);
            throw new Exception('Unable to write the language file; check that the locale folder is writable');
        }
        @chmod($tmp, file_exists($file) ? (fileperms($file) & 0777) : 0644);
        if (!@rename($tmp, $file)) {
            @unlink($tmp);
            throw new Exception('Unable to replace the language file');
        }
        unset(self::$readCache[$file]);
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($file, true);
        }
    }

    // ------------------------------------------------------------------ status

    /**
     * Texts with no translation (no entry with exactly that text, or an empty one, which __()
     * ignores).
     */
    public static function getMissingStrings(array $strings, array $entries)
    {
        $missing = [];
        foreach ($strings as $string) {
            if (!isset($entries[$string]) || (string) $entries[$string] === '') {
                $missing[] = $string;
            }
        }
        return $missing;
    }

    /**
     * en_US is the catalog of every text (each one "translated" to itself) that the other
     * languages follow; tests/Unit/PersianLocaleTest.php requires fa_IR to have exactly its keys.
     * Called whenever texts are added to another language, and before translating.
     *
     * @return int texts added to en_US
     */
    public static function addSourceTexts(array $texts, $dir = '')
    {
        $missing = self::getMissingStrings($texts, self::readLanguage(self::SOURCE_LANGUAGE, $dir)['entries']);
        if (empty($missing)) {
            return 0;
        }
        self::saveTranslations(self::SOURCE_LANGUAGE, array_combine($missing, $missing), $dir);
        return count($missing);
    }

    /**
     * Entries of a language including the ones it gets from an included base file.
     */
    public static function getEffectiveEntries($lang, $dir = '')
    {
        $language = self::readLanguage($lang, $dir);
        $entries = [];
        foreach ($language['includes'] as $base) {
            if ($base !== $lang) {
                $entries = array_replace($entries, self::readLanguage($base, $dir)['entries']);
            }
        }
        return array_replace($entries, $language['entries']);
    }

    /**
     * Languages that receive machine translations: every file except the source language and
     * files that only override another language (en_AU, en_CA).
     */
    public static function canTranslateLanguage($lang, $dir = '')
    {
        if (!self::isValidLanguageCode($lang) || strpos($lang, 'en') === 0) {
            return false;
        }
        $language = self::readLanguage($lang, $dir);
        return empty($language['includes']) && empty($language['errors']);
    }

    // ------------------------------------------------------------------ translation checks

    // Parts of a text the code or the browser depend on, kept identical by a translation: sprintf
    // placeholders, {{name}}/{name}, _CONSTANT_ markers, whole HTML tags, entities and URLs.
    // Same definition as tests/Unit/PersianLocaleTest.php.
    const PROTECTED_TOKENS_REGEX = '~%(?:\d+\$)?[-+0-9.]*[bcdeEfFgGosuxX](?![A-Za-z0-9])'
        . '|\{\{[^{}]+\}\}'
        . '|\{[A-Za-z_][A-Za-z0-9_]*\}'
        . '|_[A-Z][A-Z0-9_]*_'
        . '|</?[A-Za-z][^>]*>'
        . '|&(?:#[0-9]+|#x[0-9A-Fa-f]+|[A-Za-z][A-Za-z0-9]+);'
        . '|https?://[^\s\'"<>]+~';

    /**
     * A translation is only stored when it keeps every protected token of the original (a
     * missing %s makes sprintf() throw on PHP 8; a changed tag breaks the page) and adds no
     * script or control characters the original does not have.
     *
     * @return string empty when safe, otherwise the reason
     */
    public static function getTranslationProblem($source, $translation)
    {
        if (!is_string($translation)) {
            return 'not a text';
        }
        if (trim($translation) === '' && trim($source) !== '') {
            return 'empty';
        }
        if (strlen($translation) > max(300, strlen($source) * 6)) {
            return 'too long';
        }
        if (function_exists('mb_check_encoding') && !mb_check_encoding($translation, 'UTF-8')) {
            return 'invalid UTF-8';
        }
        if (self::countMatches('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $source) !== self::countMatches('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $translation)) {
            return 'control characters';
        }
        if (self::getProtectedTokens($source) !== self::getProtectedTokens($translation)) {
            return 'placeholders, HTML or URLs changed';
        }
        if (strpos($source, '<') === false && strpos($translation, '<') !== false) {
            return 'adds HTML';
        }
        if (preg_match('/javascript:|\son[a-z]+\s*=/i', $translation) && !preg_match('/javascript:|\son[a-z]+\s*=/i', $source)) {
            return 'adds script';
        }
        return '';
    }

    public static function getProtectedTokens($text)
    {
        preg_match_all(self::PROTECTED_TOKENS_REGEX, $text, $matches);
        $tokens = $matches[0];
        sort($tokens);
        return $tokens;
    }

    private static function countMatches($regex, $text)
    {
        return preg_match_all($regex, $text);
    }

    /**
     * Gives the translation the same leading/trailing white space as the original, since the
     * code often concatenates these texts.
     */
    public static function keepSurroundingSpaces($source, $translation)
    {
        preg_match('/^\s*/', $source, $lead);
        preg_match('/\s*$/', $source, $trail);
        return $lead[0] . trim($translation) . $trail[0];
    }

    // ------------------------------------------------------------------ AI translation

    /**
     * Claude API settings, from videos/configuration.php or the environment:
     *   $global['localeTranslatorApiKey'] (or the ANTHROPIC_API_KEY environment variable)
     *   $global['localeTranslatorModel']     default claude-opus-5-5
     *   $global['localeTranslatorEffort']    default low (low, medium, high, xhigh, max)
     *   $global['localeTranslatorBatchSize'] default 40 texts per request
     */
    public static function getAIConfig()
    {
        global $global;
        $apiKey = !empty($global['localeTranslatorApiKey']) ? $global['localeTranslatorApiKey'] : getenv('ANTHROPIC_API_KEY');
        $effort = !empty($global['localeTranslatorEffort']) ? $global['localeTranslatorEffort'] : self::DEFAULT_EFFORT;
        if (!in_array($effort, ['low', 'medium', 'high', 'xhigh', 'max'], true)) {
            $effort = self::DEFAULT_EFFORT;
        }
        $batchSize = !empty($global['localeTranslatorBatchSize']) ? intval($global['localeTranslatorBatchSize']) : self::DEFAULT_BATCH_SIZE;
        return [
            'apiKey' => is_string($apiKey) ? trim($apiKey) : '',
            'model' => !empty($global['localeTranslatorModel']) ? $global['localeTranslatorModel'] : self::DEFAULT_MODEL,
            'effort' => $effort,
            'batchSize' => max(1, min(self::MAX_BATCH_SIZE, $batchSize)),
        ];
    }

    public static function isAIConfigured()
    {
        $config = self::getAIConfig();
        return !empty($config['apiKey']);
    }

    public static function getLanguageName($lang)
    {
        global $global;
        if (empty($global['bcp47']) && !empty($global['systemRootPath']) && file_exists($global['systemRootPath'] . 'objects/bcp47.php')) {
            include_once $global['systemRootPath'] . 'objects/bcp47.php';
        }
        if (!empty($global['bcp47'][$lang]['label'])) {
            return $global['bcp47'][$lang]['label'];
        }
        return $lang;
    }

    public static function isKnownLanguage($lang)
    {
        return self::getLanguageName($lang) !== $lang;
    }

    public static function buildAIRequest(array $strings, $lang, $languageName, array $config)
    {
        $system = "You translate the user interface of AVideo, an open-source web platform for video sharing, live streaming and monetization, from English into {$languageName} ({$lang}).\n"
            . "Rules:\n"
            . "- Write the text a native speaker expects on a website in that language, using the usual terms for video platforms.\n"
            . "- Keep placeholders exactly as they are: %s, %d, %1\$s, {name}, {{name}}, _NAME_.\n"
            . "- Keep HTML tags with all their attributes, HTML entities (&nbsp;) and URLs exactly as they are; translate only the visible text between tags.\n"
            . "- Keep product names, brand names and technical terms in their usual form (AVideo, YouTube, PayPal, RTMP, HLS, API, URL, FFmpeg).\n"
            . "- Keep the capitalization style and the final punctuation of the original.\n"
            . "- When an item should not be translated (a code, a URL, a file name, a brand), return it unchanged.\n"
            . "- Return every id exactly once, with only the translated text.";
        $items = [];
        foreach (array_values($strings) as $index => $string) {
            $items[] = ['id' => (string) ($index + 1), 'text' => $string];
        }
        $schema = [
            'type' => 'object',
            'properties' => [
                'translations' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'string'],
                            'text' => ['type' => 'string'],
                        ],
                        'required' => ['id', 'text'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['translations'],
            'additionalProperties' => false,
        ];
        $body = [
            'model' => $config['model'],
            'max_tokens' => 16000,
            'system' => $system,
            'messages' => [[
                'role' => 'user',
                'content' => "Translate the text of each item into {$languageName}.\n\n" . json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            ]],
            'output_config' => [
                'effort' => $config['effort'],
                'format' => ['type' => 'json_schema', 'schema' => $schema],
            ],
        ];
        $headers = [
            'content-type: application/json',
            'x-api-key: ' . $config['apiKey'],
            'anthropic-version: ' . self::ANTHROPIC_VERSION,
        ];
        if (in_array($config['model'], self::FALLBACK_MODELS, true)) {
            // On a policy decline the API re-runs the request on its recommended fallback model.
            $body['fallbacks'] = 'default';
            $headers[] = 'anthropic-beta: server-side-fallback-2026-07-01';
        }
        return ['body' => $body, 'headers' => $headers];
    }

    /**
     * @return array id => translated text
     */
    public static function parseAIResponse($responseBody, $httpCode)
    {
        $response = json_decode((string) $responseBody, true);
        if ($httpCode !== 200) {
            $message = !empty($response['error']['message']) ? $response['error']['message'] : 'no response';
            throw new Exception("Claude API error (HTTP {$httpCode}): {$message}");
        }
        if (!is_array($response)) {
            throw new Exception('Claude API returned an invalid response');
        }
        $stopReason = isset($response['stop_reason']) ? $response['stop_reason'] : '';
        if ($stopReason === 'refusal') {
            throw new Exception('Claude declined to translate this batch');
        }
        if ($stopReason === 'max_tokens') {
            throw new Exception('The translation was cut off; use a smaller batch size');
        }
        $text = '';
        foreach (isset($response['content']) && is_array($response['content']) ? $response['content'] : [] as $block) {
            if (isset($block['type']) && $block['type'] === 'text' && isset($block['text'])) {
                $text .= $block['text'];
            }
        }
        $data = json_decode($text, true);
        if (!is_array($data) || !isset($data['translations']) || !is_array($data['translations'])) {
            throw new Exception('Claude API returned no translations');
        }
        $translations = [];
        foreach ($data['translations'] as $item) {
            if (isset($item['id'], $item['text']) && is_string($item['id']) && is_string($item['text'])) {
                $translations[$item['id']] = $item['text'];
            }
        }
        return $translations;
    }

    // Tests replace the HTTP call with a callable($request) returning [body, httpCode].
    public static $requestHandler = null;

    /**
     * @return array [response body, HTTP code]
     */
    private static function sendAIRequest(array $request)
    {
        if (is_callable(self::$requestHandler)) {
            return call_user_func(self::$requestHandler, $request);
        }
        $ch = curl_init(self::ANTHROPIC_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($request['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => $request['headers'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 600,
            // Fixed public endpoint: always verify the certificate.
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $responseBody = curl_exec($ch);
        $httpCode = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($responseBody === false) {
            throw new Exception('Unable to reach the Claude API: ' . $curlError);
        }
        return [$responseBody, $httpCode];
    }

    /**
     * Translates texts with the Claude API and checks each answer.
     *
     * @return array ['translations' => [text => translation], 'rejected' => [text => reason]]
     */
    public static function translateWithAI(array $strings, $lang, $config = null)
    {
        if (empty($config)) {
            $config = self::getAIConfig();
        }
        if (empty($config['apiKey'])) {
            throw new Exception('The Claude API key is not configured');
        }
        $strings = array_values($strings);
        if (empty($strings)) {
            return ['translations' => [], 'rejected' => []];
        }
        $request = self::buildAIRequest($strings, $lang, self::getLanguageName($lang), $config);
        list($responseBody, $httpCode) = self::sendAIRequest($request);
        $byId = self::parseAIResponse($responseBody, $httpCode);

        $result = ['translations' => [], 'rejected' => []];
        foreach ($strings as $index => $source) {
            $id = (string) ($index + 1);
            if (!isset($byId[$id])) {
                $result['rejected'][$source] = 'not returned';
                continue;
            }
            $translation = self::keepSurroundingSpaces($source, $byId[$id]);
            $problem = self::getTranslationProblem($source, $translation);
            if ($problem !== '') {
                $result['rejected'][$source] = $problem;
                continue;
            }
            $result['translations'][$source] = $translation;
        }
        return $result;
    }

    /**
     * Translates the next batch of missing texts of a language and saves it.
     *
     * $offset skips texts that were rejected before in the same run, so they are not sent again.
     *
     * @return array ['translated' => int, 'rejected' => [text => reason], 'missing' => int, 'total' => int]
     */
    public static function translateNextBatch($lang, array $strings, $offset = 0, $dir = '', $config = null)
    {
        if (empty($config)) {
            $config = self::getAIConfig();
        }
        if (!self::canTranslateLanguage($lang, $dir)) {
            throw new Exception('This language is not translated automatically');
        }
        $missing = self::getMissingStrings($strings, self::getEffectiveEntries($lang, $dir));
        $batch = array_slice($missing, max(0, intval($offset)), $config['batchSize']);
        $result = ['translated' => 0, 'rejected' => [], 'missing' => count($missing), 'total' => count($strings)];
        if (empty($batch)) {
            return $result;
        }
        $translated = self::translateWithAI($batch, $lang, $config);
        if (!empty($translated['translations'])) {
            self::saveTranslations($lang, $translated['translations'], $dir);
            self::addSourceTexts(array_keys($translated['translations']), $dir);
        }
        $result['translated'] = count($translated['translations']);
        $result['rejected'] = $translated['rejected'];
        $result['missing'] -= $result['translated'];
        return $result;
    }

    /**
     * Status of every language file for the admin page and the command line.
     */
    public static function getStatus(array $strings, $dir = '')
    {
        $languages = [];
        foreach (self::listLanguageFiles($dir) as $lang => $file) {
            $language = self::readLanguage($lang, $dir);
            $missing = self::getMissingStrings($strings, self::getEffectiveEntries($lang, $dir));
            $languages[] = [
                'code' => $lang,
                'name' => self::getLanguageName($lang),
                'entries' => count($language['entries']),
                'duplicates' => $language['duplicates'],
                'missing' => count($missing),
                'translated' => count($strings) - count($missing),
                'base' => empty($language['includes']) ? '' : implode(', ', $language['includes']),
                'errors' => $language['errors'],
                'canTranslate' => $lang !== self::SOURCE_LANGUAGE && self::canTranslateLanguage($lang, $dir),
            ];
        }
        return $languages;
    }
}
