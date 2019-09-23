<?php
require_once '../../videos/configuration.php';

if(!User::isAdmin()){
    die("Must be admin");
}
ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

$videos = Video::getAllVideosLight("", false, true);
foreach ($videos as $value) {
    $value['description'] = trim($value['description']);
    if(empty($value['description'])){
        continue;
    }
    $newDescription = strip_tags($value['description'], "<br><p>");
    
    if($newDescription==$value['description']){
        continue;
    }
    $newDescription = br2nl($newDescription);
    if($newDescription==$value['description']){
        continue;
    }
    if(empty($newDescription)){
        continue;
    }
    
    $video = new Video("", "", $value['id']);
    $video->setDescription($newDescription);
    $video->save();
    echo "{$value['title']}<br>";
    
}


