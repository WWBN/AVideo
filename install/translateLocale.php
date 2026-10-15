<?php
/**
 * Keeps the translation files in locale/ complete, sorted and without repeated texts.
 *
 * php install/translateLocale.php                      status of every language
 * php install/translateLocale.php --normalize          rewrite every file sorted, without duplicates (no AI)
 * php install/translateLocale.php --prune --dry-run    list the translations nothing uses any more
 * php install/translateLocale.php --prune              remove them from every file (or --lang=...)
 *                                                      A text stays when it is given to __(), differs
 *                                                      only in letter case from one that is, is
 *                                                      written anywhere in the code (PHP/JS/HTML/JSON/
 *                                                      SQL) as a plain string (it may reach __()
 *                                                      through a variable), or matches a text built at
 *                                                      runtime (__("{$i} Month" . ...)). Run it
 *                                                      in a checkout with every plugin you ship, and
 *                                                      review the diff: the mobile app receives these
 *                                                      files too (/api/locale).
 * php install/translateLocale.php --translate          translate the missing texts of every language with AI
 *                                                      (en_US first receives every text, translated to itself)
 * php install/translateLocale.php --translate --lang=pt_BR,es
 *                                                      only these languages (a new code from
 *                                                      objects/bcp47.php creates its file)
 * Options for --translate:
 *   --batch=40          texts per AI request
 *   --max-batches=N     stop each language after N requests
 *   --dry-run           show what would be translated, without calling the AI or writing files
 *   --provider=api|claude-cli
 *                       api: Claude API key from the ANTHROPIC_API_KEY environment variable.
 *                       claude-cli: `claude -p` with the Claude Code login of whoever runs this
 *                       command (no API key; counts against that Claude plan).
 *                       Default: api when a key is set, otherwise claude-cli.
 *   --claude=PATH       Claude Code executable (default: `claude` in the PATH or the VS Code
 *                       extension's bundled binary)
 *   --model=NAME        default claude-opus-5-5
 *   --effort=LEVEL      low (default), medium, high, xhigh, max
 * Every file is validated before it replaces the old one, so an interrupted run loses nothing:
 * run it again and it continues with the texts that are still missing.
 *
 * It does not load videos/configuration.php: it only reads the code and writes locale/, so it
 * needs no database and works in any checkout (the folder above this one is the AVideo root).
 */
if (PHP_SAPI !== 'cli') {
    die('Command Line only');
}
$global = ['systemRootPath' => str_replace('\\', '/', dirname(__DIR__)) . '/'];
require_once $global['systemRootPath'] . 'objects/LocaleTranslator.php';

$options = getopt('', ['normalize', 'prune', 'translate', 'lang:', 'batch:', 'max-batches:', 'dry-run', 'provider:', 'claude:', 'model:', 'effort:']);
if (!empty($options['model'])) {
    $global['localeTranslatorModel'] = $options['model'];
}
if (!empty($options['effort'])) {
    $global['localeTranslatorEffort'] = $options['effort'];
}
$languages = LocaleTranslator::listLanguageFiles();
$selected = empty($options['lang']) ? [] : array_filter(array_map('trim', explode(',', $options['lang'])));
foreach ($selected as $lang) {
    if (!LocaleTranslator::isValidLanguageCode($lang) || (!isset($languages[$lang]) && !LocaleTranslator::isKnownLanguage($lang))) {
        echo "Unknown language: {$lang}" . PHP_EOL;
        exit(2);
    }
}

echo "Scanning the code for texts..." . PHP_EOL;
$strings = LocaleTranslator::extractStrings();
echo count($strings) . " texts found" . PHP_EOL . PHP_EOL;

if (isset($options['normalize'])) {
    foreach (empty($selected) ? array_keys($languages) : $selected as $lang) {
        try {
            $result = LocaleTranslator::normalizeLanguage($lang);
            echo str_pad($lang, 12) . " {$result['total']} texts, {$result['duplicatesRemoved']} repeated removed" . PHP_EOL;
        } catch (Exception $e) {
            echo str_pad($lang, 12) . " ERROR " . $e->getMessage() . PHP_EOL;
        }
    }
    exit(0);
}

if (isset($options['prune'])) {
    $dryRun = isset($options['dry-run']);
    echo "Collecting every string written in the code and the texts it builds at runtime..." . PHP_EOL;
    $usage = LocaleTranslator::extractLiterals();
    $removedTotal = 0;
    foreach (empty($selected) ? array_keys($languages) : $selected as $lang) {
        try {
            $result = LocaleTranslator::pruneLanguage($lang, $strings, $usage, '', $dryRun);
        } catch (Exception $e) {
            echo str_pad($lang, 12) . " ERROR " . $e->getMessage() . PHP_EOL;
            continue;
        }
        $removedTotal += count($result['removed']);
        echo str_pad($lang, 12) . ' ' . count($result['removed']) . ($dryRun ? ' unused' : ' removed') . ", {$result['total']} texts" . PHP_EOL;
        if ($dryRun) {
            foreach (array_slice($result['removed'], 0, 10) as $text) {
                echo "    " . json_encode($text, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            }
        }
    }
    echo PHP_EOL . ($dryRun ? "{$removedTotal} unused translations; run without --dry-run to remove them." : "{$removedTotal} translations removed; review them with git diff locale/.") . PHP_EOL;
    exit(0);
}

if (isset($options['translate'])) {
    $config = LocaleTranslator::getAIConfig();
    if (!empty($options['provider']) || !empty($options['claude'])) {
        $provider = !empty($options['provider']) ? $options['provider'] : LocaleTranslator::PROVIDER_CLAUDE_CLI;
        if (!in_array($provider, [LocaleTranslator::PROVIDER_API, LocaleTranslator::PROVIDER_CLAUDE_CLI], true)) {
            echo "--provider must be api or claude-cli" . PHP_EOL;
            exit(2);
        }
        $config = LocaleTranslator::setAIProvider($config, $provider, empty($options['claude']) ? '' : $options['claude']);
    }
    if (!empty($options['batch'])) {
        $config['batchSize'] = max(1, min(LocaleTranslator::MAX_BATCH_SIZE, intval($options['batch'])));
    }
    $dryRun = isset($options['dry-run']);
    if ($config['provider'] === '' && !$dryRun) {
        echo "No AI available. Either set \$global['localeTranslatorApiKey'] in videos/configuration.php (or the" . PHP_EOL
            . "ANTHROPIC_API_KEY environment variable), or install Claude Code, log in, and use --provider=claude-cli" . PHP_EOL
            . "(--claude=PATH when the claude executable is not in the PATH)." . PHP_EOL;
        exit(2);
    }
    if ($config['provider'] === LocaleTranslator::PROVIDER_CLAUDE_CLI) {
        echo "Using Claude Code with your own login: {$config['claudePath']}" . PHP_EOL;
    }
    $maxBatches = empty($options['max-batches']) ? 0 : intval($options['max-batches']);
    $targets = $selected;
    if (empty($targets)) {
        foreach (array_keys($languages) as $lang) {
            if (LocaleTranslator::canTranslateLanguage($lang)) {
                $targets[] = $lang;
            }
        }
    }
    echo "Model {$config['model']}, effort {$config['effort']}, {$config['batchSize']} texts per request" . PHP_EOL;
    if (!$dryRun) {
        // en_US is the catalog the other languages follow (each text translated to itself).
        echo str_pad(LocaleTranslator::SOURCE_LANGUAGE, 12) . ' +' . LocaleTranslator::addSourceTexts($strings) . ' texts added (no AI needed)' . PHP_EOL;
    }
    $exitCode = 0;
    foreach ($targets as $lang) {
        if (!LocaleTranslator::canTranslateLanguage($lang)) {
            echo str_pad($lang, 12) . " skipped (source language, or it only overrides another file)" . PHP_EOL;
            continue;
        }
        $name = LocaleTranslator::getLanguageName($lang);
        $missing = LocaleTranslator::getMissingStrings($strings, LocaleTranslator::getEffectiveEntries($lang));
        echo str_pad($lang, 12) . " {$name}: " . count($missing) . " missing" . PHP_EOL;
        if ($dryRun) {
            foreach (array_slice($missing, 0, 10) as $text) {
                echo "    " . json_encode($text, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            }
            continue;
        }
        $offset = 0;
        $batches = 0;
        while (true) {
            if ($maxBatches && $batches >= $maxBatches) {
                break;
            }
            try {
                $result = LocaleTranslator::translateNextBatch($lang, $strings, $offset, '', $config);
            } catch (Exception $e) {
                echo "    ERROR " . $e->getMessage() . PHP_EOL;
                $exitCode = 1;
                break;
            }
            $batches++;
            if ($result['translated'] === 0 && empty($result['rejected'])) {
                break; // nothing left to send
            }
            foreach ($result['rejected'] as $text => $reason) {
                echo "    rejected ({$reason}): " . json_encode($text, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            }
            // Rejected texts stay missing; skip them so the next request sends new ones.
            $offset += count($result['rejected']);
            echo "    +{$result['translated']} translated, {$result['missing']} missing" . PHP_EOL;
            if ($result['missing'] <= $offset) {
                break;
            }
        }
    }
    exit($exitCode);
}

printf("%-12s %-36s %8s %8s %10s  %s" . PHP_EOL, 'Language', 'Name', 'Missing', 'Texts', 'Repeated', 'Notes');
foreach (LocaleTranslator::getStatus($strings) as $language) {
    $notes = [];
    if ($language['base'] !== '') {
        $notes[] = "overrides {$language['base']}";
    }
    if ($language['code'] === LocaleTranslator::SOURCE_LANGUAGE) {
        $notes[] = 'source language';
    }
    if (!empty($language['errors'])) {
        $notes[] = 'ERROR ' . implode('; ', $language['errors']);
    }
    printf("%-12s %-36s %8d %8d %10d  %s" . PHP_EOL, $language['code'], mb_substr($language['name'], 0, 36), $language['missing'], $language['entries'], $language['duplicates'], implode(', ', $notes));
}
echo PHP_EOL . "--normalize sorts the files and removes repeated texts; --translate fills the missing ones with AI." . PHP_EOL;
