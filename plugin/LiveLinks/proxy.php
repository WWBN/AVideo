<?php

require_once '../../videos/configuration.php';
session_write_close();
try {
    $global['mysqli']->close();
} catch (Exception $exc) {
    //echo $exc->getTraceAsString();
}


/*
 * this file is to handle HTTP URLs into HTTPS
 */
if (!filter_var($_GET['livelink'], FILTER_VALIDATE_URL) || !preg_match("/^http.*/i", $_GET['livelink'])) {
    echo "Invalid Link";
    exit;
}
header("Content-Type: video/vnd.mpegurl");
header("Content-Disposition: attachment;filename=playlist.m3u");

$options = array(
    'http' => array(
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        "method" => "GET",
        "header" => array("Referer: localhost\r\nAccept-languange: en\r\nCookie: foo=bar\r\n")
    )
);
$context = stream_context_create($options);

$_GET['livelink'] = addGlobalTokenIfSameDomain($_GET['livelink']);

$headers = get_headers($_GET['livelink'], 1, $context);
if (!empty($headers["Location"])) {
    $_GET['livelink'] = $headers["Location"];
    $urlinfo = parse_url($_GET['livelink']);
    $content = fakeBrowser($_GET['livelink']);
    $_GET['livelink'] = "{$urlinfo["scheme"]}://{$urlinfo["host"]}:{$urlinfo["port"]}";
} else {
    $content = fakeBrowser($_GET['livelink']);
    $pathinfo = pathinfo($_GET['livelink']);
}
if($content === "Empty Token"){
    die("Empty Token on URL {$_GET['livelink']}");
}else{
    foreach (preg_split("/((\r?\n)|(\r\n?))/", $content) as $line) {
        $line = trim($line);
        if (!empty($line) && $line[0] !== "#") {
            if (!filter_var($line, FILTER_VALIDATE_URL)) {
                if (!empty($pathinfo["extension"])) {
                    $_GET['livelink'] = str_replace($pathinfo["basename"], "", $_GET['livelink']);
                }
                $line = $_GET['livelink'] . $line;
            }
        }
        echo $line . PHP_EOL;
    } 
}
