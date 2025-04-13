<?php

if (!class_exists('FtpClient')) {
    require_once $global['systemRootPath'] . 'plugin/CDN/FtpClient/FtpClient.php';
    require_once $global['systemRootPath'] . 'plugin/CDN/FtpClient/FtpWrapper.php';
    require_once $global['systemRootPath'] . 'plugin/CDN/FtpClient/FtpException.php';
}

class CDNStorage
{

    public static $allowedFiles = ['mp4', 'webm', 'mp3', 'm3u8', 'ts', 'pdf', 'zip'];

    private function getClient($try = 0)
    {
        return self::getStorageClient();
    }

    public static function getStorageClient($try = 0)
    {
        $obj = AVideoPlugin::getDataObject('CDN');
        if (empty($obj->storage_hostname)) {
            //var_dump(debug_backtrace());
            die('CDNStorage storage_hostname is empty ');
        }
        $CDNstorage = new \FtpClient\FtpClient();
        try {
            $CDNstorage->connect($obj->storage_hostname);
            $CDNstorage->login($obj->storage_username, $obj->storage_password);
            $CDNstorage->pasv(true);
        } catch (Exception $exc) {
            _error_log("FTP:getClient fail try={$try} ($obj->storage_hostname) ($obj->storage_username), ($obj->storage_password) " . $exc->getMessage());
            $try++;
            if ($try < 5) {
                sleep($try);
                return self::getStorageClient($try);
            } else if ($try == 5 && isCommandLineInterface()) {
                sleep(30);
                return self::getStorageClient($try);
            } else {
                die('CDNStorage FTP Error ' . $exc->getMessage());
            }
        }
        _error_log("FTP:getClient finish");
        return $CDNstorage;
    }

    public function xsendfilePreVideoPlay()
    {
        global $global;

        $path_parts = pathinfo($_GET['file']);

        $filename = Video::getCleanFilenameFromFile($path_parts['filename']);

        if (in_array(strtolower($path_parts['extension']), CDNStorage::$allowedFiles)) {
            $paths = Video::getPaths($_GET['file']);
            $localFile = $paths['path'];
            if (!file_exists($localFile) || filesize($localFile) < 1024) {
                $url = self::getURL($path_parts["basename"]);
                header("Location: {$url}");
                exit;
            }
        }
    }

    public static function getFilesListBoth($videos_id)
    {
        global $_getFilesListBoth;
        if (!isset($_getFilesListBoth)) {
            $_getFilesListBoth = [];
        }
        if (!empty($_getFilesListBoth[$videos_id])) {
            return $_getFilesListBoth[$videos_id];
        }
        $remoteList = self::getFilesListRemote($videos_id);
        $localList = self::getFilesListLocal($videos_id, false);

        $searchThis = $localList;
        $compareThis = $remoteList;
        $searchingLocal = true;
        $files = [];
        foreach ($localList as $key => $value) {
            $isLocal = true;

            if (@$localList[$key]['local_filesize'] < @$remoteList[$key]['remote_filesize']) {
                $isLocal = false;
            }

            $files[$key] = ['isLocal' => $isLocal, 'local' => @$localList[$key], 'remote' => @$remoteList[$key]];
            unset($remoteList[$key]);
        }
        foreach ($remoteList as $key => $value) {
            $isLocal = true;

            if (
                @$localList[$key]['local_filesize'] <
                @$remoteList[$key]['remote_filesize']
            ) {
                $isLocal = false;
            }

            $files[$key] = ['isLocal' => $isLocal, 'local' => @$localList[$key], 'remote' => @$remoteList[$key]];
            unset($localList[$key]);
        }

        $_getFilesListBoth[$videos_id] = $files;
        return $files;
    }

    public static function getPZ()
    {
        $obj = AVideoPlugin::getDataObject('CDN');
        return addLastSlash($obj->storage_username . '.cdn.ypt.me');
    }

    public static function getFilesListRemote($videos_id, $client = null)
    {
        global $global, $_getFilesListRemote;
        if (empty($videos_id)) {
            return [];
        }
        if (!isset($_getFilesListRemote)) {
            $_getFilesListRemote = [];
        }
        if (!empty($_getFilesListRemote[$videos_id])) {
            return $_getFilesListRemote[$videos_id];
        }
        $video = Video::getVideoLight($videos_id);

        if (empty($video)) {
            return [];
        }

        //$paths = Video::getPaths($video['filename']);
        if (empty($client)) {
            $client = self::getStorageClient();
        }
        $dir = self::filenameToRemotePath($video['filename']);
        try {
            if (!$client->isDir($dir)) {
                return [];
            }
        } catch (Exception $exc) {
            _error_log("CDNStorage::getFilesListRemote ({$dir}) " . $exc->getTraceAsString());
        }

        $obj = AVideoPlugin::getDataObject('CDN');
        $pz = self::getPZ();
        try {
            $list = $client->rawlist($video['filename'], true);
        } catch (Exception $exc) {
            $list = [];
        }
        //var_dump($list);exit;
        $files = [];
        foreach ($list as $key => $value) {
            $parts1 = explode('#', $key);
            if ($parts1[0] == 'directory') {
                continue;
            }

            preg_match('/ ([0-9]+) [a-zA-z]+ [0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}/', $value, $matches);

            $remote_filesize = empty($matches[1]) ? 0 : $matches[1];
            $relative = $parts1[1];
            $local_path = "{$global['systemRootPath']}videos/{$relative}";
            $local_filesize = @filesize($local_path);
            $remote_path = self::filenameToRemotePath($relative);
            $path_parts = pathinfo($local_path);
            $extension = $path_parts['extension'];

            $file = [
                'extension' => $path_parts['extension'],
                'videos_id' => $videos_id,
                'local_path' => $local_path,
                'remote_path' => $remote_path,
                'local_url' => "{$global['webSiteRootURL']}videos/{$relative}",
                'remote_url' => "https://{$pz}{$relative}",
                'relative' => $relative,
                'local_filesize' => $local_filesize,
                'remote_filesize' => $remote_filesize,
                'video' => $video,
            ];

            $files[$relative] = $file;
        }
        $_getFilesListRemote[$videos_id] = $files;
        return $files;
    }

    public static function getRemoteDirectorySize($videos_id, $client = null)
    {
        $list = self::getFilesListRemote($videos_id, $client);
        $total = 0;
        foreach ($list as $value) {
            $total += $value['remote_filesize'];
        }
        return $total;
    }

    public static function getFilesListInfo($local_path, $storage_pullzone, $videos_id, $skipDummyFiles = true)
    {
        global $global;
        if ($skipDummyFiles && is_string($local_path) && filesize($local_path) < 20) {
            return false;
        } else if (!is_string($local_path)) {
            echo 'ERROR' . PHP_EOL;
            var_dump($local_path, debug_backtrace());
        }
        if (empty($local_path)) {
            return false;
        }
        if (!is_string($local_path)) {
            //debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            //var_dump($local_path);
            return false;
        }
        $path_parts = pathinfo($local_path);
        if (empty($path_parts) || empty($path_parts['extension'])) {
            return false;
        }
        $extension = $path_parts['extension'];
        if (!in_array(strtolower($extension), CDNStorage::$allowedFiles)) {
            return false;
        }
        $videosDir = Video::getStoragePath();
        $relative = str_replace($videosDir, '', $local_path);
        $relative = str_replace('\\', '/', $relative);
        $local_filesize = filesize($local_path);
        $remote_path = self::filenameToRemotePath($relative);
        $pz = self::getPZ();
        $file = [
            'extension' => $path_parts['extension'],
            'videos_id' => $videos_id,
            'local_path' => $local_path,
            'remote_path' => $remote_path,
            'local_url' => "{$global['webSiteRootURL']}videos/{$relative}",
            'remote_url' => "https://{$pz}{$relative}",
            'relative' => $relative,
            'local_filesize' => $local_filesize,
        ];
        return $file;
    }

    public static function getLocalFolder($videos_id)
    {
        if (empty($videos_id)) {
            return [];
        }
        $video = Video::getVideoLight($videos_id);

        if (empty($video)) {
            return [];
        }

        $paths = Video::getPaths($video['filename']);

        return listFolderFiles($paths['path']);
    }

    public static function getOrCreateSite()
    {
        $status = 'y';
        $row = Sites::getFromStatus($status);
        if (empty($row)) {
            $row['name'] = 'Storage';
            $row['url'] = 'url';
            $row['status'] = $status;
            $row['secret'] = 'no secret';
            $s = new Sites(0);
            $s->setName($row['name']);
            $s->setURL($row['url']);
            $s->setStatus($row['status']);
            $s->setSecret($row['secret']);
            $row['id'] = $s->save();

            return $row;
        }
        return $row[0];
    }

    public static function setSite($videos_id, $isOnTheStorage)
    {
        //_mysql_connect();
        $v = new Video('', '', $videos_id);
        if ($isOnTheStorage) {
            $site = self::getOrCreateSite();
            $v->setSites_id($site['id']);
        } else {
            $v->setSites_id(0);
        }

        return $v->save(false, true);
    }

    public static function moveRemoteToLocal($videos_id, $runInBackground = true, $deleteWhenIsDone = true)
    {
        $start = microtime(true);
        self::addToLog($videos_id, "Start moveRemoteToLocal videos_id={$videos_id}");
        $client = self::getStorageClient();
        $list = self::getFilesListRemote($videos_id, $client);
        $totalFiles = count($list);
        $filesCopied = 0;
        if (empty($totalFiles)) {
            $msg = 'There is not file to transfer (' . $totalFiles . ')';
            _error_log($msg);
            self::addToLog($videos_id, $msg);
            self::setProgress($videos_id, true, true);
            self::deleteRemoteDirectory($videos_id, $client);
            return false;
        }
        self::addToLog($videos_id, 'Found ' . $totalFiles . ' Files');
        $video = Video::getVideoLight($videos_id);
        if ($runInBackground) {
            outputAndContinueInBackground();
        }
        @_session_write_close();
        _mysql_close();
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        $fails = 0;
        $totalBytesTransferred = 0;
        $count = 0;
        $total = count($list);
        foreach ($list as $value) {
            $count++;
            $remote_filesize = $client->size($value['relative']);
            $local_filesize = @filesize($value['local_path']);
            if ($local_filesize >= $remote_filesize) {
                self::addToLog($value['videos_id'], $value['local_path'] . ' is NOT a dummy file local_filesize=' . $value['local_filesize'] . ' Bytes');
                //$client->delete($value['remote_path']);
                continue;
            }
            try {
                $msg = "[{$count}/{$total}] GET File start from {$value['remote_path']} " . humanFileSize($remote_filesize);
                self::addToLog($videos_id, $msg);
                $start = microtime(true);
                $response = $client->get($value['local_path'], $value['relative']);
                $end = microtime(true) - $start;
                $msg = "GET File moved from {$value['remote_path']} to {$value['local_path']} in " . secondsToDuration($end) . ' ETA: ' . secondsToDuration($end * ($total - $count));
                self::addToLog($videos_id, $msg);
                $filesCopied++;
                $totalBytesTransferred += $remote_filesize;
            } catch (Exception $exc) {
                $fails++;
                _error_log($exc->getTraceAsString());
                _error_log(json_encode(error_get_last()));
                self::addToLog($videos_id, "ERROR 1 " . $exc->getTraceAsString());
                self::addToLog($videos_id, "ERROR 2 " . json_encode(error_get_last()));
            }
        }
        if (empty($fails)) {
            if ($deleteWhenIsDone) {
                self::deleteRemoteDirectory($videos_id, $client);
            }
            self::setProgress($videos_id, false, true);
            self::sendSocketNotification($videos_id, __('Video download complete'));
        } else {
            _error_log("ERROR moveRemoteToLocal had {$fails} fails videos_id=($videos_id) filesCopied={$filesCopied} in {$end} Seconds");
        }
        $end = microtime(true) - $start;
        _error_log("Finish moveRemoteToLocal videos_id=($videos_id) filesCopied={$filesCopied} in {$end} Seconds");
        return ['filesCopied' => $filesCopied, 'totalBytesTransferred' => $totalBytesTransferred];
    }

    public static function deleteRemoteDirectory($videos_id, $client = null, $recursive = true)
    {
        if (empty($videos_id)) {
            return false;
        }
        $video = Video::getVideoLight($videos_id);
        if (empty($video['filename'])) {
            return false;
        }
        return self::deleteRemoteDirectoryFromFilename($video['filename'], $client, $recursive);
    }

    public static function deleteRemoteDirectoryFromFilename($filename, $client = null, $recursive = true)
    {
        if (empty($filename)) {
            return false;
        }
        $obj = AVideoPlugin::getDataObject('CDN');
        if (empty($client)) {
            $client = self::getStorageClient();
        }
        $dir = self::filenameToRemotePath($filename);
        if (!$client->isDir($dir)) {
            return false;
        }
        _error_log("CDNStorage::deleteRemoteDirectoryFromFilename {$dir}");
        return $client->rmdir($dir, $recursive);
    }

    public static function filenameToRemotePath($filename, $addUsernameFolder = true)
    {
        global $global;
        $obj = AVideoPlugin::getDataObject('CDN');
        $filename = str_replace(getVideosDir(), '', $filename);
        if ($addUsernameFolder && !preg_match('/^\/' . $obj->storage_username . '\//', $filename)) {
            return "/{$obj->storage_username}/$filename";
        }
        return $filename;
    }

    public static function moveLocalToRemote($videos_id, $runInBackground = true)
    {
        $start = microtime(true);
        self::addToLog($videos_id, "Start moveLocalToRemote videos_id={$videos_id}");
        $list = self::getFilesListLocal($videos_id);
        $totalFiles = count($list);
        if (empty($totalFiles)) {
            self::addToLog($videos_id, 'There is not file to transfer (' . $totalFiles . ')');
            self::setProgress($videos_id, true, true);
            return false;
        }
        self::addToLog($videos_id, 'Found ' . $totalFiles . ' Files');
        $client = self::getStorageClient();
        $video = Video::getVideoLight($videos_id);
        //$client->mkdir($video['filename'], true);
        if ($runInBackground) {
            outputAndContinueInBackground();
        }
        @_session_write_close();
        _mysql_close();
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        //self::addToLog($videos_id, 'Directory ' . $video['filename'] . ' Created');
        $totalTime = 0;
        $itemsProcessed = 0;
        $totalFilesToTransfer = count($list);
        $totalBytesTransferred = 0;
        $filesCopied = 0;
        foreach ($list as $value) {
            $itemsProcessed++;
            if (filesize($value['local_path']) < 20) {
                self::addToLog($value['videos_id'], $value['local_path'] . ' is a dummy file local_filesize=' . $value['local_filesize'] . ' Bytes');
                continue;
            }
            try {
                if (empty($value['remote_filesize'])) {
                    $remote_filesize = $client->size($value['relative']);
                } else {
                    $remote_filesize = $value['remote_filesize'];
                }

                if ($remote_filesize > 0 && $remote_filesize == $value['local_filesize']) {
                    $msg = "File is already on the remote {$value['local_path']} to {$value['remote_path']} ";
                    self::addToLog($videos_id, $msg);
                    self::createDummy($value['local_path']);
                    continue;
                }
                $uploadstart = microtime(true);
                $response = $client->put($value['relative'], $value['local_path']);
                $filesCopied++;
                $uploadfinish = microtime(true) - $uploadstart;
                $totalTime += $uploadfinish;
                $bytesPerSecond = $value['local_filesize'] / $uploadfinish;
                $remainingFiles = $totalFilesToTransfer - $itemsProcessed;
                $averageSeconds = $totalTime / $itemsProcessed;
                $remainingSeconds = intval($remainingFiles * $averageSeconds);
                $remainingSecondsHuman = secondsToVideoTime($remainingSeconds);
                $totalBytesTransferred += filesize($value['local_path']);
                $msg = "{$itemsProcessed}/{$totalFilesToTransfer} {$remainingSecondsHuman} to finish: File moved from {$value['local_path']} to {$value['remote_path']} in {$uploadfinish} seconds " . humanFileSize($bytesPerSecond) . '/sec Average: ' . number_format($averageSeconds, 2);
                self::addToLog($videos_id, $msg);
                if ($itemsProcessed % 100 === 0) {
                    self::createDummyFiles($videos_id);
                }
                /*
                  $remote_filesize = $client->size($value['relative']);
                  if ($remote_filesize < 0) {
                  self::addToLog($videos_id, "Filesizes are not the same trying the full path ");
                  $response = $client->put($value['remote_path'], $value['local_path']);
                  $remote_filesize = $client->size($value['remote_path']);
                  }

                  if ($remote_filesize == $value['local_filesize']) {
                  $msg = "PUT File moved from {$value['local_path']} to {$value['remote_path']} ";
                  self::addToLog($videos_id, $msg);
                  $filesCopied++;
                  self::createDummy($value['local_path']);
                  } else {
                  self::addToLog($videos_id, "ERROR Filesizes are not the same $remote_filesize == {$value['local_filesize']} " . json_encode($value));
                  self::addToLog($videos_id, "ERROR " . json_encode($response));
                  }
                 */
            } catch (Exception $exc) {
                _error_log($exc->getTraceAsString());
                _error_log(json_encode(error_get_last()));
                self::addToLog($videos_id, "ERROR 1 " . $exc->getTraceAsString());
                self::addToLog($videos_id, "ERROR 2 " . json_encode(error_get_last()));
            }
        }
        self::createDummyFiles($videos_id);
        self::sendSocketNotification($videos_id, __('Video upload complete'));
        self::setProgress($videos_id, true, true);
        $end = microtime(true) - $start;
        _error_log("Finish moveLocalToRemote videos_id=($videos_id) filesCopied={$filesCopied} in {$end} Seconds");

        return ['filesCopied' => $filesCopied, 'totalBytesTransferred' => $totalBytesTransferred];
    }

    public static function put($videos_id, $totalSameTime, $onlyExtension = '')
    {
        global $_uploadInfo;
        if (empty($videos_id)) {
            return false;
        }
        _error_log("CDNStorage::put got a list from {$videos_id}");
        $list = self::getFilesListBoth($videos_id);
        _error_log("CDNStorage::put got a list " . count($list));

        $filesToUpload = [];
        $totalFilesize = 0;
        $totalBytesTransferred = 0;
        $fileUploadCount = 0;
        $forceUseFTP = false;
        foreach ($list as $value) {
            if (empty($value['local'])) {
                continue;
            }
            $ext = pathinfo($value['local']['local_path'], PATHINFO_EXTENSION);
            if (!empty($onlyExtension) && strtolower($onlyExtension) !== strtolower($ext)) {
                _error_log("CDNStorage::put we will only upload {$onlyExtension} we will not uplaod {$value['local']['local_path']}");
                continue;
            }
            $filesize = filesize($value['local']['local_path']);
            if ($value['isLocal'] && $filesize > 20) {
                if (empty($value) || empty($value['remote']) || $filesize != $value['remote']['remote_filesize']) {
                    if (!empty($value['remote']['remote_filesize'])) {
                        _error_log("CDNStorage:: add {$value['remote']['relative']} {$filesize} != {$value['remote']['remote_filesize']}");
                    }
                    $filesToUpload[] = $value['local']['local_path'];
                    $totalFilesize += $filesize;
                } else {
                    _error_log("CDNStorage::put same size {$value['remote']['remote_filesize']} {$value['remote']['relative']}");
                }
            } else {
                _error_log("CDNStorage::put not valid local file {$value['local']['local_path']}");
            }
        }
        $totalFiles = count($filesToUpload);
        if (empty($filesToUpload)) {
            _error_log("CDNStorage::put videos_id={$videos_id} There is no file to upload ");
        } else {
            _error_log("CDNStorage::put videos_id={$videos_id} totalSameTime=$totalSameTime totalFiles={$totalFiles} totalFilesize=" . humanFileSize($totalFilesize));

            if (version_compare(PHP_VERSION, '8.0.0') >= 0 && !$forceUseFTP) {
                try {
                    $response = self::putUsingAPI($filesToUpload);
                } catch (\Throwable $th) {
                    _error_log("CDNStorage::put API error use FTP");
                    $response = self::putUsingFTP($filesToUpload, $totalSameTime);
                    $forceUseFTP = true;
                }
            } else {
                $response = self::putUsingFTP($filesToUpload, $totalSameTime);
            }

            $fileUploadCount += $response['filesCopied'];
            $totalBytesTransferred += $response['totalBytesTransferred'];
        }

        if ((empty($onlyExtension) || !empty($fileUploadCount)) && $fileUploadCount == $totalFiles) {
            self::createDummyFiles($videos_id);
            self::sendSocketNotification($videos_id, __('Video upload complete'));
            self::setProgress($videos_id, true, true);
            _error_log("CDNStorage::put finished SUCCESS ");
        } else {
            _error_log("CDNStorage::put finished ERROR " . json_encode(array(
                'onlyExtension' => $onlyExtension,
                'fileUploadCount' => $fileUploadCount,
                'totalFiles' => $totalFiles
            )));
        }
        return ['filesCopied' => $fileUploadCount, 'totalBytesTransferred' => $totalBytesTransferred];
    }

    public static function putUsingAPI($filesToUpload)
    {
        global $_uploadInfo;
        if (!isset($_uploadInfo)) {
            $_uploadInfo = [];
        }
        $cdnObj = AVideoPlugin::getDataObjectIfEnabled('CDN');
        $parts = explode('.', $cdnObj->storage_hostname);
        $apiAccessKey = $cdnObj->storage_password;
        $storageZoneName = $cdnObj->storage_username; // Replace with your storage zone name
        $storageZoneRegion = trim(strtolower($parts[0]));
        $fileUploadCount = 0;
        $totalBytesTransferred = 0;
        $total = count($filesToUpload);
        _error_log("CDNStorage::putUsingAPI total=$total storageZoneName=$storageZoneName, storageZoneRegion=$storageZoneRegion" . json_encode($filesToUpload));
        $client = new \Bunny\Storage\Client($apiAccessKey, $storageZoneName, $storageZoneRegion);
        foreach ($filesToUpload as $value) {
            if (empty($value)) {
                _error_log("CDNStorage::putUsingAPI empty local " . json_encode($value));
                continue;
            }
            $filesize = @filesize($value);
            if ($filesize > 20) {
                $fileUploadCount++;
                $remote_file = CDNStorage::filenameToRemotePath($value);
                _error_log("CDNStorage::putUsingAPI {$remote_file} ");
                $client->upload($value, $remote_file);
                $totalBytesTransferred += $filesize; // Update remaining size
            } else {
                _error_log("CDNStorage::putUsingAPI invalid filesize [$filesize] " . json_encode($value));
            }
        }
        return ['filesCopied' => $fileUploadCount, 'totalBytesTransferred' => $totalBytesTransferred];
    }

    public static function putUsingFTP($filesToUpload, $totalSameTime)
    {
        global $_uploadInfo;
        if (!isset($_uploadInfo)) {
            $_uploadInfo = [];
        }
        $totalFiles = count($filesToUpload);
        $fileUploadCount = 0;
        $totalBytesTransferred = 0;
        $conn_id = [];
        $ret = [];
        $maxRetries = 3; // Maximum number of retries
        $retryDelay = 5; // Delay between retries in seconds

        for ($i = 0; $i < $totalSameTime; $i++) {
            $file = array_shift($filesToUpload);
            if (empty($file)) {
                continue;
            }
            //_error_log("CDNStorage::put:upload 1 {$i} Start {$file}");
            $upload = self::uploadToCDNStorage($file, $i, $conn_id, $ret);
            //_error_log("CDNStorage::put:upload 1 {$i} done {$file}");
            if ($upload) {
                $fileUploadCount++;
            } else {
                _error_log("CDNStorage::put:upload 1 {$i} error {$file}");
            }
        }
        //_error_log("CDNStorage::put confirmed " . count($ret));
        $continue = true;
        while ($continue) {
            $continue = false;
            foreach ($ret as $key => $r) {
                if (empty($r)) {
                    continue;
                }
                if ($r == FTP_MOREDATA) {
                    // Continue uploading...
                    $retries = 0;
                    while ($retries < $maxRetries) {
                        try {
                            $ret[$key] = ftp_nb_continue($conn_id[$key]);
                            break; // If successful, break out of the retry loop
                        } catch (Exception $exc) {
                            _error_log("CDNStorage::put:upload ftp_nb_continue error " . $exc->getMessage());
                            $retries++;
                            if ($retries >= $maxRetries) {
                                _error_log("CDNStorage::put:upload ftp_nb_continue failed after {$maxRetries} retries");
                                break;
                            }
                            sleep($retryDelay);
                        }
                    }
                    $continue = true;
                }
                if ($r == FTP_FINISHED) {
                    $end = microtime(true) - $_uploadInfo[$key]['microtime'];
                    $filesize = $_uploadInfo[$key]['filesize'];
                    $remote_file = $_uploadInfo[$key]['remote_file'];
                    $humanFilesize = humanFileSize($filesize);
                    $ps = humanFileSize($filesize / $end);
                    $seconds = number_format($end);
                    $ETA = secondsToDuration($end * (($totalFiles - $fileUploadCount) / $totalSameTime));
                    $totalBytesTransferred += $filesize;
                    unset($ret[$key]);
                    unset($_uploadInfo[$key]);

                    _error_log(date('Y-m-d H:i:s') . " CDNStorage::put:uploadToCDNStorage [$key] [{$fileUploadCount}/{$totalFiles}] FTP_FINISHED {$remote_file} in {$seconds} seconds {$humanFilesize} {$ps}ps ETA: {$ETA}");

                    $file = array_shift($filesToUpload);
                    if (empty($file)) {
                        continue;
                    }
                    //echo "File finished... $key" . PHP_EOL;
                    $upload = self::uploadToCDNStorage($file, $key, $conn_id, $ret);
                    if ($upload) {
                        $fileUploadCount++;
                        $totalBytesTransferred += $filesize;
                    } else {
                        _error_log("CDNStorage::put:upload 2 {$i} error {$file}");
                    }
                }
            }
        }

        _error_log("CDNStorage::put End totalFiles => $totalFiles, filesCopied => $fileUploadCount, totalBytesTransferred => $totalBytesTransferred");
        // close the connection
        foreach ($conn_id as $value) {
            ftp_close($value);
        }
        return ['filesCopied' => $fileUploadCount, 'totalBytesTransferred' => $totalBytesTransferred];
    }


    public static function ftp_get($videos_id)
    {
        global $_downloadInfo, $videos_id_to_move;
        $list = self::getFilesListBoth($videos_id);
        //var_dump($list);exit;
        $filesToDownload = [];
        $totalFilesize = 0;
        $totalBytesTransferred = 0;

        $fileDownloadCount = 0;
        $conn_id = array();
        $connID = self::getConnID(0, $conn_id);

        $total = count($list);
        $count = 0;

        foreach ($list as $filePath => $value) {
            $count++;
            //var_dump($value);exit;
            if (empty($value)) {
                continue;
            }
            if (!empty($value['local'])) {
                $filesize = filesize($value['local']['local_path']);
                if ($value['isLocal']) {
                    //_error_log("CDNStorage::get Local {$value['local']['local_path']} {$filesize} ");
                    if ($filesize > $value['remote']['remote_filesize']) {
                        _error_log("CDNStorage::get Local filesize is too big");
                        continue;
                    } elseif ($value['remote']['remote_filesize'] < 20) {
                        _error_log("CDNStorage::get remote filesize is too small");
                        continue;
                    } elseif ($filesize == $value['remote']['remote_filesize']) {
                        _error_log("CDNStorage::get same size {$value['remote']['remote_filesize']} {$value['remote']['relative']}");
                        continue;
                    }
                }
            }

            $local_file = $value['remote']['local_path'];
            //_error_log("CDNStorage::get:download Start {$local_file} ". humanFileSize($value['remote']['remote_filesize']));
            if (!empty($local_file)) {
                $remote_file = '/' . CDNStorage::filenameToRemotePath($local_file, false);
                $start = microtime(true);
                $totalVideosLeft = count($videos_id_to_move);
                if (ftp_get($connID, $local_file, $remote_file, FTP_BINARY)) {
                    $fileDownloadCount++;
                    $thisFilesize = filesize($local_file);
                    $seconds = intval(microtime(true) - $start);
                    if (!empty($seconds)) {
                        $bytesPerSecond = $thisFilesize / $seconds;
                        if ($bytesPerSecond < 200000) {
                            _error_log("CDNStorage::get too slow reconnect");
                            ftp_close($connID);
                            unset($conn_id[0]);
                            $connID = self::getConnID(0, $conn_id);
                        }
                    } else {
                        $bytesPerSecond = 0;
                    }
                    $mbps = humanFileSize($bytesPerSecond) . 'ps';
                    $totalBytesTransferred += $thisFilesize;
                    $remainingFiles = $total - $count;
                    $eta = $remainingFiles * $seconds;
                    $eta_string = secondsToDuration($eta);
                    _error_log("CDNStorage::ftp_get[{$totalVideosLeft}|{$count}/{$total}] success ETA $eta_string $mbps [$bytesPerSecond] {$remote_file} " . humanFileSize($thisFilesize));
                } else {
                    _error_log("CDNStorage::ftp_get[{$totalVideosLeft}|{$count}/{$total}] ERROR {$remote_file}");
                }
            }
        }
        if (empty($filesToDownload)) {
            _error_log("CDNStorage::get videos_id={$videos_id} There is no file to download ");
            return false;
        }

        return ['filesCopied' => $fileDownloadCount, 'totalBytesTransferred' => $totalBytesTransferred];
    }

    public static function get($videos_id, $totalSameTime)
    {
        global $_downloadInfo;
        $list = self::getFilesListBoth($videos_id);
        //var_dump($list);exit;
        $filesToDownload = [];
        $totalFilesize = 0;
        $totalBytesTransferred = 0;
        foreach ($list as $filePath => $value) {
            //var_dump($value);exit;
            if (empty($value)) {
                continue;
            }
            if (!empty($value['local'])) {
                $filesize = filesize($value['local']['local_path']);
                if ($value['isLocal']) {
                    _error_log("CDNStorage::get Local {$value['local']['local_path']} {$filesize} ");
                    if ($filesize > $value['remote']['remote_filesize']) {
                        _error_log("CDNStorage::get Local filesize is too big");
                    } elseif ($value['remote']['remote_filesize'] < 20) {
                        _error_log("CDNStorage::get remote filesize is too small");
                    } elseif ($filesize == $value['remote']['remote_filesize']) {
                        _error_log("CDNStorage::get same size {$value['remote']['remote_filesize']} {$value['remote']['relative']}");
                    } else {
                        $filesToDownload[] = $value['remote']['local_path'];
                        $totalFilesize += $value['remote']['remote_filesize'];
                    }
                } else {
                    $filesToDownload[] = $value['remote']['local_path'];
                    $totalFilesize += $value['remote']['remote_filesize'];
                }
            } else {
                $filesToDownload[] = $value['remote']['local_path'];
                $totalFilesize += $value['remote']['remote_filesize'];
            }
        }
        if (empty($filesToDownload)) {
            _error_log("CDNStorage::get videos_id={$videos_id} There is no file to download ");
            return false;
        }

        $totalFiles = count($filesToDownload);

        _error_log("CDNStorage::get videos_id={$videos_id} totalSameTime=$totalSameTime totalFiles={$totalFiles} totalFilesize=" . humanFileSize($totalFilesize));

        $conn_id = [];
        $ret = [];
        $fileDownloadCount = 0;
        for ($i = 0; $i < $totalSameTime; $i++) {
            $file = array_shift($filesToDownload);
            //_error_log("CDNStorage::get:download 1 {$i} Start {$file}");
            if (empty($file)) {
                continue;
            }
            $download = self::downloadFromCDNStorage($file, $i, $conn_id, $ret);
            //_error_log("CDNStorage::get:download 1 {$i} done {$file}");
            if ($download) {
                $fileDownloadCount++;
            } else {
                _error_log("CDNStorage::get:download 1 {$i} error {$file}");
            }
        }
        _error_log("CDNStorage::get confirmed " . count($ret));
        $continue = true;
        while ($continue) {
            $continue = false;
            foreach ($ret as $key => $r) {
                if (empty($r)) {
                    continue;
                }
                if ($r == FTP_MOREDATA) {
                    // Continue downloading...
                    _error_log(date('Y-m-d H:i:s') . " CDNStorage::get:downloadToCDNStorage Continue downloading. [$key] [$r] " . count($conn_id));
                    try {
                        $ret[$key] = ftp_nb_continue($conn_id[$key]);
                        $continue = true;
                    } catch (Exception $exc) {
                        _error_log(date('Y-m-d H:i:s') . " CDNStorage::get:downloadToCDNStorage ERROR . [$key] " . $exc->getMessage());
                    }
                }
                //_error_log(date('Y-m-d H:i:s') . " CDNStorage::get:downloadToCDNStorage Continue downloading 2. [$key] ");

                if ($r == FTP_FINISHED) {
                    $end = microtime(true) - $_downloadInfo[$key]['microtime'];
                    $filesize = $_downloadInfo[$key]['filesize'];
                    $remote_file = $_downloadInfo[$key]['remote_file'];
                    $humanFilesize = humanFileSize($filesize);
                    $ps = humanFileSize($filesize / $end);
                    $seconds = number_format($end);
                    $ETA = secondsToDuration($end * (($totalFiles - $fileDownloadCount) / $totalSameTime));
                    $totalBytesTransferred += $filesize;
                    unset($ret[$key]);
                    unset($_downloadInfo[$key]);

                    _error_log(date('Y-m-d H:i:s') . " CDNStorage::get:downloadToCDNStorage [$key] [{$fileDownloadCount}/{$totalFiles}] FTP_FINISHED {$remote_file} in {$seconds} seconds {$humanFilesize} {$ps}ps ETA: {$ETA}");

                    $file = array_shift($filesToDownload);
                    if (empty($file)) {
                        continue;
                    }
                    //echo "File finished... $key" . PHP_EOL;
                    $download = self::downloadFromCDNStorage($file, $key, $conn_id, $ret);
                    if ($download) {
                        $fileDownloadCount++;
                        $totalBytesTransferred += $filesize;
                    } else {
                        _error_log("CDNStorage::get:download 2 {$i} error {$file}");
                    }
                }
            }
        }

        _error_log("CDNStorage::get videos_id={$videos_id} End totalFiles => $totalFiles, filesCopied => $fileDownloadCount, totalBytesTransferred => $totalBytesTransferred");
        // close the connection
        foreach ($conn_id as $value) {
            ftp_close($value);
        }

        if ($fileDownloadCount == $totalFiles) {
            //self::sendSocketNotification($videos_id, __('Video download complete'));
            //self::setProgress($videos_id, false, true);
            _error_log("CDNStorage::get finished SUCCESS ");
        } else {
            _error_log("CDNStorage::get finished ERROR ");
        }
        return ['filesCopied' => $fileDownloadCount, 'totalBytesTransferred' => $totalBytesTransferred];
    }

    private static function getConnID($index, &$conn_id)
    {
        if (empty($conn_id[$index])) {
            $timeout = 180;
            $obj = AVideoPlugin::getDataObject('CDN');
            if (!empty($conn_id[$index])) {
                ftp_close($conn_id[$index]);
            }
            $conn_id[$index] = ftp_connect($obj->storage_hostname, 21, $timeout);
            if (empty($conn_id[$index])) {
                error_log("getConnID($index) error on ftp_connect($obj->storage_hostname, 21, $timeout)");
                exit;
            }
            ftp_set_option($conn_id[$index], FTP_TIMEOUT_SEC, $timeout);
            if (empty($conn_id[$index])) {
                sleep(1);
                return self::getConnID($index, $conn_id);
            }
            // login with username and password
            $login_result = ftp_login($conn_id[$index], $obj->storage_username, $obj->storage_password);
            ftp_pasv($conn_id[$index], true);
        } else {
            //_error_log("CDNStorage::put:getConnID $index created");
        }
        return $conn_id[$index];
    }

    private static function downloadFromCDNStorage($local_path, $index, &$conn_id, &$ret)
    {
        global $_uploadInfo;
        if (!isset($_uploadInfo)) {
            $_uploadInfo = [];
        }
        if (empty($local_path)) {
            _error_log("CDNStorage::downloadFromCDNStorage empty local file name {$local_path}");
            //return false;
        }

        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        $remote_file = '/' . CDNStorage::filenameToRemotePath($local_path, false);
        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        if (empty($remote_file)) {
            _error_log("CDNStorage::downloadFromCDNStorage error empty remote file name {$local_path}");
            return false;
        }
        $connID = self::getConnID($index, $conn_id);
        $filesize = ftp_size($connID, $remote_file);

        if ($filesize < 20) {
            _error_log("CDNStorage::downloadFromCDNStorage error {$remote_file} filesize is too small {$filesize}");
            return false;
        }

        $localFilesize = @filesize($local_path);
        if (file_exists($local_path) && $localFilesize > 20 && $localFilesize >= $filesize) {
            _error_log("CDNStorage::downloadFromCDNStorage error file already exists {$local_path}");
            return false;
        }
        _error_log("CDNStorage::downloadFromCDNStorage [$index] START {$remote_file} to {$local_path} ");
        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        $_uploadInfo[$index] = ['microtime' => microtime(true), 'filesize' => $filesize, 'local_path' => $local_path, 'remote_file' => $remote_file];
        $fp = fopen($local_path, 'w');
        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        $ret[$index] = ftp_nb_fget($connID, $fp, $remote_file, FTP_BINARY);
        _error_log("CDNStorage::get:downloadFromCDNStorage SUCCESS [$index] {$remote_file} " . json_encode($_uploadInfo[$index]));
        return true;
    }

    private static function uploadToCDNStorage($local_path, $index, &$conn_id, &$ret)
    {
        global $_uploadInfo;
        if (!isset($_uploadInfo)) {
            $_uploadInfo = [];
        }
        if (empty($local_path)) {
            _error_log("CDNStorage::put:uploadToCDNStorage error empty local file name {$local_path}");
            return false;
        }
        if (!file_exists($local_path)) {
            _error_log("CDNStorage::put:uploadToCDNStorage error file does not exists {$local_path}");
            return false;
        }
        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        $remote_file = CDNStorage::filenameToRemotePath($local_path);
        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        if (empty($remote_file)) {
            _error_log("CDNStorage::put:uploadToCDNStorage error empty remote file name {$local_path}");
            return false;
        }
        $filesize = filesize($local_path);
        //_error_log("CDNStorage::put:uploadToCDNStorage [$index] START " . humanFileSize($filesize) . " {$remote_file} ");
        $connID = self::getConnID($index, $conn_id);
        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        $_uploadInfo[$index] = ['microtime' => microtime(true), 'filesize' => $filesize, 'local_path' => $local_path, 'remote_file' => $remote_file];
        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        $ret[$index] = ftp_nb_put($connID, $remote_file, $local_path, FTP_BINARY);
        //_erroftp_close($connID); r_log("CDNStorage::put:uploadToCDNStorage SUCCESS [$index] {$remote_file} " . json_encode($_uploadInfo[$index]));

        return true;
    }

    public static function createDummyFiles($videos_id)
    {
        $obj = AVideoPlugin::getObjectData('CDN');

        if (!empty($obj->storage_keep_original_files)) {
            return 0;
        }

        $msg = "createDummyFiles($videos_id) ";
        self::addToLog($videos_id, $msg);

        global $_getFilesListBoth, $_getFilesListRemote, $_getFilesList_CDNSTORAGE;
        unset($_getFilesListBoth);
        unset($_getFilesListRemote);
        unset($_getFilesList_CDNSTORAGE);

        $list = self::getFilesListBoth($videos_id);
        $filesAffected = 0;
        $allRemote = true; // Flag to check if all local files are on the remote

        // First pass: Check if all local files have corresponding remote files
        foreach ($list as $key => $value) {
            if (empty($value['local']) || empty($value['local']['local_filesize']) || $value['local']['local_filesize'] <= 20) {
                continue;
            } elseif (empty($value['remote']) || $value['local']['local_filesize'] != $value['remote']['remote_filesize']) {
                // Log the file that is missing or has a size mismatch on the remote
                $msg = "File not on remote or size mismatch: {$value['local']['local_path']}, Local size: {$value['local']['local_filesize']}, Remote size: " . (!empty($value['remote']['remote_filesize']) ? $value['remote']['remote_filesize'] : 'N/A');
                self::addToLog($videos_id, $msg);
                $allRemote = false; // Set to false if any file does not match
                break; // Exit the loop if any file is missing on the remote
            }
        }

        // Second pass: Create dummy files only if all files are on the remote
        if ($allRemote) {
            foreach ($list as $key => $value) {
                if (!empty($value['local']) && !empty($value['local']['local_filesize']) && $value['local']['local_filesize'] > 20) {
                    $msg = "createDummyFiles {$value['local']['local_path']} ";
                    self::addToLog($videos_id, $msg);
                    self::createDummy($value['local']['local_path']);
                    $filesAffected++;
                }
            }
        }

        $msg = "createDummyFiles filesAffected=$filesAffected";
        self::addToLog($videos_id, $msg);

        if ($filesAffected) {
            Video::clearCache($videos_id);
        }

        return $filesAffected;
    }


    public static function sendSocketNotification($videos_id, $msg)
    {
        if (empty($videos_id)) {
            return false;
        }
        $v = Video::getVideoLight($videos_id);
        if (empty($v)) {
            return false;
        }
        $users_id = $v['users_id'];
        if (!empty($users_id)) {
            $poster = Video::getPoster($videos_id);
            $img = "<img src='{$poster}' class='img img-responsive'>";
            sendSocketMessageToUsers_id($msg . '<br>' . $img, $users_id, 'socketCDNStorageMoved');
        }
    }

    private static function setProgress($videos_id, $isOnTheStorage, $finished)
    {
        self::setSite($videos_id, $isOnTheStorage);
        if ($finished) {
            Video::updateFilesize($videos_id);
            self::deleteLog($videos_id);
        }
    }

    public static function isMoving($videos_id)
    {
        $file = self::getLogFile($videos_id);
        if (empty($file) || !file_exists($file)) {
            return false;
        }

        $modified = filemtime($file);
        $totalTime = time() - $modified;
        if ($totalTime > 300) {
            if ($totalTime > 10000) {
                @unlink($file);
                return false;
            } else {
                // if is laonger than 5 min say it is not moving
                _error_log("CDNStorage isMoving is taking too long to finish ({$totalTime} seconds), check your connection speed or FTP errors {$file}", AVideoLog::$WARNING);
                return false;
            }
        }

        return ['modified' => $modified, 'created' => filectime($file)];
    }

    public static function createDummy($file_path)
    {
        global $global;
        $path_parts = pathinfo($file_path);
        $extension = strtolower($path_parts['extension']);

        if ($extension == 'ts') {
            @unlink($file_path);
        } elseif (in_array($extension, CDNStorage::$allowedFiles)) {
            file_put_contents($file_path, 'Dummy File');
        } else {
            return false;
        }

        return true;
    }

    public static function getFilesListLocal($videos_id, $skipDummyFiles = true)
    {
        global $global, $_getFilesList_CDNSTORAGE;
        if (empty($videos_id)) {
            return [];
        }
        if (!isset($_getFilesList_CDNSTORAGE)) {
            $_getFilesList_CDNSTORAGE = [];
        }
        if (!empty($_getFilesList_CDNSTORAGE[$videos_id])) {
            return $_getFilesList_CDNSTORAGE[$videos_id];
        }

        $pz = self::getPZ();
        $files = self::getLocalFolder($videos_id);
        $filesList = [];
        $acumulative = 0;

        // Iterate over root files or directories
        foreach ($files as $value) {
            self::processFileOrDirectory($value, $filesList, $acumulative, $pz, $videos_id, $skipDummyFiles);
        }

        $_getFilesList_CDNSTORAGE[$videos_id] = $filesList;
        return $filesList;
    }

    // Recursive function to process files and directories
    private static function processFileOrDirectory($value, &$filesList, &$acumulative, $pz, $videos_id, $skipDummyFiles)
    {
        if (is_array($value)) {
            foreach ($value as $subValue) {
                self::processFileOrDirectory($subValue, $filesList, $acumulative, $pz, $videos_id, $skipDummyFiles);
            }
        } else {
            $file = self::getFilesListInfo($value, $pz, $videos_id, $skipDummyFiles);
            if (!empty($file)) {
                $acumulative += $file['local_filesize'];
                $file['acumulativeFilesize'] = $acumulative;
                $filesList[$file['relative']] = $file;
            }
        }
    }


    public function getAddress($filename)
    {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $file = Video::getPathToFile($filename);
        $address = ['path' => $file, 'url' => self::getURL($filename)];
        return $address;
    }

    public static function getURL($filename)
    {
        global $global;

        // this is because sometimes I send filenames like this "videos/video_200721131007_6b3e/video_200721131007_6b3e_Low.mp4"
        if (preg_match('/^videos\\//', $filename)) {
            $parts = explode('/', $filename);
            if (count($parts) == 3) {
                $filename = $parts[2];
            }
        }

        $paths = Video::getPaths($filename);
        if(!preg_match('/index.mp[34]$/', $paths['path'])){
            $file = $paths['path'] . $filename;
        }else{
            $file = $paths['path'];
        }
        if (!file_exists($file)) {
            $file = $paths['path'] . $filename;
        }
        if (!file_exists($file)) {
            $file = $paths['path'] . 'index.m3u8';
        }
        if (is_dir($file)) {
            return false;
        }
        if (!file_exists($file)) {
            return false;
        }
        if (filesize($file) > 20) {
            return Video::getURLToFile($filename);
        }
        if (preg_match('/m3u8$/', $filename)) {
            $relativeFilename = $filename;
        } else if (preg_match('/index.mp[34]$/', $filename)) {
            $relativeFilename = $filename;
        } else {
            $relativeFilename = "{$paths['filename']}/{$filename}";
        }
        $obj = AVideoPlugin::getDataObject('CDN');
        $pz = self::getPZ();
        return "https://{$pz}{$relativeFilename}";
    }

    public static function getLogFile($videos_id)
    {
        if (empty($videos_id)) {
            return false;
        }
        $video = Video::getVideoLight($videos_id);

        if (empty($video)) {
            return false;
        }

        $path = Video::getPathToFile($video['filename']);

        $path .= '_cdnStorage.log';
        return $path;
    }

    public static function addToLog($videos_id, $message)
    {
        if (empty($videos_id)) {
            return false;
        }
        if (isCommandLineInterface()) {
            echo $message . PHP_EOL;
        }
        _error_log($message, AVideoLog::$MONITORE);
        $file = self::getLogFile($videos_id);
        if (empty($file)) {
            return false;
        }
        return file_put_contents($file, date('Y-m-d H:i:s: ') . $message . PHP_EOL, FILE_APPEND);
    }

    public static function deleteLog($videos_id)
    {
        if (empty($videos_id)) {
            return false;
        }
        $file = self::getLogFile($videos_id);
        if (empty($file)) {
            return false;
        }
        return @unlink($file);
    }

    public static function file_get_contents($remote_filename)
    {
        $obj = AVideoPlugin::getDataObject('CDN');
        $filename = "ftp://{$obj->storage_username}:{$obj->storage_password}@{$obj->storage_hostname}/{$remote_filename}";
        return file_get_contents($filename);
    }

    public static function file_exists_on_cdn($remote_filename)
    {
        global $file_exists_on_cdn;
        if (!isset($file_exists_on_cdn)) {
            $file_exists_on_cdn = array();
        }
        $remote_filename = str_replace('videos/', '', $remote_filename);
        if (isset($file_exists_on_cdn[$remote_filename])) {
            return $file_exists_on_cdn[$remote_filename];
        }

        $client = self::getStorageClient();
        $dir = dirname($remote_filename);
        try {
            $list = $client->rawlist($dir, true);
        } catch (\Throwable $th) {
            $list = array();
        }
        $index = "file#{$remote_filename}";
        preg_match('/ ([0-9]+) [a-zA-z]+ [0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}/', $list[$index], $matches);
        $filesize = empty($matches[1]) ? 0 : $matches[1];
        if (empty($filesize)) {
            $file_exists_on_cdn[$remote_filename] = false;
        } else {
            $file_exists_on_cdn[$remote_filename] = isset($list[$index]);
        }
        _error_log("file_exists_on_cdn($remote_filename) ({$filesize}) {$list[$index]} - " . json_encode($file_exists_on_cdn[$remote_filename]));
        //var_dump($matches, $list[$index]);exit;
        return $file_exists_on_cdn[$remote_filename];
    }

    public static function convertCDNHLSVideoToDownload($videos_id, $format = 'mp4', $logFile = '')
    {
        _error_log("convertCDNHLSVideoToDownload: start $videos_id, $format, $logFile ");

        $format = strtolower($format);
        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();
        $files = getVideosURL($filename);

        $m3u8File = false;
        $mp4File = false;

        foreach ($files as $key => $theLink) {
            if (preg_match('/cdn\.ypt\.me(.*)' . $filename . '\/index\.m3u8/i', $theLink['url'])) {
                $m3u8File = $theLink['url'];
                break;
            } else if (preg_match('/cdn\.ypt\.me(.*)' . $filename . '\/.*.mp4/i', $theLink['url'])) {
                $mp4File = $theLink['url'];
                break;
            }
        }

        if (empty($m3u8File)) {
            if (!empty($mp4File)) {
                return $mp4File;
            }
            _error_log("convertCDNHLSVideoToDownload: m3u8 not found videos_id={$videos_id}, format={$format} " . json_encode($files));
            return false;
        }

        $url = str_replace('.m3u8', '.' . $format, $m3u8File);
        $parts1 = explode('cdn.ypt.me/', $url);
        $url = addQueryStringParameter($url, 'download', 1);
        if (empty($parts1[1])) {
            _error_log('convertCDNHLSVideoToDownload: Invalid filename ' . $url);
            return false;
        }
        $parts2 = explode('?', $parts1[1]);
        $relativeFilename = $parts2[0];
        $localFile = getVideosDir() . "{$relativeFilename}";
        $localFile = str_replace('/videos/videos/', '/videos/', $localFile);
        //var_dump($localFile);exit;
        $returnURL = false;
        if (file_exists($localFile)) {
            if (isDummyFile($localFile)) {
                $file_exists = true;
            } else {
                _error_log('convertCDNHLSVideoToDownload: download from CDN download file exists but the file is not a dummy file ' . $localFile . ' ' . filesize($localFile));
            }
        } else {
            _error_log('convertCDNHLSVideoToDownload: download from CDN dummy file not found ' . $localFile);
        }
        if (empty($file_exists)) {
            $file_exists = CDNStorage::file_exists_on_cdn($relativeFilename);
            if (!$file_exists && isDummyFile($localFile)) {
                _error_log("convertCDNHLSVideoToDownload: unlink($localFile)");
                @unlink($localFile);
            } else if ($file_exists && !isDummyFile($localFile)) {
                _error_log("convertCDNHLSVideoToDownload: self::createDummy($localFile)");
                self::createDummy($localFile);
            }
        }
        if ($file_exists && isURL200($url, true)) {
            $returnURL = $url;
            _error_log("convertCDNHLSVideoToDownload: returnURL ($returnURL)");
        } else {
            //var_dump($localFile);exit;
            if (!file_exists($localFile)) {
                _error_log("convertCDNHLSVideoToDownload: convertVideoFileWithFFMPEG($m3u8File, $localFile, $logFile)");
                $progressFile = convertVideoFileWithFFMPEG($m3u8File, $localFile, $logFile);
            }
            if (!file_exists($localFile)) {
                _error_log('convertCDNHLSVideoToDownload: download from CDN file not created ' . $localFile);
            } else {
                $filesize = filesize($localFile);
                if (empty($filesize) || isDummyFile($localFile)) {
                    _error_log("convertCDNHLSVideoToDownload 2: unlink($localFile)");
                    @unlink($localFile);
                } else if (!isDummyFile($localFile)) {
                    _error_log("convertCDNHLSVideoToDownload 2: !isDummyFile($localFile)");
                    $client = CDNStorage::getStorageClient();
                    $client->put($relativeFilename, $localFile);
                    self::createDummy($localFile);
                    $returnURL = $url;
                }
            }
        }
        return $returnURL;
    }

    public static function convertCDNHLSVideoToDownloadProgress($videos_id, $format = 'mp4')
    {
        $format = strtolower($format);
        $video = new Video('', '', $videos_id);
        $filename = $video->getFilename();
        $progressFile = getVideosDir() . "{$filename}/index.{$format}.log";
        return array('file' => $progressFile, 'progress' => parseFFMPEGProgress($progressFile));
    }
}
