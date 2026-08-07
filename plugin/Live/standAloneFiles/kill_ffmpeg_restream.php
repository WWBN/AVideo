<?php

// Directory where FFmpeg logs are stored
$logDir = '/var/www/tmp/';
// Command to list FFmpeg processes
$psCommand = 'ps -eo pid,cmd | grep ffmpeg | grep -v grep';
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

    $fileSize = filesize($file);
    if ($fileSize === 0) {
        fclose($fp);
        return [];
    }

    // Read at most the last 100 KB to avoid slow processing of large files.
    // FFmpeg logs with \r-only line endings would cause the old char-by-char
    // loop to iterate the entire file byte by byte, hanging for minutes.
    $readLimit = min($fileSize, 100 * 1024);
    fseek($fp, -$readLimit, SEEK_END);
    $content = fread($fp, $readLimit);
    fclose($fp);

    if ($content === false || $content === '') {
        return [];
    }

    // Normalize all line endings (\r\n and \r) to \n before splitting
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    $allLines = array_values(array_filter(explode("\n", $content), 'strlen'));

    return array_slice($allLines, -$lines);
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

    // FFmpeg always prints its final per-stream stats line (e.g. "kb/s:2503.20")
    // as part of the summary it dumps right before exiting - this happens on a
    // *successful* stop AND on a *crash* ("Conversion failed!"/broken pipe/I-O
    // error). Since that stats line is written to the log BEFORE the terminal
    // "Conversion failed"/"Exiting normally" line, scanning the tail in
    // chronological order and stopping at the first match previously caused
    // crashed restreams to be misreported as "SUCCESS" (the stats line was
    // always reached first). Scan the whole tail for the terminal marker
    // first, regardless of line position, so a crash is never masked.
    $terminalErrorLine = null;
    foreach ($logContent as $line) {
        $line = str_replace(array("\r", "\n"), '', $line);
        if (strpos($line, 'Exiting normally') !== false || strpos($line, 'Conversion failed') !== false) {
            $terminalErrorLine = $line;
            break;
        }
    }
    if ($terminalErrorLine !== null) {
        echo "CRASHED/EXITED restream log file $logFile due to message: $terminalErrorLine (last modified on $lastModifiedFormatted). "
            . "The FFmpeg process already terminated on its own, so there is no process left to kill here. "
            . "If the live source is still active and this restream did not restart automatically, enable "
            . "'Restream Watchdog' in the Live plugin settings for automatic recovery.\n";
        continue; // Skip to the next log file
    }

    //echo "kill_ffmpeg_restream.php start.\n";
    // Loop through the last N lines of the log file
    foreach ($logContent as $key => $line) {
        $line = str_replace(array("\r", "\n"), '', $line);

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
        $lines = explode("\n", $processList ?? '');
        foreach ($lines as $processLine) {
            if (strpos($processLine, $lastUrlOpened) !== false) {
                // Extract the process ID
                preg_match('/^\s*(\d+)/', $processLine, $pidMatch);
                if (isset($pidMatch[1])) {
                    $pid = (int) $pidMatch[1];
                    if ($pid > 0) {
                        echo "Killed FFmpeg process with PID: $pid for URL: $lastUrlOpened".PHP_EOL;
                        // Kill the process
                        shell_exec("kill -9 $pid");
                        echo "Killed FFmpeg process with PID: $pid for URL: $lastUrlOpened (log file last modified on $lastModifiedFormatted).\n";
                    }
                }
            }
        }
    }
}

echo "kill_ffmpeg_restream.php logFiles done.\n";
?>
