<?php

if (empty($_GET['lang'])) {
    return '';
}

$lang = preg_replace('/[^a-z0-9_]/i', '', $_GET['lang']);

$langFile = "./{$lang}.php";
if(!file_exists($langFile)){
    return '';
}

include_once $langFile;
header('Content-Type: application/javascript');
?>
var translations = <?php echo json_encode($t); ?>;
