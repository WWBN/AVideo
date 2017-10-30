<?php
require_once '../videos/configuration.php';
require_once 'video.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Content-Type: application/json');

$video = new Video("", "", $_POST['id']);
if(empty($video)){
    die("Object not found");
}
$video->addView();

$VideoInformation = $video->getVideo($_POST['id']);

$VideoInformation['Thumbnail'] = "{$global['webSiteRootURL']}videos/".$VideoInformation['filename'].".jpg";
$VideoInformation['CreatorImage'] = "{$global['webSiteRootURL']}".$VideoInformation['photoURL'];
$VideoInformation['VideoSources'] = getSources($VideoInformation['filename'], true);
//Fix null in myVote
if ($VideoInformation['myVote'] == null) {$VideoInformation['myVote']= 0;}

echo json_encode($VideoInformation);