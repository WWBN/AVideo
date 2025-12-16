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
        //_error_log('get_ffmpeg $global[ffmpeg] detected ' . $global['ffmpeg']);
        $ffmpeg = "{$global['ffmpeg']}{$ffmpeg}";
    } else {
        //_error_log('get_ffmpeg default ' . $ffmpeg . $complement);
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
    $global['convertVideoToMP3FileIfNotExistsSteps'] = []; // Initialize an array to track steps
    $global['convertVideoToMP3FileIfNotExistsFileAlreadyExists'] = false; // Track if MP3 file already exists
    $global['convertVideoToMP3FilePath'] = ''; // Store the MP3 file path

    if (!empty($global['disableMP3'])) {
        $global['convertVideoToMP3FileIfNotExistsSteps'][] = '$global[disableMP3] isset';
        _error_log('convertVideoToMP3FileIfNotExists: $global[disableMP3] isset');
        return false;
    }

    $video = Video::getVideoLight($videos_id);
    if (empty($video)) {
        $global['convertVideoToMP3FileIfNotExistsSteps'][] = "videos_id=$videos_id not found";
        _error_log("convertVideoToMP3FileIfNotExists: videos_id=$videos_id not found");
        return false;
    }
    $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Video loaded successfully";

    $types = [Video::$videoTypeVideo, Video::$videoTypeAudio];
    if (!in_array($video['type'], $types)) {
        $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Invalid type: {$video['type']}";
        _error_log("convertVideoToMP3FileIfNotExists: invalid type {$video['type']}");
        return false;
    }
    $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Valid video type: {$video['type']}";

    $paths = Video::getPaths($video['filename']);
    $mp3HLSFile = "{$paths['path']}index.mp3";
    $mp3File = "{$paths['path']}{$video['filename']}.mp3";
    $global['convertVideoToMP3FilePath'] = $mp3File; // Set the global variable for MP3 file path
    $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Paths set: MP3 HLS File={$mp3HLSFile}, MP3 File={$mp3File}";

    if ($forceTry) {
        if (file_exists($mp3HLSFile)) {
            unlink($mp3HLSFile);
            $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Force delete MP3 HLS File";
        }
        if (file_exists($mp3File)) {
            unlink($mp3File);
            $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Force delete MP3 File";
        }
    }

    if (file_exists($mp3HLSFile) || file_exists($mp3File)) {
        $global['convertVideoToMP3FileIfNotExistsSteps'][] = "MP3 file already exists";
        $global['convertVideoToMP3FileIfNotExistsFileAlreadyExists'] = true; // Indicate that the file already exists
        return Video::getSourceFile($video['filename'], ".mp3", true); // Treat as successful since the file exists
    } else {
        $f = convertVideoFileWithFFMPEGIsLockedInfo($mp3File);
        if ($f['isUnlocked']) {
            $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Starting FFmpeg conversion";
            _error_log("convertVideoToMP3FileIfNotExists: start videos_id=$videos_id try=$forceTry ");
            $sources = getVideosURLOnly($video['filename'], false);

            if (!empty($sources)) {
                $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Sources found";
                $source = !empty($sources['m3u8']) ? $sources['m3u8'] : end($sources);
                $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Using source URL: {$source['url']}";
                convertVideoFileWithFFMPEG($source['url'], $mp3File, '', $forceTry);

                if (file_exists($mp3File)) {
                    $global['convertVideoToMP3FileIfNotExistsSteps'][] = "MP3 file successfully created";
                    return Video::getSourceFile($video['filename'], ".mp3", true); // Conversion successful
                } else {
                    $global['convertVideoToMP3FileIfNotExistsSteps'][] = "MP3 file creation failed: File does not exist";
                    _error_log("convertVideoToMP3FileIfNotExists: file not exists {$mp3File}");
                }
            } else {
                $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Sources not found";
                _error_log("convertVideoToMP3FileIfNotExists: sources not found");
            }
        } else {
            $global['convertVideoToMP3FileIfNotExistsSteps'][] = "Conversion is locked";
            _error_log("convertVideoToMP3FileIfNotExists: is locked");
        }
        return false; // Conversion failed
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
            _error_log("m3u8ToMP4: Another process is already running. Lock file age: {$lockFileAge} seconds. [$lockFile]");
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


/**
 * Convert M3U8 to MP4 using remote FFMPEG
 * 
 * @param string $input Input M3U8 file path
 * @param string $callback Optional callback in JSON format. Use buildSecureFFMPEGCallback() to create.
 *                         Format: {"action": "actionName", "params": {...}}
 *                         Allowed actions: 'updateVideoStatus', 'triggerPluginHook', 'updateVideoMetadata', 'logEvent'
 * @return array Result with error status and message
 */
function m3u8ToMP4RemoteFFMpeg($input, $callback)
{
    $videosDir = getVideosDir();
    $outputfilename = str_replace($videosDir, "", $input);
    $parts = explode("/", $outputfilename);
    $resolution = Video::getResolutionFromFilename($input);
    $video_filename = $parts[count($parts) - 2];

    $outputfilename = "index.mp4";
    $outputpathDir = "{$videosDir}{$video_filename}/";

    make_path($outputpathDir);
    $outputpath = "{$outputpathDir}{$outputfilename}";
    $msg = '';
    $error = true;

    if (empty($outputfilename)) {
        $msg = "downloadHLS: empty outputfilename {$outputfilename}";
        _error_log($msg);
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
        $return = convertVideoFileWithFFMPEGAsyncOrRemote($filepath, $outputpath, 'm3u8ToMP4RemoteFFMpeg_'.md5($input), $callback);

        if (empty($return)) {
            $msg3 = "downloadHLS: ERROR 2 ";
            $finalMsg = $msg . PHP_EOL . $msg3;
            _error_log($msg3);
            return ['error' => $error,
                'msg' => $finalMsg,
                'path' => $outputpath,
                'filename' => $outputfilename
            ];
        } else {
            return [
                'error' => false,
                'msg' => '',
                'return' => $return,
                'path' => $outputpath,
                'filename' => $outputfilename
            ];
        }
    } else {
        $msg = "downloadHLS: outputpath already exists ({$outputpath})";
        _error_log($msg);
    }

    $error = false;
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

function buildFFMPEGCommand($fromFileLocation, $toFileLocation, $try = 0, $cpuUsagePercentage = 80)
{
    global $advancedCustom;

    // Dynamically get the number of CPU cores
    $threads = 1; // Default to 1 thread
    if (function_exists('shell_exec')) {
        $cpuCores = (int)shell_exec('nproc 2>/dev/null'); // Linux
        if (!$cpuCores) {
            $cpuCores = (int)shell_exec('sysctl -n hw.ncpu 2>/dev/null'); // macOS
        }

        if ($cpuCores > 0) {
            // Calculate the number of threads based on the percentage
            $threads = max(1, (int)($cpuCores * ($cpuUsagePercentage / 100)));
        } else {
            _error_log("buildFFMPEGCommand: Unable to detect CPU cores. Defaulting to 1 thread.");
        }
    }

    $fromFileLocationEscaped = escapeshellarg($fromFileLocation);
    $toFileLocationEscaped = escapeshellarg($toFileLocation);
    $format = pathinfo($toFileLocation, PATHINFO_EXTENSION);

    // Generate the FFmpeg command based on format and try count
    if ($format === 'mp3') {
        switch ($try) {
            case 0:
                $command = get_ffmpeg() . " -threads {$threads} -i {$fromFileLocationEscaped} -c:a libmp3lame -b:a 128k -ar 44100 -ac 2 {$toFileLocationEscaped}";
                break;
            case 1:
                $command = get_ffmpeg() . " -threads {$threads} -i {$fromFileLocationEscaped} -c:a libmp3lame -b:a 192k -ar 48000 -ac 2 {$toFileLocationEscaped}";
                break;
            default:
                return false;
        }
    } else {
        switch ($try) {
            case 0:
                $command = get_ffmpeg() . " -threads {$threads} -i {$fromFileLocationEscaped} {$advancedCustom->ffmpegParameters} {$toFileLocationEscaped}";
                break;
            case 1:
                $command = get_ffmpeg() . " -threads {$threads} -i {$fromFileLocationEscaped} -c copy {$toFileLocationEscaped}";
                break;
            default:
                return false;
        }
    }

    return $command;
}

function convertVideoFileWithFFMPEG($fromFileLocation, $toFileLocation, $logFile = '', $try = 0)
{
    $command = buildFFMPEGCommand($fromFileLocation, $toFileLocation, $try);

    if (!$command) {
        _error_log("convertVideoFileWithFFMPEG: Failed to build command");
        return false;
    }

    if(!empty($logFile)){
        $command .= " > {$logFile} 2>&1";
    }

    _error_log("convertVideoFileWithFFMPEG: Command start $command");

    exec($command, $output, $return);

    _error_log("convertVideoFileWithFFMPEG: Command executed with return code {$return}");
    return ['return' => $return, 'output' => $output, 'command' => $command, 'toFileLocation' => $toFileLocation];
}

/**
 * Convert video file with FFMPEG (async or remote)
 * 
 * @param string $fromFileLocation Source file path
 * @param string $toFileLocation Destination file path
 * @param string $keyword Process keyword
 * @param string $callback Optional callback in JSON format. Use buildSecureFFMPEGCallback() to create.
 *                         Format: {"action": "actionName", "params": {...}}
 *                         Allowed actions: 'updateVideoStatus', 'triggerPluginHook', 'updateVideoMetadata', 'logEvent'
 * @return mixed Result of execution
 */
function convertVideoFileWithFFMPEGAsyncOrRemote($fromFileLocation, $toFileLocation, $keyword, $callback = '')
{
    $command = buildFFMPEGCommand($fromFileLocation, $toFileLocation, 0);

    if (!$command) {
        _error_log("convertVideoFileWithFFMPEGAsyncOrRemote: Failed to build command");
        return false;
    }

    $result = execFFMPEGAsyncOrRemote($command, $keyword, $callback);
    _error_log("convertVideoFileWithFFMPEGAsyncOrRemote: Command executed remotely or asynchronously");
    return $result;
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


/**
 * Build a secure callback for notify.ffmpeg.json.php
 * 
 * The callback must be in JSON format: {"action": "actionName", "params": {...}}
 * Allowed actions: 'updateVideoStatus', 'triggerPluginHook', 'updateVideoMetadata', 'logEvent'
 * 
 * @param string $action The action name (must be whitelisted)
 * @param array $params Parameters for the action
 * @return string JSON-encoded callback string, or empty string if invalid
 */
function buildSecureFFMPEGCallback($action, $params = []) {
    $allowedActions = ['updateVideoStatus', 'triggerPluginHook', 'updateVideoMetadata', 'logEvent'];
    
    if (empty($action) || !in_array($action, $allowedActions)) {
        if (function_exists('_error_log')) {
            _error_log("buildSecureFFMPEGCallback: Invalid or non-whitelisted action: $action");
        }
        return '';
    }
    
    if (!is_array($params)) {
        if (function_exists('_error_log')) {
            _error_log("buildSecureFFMPEGCallback: Params must be an array");
        }
        return '';
    }
    
    return json_encode([
        'action' => $action,
        'params' => $params
    ]);
}

/**
 * Get video by ID for callback handlers
 * 
 * @param int $videos_id Video ID
 * @return Video|null Video object if found, null otherwise
 */
function getVideoByIdForCallback($videos_id) {
    $videos_id = intval($videos_id);
    $video = new Video("", "", $videos_id);
    return $video->getUsers_id() ? $video : null;
}

/**
 * Validate required parameters in callback params
 * 
 * @param array $params Parameters to validate
 * @param array $required Required parameter names
 * @return array|null Error array if validation fails, null if valid
 */
function validateCallbackParams($params, $required) {
    foreach ($required as $param) {
        if (!isset($params[$param])) {
            return ['error' => "Missing required parameter: $param"];
        }
    }
    return null;
}

/**
 * Sanitize alphanumeric value for callback parameters
 * 
 * @param string $value Value to sanitize
 * @return string Sanitized value (alphanumeric and underscore only)
 */
function sanitizeAlphanumericForCallback($value) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', $value);
}

/**
 * Handle updateVideoStatus callback action
 * 
 * @param array $params Callback parameters
 * @param array $notify Notification data
 * @return array Result array
 */
function handleCallbackUpdateVideoStatus($params, $notify) {
    $error = validateCallbackParams($params, ['videos_id', 'status']);
    if ($error) return $error;

    $video = getVideoByIdForCallback($params['videos_id']);
    if (!$video) return ['error' => 'Video not found'];

    $status = sanitizeAlphanumericForCallback($params['status']);
    $video->setStatus($status);
    return ['success' => true, 'videos_id' => intval($params['videos_id']), 'status' => $status];
}

/**
 * Handle triggerPluginHook callback action
 * 
 * @param array $params Callback parameters
 * @param array $notify Notification data
 * @return array Result array
 */
function handleCallbackTriggerPluginHook($params, $notify) {
    $error = validateCallbackParams($params, ['hook', 'videos_id']);
    if ($error) return $error;

    $hook = sanitizeAlphanumericForCallback($params['hook']);
    $videos_id = intval($params['videos_id']);

    $allowedHooks = ['onNewVideo', 'onVideoProcessed'];
    if (!in_array($hook, $allowedHooks)) {
        return ['error' => 'Hook not allowed'];
    }

    if ($hook === 'onNewVideo') {
        AVideoPlugin::onNewVideo($videos_id);
    }
    return ['success' => true, 'hook' => $hook, 'videos_id' => $videos_id];
}

/**
 * Handle updateVideoMetadata callback action
 * 
 * @param array $params Callback parameters
 * @param array $notify Notification data
 * @return array Result array
 */
function handleCallbackUpdateVideoMetadata($params, $notify) {
    $error = validateCallbackParams($params, ['videos_id']);
    if ($error) return $error;

    $video = getVideoByIdForCallback($params['videos_id']);
    if (!$video) return ['error' => 'Video not found'];

    $updates = [];
    if (isset($params['duration']) && is_numeric($params['duration'])) {
        $video->setDuration(intval($params['duration']));
        $updates['duration'] = intval($params['duration']);
    }
    if (isset($params['resolution']) && preg_match('/^\d+x\d+$/', $params['resolution'])) {
        $updates['resolution'] = $params['resolution'];
    }

    return ['success' => true, 'updates' => $updates];
}

/**
 * Handle logEvent callback action
 * 
 * @param array $params Callback parameters
 * @param array $notify Notification data
 * @return array Result array
 */
function handleCallbackLogEvent($params, $notify) {
    $message = isset($params['message']) ? substr($params['message'], 0, 500) : 'Callback executed';
    _error_log("notify.ffmpeg callback log: " . $message);
    return ['success' => true, 'logged' => $message];
}

/**
 * Get FFMPEG callback handlers
 * 
 * @return array Array of action handlers mapping action names to handler functions
 */
function getFFMPEGCallbackHandlers() {
    return [
        'updateVideoStatus' => 'handleCallbackUpdateVideoStatus',
        'triggerPluginHook' => 'handleCallbackTriggerPluginHook',
        'updateVideoMetadata' => 'handleCallbackUpdateVideoMetadata',
        'logEvent' => 'handleCallbackLogEvent'
    ];
}

/**
 * Process FFMPEG callback securely
 * 
 * @param string $callback Decrypted callback string (JSON format)
 * @param array $notify Notification data
 * @return array|null Callback result or null if invalid
 */
function processFFMPEGCallback($callback, $notify) {
    _error_log("notify.ffmpeg: callback requested: $callback");

    $callbackData = json_decode($callback, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($callbackData)) {
        _error_log("notify.ffmpeg: SECURITY - invalid callback format: $callback");
        return ['error' => 'Invalid callback format'];
    }

    $action = $callbackData['action'] ?? '';
    $params = is_array($callbackData['params'] ?? null) ? $callbackData['params'] : [];

    if (empty($action)) {
        _error_log("notify.ffmpeg: SECURITY - missing action in callback");
        return ['error' => 'Missing action'];
    }

    $allowedActions = getFFMPEGCallbackHandlers();
    if (!isset($allowedActions[$action])) {
        _error_log("notify.ffmpeg: SECURITY - blocked non-whitelisted action: $action");
        return ['error' => 'Action not allowed'];
    }

    $handlerName = $allowedActions[$action];
    if (!function_exists($handlerName)) {
        _error_log("notify.ffmpeg: ERROR - handler function '$handlerName' not found");
        return ['error' => 'Handler function not found'];
    }

    try {
        $result = $handlerName($params, $notify);
        _error_log("notify.ffmpeg: executed action '$action': " . json_encode($result));
        return $result;
    } catch (Exception $e) {
        _error_log("notify.ffmpeg: error executing action '$action': " . $e->getMessage());
        return ['error' => 'Action execution failed: ' . $e->getMessage()];
    }
}

function buildFFMPEGRemoteURL($actionParams, $callback='', $standAloneFFMPEG='')
{
    $obj = AVideoPlugin::getDataObjectIfEnabled('API');
    if (empty($obj) || empty($obj->standAloneFFMPEG)) {
        return false;
    }
    if(!empty($standAloneFFMPEG)){
        $url = $standAloneFFMPEG;
    }else{
        $url = "{$obj->standAloneFFMPEG}";
    }
    $actionParams['time'] = time();
    $actionParams['notifyCode'] = encryptString(time());
    
    // Callback must be in JSON format: {"action": "actionName", "params": {...}}
    // Use buildSecureFFMPEGCallback() helper function to create valid callbacks
    // Empty string is allowed (no callback will be executed)
    $actionParams['callback'] = encryptString($callback);
    $encryptedParams = encryptString(json_encode($actionParams));
    $url = addQueryStringParameter($url, 'APISecret', $obj->APISecret);
    $url = addQueryStringParameter($url, 'codeToExecEncrypted', $encryptedParams);
    return $url;
}

function execFFMPEGAsyncOrRemote($command, $keyword, $callback='', $standAloneFFMPEG='')
{
    $url = buildFFMPEGRemoteURL(['ffmpegCommand' => $command, 'keyword' => $keyword], $callback, $standAloneFFMPEG);
    if ($url) {
        _error_log("execFFMPEGAsyncOrRemote: URL $command");
        _error_log("execFFMPEGAsyncOrRemote: URL $url");
        return url_get_contents($url);
    } else {
        _error_log("execFFMPEGAsyncOrRemote: Async $command");
        return execAsync($command, $keyword);
    }
}

function getFFMPEGRemoteLog($keyword, $standAloneFFMPEG='')
{
    $url = buildFFMPEGRemoteURL(['log' => 1, 'keyword' => $keyword], '', $standAloneFFMPEG);
    //var_dump($url);
    if ($url) {
        _error_log("getFFMPEGRemoteLog: URL $url " . json_encode(debug_backtrace()));
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

function stopFFMPEGRemote($keyword, $standAloneFFMPEG='')
{
    $url = buildFFMPEGRemoteURL(['stop' => 1, 'keyword' => $keyword], '', $standAloneFFMPEG);
    if ($url) {
        _error_log("stopFFMPEGRemote: URL $url");
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

function testFFMPEGRemote($standAloneFFMPEG='')
{
    $url = buildFFMPEGRemoteURL(['test' => 1, 'microtime' => microtime(true)], '', $standAloneFFMPEG);
    if ($url) {
        _error_log("testFFMPEGRemote: URL $url");
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

function listFFMPEGRemote($keyword = '', $standAloneFFMPEG='')
{
    $url = buildFFMPEGRemoteURL(['list' => 1, 'keyword' => $keyword, 'microtime' => microtime(true)], '', $standAloneFFMPEG);
    if ($url) {
        _error_log("listFFMPEGRemote: URL $url");
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

function killFFMPEGRemote($pid, $standAloneFFMPEG='')
{
    $url = buildFFMPEGRemoteURL(['kill' => $pid, 'microtime' => microtime(true)], '', $standAloneFFMPEG);
    if ($url) {
        _error_log("killFFMPEGRemote: URL $url");
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

function isKeywordRunningFFMPEGRemote($keyword, $standAloneFFMPEG='')
{
    $url = buildFFMPEGRemoteURL(['isKeywordRunning' => $keyword, 'microtime' => microtime(true)]);
    if ($url) {
        _error_log("isKeywordRunningFFMPEGRemote: URL $url");
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

function deleteFolderFFMPEGRemote($videoFilename, $standAloneFFMPEG='')
{
    $url = buildFFMPEGRemoteURL(['deleteFolder' => $videoFilename], '', $standAloneFFMPEG);
    if ($url) {
        _error_log("deleteFolderFFMPEGRemote: URL $url");
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

function deleteFileFFMPEGRemote($filePath, $standAloneFFMPEG='')
{
    $url = buildFFMPEGRemoteURL(['deleteFile' => $filePath], '', $standAloneFFMPEG);
    if ($url) {
        _error_log("deleteFileFFMPEGRemote: URL $url");
        return json_decode(url_get_contents($url));
    } else {
        return false;
    }
}

/**
 * Build local file path for video processing
 * 
 * @param array $global Global variables array
 * @param array $notify Notification data with avideoRelativePath
 * @return string Full local file path
 */
function buildLocalPathForNotify($global, $notify) {
    return "{$global['systemRootPath']}{$notify['avideoRelativePath']}";
}

/**
 * Build remote URL for video download from standAlone FFMPEG
 * 
 * @param string $standAloneFFMPEG Standalone FFMPEG URL
 * @param array $notify Notification data with avideoRelativePath
 * @return string Constructed remote URL
 */
function buildRemoteUrlForNotify($standAloneFFMPEG, $notify) {
    $url = str_replace('plugin/API/standAlone/ffmpeg.json.php', '', $standAloneFFMPEG);
    return "{$url}{$notify['avideoRelativePath']}";
}

/**
 * Create response template for notify.ffmpeg.json.php
 * 
 * @param array $notify Notification data
 * @return array Response template
 */
function createNotifyResponseTemplate($notify) {
    return [
        'error' => true,
        'avideoPath' => $notify['avideoPath'] ?? null,
        'format' => null,
        'standAloneFFMPEG' => null,
        'constructedURL' => null,
        'contentLength' => null,
        'bytesWritten' => null,
        'filePath' => null,
        'deleteStatus' => null,
    ];
}

/**
 * Process video file download and save for notify.ffmpeg.json.php
 * 
 * @param array $notify Notification data
 * @return array Response array with processing results
 */
function processNotifyVideoFile($notify) {
    global $global;
    
    $response = createNotifyResponseTemplate($notify);

    if (empty($notify['avideoPath'])) {
        _error_log("notify.ffmpeg: No avideoPath provided");
        return $response;
    }

    $format = pathinfo($notify['avideoPath'], PATHINFO_EXTENSION);
    $response['format'] = $format;

    if (!in_array($format, ['mp4', 'mp3'])) {
        _error_log("notify.ffmpeg: Unsupported format: $format");
        return $response;
    }

    $obj = AVideoPlugin::getDataObjectIfEnabled('API');
    $response['standAloneFFMPEG'] = $obj->standAloneFFMPEG ?? null;

    $localPath = buildLocalPathForNotify($global, $notify);
    if (file_exists($localPath)) {
        _error_log("notify.ffmpeg: Local file exists, skipping download");
        $response['error'] = false;
        return $response;
    }

    if (empty($obj) || empty($obj->standAloneFFMPEG)) {
        _error_log("notify.ffmpeg: API Plugin or standAloneFFMPEG not configured");
        return $response;
    }

    $url = buildRemoteUrlForNotify($obj->standAloneFFMPEG, $notify);
    $response['constructedURL'] = $url;

    $content = url_get_contents($url);
    if ($content === false || empty($content)) {
        _error_log("notify.ffmpeg: Failed to fetch or empty content from: $url");
        return $response;
    }

    $response['contentLength'] = strlen($content);
    $filePath = buildLocalPathForNotify($global, $notify);
    $bytes = file_put_contents($filePath, $content);

    if ($bytes === false) {
        _error_log("notify.ffmpeg: Failed to save file: $filePath");
        return $response;
    }

    $response['bytesWritten'] = $bytes;
    $response['filePath'] = $filePath;
    $response['error'] = false;
    $response['deleteStatus'] = deleteFolderFFMPEGRemote($notify['avideoFilename'] ?? '');

    return $response;
}

function addKeywordToFFmpegCommand(string $command, string $keyword): string
{
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

function getVideoInfo($filePath)
{
    $cmd = get_ffprobe() . " -v error -select_streams v:0 -show_entries stream=width,height,bit_rate -of json " . escapeshellarg($filePath);
    $cmd = removeUserAgentIfNotURL($cmd);
    $output = shell_exec($cmd);
    $json = json_decode($output, true);

    $info = [
        'width' => null,
        'height' => null,
        'bitrate' => null
    ];

    if (!empty($json['streams'][0])) {
        $stream = $json['streams'][0];
        $info['width'] = $stream['width'] ?? null;
        $info['height'] = $stream['height'] ?? null;
        $info['bitrate'] = isset($stream['bit_rate']) ? intval($stream['bit_rate']) : null;
    }

    return $info;
}
