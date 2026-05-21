<?php
// No allowOrigin() call: this endpoint is consumed by same-origin JavaScript
// only (see view/js/session.js). Omitting CORS headers means the browser's
// same-origin policy already blocks cross-origin reads, preventing any
// third-party site from fetching the session ID via a credentialed request.
header('Content-Type: application/json');

function getAVideoSessionNameFromConfig()
{
    $configFile = __DIR__ . '/../videos/configuration.php';
    $systemRootPath = '';

    if (is_readable($configFile)) {
        $config = file_get_contents($configFile);
        if (preg_match('/\$global\s*\[\s*[\'"]systemRootPath[\'"]\s*\]\s*=\s*([\'"])(.*?)\1\s*;/', $config, $matches)) {
            $systemRootPath = stripcslashes($matches[2]);
        }
    }

    if (empty($systemRootPath)) {
        $realPath = realpath(__DIR__ . '/..');
        if (!empty($realPath)) {
            $systemRootPath = str_replace('\\', '/', $realPath);
        }
    }

    if (!empty($systemRootPath)) {
        $systemRootPath .= (substr($systemRootPath, -1) == '/' ? '' : '/');
        return md5($systemRootPath);
    }

    return session_name();
}

$sessionName = getAVideoSessionNameFromConfig();

$obj = new stdClass();
$obj->phpsessid = '';
if (!empty($_COOKIE[$sessionName])) {
    $obj->phpsessid = $_COOKIE[$sessionName];
} elseif (!empty($_COOKIE[session_name()])) {
    $obj->phpsessid = $_COOKIE[session_name()];
}

echo json_encode($obj);
