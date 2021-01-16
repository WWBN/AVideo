<?php

$obj = new stdClass();
$obj->providerName = $title;
$obj->language = "en";
$obj->lastUpdated = date('c');
$obj->movies = array();
$categories = array();
foreach ($rows as $row) {  
    $videoSource = Video::getHigestResolution($row['filename']);
    if(empty($videoSource)){
        continue;
    }
    $movie = new stdClass();
    $movie->id = Video::getLinkToVideo($row['id'], $row['clean_title'], false, "permalink");
    $movie->title = UTF8encode($row['title']);
    $movie->longDescription = "=> " . _substr(strip_tags(br2nl(UTF8encode($row['description']))), 0, 490);
    $movie->shortDescription = _substr($movie->longDescription, 0, 200);
    $movie->thumbnail = Video::getRokuImage($row['id']);
    $movie->tags = _substr(UTF8encode($row['category']), 0, 20);
    $movie->genres = array("special");
    $movie->releaseDate = date('c', strtotime($row['created']));

    $content = new stdClass();
    $content->dateAdded = date('c', strtotime($row['created']));
    $content->captions = array();
    $content->duration = durationToSeconds($row['duration']);
    $content->language = "en";
    $content->adBreaks = array("00:00:00");

    $video = new stdClass();
    $video->url = $videoSource["url"];
    $video->quality = "HD";
    $video->videoType = Video::getVideoTypeText($row['filename']);
    $content->videos = array($video);
    
    $movie->content = $content;
            
    $obj->movies[] = $movie;
    
    if(empty($categories[$row['categories_id']])){
        $categories[$row['categories_id']] = new stdClass();
        $categories[$row['categories_id']]->name = $movie->tags;
        $categories[$row['categories_id']]->query = $movie->tags;
        $categories[$row['categories_id']]->order = 'most_recent';
    }
    
}

$obj->categories = array();
foreach ($categories as $value) {
    $obj->categories[] = $value;
}

$output = json_encode($obj, JSON_UNESCAPED_UNICODE );
if(json_last_error()){
    $output = json_encode(json_last_error_msg());
}

die($output);
?>