<?php

function cutVideoWithFFmpeg($inputFile, $startTimeInSeconds, $endTimeInSeconds, $outputFile)
{
    // Ensure start and end times are numeric
    $startTimeInSeconds = (int)$startTimeInSeconds;
    $endTimeInSeconds = (int)$endTimeInSeconds;
    make_path($outputFile);
    // Escape arguments to ensure command is safe to execute
    $escapedInputFile = escapeshellarg($inputFile);
    $escapedOutputFile = escapeshellarg($outputFile);
    $escapedStartTime = escapeshellarg($startTimeInSeconds);
    $escapedEndTime = escapeshellarg($endTimeInSeconds);

    // Construct the FFmpeg command
    $cmd = get_ffmpeg() . " -ss {$escapedStartTime} -to {$escapedEndTime} -i {$escapedInputFile} -c copy {$escapedOutputFile}";

    $cmd = removeUserAgentIfNotURL($cmd);
    // Execute the command
    _error_log('cutVideoWithFFmpeg start ' . $cmd);

    exec($cmd, $output, $returnVar);

    // Check if the command was executed successfully
    if ($returnVar === 0) {
        _error_log('cutVideoWithFFmpeg success ' . $outputFile);
        return true; // Command executed successfully
    } else {
        _error_log('cutVideoWithFFmpeg error ');
        return false; // Command failed
    }
}

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
            error_log('getDurationFromFile try 1, File (' . $videoFile . ') Not Found');
            $videoFile = $hls;
        }
    }
    if (!file_exists($videoFile)) {
        $file_headers = @get_headers($videoFile);
        if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            error_log('getDurationFromFile try 2, File (' . $videoFile . ') Not Found');
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
    return false;
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

    if (isWindows()) {
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

function convertVideoFileWithFFMPEG($fromFileLocation, $toFileLocation, $logFile = '', $try = 0)
{
    $parts = explode('?', $fromFileLocation);
    $localFileLock = getCacheDir() . 'convertVideoFileWithFFMPEG_' . md5($parts[0]) . ".lock";
    $ageInSeconds = time() - @filemtime($localFileLock);
    if ($ageInSeconds > 60) {
        _error_log("convertVideoFileWithFFMPEG: age: {$ageInSeconds} too long without change, unlock it " . $fromFileLocation);
        @unlink($localFileLock);
    } elseif (file_exists($localFileLock)) {
        _error_log("convertVideoFileWithFFMPEG: age: {$ageInSeconds} download from CDN There is a process running for " . $fromFileLocation);
        return false;
    } else {
        _error_log("convertVideoFileWithFFMPEG: creating file: localFileLock: {$localFileLock} toFileLocation: {$toFileLocation}");
    }
    make_path($toFileLocation);
    file_put_contents($localFileLock, time());
    $fromFileLocationEscaped = escapeshellarg($fromFileLocation);
    $toFileLocationEscaped = escapeshellarg($toFileLocation);

    $format = pathinfo($toFileLocation, PATHINFO_EXTENSION);

    if ($format == 'mp3') {
        switch ($try) {
            case 0:
                $command = get_ffmpeg() . " -i \"{$fromFileLocation}\" -c:a libmp3lame \"{$toFileLocation}\"";
                break;
            default:
                return false;
                break;
        }
    } else {
        if ($try === 0 && preg_match('/_offline\.mp4/', $toFileLocation)) {
            $try = 'offline';
            $fromFileLocationEscaped = "\"$fromFileLocation\"";
            $command = get_ffmpeg() . " -i {$fromFileLocationEscaped} -crf 30 {$toFileLocationEscaped}";
        } else {
            switch ($try) {
                case 0:
                    $command = get_ffmpeg() . " -i {$fromFileLocationEscaped} -c:v libx264 -preset veryfast -crf 23 -c:a aac -b:a 128k {$toFileLocationEscaped}";
                    break;
                case 1:
                    $command = get_ffmpeg() . " -i {$fromFileLocationEscaped} -c copy {$toFileLocationEscaped}";
                    break;
                case 2:
                    $command = get_ffmpeg() . " -allowed_extensions ALL -y -i {$fromFileLocationEscaped} -c:v copy -c:a copy -bsf:a aac_adtstoasc -strict -2 {$toFileLocationEscaped}";
                    break;
                case 3:
                    $command = get_ffmpeg() . " -y -i {$fromFileLocationEscaped} -c:v copy -c:a copy -bsf:a aac_adtstoasc -strict -2 {$toFileLocationEscaped}";
                    break;
                default:
                    return false;
                    break;
            }
        }
    }
    if (!empty($logFile)) {
        $progressFile = getConvertVideoFileWithFFMPEGProgressFilename($toFileLocation);
    } else {
        $progressFile = $logFile;
    }
    $command = removeUserAgentIfNotURL($command);
    _session_write_close();
    _mysql_close();
    _error_log("convertVideoFileWithFFMPEG try[{$try}]: " . $command . ' ' . json_encode(debug_backtrace()));
    exec($command, $output, $return);
    _session_start();
    _mysql_connect();
    _error_log("convertVideoFileWithFFMPEG try[{$try}] output: " . json_encode($output));

    unlink($localFileLock);

    return ['return' => $return, 'output' => $output, 'command' => $command, 'fromFileLocation' => $fromFileLocation, 'toFileLocation' => $toFileLocation, 'progressFile' => $progressFile];
}

function rrmdirCommandLine($dir, $async = false)
{
    if (is_dir($dir)) {
        $dir = escapeshellarg($dir);
        if (isWindows()) {
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

    // Escape the input parameters to prevent command injection attacks
    $filename = escapeshellarg($filename);
    $destination = escapeshellarg($destination);

    // Build the command for unzipping the file
    $cmd = "unzip -q -o {$filename} -d {$destination} 2>&1";

    // Log the command for debugging purposes
    _error_log("unzipDirectory: {$cmd}");

    // Execute the command and check the return value
    exec($cmd, $output, $return_val);

    if ($return_val !== 0) {
        // If the unzip command fails, try using PHP's ZipArchive class as a fallback
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filename) === true) {
                $zip->extractTo($destination);
                $zip->close();
                _error_log("unzipDirectory: Success {$destination}");
            } else {
                _error_log("unzipDirectory: Error opening zip archive: {$filename}");
            }
        } else {
            _error_log("unzipDirectory: Error: ZipArchive class is not available");
        }
    } else {
        _error_log("unzipDirectory: Success {$destination}");
    }

    // Delete the original zip file
    _error_log("unzipDirectory($filename) unlink line=" . __LINE__);
    @unlink($filename);
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

function execAsync($command)
{
    //$command = escapeshellarg($command);
    // If windows, else
    if (isWindows()) {
        //echo $command;
        //$pid = system("start /min  ".$command. " > NUL");
        //$commandString = "start /B " . $command;
        //pclose($pid = popen($commandString, "r"));
        _error_log($command);
        $pid = exec($command, $output, $retval);
        _error_log('execAsync Win: ' . json_encode($output) . ' ' . $retval);
    } else {
        $newCommand = $command . " > /dev/null 2>&1 & echo $!; ";
        _error_log('execAsync Linux: ' . $newCommand);
        $pid = exec($newCommand);
    }
    return $pid;
}

function killProcess($pid)
{
    $pid = intval($pid);
    if (empty($pid)) {
        return false;
    }
    if (isWindows()) {
        exec("taskkill /F /PID $pid");
    } else {
        exec("kill -9 $pid");
    }
    return true;
}
