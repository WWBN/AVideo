<?php
/**
 * Admin endpoint of the translation page (locale/index.php). Logic lives in LocaleTranslator.
 *
 * action=status     languages, how many texts each one is missing, AI configuration (read only)
 * action=strings    texts of one language for the manual editor (lang, mode=missing|all)
 * action=translate  POST: translates the next batch of missing texts with the Claude API (lang, offset)
 * action=save       POST: adds/replaces translations (lang, translations = JSON [[text, translation], ...])
 * action=normalize  POST: rewrites a file (or lang=all) sorted and without repeated texts
 * action=source     POST: adds the texts missing in en_US, the catalog the other languages follow
 */
header('Content-Type: application/json');
global $global;
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/LocaleTranslator.php';

if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}
if (!empty($global['disableAdvancedConfigurations'])) {
    forbiddenPage('Translations are disabled on this site', true);
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'status';
if (in_array($action, ['translate', 'save', 'normalize', 'source'], true)) {
    forbidIfNotPost();
    if (!isGlobalTokenValid()) {
        forbiddenPage('Invalid or missing CSRF token', true);
    }
}
$lang = isset($_REQUEST['lang']) ? (string) $_REQUEST['lang'] : '';

/**
 * Existing language files, or a language AVideo knows (objects/bcp47.php) when creating one.
 */
function localeTranslatorCheckLanguage($lang)
{
    $languages = LocaleTranslator::listLanguageFiles();
    if (!LocaleTranslator::isValidLanguageCode($lang) || (!isset($languages[$lang]) && !LocaleTranslator::isKnownLanguage($lang))) {
        throw new Exception('Invalid language');
    }
}

$response = ['error' => false, 'msg' => ''];
try {
    switch ($action) {
        case 'status':
            $strings = listAllWordsToTranslate(!empty($_REQUEST['rescan']));
            $config = LocaleTranslator::getAIConfig();
            $response['strings'] = count($strings);
            $response['languages'] = LocaleTranslator::getStatus($strings);
            $response['ai'] = [
                'configured' => !empty($config['apiKey']),
                'model' => $config['model'],
                'batchSize' => $config['batchSize'],
            ];
            include_once $global['systemRootPath'] . 'objects/bcp47.php';
            $existing = LocaleTranslator::listLanguageFiles();
            $response['newLanguages'] = [];
            foreach (isset($global['bcp47']) ? $global['bcp47'] : [] as $code => $language) {
                if (!isset($existing[$code]) && LocaleTranslator::isValidLanguageCode($code) && strpos($code, 'en') !== 0) {
                    $response['newLanguages'][] = ['code' => $code, 'name' => $language['label']];
                }
            }
            break;

        case 'strings':
            localeTranslatorCheckLanguage($lang);
            $strings = listAllWordsToTranslate();
            $entries = LocaleTranslator::getEffectiveEntries($lang);
            if (empty($_REQUEST['mode']) || $_REQUEST['mode'] !== 'all') {
                $strings = LocaleTranslator::getMissingStrings($strings, $entries);
            }
            $response['items'] = [];
            foreach ($strings as $string) {
                $response['items'][] = [$string, isset($entries[$string]) ? (string) $entries[$string] : ''];
            }
            break;

        case 'translate':
            localeTranslatorCheckLanguage($lang);
            if (!LocaleTranslator::isAIConfigured()) {
                throw new Exception('The Claude API key is not configured');
            }
            // The AI call can take a while: do not keep the admin's other tabs waiting on the session.
            _session_write_close();
            @set_time_limit(900);
            $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
            $result = LocaleTranslator::translateNextBatch($lang, listAllWordsToTranslate(), $offset);
            $response = array_merge($response, $result);
            $response['rejectedCount'] = count($result['rejected']);
            if (!empty($result['rejected'])) {
                _error_log('LocaleTranslator: ' . $lang . ' rejected ' . json_encode($result['rejected'], JSON_UNESCAPED_UNICODE), AVideoLog::$WARNING);
            }
            break;

        case 'save':
            localeTranslatorCheckLanguage($lang);
            $pairs = isset($_POST['translations']) ? json_decode((string) $_POST['translations'], true) : null;
            if (!is_array($pairs)) {
                throw new Exception('Invalid translations');
            }
            $translations = [];
            $rejected = [];
            foreach ($pairs as $pair) {
                if (!is_array($pair) || count($pair) !== 2 || !is_string($pair[0]) || !is_string($pair[1])) {
                    throw new Exception('Invalid translations');
                }
                if (trim($pair[1]) === '') {
                    continue; // an empty line means "not translated yet"
                }
                $problem = LocaleTranslator::getTranslationProblem($pair[0], $pair[1]);
                if ($problem !== '') {
                    $rejected[$pair[0]] = $problem;
                    continue;
                }
                $translations[$pair[0]] = $pair[1];
            }
            $response = array_merge($response, LocaleTranslator::saveTranslations($lang, $translations));
            LocaleTranslator::addSourceTexts(array_keys($translations));
            $response['rejected'] = $rejected;
            $response['rejectedCount'] = count($rejected);
            break;

        case 'normalize':
            $languages = LocaleTranslator::listLanguageFiles();
            $targets = $lang === 'all' ? array_keys($languages) : [$lang];
            $response['languages'] = [];
            foreach ($targets as $target) {
                if (!isset($languages[$target])) {
                    throw new Exception('Invalid language');
                }
                $response['languages'][$target] = LocaleTranslator::normalizeLanguage($target);
            }
            break;

        case 'source':
            $response['added'] = LocaleTranslator::addSourceTexts(listAllWordsToTranslate());
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    // LocaleTranslator only throws messages meant for the admin (no paths, no secrets).
    $response = ['error' => true, 'msg' => $e->getMessage()];
} catch (\Throwable $th) {
    _error_log('localeTranslator.json.php: ' . $th->getMessage(), AVideoLog::$ERROR);
    $response = ['error' => true, 'msg' => 'An error occurred'];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
