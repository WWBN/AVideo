<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

session_write_close();
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

if (empty($_GET['file'])) {
    _error_log("XSENDFILE GET file not found ");
    die('GET file not found');
}

$path_parts = pathinfo($_GET['file']);
$file = $path_parts['basename'];


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

$path = Video::getPathToFile($file);
if (file_exists($path)) {
    if (!empty($_GET['download'])) {
        if(!CustomizeUser::canDownloadVideos()){
            _error_log("downloadHLS: CustomizeUser::canDownloadVideos said NO");
            forbiddenPage("Can't download this");
        }
        if (!empty($_GET['title'])) {
            $quoted = sprintf('"%s"', addcslashes(basename($_GET['title']), '"\\'));
        } else {
            $quoted = sprintf('"%s"', addcslashes(basename($_GET['file']), '"\\'));
        }
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
        }else{
            _error_log("Careful, we recommend you to use the X-Sendfile and it is disabled on AdvancedCustom plugin -> doNotUseXsendFile. You may have an error 'Allowed Memory Size Exhausted' if your video file is too big", AVideoLog::$WARNING);
        }
    } else {
        $advancedCustom->doNotUseXsendFile = true;
    }
    header("Content-type: " . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
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
    _error_log("XSENDFILE ERROR: Not exists {$path}");
}
