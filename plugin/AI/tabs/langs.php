<?php
require_once '../../../videos/configuration.php';
if (!AVideoPlugin::isEnabledByName('AI')) {
    forbiddenPage('AI plugin is disabled');
}
$videos_id = getVideos_id();
if (empty($videos_id)) {
    forbiddenPage('Videos ID is required');
}
$currentLangCodes = AI::getVTTLanguageCodes($videos_id);
foreach ($global['langs_codes'] as $key => $value) {
    echo '<div class="checkbox">';
    if (in_array($value['value'], $currentLangCodes)) {
        echo "<i class=\"fa-regular fa-square-check langButton\" onclick=\"deleteLang($key);\"></i> <i class=\"flagstrap-icon flagstrap-{$value['flag']}\"></i> [{$value['value']}] {$value['label']}";
    } else {
        $checked = isset($_COOKIE['lang_' . $value['value']]) && $_COOKIE['lang_' . $value['value']] == 'true' ? 'checked' : '';
        echo "  <label>
                <input type=\"checkbox\" class=\"languageCheckbox\" data-lang-code=\"{$value['value']}\" value=\"{$value['label']}\" {$checked}>
                <i class=\"flagstrap-icon flagstrap-{$value['flag']}\"></i> [{$value['value']}] {$value['label']}
            </label>
            <span id=\"progress{$value['value']}\" class=\"badge\" style=\"display:none;\">...</span>";
    }

    echo '</div>';
}
