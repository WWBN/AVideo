<?php
function getDurationFromFile($file)
{
    global $config, $getDurationFromFile;
    if (empty($file)) {
        return "EE:EE:EE";
    }

    if (!isset($getDurationFromFile)) {
        $getDurationFromFile = [];
    }

    if (!empty($getDurationFromFile[$file])) {
        // I need to check again because I am recreating the file on the AI
        //return $getDurationFromFile[$file];
    }

    $hls = str_replace(".zip", "/index.m3u8", $file);
    $file = str_replace(".zip", ".mp4", $file);

    // get movie duration HOURS:MM:SS.MICROSECONDS
    $videoFile = $file;
    if (!file_exists($videoFile)) {
        $file_headers = @get_headers($videoFile);
        if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            error_log('getDurationFromFile try 1, File (' . $videoFile . ') Not Found original=' . $file);
            $videoFile = $hls;
        }
    }
    if (!file_exists($videoFile)) {
        $file_headers = @get_headers($videoFile);
        if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            error_log('getDurationFromFile try 2, File (' . $videoFile . ') Not Found original=' . $file);
            $videoFile = '';
        }
    }
    if (empty($videoFile)) {
        return "EE:EE:EE";
    }
    $videoFile = escapeshellarg($videoFile);
    /**
     * @var string $cmd
     */
    //$cmd = 'ffprobe -i ' . $file . ' -sexagesimal -show_entries  format=duration -v quiet -of csv="p=0"';
    eval('$cmd=get_ffprobe()." -i {$videoFile} -sexagesimal -show_entries  format=duration -v quiet -of csv=\\"p=0\\"";');
    $cmd = removeUserAgentIfNotURL($cmd);
    exec($cmd . ' 2>&1', $output, $return_val);
    if ($return_val !== 0) {
        error_log('{"status":"error", "msg":' . json_encode($output) . ' ,"return_val":' . json_encode($return_val) . ', "where":"getDuration", "cmd":"' . $cmd . '"}');
        // fix ffprobe
        $duration = "EE:EE:EE";
    } else {
        preg_match("/([0-9]+:[0-9]+:[0-9]{2})/", $output[0], $match);
        if (!empty($match[1])) {
            $duration = $match[1];
        } else {
            error_log('{"status":"error", "msg":' . json_encode($output) . ' ,"match_not_found":' . json_encode($match) . ' ,"return_val":' . json_encode($return_val) . ', "where":"getDuration", "cmd":"' . $cmd . '"}');
            $duration = "EE:EE:EE";
        }
    }
    error_log("Duration found: {$duration}");
    if ($duration !== 'EE:EE:EE') {
        $getDurationFromFile[$file] = $duration;
    }
    return $duration;
}

function wget($url, $filename, $debug = false)
{
    if (empty($url) || $url == "php://input" || !isValidURL($url)) {
        return false;
    }
    if ($lockfilename = wgetIsLocked($url)) {
        if ($debug) {
            _error_log("wget: ERROR the url is already downloading {$lockfilename} $url, $filename");
        }
        return false;
    }
    wgetLock($url);
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $content = @file_get_contents($url);
        if (!empty($content) && file_put_contents($filename, $content) > 100) {
            wgetRemoveLock($url);
            return true;
        }
        wgetRemoveLock($url);
        return false;
    }

    $filename = escapeshellarg($filename);
    $url = escapeshellarg($url);
    $cmd = "wget --tries=1 {$url} -O {$filename} --no-check-certificate";
    if ($debug) {
        _error_log("wget Start ({$cmd}) ");
    }
    //echo $cmd;
    exec($cmd);
    wgetRemoveLock($url);
    if (!file_exists($filename)) {
        _error_log("wget: ERROR the url does not download $url, $filename");
        return false;
    }
    if ($_SERVER['SCRIPT_NAME'] !== '/plugin/Live/m3u8.php' && empty(filesize($filename))) {
        _error_log("wget: ERROR the url download but is empty $url, $filename");
        return true;
    }
    return file_exists($filename);
}

function getDirSize($dir, $forceNew = false)
{
    global $_getDirSize;

    if (!isset($_getDirSize)) {
        $_getDirSize = [];
    }
    if (empty($forceNew) && isset($_getDirSize[$dir])) {
        return $_getDirSize[$dir];
    }

    _error_log("getDirSize: start {$dir}");

    if (isWindowsServer()) {
        $return = foldersize($dir);
        $_getDirSize[$dir] = $return;
        return $return;
    } else {
        $command = "du -sb {$dir}";
        exec($command . " < /dev/null 2>&1", $output, $return_val);
        if ($return_val !== 0) {
            _error_log("getDirSize: ERROR ON Command {$command}");
            $return = 0;
            $_getDirSize[$dir] = $return;
            return $return;
        } else {
            if (!empty($output[0])) {
                preg_match("/^([0-9]+).*/", $output[0], $matches);
            }
            if (!empty($matches[1])) {
                _error_log("getDirSize: found {$matches[1]} from - {$output[0]}");
                $return = intval($matches[1]);
                $_getDirSize[$dir] = $return;
                return $return;
            }

            _error_log("getDirSize: ERROR on pregmatch {$output[0]}");
            $return = 0;
            $_getDirSize[$dir] = $return;
            return $return;
        }
    }
}

function rrmdirCommandLine($dir, $async = false)
{
    if (is_dir($dir)) {
        $dir = escapeshellarg($dir);
        if (isWindowsServer()) {
            $command = ('rd /s /q ' . $dir);
        } else {
            $command = ('rm -fR ' . $dir);
        }

        if ($async) {
            return execAsync($command);
        } else {
            return exec($command);
        }
    }
}

function unzipDirectory($filename, $destination)
{
    // Set memory limit and execution time to avoid issues with large files
    ini_set('memory_limit', '-1');
    set_time_limit(0);

    // Ensure the destination directory exists, create it if it doesn't
    if (!is_dir($destination)) {
        if (!mkdir($destination, 0755, true)) {
            _error_log("unzipDirectory: Failed to create destination directory: {$destination}");
            return false;
        }
        _error_log("unzipDirectory: Destination directory created: {$destination}");
    }

    // Ensure the destination directory is writable
    if (!is_writable($destination)) {
        _error_log("unzipDirectory: Destination directory is not writable: {$destination}");
        return false;
    }

    // Escape the input parameters to prevent command injection attacks
    $filename = escapeshellarg($filename);
    $destination = escapeshellarg($destination);

    // Build the command for unzipping the file
    $cmd = "unzip -q -o {$filename} -d {$destination} 2>&1";

    // Log the command for debugging purposes
    _error_log("unzipDirectory: {$cmd}");

    // Execute the command and capture the output and return value
    exec($cmd, $output, $return_val);

    if ($return_val !== 0) {
        // Log the output and return value
        _error_log("unzipDirectory: Command failed with return value {$return_val}");
        _error_log("unzipDirectory: Command output: " . implode("\n", $output));

        // Check if the file exists
        if (!file_exists($filename)) {
            _error_log("unzipDirectory: Error - file does not exist: {$filename}");
        } else {
            _error_log("unzipDirectory: Error - file exists: {$filename}");
        }

        // Try using PHP's ZipArchive class as a fallback
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filename) === true) {
                $zip->extractTo($destination);
                $zip->close();
                _error_log("unzipDirectory: Success using ZipArchive for {$destination}");
            } else {
                _error_log("unzipDirectory: Error opening zip archive using ZipArchive: {$filename}");
            }
        } else {
            _error_log("unzipDirectory: Error: ZipArchive class is not available");
        }
    } else {
        _error_log("unzipDirectory: Success {$destination}");
    }

    // Delete the original zip file
    _error_log("unzipDirectory($filename) unlink line=" . __LINE__);
    if (@unlink($filename)) {
        _error_log("unzipDirectory: Successfully deleted the zip file: {$filename}");
    } else {
        _error_log("unzipDirectory: Error deleting the zip file: {$filename}");
    }

    return $return_val === 0;
}

function zipDirectory($source, $destination)
{
    // Set memory limit and execution time to avoid issues with large files
    ini_set('memory_limit', '-1');
    set_time_limit(0);

    // Check if the source directory exists
    if (!is_dir($source)) {
        _error_log("zipDirectory: Source directory does not exist: {$source}");
        return false;
    }

    // Ensure the destination directory exists, create it if it doesn't
    $destinationDir = dirname($destination);
    if (!is_dir($destinationDir)) {
        if (!mkdir($destinationDir, 0755, true)) {
            _error_log("zipDirectory: Failed to create destination directory: {$destinationDir}");
            return false;
        }
        _error_log("zipDirectory: Destination directory created: {$destinationDir}");
    }
    chmod($source, 0755);

    // Escape the input parameters to prevent command injection attacks
    $sourceOriginal = rtrim($source, '/'); // Remove trailing slash for consistency
    $destinationOriginal = $destination;
    $source = escapeshellarg($source);
    $destination = escapeshellarg($destination);

    // Build the command for zipping the directory
    $cmd = "zip -r -q {$destination} {$source} 2>&1";

    // Log the command for debugging purposes
    _error_log("zipDirectory: {$cmd}");

    // Execute the command and capture the output and return value
    exec($cmd, $output, $return_val);

    if ($return_val !== 0) {
        // Log the output and return value
        _error_log("zipDirectory: Command failed with return value {$return_val}");
        _error_log("zipDirectory: Command output: " . implode("\n", $output));

        // Try using PHP's ZipArchive class as a fallback
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($destinationOriginal, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $dirIterator = new RecursiveDirectoryIterator($sourceOriginal);
                $files = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::LEAVES_ONLY);

                // Get the base directory name to use as root folder in the zip
                $baseFolder = basename($sourceOriginal);

                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        // Calculate relative path, including the base folder
                        $relativePath = $baseFolder . '/' . substr($filePath, strlen($sourceOriginal) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                $zip->close();
                _error_log("zipDirectory: Success using ZipArchive for {$destination}");
            } else {
                _error_log("zipDirectory: Error opening zip archive using ZipArchive: {$destination}");
            }
        } else {
            _error_log("zipDirectory: Error: ZipArchive class is not available");
        }
    } else {
        _error_log("zipDirectory: Success {$destination}");
    }

    return file_exists($destinationOriginal);
}


function getPIDUsingPort($port)
{
    $port = intval($port);
    if (empty($port)) {
        return false;
    }
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $command = 'netstat -ano | findstr ' . $port;
        exec($command, $output, $retval);
        $pid = 0;
        foreach ($output as $value) {
            if (preg_match('/LISTENING[^0-9]+([0-9]+)/i', $value, $matches)) {
                if (!empty($matches[1])) {
                    $pid = intval($matches[1]);
                    return $pid;
                }
            }
        }
    } else {
        $command = 'lsof -n -i :' . $port . ' | grep LISTEN';
        exec($command, $output, $retval);
        $pid = 0;
        foreach ($output as $value) {
            if (preg_match('/[^ ] +([0-9]+).*/i', $value, $matches)) {
                if (!empty($matches[1])) {
                    $pid = intval($matches[1]);
                    return $pid;
                }
            } elseif (preg_match('/lsof: not found/i', $value)) {
                die('Please install lsof running this command: "sudo apt-get install lsof"');
            }
        }
    }
    return false;
}

function canExecutePgrep()
{
    // Check if we can successfully pgrep the init or systemd process
    $test = shell_exec('pgrep -f init || pgrep -f systemd');
    return !empty($test); // Return true if we can execute pgrep, false otherwise
}

function getProcessPids($processName)
{
    if (!canExecutePgrep()) {
        return null; // If we can't execute pgrep, return null
    }

    // Using pgrep with -a to get both PID and the full command line
    $output = shell_exec('pgrep -af ' . escapeshellarg($processName));

    if (empty($output)) {
        return array();
    }

    // Split the string into an array based on newline and filter out any empty values
    $lines = array_filter(explode("\n", $output));

    $pids = [];
    foreach ($lines as $line) {
        // Skip the line containing sh -c pgrep
        if (strpos($line, 'pgrep') !== false) {
            continue;
        }
        //_error_log("getProcessPids($processName) $line");
        // Extract PID from the start of the line
        list($pid,) = explode(' ', trim($line), 2);
        $pids[] = $pid;
    }

    return $pids;
}

function getCommandByPid($pid)
{
    $cmdlineFile = "/proc/{$pid}/cmdline";

    // Check if the cmdline file exists for the given PID
    if (!file_exists($cmdlineFile)) {
        return false;  // or return an error message or throw an exception
    }

    // Read the content and break it into an array using null characters as the delimiter
    $cmd = file_get_contents($cmdlineFile);
    $cmdArray = explode("\0", $cmd);

    // Remove any empty elements from the array
    $cmdArray = array_filter($cmdArray, function ($value) {
        return $value !== '';
    });

    return $cmdArray;
}

function execAsync($command, $keyword = null)
{
    if ($keyword) {
        // Sanitize the keyword to make it a valid filename
        $keyword = preg_replace('/[^a-zA-Z0-9_-]/', '_', $keyword);
    }

    $command = addcslashes($command, '"');

    if (isWindowsServer()) {
        if ($keyword) {
            // Add the keyword as a comment to the command for Windows
            $commandWithKeyword = "start /B cmd /c \"$command & REM $keyword\" > NUL 2>&1";
        } else {
            $commandWithKeyword = "start /B cmd /c \"$command\" > NUL 2>&1";
        }
        _error_log($commandWithKeyword);
        $pid = exec($commandWithKeyword, $output, $retval);
        if ($retval !== 0) {
            _error_log('execAsync Win Error: ' . json_encode($output) . ' Return Value: ' . $retval);
        } else {
            _error_log('execAsync Win: ' . json_encode($output) . ' ' . $retval);
        }
    } else {
        if ($keyword) {
            // Add the keyword as a comment to the command for Linux
            $commandWithKeyword = "nohup sh -c \"$command & echo \\$! > /tmp/$keyword.pid\" > /dev/null 2>&1 &";
        } else {
            $commandWithKeyword = "nohup sh -c \"$command & echo \\$!\" > /dev/null 2>&1 &";
        }
        _error_log('execAsync Linux: ' . $commandWithKeyword);
        exec($commandWithKeyword, $output, $retval);
        _error_log('Command output: ' . json_encode($output));
        _error_log('Return value: ' . $retval);
        if ($retval !== 0) {
            _error_log('execAsync Linux Error: ' . json_encode($output) . ' Return Value: ' . $retval);
        } else {
            if ($keyword) {
                $pidFile = "/tmp/$keyword.pid";
                _error_log('Checking PID file: ' . $pidFile);
                sleep(1); // Wait a bit to ensure the PID file is written
                if (file_exists($pidFile) && filesize($pidFile) > 0) {
                    $pid = (int)file_get_contents($pidFile);
                    _error_log('PID file exists, PID: ' . $pid);
                } else {
                    _error_log('PID file does not exist or is empty. Using output[0].');
                    if (!empty($output[0])) {
                        $pid = (int)$output[0];
                        _error_log('PID from output[0]: ' . $pid);
                        // Save the PID to the file as a fallback
                        file_put_contents($pidFile, $pid);
                        _error_log('PID saved to file: ' . $pidFile);
                    } else {
                        _error_log('Output[0] is also empty. Unable to determine PID.');
                        $pid = null;
                    }
                }
            } else {
                if (empty($output)) {
                    return $output;
                }
                $pid = (int)$output[0];
            }
        }
    }
    return $pid;
}

function execFFMPEGAsyncOrRemote($command, $keyword = null)
{
    $obj = AVideoPlugin::getDataObjectIfEnabled('API');
    if(!empty($obj) && !empty($obj->standAloneFFMPEG)){
        $url = "{$obj->standAloneFFMPEG}";

        $codeToExec = array('ffmpegCommand'=>$command, 'keyword'=>$keyword, 'time'=>time());

        $codeToExecEncrypted = encryptString(json_encode($codeToExec));

        $url = addQueryStringParameter($url, 'APISecret', $obj->APISecret);
        $url = addQueryStringParameter($url, 'codeToExecEncrypted', $codeToExecEncrypted);
        //var_dump($url);
        _error_log("execFFMPEGAsyncOrRemote: URL $command");
        _error_log("execFFMPEGAsyncOrRemote: URL $url");
        return url_get_contents($url);
    }else{
        _error_log("execFFMPEGAsyncOrRemote: Async $command");
        return execAsync($command, $keyword);
    }
}

function getFFMPEGRemoteLog($keyword)
{
    $obj = AVideoPlugin::getDataObjectIfEnabled('API');
    if(!empty($obj) && !empty($obj->standAloneFFMPEG)){
        $url = "{$obj->standAloneFFMPEG}";

        $codeToExec = array('log'=>1, 'keyword'=>$keyword, 'time'=>time());

        $codeToExecEncrypted = encryptString(json_encode($codeToExec));

        $url = addQueryStringParameter($url, 'APISecret', $obj->APISecret);
        $url = addQueryStringParameter($url, 'codeToExecEncrypted', $codeToExecEncrypted);
        var_dump($url);
        _error_log("execFFMPEGAsyncOrRemote: URL $url");
        return url_get_contents($url);
    }else{
        return false;
    }
}

// Function to find the process by keyword using the pid file
function findProcess($keyword)
{
    $output = [];
    if ($keyword) {
        // Sanitize the keyword to make it a valid filename
        $keyword = preg_replace('/[^a-zA-Z0-9_-]/', '_', $keyword);
    }
    // Use pgrep to find processes with the keyword (case insensitive)
    exec("pgrep -fai " . escapeshellarg($keyword), $pgrepOutput, $retval);
    //var_dump($pgrepOutput);
    if ($retval === 0) {
        foreach ($pgrepOutput as $pgrepPid) {
            if (preg_match('/pgrep /i', $pgrepPid)) {
                continue;
            }
            if (preg_match('/([0-9]+) (.*)/i', $pgrepPid, $matches)) {
                if (!empty($matches[2])) {
                    $output[] = array('pid' => (int)$matches[1], 'command' => trim($matches[2]));
                }
            }
            //$output[] = (int)$pgrepPid;
            //$output[] = $pgrepPid;
        }
    }

    // Remove duplicate PIDs
    $output = array_unique($output);

    return $output; // Returns an array of PIDs
}


// Function to kill the process by keyword using the pid file
function killProcessFromKeyword($keyword)
{
    $pids = findProcess($keyword);
    _error_log("killProcessFromKeyword($keyword) findProcess " . json_encode($pids));
    foreach ($pids as $pid) {
        killProcess($pid);
    }
}

function killProcess($pid)
{
    if (is_array($pid)) {
        $pid = $pid['pid'];
    }

    $pid = intval($pid);
    if (empty($pid)) {
        _error_log("killProcess: Invalid PID $pid");
        return false;
    }

    _error_log("killProcess($pid)");

    if (isWindowsServer()) {
        $cmd = "taskkill /F /PID $pid";
    } else {
        $cmd = "kill -9 $pid";
    }
    _error_log("Executing command: $cmd");

    exec($cmd, $output, $retval);

    if ($retval === 0) {
        _error_log("killProcess: Successfully killed process $pid");
        return true;
    } else {
        _error_log("killProcess: Failed to kill process $pid. Command output: " . json_encode($output) . " Return value: $retval");
        return false;
    }
}
