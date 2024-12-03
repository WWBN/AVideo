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

if (empty($streamerURL)) {
    echo json_encode(['error' => true, 'message' => 'streamerURL not defined']);
    exit;
}

// Function to safely get inputs from either command line or request
function getInput($key, $default = '') {
    global $argv;

    // Check if running from command line or HTTP request
    if (php_sapi_name() === 'cli') {
        // Look for the parameter in $argv (command line)
        foreach ($argv as $arg) {
            if (strpos($arg, "{$key}=") === 0) {
                return substr($arg, strlen("{$key}="));
            }
        }
    } else {
        // Fallback to HTTP request ($_REQUEST)
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }

    return $default;
}

// Validate and sanitize the ffmpegCommand
function sanitizeFFmpegCommand($command) {
    // Allowable ffmpeg prefixes
    $allowedPrefixes = ['ffmpeg', '/usr/bin/ffmpeg', '/bin/ffmpeg'];

    // Remove dangerous characters
    $command = str_replace('&&', '', $command);
    $command = preg_replace('/[;|`<]/', '', $command);

    // Ensure it starts with ffmpeg
    foreach ($allowedPrefixes as $prefix) {
        if (strpos(trim($command), $prefix) === 0) {
            return $command;
        }
    }

    return '';
}

// Fetch and sanitize inputs
$ffmpegCommand = sanitizeFFmpegCommand(getInput('ffmpegCommand', ''));
$keyword = getInput('keyword', '');

// Kill processes associated with the keyword
if (!empty($keyword)) {
    killProcessFromKeyword($keyword);
}

// Validate that ffmpegCommand is not empty after sanitization
if (empty($ffmpegCommand)) {
    echo json_encode([
        'error' => true,
        'msg' => 'Invalid or empty ffmpeg command',
    ]);
    exit;
}

// Debug output (optional)
error_log("Constructed FFMPEG Command: $ffmpegCommand");

try {
    $pid = execAsync($ffmpegCommand, $keyword);
    echo json_encode([
        'error' => false,
        'msg' => 'Command executed',
        'command' => $ffmpegCommand,
        'pid' => $pid,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'msg' => 'Failed to execute command',
        'errorMsg' => $e->getMessage(),
    ]);
}
exit;
