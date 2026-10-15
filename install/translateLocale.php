<?php
/**
 * Keeps the translation files in locale/ complete, sorted and without repeated texts.
 *
 * php install/translateLocale.php                      status of every language
 * php install/translateLocale.php --normalize          rewrite every file sorted, without duplicates (no AI)
 * php install/translateLocale.php --translate          translate the missing texts of every language with AI
 *                                                      (en_US first receives every text, translated to itself)
 * php install/translateLocale.php --translate --lang=pt_BR,es
 *                                                      only these languages (a new code from
 *                                                      objects/bcp47.php creates its file)
 * Options for --translate:
 *   --batch=40          texts per AI request (default $global['localeTranslatorBatchSize'] or 40)
 *   --max-batches=N     stop each language after N requests
 *   --dry-run           show what would be translated, without calling the AI or writing files
 *
 * AI: the Claude API key comes from $global['localeTranslatorApiKey'] in videos/configuration.php
 * or the ANTHROPIC_API_KEY environment variable (see LocaleTranslator::getAIConfig()).
 * Every file is validated before it replaces the old one, so an interrupted run loses nothing:
 * run it again and it continues with the texts that are still missing.
 */
require_once __DIR__ . '/../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
require_once $global['systemRootPath'] . 'objects/LocaleTranslator.php';

$options = getopt('', ['normalize', 'translate', 'lang:', 'batch:', 'max-batches:', 'dry-run']);
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

if (isset($options['translate'])) {
    $config = LocaleTranslator::getAIConfig();
    if (!empty($options['batch'])) {
        $config['batchSize'] = max(1, min(LocaleTranslator::MAX_BATCH_SIZE, intval($options['batch'])));
    }
    $dryRun = isset($options['dry-run']);
    if (empty($config['apiKey']) && !$dryRun) {
        echo "Set \$global['localeTranslatorApiKey'] in videos/configuration.php or the ANTHROPIC_API_KEY environment variable." . PHP_EOL;
        exit(2);
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
