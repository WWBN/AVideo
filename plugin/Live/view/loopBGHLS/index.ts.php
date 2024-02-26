<?php

header("Content-Type: video/mp2t");

if (empty($_GET['res'])) {
    $_GET['res'] = 240;
}
$_GET['res'] = intval($_GET['res']);
if ($_GET['seq']%2) {
    $filename = "res{$_GET['res']}/index1.ts";
} else {
    $filename = "res{$_GET['res']}/index0.ts";
}
echo file_get_contents($filename);
