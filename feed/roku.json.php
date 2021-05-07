<?php
header('Content-Type: application/json');
$cacheFeedName = "feedCacheROKU" . json_encode($_REQUEST);
$lifetime = 43200;
$output = ObjectYPT::getCache($cacheFeedName, $lifetime);
if (empty($output)) {
    $obj = new stdClass();
    $obj->providerName = $title;
    $obj->language = "en";
    $obj->lastUpdated = date('c');
    $obj->movies = array();

    $cacheName = "roju.json.movies";

    $movies = ObjectYPT::getCache($cacheName, 0);

    $categories = array();
    if (empty($movies)) {
        foreach ($rows as $row) {
            $videoSource = Video::getHigestResolution($row['filename']);
            if (empty($videoSource)) {
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
            $movie->categories_id = $row['categories_id'];

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

            if (empty($categories[$movie->categories_id])) {
                $categories[$movie->categories_id] = new stdClass();
                $categories[$movie->categories_id]->name = $movie->tags;
                $categories[$movie->categories_id]->query = $movie->tags;
                $categories[$movie->categories_id]->order = 'most_recent';
            }
        }
        ObjectYPT::setCache($cacheName, $obj->movies);
    } else {
        $obj->movies = $movies;
        foreach ($obj->movies as $movie) {
            if (empty($categories[$movie->categories_id])) {
                $categories[$movie->categories_id] = new stdClass();
                $categories[$movie->categories_id]->name = $movie->tags;
                $categories[$movie->categories_id]->query = $movie->tags;
                $categories[$movie->categories_id]->order = 'most_recent';
            }
        }
    }

    $obj->categories = array();
    foreach ($categories as $value) {
        $obj->categories[] = $value;
    }

    $output = json_encode($obj, JSON_UNESCAPED_UNICODE);
    if (json_last_error()) {
        $output = json_encode(json_last_error_msg());
    }
    ObjectYPT::setCache($cacheFeedName, $output);
}else{
    //echo '<!-- cache -->';
}
if(!is_string($output)){
    $output = json_encode($output);
}
die($output);
?>