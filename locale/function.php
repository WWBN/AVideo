<?php

global $t;
// filter some security here
if (!empty($_GET['lang'])) {
    $_GET['lang'] = str_replace(["'", '"', "&quot;", "&#039;"], ['', '', '', ''], xss_esc($_GET['lang']));
}

includeLangFile();

function includeLangFile() {
    global $t, $global;
    setSiteLang();
    @include_once "{$global['systemRootPath']}locale/{$_SESSION['language']}.php";
}

function __($str, $allowHTML = false) {
    global $t, $t_insensitive;
    if (!isset($t_insensitive)) {
        if (is_array($t) && function_exists('array_change_key_case') && !isCommandLineInterface()) {
            $t_insensitive = array_change_key_case($t, CASE_LOWER);
        } else {
            $t_insensitive = [];
        }
    }
    $return = $str;

    if (!empty($t[$str])) {
        $return = $t[$str];
    } elseif (!empty($t_insensitive) && !empty($t_insensitive[strtolower($str)])) {
        $return = $t_insensitive[strtolower($str)];
    }

    if ($allowHTML) {
        return $return;
    }
    return str_replace(["'", '"', "<", '>'], ['&apos;', '&quot;', '&lt;', '&gt;'], $return);
}

function printJSString($str, $return = false) {
    $text = json_encode(__($str), JSON_UNESCAPED_UNICODE);
    if ($return) {
        return $text;
    } else {
        echo $text;
    }
}

function isRTL() {
    global $t_isRTL;
    return _isRTL(getLanguage()) || (!empty($t_isRTL) && $t_isRTL);
}

function _isRTL($code) {
    // Convert input to lowercase and replace dashes with underscores to make comparison case insensitive and format uniform
    $code = str_replace('-', '_', strtolower($code));

    // Array of RTL short codes
    $rtlLanguages = array('ar', 'ar_sa', 'fa', 'fa_ir', 'ur', 'ur_pk', 'he', 'he_il', 'yi', 'yi_de', 'sd', 'sd_in', 'ps', 'ps_af', 'dv', 'dv_mv', 'ckb', 'ckb_iq');

    // Check if code is in the RTL array
    if(in_array($code, $rtlLanguages)) {
        return true;
    }

    return false;
}

function getAllFlags() {
    global $global;
    $dir = "{$global['systemRootPath']}view/css/flag-icon-css-master/flags/4x3";
    $flags = [];
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $flags[] = str_replace(".svg", "", $entry);
            }
        }
        closedir($handle);
    }
    sort($flags);
    return $flags;
}

/**
 * Deprecated replaced by Layout::getAvilableFlags()
 * @global array $global
 * @return array
 */
function getEnabledLangs() {
    global $global;
    $dir = "{$global['systemRootPath']}locale";
    $flags = [];
    if (empty($global['dont_show_us_flag'])) {
        $flags[] = 'us';
    }
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..' && $entry != 'index.php' && $entry != 'function.php' && $entry != 'save.php') {
                $flags[] = str_replace('.php', '', $entry);
            }
        }
        closedir($handle);
    }
    sort($flags);
    return $flags;
}

function textToLink($string, $targetBlank = false) {
    $target = "";
    if ($targetBlank) {
        $target = "target=\"_blank\"";
    }
    return preg_replace('$(\s|^)(https?://[a-z0-9_./?=&-]+)(?![^<>]*>)$i', ' <a href="$2" ' . $target . '>$2</a> ', $string);
}

function br2nl($html) {
    $nl = preg_replace(['#<br\s*/?>#i', '#<p\s*/?>#i', '#</p\s*>#i'], ["\n", "\n", ''], $html);
    return $nl;
}

function flag2Lang($flagCode) {
    global $global;
    $index = strtolower($flagCode);
    if (!empty($global['flag2Lang'][$index])) {
        return $global['flag2Lang'][$index];
    }
    return $flagCode;
}

function setSiteLang() {
    global $config, $global;
    if (empty($global['systemRootPath'])) {
        if (function_exists('getLanguageFromBrowser')) {
            includeConfigLog(__LINE__, basename(__FILE__));
            setLanguage(getLanguageFromBrowser());
        } else {
            includeConfigLog(__LINE__, basename(__FILE__));
            setLanguage('en_US');
        }
        includeConfigLog(__LINE__, basename(__FILE__));
    } else {
        includeConfigLog(__LINE__, basename(__FILE__));
        require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
        $userLocation = false;
        includeConfigLog(__LINE__, basename(__FILE__));
        $obj = AVideoPlugin::getDataObjectIfEnabled('User_Location');
        includeConfigLog(__LINE__, basename(__FILE__));
        $userLocation = !empty($obj) && !empty($obj->autoChangeLanguage);

        if (!empty($_GET['lang'])) {
            includeConfigLog(__LINE__, basename(__FILE__));
            _session_start();
            includeConfigLog(__LINE__, basename(__FILE__));
            setLanguage($_GET['lang']);
        } else if ($userLocation) {
            includeConfigLog(__LINE__, basename(__FILE__));
            User_Location::changeLang();
        }
        includeConfigLog(__LINE__, basename(__FILE__));
        try {
            if (empty($config) || !is_object($config)) {
                includeConfigLog(__LINE__, basename(__FILE__));
                require_once $global['systemRootPath'] . 'objects/configuration.php';
                if (class_exists('Configuration')) {
                    $config = new Configuration();
                } else {
                    //_error_log("setSiteLang ERROR 1 systemRootPath=[{$global['systemRootPath']}] " . json_encode(debug_backtrace()));
                }
                includeConfigLog(__LINE__, basename(__FILE__));
            }
        } catch (Exception $exc) {
            _error_log("setSiteLang ERROR 2 systemRootPath=[{$global['systemRootPath']}] " . $exc->getMessage() . ' ' . json_encode(debug_backtrace()));
        }

        if (empty($_SESSION['language']) && is_object($config)) {
            includeConfigLog(__LINE__, basename(__FILE__));
            setLanguage($config->getLanguage());
            includeConfigLog(__LINE__, basename(__FILE__));
        }
        if (empty($_SESSION['language'])) {
            if (function_exists('getLanguageFromBrowser')) {
                includeConfigLog(__LINE__, basename(__FILE__));
                setLanguage(getLanguageFromBrowser());
            } else {
                includeConfigLog(__LINE__, basename(__FILE__));
                setLanguage('en_US');
            }
            includeConfigLog(__LINE__, basename(__FILE__));
        }
    }
}

function setLanguage($lang) {
    $lang = strip_tags($lang);
    if (empty($lang)) {
        return false;
    }
    global $global;
    $lang = flag2Lang($lang);
    if (empty($lang) || $lang === '-') {
        return false;
    }

    $file = "{$global['systemRootPath']}locale/{$lang}.php";
    _session_start();
    if (file_exists($file)) {
        $_SESSION['language'] = $lang;
        include_once $file;
        return true;
    } else {
        //_error_log('setLanguage: File does not exists 1 ' . $file);
        $lang = strtolower($lang);
        $file = "{$global['systemRootPath']}locale/{$lang}.php";
        if (file_exists($file)) {
            $_SESSION['language'] = $lang;
            include_once $file;
            return true;
        } else {
            $parts = explode('_', $lang);
            $lang = $parts[0];
            $file = "{$global['systemRootPath']}locale/{$lang}.php";
            if (file_exists($file)) {
                $_SESSION['language'] = $lang;
                include_once $file;
                return true;
            } else {
                //_error_log('setLanguage: File does not exists 2 ' . $file);
            }
        }
    }
    return false;
}

function getLanguage() {
    if (empty($_SESSION['language'])) {
        return 'en_US';
    }
    return fixLangString($_SESSION['language']);
}

function fixLangString($lang) {
    return strtolower(str_replace('_', '-', $lang));
}

function revertLangString($lang) {
    $parts = explode('-', $lang);

    $lang = strtolower($parts[0]);
    if (!empty($parts[1])) {
        $lang .= '_' . strtoupper($parts[1]);
    }
    return $lang;
}

//var_dump(getLanguage());exit;