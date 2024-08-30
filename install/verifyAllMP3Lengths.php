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
            @unlink($result['mp3Path']);
        }

        for ($i=0; $i <= 3 ; $i++) { 
            $response = convertVideoToMP3FileIfNotExists($value['id'], $i);
            $result2 = Video::isMP3LengthValid($value['id']);
            if ($result2['isValid']) {
                break;
            }else{
                @unlink($result['mp3Path']);
                echo "ERROR Videos_id={$value['id']} File still invalid, try again $i {$result2['msg']} " . PHP_EOL .json_encode($global['lastFFMPEG'], JSON_PRETTY_PRINT) . PHP_EOL;
            }
        }

    }
}

echo "Bye";
echo "\n";
die();
