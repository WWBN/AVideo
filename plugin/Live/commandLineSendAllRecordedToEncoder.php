<?php
/**
 * This script will get all the saved lives on remote servers ans send them to the recorder plugin
 */
if (php_sapi_name() !== 'cli') {
    return die('Command Line only');
}

$record_path = "/var/www/tmp/";
$liveServerURL = "encoder.gdrive.local"; // your live server URL
$streamerServerURL = "https://www.yourStreamerSite.tv/"; // your live server URL
$secretRecorderKey = "xxx"; // must match with the plugin parameter


$files = glob($record_path . '*.{flv}', GLOB_BRACE);
foreach ($files as $file) {
    echo PHP_EOL."Start $file ".PHP_EOL;
    $pattern = "/.*\/([a-z0-9]+)-([0-9]{2}-[a-zA-z]{3}-[0-9]{2}-[0-9]{2}:[0-9]{2}:[0-9]{2}).flv$/";
    preg_match($pattern, $file, $matches);
    if (!empty($matches[1])) {
        $filePath = $file;
        $postFields = array(
            "app" => "live",
            "tcurl" => "rtmp://$liveServerURL:1935/live",
            "name" => $matches[1],
            "path" => $filePath
        );
        $target = $streamerServerURL . "plugin/Live/on_record_done.php?secretRecorderKey={$secretRecorderKey}";
        echo "Sending to $target filesize=". humanFileSize(filesize($filePath))." ". json_encode($postFields).PHP_EOL;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $target);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $r = curl_exec($curl);
        if ($errno = curl_errno($curl)) {
            $error_message = curl_strerror($errno);
            //echo "cURL error ({$errno}):\n {$error_message}";
            echo " ---- cURL error ({$errno}):\n {$error_message} ".PHP_EOL;
        } else {
            echo " **** Success ".$r.PHP_EOL;
        }
        curl_close($curl);
    }else{
        echo "ERROR pattern does not match ".PHP_EOL;
    }
}

function humanFileSize($size, $unit = "") {
    if ((!$unit && $size >= 1 << 30) || $unit == "GB") {
        return number_format($size / (1 << 30), 2) . "GB";
    }

    if ((!$unit && $size >= 1 << 20) || $unit == "MB") {
        return number_format($size / (1 << 20), 2) . "MB";
    }

    if ((!$unit && $size >= 1 << 10) || $unit == "KB") {
        return number_format($size / (1 << 10), 2) . "KB";
    }

    return number_format($size) . " bytes";
}