<?php

function rokuRating($avideoRating){
    //('', 'g', 'pg', 'pg-13', 'r', 'nc-17', 'ma');
    switch (strtolower($avideoRating)) {
        case 'g':
            return 'G';
            break;
        case 'pg':
            return 'PG';
            break;
        case 'pg-13':
            return 'PG13';
            break;
        case 'r':
            return 'R';
            break;
        case 'nc-17':
            return 'NC17';
            break;
        case 'ma':
            return '18+';
            break;
        default:
            return 'G';
            break;
    }
}

header('Content-Type: application/json');
$cacheFeedName = "feedCache_ROKU" . json_encode($_REQUEST);
$lifetime = 43200;
$output = ObjectYPT::getCache($cacheFeedName, $lifetime);
if (empty($output)) {
    $obj = new stdClass();
    $obj->providerName = $title;
    $obj->language = "en";
    $obj->lastUpdated = date('c');
    $obj->movies = array();

    $cacheName = "feedCache_ROKU_movies".json_encode($_REQUEST);

    $movies = ObjectYPT::getCache($cacheName, 0);
    
    if (empty($movies)) {
        foreach ($rows as $row) {
            $videoSource = Video::getSourceFileURL($row['filename']);
            $videoResolution = Video::getResolutionFromFilename($videoSource);
            //var_dump($videoSource);
            if (empty($videoSource)) {
                _error_log("Roku Empty video source {$row['id']}, {$row['clean_title']}, {$row['filename']}");
                continue;
            }            
            
            $movie = new stdClass();
            $movie->id = 'video_'.$row['id'];
            $movie->title = UTF8encode($row['title']);
            $movie->longDescription = "=> " . _substr(strip_tags(br2nl(UTF8encode($row['description']))), 0, 490);
            $movie->shortDescription = _substr($movie->longDescription, 0, 200);
            $movie->thumbnail = Video::getRokuImage($row['id']);
            $movie->tags = array(_substr(UTF8encode($row['category']), 0, 20));
            $movie->genres = array("special");
            $movie->releaseDate = date('c', strtotime($row['created']));
            $movie->categories_id = $row['categories_id'];
            $rrating = $row['rrating'];
            if(!empty($rrating)){
                $movie->rating = new stdClass();
                $movie->rating->rating = rokuRating($rrating);
                $movie->rating->ratingSource = 'MPAA';
            }
            
            
            $content = new stdClass();
            $content->dateAdded = date('c', strtotime($row['created']));
            $content->captions = array();
            $content->duration = durationToSeconds($row['duration']);
            $content->language = "en";
            $content->adBreaks = array("00:00:00");

            $video = new stdClass();
            $video->url = $videoSource;
            $video->quality = getResolutionTextRoku($videoResolution);
            $video->videoType = Video::getVideoTypeText($row['filename']);
            $content->videos = array($video);

            $movie->content = $content;

            $obj->movies[] = $movie;

        }
        ObjectYPT::setCache($cacheName, $obj->movies);
    } else {
        $obj->movies = $movies;
    }

    
    $itemIds = array();
    foreach ($obj->movies as $value) {
        $itemIds[] = $value->id;
    }
    $obj->playlists = array(array('name'=>'all', 'itemIds'=>$itemIds));
    
    $obj->categories = array(array('name'=>'All', 'playlistName'=>'all', 'order'=>'most_recent'));

    $output = _json_encode($obj, JSON_UNESCAPED_UNICODE);
    if (empty($output) && json_last_error()) {
        $output = json_encode(json_last_error_msg());
        var_dump($obj);
    }else{
        ObjectYPT::setCache($cacheFeedName, $output);
    }
}else{
    //echo '<!-- cache -->';
}
if(!is_string($output)){
    $output = json_encode($output);
}
die($output);
?>