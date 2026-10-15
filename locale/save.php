<?php
// Kept for pages/scripts that still post the old `code` format ($t['text'] = "translation"; lines).
// The posted code is never written as is: it is read as data and merged into the language file by
// LocaleTranslator, which rewrites the file sorted, without duplicates and always valid PHP.
// The translation page now uses objects/localeTranslator.json.php.
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/LocaleTranslator.php';
$obj = new stdClass();
$obj->status = "1";
$obj->error = "";
if (!User::isAdmin() || !empty($global['disableAdvancedConfigurations'])) {
    forbiddenPage('Permission denied', true);
}
if (!isGlobalTokenValid()) {
    forbiddenPage('Invalid or missing CSRF token', true);
}
if (empty($_REQUEST['custom'])) {
    $dir = "{$global['systemRootPath']}locale/";
} else {
    $dir = "{$global['systemRootPath']}videos/locale/";
    make_path($dir);
}
if (!is_writable($dir) && !isWindowsServer()) {
    $obj->status = 0;
    $obj->error = sprintf(__("Your %s locale dir is not writable"), $global['systemRootPath']);
    die(json_encode($obj));
}

if (empty($_POST['flag'])) {
    forbiddenPage('Flag is empty');
}
$flag = basename($_POST['flag']);
if (!LocaleTranslator::isValidLanguageCode($flag)) {
    $obj->status = 0;
    $obj->error = __("Invalid locale flag");
    die(json_encode($obj));
}

try {
    $parsed = LocaleTranslator::parseLanguageCode("<?php\n" . (isset($_POST['code']) ? (string) $_POST['code'] : ''));
    if (!empty($parsed['errors']) || !empty($parsed['includes'])) {
        throw new Exception('Only lines like $t[\'text\'] = \'translation\'; are accepted: ' . implode('; ', $parsed['errors']));
    }
    $translations = [];
    foreach ($parsed['entries'] as $text => $translation) {
        if (trim($translation) !== '' && LocaleTranslator::getTranslationProblem($text, $translation) === '') {
            $translations[$text] = $translation;
        }
    }
    LocaleTranslator::saveTranslations($flag, $translations, $dir);
    if (empty($_REQUEST['custom'])) {
        LocaleTranslator::addSourceTexts(array_keys($translations), $dir);
    }
} catch (Exception $e) {
    $obj->status = 0;
    $obj->error = $e->getMessage();
} catch (\Throwable $th) {
    _error_log('locale/save.php: ' . $th->getMessage(), AVideoLog::$ERROR);
    $obj->status = 0;
    $obj->error = __("Unable to open file!");
}
echo json_encode($obj);
