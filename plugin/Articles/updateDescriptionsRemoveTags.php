<?php
require_once '../../videos/configuration.php';

if(!User::isAdmin()){
    die("Must be admin");
}


$videos = Video::getAllVideosLight("", false, true);
foreach ($videos as $value) {
    $value['description'] = trim($value['description']);
    if(empty($value['description'])){
        continue;
    }
    $newDescription = strip_tags($value['description'], "<br><p>");
    $newDescription = br2nl($newDescription);
    $video = new Video("", "", $value['id']);
    $video->setDescription($newDescription);
    $video->save();
    echo "{$value['title']}<br>";
    
}


