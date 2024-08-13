<?php

require_once __DIR__.'/rokuFunctions.php';

if(empty($rows)){
    $rows = array();
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
    $obj->movies = [];

    $cacheName = "feedCache_ROKU_movies".json_encode($_REQUEST);

    $movies = ObjectYPT::getCache($cacheName, 0);

    if (empty($movies)) {
        foreach ($rows as $row) {
            $movie = rowToRoku($row);
            if(!empty($movie)){
                $obj->movies[] = $movie;
            }
        }
        ObjectYPT::setCache($cacheName, $obj->movies);
    } else {
        $obj->movies = $movies;
    }


    $itemIds = [];
    foreach ($obj->movies as $value) {
        $itemIds[] = $value->id;
    }
    
    $categoryName = 'All';
    $playlistName = 'all';
    if(!empty($_REQUEST['program_id'])){
        $program = new PlayList($_REQUEST['program_id']);
        $categoryName = trim($program->getName()); 
        $playlistName = ucwords(str_replace("-", " ", $categoryName));
    }else if(!empty($_REQUEST['catName'])){
        $categoryName = trim(@$_REQUEST["catName"]);  // 1. LETS USE THE CATEGORY NAME INSTEAD OF 'ALL'
        $playlistName = ucwords(str_replace("-", " ", $categoryName));
    }
    
    if(empty($categoryName)){
        $categoryName = 'All';
    }
    if(empty($playlistName)){
        $playlistName = 'all';
    }
        
    $obj->playlists = [['name' => $playlistName, 'itemIds'=>$itemIds]];
    $obj->categories = [['name' => $categoryName, 'playlistName' => $playlistName, 'order' => 'most_recent']];

    $output = _json_encode($obj);
    if (empty($output) && json_last_error()) {
        $output = json_encode(json_last_error_msg());
        var_dump($obj);
    } else {
        ObjectYPT::setCache($cacheFeedName, $output);
    }
} else {
    //echo '<!-- cache -->';
}
if (!is_string($output)) {
    $output = json_encode($output);
}
die($output);
