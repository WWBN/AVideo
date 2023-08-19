<?php

if (empty($_GET['lang'])) {
    return '';
}

$lang = preg_replace('/[^a-z0-9_]/i', '', $_GET['lang']);

$langFile = "./{$lang}.php";
if(!file_exists($langFile)){
    return '';
}

include_once $langFile;
header('Content-Type: application/javascript');
?>
var translations = <?php echo json_encode($t); ?>;

function __(str, allowHTML = false) {
    let returnStr = str;
    
    // Check if translation exists for exact string
    if (translations.hasOwnProperty(str)) {
        returnStr = translations[str];
    } else {
        // Case insensitive check
        let lowerCaseKey = Object.keys(translations).find(key => key.toLowerCase() === str.toLowerCase());
        if (lowerCaseKey) {
            returnStr = translations[lowerCaseKey];
        }
    }

    if (allowHTML) {
        return returnStr;
    }

    // Escape certain characters for security
    return returnStr.replace(/'/g, "&apos;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}