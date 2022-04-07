<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ob_end_flush();

function download($url, $filename, $path, $forceDownload=false){
    $parts = explode("/{$filename}/", $url);
    $parts2 = explode('?', $parts[1]);
    $file = $parts2[0];
    $destination = $path.$file;
    if($forceDownload || !file_exists($destination)){
        _error_log("importChannel::download [$destination]");
        return wget($url, $destination, true);
    }else{
        _error_log("importChannel::download skipped [$destination]");
    }
    return false;
}

set_time_limit(1800);
ini_set('max_execution_time', 1800);

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;

$channelURL = trim(@$argv[1]);

if(empty($channelURL) || !isValidURL($channelURL)){
    echo 'Enter a valid URL';
    exit;
}

$channelURL = 'https://vod.lifestream.tv/channel/Fcccbmt';

$parts = explode('/channel/', $channelURL);

$siteURL = addLastSlash($parts[0]);
$channelName = urlencode($parts[1]);
$rowCount = 10;
$current = 1;

if(empty($channelName)){
    echo 'Enter a valid channel URL';
    exit;
}

$hasNewContent = true;

_error_log("importChannel: start {$siteURL} {$channelName}");

while($hasNewContent){
    
    $APIURL = "{$siteURL}plugin/API/get.json.php?APIName=video&channelName=$channelName&rowCount={$rowCount}&current={$current}";
    
    $content = url_get_contents($APIURL, "", 30);    
    
    $hasNewContent = false;
    $current++;
    
    if(!empty($content)){
        _error_log("importChannel: SUCCESS {$APIURL}");
        $json = _json_decode($content);
        if(!empty($json) && !empty($json->response) && !empty($json->response->totalRows)){
            _error_log("importChannel: JSON SUCCESS totalRows={$json->response->totalRows}");
            $hasNewContent = true;
            foreach ($json->response->rows as $key => $value) {
                
                $videos_id = 0;
                
                $row = Video::getVideoFromFileNameLight($value->filename);
                if(!empty($row)){
                    _error_log("importChannel: Video found");
                    $videos_id = $row['id'];
                }else{
                    _error_log("importChannel: Video NOT found");
                }
                _error_log("importChannel: Video {$videos_id} {$value->title} {$value->fileName}");
                
                $video = new Video($value->title, $value->filename, $videos_id);
                
                $video->setCreated("'$value->created'");
                $video->setDuration($value->duration);
                $video->setType($value->type);
                $video->setVideoDownloadedLink($value->videoDownloadedLink);
                $video->setDuration_in_seconds($value->duration_in_seconds);
                $video->setDescription($value->description);
                $video->setUsers_id(1);
                $video->setStatus(Video::$statusTranfering);
                
                _error_log("importChannel: Saving video");
                $id = $video->save(false, true);
                if($id){
                    _error_log("importChannel: Video saved {$id}");
                    $path = getVideosDir().$value->filename.DIRECTORY_SEPARATOR;
                    make_path($path);
                    
                    // download images
                    download($value->images->poster, $value->filename, $path);
                    download($value->images->thumbsGif, $value->filename, $path);
                                        
                    foreach ($value->videos->mp4 as $key2=>$value2) {
                        _error_log("importChannel: key = {$key} key2 = {$key2} APIURL = $APIURL");                        
                        download($value2, $value->filename, $path);
                    }
                    $video->setStatus(Video::$statusActive);
                }else{
                    _error_log("importChannel: ERROR Video NOT saved");
                    $video->setStatus(Video::$statusBrokenMissingFiles);
                }
                $video->save(false, true);
                //exit;
                
            }
        }else{
            _error_log("importChannel: JSON ERROR {$content} ");
        }
    }else{ 
        _error_log("importChannel: ERROR {$APIURL} content is empty");
    }
    
    
}

die();
