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
$path = "{$global['systemRootPath']}videos/{$file}";

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
    _error_log("XSENDFILE Cant read this configuration ");
    forbiddenPage("Cant read this");
}

if (file_exists($path)) {
    if (!empty($_GET['download'])) {
        if(!CustomizeUser::canDownloadVideos()){
            _error_log("downloadHLS: CustomizeUser::canDownloadVideos said NO");
            forbiddenPage("Cant download this");
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
        }
    } else {
        $advancedCustom->doNotUseXsendFile = true;
    }
    header("Content-type: " . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
    if (!empty($advancedCustom->doNotUseXsendFile)) {
        ini_set('memory_limit', filesize($path) * 1.5);
        _error_log("Your XSEND File is not enabled, it may slowdown your site, file = $path", AVideoLog::$WARNING);
        //echo url_get_contents($path);
        // stream the file
        $fp = fopen($path, 'rb');
        fpassthru($fp);
    }
    die();
} else {
    _error_log("XSENDFILE ERROR: Not exists {$path}");
}
