<?php
$global_timeLimit = 300;

ini_set("memory_limit", -1);
ini_set('default_socket_timeout', $global_timeLimit);
set_time_limit($global_timeLimit);
ini_set('max_execution_time', $global_timeLimit);
ini_set("memory_limit", "-1");

function _decryptString($string)
{
    global $global;
    $url = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=decryptString";

    $url = str_replace('http://192.168.0.2/', 'https://vlu.me/', $url);

    _error_log("Decrypting string using URL: $url");
    //return json_encode(array('_decryptString'=>$url));
    $data = ['string' => $string];
    $content = postVariables($url, $data, false);
    $json = json_decode($content);

    if (!empty($json) && empty($json->error)) {
        $json2 = json_decode($json->message);
        if ($_SERVER["SERVER_NAME"] == 'vlu.me') {
            //_error_log("String decrypted successfully vlu.me $content");
            return $json2;
        }
        if ($json2->time > strtotime('30 seconds ago')) {
            //_error_log("String decrypted successfully");
            return $json2;
        }
    }
    _error_log("Failed to decrypt string or invalid time $content");
    //return $json2;
    return false;
}

// Function to safely get inputs from either command line or request
function getInput($key, $default = '')
{
    global $argv;

    // Check if running from command line or HTTP request
    if (php_sapi_name() === 'cli') {
        foreach ($argv as $arg) {
            if (strpos($arg, "{$key}=") === 0) {
                return substr($arg, strlen("{$key}="));
            }
        }
    } else {
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }

    return $default;
}

// Validate and sanitize the ffmpegCommand
function sanitizeFFmpegCommand($command)
{
    $allowedPrefixes = ['ffmpeg', '/usr/bin/ffmpeg', '/bin/ffmpeg'];
    _error_log("Sanitizing FFMPEG command: $command");

    // Remove dangerous characters
    $command = str_replace('&&', '', $command);
    $command = str_replace('rtmp://vlu.me/', 'rtmp://live/', $command);
    //$command = str_replace('rtmp://live/', 'rtmp://vlu.me/', $command);
    //$command = str_replace('https://live:8443/', 'https://vlu.me:8443/', $command);
    $command = preg_replace('/\s*&?>.*(?:2>&1)?/', '', $command);
    $command = preg_replace('/[;|`<>]/', '', $command);

    // Ensure it starts with an allowed prefix
    foreach ($allowedPrefixes as $prefix) {
        if (strpos(trim($command), $prefix) === 0) {
            _error_log("Command sanitized successfully");
            return $command;
        }
    }

    _error_log("Sanitization failed: Command does not start with an allowed prefix");
    return '';
}
