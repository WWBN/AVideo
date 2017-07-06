<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"' . __("Permission denied") . '"}');
}

$type = !empty($_POST['type']) ? $_POST['type'] : "";

require_once 'video.php';
$obj = new Video("", "", $_POST['id']);
if (empty($obj)) {
    die("Object not found");
}

$fileName = "{$global['systemRootPath']}videos/original_{$obj->getFilename()}";
$obj2 = new stdClass();
if (file_exists($fileName)) {
    $obj->setStatus('e');
    $resp = $obj->save();
    $cmd = PHP_BINDIR.DS."php -f {$global['systemRootPath']}view/mini-upload-form/videoEncoder.php {$obj->getFilename()} {$obj->getId()} {$type} > /dev/null 2>/dev/null &";
    exec($cmd . " 2>&1", $output, $return_val);
    if ($return_val !== 0) {
        $obj2->status = false;
        $obj2->message = __("Error on re-encoding!");
        $obj2->command = $cmd;
        $obj2->output = $output;
        echo json_encode($obj2);
        error_log($cmd . "\n" . print_r($output, true));
    } else {
        $obj2->status = true;
        $obj2->message = __("Video re-encoding!");
        $obj2->command = $cmd;
        $obj2->output = $output;
        echo json_encode($obj2);
    }
} else {
    $obj2->status = false;
    $obj2->message = __("The original file for this video does not exists anymore");
    echo json_encode($obj2);
}

