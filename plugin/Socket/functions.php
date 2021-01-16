<?php

function getEncryptedInfo() {
    $msgObj = new stdClass();
    $msgObj->users_id = User::getId();
    $msgObj->token = getToken(43200); // valid for 12 hours
    $msgObj->time = time();

    return encryptString(json_encode($msgObj));
}

function getDecryptedInfo($string) {
    $decriptedString = decryptString($string);
    $json = json_decode($decriptedString);
    if (!empty($json) && !empty($json->token)) {
        if (isTokenValid($json->token)) {
            return $json;
        } else {
            _error_log("socket:getDecryptedInfo: token is invalid ");
        }
    } else {
        _error_log("socket:getDecryptedInfo: json->token is empty ({$decriptedString})");
    }
    return false;
}
