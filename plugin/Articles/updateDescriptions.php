<?php
require_once '../../videos/configuration.php';

if(!User::isAdmin()){
    die("Must be admin");
}


$videos = Video::getAllVideosLight("", false, true);
foreach ($videos as $value) {
    $newDescription = strip_tags($value['description']);
    $value['description'] = trim($value['description']);
    if(empty($value['description'])){
        continue;
    }
    if(strip_tags($value['description']) === $value['description']){
        $video = new Video("", "", $value['id']);
        $video->setDescription(nl2br(textToLink($value['description'])));
        $video->save();
        echo "{$value['title']}<br>";
    }
    
}


