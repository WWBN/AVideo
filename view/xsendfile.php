<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
session_write_close();
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';

if (empty($_GET['file'])) {
    die('GET file not found');
}

function send_video($file) {
    # Nginx don't have PATH_INFO
    if (!isset($_SERVER['PATH_INFO'])) {
        $_SERVER['PATH_INFO'] = substr($_SERVER["ORIG_SCRIPT_FILENAME"], strlen($_SERVER["SCRIPT_FILENAME"]));
    }
    //$request = substr($_SERVER['PATH_INFO'], 1);
    //$file = $request;
    $fp = @fopen($file, 'rb');
    $size = filesize($file); // File size
    $length = $size;           // Content length
    $start = 0;               // Start byte
    $end = $size - 1;       // End byte
    header('Content-type: video/mp4');
    header("Accept-Ranges: 0-$length");
    if (isset($_SERVER['HTTP_RANGE'])) {
        $c_start = $start;
        $c_end = $end;
        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        if (strpos($range, ',') !== false) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        if ($range == '-') {
            $c_start = $size - substr($range, 1);
        } else {
            $range = explode('-', $range);
            $c_start = $range[0];
            $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
        }
        $c_end = ($c_end > $end) ? $end : $c_end;
        if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            exit;
        }
        $start = $c_start;
        $end = $c_end;
        $length = $end - $start + 1;
        fseek($fp, $start);
        header('HTTP/1.1 206 Partial Content');
    }
    header("Content-Range: bytes $start-$end/$size");
    header("Content-Length: " . $length);
    $buffer = 1024 * 8;
    while (!feof($fp) && ($p = ftell($fp)) <= $end) {
        if ($p + $buffer > $end) {
            $buffer = $end - $p + 1;
        }
        set_time_limit(0);
        echo fread($fp, $buffer);
        flush();
    }
    fclose($fp);
}

$path_parts = pathinfo($_GET['file']);
$file = $path_parts['basename'];
$path = "{$global['systemRootPath']}videos/{$file}";
if (file_exists($path)) {
    if (!empty($_GET['download'])) {
        $quoted = sprintf('"%s"', addcslashes(basename($_GET['file']), '"\\'));
        $size = filesize($file);
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $quoted);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    }
    YouPHPTubePlugin::xsendfilePreVideoPlay();
    $advancedCustom = YouPHPTubePlugin::getObjectDataIfEnabled("CustomizeAdvanced");
    if (empty($advancedCustom->doNotUseXsendFile)) {
        header("X-Sendfile: {$path}");
    }
    if (empty($_GET['download'])) {
        header("Content-type: " . mime_content_type($path));
    }
    header('Content-Length: ' . filesize($path));
    if (!empty($advancedCustom->doNotUseXsendFile)) {
        if (strtolower($path_parts['extension']) === "mp4" || strtolower($path_parts['extension']) === "webm") {
            // Not working yet
            //send_video($path);
            echo url_get_contents($path);
        } else {
            echo url_get_contents($path);
        }
    }
    die();
}
