<?php

require_once '../../videos/configuration.php';
session_write_close();
$filename = $global['systemRootPath'] . 'plugin/Live/view/OnAir.jpg';
//echo file_get_contents($filename);exit;

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';

$liveLink = new LiveLinksTable($_GET['id']);
if (empty($_GET['format'])) {
    $_GET['format'] = "png";
    header('Content-Type: image/x-png');
} else if ($_GET['format'] === 'jpg') {
    header('Content-Type: image/jpg');
} else if ($_GET['format'] === 'gif') {
    header('Content-Type: image/gif');
} else if ($_GET['format'] === 'webp') {
    header('Content-Type: image/webp');
} else {
    $_GET['format'] = "png";
    header('Content-Type: image/x-png');
}
$video = $liveLink->getLink();

if (preg_match("/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $video)) {
    $url = $config->getEncoderURL() . "getImage/" . base64_encode($video) . "/{$_GET['format']}";
    if (empty($_SESSION[$url]['expire']) || $_SESSION[$url]['expire'] < time()) {
        _error_log("LiveLink: getImage.php: " . $url);
        $content = url_get_contents($url);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        _error_log(" Image Expired in " . date("d/m/Y H:i:s", @$_SESSION[$url]['expire']) . " NOW is " . date("d/m/Y H:i:s"));
        $_SESSION[$url] = array('content' => $content, 'expire' => strtotime("+2 min"));
        _error_log(" New Image will Expired in " . date("d/m/Y H:i:s", $_SESSION[$url]['expire']) . " NOW is " . date("d/m/Y H:i:s"));
    }
    if (!empty($_SESSION[$url]['content'])) {
        ob_end_clean();
        echo $_SESSION[$url]['content'];
        _error_log(" Cached Good until " . date("d/m/Y H:i:s", $_SESSION[$url]['expire']) . " NOW is " . date("d/m/Y H:i:s"));
    } else {
        ob_end_clean();
        echo url_get_contents($filename);
        _error_log(" Get default image ");
    }
} else {
    ob_end_clean();
    echo local_get_contents($filename);
    _error_log(" Invalid URL ");
}
$p = AVideoPlugin::loadPluginIfEnabled("Cache");
if (!empty($p)) {
    $p->getEnd();
}