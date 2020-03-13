<?php

/*
 * this file is to handle HTTP URLs into HTTPS
 */
if (!filter_var($_GET['livelink'], FILTER_VALIDATE_URL)) {
    echo "Invalid Link";
    exit;
}
$url = parse_url($_GET['livelink']);

if ($url['scheme'] == 'https') {
    header("Location: {$_GET['livelink']}");
} else {
    echo url_get_contents($_GET['livelink']);
}
