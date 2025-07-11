<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
$global['doNotPrintLogs'] = 1;
ob_end_flush();

function download($url, $filename, $path, $forceDownload = false) {
    if(empty($url) || preg_match('/view.img/', $url)){
        echo ("importChannel::download ERROR on url {$url}").PHP_EOL;
        return false;
    }
    $parts = explode("/{$filename}/", $url);

    if (empty($parts[1])) {
        if (preg_match("/\.mp3$/", $url)) {
            $parts[1] = "{$filename}.mp3";
        }else if (preg_match("/s3\.cdn\.ypt\.me.*\.mp4$/", $url)) {
            $partsCDN = explode("s3.cdn.ypt.me/", $url);
            $parts[1] = $partsCDN[1];
        }
    }

    if (empty($parts[1])) {
        echo ("importChannel::download ERROR on download filename=$filename {$url}").PHP_EOL;
        return false;
    }

    $parts2 = explode('?', $parts[1]);
    $file = $parts2[0];
    $destination = $path . $file;
    if ($forceDownload || !file_exists($destination)) {
        echo ("importChannel::download [$destination]").PHP_EOL;
        return wget($url, $destination, true);
    } else {
        echo ("importChannel::download skipped [$destination]").PHP_EOL;
    }
    return false;
}

function getChannel() {
    while (empty($channelName)) {
        while (empty($channelURL) || !isValidURL($channelURL)) {
            echo 'Enter a Channel URL to import: ';
            $channelURL = trim(readline(""));
        }

        $parts = explode('/channel/', $channelURL);

        $siteURL = addLastSlash($parts[0]);
        $channelName = urlencode($parts[1]);

        if (empty($channelName)) {
            echo 'Enter a valid channel URL'.PHP_EOL;
        }else{
            return array('channelName'=>$channelName,'channelURL'=>$channelURL,'siteURL'=>$siteURL);
        }
    }
}

set_time_limit(360000);
ini_set('max_execution_time', 360000);

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;

$rowCount = 50;
$current = 1;
$hasNewContent = true;
$totalVideosImported = 0;

echo PHP_EOL.PHP_EOL;
echo '****************************************Channel Import Tool****************************************'.PHP_EOL;
echo 'This script will import all videos from a specific channel to a specific user account using the API (so the API MUST be enabled on the source site channel).'.PHP_EOL;
echo 'The final result will depend on the video type'.PHP_EOL;
echo '- MP4 or MP3 we will also download the video'.PHP_EOL;
echo '- All other formats we will only import the database metadata poster and images'.PHP_EOL;
echo '- We cannot download the HLS video, so to make it work you will need to copy the video folder itself, do not rename the folder because we always import it with the same filename'.PHP_EOL;
echo '- Embed videos should work with no problem'.PHP_EOL;
echo 'Initially, we will ask you for the URL of an AVideo channel and then we will ask you for a user id.'.PHP_EOL;
echo 'If you do not know the id we will offer you a search to facilitate the location'.PHP_EOL;
echo 'The category will be the site\'s default'.PHP_EOL;
echo 'If you run the script again, we will NOT duplicate the video but we will update the metadata with the source site channel '.PHP_EOL;
echo '****************************************Channel Import Tool****************************************'.PHP_EOL;
echo PHP_EOL.PHP_EOL;

$info = getChannel();
$siteURL = $info['siteURL'];
$channelName = $info['channelName'];
$channelURL = $info['channelURL'];

echo ("importChannel: start {$siteURL} {$channelName}").PHP_EOL;


$users_id = 0;
while (empty($users_id)) {
    $_GET['searchPhrase'] = '';
    echo 'Enter username or user ID:';
    $users_id = trim(readline(""));
    if(is_numeric($users_id)){
        $users_id = intval($users_id);
    }else if(!empty($users_id)){
        $_GET['searchPhrase'] = $users_id;
        $rows = User::getAllUsers(true);
        $users_id = 0;
        echo 'We found those users options:'.PHP_EOL;
        foreach ($rows as $roow) {
            echo "id={$roow['id']} user={$roow['user']} email={$roow['email']}".PHP_EOL;
        }
    }
}

echo 'Do you want to limit the max of videos imported? just press enter for unlimited:';
$maxVideosImported = intval(readline(""));

while ($hasNewContent && !empty($users_id)) {

    $APIURL = "{$siteURL}plugin/API/get.json.php?APIName=video&channelName=$channelName&rowCount={$rowCount}&current={$current}";

    $content = url_get_contents($APIURL, "", 30);

    $hasNewContent = false;
    $current++;

    if (!empty($content)) {
        echo ("importChannel: SUCCESS {$APIURL}").PHP_EOL;
        $json = _json_decode($content);
        if (!empty($json) && !empty($json->response) && !empty($json->response->totalRows) && !empty($json->response->rows)) {
            echo ("importChannel: JSON SUCCESS totalRows={$json->response->totalRows}").PHP_EOL;
            $hasNewContent = true;
            foreach ($json->response->rows as $key => $value) {
                if(!empty($maxVideosImported) && $totalVideosImported>=$maxVideosImported){
                    echo PHP_EOL.'****************************************Channel Import Limit reached****************************************'.PHP_EOL;
                    echo ("You have set the maximum of {$maxVideosImported} videos").PHP_EOL;
                    exit;
                }
                $totalVideosImported++;
                $videos_id = 0;
                echo PHP_EOL;
                $row = Video::getVideoFromFileNameLight($value->filename);
                if (!empty($row)) {
                    echo ("importChannel: [{$totalVideosImported}] Video {$row['id']} {$value->title} {$value->fileName}").PHP_EOL;
                    $videos_id = $row['id'];
                } else {
                    echo ("importChannel: Video NOT found").PHP_EOL;
                }

                $video = new Video($value->title, $value->filename, $videos_id);

                $video->setCreated("'$value->created'");
                $video->setDuration($value->duration);
                $video->setDescription($value->description);
                $video->setType($value->type);
                $video->setVideoDownloadedLink($value->videoDownloadedLink);
                $video->setVideoLink($value->videoLink);
                $video->setDuration_in_seconds($value->duration_in_seconds);
                $video->setDescription($value->description);
                $video->setUsers_id($users_id);
                $video->setRrating($value->rrating);
                $video->setExternalOptions($value->externalOptions);
                $video->setStatus(Video::STATUS_TRANFERING);

                echo ("importChannel: Saving video").PHP_EOL;
                $id = $video->save(false, true);
                if ($id) {
                    echo ("importChannel: Video saved {$id} downloading poster and images ...").PHP_EOL;
                    $path = getVideosDir() . $value->filename . DIRECTORY_SEPARATOR;
                    make_path($path);

                    // download images
                    download($value->images->poster, $value->filename, $path);
                    download($value->images->thumbsGif, $value->filename, $path);

                    foreach ($value->videos->mp4 as $key2 => $value2) {
                        //echo ("importChannel MP4: key = {$key} key2 = {$key2} APIURL = $APIURL");
                        download($value2, $value->filename, $path);
                    }

                    if (!empty($value->videos->mp3)) {
                        //echo ("importChannel MP3: {$value->videos->mp3} APIURL = $APIURL");
                        download($value->videos->mp3, $value->filename, $path);
                    }

                    $video->setStatus(Video::STATUS_ACTIVE);
                } else {
                    echo ("importChannel: ERROR Video NOT saved").PHP_EOL;
                    $video->setStatus(Video::STATUS_BROKEN_MISSING_FILES);
                }
                $video->save(false, true);
                //exit;
            }
        } else {
            echo ("importChannel: JSON ERROR {$content} ").PHP_EOL;
        }
    } else {
        echo ("importChannel: ERROR {$APIURL} content is empty, check if the API plugin is enabled").PHP_EOL;
    }
}
echo PHP_EOL.'****************************************Channel Import Finished****************************************'.PHP_EOL;
die();
