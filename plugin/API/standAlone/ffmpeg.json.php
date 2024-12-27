<?php

/**
 * FFMPEG Command Execution Script with API Secret Validation
 * -----------------------------------------------------------
 * This script is used to safely execute FFMPEG commands on the server via HTTP request or command line.
 * It includes security measures to validate the provided `APISecret` against the AVideo platform API.
 *
 * Usage Instructions:
 * -------------------
 * 1. **API Secret Validation:**
 *    - All requests (HTTP or command line) must include a valid `APISecret` parameter.
 *    - The `APISecret` is verified by making a request to the AVideo platform's API.
 *    - If the `APISecret` is invalid, the script will terminate with an error message.
 *
 * 2. **Via HTTP Request:**
 *    Send a GET or POST request to the script with the following parameters:
 *    - `APISecret` (required): The API secret key obtained from the API plugin in AVideo.
 *    - `ffmpegCommand` (required): The full FFMPEG command to execute. The command must start with `ffmpeg`.
 *    - `keyword` (optional): A unique keyword associated with the process, allowing you to kill a previously started process with the same keyword.
 *
 *    **Example URL:**
 *    ```
 *    http://yourserver.com/path/to/script.php?APISecret=yourAPISecret&ffmpegCommand=ffmpeg+-i+input.mp4+-vcodec+libx264+-preset+fast+output.mp4&keyword=uniqueProcessKey
 *    ```
 *
 * 3. **Via Command Line:**
 *    Run the script from the terminal with the following parameters:
 *    - `APISecret` (required): The API secret key obtained from the API plugin in AVideo.
 *    - `ffmpegCommand` (required): The full FFMPEG command to execute. The command must start with `ffmpeg`.
 *    - `keyword` (optional): A unique keyword associated with the process.
 *
 *    **Example Command:**
 *    ```
 *    php script.php APISecret="yourAPISecret" ffmpegCommand="ffmpeg -i input.mp4 -vcodec libx264 -preset fast output.mp4" keyword="uniqueProcessKey"
 *    ```
 *
 * Security Features:
 * -------------------
 * - **API Secret Validation:** Ensures only authorized users can execute commands.
 *   The API secret is verified by sending a request to the AVideo platform API endpoint:
 *   ```
 *   plugin/API/get.json.php?APIName=isAPISecretValid&APISecret=yourAPISecret
 *   ```
 *   If the validation fails, the script terminates immediately with an error.
 *
 * - **Command Validation:**
 *   - Ensures the `ffmpegCommand` starts with `ffmpeg` or an allowed path (`/usr/bin/ffmpeg`, `/bin/ffmpeg`).
 *   - Sanitizes the command by removing potentially dangerous characters (`;`, `&`, `|`, `` ` ``, `<`, `>`).
 * 
 * - **Kill Process by Keyword:** Allows stopping a previously started process by providing a `keyword`.
 *
 * Output:
 * -------
 * The script returns a JSON response with the status of the command execution:
 * - **Success Response:**
 *   ```json
 *   {
 *       "error": false,
 *       "msg": "Command executed",
 *       "command": "ffmpeg -i input.mp4 -vcodec libx264 -preset fast output.mp4",
 *       "pid": 12345
 *   }
 *   ```
 *
 * - **Error Response:**
 *   ```json
 *   {
 *       "error": true,
 *       "msg": "Invalid or empty ffmpeg command"
 *   }
 *   ```
 *
 * Standalone Configuration File:
 * ------------------------------
 * If the standalone configuration file is missing, the script will prompt the user to create it manually:
 *
 * **Required File:**
 * `<installation_root>/videos/standalone.configuration.php`
 *
 * **Sample Content:**
 * ```php
 * <?php
 * $global['webSiteRootURL'] = 'https://yourSite.com/';
 * ?>
 * ```
 *
 * Replace `https://yourSite.com/` with your actual website URL.
 */
$global_timeLimit = 300;

ini_set("memory_limit", -1);
ini_set('default_socket_timeout', $global_timeLimit);
set_time_limit($global_timeLimit);
ini_set('max_execution_time', $global_timeLimit);
ini_set("memory_limit", "-1");

header('Content-Type: application/json');

require_once __DIR__ . "/../../../objects/functionsStandAlone.php";

_error_log("Script initiated: FFMPEG command execution script started");

if (empty($streamerURL)) {
    _error_log("Error: streamerURL is not defined");
    echo json_encode(['error' => true, 'msg' => 'streamerURL not defined']);
    exit;
}

function _decryptString($string)
{
    global $global;
    $url = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=decryptString&string={$string}";
    _error_log("Decrypting string using URL: $url");

    $content = file_get_contents($url);
    $json = json_decode($content);

    if (!empty($json) && empty($json->error)) {
        $json2 = json_decode($json->message);
        if ($json2->time > strtotime('30 seconds ago')) {
            _error_log("String decrypted successfully");
            return $json2;
        }
    }
    _error_log("Failed to decrypt string or invalid time");
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

function addKeywordToFFmpegCommand(string $command, string $keyword): string {
    // Escape the keyword to avoid shell injection
    $escapedKeyword = escapeshellarg($keyword);

    // Break the command into parts to safely insert the metadata
    $commandParts = explode(' ', $command);

    // Find the index of the output URL (typically the last argument in FFmpeg commands)
    $outputUrlIndex = array_key_last($commandParts);
    if (preg_match('/^(rtmp|http|https):\/\//', $commandParts[$outputUrlIndex])) {
        // Insert metadata before the output URL
        array_splice($commandParts, $outputUrlIndex, 0, ["-metadata", "keyword=$escapedKeyword"]);
    } else {
        // If no URL is found, append metadata at the end
        $commandParts[] = "-metadata";
        $commandParts[] = "keyword=$escapedKeyword";
    }

    // Reconstruct the command
    return implode(' ', $commandParts);
}

_error_log("Fetching inputs...");
$codeToExecEncrypted = getInput('codeToExecEncrypted', '');
$codeToExec = _decryptString($codeToExecEncrypted);

if (empty($codeToExec)) {
    _error_log("Invalid or missing codeToExecEncrypted");
    die(json_encode(array('error' => true, 'msg' => 'Invalid or missing code')));
}

$ffmpegCommand = !empty($codeToExec->ffmpegCommand) ? sanitizeFFmpegCommand($codeToExec->ffmpegCommand) : '';

if (empty($codeToExec->keyword)) {
    _error_log("keyword: is empty");
    $keyword = date('Ymdhmi');
} else {
    _error_log("keyword: found: {$codeToExec->keyword}");
    $keyword = preg_replace('/[^a-zA-Z0-9_-]/', '', $codeToExec->keyword);
}

_error_log("Code to Execute: " . json_encode($codeToExec));
_error_log("Sanitized FFMPEG Command: $ffmpegCommand");
_error_log("Keyword: $keyword");


$tempDir = "{$global['systemRootPath']}videos/ffmpegLogs/";
make_path($tempDir);

$tempDir = rtrim($tempDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$logFile = "{$tempDir}ffmpeg_{$keyword}.log";
//_error_log("Log file set to: $logFile");

if (!empty($codeToExec->test)) {
    $microtime = microtime(true);
    _error_log("Test mode triggered");
    echo json_encode([
        'error' => false,
        'msg' => sprintf('Remote FFmpeg responded successfully in %.4f seconds.', $microtime - $codeToExec->microtime),
        'request_start_time' => $codeToExec->microtime,
        'response_end_time' => $microtime,
        'elapsed_time' => number_format($microtime - $codeToExec->microtime, 4),
    ]);
    exit;
} else if (!empty($codeToExec->log)) {
    _error_log("Log retrieval mode triggered");
    $time = time();
    $modified = @filemtime($logFile);
    $secondsAgo = $time - $modified;
    $isActive = $secondsAgo < 10;
    echo json_encode([
        'error' => !file_exists($logFile),
        'msg' => '',
        'logFile' => $logFile,
        'time' => $time,
        'modified' => $modified,
        'secondsAgo' => $secondsAgo,
        'isActive' => $isActive,
    ]);
    exit;
} else if (!empty($codeToExec->stop) && !empty($keyword)) {
    _error_log("Stop mode triggered for keyword: $keyword");

    // Count the number of processes with the keyword before killing them
    $countCommand = "pgrep -f 'ffmpeg.*$keyword' | wc -l";
    $processToKillCount = intval(exec($countCommand));

    // Kill the processes matching the keyword
    $killCommand = "pkill -f 'ffmpeg.*$keyword'";
    $killResult = exec($killCommand, $output, $killStatus);

    $processAfterKillCount = intval(exec($countCommand));

    // Attempt to delete the log file
    $unlinkSuccess = false;
    if (file_exists($logFile)) {
        $unlinkSuccess = unlink($logFile);
    }

    echo json_encode([
        'error' =>$processAfterKillCount !== 0, // Indicate if killing processes was successful
        'msg' => $processAfterKillCount !== 0 ? 'Processes killed successfully.' : 'Failed to kill processes.',
        'logFile' => $logFile,
        'kill' => $killResult, // Result of pkill command
        'keyword' => $keyword,
        'unlink' => $unlinkSuccess,
        'processToKillCount' => $processToKillCount, 
        'processAfterKillCount' => $processAfterKillCount, // Number of processes killed
        'countCommand' => $countCommand,
        'killCommand' => $killCommand,
        'output' => $output,
        'killStatus' => $killStatus, 
    ]);
    exit;
}

if (empty($ffmpegCommand)) {
    _error_log("Error: Invalid or empty FFMPEG command");
    echo json_encode([
        'error' => true,
        'msg' => 'Invalid or empty ffmpeg command',
        'codeToExec' => $codeToExec,
    ]);
    exit;
}

// Kill processes associated with the keyword
if (!empty($keyword)) {
    _error_log("Killing process with keyword: $keyword");
    killProcessFromKeyword($keyword);
}

$ffmpegCommand = addKeywordToFFmpegCommand($ffmpegCommand, $keyword);

file_put_contents($logFile, $ffmpegCommand.PHP_EOL.PHP_EOL);

$ffmpegCommand .= " > {$logFile} 2>&1";
_error_log("Executing FFMPEG Command [$keyword]: $ffmpegCommand ".json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));

try {
    $pid = execAsync($ffmpegCommand, $keyword);
    _error_log("Command executed successfully with PID: $pid");
    echo json_encode([
        'error' => false,
        'msg' => 'Command executed',
        'command' => $ffmpegCommand,
        'pid' => $pid,
        'logFile' => $logFile,
    ]);
} catch (Exception $e) {
    _error_log("Error executing command: " . $e->getMessage());
    echo json_encode([
        'error' => true,
        'msg' => 'Failed to execute command',
        'errorMsg' => $e->getMessage(),
        'logFile' => $logFile,
    ]);
}
exit;
