<?php

$CDNObj = AVideoPlugin::getDataObject('CDN');

function getConnID($index) {
    global $conn_id, $CDNObj;
    if (empty($conn_id[$index])) {
        $conn_id[$index] = ftp_connect($CDNObj->storage_hostname);
        if (empty($conn_id[$index])) {
            echo "getConnID trying again {$index}" . PHP_EOL;
            sleep(1);
            return getConnID($index);
        }
        // login with username and password
        $login_result = ftp_login($conn_id[$index], $CDNObj->storage_username, $CDNObj->storage_password);
        ftp_pasv($conn_id[$index], true);
    }
    return $conn_id[$index];
}

function ftp_mlsd_recursive($ftp_stream, $directory) {
    $result = [];

    $files = ftp_mlsd($ftp_stream, $directory);
    if ($files === false) {
        die("Cannot list $directory");
    }

    foreach ($files as $file) {
        $name = $file["name"];
        $filepath = $directory . "/" . $name;
        if (($file["type"] == "cdir") || ($file["type"] == "pdir")) {
            // noop
        } else if ($file["type"] == "dir") {
            $result = array_merge($result, ftp_mlsd_recursive($ftp_stream, $filepath));
        } else {
            $result[] = $filepath;
        }
    }
    return $result;
}
