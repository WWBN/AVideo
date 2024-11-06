<?php

function TimeLogStart($name)
{
    global $global;
    if (!empty($global['noDebug'])) {
        return false;
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    if (empty($global['start']) || !is_array($global['start'])) {
        $global['start'] = [];
    }
    $global['start'][$name] = $time;
    return $name;
}

function TimeLogEnd($name, $line, $TimeLogLimit = 0.7)
{
    global $global;
    if (!empty($global['noDebug']) || empty($global['start'][$name])) {
        return false;
    }
    if (!empty($global['TimeLogLimit'])) {
        $TimeLogLimit = $global['TimeLogLimit'];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $global['start'][$name]), 4);
    $type = AVideoLog::$PERFORMANCE;
    $backtrace = '';
    $ua = ' IP=' . getRealIpAddr();

    if (empty($global['noDebugSlowProcess']) && $total_time > $TimeLogLimit) {
        if ($total_time > 1) {
            $type = AVideoLog::$WARNING;
        }
        if ($total_time > 2) {
            $type = AVideoLog::$ERROR;
            $backtrace = ' backtrace=' . json_encode(debug_backtrace());
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            if (isBot()) {
                $ua .= " BOT ";
                $ua .= " USER_AGENT={$_SERVER['HTTP_USER_AGENT']}";
            }
        } else {
            $ua .= " USER_AGENT=Undefined server=" . json_encode($_SERVER);
        }

        _error_log("Time: " . str_pad(number_format($total_time, 3) . "s", 8) . " | Limit: {$TimeLogLimit}s | Location: {$_SERVER["SCRIPT_FILENAME"]} Line {$line} [{$name}]{$ua}{$backtrace}", $type);
    }
    TimeLogStart($name);
}


class AVideoLog
{

    public static $DEBUG = 0;
    public static $WARNING = 1;
    public static $ERROR = 2;
    public static $SECURITY = 3;
    public static $SOCKET = 4;
    public static $PERFORMANCE = 5;
    public static $MONITORE = 6;
}

function _error_log_debug($message, $show_args = false)
{
    $array = debug_backtrace();
    $message .= PHP_EOL;
    foreach ($array as $value) {
        $message .= "function: {$value['function']} Line: {{$value['line']}} File: {{$value['file']}}" . PHP_EOL;
        if ($show_args) {
            $message .= print_r($value['args'], true) . PHP_EOL;
        }
    }
    _error_log(PHP_EOL . '***' . PHP_EOL . $message . '***');
}

function _error_log($message, $type = 0, $doNotRepeat = false)
{
    global $global;
    if (!is_string($message)) {
        $message = json_encode($message);
    }
    if (!empty($global['printLogs'])) {
        echo $message . PHP_EOL;
        return false;
    }
    if (empty($doNotRepeat)) {
        // do not log it too many times when you are using HLS format, other wise it will fill the log file with the same error
        $doNotRepeat = preg_match("/hls.php$/", $_SERVER['SCRIPT_NAME']);
    }
    if ($doNotRepeat) {
        return false;
    }
    if (isCommandLineInterface() && empty($global['doNotPrintLogs'])) {
        //echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    }
    if (empty($global['noDebug'])) {
        $global['noDebug'] = array();
    }
    if (!empty($global['noDebug']) && ($type == AVideoLog::$DEBUG || $type == AVideoLog::$PERFORMANCE)) {
        if (is_array($global['noDebug'])) {
            if (in_array($type, $global['noDebug'])) {
                return false;
            }
        } else if (($type == AVideoLog::$DEBUG || $type == AVideoLog::$PERFORMANCE)) {
            return false;
        }
    }
    $prefix = "AVideoLog::";
    switch ($type) {
        case AVideoLog::$DEBUG:
            $prefix .= "DEBUG:      ";
            break;
        case AVideoLog::$WARNING:
            $prefix .= "WARNING:    ";
            break;
        case AVideoLog::$ERROR:
            $prefix .= "ERROR:      ";
            break;
        case AVideoLog::$SECURITY:
            $prefix .= "SECURITY:   ";
            break;
        case AVideoLog::$SOCKET:
            $prefix .= "SOCKET:      ";
            break;
        case AVideoLog::$PERFORMANCE:
            $prefix .= "PERFORMANCE: ";
            break;
        case AVideoLog::$MONITORE:
            $prefix .= "MONITORE:    ";
            break;
    }
    $str = $prefix . $message . " SCRIPT_NAME: {$_SERVER['SCRIPT_NAME']}";
    error_log($str);
}

function isSchedulerRun()
{
    return preg_match('/Scheduler\/run\.php$/', $_SERVER['SCRIPT_NAME']);
}


function _dieAndLogObject($obj, $prefix = "")
{
    $objString = json_encode($obj);
    _error_log($prefix . $objString);
    die($objString);
}
