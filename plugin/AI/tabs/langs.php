<?php
require_once '../../../videos/configuration.php';
if (!AVideoPlugin::isEnabledByName('AI')) {
    forbiddenPage('AI plugin is disabled');
}

if(!AI::canUseAI()){
    forbiddenPage('You cannot use AI');
}

require_once $global['systemRootPath'] . 'objects/bcp47.php';
$videos_id = getVideos_id();
if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}
echo '<!-- langs_codes='.count($global['langs_codes']).' -->';
$currentLangCodes = AI::getVTTLanguageCodes($videos_id);
echo '<!-- currentLangCodes='.count($currentLangCodes).' -->';
foreach ($global['langs_codes'] as $key => $value) {
    if(!preg_match('/^[a-z]{2}(_[a-z]{2})?$/i', $value['value'])){
        echo '<!-- not a lang='.$value['value'].' -->';
        continue;
    }
    echo '<div class="checkbox">';
    if (in_array($value['value'], $currentLangCodes)) {
        echo "<i class=\"fa-regular fa-square-check langButton\" onclick=\"deleteLang($key);\"></i> <i class=\"flagstrap-icon flagstrap-{$value['flag']}\"></i> [{$value['value']}] {$value['label']}";
    } else {
        $checked = isset($_COOKIE['lang_' . $value['value']]) && $_COOKIE['lang_' . $value['value']] == 'true' ? 'checked' : '';
        echo "  <label>
                <input type=\"checkbox\" class=\"languageCheckbox\" data-lang-code=\"{$value['value']}\" value=\"{$value['label']}\" {$checked}>
                <i class=\"flagstrap-icon flagstrap-{$value['flag']}\"></i> [{$value['value']}] {$value['label']}
            </label>".AI::getProgressBarHTML("translation_{$value['value']}_{$videos_id}", '');
    }

    echo '</div>';
}
