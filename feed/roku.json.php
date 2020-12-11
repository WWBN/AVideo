<?php

$obj = new stdClass();
$obj->providerName = $title;
$obj->language = "en-us";
$obj->lastUpdated = date('r');
$obj->movies = array();
foreach ($rows as $row) {
    $movie = new stdClass();
    $movie->id = Video::getLinkToVideo($row['id'], $row['clean_title'], false, "permalink");
    $movie->title = $row['title'];
    $movie->longDescription = "=> " . substr(strip_tags(br2nl($row['description'])), 0, 490);
    $movie->shortDescription = substr($movie->longDescription, 0, 200);
    $movie->thumbnail = Video::getRokuImage($row['id']);
    $movie->genres = array("special");
    $movie->releaseDate = date('r', strtotime($row['created']));

    $content = new stdClass();
    $content->dateAdded = date('r', strtotime($row['created']));
    $content->captions = new stdClass();
    $content->duration = durationToSeconds($row['duration']);
    $content->language = "en";
    $content->adBreaks = array("00:00:00");

    $video = new stdClass();
    $video->url = Video::getLinkToVideo($row['id'], $row['clean_title'], false, "permalink");
    $video->quality = "HD";
    $video->videoType = Video::getVideoTypeText($row['filename']);
    $content->videos = array($video);
    
    $movie->content = array($content);
            
    $obj->movies[] = $movie;
}

die(json_encode($obj));
?>