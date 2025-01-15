<?php

function checkFileModified($filePath)
{
    // Check if the file exists
    if (file_exists($filePath)) {
        // Get the last modified time of the file
        $lastModifiedTime = filemtime($filePath);

        // Get the current time
        $currentTime = time();

        // Check if the file was modified at least 1 minute ago
        return ($currentTime - $lastModifiedTime);
    } else {
        return false;
    }
}

// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function file_upload_max_size()
{
    static $max_size = -1;

    if ($max_size < 0) {
        // Start with post_max_size.
        $max_size = parse_size(ini_get('post_max_size'));

        // If upload_max_size is less, then reduce. Except if upload_max_size is
        // zero, which indicates no limit.
        $upload_max = parse_size(ini_get('upload_max_filesize'));
        if ($upload_max > 0 && $upload_max < $max_size) {
            $max_size = $upload_max;
        }
    }
    return $max_size;
}

function getServerLimits()
{
    // Get PHP limits
    $limits = [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'max_input_time' => ini_get('max_input_time')
    ];

    // Check for Apache-specific limits if running under Apache
    if (function_exists('apache_get_version')) {
        $limits['apache_version'] = apache_get_version();
    }

    // Check if mod_reqtimeout is enabled
    if (file_exists('/etc/apache2/mods-enabled/reqtimeout.conf')) {
        $reqtimeout = file_get_contents('/etc/apache2/mods-enabled/reqtimeout.conf');
        preg_match_all('/RequestReadTimeout\s+(header=.*?,minrate=.*?|body=.*?,minrate=.*?)/', $reqtimeout, $matches);
        $limits['apache_reqtimeout'] = isset($matches[0]) ? $matches[0] : 'Not set';
    }

    return $limits;
}

function parse_size($size)
{
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
    $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
    if ($unit) {
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

function humanFileSize($size, $unit = "")
{
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


function get_max_file_size()
{
    return humanFileSize(file_upload_max_size());
}


function checkVideosDir()
{
    $dir = "../videos";
    if (file_exists($dir)) {
        return is_writable($dir);
    }
    return mkdir($dir);
}

function getVideosDir()
{
    global $isStandAlone, $global;
    if (empty($isStandAlone)) {
        return Video::getStoragePath();
    } else {
        return "{$global['systemRootPath']}videos/";
    }
}

function rrmdir($dir)
{
    //if(preg_match('/cache/i', $dir)){_error_log("rrmdir($dir) ". json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));exit;}

    $dir = str_replace(['//', '\\\\'], DIRECTORY_SEPARATOR, $dir);
    //_error_log('rrmdir: ' . $dir);
    if (empty($dir)) {
        _error_log('rrmdir: the dir was empty');
        return false;
    }
    global $global;
    $dir = fixPath($dir);
    $pattern = '/' . addcslashes($dir, DIRECTORY_SEPARATOR) . 'videos[\/\\\]?$/i';
    if ($dir == getVideosDir() || $dir == "{$global['systemRootPath']}videos" . DIRECTORY_SEPARATOR || preg_match($pattern, $dir)) {
        _error_log('rrmdir: A script ties to delete the videos Directory [' . $dir . '] ' . json_encode([$dir == getVideosDir(), $dir == "{$global['systemRootPath']}videos" . DIRECTORY_SEPARATOR, preg_match($pattern, $dir)]));
        return false;
    }
    rrmdirCommandLine($dir);
    if (is_dir($dir)) {
        //_error_log('rrmdir: The Directory was not deleted, trying again ' . $dir);
        $objects = @scandir($dir);
        if (!empty($objects)) {
            //_error_log('rrmdir: scandir ' . $dir . ' '. json_encode($objects));
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                        rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
        }
        if (preg_match('/(\/|^)videos(\/cache)?\/?$/i', $dir)) {
            _error_log('rrmdir: do not delete videos or cache folder ' . $dir);
            // do not delete videos or cache folder
            return false;
        }
        if (is_dir($dir)) {
            if (@rmdir($dir)) {
                return true;
            } elseif (is_dir($dir)) {
                _error_log('rrmdir: could not delete folder ' . $dir);
                return false;
            }
        }
    } else {
        //_error_log('rrmdir: The Directory does not exists '.$dir);
        return true;
    }
}


function make_path($path)
{
    $created = false;
    if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
        $path = pathinfo($path, PATHINFO_DIRNAME);
    }
    if (!is_dir($path)) {
        //if(preg_match('/getvideoinfo/i', $path)){var_dump(debug_backtrace());}
        $created = @mkdir($path, 0777, true);
        /*
          if (!$created) {
          _error_log('make_path: could not create the dir ' . json_encode($path) . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
          }
         */
    } else {
        $created = true;
    }

    if (preg_match('/cache/i', $path) || isCommandLineInterface()) {
        $mode = 0777;
    } else {
        $mode = 0755;
    }
    @chmod($path, $mode);
    return $created;
}

function local_get_contents($path)
{
    if (function_exists('fopen')) {
        $myfile = fopen($path, "r") or die("Unable to open file! [{$path}]");
        $text = fread($myfile, filesize($path));
        fclose($myfile);
        return $text;
    }
}

function try_get_contents_from_local($url)
{
    if (substr($url, 0, 1) === '/') {
        // it is not a URL
        return file_get_contents($url);
    }
    global $global;

    $parts = explode('/videos/', $url);
    if (!empty($parts[1])) {
        if (preg_match('/cache\//', $parts[1])) {
            $encoder = '';
        } else {
            $encoder = 'Encoder/';
        }
        $tryFile = "{$global['systemRootPath']}{$encoder}videos/{$parts[1]}";
        //_error_log("try_get_contents_from_local {$url} => {$tryFile}");
        if (file_exists($tryFile)) {
            return file_get_contents($tryFile);
        }
    }
    return false;
}

/**
 * A function that could get me the last N lines of a log file.
 * @param string $filepath
 * @param string $lines
 * @param string $adaptive
 * @return boolean
 */
function tail($filepath, $lines = 1, $adaptive = true, $returnArray = false)
{
    if (!function_exists('mb_strlen')) {
        $msg = "AVideoLog::ERROR you need to install the mb_strlen function to make it work, please the command 'sudo apt install php-mbstring'";
        if ($returnArray) {
            return [[$msg]];
        } else {
            return $msg;
        }
    }
    // Open file
    $f = @fopen($filepath, "rb");
    if ($f === false) {
        return false;
    }

    // Sets buffer size, according to the number of lines to retrieve.
    // This gives a performance boost when reading a few lines from the file.
    if (!$adaptive) {
        $buffer = 4096;
    } else {
        $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
    }

    // Jump to last character
    fseek($f, -1, SEEK_END);
    // Read it and adjust line number if necessary
    // (Otherwise the result would be wrong if file doesn't end with a blank line)
    if (fread($f, 1) !== "\n") {
        $lines -= 1;
    }

    // Start reading
    $output = '';
    $chunk = '';
    // While we would like more
    while (ftell($f) > 0 && $lines >= 0) {
        // Figure out how far back we should jump
        $seek = min(ftell($f), $buffer);
        // Do the jump (backwards, relative to where we are)
        fseek($f, -$seek, SEEK_CUR);
        // Read a chunk and prepend it to our output
        $output = ($chunk = fread($f, $seek)) . $output;
        // Jump back to where we started reading
        fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
        // Decrease our line counter
        $lines -= substr_count($chunk, "\n");
    }
    // While we have too many lines
    // (Because of buffer size we might have read too many)
    while ($lines++ < 0) {
        // Find first newline and remove all text before that
        $output = substr($output, strpos($output, "\n") + 1);
    }
    // Close file and return
    fclose($f);
    $output = trim($output);
    if ($returnArray) {
        $array = explode("\n", $output);
        $newArray = [];
        foreach ($array as $value) {
            $newArray[] = [$value];
        }
        return $newArray;
    } else {
        return $output;
    }
}

function getUsageFromFilename($filename, $dir = "")
{
    global $global;

    if (!empty($global['getUsageFromFilename'])) { // manually add this variable in your configuration.php file to not scan your video usage
        return 0;
    }

    if (empty($dir)) {
        $paths = Video::getPaths($filename);
        $dir = $paths['path'];
    }
    $dir = addLastSlash($dir);
    $totalSize = 0;
    //_error_log("getUsageFromFilename: start {$dir}{$filename} " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    //$files = glob("{$dir}{$filename}*");
    $paths = Video::getPaths($filename);

    if (is_dir($paths['path'])) {
        $files = [$paths['path']];
    } else {
        $files = globVideosDir($filename);
    }
    //var_dump($paths, $files, $filename);exit;
    _session_write_close();
    $filesProcessed = [];
    if (empty($files)) {
        _error_log("getUsageFromFilename: we did not find any file for {$dir}{$filename}, we will create a fake one " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        make_path($dir);
        file_put_contents("{$dir}{$filename}.notfound", time());
        $totalSize = 10;
    } else {
        foreach ($files as $f) {
            if (strpos($f, '.size.lock') !== false) {
                continue;
            }
            if (is_dir($f)) {
                $dirSize = getDirSize($f, true);
                //_error_log("getUsageFromFilename: is Dir dirSize={$dirSize} " . humanFileSize($dirSize) . " {$f}");
                $totalSize += $dirSize;
                $minDirSize = 4000000;
                $isEnabled = AVideoPlugin::isEnabledByName('YPTStorage');
                $isEnabledCDN = AVideoPlugin::getObjectDataIfEnabled('CDN');
                $isEnabledS3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
                $isEnabledB2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
                if (!empty($isEnabledCDN) && $isEnabledCDN->enable_storage) {
                    $v = Video::getVideoFromFileName($filename);
                    if (!empty($v)) {
                        $size = CDNStorage::getRemoteDirectorySize($v['id']);
                        //_error_log("getUsageFromFilename: CDNStorage found $size " . humanFileSize($size));
                        $totalSize += $size;
                    }
                }
                if ($dirSize < $minDirSize && $isEnabled) {
                    // probably the HLS file is hosted on the YPTStorage
                    $info = YPTStorage::getFileInfo($filename);
                    if (!empty($info->size)) {
                        //_error_log("getUsageFromFilename: found info on the YPTStorage " . print_r($info, true));
                        $totalSize += $info->size;
                    } else {
                        //_error_log("getUsageFromFilename: there is no info on the YPTStorage " . print_r($info, true));
                    }
                } elseif ($dirSize < $minDirSize && $isEnabledS3) {
                    // probably the HLS file is hosted on the S3
                    $size = $isEnabledS3->getFilesize($filename);
                    if (!empty($size)) {
                        //_error_log("getUsageFromFilename: found info on the AWS_S3 {$filename} {$size}");
                        $totalSize += $size;
                    } else {
                        //_error_log("getUsageFromFilename: there is no info on the AWS_S3  {$filename} {$size}");
                    }
                } elseif ($dirSize < $minDirSize && $isEnabledB2) {
                    // probably the HLS file is hosted on the S3
                    $size = $isEnabledB2->getFilesize($filename);
                    if (!empty($size)) {
                        _error_log("getUsageFromFilename: found info on the B2 {$filename} {$size}");
                        $totalSize += $size;
                    } else {
                        _error_log("getUsageFromFilename: there is no info on the B2  {$filename} {$size}");
                    }
                } else {
                    if (!($dirSize < $minDirSize)) {
                        //_error_log("getUsageFromFilename: does not have the size to process $dirSize < $minDirSize");
                    }
                    if (!$isEnabled) {
                        //_error_log("getUsageFromFilename: YPTStorage is disabled");
                    }
                    if (!$isEnabledCDN) {
                        //_error_log("getUsageFromFilename: CDN Storage is disabled");
                    }
                    if (!$isEnabledS3) {
                        //_error_log("getUsageFromFilename: S3 Storage is disabled");
                    }
                    if (!$isEnabledB2) {
                        //_error_log("getUsageFromFilename: B2 Storage is disabled");
                    }
                }
            } elseif (is_file($f)) {
                $filesize = filesize($f);
                if ($filesize < 20) { // that means it is a dummy file
                    $lockFile = $f . ".size.lock";
                    if (!file_exists($lockFile) || (time() - 600) > filemtime($lockFile)) {
                        file_put_contents($lockFile, time());
                        //_error_log("getUsageFromFilename: {$f} is Dummy file ({$filesize})");
                        $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
                        $bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
                        if (!empty($aws_s3)) {
                            //_error_log("getUsageFromFilename: Get from S3");
                            $filesize += $aws_s3->getFilesize($filename);
                        } elseif (!empty($bb_b2)) {
                            $filesize += $bb_b2->getFilesize($filename);
                        } else {
                            $urls = Video::getVideosPaths($filename, true);
                            //_error_log("getUsageFromFilename: Paths " . json_encode($urls));
                            if (!empty($urls["m3u8"]['url'])) {
                                $filesize += getUsageFromURL($urls["m3u8"]['url']);
                            }
                            if (!empty($urls['mp4'])) {
                                foreach ($urls['mp4'] as $mp4) {
                                    if (in_array($mp4, $filesProcessed)) {
                                        continue;
                                    }
                                    $filesProcessed[] = $mp4;
                                    $filesize += getUsageFromURL($mp4);
                                }
                            }
                            if (!empty($urls['webm'])) {
                                foreach ($urls['webm'] as $mp4) {
                                    if (in_array($mp4, $filesProcessed)) {
                                        continue;
                                    }
                                    $filesProcessed[] = $mp4;
                                    $filesize += getUsageFromURL($mp4);
                                }
                            }
                            if (!empty($urls["pdf"]['url'])) {
                                $filesize += getUsageFromURL($urls["pdf"]['url']);
                            }
                            if (!empty($urls["image"]['url'])) {
                                $filesize += getUsageFromURL($urls["image"]['url']);
                            }
                            if (!empty($urls["zip"]['url'])) {
                                $filesize += getUsageFromURL($urls["zip"]['url']);
                            }
                            if (!empty($urls["mp3"]['url'])) {
                                $filesize += getUsageFromURL($urls["mp3"]['url']);
                            }
                        }
                        unlink($lockFile);
                    }
                } else {
                    //_error_log("getUsageFromFilename: {$f} is File ({$filesize})");
                }
                $totalSize += $filesize;
            }
        }
    }
    return $totalSize;
}

/**
 * Returns the size of a file without downloading it, or -1 if the file
 * size could not be determined.
 *
 * @param $url - The location of the remote file to download. Cannot
 * be null or empty.
 *
 * @return int
 * return The size of the file referenced by $url, or false if the size
 * could not be determined.
 */
function getUsageFromURL($url)
{
    global $global;

    if (!empty($global['doNotGetUsageFromURL'])) { // manually add this variable in your configuration.php file to not scan your video usage
        return 0;
    }

    _error_log("getUsageFromURL: start ({$url})");
    // Assume failure.
    $result = false;

    $curl = curl_init($url);

    _error_log("getUsageFromURL: curl_init ");

    try {
        // Issue a HEAD request and follow any redirects.
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($curl, CURLOPT_USERAGENT, get_user_agent_string());
        $data = curl_exec($curl);
    } catch (Exception $exc) {
        echo $exc->getTraceAsString();
        _error_log("getUsageFromURL: ERROR " . $exc->getMessage());
        _error_log("getUsageFromURL: ERROR " . curl_errno($curl));
        _error_log("getUsageFromURL: ERROR " . curl_error($curl));
    }

    if ($data) {
        //_error_log("getUsageFromURL: response header " . $data);
        $content_length = "unknown";
        $status = "unknown";

        if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)) {
            $status = (int) $matches[1];
        }

        if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
            $content_length = (int) $matches[1];
        }

        // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
        if ($status == 200 || ($status > 300 && $status <= 308)) {
            $result = $content_length;
        }
    } else {
        _error_log("getUsageFromURL: ERROR no response data " . curl_error($curl));
    }

    curl_close($curl);
    return (int) $result;
}

function foldersize($path)
{
    $total_size = 0;
    $files = scandir($path);
    $cleanPath = rtrim($path, '/') . '/';

    foreach ($files as $t) {
        if ($t <> "." && $t <> "..") {
            $currentFile = $cleanPath . $t;
            if (is_dir($currentFile)) {
                $size = foldersize($currentFile);
                $total_size += $size;
            } else {
                $size = filesize($currentFile);
                $total_size += $size;
            }
        }
    }

    return $total_size;
}

function getDiskUsage()
{
    global $global;
    $dir = getVideosDir() . "";
    $obj = new stdClass();
    $obj->disk_free_space = disk_free_space($dir);
    $obj->disk_total_space = disk_total_space($dir);
    $obj->videos_dir = getDirSize($dir);
    $obj->disk_used = $obj->disk_total_space - $obj->disk_free_space;
    $obj->disk_used_by_other = $obj->disk_used - $obj->videos_dir;
    $obj->disk_free_space_human = humanFileSize($obj->disk_free_space);
    $obj->disk_total_space_human = humanFileSize($obj->disk_total_space);
    $obj->videos_dir_human = humanFileSize($obj->videos_dir);
    $obj->disk_used_human = humanFileSize($obj->disk_used);
    $obj->disk_used_by_other_human = humanFileSize($obj->disk_used_by_other);
    // percentage of disk used
    $obj->disk_used_percentage = sprintf('%.2f', ($obj->disk_used / $obj->disk_total_space) * 100);
    $obj->videos_dir_used_percentage = sprintf('%.2f', ($obj->videos_dir / $obj->disk_total_space) * 100);
    $obj->disk_free_space_percentage = sprintf('%.2f', ($obj->disk_free_space / $obj->disk_total_space) * 100);

    return $obj;
}

/**
 * Copy remote file over HTTP one small chunk at a time.
 *
 * @param $infile The full URL to the remote file
 * @param $outfile The path where to save the file
 */
function copyfile_chunked($infile, $outfile)
{
    $chunksize = 10 * (1024 * 1024); // 10 Megs

    /**
     * parse_url breaks a part a URL into it's parts, i.e. host, path,
     * query string, etc.
     */
    $parts = parse_url($infile);
    $i_handle = fsockopen($parts['host'], 80, $errstr, $errcode, 5);
    $o_handle = fopen($outfile, 'wb');

    if ($i_handle == false || $o_handle == false) {
        return false;
    }

    if (!empty($parts['query'])) {
        $parts['path'] .= '?' . $parts['query'];
    }

    /**
     * Send the request to the server for the file
     */
    $request = "GET {$parts['path']} HTTP/1.1\r\n";
    $request .= "Host: {$parts['host']}\r\n";
    $request .= "User-Agent: Mozilla/5.0\r\n";
    $request .= "Keep-Alive: 115\r\n";
    $request .= "Connection: keep-alive\r\n\r\n";
    fwrite($i_handle, $request);

    /**
     * Now read the headers from the remote server. We'll need
     * to get the content length.
     */
    $headers = [];
    while (!feof($i_handle)) {
        $line = fgets($i_handle);
        if ($line == "\r\n") {
            break;
        }
        $headers[] = $line;
    }

    /**
     * Look for the Content-Length header, and get the size
     * of the remote file.
     */
    $length = 0;
    foreach ($headers as $header) {
        if (stripos($header, 'Content-Length:') === 0) {
            $length = (int) str_replace('Content-Length: ', '', $header);
            break;
        }
    }

    /**
     * Start reading in the remote file, and writing it to the
     * local file one chunk at a time.
     */
    $cnt = 0;
    while (!feof($i_handle)) {
        $buf = '';
        $buf = fread($i_handle, $chunksize);
        $bytes = fwrite($o_handle, $buf);
        if ($bytes == false) {
            return false;
        }
        $cnt += $bytes;

        /**
         * We're done reading when we've reached the conent length
         */
        if ($cnt >= $length) {
            break;
        }
    }

    fclose($i_handle);
    fclose($o_handle);
    return $cnt;
}

function wgetLockFile($url)
{
    return getTmpDir("YPTWget") . md5($url) . ".lock";
}

function wgetLock($url)
{
    $file = wgetLockFile($url);
    return file_put_contents($file, time() . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function wgetRemoveLock($url)
{
    $filename = wgetLockFile($url);
    if (!file_exists($filename)) {
        return false;
    }
    return unlink($filename);
}

function getLockFile($name)
{
    return getTmpDir("YPTLockFile") . md5($name) . ".lock";
}

function setLock($name)
{
    $file = getLockFile($name);
    return file_put_contents($file, time());
}

function isLock($name, $timeout = 60)
{
    $file = getLockFile($name);
    if (file_exists($file)) {
        $time = intval(file_get_contents($file));
        if ($time + $timeout < time()) {
            return false;
        }
    }
}

function removeLock($name)
{
    $filename = getLockFile($name);
    if (!file_exists($filename)) {
        return false;
    }
    return unlink($filename);
}

function wgetIsLocked($url)
{
    $filename = wgetLockFile($url);
    if (!file_exists($filename)) {
        return false;
    }
    $time = intval(file_get_contents($filename));
    if (time() - $time > 36000) { // more then 10 hours
        unlink($filename);
        return false;
    }
    return $filename;
}

// due the some OS gives a fake is_writable response
function isWritable($dir)
{
    $dir = rtrim($dir, '/') . '/';
    $file = $dir . uniqid();
    $result = false;
    $time = time();
    if (@file_put_contents($file, $time)) {
        if ($fileTime = @file_get_contents($file)) {
            if ($fileTime == $time) {
                $result = true;
            }
        }
    }
    @unlink($file);
    return $result;
}

function _isWritable($dir)
{
    if (!isWritable($dir)) {
        return false;
    }
    $tmpFile = "{$dir}" . uniqid();
    $bytes = @file_put_contents($tmpFile, time());
    @unlink($tmpFile);
    return !empty($bytes);
}

function getTmpDir($subdir = "")
{
    global $global;
    if (empty($_SESSION['getTmpDir'])) {
        $_SESSION['getTmpDir'] = [];
    }
    if (empty($_SESSION['getTmpDir'][$subdir . "_"])) {
        if (empty($global['tmpDir'])) {
            // disabled it because command line and web were generating different caches
            //$tmpDir = sys_get_temp_dir();
            $tmpDir = '/var/www/tmp/';
            if (empty($tmpDir) || !_isWritable($tmpDir)) {
                $obj = AVideoPlugin::getDataObjectIfEnabled('Cache');
                if (!empty($obj)) {
                    $tmpDir = $obj->cacheDir;
                }
                if (empty($tmpDir) || !_isWritable($tmpDir)) {
                    $tmpDir = getVideosDir() . "cache" . DIRECTORY_SEPARATOR;
                }
            }
            $tmpDir = addLastSlash($tmpDir);
            $tmpDir = "{$tmpDir}{$subdir}";
        } else {
            $tmpDir = $global['tmpDir'];
        }
        $tmpDir = addLastSlash($tmpDir);
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }
        _session_start();
        $_SESSION['getTmpDir'][$subdir . "_"] = $tmpDir;
    } else {
        $tmpDir = $_SESSION['getTmpDir'][$subdir . "_"];
    }
    make_path($tmpDir);
    return $tmpDir;
}

function getTmpFile()
{
    return getTmpDir("tmpFiles") . uniqid();
}

function _file_put_contents($filename, $data, $flags = 0, $context = null)
{
    make_path($filename);
    if (!is_string($data)) {
        $data = _json_encode($data);
    }
    return file_put_contents($filename, $data, $flags, $context);
}

// just realize the readdir is a lot faster then glob
function _glob($dir, $pattern, $recreateCache = false)
{
    global $_glob;
    if (empty($dir)) {
        return [];
    }
    if (empty($_glob)) {
        $_glob = [];
    }
    $name = md5($dir . $pattern);
    if (!$recreateCache && isset($_glob[$name])) {
        //_error_log("_glob cache found: {$dir}[$pattern]");
        return $_glob[$name];
    }
    $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $array = [];
    if (is_dir($dir) && $handle = opendir($dir)) {
        $count = 0;
        while (false !== ($file_name = readdir($handle))) {
            if ($file_name == '.' || $file_name == '..') {
                continue;
            }
            //_error_log("_glob: {$dir}{$file_name} [$pattern]");
            //var_dump($pattern, $file_name, preg_match($pattern, $file_name));
            if (preg_match($pattern, $file_name)) {
                //_error_log("_glob Success: {$dir}{$file_name} [$pattern]");
                $array[] = "{$dir}{$file_name}";
            }
        }
        closedir($handle);
    }
    $_glob[$name] = $array;
    return $array;
}

function globVideosDir($filename, $filesOnly = false, $recreateCache = false)
{
    global $global;
    if (empty($filename)) {
        return [];
    }
    $cleanfilename = Video::getCleanFilenameFromFile($filename);
    $paths = Video::getPaths($filename);

    $dir = $paths['path'];

    if (is_dir($dir . $filename)) {
        $dir = $dir . $filename;
        $cleanfilename = '';
    }

    $pattern = "/({$cleanfilename}|index).*";
    if (!empty($filesOnly)) {
        $formats = getValidFormats();
        $pattern .= ".(" . implode("|", $formats) . ")$";
    }
    $pattern .= "/";
    //_error_log("_glob($dir, $pattern)");
    //var_dump($dir, $pattern);
    return _glob($dir, $pattern, $recreateCache);
}

function getIncludeFileContent($filePath, $varsArray = [], $setCacheName = false)
{
    global $global, $config, $advancedCustom, $advancedCustomUser, $t;

    if (empty($advancedCustom)) {
        $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
    }
    if (empty($advancedCustomUser)) {
        $advancedCustomUser = AVideoPlugin::getObjectData("CustomizeUser");
    }
    foreach ($varsArray as $key => $value) {
        eval("\${$key} = \$value;");
    }
    /*
      if(doesPHPVersioHasOBBug()){
      include $filePath;
      return '';
      }
     */

    _ob_start();
    if (!ob_get_level()) {
        _ob_start(true);
    }
    if (!ob_get_level()) {
        include $filePath;
        return '';
    }
    $__out = _ob_get_clean();
    if (!ob_get_level()) {
        echo $__out;
        include $filePath;
        return '';
    }
    //_ob_start();
    //$basename = basename($filePath);
    //$return = "<!-- {$basename} start -->";
    $return = '';
    if (!empty($setCacheName)) {
        $name = $filePath . '_' . User::getId() . '_' . getLanguage() . '_' . (isForKidsSet() ? 'kids' : '');
        if (is_string($setCacheName)) {
            $name .= $setCacheName;
        }
        //var_dump($name);exit;
        $return = ObjectYPT::getSessionCache($name, 0);
    }
    if (empty($return)) {
        if (file_exists($filePath)) {
            include $filePath;
            _ob_start();
            $return = _ob_get_clean();
            if (!empty($setCacheName)) {
                ObjectYPT::setSessionCache($name, $return);
            }
        } else {
            _error_log("getIncludeFileContent error $filePath");
        }
    }
    //$return .= "<!-- {$basename} end -->";
    echo $__out;
    return $return;
}

function mime_content_type_per_filename($filename)
{
    $mime_types = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'webp' => 'image/webp',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'mp4' => 'video/mp4',
        'avi' => 'video/avi',
        'mkv' => 'video/mkv',
        'wav' => 'audio/wav',
        'm4v' => 'video/mpeg',
        'webm' => 'video/webm',
        'wmv' => 'video/wmv',
        'mpg' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'f4v' => 'video/x-flv',
        'm4v' => 'video/m4v',
        'm4a' => 'video/quicktime',
        'm2p' => 'video/quicktime',
        'rm' => 'video/quicktime',
        'vob' => 'video/quicktime',
        'mkv' => 'video/quicktime',
        '3gp' => 'video/quicktime',
        'm3u8' => 'application/x-mpegURL',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    ];
    if (!empty($filename)) {
        if (filter_var($filename, FILTER_VALIDATE_URL) === false) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
        } else {
            $ext = pathinfo(parse_url($filename, PHP_URL_PATH), PATHINFO_EXTENSION);
        }

        if ($ext === 'mp4' || $ext === 'webm') {
            $securePlugin = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
            if (!empty($securePlugin)) {
                if (method_exists($securePlugin, "useEncoderWatrermarkFromFileName") && $securePlugin->useEncoderWatrermarkFromFileName($filename)) {
                    return "application/x-mpegURL";
                }
            }
        }

        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            _error_log("mime_content_type_per_filename($filename) not found, ext=[{$ext}]");
            $finfo = finfo_open(FILEINFO_MIME);
            if (!empty($finfo)) {
                $mimetype = finfo_file($finfo, $filename);
                finfo_close($finfo);
                return $mimetype;
            }
        }
    }
    return 'application/octet-stream';
}

if (!function_exists('mime_content_type')) {

    function mime_content_type($filename)
    {
        return mime_content_type_per_filename($filename);
    }
}


function isDummyFile($filePath)
{
    global $_isDummyFile;

    if (!isset($_isDummyFile)) {
        $_isDummyFile = [];
    }
    if (isset($_isDummyFile[$filePath])) {
        return $_isDummyFile[$filePath];
    }

    $return = false;

    if (file_exists($filePath)) {
        $fileSize = filesize($filePath);
        if ($fileSize > 5 && $fileSize < 20) {
            $return = true;
        } elseif ($fileSize < 100) {
            $return = preg_match("/Dummy File/i", file_get_contents($filePath));
        }
    }
    $_isDummyFile[$filePath] = $return;
    return $return;
}

function listFolderFiles($dir)
{
    if (empty($dir)) {
        return [];
    }
    if (!is_dir($dir)) {
        return [];
    }
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    $files = [];
    // prevent empty ordered elements
    if (count($ffs) >= 1) {
        foreach ($ffs as $ff) {
            $dir = rtrim($dir, DIRECTORY_SEPARATOR);
            $file = $dir . DIRECTORY_SEPARATOR . $ff;
            if (is_dir($file)) {
                $files[] = listFolderFiles($file);
            } else {
                $files[] = $file;
            }
        }
    }
    return $files;
}


function fixSystemPath()
{
    global $global;
    $global['systemRootPath'] = fixPath($global['systemRootPath']);
}

function fixPath($path, $addLastSlash = false)
{
    if (empty($path)) {
        return false;
    }
    if (isWindowsServer()) {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $path = str_replace('\\\\\\', DIRECTORY_SEPARATOR, $path);
    } else {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
    }
    if ($addLastSlash) {
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
    return $path;
}

function getVideosDirectoryUsageInfo()
{
    $dir = getVideosDir();
    // Verify if the directory exists
    if (!file_exists($dir)) {
        return "Directory does not exist.";
    }

    // Check if the directory is a symbolic link
    $isSymbolicLink = is_link($dir);

    // Get the real path of the directory
    $realPath = realpath($dir);

    // Get disk usage information using 'du' command
    $command = "du -s $realPath 2>&1"; // Removed 'h' to get the usage in bytes
    $usageOutput = shell_exec($command);
    $usageBytes = intval(preg_split('/\s+/', $usageOutput)[0]) * 1024; // Convert from KB to bytes

    // Get the total space and free space on the partition
    $totalSpace = disk_total_space($realPath);
    $freeSpace = disk_free_space($realPath);
    $usedSpace = $totalSpace - $freeSpace;
    $usedPercentage = ($usedSpace / $totalSpace) * 100;

    // Format the size values and percentage
    $totalSpaceFormatted = humanFileSize($totalSpace);
    $freeSpaceFormatted = humanFileSize($freeSpace);
    $usedSpaceFormatted = humanFileSize($usedSpace);
    $usedPercentageFormatted = sprintf('%.2f%%', $usedPercentage);

    return [
        'is_symbolic_link' => $isSymbolicLink,
        'real_path' => $realPath,
        'directory_usage' => trim($usageOutput),
        'directory_bytes_used' => $usageBytes,  // Total bytes used by the directory
        'total_space_bytes' => $totalSpace,     // Total bytes in the partition
        'free_space_bytes' => $freeSpace,       // Free bytes in the partition
        'used_space_bytes' => $usedSpace,       // Used bytes in the partition
        'directory_used' => humanFileSize($usageBytes),
        'total_space' => $totalSpaceFormatted,
        'free_space' => $freeSpaceFormatted,
        'used_space' => $usedSpaceFormatted,
        'used_percentage' => $usedPercentageFormatted,
        'used_percentage_number' => $usedPercentage
    ];
}

function findMP4File($folderPath)
{
    // Ensure the folder path ends with a slash
    $folderPath = addLastSlash($folderPath);

    // Open the folder and iterate over files
    if (is_dir($folderPath)) {
        $files = scandir($folderPath);
        foreach ($files as $file) {
            // Check if the file has a .mp4 extension
            if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
                // Return the absolute path to the first .mp4 file found
                return $folderPath . $file;
            }
        }
    }

    // Return false if no .mp4 file is found
    return false;
}


function findMP3File($folderPath)
{
    // Ensure the folder path ends with a slash
    $folderPath = addLastSlash($folderPath);

    // Open the folder and iterate over files
    if (is_dir($folderPath)) {
        $files = scandir($folderPath);
        foreach ($files as $file) {
            // Check if the file has a .mp4 extension
            if (pathinfo($file, PATHINFO_EXTENSION) === 'mp3') {
                // Return the absolute path to the first .mp4 file found
                return $folderPath . $file;
            }
        }
    }

    // Return false if no .mp4 file is found
    return false;
}


// Helper function to read JSON files
function readJsonFile($filePath)
{
    if (!file_exists($filePath)) {
        return null;
    }
    $content = file_get_contents($filePath);
    return json_decode($content, true);
}
