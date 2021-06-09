<?php
require_once $global['systemRootPath'] . 'objects/functions.php';

// filter some security here
$securityFilter = array('error', 'catName', 'type', 'channelName', 'captcha', 'showOnly', 'key', 'link', 'email', 'country', 'region', 'videoName');
$securityFilterInt = array('isAdmin', 'priority', 'totalClips', 'rowCount');
$securityRemoveSingleQuotes = array('search', 'searchPhrase', 'videoName', 'databaseName', 'sort', 'user', 'pass', 'encodedPass', 'isAdmin', 'videoLink', 'video_password');
$securityRemoveNonChars = array('resolution', 'format', 'videoDirectory');
$filterURL = array('videoURL', 'siteURL', 'redirectUri', 'encoderURL');

if (!empty($_FILES)) {
    foreach ($_FILES as $key => $value) {
        $_FILES[$key]['name'] = preg_replace('/[^a-z0-9.,()+& #-]/i', '', cleanString($_FILES[$key]['name']));
    }
}

$scanVars = array('_GET', '_POST', '_REQUEST');

foreach ($scanVars as $value) {
    $scanThis = &$$value;
    if (!empty($scanThis['base64Url'])) {
        if (!filter_var(base64_decode($scanThis['base64Url']), FILTER_VALIDATE_URL)) {
            _error_log('base64Url attack ' . json_encode($_SERVER), AVideoLog::$SECURITY);
            exit;
        }
    }
    if (!empty($scanThis['videos_id'])) {
        $scanThis['videos_id'] = videosHashToID($scanThis['videos_id']);
    }
    if (!empty($scanThis['v'])) {
        $originalValue = $scanThis['v'];
        $scanThis['v'] = videosHashToID($scanThis['v']);
        if(!empty($global['makeVideosIDHarderToGuessNotDecrypted']) && $originalValue != $scanThis['v']){
            // if you set $global['makeVideosIDHarderToGuessNotDecrypted'] and originalValue = scanThis['v'] it meand it was not decrypted, and it is a direct video ID, 
            // otherwiseit was a hash that we decrypt into an ID
            $global['makeVideosIDHarderToGuessNotDecrypted'] = 0;
        }        
    }

    foreach ($filterURL as $key => $value) {
        if (!empty($scanThis[$value])) {
            if (!filter_var($scanThis[$value], FILTER_VALIDATE_URL) || !preg_match("/^http.*/i", $scanThis[$value])) {
                //_error_log($value.' attack ' . json_encode($_SERVER), AVideoLog::$SECURITY);
                unset($scanThis[$value]);
            } else {
                $scanThis[$value] = str_replace(array("'", '"', "<", ">"), array("", "", "", ""), $scanThis[$value]);
            }
        }
    }

    foreach ($securityRemoveNonChars as $value) {
        if (!empty($scanThis[$value])) {
            if (is_string($scanThis[$value])) {
                $scanThis[$value] = str_replace('/[^a-z0-9./]/i', '', trim($scanThis[$value]));
            } elseif (is_array($scanThis[$value])) {
                foreach ($scanThis[$value] as $key => $value2) {
                    if (is_string($scanThis[$value][$key])) {
                        $scanThis[$value][$key] = str_replace('/[^a-z0-9./]/i', '', trim($scanThis[$value][$key]));
                    }
                }
            }
        }
    }

    foreach ($securityRemoveSingleQuotes as $value) {
        if (!empty($scanThis[$value])) {
            if (is_string($scanThis[$value])) {
                $scanThis[$value] = str_replace("'", "", trim($scanThis[$value]));
            } elseif (is_array($scanThis[$value])) {
                foreach ($scanThis[$value] as $key => $value2) {
                    if (is_string($scanThis[$value][$key])) {
                        $scanThis[$value][$key] = str_replace("'", "", trim($scanThis[$value][$key]));
                    }
                }
            }
        }
    }

    // all variables with _id at the end will be forced to be interger
    foreach ($scanThis as $key => $value) {
        if (preg_match('/_id$/i', $key)) {
            if(empty($value)){
                $scanThis[$key] = 0;
            }else 
            if (is_numeric($value)) {
                $scanThis[$key] = intval($value);
            } else {
                if(is_string($value)){
                    $json = json_decode($value);
                    if(empty($json)){
                        $json = json_decode("[$value]");
                    }
                }else{
                    $json = $value;
                }
                if (is_array($json)) {
                    foreach ($json as $key => $value) {
                        $json[$key] = intval($value);
                    }
                    $scanThis[$key] = json_encode($json);
                } else {
                    $scanThis[$key] = intval($value);
                }
            }
        }
    }

    foreach ($securityFilterInt as $value) {
        if (!empty($scanThis[$value])) {
            if (strtolower($scanThis[$value]) === "true") {
                $scanThis[$value] = 1;
            } else {
                $scanThis[$value] = intval($scanThis[$value]);
            }
        }
    }

    foreach ($securityFilter as $value) {
        if (!empty($scanThis[$value])) {
            $scanThis[$value] = str_replace(array('\\', "--", "'", '"', "&quot;", "&#039;", "%23", "%5c", "#"), array('', '', '', '', '', '', '', '', ''), xss_esc($scanThis[$value]));
        }
    }

    if (!empty($scanThis['sort']) && is_array($scanThis['sort'])) {
        foreach ($scanThis['sort'] as $key => $value) {
            $scanThis['sort'][xss_esc($key)] = strcasecmp($value, "ASC") === 0 ? "ASC" : "DESC";
        }
    }
}
