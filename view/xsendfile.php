<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

_session_write_close();
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

if (!empty($isStandAlone)) {
    $folder = preg_replace('/[^0-9a-z_-]/i', '', $_REQUEST['folder']);
    $file = preg_replace('/[^0-9a-z_.-]/i', '', $_REQUEST['file']);
    $path = "{$global['systemRootPath']}videos/$folder/$file";
    $filesize = filesize($path);
    header('Content-Description: File Transfer');
    //header('Content-Disposition: attachment; filename=' . $quoted);
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header("X-Sendfile: {$path}");
    header("Content-type: " . mime_content_type($path));
    header('Content-Length: ' . $filesize);
    exit;
}

if (empty($_GET['file'])) {
    _error_log("XSENDFILE GET file not found ");
    die('GET file not found');
}
if ($_GET['file'] == 'index.mp3') {
    $url = parse_url($_SERVER["REQUEST_URI"]);
    $paths = Video::getPaths($url["path"]);
    $path = "{$paths['path']}index.mp3";
    $file = "{$paths["relative"]}index.mp3";
    $path_parts = pathinfo($file);
    //var_dump(__LINE__, $file, $path, $paths);
} else if ($_GET['file'] == 'index.mp4') {
    $url = parse_url($_SERVER["REQUEST_URI"]);
    $paths = Video::getPaths($url["path"]);
    $path = "{$paths['path']}index.mp4";
    $file = "{$paths["relative"]}index.mp4";
    $path_parts = pathinfo($file);
    //var_dump(__LINE__, $file, $path, $paths);
} else if ($_GET['file'] == 'index_offline.mp4') {
    $url = parse_url($_SERVER["REQUEST_URI"]);
    $paths = Video::getPaths($url["path"]);
    $path = "{$paths['path']}index_offline.mp4";
    $file = "{$paths["relative"]}index_offline.mp4";
    $path_parts = pathinfo($file);
    //var_dump($paths);exit;
} else {
    $path_parts = pathinfo($_GET['file']);
    $file = $path_parts['basename'];
}
//header('Content-Type: application/json');var_dump($path, $file, $paths, $url, Video::getPaths($redirectURI));exit;

if ($file == "test.mp4") {
    $path = "{$global['systemRootPath']}view/xsendfile.html";
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Type: text/html');
    header('Content-Length: ' . filesize($path));
    header("X-Sendfile: {$path}");
    exit;
}

if ($file == "X-Sendfile.mp4") {
    $path = "{$global['systemRootPath']}plugin/SecureVideosDirectory/test.json";
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Type: application/json');
    header('Content-Length: ' . filesize($path));
    header("X-Sendfile: {$path}");
    exit;
}

if ($file == "configuration.php") {
    _error_log("XSENDFILE Can't read this configuration ");
    forbiddenPage("Can't read this");
}
if (!empty($_REQUEST['cacheDownload'])) {
    $file = preg_replace('/[^0-9a-z_\.]/i', '', $_GET['file']);
    $relativePath = "cache/download/";
    $path = getVideosDir() . $relativePath . $file;
    $_GET['download'] = 1;
    _error_log("cacheDownload: $path");
} else {
    $path = Video::getPathToFile($file);
    $folder = preg_replace('/[^a-z0-9_-]/i', '', @$_GET['folder']);
    if (!file_exists($path) && !empty($folder)) {
        $file = str_replace("videos/{$folder}/", '', $file);
        $path = "{$global['systemRootPath']}videos/{$folder}/{$file}";
    }
    //var_dump($path, $file, $_GET);exit;
}

if (!file_exists($path)) {
    if (preg_match('/.mp4/', $file)) {
        $path = str_replace($file, 'index.mp4', $path);
    }
}

//header('Content-Type: application/json');var_dump(__LINE__, $_SERVER["REQUEST_URI"], $file, $path);exit;
//header('Content-Type: application/json');var_dump($advancedCustom->doNotUseXsendFile);
if (file_exists($path)) {
    $filesize = filesize($path);
    if (!empty($_GET['download'])) {
        if (empty($_REQUEST['cacheDownload']) && !CustomizeUser::canDownloadVideos()) {
            _error_log("downloadHLS: CustomizeUser::canDownloadVideos said NO");
            forbiddenPage("Can't download this");
        }
        if (!empty($_GET['title'])) {
            $quoted = safeString($_GET['title'], true);
        } else {
            $quoted = safeString(basename($_GET['file']), true);
        }

        $quoted = preg_replace('/[^a-z0-9_.-]/i', '', $quoted);

        if (empty($quoted)) {
            $quoted = 'undefinedName';
        }

        if (!preg_match('/\.' . $path_parts['extension'] . '$/i', $quoted)) {
            $quoted = "{$quoted}.{$path_parts['extension']}";
        }

        //var_dump(__LINE__, $quoted, $path_parts);exit;
        //header('Content-Type: application/json');var_dump($quoted);exit;
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $quoted);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    }
    if (preg_match("/(mp4|webm|m3u8|mp3|ogg)/i", $path_parts['extension'])) {
        if (isAVideoEncoderOnSameDomain() || empty($_GET['ignoreXsendfilePreVideoPlay'])) {
            AVideoPlugin::xsendfilePreVideoPlay();
        }
        if (empty($advancedCustom->doNotUseXsendFile)) {
            //_error_log("X-Sendfile: {$path}");
            header("X-Sendfile: {$path}");
        } else {
            _error_log("Careful, we recommend you to use the X-Sendfile and it is disabled on AdvancedCustom plugin -> doNotUseXsendFile. You may have an error 'Allowed Memory Size Exhausted' if your video file is too big", AVideoLog::$WARNING);
        }
    } else {
        $advancedCustom->doNotUseXsendFile = true;
    }
    header("Content-type: " . mime_content_type($path));
    header('Content-Length: ' . $filesize);
    //header("Content-Range: 0-".($filesize-1)."/".$filesize);
    //_error_log("downloadHLS: filesize={$filesize} {$path}");
    //var_dump($advancedCustom->doNotUseXsendFile);exit;
    if (!empty($advancedCustom->doNotUseXsendFile)) {
        ini_set('memory_limit', filesize($path) * 1.5);
        _error_log("Your XSEND File is not enabled, it may slow down your site, file = $path", AVideoLog::$WARNING);
        //echo url_get_contents($path);
        // stream the file
        $fp = fopen($path, 'rb');
        fpassthru($fp);
    }
    die();
} else {
    _error_log("XSENDFILE ERROR: Not exists path={$path} file={$file} " . json_encode($_GET));
}
