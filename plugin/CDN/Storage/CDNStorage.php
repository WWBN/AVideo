<?php

if (!class_exists('FtpClient')) {
    require_once $global['systemRootPath'] . 'plugin/CDN/FtpClient/FtpClient.php';
    require_once $global['systemRootPath'] . 'plugin/CDN/FtpClient/FtpWrapper.php';
    require_once $global['systemRootPath'] . 'plugin/CDN/FtpClient/FtpException.php';
}

class CDNStorage {

    static $allowedFiles = array('mp4', 'webm', 'mp3', 'm3u8', 'ts', 'pdf', 'zip');

    private function getClient($try = 0) {
        return self::getStorageClient();
    }

    static function getStorageClient() {
        $obj = AVideoPlugin::getDataObject('CDN');
        $CDNstorage = new \FtpClient\FtpClient();
        try {
            $CDNstorage->connect($obj->storage_hostname);
            $CDNstorage->login($obj->storage_username, $obj->storage_password);
            $CDNstorage->pasv(true);
        } catch (Exception $exc) {
            _error_log("FTP:getClient fail ($obj->storage_hostname) ($obj->storage_username), ($obj->storage_password) " . $exc->getMessage());
            die('CDNStorage FTP Error ' . $exc->getMessage());
        }
        _error_log("FTP:getClient finish");
        return $CDNstorage;
    }

    public function xsendfilePreVideoPlay() {
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

    static function getFilesListBoth($videos_id) {
        global $_getFilesListBoth;
        if (!isset($_getFilesListBoth)) {
            $_getFilesListBoth = array();
        }
        if (!empty($_getFilesListBoth[$videos_id])) {
            return $_getFilesListBoth[$videos_id];
        }
        $remoteList = self::getFilesListRemote($videos_id);
        $localList = self::getFilesListLocal($videos_id, false);

        $searchThis = $localList;
        $compareThis = $remoteList;
        $searchingLocal = true;
        $files = array();
        foreach ($localList as $key => $value) {
            $isLocal = true;

            if (@$localList[$key]['local_filesize'] < @$remoteList[$key]['remote_filesize']) {
                $isLocal = false;
            }

            $files[$key] = array('isLocal' => $isLocal, 'local' => @$localList[$key], 'remote' => @$remoteList[$key]);
            unset($remoteList[$key]);
        }
        foreach ($remoteList as $key => $value) {
            $isLocal = true;

            if (
                    @$localList[$key]['local_filesize'] < 
                    @$remoteList[$key]['remote_filesize']) {
                $isLocal = false;
            }

            $files[$key] = array('isLocal' => $isLocal, 'local' => @$localList[$key], 'remote' => @$remoteList[$key]);
            unset($localList[$key]);
        }

        $_getFilesListBoth[$videos_id] = $files;
        return $files;
    }

    static function getPZ() {
        $obj = AVideoPlugin::getDataObject('CDN');
        return addLastSlash($obj->storage_username . '.cdn.ypt.me');
    }

    static function getFilesListRemote($videos_id, $client = null) {
        global $global, $_getFilesListRemote;
        if (!isset($_getFilesListRemote)) {
            $_getFilesListRemote = array();
        }
        if (!empty($_getFilesListRemote[$videos_id])) {
            return $_getFilesListRemote[$videos_id];
        }
        $video = Video::getVideoLight($videos_id);
        //$paths = Video::getPaths($video['filename']);
        if (empty($client)) {
            $client = self::getStorageClient();
        }
        $dir = self::filenameToRemotePath($video['filename']);
        try {
            if (!$client->isDir($dir)) {
                return array();
            }
        } catch (Exception $exc) {
            _error_log("CDNStorage::getFilesListRemote ({$dir}) " . $exc->getTraceAsString());
        }

        $obj = AVideoPlugin::getDataObject('CDN');
        $pz = self::getPZ();
        try {
            $list = $client->rawlist($video['filename'], true);
        } catch (Exception $exc) {
            $list = array();
        }

        $files = array();
        foreach ($list as $key => $value) {
            $parts1 = explode('#', $key);
            if ($parts1[0] == 'directory') {
                continue;
            }

            preg_match('/ ([0-9]+) [a-zA-z]+ [0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}/', $value, $matches);

            $remote_filesize = $matches[1];
            $relative = $parts1[1];
            $local_path = "{$global['systemRootPath']}videos/{$relative}";
            $local_filesize = @filesize($local_path);
            $remote_path = self::filenameToRemotePath($relative);
            $path_parts = pathinfo($local_path);
            $extension = $path_parts['extension'];

            $file = array(
                'extension' => $path_parts['extension'],
                'videos_id' => $videos_id,
                'local_path' => $local_path,
                'remote_path' => $remote_path,
                'local_url' => "{$global['webSiteRootURL']}videos/{$relative}",
                'remote_utl' => "https://{$pz}{$relative}",
                'relative' => $relative,
                'local_filesize' => $local_filesize,
                'remote_filesize' => $remote_filesize,
                'video' => $video);

            $files[$relative] = $file;
        }
        $_getFilesListRemote[$videos_id] = $files;
        return $files;
    }

    static function getRemoteDirectorySize($videos_id, $client = null) {
        $list = self::getFilesListRemote($videos_id, $client);
        $total = 0;
        foreach ($list as $value) {
            $total += $value['remote_filesize'];
        }
        return $total;
    }

    static function getFilesListInfo($local_path, $storage_pullzone, $videos_id, $skipDummyFiles = true) {
        global $global;
        if ($skipDummyFiles && filesize($local_path) < 20) {
            return false;
        }
        if(empty($local_path)){
            return false;
        }
        if(!is_string($local_path)){
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            //var_dump($local_path);
            return false;
        }
        $path_parts = pathinfo($local_path);
        if(empty($path_parts) || empty($path_parts['extension'])){
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
        $file = array(
            'extension' => $path_parts['extension'],
            'videos_id' => $videos_id,
            'local_path' => $local_path,
            'remote_path' => $remote_path,
            'local_url' => "{$global['webSiteRootURL']}videos/{$relative}",
            'remote_utl' => "https://{$pz}{$relative}",
            'relative' => $relative,
            'local_filesize' => $local_filesize);
        return $file;
    }

    static function getLocalFolder($videos_id) {
        $video = Video::getVideoLight($videos_id);
        $paths = Video::getPaths($video['filename']);

        return listFolderFiles($paths['path']);
    }

    static function getOrCreateSite() {
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

    public static function setSite($videos_id, $isOnTheStorage) {
        _mysql_connect();
        $v = new Video('', '', $videos_id);
        if ($isOnTheStorage) {
            $site = self::getOrCreateSite();
            $v->setSites_id($site['id']);
        } else {
            $v->setSites_id(0);
        }

        return $v->save(false, true);
    }

    static function moveRemoteToLocal($videos_id, $runInBackground = true, $deleteWhenIsDone = true) {
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
        @session_write_close();
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
        return array('filesCopied' => $filesCopied, 'totalBytesTransferred' => $totalBytesTransferred);
    }

    static function deleteRemoteDirectory($videos_id, $client = null, $recursive = true) {
        if (empty($videos_id)) {
            return false;
        }
        $video = Video::getVideoLight($videos_id);
        if (empty($video['filename'])) {
            return false;
        }
        return self::deleteRemoteDirectoryFromFilename($video['filename'], $client, $recursive);
    }

    static function deleteRemoteDirectoryFromFilename($filename, $client = null, $recursive = true) {
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

    static function filenameToRemotePath($filename, $addUsernameFolder = true) {
        global $global;
        $obj = AVideoPlugin::getDataObject('CDN');
        $filename = str_replace(getVideosDir(), '', $filename);
        if ($addUsernameFolder && !preg_match('/^\/' . $obj->storage_username . '\//', $filename)) {
            return "/{$obj->storage_username}/$filename";
        }
        return $filename;
    }

    static function moveLocalToRemote($videos_id, $runInBackground = true) {
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
        @session_write_close();
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

        return array('filesCopied' => $filesCopied, 'totalBytesTransferred' => $totalBytesTransferred);
    }

    static function put($videos_id, $totalSameTime, $onlyExtension = '') {
        global $_uploadInfo;
        $list = self::getFilesListBoth($videos_id);
        $filesToUpload = array();
        $totalFilesize = 0;
        $totalBytesTransferred = 0;
        $fileUploadCount = 0;
        foreach ($list as $value) {
            $ext = pathinfo($value['local']['local_path'], PATHINFO_EXTENSION);
            if (!empty($onlyExtension) && strtolower($onlyExtension) !== strtolower($ext)) {
                _error_log("CDNStorage::put we will only upload {$onlyExtension} we will not uplaod {$value['local']['local_path']}");
                continue;
            }
            $filesize = filesize($value['local']['local_path']);
            if ($value['isLocal'] && $filesize > 20) {
                if ($filesize != $value['remote']['remote_filesize']) {
                    if(!empty($value['remote']['remote_filesize'])){
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
            $conn_id = array();
            $ret = array();
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
                        $ret[$key] = ftp_nb_continue($conn_id[$key]);
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

                        _error_log("CDNStorage::put:uploadToCDNStorage [$key] [{$fileUploadCount}/{$totalFiles}] FTP_FINISHED in {$seconds} seconds {$humanFilesize} {$ps}ps ETA: {$ETA}");

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

            _error_log("CDNStorage::put videos_id={$videos_id} End totalFiles => $totalFiles, filesCopied => $fileUploadCount, totalBytesTransferred => $totalBytesTransferred");
            // close the connection
            foreach ($conn_id as $value) {
                ftp_close($value);
            }
        }

        if ((empty($onlyExtension) || !empty($fileUploadCount)) && $fileUploadCount == $totalFiles) {
            self::createDummyFiles($videos_id);
            self::sendSocketNotification($videos_id, __('Video upload complete'));
            self::setProgress($videos_id, true, true);
            _error_log("CDNStorage::put finished SUCCESS ");
        } else {
            _error_log("CDNStorage::put finished ERROR ");
        }
        return array('filesCopied' => $fileUploadCount, 'totalBytesTransferred' => $totalBytesTransferred);
    }

    static function get($videos_id, $totalSameTime) {
        global $_downloadInfo;
        $list = self::getFilesListBoth($videos_id);
        $filesToDownload = array();
        $totalFilesize = 0;
        $totalBytesTransferred = 0;
        foreach ($list as $value) {
            $filesize = filesize($value['local']['local_path']);
            if (!$value['isLocal']) {
                _error_log("CDNStorage::get Local {$value['local']['local_path']} {$filesize} ");
                if ($filesize > $value['remote']['remote_filesize']) {
                    _error_log("CDNStorage::get Local filesize is too big");
                } else if ($value['remote']['remote_filesize'] < 20) {
                    _error_log("CDNStorage::get remote filesize is too small");
                } else if ($filesize == $value['remote']['remote_filesize']) {
                    _error_log("CDNStorage::get same size {$value['remote']['remote_filesize']} {$value['remote']['relative']}");
                } else {
                    $filesToDownload[] = $value['local']['local_path'];
                    $totalFilesize += $value['remote']['remote_filesize'];
                }
            } else {
                _error_log("CDNStorage::get not valid local file " . json_encode($value['remote']));
            }
        }
        if (empty($filesToDownload)) {
            _error_log("CDNStorage::get videos_id={$videos_id} There is no file to download ");
            return false;
        }

        $totalFiles = count($filesToDownload);

        _error_log("CDNStorage::get videos_id={$videos_id} totalSameTime=$totalSameTime totalFiles={$totalFiles} totalFilesize=" . humanFileSize($totalFilesize));

        $conn_id = array();
        $ret = array();
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
        //_error_log("CDNStorage::get confirmed " . count($ret));
        $continue = true;
        while ($continue) {
            $continue = false;
            foreach ($ret as $key => $r) {
                if (empty($r)) {
                    continue;
                }
                if ($r == FTP_MOREDATA) {
                    // Continue downloading...
                    $ret[$key] = ftp_nb_continue($conn_id[$key]);
                    $continue = true;
                }
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

                    _error_log("CDNStorage::get:downloadToCDNStorage [$key] [{$fileDownloadCount}/{$totalFiles}] FTP_FINISHED in {$seconds} seconds {$humanFilesize} {$ps}ps ETA: {$ETA}");

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
        return array('filesCopied' => $fileDownloadCount, 'totalBytesTransferred' => $totalBytesTransferred);
    }

    private static function getConnID($index, &$conn_id) {
        if (empty($conn_id[$index])) {
            $obj = AVideoPlugin::getDataObject('CDN');
            $conn_id[$index] = ftp_connect($obj->storage_hostname);
            if (empty($conn_id[$index])) {
                sleep(1);
                return self::getConnID($index);
            }
            // login with username and password
            $login_result = ftp_login($conn_id[$index], $obj->storage_username, $obj->storage_password);
            ftp_pasv($conn_id[$index], true);
        } else {
            //_error_log("CDNStorage::put:getConnID $index created");
        }
        return $conn_id[$index];
    }

    private static function downloadFromCDNStorage($local_path, $index, &$conn_id, &$ret) {
        global $_uploadInfo;
        if (!isset($_uploadInfo)) {
            $_uploadInfo = array();
        }
        if (empty($local_path)) {
            _error_log("CDNStorage::downloadFromCDNStorage error empty local file name {$local_path}");
            return false;
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

        $localFilesize = filesize($local_path);
        if (file_exists($local_path) && $localFilesize > 20 && $localFilesize >= $filesize) {
            _error_log("CDNStorage::downloadFromCDNStorage error file already exists {$local_path}");
            return false;
        }
        _error_log("CDNStorage::downloadFromCDNStorage [$index] START {$remote_file} to {$local_path} ");
        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        $_uploadInfo[$index] = array('microtime' => microtime(true), 'filesize' => $filesize, 'local_path' => $local_path, 'remote_file' => $remote_file);
        $fp = fopen($local_path, 'w');
        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        $ret[$index] = ftp_nb_fget($connID, $fp, $remote_file, FTP_BINARY);
        //_error_log("CDNStorage::put:uploadToCDNStorage SUCCESS [$index] {$remote_file} " . json_encode($_uploadInfo[$index]));
        return true;
    }

    private static function uploadToCDNStorage($local_path, $index, &$conn_id, &$ret) {
        global $_uploadInfo;
        if (!isset($_uploadInfo)) {
            $_uploadInfo = array();
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
        $_uploadInfo[$index] = array('microtime' => microtime(true), 'filesize' => $filesize, 'local_path' => $local_path, 'remote_file' => $remote_file);
        //_error_log("CDNStorage::put:uploadToCDNStorage " . __LINE__);
        $ret[$index] = ftp_nb_put($connID, $remote_file, $local_path, FTP_BINARY);
        //_error_log("CDNStorage::put:uploadToCDNStorage SUCCESS [$index] {$remote_file} " . json_encode($_uploadInfo[$index]));
        return true;
    }

    static function createDummyFiles($videos_id) {
        $msg = "createDummyFiles($videos_id) ";
        self::addToLog($videos_id, $msg);
        global $_getFilesListBoth, $_getFilesListRemote, $_getFilesList_CDNSTORAGE;
        unset($_getFilesListBoth);
        unset($_getFilesListRemote);
        unset($_getFilesList_CDNSTORAGE);
        
        $list = self::getFilesListBoth($videos_id);
        $filesAffected = 0;
        foreach ($list as $key => $value) {
            if (empty($value['local']) || empty($value['local']['local_filesize']) || $value['local']['local_filesize'] <= 20) {
                continue;
            } else if (@$value['local']['local_filesize'] == @$value['remote']['remote_filesize']) {
                $msg = "createDummyFiles {$value['local']['local_path']} ";
                self::addToLog($videos_id, $msg);
                self::createDummy($value['local']['local_path']);
                $filesAffected++;
            }
        }
        $msg = "createDummyFiles  filesAffected=$filesAffected";
        self::addToLog($videos_id, $msg);
        return $filesAffected;
    }

    static function sendSocketNotification($videos_id, $msg) {
        $v = Video::getVideoLight($videos_id);
        $users_id = $v['users_id'];
        if (!empty($users_id)) {
            $poster = Video::getPoster($videos_id);
            $img = "<img src='{$poster}' class='img img-responsive'>";
            sendSocketMessageToUsers_id($msg . '<br>' . $img, $users_id, 'socketCDNStorageMoved');
        }
    }

    private static function setProgress($videos_id, $isOnTheStorage, $finished) {
        self::setSite($videos_id, $isOnTheStorage);
        if ($finished) {
            Video::updateFilesize($videos_id);
            self::deleteLog($videos_id);
        }
    }

    static function isMoving($videos_id) {
        $file = self::getLogFile($videos_id);
        if (empty($file) || !file_exists($file)) {
            return false;
        }

        $modified = filemtime($file);
        if (time() - $modified > 300) {
            // if is laonger than 5 min say it is not moving
            _error_log('CDNStorage isMoving is taking too long to finish, check your connection speed or FTP errors', AVideoLog::$WARNING);
            return false;
        }

        return array('modified' => $modified, 'created' => filectime($file));
    }

    static function createDummy($file_path) {
        global $global;
        $path_parts = pathinfo($file_path);
        $extension = strtolower($path_parts['extension']);

        if ($extension == 'ts') {
            unlink($file_path);
        } else if (in_array($extension, CDNStorage::$allowedFiles)) {
            file_put_contents($file_path, 'Dummy File');
        } else {
            return false;
        }

        return true;
    }

    static function getFilesListLocal($videos_id, $skipDummyFiles = true) {
        global $global, $_getFilesList_CDNSTORAGE;
        if (!isset($_getFilesList_CDNSTORAGE)) {
            $_getFilesList_CDNSTORAGE = array();
        }
        if (!empty($_getFilesList_CDNSTORAGE[$videos_id])) {
            return $_getFilesList_CDNSTORAGE[$videos_id];
        }
        $obj = AVideoPlugin::getDataObject('CDN');
        $pz = self::getPZ();
        $files = self::getLocalFolder($videos_id);
        var_dump($videos_id, $files);exit;
        $video = Video::getVideoLight($videos_id);
        $filesList = array();
        $acumulative = 0;
        foreach ($files as $value) {
            if (is_array($value)) {
                foreach ($value as $value2) {
                    $file = self::getFilesListInfo($value2, $pz, $videos_id, $skipDummyFiles);
                    if (!empty($file)) {
                        $acumulative+= $file['local_filesize'];
                        $file['acumulativeFilesize'] = $acumulative;
                        $filesList[$file['relative']] = $file;
                    }
                }
            } else {
                $file = self::getFilesListInfo($value, $pz, $videos_id, $skipDummyFiles);
                if (!empty($file)) {
                    $acumulative+= $file['local_filesize'];
                    $file['acumulativeFilesize'] = $acumulative;
                    $filesList[$file['relative']] = $file;
                }
            }
        }
        $_getFilesList_CDNSTORAGE[$videos_id] = $filesList;
        return $filesList;
    }

    public function getAddress($filename) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->getDataObject();
        $file = Video::getPathToFile($filename);
        $address = array('path' => $file, 'url' => $this->getURL($filename));
        return $address;
    }

    static function getURL($filename) {
        global $global;

        // this is because sometimes I send filenames like this "videos/video_200721131007_6b3e/video_200721131007_6b3e_Low.mp4"
        if (preg_match('/^videos\\//', $filename)) {
            $parts = explode('/', $filename);
            if (count($parts) == 3) {
                $filename = $parts[2];
            }
        }

        $paths = Video::getPaths($filename);
        $file = $paths['path'] . $filename;
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
        } else {
            $relativeFilename = "{$paths['filename']}/{$filename}";
        }
        $obj = AVideoPlugin::getDataObject('CDN');
        $pz = self::getPZ();
        return "https://{$pz}{$relativeFilename}";
    }

    static function getLogFile($videos_id) {
        $video = Video::getVideoLight($videos_id);
        $path = Video::getPathToFile($video['filename']);

        $path .= '_cdnStorage.log';
        return $path;
    }

    static function addToLog($videos_id, $message) {
        if (isCommandLineInterface()) {
            echo $message . PHP_EOL;
        }
        _error_log($message);
        $file = self::getLogFile($videos_id);
        return file_put_contents($file, date('Y-m-d H:i:s: ') . $message . PHP_EOL, FILE_APPEND);
    }

    static function deleteLog($videos_id) {
        $file = self::getLogFile($videos_id);
        return unlink($file);
    }

    static function file_get_contents($remote_filename) {
        $obj = AVideoPlugin::getDataObject('CDN');
        $filename = "ftp://{$obj->storage_username}:{$obj->storage_password}@{$obj->storage_hostname}/{$remote_filename}";
        return file_get_contents($filename);
    }

}
