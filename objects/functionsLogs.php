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
            $backtrace = ' backtrace=' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20));
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            if (isBot()) {
                $ua .= " BOT ";
                // Strip newlines to prevent log injection (CWE-117); security.php does not cover $_SERVER
                $ua .= " USER_AGENT=" . preg_replace('/[\r\n]+/', ' ', $_SERVER['HTTP_USER_AGENT']);
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

function avideoRequestId()
{
    static $requestId;
    if ($requestId === null) {
        try {
            $requestId = bin2hex(random_bytes(12));
        } catch (\Throwable $exception) {
            // Correlation is diagnostic only; random-source failure must not break a request.
            $requestId = substr(hash('sha256', uniqid('', true) . getmypid()), 0, 24);
        }
    }
    return $requestId;
}

/** Keep the error text readable without recording arguments, URLs or quoted values. */
function avideoDiagnosticMessage($message)
{
    global $global;
    $message = explode("\nStack trace:", (string) $message, 2)[0];
    $root = rtrim(str_replace('\\', '/', $global['systemRootPath'] ?? ''), '/');
    if ($root !== '') {
        $message = str_replace([$root . '/', str_replace('/', '\\', $root) . '\\'], '', $message);
    }
    // "Uncaught X: text in file:line" repeats the location that is logged separately.
    if (strpos($message, 'Uncaught ') === 0) {
        $message = preg_replace('/ in (?:(?! in ).)+:\d+$/', '', $message);
    }
    $message = preg_replace('/\b(?:authorization|cookie)\s*[:=][^\r\n]*/i', '[credential redacted]', $message);
    $message = preg_replace('/\bBearer\s+\S+/i', '[credential redacted]', $message);
    $message = preg_replace('/\b(?:https?|rtmps?):\/\/[^\s<>]+/i', '[URL redacted]', $message);
    $message = preg_replace('/([\x22\x27])(?:\\\\.|(?!\1).)*?\1/s', '[value redacted]', $message);
    $message = preg_replace('/\b(?:password|passwd|pass|token|secret|api[_-]?key|authorization|cookie)\b\s*[:=]\s*\S+/i', '[credential redacted]', $message);
    return substr(preg_replace('/[\x00-\x1f\x7f]+/', ' ', $message), 0, 1000);
}

function avideoDiagnosticLocation($file)
{
    global $global;
    $file = str_replace('\\', '/', (string) $file);
    $root = str_replace('\\', '/', $global['systemRootPath'] ?? '');
    if ($root !== '' && strpos($file, rtrim($root, '/') . '/') === 0) {
        $file = substr($file, strlen(rtrim($root, '/')) + 1);
    }
    return substr($file, 0, 240);
}

function avideoDiagnosticFrame($file, $line, $function)
{
    $location = $file === '' ? '[internal function]' : avideoDiagnosticLocation($file) . '(' . intval($line) . ')';
    return $location . ': ' . substr($function, 0, 160) . '()';
}

/** Frames of an uncaught exception, read from PHP's message without the call arguments. */
function avideoDiagnosticTrace($message)
{
    $parts = explode("\nStack trace:\n", (string) $message, 2);
    $frames = [];
    foreach (explode("\n", $parts[1] ?? '') as $line) {
        if (count($frames) >= 12) {
            break;
        }
        if (preg_match('/^#\d+ (.*?)(?:\((\d+)\))?: ([^\s(]+)\(/', trim($line), $match)) {
            $file = $match[1] === '[internal function]' ? '' : $match[1];
            $frames[] = avideoDiagnosticFrame($file, $match[2], $match[3]);
        }
    }
    return $frames;
}

/** The last lines this request wrote to the log: usually the reason for a 500. */
function avideoRecentLogMessages($message = null)
{
    static $messages = [];
    if ($message !== null && strpos($message, 'RequestDiagnostic ') !== 0) {
        $messages[] = substr(preg_replace('/[\x00-\x1f\x7f]+/', ' ', $message), 0, 300);
        $messages = array_slice($messages, -3);
    }
    return $messages;
}

/** Log an exception at its catch site; stack arguments and request data stay private. */
function avideoLogException($exception)
{
    if (!($exception instanceof \Throwable)) {
        return;
    }
    $frames = [];
    foreach (array_slice($exception->getTrace(), 0, 12) as $frame) {
        $function = ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '');
        $frames[] = avideoDiagnosticFrame($frame['file'] ?? '', $frame['line'] ?? 0, $function);
    }
    avideoWriteRequestDiagnostic('exception', [
        'class' => get_class($exception),
        'code' => intval($exception->getCode()),
        'message' => avideoDiagnosticMessage($exception->getMessage()),
        'file' => avideoDiagnosticLocation($exception->getFile()),
        'line' => $exception->getLine(),
        'trace' => $frames,
    ]);
}

function avideoDiagnosticSummary($record)
{
    $details = $record['details'];
    $where = isset($details['file']) ? ' at ' . $details['file'] . ':' . $details['line'] : '';
    if ($record['event'] === 'php_fatal') {
        $summary = 'PHP ' . $details['type_name'] . ': ' . $details['message'] . $where;
    } elseif ($record['event'] === 'exception') {
        $summary = 'Caught ' . $details['class'] . ': ' . $details['message'] . $where;
    } else {
        $summary = 'HTTP ' . $record['http_status'] . ' returned by ' . $record['script'];
    }
    return $summary . ' (' . trim($record['method'] . ' ' . $record['script']) . ', request ' . $record['request_id'] . ')';
}

function avideoWriteRequestDiagnostic($event, $details = [], $rateLimitKey = '')
{
    global $global;
    if (!isset($global) || !is_array($global)) {
        $global = [];
    }
    $record = [
        'event' => $event,
        'request_id' => avideoRequestId(),
        'at_utc' => gmdate('c'),
        'http_status' => http_response_code() ?: null,
        // SCRIPT_FILENAME identifies the PHP entry point without pretty-URL/query secrets.
        'script' => avideoDiagnosticLocation($_SERVER['SCRIPT_FILENAME'] ?? ''),
        'method' => preg_replace('/[^A-Z]/', '', substr($_SERVER['REQUEST_METHOD'] ?? '', 0, 12)),
        'pid' => getmypid(),
        'details' => $details,
    ];
    // printLogs is useful for CLI debugging, but diagnostics must never enter a response.
    $hadPrintLogs = array_key_exists('printLogs', $global);
    $printLogs = $global['printLogs'] ?? null;
    $global['printLogs'] = false;
    try {
        // Readable summary first for people scanning the log, JSON after it for tools.
        $message = 'RequestDiagnostic ' . avideoDiagnosticSummary($record) . ' ' . json_encode($record, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        // rateLimitedLog() needs functions.php helpers, absent when a request fails during boot.
        if ($rateLimitKey !== '' && function_exists('make_path') && function_exists('_file_put_contents')) {
            rateLimitedLog('RequestDiagnostic_' . md5($rateLimitKey), $message, 300, AVideoLog::$ERROR);
        } else {
            _error_log($message, AVideoLog::$ERROR, false);
        }
    } finally {
        if ($hadPrintLogs) {
            $global['printLogs'] = $printLogs;
        } else {
            unset($global['printLogs']);
        }
    }
}

function avideoLogHttpError($error = null)
{
    static $logged = false;
    if ($logged) {
        return;
    }
    $fatal = is_array($error) && in_array($error['type'] ?? 0, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true);
    $status = http_response_code();
    if (!$fatal && ($status < 500 || $status > 599)) {
        return;
    }
    $logged = true;
    $details = [];
    if ($fatal) {
        $typeNames = [E_ERROR => 'Fatal error', E_PARSE => 'Parse error', E_CORE_ERROR => 'Core error', E_COMPILE_ERROR => 'Compile error', E_USER_ERROR => 'Fatal error (trigger_error)', E_RECOVERABLE_ERROR => 'Recoverable fatal error'];
        $details = [
            'type' => $error['type'],
            'type_name' => $typeNames[$error['type']],
            'message' => avideoDiagnosticMessage($error['message'] ?? ''),
            'file' => avideoDiagnosticLocation($error['file'] ?? ''),
            'line' => intval($error['line'] ?? 0),
        ];
        $trace = avideoDiagnosticTrace($error['message'] ?? '');
        if (!empty($trace)) {
            $details['trace'] = $trace;
        }
    }
    $recent = avideoRecentLogMessages();
    if (!empty($recent)) {
        $details['recent_log'] = $recent;
    }
    $event = $fatal ? 'php_fatal' : 'http_error';
    // Polling endpoints, ad proxies and a broken page repeat the same failure on every request:
    // keep one record per entry point/status (or fatal location) every 5 minutes.
    $rateLimitKey = implode('|', [$event, $fatal ? '' : $status, $_SERVER['SCRIPT_FILENAME'] ?? '', $details['type'] ?? '', $details['file'] ?? '', $details['line'] ?? '']);
    avideoWriteRequestDiagnostic($event, $details, $rateLimitKey);
}

function avideoRegisterRequestDiagnostics()
{
    static $registered = false;
    if ($registered || PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
        return;
    }
    $registered = true;
    if (!headers_sent()) {
        header('X-AVideo-Request-ID: ' . avideoRequestId());
    }
    // Leave memory available to log a PHP memory-limit failure at shutdown.
    $reserve = str_repeat('x', 32768);
    register_shutdown_function(function () use (&$reserve) {
        $reserve = null;
        avideoLogHttpError(error_get_last());
    });
}

avideoRegisterRequestDiagnostics();

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

function _error_log_stringify_message($message)
{
    if (!is_string($message)) {
        $originalMessage = $message;
        $message = json_encode($message);
        if (!is_string($message)) {
            $message = print_r($originalMessage, true);
        }
    }
    return $message;
}

function _error_log_build_message($message, $type = 0)
{
    $message = _error_log_stringify_message($message);
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
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
    return $prefix . $message . " SCRIPT_NAME: {$scriptName}";
}

function _error_log_truncate_message($message)
{
    global $global;

    $message = _error_log_stringify_message($message);

    $maxLength = isset($global['maxLogLineLength']) ? intval($global['maxLogLineLength']) : 8192;
    if ($maxLength <= 0 || strlen($message) <= $maxLength) {
        return $message;
    }

    $suffix = '... [log truncated, original length=' . strlen($message) . ' bytes]';
    $maxMessageLength = $maxLength - strlen($suffix);
    if ($maxMessageLength < 1) {
        return substr($message, 0, $maxLength);
    }

    return substr($message, 0, $maxMessageLength) . $suffix;
}

function _error_log($message, $type = 0, $doNotRepeat = null)
{
    global $global;
    $message = _error_log_stringify_message($message);
    if (!empty($global['printLogs'])) {
        echo _error_log_truncate_message($message) . PHP_EOL;
        return false;
    }

    if ($doNotRepeat === null) {
        // HLS requests can repeat the same error many times per playback session.
        $doNotRepeat = preg_match("/hls.php$/", $_SERVER['SCRIPT_NAME'] ?? '');
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
    if ($type != AVideoLog::$PERFORMANCE) {
        avideoRecentLogMessages($message);
    }
    $str = _error_log_build_message($message, $type);
    $logMessage = _error_log_truncate_message($str);
    if ($doNotRepeat) {
        return rateLimitedLog(
            '_error_log_' . md5($str),
            $logMessage
        );
    }
    error_log($logMessage);
}

function rateLimitedLog($key, $message, $ttl = 300, $type = null)
{
    global $global;

    $message = _error_log_stringify_message($message);

    if (!empty($global['printLogs'])) {
        if ($type === null) {
            echo _error_log_truncate_message($message) . PHP_EOL;
        } else {
            echo _error_log_truncate_message(_error_log_build_message($message, $type)) . PHP_EOL;
        }
        return false;
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

    if (class_exists('ObjectYPT')) {
        $tmpDir = ObjectYPT::getTmpCacheDir() . 'rateLimitedLogs' . DIRECTORY_SEPARATOR;
    } else {
        $tmpDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'YPTObjectCache' . DIRECTORY_SEPARATOR . 'rateLimitedLogs' . DIRECTORY_SEPARATOR;
    }
    make_path($tmpDir);
    $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $key);
    $rateFile = $tmpDir . 'rate_limited_log_' . $safeKey . '.cache';
    $now = time();

    if (is_file($rateFile)) {
        $lastLogged = intval(@file_get_contents($rateFile));
        if (!empty($lastLogged) && ($lastLogged + $ttl) > $now) {
            return false;
        }
    }

    @_file_put_contents($rateFile, $now);

    if ($type === null) {
        error_log(_error_log_truncate_message($message));
    } else {
        error_log(_error_log_truncate_message(_error_log_build_message($message, $type)));
    }

    return true;
}

function getAVideoLogPermissionCheckFile($logfile)
{
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'avideo_log_permission_check_' . md5($logfile) . '.cache';
}

function getAVideoWebServerUser()
{
    global $global;

    if (!empty($global['webserverUser'])) {
        return $global['webserverUser'];
    }

    if (!empty($_SERVER['APACHE_RUN_USER'])) {
        return $_SERVER['APACHE_RUN_USER'];
    }

    if (function_exists('posix_getpwnam')) {
        foreach (['www-data', 'apache', 'nginx'] as $user) {
            if (posix_getpwnam($user) !== false) {
                return $user;
            }
        }
    }

    return 'www-data';
}

function canCurrentProcessChown()
{
    return isCommandLineInterface() && function_exists('posix_geteuid') && posix_geteuid() === 0;
}

function ensureAVideoLogWritable($force = false, $ttl = 300)
{
    global $global;

    if (empty($global['logfile'])) {
        return false;
    }

    $logfile = $global['logfile'];
    if (strpos($logfile, '/dev/') === 0 || strpos($logfile, 'php://') === 0) {
        return true;
    }

    $checkFile = getAVideoLogPermissionCheckFile($logfile);
    if (
        empty($force) &&
        !empty($ttl) &&
        file_exists($checkFile) &&
        filemtime($checkFile) >= (time() - intval($ttl))
    ) {
        $lastCheck = json_decode(@file_get_contents($checkFile));
        if (!empty($lastCheck) && !empty($lastCheck->result)) {
            return true;
        }
    }

    $result = true;
    $dir = dirname($logfile);
    if (!is_dir($dir) && function_exists('make_path')) {
        $result = make_path($dir) && $result;
    }

    if (!file_exists($logfile)) {
        $created = @file_put_contents($logfile, '');
        if ($created === false) {
            _error_log("ensureAVideoLogWritable: failed to create log file {$logfile}", AVideoLog::$WARNING);
            $result = false;
        }
    }

    if (file_exists($logfile)) {
        $webServerUser = getAVideoWebServerUser();
        if (canCurrentProcessChown() && function_exists('posix_getpwnam')) {
            $userInfo = posix_getpwnam($webServerUser);
            if (!empty($userInfo) && fileowner($logfile) !== intval($userInfo['uid'])) {
                if (!@chown($logfile, $webServerUser)) {
                    _error_log("ensureAVideoLogWritable: failed to chown {$logfile} to {$webServerUser}", AVideoLog::$WARNING);
                    $result = false;
                }
            }
        }

        if (!@chmod($logfile, 0664)) {
            _error_log("ensureAVideoLogWritable: failed to chmod 0664 {$logfile}", AVideoLog::$WARNING);
            $result = false;
        }

        if (!is_writable($logfile)) {
            _error_log("ensureAVideoLogWritable: log file is not writable by current process {$logfile}", AVideoLog::$WARNING);
            $result = false;
        }
    }

    @file_put_contents($checkFile, json_encode([
        'time' => time(),
        'result' => $result,
        'logfile' => $logfile,
    ]));

    return $result;
}

function isSchedulerRun()
{
    return preg_match('/Scheduler\/run\.php$/', $_SERVER['SCRIPT_NAME']);
}

/**
 * Rotates avideo.log daily (similar to Apache logrotate):
 * - Renames avideo.log to avideo.YYYY-MM-DD.log (yesterday's date)
 * - Creates a fresh empty avideo.log
 * - Deletes rotated logs older than $daysToKeep days
 *
 * Set $global['logRotationDays'] in configuration.php to change retention (default: 30).
 */
function rotateAVideoLog()
{
    global $global;

    // Skip rotation when logging to stdout/stderr (Docker) or log is not a regular file
    if (empty($global['logfile']) || !file_exists($global['logfile']) || !is_file($global['logfile'])) {
        return;
    }

    $logfile = $global['logfile'];
    $daysToKeep = isset($global['logRotationDays']) ? (int) $global['logRotationDays'] : 30;
    if ($daysToKeep < 1) {
        $daysToKeep = 1;
    }

    // Rotate: rename current log to avideo.YYYY-MM-DD.log (yesterday)
    // Keeps the .log extension so web server rules blocking *.log also protect archived files
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $logDir = dirname($logfile);
    $logBasename = basename($logfile, '.log'); // e.g. "avideo"
    $rotatedFile = $logDir . DIRECTORY_SEPARATOR . $logBasename . '.' . $yesterday . '.log';

    // Only rotate if there is content and not already rotated today
    if (filesize($logfile) > 0 && !file_exists($rotatedFile)) {
        if (rename($logfile, $rotatedFile)) {
            // Create a fresh empty log
            file_put_contents($logfile, '');
            ini_set('error_log', $logfile);
            ensureAVideoLogWritable(true);
            _error_log("rotateAVideoLog: rotated to {$rotatedFile}, keeping {$daysToKeep} days");
        } else {
            _error_log("rotateAVideoLog: failed to rotate {$logfile}", AVideoLog::$ERROR);
            return;
        }
    }

    // Delete old rotated logs beyond the retention period
    // Pattern: avideo.YYYY-MM-DD.log
    $cutoffTime = strtotime("-{$daysToKeep} days");

    foreach (glob($logDir . DIRECTORY_SEPARATOR . $logBasename . '.????-??-??.log') ?: [] as $oldFile) {
        if (!is_file($oldFile)) {
            continue;
        }
        // Match files like avideo.YYYY-MM-DD.log
        if (!preg_match('/\.(\d{4}-\d{2}-\d{2})\.log$/', $oldFile, $m)) {
            continue;
        }
        $fileTime = strtotime($m[1]);
        if ($fileTime !== false && $fileTime < $cutoffTime) {
            if (unlink($oldFile)) {
                _error_log("rotateAVideoLog: deleted old log {$oldFile}");
            } else {
                _error_log("rotateAVideoLog: failed to delete {$oldFile}", AVideoLog::$WARNING);
            }
        }
    }
}


function _dieAndLogObject($obj, $prefix = "")
{
    $objString = json_encode($obj);
    _error_log($prefix . $objString);
    die($objString);
}
