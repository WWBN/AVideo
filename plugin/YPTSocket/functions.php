<?php

function getEncryptedInfo($timeOut = 0, $send_to_uri_pattern = "") {
    if (empty($timeOut)) {
        $timeOut = 43200; // valid for 12 hours
    }
    $msgObj = new stdClass();
    $msgObj->from_users_id = User::getId();
    $msgObj->isAdmin = User::isAdmin();
    $msgObj->user_name = User::getNameIdentification();
    $msgObj->browser = get_browser_name();
    $msgObj->yptDeviceId = getDeviceID(false);
    $msgObj->token = getToken($timeOut);
    $msgObj->time = time();
    $msgObj->ip = getRealIpAddr();
    $msgObj->send_to_uri_pattern = $send_to_uri_pattern;
    //var_dump($msgObj);exit;
    if (!empty($_REQUEST['webSocketSelfURI'])) {
        $msgObj->selfURI = $_REQUEST['webSocketSelfURI'];
    } else {
        $msgObj->selfURI = getSelfURI();
    }
    if (empty($msgObj->videos_id)) {
        if (!empty($_REQUEST['webSocketVideos_id'])) {
            $msgObj->videos_id = $_REQUEST['webSocketVideos_id'];
        } else {
            $msgObj->videos_id = getVideos_id();
        }
    }
    if (empty($msgObj->live_key)) {
        if (!empty($_REQUEST['webSocketLiveKey'])) {
            $msgObj->live_key = _json_decode($_REQUEST['webSocketLiveKey']);
        } else {
            $msgObj->live_key = isLive();
        }
    }

    if (AVideoPlugin::isEnabledByName('User_location')) {
        $msgObj->location = User_Location::getThisUserLocation();
    } else {
        $msgObj->location = false;
    }
    return encryptString(json_encode($msgObj));
}

function getDecryptedInfo($string) {
    $decriptedString = decryptString($string);
    $json = _json_decode($decriptedString);
    if (!empty($json) && !empty($json->token)) {
        if (verifyTokenSocket($json->token)) {
            return $json;
        } else {
            _error_log("socket:getDecryptedInfo: token is invalid ");
        }
    } else {
        _error_log("socket:getDecryptedInfo: json->token is empty ({$decriptedString})");
    }
    return false;
}

function verifyTokenSocket($token) {
    global $global;
    $obj = _json_decode(decryptString($token));
    if (empty($obj)) {
        _error_log("verifyToken invalid token");
        return false;
    }
    if ($obj->salt !== $global['salt']) {
        _error_log("verifyToken salt fail");
        return false;
    }
    $time = time();
    if (!($time >= $obj->time && $time <= $obj->timeout)) {
        _error_log("verifyToken token timout time = $time; obj->time = $obj->time;  obj->timeout = $obj->timeout");
        //return false;
    }
    return true;
}

class SocketMessageType {

    const NEW_CONNECTION = "NEW_CONNECTION";
    const NEW_DISCONNECTION = "NEW_DISCONNECTION";
    const DEFAULT_MESSAGE = "DEFAULT_MESSAGE";
    const ON_VIDEO_MSG = "ON_VIDEO_MSG";
    const ON_LIVE_MSG = "ON_LIVE_MSG";
    const TESTING = "TESTING";

}

function getTotalViewsLive_key($live_key) {
    if (empty($live_key)) {
        return false;
    }
    $live_key = object_to_array($live_key);
    _mysql_connect();
    $liveUsersEnabled = \AVideoPlugin::isEnabledByName("LiveUsers");
    if ($liveUsersEnabled) {
        $liveUsers = new \LiveOnlineUsers(0);
        $total = $liveUsers->getTotalUsersFromTransmitionKey($live_key['key'], $live_key['live_servers_id']);
    } else {
        $total = null;
    }

    _mysql_close();

    return $total;
}

function killProcessOnPort() {
    $obj = \AVideoPlugin::getDataObject("YPTSocket");
    $port = intval($obj->port);
    if (!empty($port)) {
        echo 'Searching for port: ' . $port . PHP_EOL;
        $command = 'netstat -ano | findstr ' . $port;
        exec($command, $output, $retval);
        $pid = getPIDUsingPort($port);
        if (!empty($pid)) {
            echo 'Killing, PID ' . $pid . PHP_EOL;
            killProcess($pid);
        } else {
            echo 'No Need to kill, port NOT found' . PHP_EOL;
        }
    }
}
