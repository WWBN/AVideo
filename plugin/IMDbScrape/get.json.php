<?php
header('Content-Type: application/json');
require_once '../../videos/configuration.php';
//require_once $global['systemRootPath'] . 'plugin/Bookmark/Objects/BookmarkTable.php';
require_once $global['systemRootPath'] . 'plugin/IMDbScrape/imdb.class.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
                                                
if(!User::isAdmin() && !Video::canEdit($_GET['videos_id'])){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}
/*
$o = new BookmarkTable(@$_POST['id']);
$o->setName($_POST['name']);
$o->setTimeInSeconds($_POST['timeInSeconds']);
$o->setVideos_id($_POST['videos_id']);

if($id = $o->save()){
    $obj->error = false;
}
 * 
 */

$video = new Video('', '', $_GET['videos_id']);

$oIMDB = new IMDB($video->getTitle());
if ($oIMDB->isReady) {
    $videoFileName = $video->getFilename();
    $poster = $oIMDB->getPoster('big', true);
    $filename = "{$global['systemRootPath']}videos/{$videoFileName}_portrait.jpg";
    file_put_contents($filename, url_get_contents($poster));
    $obj->error = false;
} else {
    $obj->msg = "Movie not found";
}

echo json_encode($obj);
