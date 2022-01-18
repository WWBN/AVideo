<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
session_write_close();

if (empty($_REQUEST['token'])) {
    die('Token is empty');
}

$token = decryptString($_REQUEST['token']);

if (empty($token)) {
    die('Token is invalid');
}

$json = json_decode($token);

if (empty($json)) {
    die('Error on decrypt token');
}

if ($json->valid < time()) {
    die('Token expired');
}

//var_dump($json);exit;
$url = str_replace('.m3u8', '.'.$json->format, $json->url);
$parts1 = explode('cdn.ypt.me/', $url);

//$parts0 = explode('?', $url);
//$url = $parts0[0];
$url = addQueryStringParameter($url,'download', 1);

if (empty($parts1[1])) {
    die('Invalid filename');
}

$parts2 = explode('?', $parts1[1]);

$relativeFilename = $parts2[0];

$file_exists = CDNStorage::file_exists_on_cdn($relativeFilename);

if ($file_exists) {
    header("Location: {$url}");
    exit;
} else {
    $localFile = getVideosDir() . "{$relativeFilename}";
    //var_dump($localFile);exit;
    if (!file_exists($localFile)) {
        if($json->format == 'mp3'){
            $command = get_ffmpeg() . " -i \"{$json->url}\" -map 0:a -acodec libmp3lame \"{$localFile}\"";
        }else{
            $command = get_ffmpeg() . " -i \"{$json->url}\" -c copy \"{$localFile}\"";
        }
        _error_log('download from CDN ' . $command);
        exec($command, $output);
        _error_log('download from CDN output: ' . json_encode($output));
    }
    if (!file_exists($localFile)) {
        _error_log('download from CDN file not created ' . $localFile);
    } else {
        /*
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $json->title);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header("X-Sendfile: {$localFile}");
        header("Content-type: " . mime_content_type($localFile));
        header('Content-Length: ' . filesize($localFile));
         * 
         */
        $client = CDNStorage::getStorageClient();
        $client->put($relativeFilename, $localFile);
        unlink($localFile);
        header("Location: {$url}");
        exit;
    }
}


//var_dump($relativeFilename, $url, $json);