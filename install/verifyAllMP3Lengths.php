<?php
//streamer config
require_once '../videos/configuration.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    return die('Command Line only');
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$videos = Video::getAllVideosLight('', false, true);

foreach ($videos as $value) {
    $result = Video::isMP3LengthValid($value['id']);
    if (!$result['isValid']) {
        echo "Converting Videos_id={$value['id']} {$value['title']} - reason:{$result['msg']}" . PHP_EOL;
        if (!empty($result['mp3Path'])) {
            unlink($result['mp3Path']);
        }
        convertVideoToMP3FileIfNotExists($value['id']);
        $result2 = Video::isMP3LengthValid($value['id']);
        if (!$result2['isValid']) {
            echo "ERROR Videos_id={$value['id']} File still invalid, try again" . PHP_EOL;
            convertVideoToMP3FileIfNotExists($value['id'], 1);
            $result3 = Video::isMP3LengthValid($value['id']);
            if (!$result3['isValid']) {
                echo "ERROR Videos_id={$value['id']} " . json_encode(array($result2, $global['lasfFFMPEG'])) . PHP_EOL;
            }
        }
    }
}

echo "Bye";
echo "\n";
die();
