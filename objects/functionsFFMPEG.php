<?php


function get_ffmpeg($ignoreGPU = false)
{
    global $global;
    $complement = ' -user_agent "' . getSelfUserAgent() . '" ';
    //return 'ffmpeg -headers "User-Agent: '.getSelfUserAgent("FFMPEG").'" ';
    $ffmpeg = 'ffmpeg ';
    if (empty($ignoreGPU) && !empty($global['ffmpegGPU'])) {
        $ffmpeg .= ' --enable-nvenc ';
    }
    if (!empty($global['ffmpeg'])) {
        _error_log('get_ffmpeg $global[ffmpeg] detected ' . $global['ffmpeg']);
        $ffmpeg = "{$global['ffmpeg']}{$ffmpeg}";
    } else {
        _error_log('get_ffmpeg default ' . $ffmpeg . $complement);
    }
    return $ffmpeg . $complement;
}

function get_ffprobe()
{
    global $global;
    $complement = ' -user_agent "' . getSelfUserAgent() . '" ';
    //return 'ffmpeg -user_agent "'.getSelfUserAgent("FFMPEG").'" ';
    //return 'ffmpeg -headers "User-Agent: '.getSelfUserAgent("FFMPEG").'" ';
    $ffprobe = 'ffprobe  ';
    if (!empty($global['ffprobe'])) {

        $dir = dirname($global['ffprobe']);

        $ffprobe = "{$dir}/{$ffprobe}";
    }
    return $ffprobe . $complement;
}

function convertVideoToMP3FileIfNotExists($videos_id, $forceTry = 0)
{
    global $global;
    if (!empty($global['disableMP3'])) {
        _error_log('convertVideoToMP3FileIfNotExists: $global[disableMP3] isset');
        return false;
    }
    $video = Video::getVideoLight($videos_id);
    if (empty($video)) {
        _error_log("convertVideoToMP3FileIfNotExists: videos_id=$videos_id not found");
        return false;
    }
    $types = [Video::$videoTypeVideo, Video::$videoTypeAudio];
    if (!in_array($video['type'], $types)) {
        _error_log("convertVideoToMP3FileIfNotExists: invalid type {$video['type']}");
        return false;
    }

    $paths = Video::getPaths($video['filename']);
    $mp3HLSFile = "{$paths['path']}index.mp3";
    $mp3File = "{$paths['path']}{$video['filename']}.mp3";
    if (file_exists($mp3HLSFile) || file_exists($mp3File)) {
        return Video::getSourceFile($video['filename'], ".mp3", true);
    } else {
        $f = convertVideoFileWithFFMPEGIsLockedInfo($mp3File);
        if ($f['isUnlocked']) {
            _error_log("convertVideoToMP3FileIfNotExists: start videos_id=$videos_id try=$forceTry ");
            $sources = getVideosURLOnly($video['filename'], false);
            if (!empty($sources)) {
                if (!empty($sources['m3u8'])) {
                    $source = $sources['m3u8'];
                } else {
                    $source = end($sources);
                }
                convertVideoFileWithFFMPEG($source['url'], $mp3File, '', $forceTry);
                if (file_exists($mp3File)) {
                    return Video::getSourceFile($video['filename'], ".mp3", true);
                } else {
                    _error_log("convertVideoToMP3FileIfNotExists: file not exists {$mp3File}");
                }
            } else {
                _error_log("convertVideoToMP3FileIfNotExists: sources not found");
            }
        } else {
            _error_log("convertVideoToMP3FileIfNotExists: is locked");
        }
        return false;
    }
}

/**
 * Cleans up the specified directory by deleting files that do not match the given resolution pattern.
 * To schedule this function as a cron job, add the following line to the crontab file:
 *
 * 0 * * * * php /var/www/html/AVideo/install/cleanup_downloads.php
 *
 * @param int $resolution The resolution to keep (default is 720).
 */
function cleanupDownloadsDirectory($resolution = 720)
{

    $videosDir = getVideosDir();
    $directory = "{$videosDir}downloads/";
    // Check if the directory exists
    if (!is_dir($directory)) {
        _error_log("cleanupDownloadsDirectory: Directory does not exist: {$directory}");
        return;
    }

    // Open the directory
    if ($handle = opendir($directory)) {
        while (false !== ($entry = readdir($handle))) {
            // Skip . and .. directories
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Full path of the current file/directory
            $filePath = $directory . $entry;

            $fsize = filesize($filePath);

            // Check if it's a file and does not match the resolution pattern (e.g., '480_.mp4')
            $pattern = '/' . $resolution . '_.mp4$/';
            if (is_file($filePath) && (!preg_match($pattern, $entry) || empty($fsize))) {
                // Attempt to delete the file
                if (unlink($filePath)) {
                    _error_log("cleanupDownloadsDirectory: Deleted file: {$filePath}");
                } else {
                    _error_log("cleanupDownloadsDirectory: Failed to delete file: {$filePath}");
                }
            } else {
                _error_log("cleanupDownloadsDirectory:do not delete [$entry][{$pattern}]: {$filePath} " . humanFileSize($fsize) . ' ' . json_encode(array(is_file($filePath), preg_match($pattern, $entry),  empty($fsize))));
            }
        }
        // Close the directory handle
        closedir($handle);
    } else {
        _error_log("cleanupDownloadsDirectory: Failed to open directory: {$directory}");
    }
}

function m3u8ToMP4($input, $makeItPermanent = false, $force = false)
{
    $videosDir = getVideosDir();
    $outputfilename = str_replace($videosDir, "", $input);
    $parts = explode("/", $outputfilename);
    $resolution = Video::getResolutionFromFilename($input);
    $video_filename = $parts[count($parts) - 2];
    $lockFile = "/tmp/m3u8ToMP4.lock";

    // Lock file logic
    if (file_exists($lockFile) || $force) {
        $lockFileAge = time() - filemtime($lockFile);
        if ($lockFileAge <= 600) { // 10 minutes = 600 seconds
            _error_log("m3u8ToMP4: Another process is already running. Lock file age: {$lockFileAge} seconds.");
            return [
                'error' => true,
                'msg' => 'Another process is already running. Please wait and try again.',
            ];
        } else {
            // Lock file is older than 10 minutes, remove it
            @unlink($lockFile);
        }
    } else {
        _error_log("m3u8ToMP4: Another process is already running");
    }

    // Create the lock file
    file_put_contents($lockFile, time());

    if ($makeItPermanent) {
        $outputfilename = "index.mp4";
        $outputpathDir = "{$videosDir}{$video_filename}/";
    } else {
        $outputfilename = $video_filename . "_{$resolution}_.mp4";
        $outputpathDir = "{$videosDir}downloads/";
    }

    make_path($outputpathDir);
    $outputpath = "{$outputpathDir}{$outputfilename}";
    $msg = '';
    $error = true;

    if (empty($outputfilename)) {
        $msg = "downloadHLS: empty outputfilename {$outputfilename}";
        _error_log($msg);
        unlink($lockFile); // Remove lock file
        return ['error' => $error, 'msg' => $msg];
    }

    _error_log("downloadHLS: m3u8ToMP4($input)");
    $ism3u8 = preg_match('/.m3u8$/i', $input);

    if (!preg_match('/^http/i', $input) && (filesize($input) <= 10 || $ism3u8)) { // dummy file
        $filepath = pathToRemoteURL($input, true, true);
        if ($ism3u8 && !preg_match('/.m3u8$/i', $filepath)) {
            $filepath = addLastSlash($filepath) . 'index.m3u8';
        }

        $token = getToken(60);
        $filepath = addQueryStringParameter($filepath, 'globalToken', $token);
    } else {
        $filepath = escapeshellcmdURL($input);
    }

    if (is_dir($filepath)) {
        $filepath = addLastSlash($filepath) . 'index.m3u8';
    }

    if (!file_exists($outputpath)) {
        $return = convertVideoFileWithFFMPEG($filepath, $outputpath);

        if (empty($return)) {
            $msg3 = "downloadHLS: ERROR 2 ";
            $finalMsg = $msg . PHP_EOL . $msg3;
            _error_log($msg3);
            unlink($lockFile); // Remove lock file
            return ['error' => $error, 'msg' => $finalMsg];
        } else {
            unlink($lockFile); // Remove lock file
            return [
                'error' => false,
                'msg' => implode(', ', $return['output']),
                'path' => $return['toFileLocation'],
                'filename' => basename($return['toFileLocation']),
                'return' => $return
            ];
        }
    } else {
        $msg = "downloadHLS: outputpath already exists ({$outputpath})";
        _error_log($msg);
    }

    $error = false;
    unlink($lockFile); // Remove lock file
    return ['error' => $error, 'msg' => $msg, 'path' => $outputpath, 'filename' => $outputfilename];
}


function getConvertVideoFileWithFFMPEGProgressFilename($toFileLocation)
{
    $progressFile = $toFileLocation . '.log';
    return $progressFile;
}

function convertVideoToDownlaodProgress($toFileLocation)
{
    $progressFile = getConvertVideoFileWithFFMPEGProgressFilename($toFileLocation);
    return parseFFMPEGProgress($progressFile);
}

function parseFFMPEGProgress($progressFilename)
{
    //get duration of source
    $obj = new stdClass();

    $obj->duration = 0;
    $obj->currentTime = 0;
    $obj->progress = 0;
    $obj->from = '';
    $obj->to = '';
    $obj->fileFound = false;
    if (!file_exists($progressFilename)) {
        return $obj;
    }

    $obj->filemtime = filemtime($progressFilename);
    $obj->secondsOld = time() - $obj->filemtime;

    $content = url_get_contents($progressFilename);
    if (empty($content)) {
        return $obj;
    }

    $obj->fileFound = true;
    //var_dump($content);exit;
    preg_match("/Duration: (.*?), start:/", $content, $matches);
    if (!empty($matches[1])) {
        $rawDuration = $matches[1];

        //rawDuration is in 00:00:00.00 format. This converts it to seconds.
        $ar = array_reverse(explode(":", $rawDuration));
        $duration = floatval($ar[0]);
        if (!empty($ar[1])) {
            $duration += intval($ar[1]) * 60;
        }
        if (!empty($ar[2])) {
            $duration += intval($ar[2]) * 60 * 60;
        }

        //get the time in the file that is already encoded
        preg_match_all("/time=(.*?) bitrate/", $content, $matches);

        $rawTime = array_pop($matches);

        //this is needed if there is more than one match
        if (is_array($rawTime)) {
            $rawTime = array_pop($rawTime);
        }
        if (empty($rawTime)) {
            $rawTime = '00:00:00.00';
        }
        //rawTime is in 00:00:00.00 format. This converts it to seconds.
        $ar = array_reverse(explode(":", $rawTime));
        $time = floatval($ar[0]);
        if (!empty($ar[1])) {
            $time += intval($ar[1]) * 60;
        }
        if (!empty($ar[2])) {
            $time += intval($ar[2]) * 60 * 60;
        }

        if (!empty($duration)) {
            //calculate the progress
            $progress = round(($time / $duration) * 100);
        } else {
            $progress = 'undefined';
        }
        $obj->duration = $duration;
        $obj->currentTime = $time;
        $obj->remainTime = ($obj->duration - $time);
        $obj->remainTimeHuman = secondsToVideoTime($obj->remainTime);
        $obj->progress = $progress;
    }

    preg_match("/Input[a-z0-9 #,]+from '([^']+)':/", $content, $matches);
    if (!empty($matches[1])) {
        $path_parts = pathinfo($matches[1]);
        $partsExtension = explode('?', $path_parts['extension']);
        $obj->from = $partsExtension[0];
    }

    preg_match("/Output[a-z0-9 #,]+to '([^']+)':/", $content, $matches);
    if (!empty($matches[1])) {
        $path_parts = pathinfo($matches[1]);
        $partsExtension = explode('?', $path_parts['extension']);
        $obj->to = $partsExtension[0];
    }

    return $obj;
}

function removeUserAgentIfNotURL($cmd)
{
    if (!preg_match('/ -i [\'"]?https?:/i', $cmd) && !preg_match('/ffprobe.*[\'"]?https?:/i', $cmd)) {
        $cmd = preg_replace('/-user_agent "[^"]+"/', '', $cmd);
    }
    return $cmd;
}

function convertVideoFileWithFFMPEGIsLockedInfo($toFileLocation)
{
    $localFileLock = $toFileLocation . ".lock";
    $ageInSeconds = time() - @filemtime($localFileLock);
    $isOld = $ageInSeconds > 300;
    $file_exists = file_exists($localFileLock);
    return array(
        'ageInSeconds' => $ageInSeconds,
        'isOld' => $isOld,
        'file_exists' => file_exists($localFileLock),
        'localFileLock' => $localFileLock,
        'isUnlocked' => $isOld || !$file_exists,
    );
}

function convertVideoFileWithFFMPEG($fromFileLocation, $toFileLocation, $logFile = '', $try = 0)
{
    global $global;

    // Dynamically get the number of CPU cores
    $threads = 1; // Default to 1 thread
    if (function_exists('shell_exec')) {
        $cpuCores = (int)shell_exec('nproc 2>/dev/null'); // Linux
        if (!$cpuCores) {
            $cpuCores = (int)shell_exec('sysctl -n hw.ncpu 2>/dev/null'); // macOS
        }
        if ($cpuCores > 1) {
            $threads = $cpuCores - 1;
        } else {
            _error_log("convertVideoFileWithFFMPEG: Unable to detect CPU cores. Defaulting to 1 thread.");
        }
    } else {
        _error_log("convertVideoFileWithFFMPEG: shell_exec is disabled. Defaulting to 1 thread.");
    }

    $f = convertVideoFileWithFFMPEGIsLockedInfo($toFileLocation);
    $localFileLock = $f['localFileLock'];
    if ($f['isOld']) {
        _error_log("convertVideoFileWithFFMPEG: age: {$f['ageInSeconds']} too long without change, unlock it " . $fromFileLocation . ' ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        @unlink($localFileLock);
    } elseif ($f['file_exists']) {
        _error_log("convertVideoFileWithFFMPEG: age: {$f['ageInSeconds']} download from CDN There is a process running for {$fromFileLocation} localFileLock=$localFileLock log=$logFile");
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
                $command = get_ffmpeg() . " -threads {$threads} -i {$fromFileLocationEscaped} -c:a libmp3lame -b:a 128k -ar 44100 -ac 2 {$toFileLocationEscaped}";
                break;
            case 1:
                $command = get_ffmpeg() . " -threads {$threads} -i {$fromFileLocationEscaped} -c:a libmp3lame -b:a 192k -ar 48000 -ac 2 {$toFileLocationEscaped}";
                break;
            case 2:
                $command = get_ffmpeg() . " -threads {$threads} -probesize 50M -analyzeduration 100M -i {$fromFileLocationEscaped} -c:a libmp3lame -b:a 128k -ar 44100 -ac 2 {$toFileLocationEscaped}";
                break;
            case 3:
                $uniqueID = uniqid('temp_audio_', true);
                $tempAudioFile = escapeshellarg("/tmp/{$uniqueID}.aac");
                $command = get_ffmpeg() . " -threads {$threads} -i {$fromFileLocationEscaped} -vn -acodec copy {$tempAudioFile}";
                exec($command, $output, $return);

                if ($return === 0) {
                    $command = get_ffmpeg() . " -threads {$threads} -i {$tempAudioFile} -c:a libmp3lame -b:a 128k -ar 44100 -ac 2 {$toFileLocationEscaped}";
                } else {
                    return false;
                }
                break;
            default:
                return false;
                break;
        }
    } else {
        switch ($try) {
            case 0:
                $command = get_ffmpeg() . " -threads {$threads} -i {$fromFileLocationEscaped} -c:v libx264 -preset veryfast -crf 23 -c:a aac -b:a 128k {$toFileLocationEscaped}";
                break;
            case 1:
                $command = get_ffmpeg() . " -threads {$threads} -i {$fromFileLocationEscaped} -c copy {$toFileLocationEscaped}";
                break;
            case 2:
                $command = get_ffmpeg() . " -threads {$threads} -allowed_extensions ALL -y -i {$fromFileLocationEscaped} -c:v copy -c:a copy -bsf:a aac_adtstoasc -strict -2 {$toFileLocationEscaped}";
                break;
            case 3:
                $command = get_ffmpeg() . " -threads {$threads} -y -i {$fromFileLocationEscaped} -c:v copy -c:a copy -bsf:a aac_adtstoasc -strict -2 {$toFileLocationEscaped}";
                break;
            default:
                return false;
                break;
        }
    }

    if (!empty($logFile)) {
        $progressFile = getConvertVideoFileWithFFMPEGProgressFilename($toFileLocation);
    } else {
        $progressFile = $logFile;
    }
    if (empty($progressFile)) {
        $progressFile = "{$toFileLocation}.log";
    }

    $command = removeUserAgentIfNotURL($command);

    if (!isCommandLineInterface()) {
        $command .= " > {$progressFile} 2>&1";
    }

    _session_write_close();
    _mysql_close();
    _error_log("convertVideoFileWithFFMPEG try[{$try}]: " . $command . ' ' . json_encode(debug_backtrace()));

    if (isCommandLineInterface()) {
        echo "convertVideoFileWithFFMPEG {$command} ";
    }
    exec($command, $output, $return);

    if (!empty($tempAudioFile)) {
        unlink($tempAudioFile);
    }

    $global['lastFFMPEG'] = array($command, $output, $return);

    _session_start();
    _mysql_connect();
    _error_log("convertVideoFileWithFFMPEG try[{$try}] output: " . json_encode($output));

    unlink($localFileLock);

    return ['return' => $return, 'output' => $output, 'command' => $command, 'fromFileLocation' => $fromFileLocation, 'toFileLocation' => $toFileLocation, 'progressFile' => $progressFile];
}


function cutVideoWithFFmpeg($inputFile, $startTimeInSeconds, $endTimeInSeconds, $outputFile, $aspectRatio)
{
    // Ensure start and end times are numeric
    $startTimeInSeconds = (int)$startTimeInSeconds;
    $endTimeInSeconds = (int)$endTimeInSeconds;

    // Define aspect ratio dimensions
    $aspectRatioDimensions = [
        Video::ASPECT_RATIO_ORIGINAL,
        Video::ASPECT_RATIO_SQUARE,
        Video::ASPECT_RATIO_VERTICAL,
        Video::ASPECT_RATIO_HORIZONTAL,
    ];

    // Validate aspect ratio parameter
    if (!in_array($aspectRatio, $aspectRatioDimensions)) {
        _error_log('cutVideoWithFFmpeg: Invalid aspect ratio parameter');
        return false;
    }

    make_path($outputFile);

    // Escape arguments to ensure command is safe to execute
    $escapedInputFile = escapeshellarg($inputFile);
    $escapedOutputFile = escapeshellarg($outputFile);
    $escapedStartTime = escapeshellarg($startTimeInSeconds);
    $escapedEndTime = escapeshellarg($endTimeInSeconds);

    if ($aspectRatio === Video::ASPECT_RATIO_ORIGINAL) {
        _error_log("cutAndAdaptVideoWithFFmpeg Original ratio");
        // Construct the FFmpeg command
        $cmd = get_ffmpeg() . " -ss {$escapedStartTime} -to {$escapedEndTime} -i {$escapedInputFile} -c:a copy {$escapedOutputFile}";
    } else {
        // Use ffprobe to get video dimensions
        $ffprobeCommand = get_ffprobe() . " -v error -select_streams v:0 -show_entries stream=width,height -of csv=s=x:p=0 {$inputFile}";
        $ffprobeCommand = removeUserAgentIfNotURL($ffprobeCommand);

        _error_log("cutAndAdaptVideoWithFFmpeg start shell_exec($ffprobeCommand)");
        $videoDimensions = shell_exec($ffprobeCommand);
        _error_log("cutAndAdaptVideoWithFFmpeg response ($videoDimensions)");
        list($width, $height) = explode('x', trim($videoDimensions));
        $width = intval($width);
        $height = intval($height);
        $cropParams = calculateCenterCrop($width, $height, $aspectRatio);

        // Calculate crop dimensions
        $cropDimension = "{$cropParams['newWidth']}:{$cropParams['newHeight']}:{$cropParams['x']}:{$cropParams['y']}";

        $escapedCropDimension = escapeshellarg($cropDimension);

        // Construct the FFmpeg command
        $cmd = get_ffmpeg() . " -ss {$escapedStartTime} -to {$escapedEndTime} -i {$escapedInputFile} -vf \"crop={$escapedCropDimension}\" -c:a copy {$escapedOutputFile}";
    }
    $cmd = removeUserAgentIfNotURL($cmd);
    // Execute the command
    _error_log('cutAndAdaptVideoWithFFmpeg start ' . $cmd);

    exec($cmd, $output, $returnVar);

    // Check if the command was executed successfully
    if ($returnVar === 0) {
        _error_log('cutAndAdaptVideoWithFFmpeg success ' . $outputFile);
        return true; // Command executed successfully
    } else {
        _error_log('cutAndAdaptVideoWithFFmpeg error ');
        return false; // Command failed
    }
}


function buildFFMPEGRemoteURL($actionParams)
{
    $obj = AVideoPlugin::getDataObjectIfEnabled('API');
    if (empty($obj) || empty($obj->standAloneFFMPEG)) {
        return false;
    }
    $url = "{$obj->standAloneFFMPEG}";
    $actionParams['time'] = time();
    $encryptedParams = encryptString(json_encode($actionParams));
    $url = addQueryStringParameter($url, 'APISecret', $obj->APISecret);
    $url = addQueryStringParameter($url, 'codeToExecEncrypted', $encryptedParams);
    return $url;
}

function execFFMPEGAsyncOrRemote($command, $keyword = null)
{
    $url = buildFFMPEGRemoteURL(['ffmpegCommand' => $command, 'keyword' => $keyword]);
    if ($url) {
        _error_log("execFFMPEGAsyncOrRemote: URL $command");
        _error_log("execFFMPEGAsyncOrRemote: URL $url");
        return url_get_contents($url);
    } else {
        _error_log("execFFMPEGAsyncOrRemote: Async $command");
        return execAsync($command, $keyword);
    }
}

function getFFMPEGRemoteLog($keyword)
{
    $url = buildFFMPEGRemoteURL(['log' => 1, 'keyword' => $keyword]);
    if ($url) {
        _error_log("getFFMPEGRemoteLog: URL $url");
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

function stopFFMPEGRemote($keyword)
{
    $url = buildFFMPEGRemoteURL(['stop' => 1, 'keyword' => $keyword]);
    if ($url) {
        _error_log("stopFFMPEGRemote: URL $url");
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

function testFFMPEGRemote()
{
    $url = buildFFMPEGRemoteURL(['test' => 1, 'microtime' => microtime(true)]);
    if ($url) {
        _error_log("testFFMPEGRemote: URL $url");
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

