<?php

// Directory where FFmpeg logs are stored
$logDir = '/var/www/tmp/';
// Command to list FFmpeg processes
$psCommand = 'ps -eo pid,cmd | grep ffmpeg';
// Number of retries allowed
$retryLimit = 10;
// Number of log lines to check from the end of the file
$linesToCheck = 50;
// Time threshold for old files (30 days)
$oldFileThreshold = 30 * 24 * 60 * 60; // 30 days in seconds

// Function to read the last N lines of a file
function tailFile($file, $lines = 50) {
    $fp = fopen($file, 'r');
    if (!$fp) {
        return [];
    }

    $data = [];
    fseek($fp, -1, SEEK_END);
    $pos = ftell($fp);
    $lastLine = '';

    while ($lines > 0 && $pos > 0) {
        $char = fgetc($fp);
        if ($char === "\n" && trim($lastLine)) {
            $lines--;
            $data[] = $lastLine; // Add the line to the array
            $lastLine = '';
        } else {
            $lastLine = $char . $lastLine;
        }
        fseek($fp, $pos--);
    }

    fclose($fp);

    // If there is a last line that wasn't added (file without a newline at the end)
    if (!empty($lastLine)) {
        $data[] = $lastLine;
    }

    return $data;
}

// Function to format the last modified time
function formatLastModifiedTime($timestamp) {
    return date('Y-m-d H:i:s', $timestamp);
}

function _humanFileSize($size, $unit = "") {
    if ((!$unit && $size >= 1 << 40) || $unit == "TB") {
        return number_format($size / (1 << 40), 2) . "TB";
    }

    if ((!$unit && $size >= 1 << 30) || $unit == "GB") {
        return number_format($size / (1 << 30), 2) . "GB";
    }

    if ((!$unit && $size >= 1 << 20) || $unit == "MB") {
        return number_format($size / (1 << 20), 2) . "MB";
    }

    if ((!$unit && $size >= 1 << 10) || $unit == "KB") {
        return number_format($size / (1 << 10), 2) . "KB";
    }

    return number_format($size) . " bytes";
}


// Get all log files for FFmpeg restreamers
$logFiles = glob($logDir . 'ffmpeg_restreamer_*.log');

// Get the current time
$currentTime = time();
echo "kill_ffmpeg_restream.php logFiles start.\n";
$maxSize = 4 * 1024 * 1024; // 4 MB in bytes
foreach ($logFiles as $logFile) {
    $filesize = filesize($logFile);
    // Get the last modified time of the log file
    $lastModified = filemtime($logFile);

    if ($filesize > $maxSize) {
        echo "kill_ffmpeg_restream.php The file too large logFiles $logFile "._humanFileSize($filesize).PHP_EOL;
        continue;
    }else{
        //echo "kill_ffmpeg_restream.php logFiles $logFile "._humanFileSize($filesize).PHP_EOL;
    }
    $lastModifiedFormatted = formatLastModifiedTime($lastModified);

    // Check if the log file has not been modified for more than 30 days and delete it if true
    if (($currentTime - $lastModified) > $oldFileThreshold) {
        echo "Deleting old log file: $logFile (last modified on $lastModifiedFormatted, more than 30 days ago).\n";
        unlink($logFile);
        continue;
    }

    //echo "Processing log file: $logFile (last modified on $lastModifiedFormatted).\n";

    // Read the last N lines of the log file
    $logContent = tailFile($logFile, $linesToCheck);

    if (empty($logContent)) {
        echo "Empty log content in $logFile.\n";
        continue;
    }

    // Variables to keep track of retries
    $consecutiveOpenings = 0;
    $processShouldBeKilled = false;
    $lastUrlOpened = '';
    $foundTsFile = false;

    //echo "kill_ffmpeg_restream.php start.\n";
    // Loop through the last N lines of the log file
    foreach ($logContent as $key => $line) {
        $line = str_replace(array("\r", "\n"), '', $line);
            
        // Check if the line contains "Exiting normally" or "Conversion failed"
        if (strpos($line, 'Exiting normally') !== false || strpos($line, 'Conversion failed') !== false) {
            echo "Skipping ERROR log file $logFile due to message: $line (last modified on $lastModifiedFormatted).\n";
            continue 2; // Skip to the next log file
        }

        // Check if there are encoding stats (indicating a successful process)
        if (preg_match("/\] kb\/s:\d+\.\d+/i", $line)) {
            echo "Skipping SUCCESS log file $logFile due to message: $line (last modified on $lastModifiedFormatted).\n";
            continue 2; // Skip to the next log file
        }

        // Check if the line contains 'Opening'
        if (preg_match("/Opening '(.*)' for reading/", $line, $matches)) {
            $url = $matches[1];

            if (strpos($url, '.ts') !== false) {
                $foundTsFile = true; // .ts file found, prevent killing
                $consecutiveOpenings = 0; // Reset if a .ts file is found
                break; // No need to check further
            } else {
                $consecutiveOpenings++;
                $lastUrlOpened = $url;
            }

            // If the number of consecutive "Opening" without .ts exceeds the limit, mark for killing
            if ($consecutiveOpenings >= $retryLimit) {
                $processShouldBeKilled = true;
                break;
            }
        }
    }
    //echo "kill_ffmpeg_restream.php done.\n";
    // If any .ts file is found, do not kill the process
    if ($foundTsFile) {
        echo "Found .ts file in log, process will not be killed for log file: $logFile (last modified on $lastModifiedFormatted).\n";
        continue; // Skip this log file
    }

    // If we need to kill the process
    if ($processShouldBeKilled && !empty($lastUrlOpened)) {
        // Get the list of running FFmpeg processes
        $processList = shell_exec($psCommand);

        // Loop through each process and find the one related to the last URL opened
        $lines = explode("\n", $processList);
        foreach ($lines as $processLine) {
            if (strpos($processLine, $lastUrlOpened) !== false) {
                // Extract the process ID
                preg_match('/^\s*(\d+)/', $processLine, $pidMatch);
                if (isset($pidMatch[1])) {
                    $pid = $pidMatch[1];
                    echo "Killed FFmpeg process with PID: $pid for URL: $lastUrlOpened".PHP_EOL;
                    // Kill the process
                    shell_exec("kill -9 $pid");
                    echo "Killed FFmpeg process with PID: $pid for URL: $lastUrlOpened (log file last modified on $lastModifiedFormatted).\n";
                }
            }
        }
    }
}

echo "kill_ffmpeg_restream.php logFiles done.\n";
?>
